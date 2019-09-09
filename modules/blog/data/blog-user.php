<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the view user post page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        $user = "";
        if (empty($_REQUEST["user"])) {
            Jaris\Uri::go("");
        } else {
            $user = strval($_REQUEST["user"]);
        }

        $blog_data = blog_get_from_db($user);

        if ($blog_data["title"]) {
            print $blog_data["title"];
        } else {
            print $user;
        }
        print " " . t("blog");
    ?>
    field;

    field: content
    <?php
        Jaris\View::addStyle(
        Jaris\Modules::directory("blog") . "styles/post.css"
        );

        $user = strval($_REQUEST["user"]);

        $user_data = Jaris\Users::get($user);

        if (
            Jaris\Authentication::isUserLogged()
            &&
            Jaris\Authentication::currentUser() == $user
        ) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "add_content",
                    $user_data["group"]
                )
                &&
                Jaris\Authentication::hasTypeAccess(
                    "blog",
                    $user_data["group"]
                )
            ) {
                Jaris\View::addTab(
                    t("Manage Blog"),
                    Jaris\Modules::getPageUri("users/blog", "blog")
                );

                Jaris\View::addTab(
                    t("Add Post"),
                    Jaris\Modules::getPageUri("admin/pages/add", "blog"),
                    ["type" => "blog"]
                );
            }
        }

        Jaris\View::addTab(
            t("Subscriptions"),
            Jaris\Modules::getPageUri("blog/subscriptions", "blog"),
            ["user" => $user]
        );

        if (
            Jaris\Authentication::isUserLogged()
            &&
            Jaris\Authentication::currentUser() != $user
        ) {
            if (
                !blog_subscribed(
                    $user,
                    Jaris\Authentication::currentUser()
                )
            ) {
                Jaris\View::addTab(
                    t("Subscribe"),
                    Jaris\Modules::getPageUri("blog/subscribe", "blog"),
                    ["user" => $user]
                );
            } else {
                Jaris\View::addTab(
                    t("Unsubscribe"),
                    Jaris\Modules::getPageUri("blog/unsubscribe", "blog"),
                    ["user" => $user]
                );
            }
        }

        $blog_data = blog_get_from_db($user);

        if ($blog_data["description"]) {
            print "<div class=\"blog-description\">"
                . $blog_data['description']
                . "</div>"
            ;
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        $month_query = "";
        $year_query = "";
        $where = "";

        $arguments = [];

        if (isset($_REQUEST["m"])) {
            $_REQUEST["m"] = intval($_REQUEST["m"]);

            $month = str_replace("'", "''", $_REQUEST["m"]);
            $month_query = "month='$month' and ";

            $arguments["m"] = $_REQUEST["m"];
        }

        if (isset($_REQUEST["y"])) {
            $_REQUEST["y"] = intval($_REQUEST["y"]);

            $year = str_replace("'", "''", $_REQUEST["y"]);
            $year_query = "year='$year'";

            $arguments["y"] = $_REQUEST["y"];
        }

        if (isset($_REQUEST["m"]) || isset($_REQUEST["y"])) {
            $where = "where {$month_query}{$year_query}";
        }

        $database_path = str_replace(
            "data.php",
            "",
            Jaris\Users::getPath($user, $user_data["group"])
        );

        $post_count = Jaris\Sql::countColumn(
            "blog",
            "post",
            "id",
            $where,
            $database_path
        );

        $post = Jaris\Sql::getDataList(
            "blog",
            "post",
            $page - 1,
            10,
            $where . "order by created_timestamp desc",
            "*",
            $database_path
        );

        foreach ($post as $post_data) {
            print blog_theme($post_data);
        }

        Jaris\System::printNavigation(
            $post_count,
            $page,
            "blog/user/" . $user,
            "",
            10,
            $arguments
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
