<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *https://opensource.org/licenses/GPL-3.0.
 *
 *@file Database file that stores the current user blog subscriptions page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        if ($user_data = Jaris\Users::get($_REQUEST["user"])) {
            $blog_data = blog_get_from_db($_REQUEST["user"]);
            $title = $_REQUEST["user"];
            if ($blog_data["title"]) {
                $title = $blog_data["title"];
            }
            print t("Blog subscriptions of") . " " . $title;
        } else {
            print t("My Blog Subscriptions");
        }
    ?>
    field;

    field: content
    <?php
        Jaris\View::addStyle(
        Jaris\Modules::directory("blog") . "styles/list.css"
        );

        $user = "";
        $user_nagivation = "";

        if (isset($_REQUEST["user"])) {
            $user_nagivation = $_REQUEST["user"];
            $user_data = Jaris\Users::get($_REQUEST["user"]);
            if (
                $user_data
                &&
                Jaris\Authentication::hasTypeAccess(
                    "blog",
                    $user_data["group"]
                )
            ) {
                $user = $_REQUEST["user"];
                $user = str_replace("'", "''", $user);

                if (
                    Jaris\Authentication::groupHasPermission(
                        "add_content",
                        $user_data["group"]
                    )
                ) {
                    Jaris\View::addTab(
                        t("Blog"),
                        Jaris\Modules::getPageUri(
                            "blog/user",
                            "blog"
                        ) . "/" . $_REQUEST["user"]
                    );
                }

                Jaris\View::addTab(
                    t("Subscriptions"),
                    Jaris\Modules::getPageUri(
                        "blog/subscriptions",
                        "blog"
                    ),
                    ["user"=>$_REQUEST["user"]]
                );
            } else {
                Jaris\Uri::go("");
            }
        } else {
            if (
                Jaris\Authentication::isUserLogged()
                &&
                Jaris\Authentication::hasTypeAccess(
                    "blog",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                $user = Jaris\Authentication::currentUser();

                if (
                    Jaris\Authentication::groupHasPermission(
                        "add_content",
                        Jaris\Authentication::currentUserGroup()
                    )
                    &&
                    Jaris\Authentication::hasTypeAccess(
                        "blog",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    Jaris\View::addTab(
                        t("View Blog"),
                        Jaris\Modules::getPageUri("blog/user", "blog")
                            . "/" . Jaris\Authentication::currentUser()
                    );

                    Jaris\View::addTab(
                        t("Edit Blog Settings"),
                        Jaris\Modules::getPageUri("admin/blog", "blog")
                    );

                    Jaris\View::addTab(
                        t("New Post"),
                        "admin/pages/add",
                        ["type"=>"blog"]
                    );
                }

                Jaris\View::addTab(
                    t("Subscriptions"),
                    Jaris\Modules::getPageUri("blog/subscriptions", "blog")
                );
            } else {
                Jaris\Uri::go("");
            }
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        $blog_count = 0;
        $blog_count = Jaris\Sql::countColumn(
            "blog_subscriptions",
            "subscriptions",
            "id",
            "where subscriber='$user'"
        );

        print "<h2>"
            . t("Total Subscriptions:")
            . " "
            . $blog_count
            . "</h2>"
        ;

        $blogs = [];
        $blogs = Jaris\Sql::getDataList(
            "blog_subscriptions",
            "subscriptions",
            $page-1,
            20,
            "where subscriber='$user' order by created_timestamp desc"
        );

        foreach ($blogs as $data) {
            $user_data = Jaris\Users::get($data["user"]);

            if ($user_data["picture"]) {
                $poster = Jaris\Uri::url("image/user/" . $data["user"]);
            } else {
                $poster = Jaris\Uri::url(
                    Jaris\Modules::directory("blog")
                        . "images/no-picture.png"
                );
            }

            $title = $data["user"];
            $blog_data = blog_get_from_db($data["user"]);
            if ($blog_data["title"]) {
                $title = $blog_data["title"];
            }

            $user_url = Jaris\Uri::url("blog/user/" . $data["user"]);

            print "<div class=\"blogs-list\">\n"
                . "<div class=\"title\">"
                . "<a title=\"$title\" href=\"" . $user_url . "\">"
                . $title
                . "</a>"
                . "</div>\n"
                . "<div class=\"thumbnail\">"
                . "<a title=\"$title\" href=\"" . $user_url . "\">"
                . "<img alt=\"{$data["title"]}\" src=\"$poster\" />"
                . "</a>"
                . "</div>\n"
                . "<div class=\"details\">\n"
                . "<div class=\"views\">"
                . t("Views:") . " " . $blog_data["views"]
                . "</div>\n"
                . "</div>\n"
                . "<div style=\"clear: both\"></div>\n"
                . "</div>\n"
            ;
        }

        print "<div style=\"clear: both\"></div>\n";

        Jaris\System::printNavigation(
            $blog_count,
            $page,
            "blog/subscriptions",
            "blog",
            20,
            ["user"=>$user_nagivation]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
