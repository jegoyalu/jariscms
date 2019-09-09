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
        <?php print t("Church Attendance Members by Course") ?>
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

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $course = "";
        $course_val = "0";
        if(!empty($_REQUEST["course"]))
        {
            $course_val = intval($_REQUEST["course"]);
            $course = "course_id=$course_val";
        }

        $where = "";

        if($course)
        {
            $where .= "where ";
        }

        if($course)
        {
            $where .= "$course ";
        }

        $course_taken = true;
        if(!empty($_REQUEST["taken"]))
        {
            if($_REQUEST["taken"] == "n")
            {
                $course_taken = false;
            }
        }

        $parameters["class"] = "filter-by-course";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $courses_list = church_attendance_courses_list();
        $courses_value = array(t("All") => "");

        foreach($courses_list as $course_id => $course_name)
        {
            $courses_value[t($course_name)] = $course_id;
        }

        $fields[] = array(
            "type" => "select",
            "name" => "course",
            "label" => t("Filter by course:"),
            "value" => $courses_value,
            "selected" => isset($_REQUEST["course"]) ?
                $_REQUEST["course"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        );

        $fields[] = array(
            "type" => "select",
            "name" => "taken",
            "label" => t("Has taken course?"),
            "value" => arraY(
                t("Yes") => "y",
                t("No") => "n"
            ),
            "selected" => isset($_REQUEST["taken"]) ?
                $_REQUEST["taken"]
                :
                "y",
            "code" => 'style="min-width: 150px;" onchange="javascript: this.form.submit()"',
            "inline" => true
        );

        $fieldset[] = array(
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["course"])
        );

        print Jaris\Forms::generate($parameters, $fieldset);

        $results_count = 0;

        if($course_taken)
        {
            $results_count = Jaris\Sql::countColumn(
                "church_attendance_courses_count",
                "church_attendance_courses_count",
                "member_id",
                $where
            );
        }
        elseif($course_val == "0")
        {
            $results_count = 0;
        }
        else
        {
            $results_count = Jaris\Sql::countColumn(
                "church_attendance_members",
                "church_attendance_members",
                "id",
                "where courses not like '%\"$course_val\"%'"
            );
        }

        print "<div>";
        print "<h2>"
            . t("Total:") . " " . $results_count
            . "</h2>"
        ;
        print "</div>";

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("First Name") . "</td>";
        print "<td>" . t("Last Names") . "</td>";
        print "<td>" . t("Course") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        if($course_taken)
        {
            $courses = Jaris\Sql::getDataList(
                "church_attendance_courses_count",
                "church_attendance_courses_count",
                $page - 1,
                20,
                $where
            );

            foreach($courses as $course_data)
            {
                $member_data = church_attendance_member_get(
                    $course_data["member_id"]
                );

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
                print t($courses_list[$course_data["course_id"]]);
                print "</td>";

                print "</tr>";
            }
        }
        elseif($course_val == "0")
        {
            Jaris\View::addMessage(
                t("Please select a course in order to be able to check which members haven't taken it."),
                "error"
            );
        }
        else
        {
            $members_db = Jaris\Sql::open("church_attendance_members");

            $item_start = 0;
            if($page > 1)
            {
                $item_start = (($page-1)*20);
            }

            $results = Jaris\Sql::query(
                "select * from church_attendance_members "
                . "where courses not like '%\"$course_val\"%' "
                . "limit $item_start,20",
                $members_db
            );

            while($member_data = Jaris\Sql::fetchArray($results))
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
                print t($courses_list[$course_val]);
                print "</td>";

                print "</tr>";
            }
        }

        print "</tbody>";

        print "</table>";


        Jaris\System::printNavigation(
            $results_count,
            $page,
            "admin/church-attendance/members/by-course",
            "church_attendance",
            20,
            array(
                "course" => $_REQUEST["course"],
                "taken" => $_REQUEST["taken"]
            )
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
