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
    <?php print t("Blog Browser") ?>
    field;

    field: content
    <?php
        Jaris\View::addStyle(
            Jaris\Modules::directory("blog") . "styles/list.css"
        );

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $category = "";
        $category_navigation = "";

        if(isset($_REQUEST["c"]))
        {
            $category = $_REQUEST["c"];
            $category_navigation = $_REQUEST["c"];

            $category = str_replace("'", "''", $category);
        }

        $blogs_count = 0;
        if($category != "")
        {
            $blogs_count = Jaris\Sql::countColumn(
                "blog",
                "blogs",
                "id",
                "where category='$category'"
            );
        }
        else
        {
            $blogs_count = Jaris\Sql::countColumn("blog", "blogs", "id");
        }

        print "<h2>" . t("Total Blogs:") . " " . $blogs_count . "</h2>";

        $blogs = array();

        if($category != "")
        {
            $blogs = Jaris\Sql::getDataList(
                "blog",
                "blogs",
                $page - 1,
                20,
                "where category='$category' order by created_timestamp desc"
            );
        }
        else
        {
            $blogs = Jaris\Sql::getDataList("blog", "blogs", $page - 1, 20);
        }

        foreach($blogs as $data)
        {
            $user_data = Jaris\Users::get($data["user"]);

            if($user_data["picture"])
            {
                $picture = Jaris\Uri::url("image/user/" . $data["user"]);
            }
            else
            {
                $picture = Jaris\Uri::url(
                    Jaris\Modules::directory("blog") . "images/no-picture.png"
                );
            }

            $user_url = Jaris\Uri::url("blog/user/" . $data["user"]);

            print "<div class=\"blog-list\">\n"
                . "<div class=\"title\">"
                . "<a title=\"{$data["title"]}\" href=\"" . $user_url . "\">"
                . $data["title"]
                . "</a>"
                . "</div>\n"
                . "<div class=\"thumbnail\">"
                . "<a title=\"{$data["title"]}\" href=\"" . $user_url . "\">"
                . "<img alt=\"{$data["title"]}\" src=\"$picture\" />"
                . "</a>"
                . "</div>\n"
                . "<div class=\"details\">\n"
                . "<div class=\"views\">"
                . t("Views:") . " " . $data["views"]
                . "</div>\n"
                . "<div class=\"user\">"
                . t("Created by:")
                . " <a href=\"$user_url\">" . $data["user"] . "</a>"
                . "</div>\n"
                . "</div>\n"
            ;

            if($data["description"])
            {
                print "<div class=\"description\">" .
                    $data["description"] .
                    "</div>\n"
                ;
            }

            print "<div style=\"clear: both\"></div>\n";
            print "</div>\n";
        }

        if(count($blogs) <= 0)
        {
            Jaris\View::addMessage(
                t("No available blogs on the system yet. Register an account and start posting")
            );
        }

        print "<div style=\"clear: both\"></div>\n";

        Jaris\System::printNavigation(
            $blogs_count,
            $page,
            "blog/browser",
            "blog",
            20,
            array("c" => $category_navigation)
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
