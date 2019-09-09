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
        Jaris\View::addTab(t("Import"), "admin/users/import");
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

        if(!isset($_REQUEST["keywords"]))
        {
            $_REQUEST["keywords"] = "";
        }

        if(!isset($_REQUEST["status"]))
        {
            $_REQUEST["status"] = "";
        }

        if(!isset($_REQUEST["sort"]))
        {
            $_REQUEST["user_asc"] = "";
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

        $keywords = "";
        if(trim($_REQUEST["keywords"]) != "")
        {
            $keyword = str_replace("'", "''", $_REQUEST["keywords"]);

            if($group == "")
            {
                $keywords = "where ";
            }
            else
            {
                $keywords .= "and ";
            }

            $keywords .= "("
                . "username like '%$keyword' or "
                . "username like '$keyword%' or "
                . "username like '%$keyword%' or "
                . "email like '%$keyword' or "
                . "email like '$keyword%' or "
                . "email like '%$keyword%' or "
                . "ip_address like '%$keyword' or "
                . "ip_address like '$keyword%' or "
                . "ip_address like '%$keyword%'"
                . ") "
            ;
        }

        $status = "";
        if(trim($_REQUEST["status"]) != "")
        {
            $status = str_replace("'", "''", $_REQUEST["status"]);

            if($group == "" && $keywords == "")
                $status = "where status='$status'";
            else
                $status = "and status='$status'";
        }

        $sort = "order by username asc";
        if(trim($_REQUEST["sort"]) != "user_asc")
        {
            switch($_REQUEST["sort"])
            {
                case "user_desc":
                    $sort = "order by username desc";
                    break;
                case "register_asc":
                    $sort = "order by register_date asc";
                    break;
                case "register_desc":
                    $sort = "order by register_date desc";
                    break;
                default:
                    $sort = "order by username asc";
                    break;
            }
        }

        $status_array = Jaris\Users::getStatuses();

        $parameters["class"] = "jaris-users-list";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields[] = array(
            "type" => "text",
            "name" => "keywords",
            "label" => t("Keywords:"),
            "value" => isset($_REQUEST["keywords"]) ?
                $_REQUEST["keywords"]
                :
                "",
            "placeholder" => t("username, e-mail, ip..."),
            "inline" => true
        );

        $fields[] = array(
            "type" => "select",
            "name" => "group",
            "label" => t("Group:"),
            "value" => array_merge(
                array(t("All") => ""),
                $groups_array
            ),
            "selected" => isset($_REQUEST["group"]) ?
                $_REQUEST["group"]
                :
                "",
            "inline" => true
        );

        $fields[] = array(
            "type" => "select",
            "name" => "status",
            "label" => t("Status:"),
            "value" => array_merge(
                array(t("All") => ""),
                $status_array
            ),
            "selected" => isset($_REQUEST["status"]) ?
                $_REQUEST["status"]
                :
                "",
            "inline" => true
        );

        $fields[] = array(
            "type" => "select",
            "name" => "sort",
            "label" => t("Sorting:"),
            "value" => array(
                t("Username Ascending") => "user_asc",
                t("Username Descending") => "user_desc",
                t("Registration Date Ascending") => "register_asc",
                t("Registration Date Descending") => "register_desc"
            ),
            "selected" => isset($_REQUEST["sort"]) ?
                $_REQUEST["sort"]
                :
                "",
            "inline" => true
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "<div></div>"
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Filter")
        );

        $fieldset[] = array(
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => $_REQUEST["keywords"] == "" &&
                $_REQUEST["group"] == "" &&
                $_REQUEST["status"] == "" &&
                $_REQUEST["sort"] == "" ?
                true
                :
                false
        );

        print Jaris\Forms::generate($parameters, $fieldset);

        $status_captions = array();
        foreach($status_array as $caption => $id)
        {
            $status_captions[$id] = $caption;
        }

        $users_count = Jaris\Sql::countColumn(
            "users",
            "users",
            "username",
            "$group $keywords $status"
        );

        print "<h2>" . t("Total users:") . " " . $users_count . "</h2>";

        $users = Jaris\Sql::getDataList(
            "users",
            "users",
            $page - 1,
            30,
            "$group $keywords $status $sort",
            "username"
        );

        Jaris\System::printNavigation(
            $users_count,
            $page,
            "admin/users/list",
            "",
            30,
            array(
                "group" => $_REQUEST["group"],
                "status" => $_REQUEST["status"],
                "sort" => $_REQUEST["sort"]
            )
        );

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Username") . "</td>";
        print "<td>" . t("E-mail") . "</td>";
        print "<td>" . t("Status") . "</td>";
        print "<td>" . t("Register date") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

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

        print "</tbody>";

        print "</table>";

        Jaris\System::printNavigation(
            $users_count,
            $page,
            "admin/users/list",
            "",
            30,
            array(
                "group" => $_REQUEST["group"],
                "keywords" => $_REQUEST["keywords"],
                "status" => $_REQUEST["status"],
                "sort" => $_REQUEST["sort"]
            )
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
