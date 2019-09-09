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
        <?php print t("Church Attendance Members by Talent") ?>
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

        $talent = "";
        if (!empty($_REQUEST["talent"])) {
            $talent_val = intval($_REQUEST["talent"]);
            $talent = "talent_id=$talent_val";
        }

        $where = "";

        if ($talent) {
            $where .= "where ";
        }

        if ($talent) {
            $where .= "$talent ";
        }

        $pages_count = Jaris\Sql::countColumn(
            "church_attendance_talents_count",
            "church_attendance_talents_count",
            "member_id",
            $where
        );

        print "<div>";
        print "<h2>"
            . t("Total:") . " " . $pages_count
            . "</h2>"
        ;
        print "</div>";

        $talents = Jaris\Sql::getDataList(
            "church_attendance_talents_count",
            "church_attendance_talents_count",
            $page - 1,
            20,
            $where
        );

        $parameters["class"] = "filter-by-talent";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $talents_list = church_attendance_talent_list();
        $talents_value = [t("All") => ""];

        foreach ($talents_list as $talent_id => $talent_name) {
            $talents_value[t($talent_name)] = $talent_id;
        }

        $fields[] = [
            "type" => "select",
            "name" => "talent",
            "label" => t("Filter by talent:"),
            "value" => $talents_value,
            "selected" => isset($_REQUEST["talent"]) ?
                $_REQUEST["talent"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"'
        ];

        $fieldset[] = [
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["talent"])
        ];

        print Jaris\Forms::generate($parameters, $fieldset);

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members/by-talent",
            "church_attendance",
            20,
            [
                "talent" => $_REQUEST["talent"]
            ]
        );

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("First Name") . "</td>";
        print "<td>" . t("Last Names") . "</td>";
        print "<td>" . t("Talent") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach ($talents as $talent_data) {
            $member_data = church_attendance_member_get(
                $talent_data["member_id"]
            );

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
            print t($talents_list[$talent_data["talent_id"]]);
            print "</td>";

            print "</tr>";
        }
        print "</tbody>";

        print "</table>";


        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members/by-talent",
            "church_attendance",
            20,
            [
                "talent" => $_REQUEST["talent"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
