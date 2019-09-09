<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the pages listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Pages List") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_content"]);
    ?>
    <script>
        $(document).ready(function(){
            $("#pages-select-action").change(function(){
                if($(this).val() == "all"){
                    $("input[name='pages[]']").attr("checked", true);
                }
                else if($(this).val() == "none"){
                    $("input[name='pages[]']").attr("checked", false);
                }
                $(this).val("");
            });
        });
    </script>
    <?php
        Jaris\View::addTab(t("Navigation View"), "admin/pages");
        Jaris\View::addTab(t("Create Page"), "admin/pages/types");

        if (!isset($_REQUEST["type"])) {
            $_REQUEST["type"] = "";
        }

        if (!isset($_REQUEST["author"])) {
            $_REQUEST["author"] = "";
        }

        $type = "";
        if (!empty($_REQUEST["type"]) && trim($_REQUEST["type"]) != "") {
            $type = str_replace("'", "''", $_REQUEST["type"]);
            $type = "type='$type'";
        }

        $types_array = Jaris\Types::getList();

        $author = "";
        if (!empty($_REQUEST["author"]) && trim($_REQUEST["author"]) != "") {
            $username = str_replace("'", "''", $_REQUEST["author"]);

            if ($type) {
                $author = "and ";
            }

            $author .= "author='$username'";
        }

        $where = "";
        if ($type || $author) {
            $where = "where ";
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        print "<form method=\"get\" action=\"" . Jaris\Uri::url("admin/pages/list") . "\">\n";
        print "<div style=\"float: left\">";
        print t("Filter by type:") . " <select onchange=\"javascript: this.form.submit()\" name=\"type\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach ($types_array as $machine_name => $type_data) {
            $selected = "";

            if (
                !empty($_REQUEST["author"]) &&
                $_REQUEST["type"] == $machine_name
            ) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$machine_name\">"
                . $type_data['name']
                . "</option>\n"
            ;
        }
        print "</select>\n";
        print "</div>";

        print "<div style=\"float: right\">";
        print t("Username:") . " <input style=\"width: 80px;\" type=\"text\" name=\"author\" value=\"{$_REQUEST["author"]}\">";
        print " <input type=\"submit\" value=\"" . t("Submit") . "\">\n";
        print "</div>";

        print "</form>\n";

        print "<div style=\"clear: both\"></div>";

        print "<hr />";

        $pages_count = Jaris\Sql::countColumn(
            "search_engine",
            "uris",
            "uri",
            "$where $type $author"
        );

        print "<form method=\"post\" action=\"" . Jaris\Uri::url("admin/pages/mass-delete") . "\">\n";
        print "<div style=\"float: left\">";
        print "<h2>" . t("Total content:") . " " . $pages_count . "</h2>";
        print "</div>";

        print "<div style=\"float: right; padding-top: 25px;\">";
        print t("Select:") . " <select id=\"pages-select-action\">\n";
        print "<option value=\"\">" . t("-Action-") . "</option>\n";
        print "<option value=\"all\">" . t("All") . "</option>\n";
        print "<option value=\"none\">" . t("None") . "</option>\n";
        print "</select>\n";

        print "<input type=\"hidden\" name=\"type\" value=\"{$_REQUEST["type"]}\">";
        print "<input type=\"hidden\" name=\"author\" value=\"{$_REQUEST["author"]}\">";
        print " <input type=\"submit\" value=\"" . t("Delete Selected") . "\">\n";
        print "</div>";

        print "<div style=\"clear: both\"></div>";

        $pages = Jaris\Sql::getDataList(
            "search_engine",
            "uris",
            $page - 1,
            20,
            "$where $type $author order by created_date desc"
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/pages/list",
            "",
            20,
            [
                "type" => $_REQUEST["type"],
                "author" => $_REQUEST["author"]
            ]
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td></td>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Author") . "</td>";
        print "<td>" . t("Dates") . "</td>";
        print "<td>" . t("Type") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "<td></td>";
        print "</tr>";
        print "</thead>";

        foreach ($pages as $result_fields) {
            $uri = $result_fields["uri"];

            $page_data = Jaris\Pages::get($uri);
            $author = isset($page_data["author"]) ?
                $page_data["author"] : t("system")
            ;
            $type = t("system");

            if (isset($page_data["type"])) {
                $type_data = Jaris\Types::get($page_data["type"]);
                $type = t($type_data["name"]);
            }

            print "<tr>";

            $images = Jaris\Pages\Images::getList($uri);
            $image_url = '';

            foreach ($images as $image) {
                $image_url = Jaris\Uri::url(
                    "image/$uri/{$image['name']}",
                    ["w" => 100]
                );
            }

            if ($image_url) {
                print "<td><a href=\"" . Jaris\Uri::url($uri) . "\"><img src=\"$image_url\" /></a></td>";
            } else {
                print "<td></td>";
            }

            print "<td><a href=\"" . Jaris\Uri::url($uri) . "\">" . Jaris\System::evalPHP($page_data["title"]) . "</a></td>";

            print "<td>" . $author . "</td>";

            print "<td>";
            if (isset($page_data["created_date"])) {
                print t("Created:") . " "
                    . date("m/d/Y g:i:s a", $page_data["created_date"])
                ;
            }
            if (isset($page_data["last_edit_date"])) {
                print "<br />" . t("Edited:") . " "
                    . date("m/d/Y g:i:s a", $page_data["last_edit_date"])
                ;
            }
            print "</td>";

            print "<td>" . $type . "</td>";

            $edit_url = Jaris\Uri::url("admin/pages/edit", ["uri" => $uri]);
            $delete_url = Jaris\Uri::url("admin/pages/delete", ["uri" => $uri]);

            print "<td>"
                . "<a href=\"$edit_url\">" . t("Edit") . "</a> <br />"
                . "<a href=\"$delete_url\">" . t("Delete") . "</a>"
                . "</td>"
            ;

            print "<td>"
                . "<input type=\"checkbox\" name=\"pages[]\" value=\"$uri\" />"
                . "</td>"
            ;

            print "</tr>";
        }

        print "</table>";

        print "</form>\n";

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/pages/list",
            "",
            20,
            [
                "type" => $_REQUEST["type"],
                "author" => $_REQUEST["author"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
