<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the users list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Users List") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_users"));

        Jaris\View::addTab(t("Navigation View"), "admin/users");
        Jaris\View::addTab(t("List View"), "admin/users/list");
        Jaris\View::addTab(t("Create User"), "admin/users/add");
        Jaris\View::addTab(t("Groups"), "admin/groups");
        Jaris\View::addTab(t("Export"), "admin/users/export");

        if(
            Jaris\Authentication::groupHasPermission(
                "edit_users",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Re-index Users List"),
                "admin/users/re-index",
                array(),
                1
            );
        }

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        if(!isset($_REQUEST["group"]))
        {
            $_REQUEST["group"] = "";
        }

        if(!isset($_REQUEST["status"]))
        {
            $_REQUEST["status"] = "";
        }

        $group = "";
        if(trim($_REQUEST["group"]) != "")
        {
            $is_regular = $_REQUEST["group"] == "regular" ? true : false;

            $group = str_replace("'", "''", $_REQUEST["group"]);
            $group = "where user_group='$group'";

            if($is_regular)
            {
                $group .= " or user_group=''";
            }
        }

        $groups_array = Jaris\Groups::getList();

        $status = "";
        if(trim($_REQUEST["status"]) != "")
        {
            $status = str_replace("'", "''", $_REQUEST["status"]);

            if($group == "")
                $status = "where status='$status'";
            else
                $status = "and status='$status'";
        }

        $status_array = Jaris\Users::getStatuses();

        print "<form method=\"get\" action=\"" . Jaris\Uri::url("admin/users/list") . "\">\n";

        print t("Filter view by group:") . " <select name=\"group\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach($groups_array as $group_name => $machine_name)
        {
            $selected = "";

            if($_REQUEST["group"] == $machine_name)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$machine_name\">$group_name</option>\n";
        }
        print "</select>\n";

        print t(" status:") . " <select name=\"status\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach($status_array as $status_label => $status_id)
        {
            $selected = "";

            if($_REQUEST["status"] == $status_id)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$status_id\">$status_label</option>\n";
        }
        print "</select>\n";

        print "<input type=\"submit\" value=\"" . t("View") . "\" />";

        print "</form>\n";

        $status_captions = array();
        foreach($status_array as $caption => $id)
        {
            $status_captions[$id] = $caption;
        }


        $users_count = Jaris\Sql::countColumn(
            "users",
            "users",
            "username",
            "$group $status"
        );

        print "<h2>" . t("Total users:") . " " . $users_count . "</h2>";

        $users = Jaris\Sql::getDataList(
            "users",
            "users",
            $page - 1,
            30,
            "$group $status order by username asc",
            "username"
        );

        Jaris\System::printNavigation(
            $users_count,
            $page,
            "admin/users/list",
            "",
            30,
            array("group" => $_REQUEST["group"])
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Username") . "</td>";
        print "<td>" . t("E-mail") . "</td>";
        print "<td>" . t("Status") . "</td>";
        print "<td>" . t("Register date") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        foreach($users as $username)
        {
            $username = $username["username"];
            $user_data = Jaris\Users::get($username);

            print "<tr>";

            print "<td>" . $username . "</td>";

            print "<td>" . $user_data["email"] . "</td>";

            print "<td>";
            print ($status_captions[$user_data["status"]] != "" ?
                $status_captions[$user_data["status"]] : t("Active"));
            print "</td>";

            print "<td>";
            print date("m/d/Y g:i:s a", $user_data["register_date"]);
            print "</td>";

            $edit_url = Jaris\Uri::url(
                "admin/users/edit",
                array("username" => $username)
            );

            $delete_url = Jaris\Uri::url(
                "admin/users/delete",
                array("username" => $username)
            );

            print "<td>" .
                "<a href=\"$edit_url\">" . t("Edit") . "</a> " .
                "<a href=\"$delete_url\">" . t("Delete") . "</a>" .
                "</td>";

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $users_count,
            $page,
            "admin/users/list",
            "",
            30,
            array("group" => $_REQUEST["group"])
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
