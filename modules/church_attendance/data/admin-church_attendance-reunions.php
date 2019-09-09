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
        <?php print t("Church Reunions Attendance Registry") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_reunions_church_attendance"]
        );

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = intval($_REQUEST["page"]);
        }

        $month = "";
        if (!empty($_REQUEST["month"])) {
            $month .= "month=" . intval($_REQUEST["month"]);
        }

        $year = "";
        if (!empty($_REQUEST["year"])) {
            $year .= "year=" . intval($_REQUEST["year"]);
        }

        $sorting = "order by activity_date desc";
        if (!empty($_REQUEST["sort"])) {
            switch ($_REQUEST["sort"]) {
                case "da":
                    $sorting = "order by activity_date asc";
                    break;
                case "dd":
                    $sorting = "order by activity_date desc";
                    break;
                case "aa":
                    $sorting = "order by attendance_count asc";
                    break;
                case "ad":
                    $sorting = "order by attendance_count desc";
                    break;
                default:
                    $sorting = "order by activity_date desc";
            }
        }

        $where = "";

        if ($month || $year) {
            $where .= "where ";
        }

        if ($month) {
            $where .= "$month ";
        }

        if ($year) {
            if ($where == "where ") {
                $where .= "$year ";
            } else {
                $where .= "and $year ";
            }
        }

        if ($sorting) {
            $where .= "$sorting ";
        }

        $pages_count = Jaris\Sql::countColumn(
            "church_attendance_reunions",
            "church_attendance_reunions",
            "id",
            $where
        );

        print "<div>";
        print "<h2>"
            . t("Total:") . " " . $pages_count
            . "</h2>"
        ;
        print "</div>";

        $reunions = Jaris\Sql::getDataList(
            "church_attendance_reunions",
            "church_attendance_reunions",
            $page - 1,
            20,
            $where
        );

        $parameters["class"] = "filter-by-reunion";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields[] = [
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "value" => array_merge(
                [t("All") => ""],
                Jaris\Date::getMonths()
            ),
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "value" => [t("All") => ""] + Jaris\Date::getYears(),
            "selected" => isset($_REQUEST["year"]) ?
                $_REQUEST["year"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "sort",
            "label" => t("Sort by:"),
            "value" => [
                t("Date Descending") => "da",
                t("Date Ascending") => "dd",
                t("Attendance Descending") => "aa",
                t("Attendance Ascending") => "ad"
            ],
            "selected" => isset($_REQUEST["sort"]) ?
                $_REQUEST["sort"]
                :
                "da",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fieldset[] = [
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["month"])
                && !isset($_REQUEST["year"])
                && !isset($_REQUEST["sort"])
        ];

        print Jaris\Forms::generate($parameters, $fieldset);

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-attendance/reunions",
            "church_attendance",
            20,
            [
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "sort" => $_REQUEST["sort"]
            ]
        );

        $members_count = Jaris\Sql::countColumn(
            "church_attendance_members",
            "church_attendance_members",
            "id"
        );

        $months_list = Jaris\Date::getMonths();
        $months_list = array_flip($months_list);

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Attendance") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach ($reunions as $reunion_data) {
            print "<tr>";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions/edit",
                    "church_attendance"
                ),
                ["id"=>$reunion_data["id"]]
            );

            print "<td>"
                . "<a href=\"$edit_url\">"
                . $reunion_data["day"]
                . "/"
                . $months_list[$reunion_data["month"]]
                . "/"
                . $reunion_data["year"]
                . "</a> "
                . "</td>"
            ;

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions/edit",
                    "church_attendance"
                ),
                ["id"=>$reunion_data["id"]]
            );

            print "<td>"
                . "<a href=\"$edit_url\">"
                . $reunion_data["title"]
                . "</a> "
                . "</td>"
            ;

            print "<td>"
                . $reunion_data["attendance_count"]
                . " / "
                . $members_count
                . "</td>"
            ;

            $delete_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions/delete",
                    "church_attendance"
                ),
                ["id"=>$reunion_data["id"]]
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
            "admin/church-attendance/reunions",
            "church_attendance",
            20,
            [
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "sort" => $_REQUEST["sort"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
