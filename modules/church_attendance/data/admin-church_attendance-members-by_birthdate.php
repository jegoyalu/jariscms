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
        <?php print t("Church Attendance Members by Birthdate") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("manage_members_church_attendance")
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

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $month = "";
        if(empty($_REQUEST["month"]))
        {
            $_REQUEST["month"] = date("n", time());
        }
        $month .= "birth_month=" . intval($_REQUEST["month"]);

        $where = "where $month";

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

        $parameters["class"] = "filter-by-month";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields[] = array(
            "type" => "select",
            "name" => "month",
            "label" => t("Filter by month:"),
            "value" => array_merge(
                array(t("Current") => ""),
                Jaris\Date::getMonths()
            ),
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"'
        );

        $fieldset[] = array(
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["month"])
        );

        print Jaris\Forms::generate($parameters, $fieldset);

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members/by-birthdate",
            "church_attendance",
            20,
            array(
                "month" => $_REQUEST["month"]
            )
        );

        $months_list = Jaris\Date::getMonths();
        $months_list = array_flip($months_list);

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("First Name") . "</td>";
        print "<td>" . t("Last Names") . "</td>";
        print "<td>" . t("Birthdate") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach($members as $member_data)
        {
            print "<tr>";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members/edit",
                    "church_attendance"
                ),
                array("id"=>$member_data["id"])
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
            print $member_data["birth_day"]
                . "/"
                . $months_list[$member_data["birth_month"]]
                . "/"
                . $member_data["birth_year"]
            ;
            print "</td>";

            print "</tr>";
        }
        print "</tbody>";

        print "</table>";


        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/members/by-birthdate",
            "church_attendance",
            20,
            array(
                "month" => $_REQUEST["month"]
            )
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
