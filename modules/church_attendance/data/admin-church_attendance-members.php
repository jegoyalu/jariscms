<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Church Attendance Members") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_members_church_attendance"]
        );

        Jaris\View::addTab(
            t("Add Member"),
            Jaris\Modules::getPageUri(
                "admin/church-attendance/members/add",
                "church_attendance"
            )
        );

        Jaris\View::addTab(
            t("Regular Member View"),
            Jaris\Modules::getPageUri(
                "admin/church-attendance/members",
                "church_attendance"
            )
        );

        Jaris\View::addTab(
            t("Courses Member View"),
            Jaris\Modules::getPageUri(
                "admin/church-attendance/members/by-course",
                "church_attendance"
            )
        );

        Jaris\View::addTab(
            t("Talents Member View"),
            Jaris\Modules::getPageUri(
                "admin/church-attendance/members/by-talent",
                "church_attendance"
            )
        );

        Jaris\View::addTab(
            t("Birthdate Member View"),
            Jaris\Modules::getPageUri(
                "admin/church-attendance/members/by-birthdate",
                "church_attendance"
            )
        );

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        $is_member = "(is_member=0 or is_member=1)";
        if (!empty($_REQUEST["m"]) || "".$_REQUEST["m"]."" == "0") {
            switch ($_REQUEST["m"]) {
                case "1":
                    $is_member = "is_member=1";
                    break;
                case "2":
                    $is_member = "is_member=2";
                    break;
                default:
                    $is_member = "is_member=0";
            }
        }

        $group = "";
        if (!empty($_REQUEST["g"])) {
            $group_val = intval($_REQUEST["g"]);
            $group = "group_id=$group_val";
        }

        $discipleship = "";
        if (
            !empty($_REQUEST["d"])
            ||
            "".$_REQUEST["d"]."" == "0"
        ) {
            $discipleship_val = intval($_REQUEST["d"]);
            $discipleship = "taken_discipleship=$discipleship_val";
        }

        $accepted = "";
        if (
            !empty($_REQUEST["a"])
            ||
            "".$_REQUEST["a"]."" == "0"
        ) {
            if ($_REQUEST["a"]) {
                $accepted = "year_accepted_christ>1";
            } else {
                $accepted = "year_accepted_christ=1";
            }
        }

        $baptized = "";
        if (
            !empty($_REQUEST["b"])
            ||
            "".$_REQUEST["b"]."" == "0"
        ) {
            $baptized_val = intval($_REQUEST["b"]);
            $baptized = "baptized=$baptized_val";
        }

        $sorting = "order by last_visit_date asc";
        if (!empty($_REQUEST["s"])) {
            switch ($_REQUEST["s"]) {
                case "la":
                    $sorting = "order by last_visit_date asc";
                    break;
                case "na":
                    $sorting = "order by first_name asc";
                    break;
                case "nd":
                    $sorting = "order by first_name desc";
                    break;
                default:
                    $sorting = "order by last_visit_date desc";
            }
        }

        $where = "";

        if ($is_member || $group || $discipleship || $accepted || $baptized) {
            $where .= "where ";
        }

        if ($is_member) {
            $where .= "$is_member ";
        }

        if ($group) {
            if ($where == "where ") {
                $where .= "$group ";
            } else {
                $where .= "and $group ";
            }
        }

        if ($discipleship) {
            if ($where == "where ") {
                $where .= "$discipleship ";
            } else {
                $where .= "and $discipleship ";
            }
        }

        if ($accepted) {
            if ($where == "where ") {
                $where .= "$accepted ";
            } else {
                $where .= "and $accepted ";
            }
        }

        if ($baptized) {
            if ($where == "where ") {
                $where .= "$baptized ";
            } else {
                $where .= "and $baptized ";
            }
        }

        if ($sorting) {
            $where .= "$sorting ";
        }

        $pages_count = Jaris\Sql::countColumn(
            "church_attendance_members",
            "church_attendance_members",
            "id",
            $where
        );

        print "<div>";
        print "<h2>"
            . t("Total:") . " " . $pages_count
            . "</h2>"
        ;
        print "</div>";

        $members = Jaris\Sql::getDataList(
            "church_attendance_members",
            "church_attendance_members",
            $page - 1,
            20,
            $where
        );

        $parameters["class"] = "filter-by-member";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields[] = [
            "type" => "select",
            "name" => "m",
            "label" => t("Member status:"),
            "value" => [
                t("All") => "",
                t("Visitors") => "0",
                t("Members") => "1",
                t("Inactive") => "2"
            ],
            "selected" => isset($_REQUEST["m"]) ?
                $_REQUEST["m"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $groups_list = [t("All") => ""];
        foreach (church_attendance_group_list() as $group_id => $group_name) {
            $groups_list[t($group_name)] = $group_id;
        }

        $fields[] = [
            "type" => "select",
            "name" => "g",
            "label" => t("Group:"),
            "value" => $groups_list,
            "selected" => isset($_REQUEST["g"]) ?
                $_REQUEST["g"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "d",
            "label" => t("Discipleship:"),
            "value" => [
                t("All") => "",
                t("No") => "0",
                t("Yes") => "1"
            ],
            "selected" => isset($_REQUEST["d"]) ?
                $_REQUEST["d"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "a",
            "label" => t("Accepted Christ:"),
            "value" => [
                t("All") => "",
                t("No") => "0",
                t("Yes") => "1"
            ],
            "selected" => isset($_REQUEST["a"]) ?
                $_REQUEST["a"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "b",
            "label" => t("Baptized:"),
            "value" => [
                t("All") => "",
                t("No") => "0",
                t("Yes") => "1"
            ],
            "selected" => isset($_REQUEST["b"]) ?
                $_REQUEST["b"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "s",
            "label" => t("Sort by:"),
            "value" => [
                t("Recent Visit Last") => "la",
                t("Recent Visit First") => "ld",
                t("Name Ascending") => "na",
                t("Name Descending") => "nd"
            ],
            "selected" => isset($_REQUEST["s"]) ?
                $_REQUEST["s"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fieldset[] = [
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["m"])
                && !isset($_REQUEST["g"])
                && !isset($_REQUEST["d"])
                && !isset($_REQUEST["a"])
                && !isset($_REQUEST["b"])
                && !isset($_REQUEST["s"])
        ];

        print Jaris\Forms::generate($parameters, $fieldset);

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members",
            "church_attendance",
            20,
            [
                "m" => $_REQUEST["m"],
                "g" => $_REQUEST["g"],
                "s" => $_REQUEST["s"],
                "d" => $_REQUEST["d"],
                "a" => $_REQUEST["a"]
            ]
        );

        $months_list = Jaris\Date::getMonths();
        $months_list = array_flip($months_list);

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("First Name") . "</td>";
        print "<td>" . t("Last Names") . "</td>";
        print "<td>" . t("Last Visit") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach ($members as $member_data) {
            print "<tr>";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members/edit",
                    "church_attendance"
                ),
                ["id"=>$member_data["id"]]
            );

            print "<td>"
                . "<a href=\"$edit_url\">"
                . $member_data["first_name"]
                . "</a> "
                . "</td>"
            ;

            print "<td>"
                . $member_data["last_name"]
                . " "
                . $member_data["maiden_name"]
                . "</td>"
            ;

            print "<td>";
            if (is_numeric($member_data["last_visit_date"])) {
                print date("j", $member_data["last_visit_date"])
                    . "/"
                    . $months_list[date("n", $member_data["last_visit_date"])]
                    . "/"
                    . date("Y", $member_data["last_visit_date"])
                ;
            }
            print "</td>";

            $delete_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members/delete",
                    "church_attendance"
                ),
                ["id"=>$member_data["id"]]
            );

            print "<td>"
                . "<a href=\"$delete_url\">" . t("Delete") . "</a>"
                . "</td>"
            ;

            print "</tr>";
        }
        print "</tbody>";

        print "</table>";


        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members",
            "church_attendance",
            20,
            [
                "m" => $_REQUEST["m"],
                "g" => $_REQUEST["g"],
                "s" => $_REQUEST["s"],
                "d" => $_REQUEST["d"],
                "a" => $_REQUEST["a"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
