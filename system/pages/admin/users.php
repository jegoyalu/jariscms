<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the users navigation page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Users") ?>
    field;

    field: content
    <?php
        if (
            !Jaris\Authentication::groupHasPermission(
                "view_users",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\Uri::go("admin/user");
        }

        Jaris\View::addTab(t("Navigation View"), "admin/users");
        Jaris\View::addTab(t("List View"), "admin/users/list");
        Jaris\View::addTab(t("Create User"), "admin/users/add");
        Jaris\View::addTab(t("Groups"), "admin/groups");
        Jaris\View::addTab(t("Export"), "admin/users/export");

        $directories = [];
        if (isset($_REQUEST["uri"])) {
            Jaris\View::addMessage(
                t("You are currently navigating:") . " " . $_REQUEST["uri"],
                "normal"
            );

            $directories = Jaris\Util::directoryBrowser(
                Jaris\Site::dataDir() . "users/" . $_REQUEST["uri"]
            );
        } else {
            $directories = Jaris\Util::directoryBrowser(Jaris\Site::dataDir() . "users");
        }

        if (count($directories) > 0) {
            $navigation = Jaris\Util::generateBrowserNavigation(
                $directories,
                Jaris\Site::dataDir() . "users"
            );

            $groups = [];
            $alphabet = [];
            $pages = [];

            foreach ($navigation as $link) {
                if ($link["type"] == "section") {
                    $groups[] = $link;
                }
            }

            foreach ($navigation as $link) {
                if ($link["type"] == "alphabet") {
                    $alphabet[] = $link;
                }
            }

            foreach ($navigation as $link) {
                if ($link["type"] == "page") {
                    $pages[] = $link;
                }
            }

            //Display Data
            if (count($groups) > 0) {
                print "<h3>" . t("Groups") . "</h3>";
                print "<ul>";
                foreach ($groups as $link) {
                    if ($link["type"] == "section") {
                        $url = Jaris\Uri::url(
                            "admin/users",
                            ["uri" => $link['path']]
                        );

                        print "<li><a href=\"$url\">{$link['current']}</a></li>";
                    }
                }
                print "</ul>";
            }

            if (count($alphabet) > 0) {
                print "<h3>" . t("Alphabetical") . "</h3>";
                print "<fieldset>";
                foreach ($alphabet as $link) {
                    if ($link["type"] == "alphabet") {
                        $url = Jaris\Uri::url(
                            "admin/users",
                            ["uri" => $link['path']]
                        );

                        print "<a href=\"$url\">{$link['current']}</a> &nbsp;";
                    }
                }
                print "</fieldset>";
            }

            if (count($pages) > 0) {
                print "<h3>" . t("Users") . "</h3>";
                print "<ul>";
                foreach ($pages as $link) {
                    if ($link["type"] == "page") {
                        $uri = Jaris\Uri::getFromPath($link['path']);

                        $url = Jaris\Uri::url(
                            "admin/users/edit",
                            ["username" => $uri]
                        );

                        print "<li><a href=\"$url\">{$link['current']}</a></li>";
                    }
                }
                print "</ul>";
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
