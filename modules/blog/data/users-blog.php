<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Blog Posted Content") ?>
    field;

    field: content
    <?php
        if(!Jaris\Authentication::hasTypeAccess("blog", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\Authentication::protectedPage();
        }

        if(Jaris\Authentication::groupHasPermission("add_content", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(
                t("View Blog"),
                Jaris\Modules::getPageUri("blog/user", "blog") . "/" . Jaris\Authentication::currentUser()
            );

            Jaris\View::addTab(
                t("Edit Blog Settings"),
                Jaris\Modules::getPageUri("admin/blog/edit", "blog")
            );

            Jaris\View::addTab(
                t("Add Post"),
                Jaris\Modules::getPageUri("admin/pages/add", "blog"),
                array("type" => "blog")
            );
        }

        Jaris\View::addTab(
            t("Subscriptions"),
            Jaris\Modules::getPageUri("blog/subscriptions", "blog")
        );

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $user_path = str_replace(
            "data.php",
            "",
            Jaris\Users::getPath(Jaris\Authentication::currentUser(), Jaris\Authentication::currentUserGroup())
        );

        $blog_count = Jaris\Sql::countColumn(
            "blog",
            "post",
            "id",
            "",
            $user_path
        );

        print "<h2>" . t("Total post:") . " " . $blog_count . "</h2>";

        $blogs = Jaris\Sql::getDataList(
            "blog",
            "post",
            $page - 1,
            10,
            "order by created_timestamp desc",
            "*",
            $user_path
        );

        Jaris\System::printNavigation(
            $blog_count,
            $page,
            "users/blog",
            "blog",
            10
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Thumbnail") . "</td>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Views") . "</td>";
        print "<td>" . t("Actions") . "</td>";
        print "</tr>";
        print "</thead>";

        foreach($blogs as $blog_data)
        {
            $page_data = Jaris\Pages::get($blog_data["uri"]);

            $images = Jaris\Pages\Images::getList($blog_data["uri"]);
            $thumbnail = false;

            foreach($images as $image)
            {
                $thumbnail = Jaris\Uri::url(
                    "image/" . $blog_data["uri"] . "/0",
                    array("w" => 100, "h" => 60)
                );

                break;
            }

            print "<tr>";

            print "<td>";
            if($thumbnail)
            {
                print "<a href=\"" . Jaris\Uri::url($blog_data["uri"]) . "\"><img alt=\"{$page_data['title']}\" src=\"$thumbnail\" /></a>";
            }
            print "</td>";

            print "<td>{$page_data["title"]}</td>";

            $created = "<strong>" . t("Created:") . "</strong> " . date("m/d/Y g:i:s a", $page_data["created_date"]);

            $edited = $page_data["last_edit_date"] ?
                "<div><strong>" . t("Edited:") . "</strong> " . date("m/d/Y g:i:s a", $page_data["last_edit_date"]) . "</div>"
                :
                ""
            ;

            print "<td>$created $edited</td>";

            print "<td>" . $page_data["views"] . "</td>";

            $view_url = Jaris\Uri::url($blog_data["uri"]);
            $edit_url = Jaris\Uri::url("admin/pages/edit", array("uri" => $blog_data["uri"]));
            $delete_url = Jaris\Uri::url("admin/pages/delete", array("uri" => $blog_data["uri"]));

            print "<td>";

            print "<div class=\"view\"><a href=\"$view_url\">" . t("View") . "</a><div>" .
                "<div class=\"edit\"><a href=\"$edit_url\">" . t("Edit") . "</a><div>" .
                "<div class=\"delete\"><a href=\"$delete_url\">" . t("Delete") . "</a><div>"
            ;

            print "</td>";

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $blog_count,
            $page,
            "users/blog",
            "blog",
            10
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
