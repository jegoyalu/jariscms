<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the groups management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Groups") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_groups"]);

        Jaris\View::addTab(t("Users"), "admin/users");
        Jaris\View::addTab(t("Create Group"), "admin/groups/add");

        $groups = Jaris\Groups::getList();
        $groups["Guest"] = "guest";

        print "<table "
            . "class=\"groups-list navigation-list navigation-list-hover\""
            . ">\n"
        ;

        print "<thead><tr>\n";

        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        foreach ($groups as $name => $machine_name) {
            $group_data = Jaris\Groups::get($machine_name);
            $description = $group_data["description"];

            print "<tr>\n";

            print "<td>" . t($name) . "</td>\n";
            print "<td>" . t($description) . "</td>\n";

            $edit_url = Jaris\Uri::url(
                "admin/groups/edit",
                ["group" => $machine_name]
            );

            $permissions_url = Jaris\Uri::url(
                "admin/groups/permissions",
                ["group" => $machine_name]
            );

            $delete_url = Jaris\Uri::url(
                "admin/groups/delete",
                ["group" => $machine_name]
            );

            $edit_text = t("Edit");
            $permissions_text = t("Permissions");
            $delete_text = t("Delete");

            print "<td>";
            print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
            print "<a href=\"$permissions_url\">$permissions_text</a>&nbsp;";
            print "<a href=\"$delete_url\">$delete_text</a>";
            print "</td>\n";

            print "</tr>\n";
        }

        print "</table>\n";
    ?>
    field;

    field: is_system
        1
    field;
row;
