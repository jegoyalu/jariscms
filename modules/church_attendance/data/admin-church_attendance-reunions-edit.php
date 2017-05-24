<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the control center page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Reunion Attendance") ?>
    field;

    field: content
    <?php
        //Stop unauthorized access
        Jaris\Authentication::protectedPage(
            array("manage_reunions_church_attendance")
        );

        $reunion_data = array();

        if(empty($_REQUEST["id"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        }
        else
        {
            $reunion_data = church_attendance_reunion_get($_REQUEST["id"]);

            if(empty($reunion_data))
            {
                Jaris\View::addMessage(t("Invalid reunion."));

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/church-attendance/reunions",
                        "church_attendance"
                    )
                );
            }
        }

        $db = Jaris\Sql::open("church_attendance_registry");

        $result = Jaris\Sql::query(
            "select * from church_attendance_registry "
            . "where activity_id={$_REQUEST["id"]}",
            $db
        );

        $members_present = array();

        while($registry_data = Jaris\Sql::fetchArray($result))
        {
            $members_present[$registry_data["member_id"]] = true;
        }

        Jaris\Sql::close($db);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-reunion")
        )
        {
            $data = array(
                "day" => $reunion_data["day"],
                "month" => $reunion_data["month"],
                "year" => $reunion_data["year"],
                "title" => $reunion_data["title"],
                "description" => $reunion_data["description"],
                "hour" => $reunion_data["hour"],
                "minute" => $reunion_data["minute"],
                "is_am" => $reunion_data["is_am"],
                "calendar_event_id" => $reunion_data["calendar_event_id"],
                "calendar_uri" => $reunion_data["calendar_uri"],
                "registered_by" => $reunion_data["registered_by"]
            );

            church_attendance_reunion_edit(
                $_REQUEST["id"], $data, $_REQUEST["members"]
            );

            Jaris\View::addMessage("Reunion attendance successfully updated.");

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        }

        $months_list = Jaris\Date::getMonths();
        $months_list = array_flip($months_list);

        print "<div>"
            . "<strong>" . t("Date:") . "</strong> "
            . $reunion_data["day"]
            . "/"
            . $months_list[$reunion_data["month"]]
            . "/"
            . $reunion_data["year"]
            . "</div>"
        ;

        $parameters["name"] = "edit-reunion";
        $parameters["class"] = "edit-reunion";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Reunion:"),
            "value" => $reunion_data["title"],
            "readonly" => true
        );

        $fieldset[] = array("fields" => $fields);

        foreach(church_attendance_group_list() as $group_id => $group_name)
        {
            $db = Jaris\Sql::open("church_attendance_members");

            $result = Jaris\Sql::query(
                "select * from church_attendance_members "
                . "where group_id = $group_id and (is_member=0 or is_member=1)",
                $db
            );

            $fields = array();

            while($member_data = Jaris\Sql::fetchArray($result))
            {
                $fields[] = array(
                    "type" => "checkbox",
                    "name" => "members[]",
                    "label" => $member_data["first_name"]
                        . " "
                        . $member_data["last_name"]
                        . " "
                        . $member_data["maiden_name"],
                    "value" => $member_data["id"]
                        . "," . $member_data["group_id"]
                        . "," . $member_data["gender"]
                        . "," . $member_data["last_visit_date"],
                    "checked" => isset($members_present[$member_data["id"]])
                );
            }

            Jaris\Sql::close($db);

            if(!empty($fields))
            {
                $select_code = '<div style="text-align: right; padding: 4px; border-bottom: solid 1px #d3d3d3; margin-bottom: 10px;">'
                    . t("Select:")
                    . ' '
                    . '<a id="'.$group_name.'-'.$group_id.'-all" style="cursor: pointer">'
                    . t("All")
                    . '</a>'
                    . ' | '
                    . '<a id="'.$group_name.'-'.$group_id.'-none" style="cursor: pointer">'
                    . t("None")
                    . '</a>'
                    . '</div>'
                    . '<script>'
                    . '$(document).ready(function(){'
                    . '$("#'.$group_name.'-'.$group_id.'-all").click(function(){'
                    . '$(this).parent().parent().find("input[type=\'checkbox\']").prop("checked", true);'
                    . '});'
                    . '$("#'.$group_name.'-'.$group_id.'-none").click(function(){'
                    . '$(this).parent().parent().find("input[type=\'checkbox\']").prop("checked", false);'
                    . '});'
                    . '});'
                    . '</script>'
                ;

                $field_select = array();

                $field_select[] = array(
                    "type" => "other",
                    "html_code" => $select_code
                );

                $fieldset[] = array(
                    "name" => t($group_name),
                    "fields" => array_merge($field_select, $fields),
                    "collapsible" => true,
                    "collapsed" => true
                );
            }
        }

        $fields[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
