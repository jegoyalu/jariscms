<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the translate page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Translate Page") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["translate_languages"]);

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        //Tabs
        if (
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }

        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);

        if (
            Jaris\Authentication::groupHasPermission(
                "view_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "view_images",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "view_files",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "translate_languages",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $languages = Jaris\Language::getInstalled();

        print "<table class=\"languages-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Code") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        foreach ($languages as $code => $name) {
            if ($code != "en") {
                print "<tr>\n";

                print "<td>" . $code . "</td>\n";
                print "<td>" . $name . "</td>\n";

                $edit_url = Jaris\Uri::url(
                    "admin/languages/translate",
                    [
                        "code" => $code,
                        "type" => "page",
                        "uri" => $_REQUEST["uri"]
                    ]
                );

                $edit_text = t("Translate");

                print "<td>
                    <a href=\"$edit_url\">$edit_text</a>&nbsp;
                   </td>\n";

                print "</tr>\n";
            }
        }

        print "</table>\n";
    ?>
    field;

    field: is_system
        1
    field;
row;
