<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content navigation page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Pages") ?>
    field;

    field: content
    <?php
        if (
            !Jaris\Authentication::groupHasPermission(
                "view_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\Uri::go("admin/user/content");
        }

        if (isset($_REQUEST["uri"])) {
            Jaris\View::addMessage(
                t("You are currently navigating:") . " " . $_REQUEST["uri"],
                "normal"
            );

            $directories = Jaris\Util::directoryBrowser(
                Jaris\Site::dataDir() . "pages/" . $_REQUEST["uri"]
            );

            $navigation = Jaris\Util::generateBrowserNavigation(
                $directories,
                Jaris\Site::dataDir() . "pages"
            );

            $sections = [];
            $alphabet = [];
            $pages = [];

            //Store Data
            foreach ($navigation as $link) {
                if ($link["type"] == "section") {
                    $sections[] = $link;
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
            if (count($sections) > 0) {
                print "<h3>" . t("Sections") . "</h3>";
                print "<ul>";
                foreach ($sections as $link) {
                    if ($link["type"] == "section") {
                        $url = Jaris\Uri::url(
                            "admin/pages",
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
                            "admin/pages",
                            ["uri" => $link['path']]
                        );

                        print "<a href=\"$url\">{$link['current']}</a> &nbsp;";
                    }
                }
                print "</fieldset>";
            }

            if (count($pages) > 0) {
                print "<h3>" . t("Pages") . "</h3>";
                print "<ul>";
                foreach ($pages as $link) {
                    if ($link["type"] == "page") {
                        $uri = Jaris\Uri::getFromPath($link['path']);

                        $url = Jaris\Uri::url(
                            "admin/pages/edit",
                            ["uri" => $uri]
                        );

                        print "<li><a href=\"$url\">{$link['current']}</a></li>";
                    }
                }
                print "</ul>";
            }
        } else {
            Jaris\View::addTab(t("List View"), "admin/pages/list");
            Jaris\View::addTab(t("Create Page"), "admin/pages/types");

            $pages = Jaris\Uri::url("admin/pages", ["uri" => "singles"]);

            $sections = Jaris\Uri::url("admin/pages", ["uri" => "sections"]);

            print "<h3>" . t("Navigation") . "</h3>";
            print "<a href=\"$pages\">" . t("Singles") . "</<a><br />";
            print "<a href=\"$sections\">" . t("Sections") . "</<a><br />";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
