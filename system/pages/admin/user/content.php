<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the user content list page.
 */
//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("My Content") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_content"]);

        if (empty($_REQUEST["type"])) {
            $_REQUEST["type"] = "";
        }

        $type = "";
        if (trim($_REQUEST["type"]) != "") {
            $type = str_replace("'", "''", $_REQUEST["type"]);
            $type = "and type='$type'";
        }

        $types_array = Jaris\Types::getList(
            Jaris\Authentication::currentUserGroup()
        );

        print "<form method=\"get\" action=\"" .
            Jaris\Uri::url("admin/user/content") . "\">\n"
        ;

        print t("Filter by type:") .
            " <select onchange=\"javascript: this.form.submit()\" name=\"type\">\n"
        ;

        print "<option value=\"\">" . t("All") . "</option>\n";

        foreach ($types_array as $machine_name => $type_data) {
            $selected = "";

            if ($_REQUEST["type"] == $machine_name) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$machine_name\">{$type_data['name']}</option>\n";
        }

        print "</select>\n";

        print "</form>\n";

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        $user = Jaris\Authentication::currentUser();

        $pages_count = Jaris\Sql::countColumn(
            "search_engine",
            "uris",
            "uri",
            "where author='$user' $type"
        );

        print "<h2>" . t("Total content:") . " " . $pages_count . "</h2>";

        $pages = Jaris\Sql::getDataList(
            "search_engine",
            "uris",
            $page - 1,
            20,
            "where author='$user' $type order by created_date desc"
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/user/content",
            "",
            20,
            ["type" => $_REQUEST["type"]]
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td></td>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Dates") . "</td>";
        print "<td>" . t("Type") . "</td>";

        if (
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            print "<td>" . t("Operation") . "</td>";
        }

        print "</tr>";
        print "</thead>";

        foreach ($pages as $data) {
            $page_data = Jaris\Pages::get($data["uri"]);
            $type_data = Jaris\Types::get($page_data["type"]);
            $type = $page_data["type"] ? t($type_data["name"]) : t("system");

            print "<tr>";

            $images = Jaris\Pages\Images::getList($data['uri']);
            $image_url = '';

            foreach ($images as $image) {
                $image_url = Jaris\Uri::url(
                    "image/{$data['uri']}/{$image['name']}",
                    ["w" => 100]
                );
            }

            if ($image_url) {
                print "<td><a href=\"" .
                    Jaris\Uri::url($data["uri"]) .
                    "\"><img src=\"$image_url\" /></a></td>"
                ;
            } else {
                print "<td></td>";
            }

            print "<td><a href=\"" .
                Jaris\Uri::url($data["uri"]) . "\">" .
                Jaris\System::evalPHP($page_data["title"]) . "</a></td>"
            ;

            print
                "<td>" .
                t("Created:") . " " . date("m/d/Y g:i:s a", $page_data["created_date"]) . "<br />" .
                t("Edited:") . " " . date("m/d/Y g:i:s a", $page_data["last_edit_date"]) .
                "</td>";

            print "<td>" . $type . "</td>";

            $edit_url = Jaris\Uri::url("admin/pages/edit", ["uri" => $data["uri"]]);
            $delete_url = Jaris\Uri::url("admin/pages/delete", ["uri" => $data["uri"]]);

            if (
                Jaris\Authentication::groupHasPermission(
                    "edit_content",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::groupHasPermission(
                    "delete_content",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                print "<td>";
                if (
                    Jaris\Authentication::groupHasPermission(
                        "edit_content",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    print "<a href=\"$edit_url\">" . t("Edit") . "</a> <br />";
                }
                if (
                    Jaris\Authentication::groupHasPermission(
                        "delete_content",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    print "<a href=\"$delete_url\">" . t("Delete") . "</a>";
                }
                print "</td>";
            }

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/user/content",
            "",
            20,
            ["type" => $_REQUEST["type"]]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
