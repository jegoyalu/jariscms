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
        <?php print t("Api Access") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_keys_api"));

        Jaris\View::addTab(t("Add Key"), "admin/settings/api/add-key");

        Jaris\ApiKey::createDatabase();

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $pages_count = Jaris\Sql::countColumn(
            "api_keys",
            "api_keys",
            "key"
        );

        $keys = Jaris\Sql::getDataList(
            "api_keys",
            "api_keys",
            $page - 1,
            20,
            "order by created_date desc"
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Key") . "</td>";
        print "<td>" . t("Description") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        foreach($keys as $data)
        {
            $edit_url = Jaris\Uri::url(
                "admin/settings/api/edit-key",
                array("id" => $data["id"])
            );

            $delete_url = Jaris\Uri::url(
                "admin/settings/api/delete-key",
                array("id" => $data["id"])
            );

            print "<td>".$data["key"]."</td>";

            print "<td>".$data["description"]."</td>";

            print "<td>"
                . "<a href=\"$edit_url\">" . t("Edit") . "</a> <br />"
                . "<a href=\"$delete_url\">" . t("Delete") . "</a>"
                . "</td>"
            ;

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/settings/api",
            "",
            20
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
