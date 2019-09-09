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
        <?php print t("Register Reunion Attendance") ?>
    field;

    field: content
    <?php
        //Stop unauthorized access
        Jaris\Authentication::protectedPage(
            ["manage_reunions_church_attendance"]
        );

        $event_data = [];

        if (empty($_REQUEST["calendar_uri"]) || empty($_REQUEST["event_id"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        } else {
            $event_data = calendar_event_data(
                $_REQUEST["event_id"],
                $_REQUEST["calendar_uri"]
            );

            if (empty($event_data)) {
                Jaris\View::addMessage(t("Invalid calendar event."));

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/church-attendance/reunions",
                        "church_attendance"
                    )
                );
            }
        }

        $db = Jaris\Sql::open("church_attendance_reunions");

        $result = Jaris\Sql::query(
            "select * from church_attendance_reunions "
            . "where calendar_event_id={$_REQUEST["event_id"]} and "
            . "calendar_uri='{$_REQUEST["calendar_uri"]}'",
            $db
        );

        if ($reunion_data = Jaris\Sql::fetchArray($result)) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions/edit",
                    "church_attendance"
                ),
                [
                    "id" => $reunion_data["id"]
                ]
            );
        }

        Jaris\Sql::close($db);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-reunion")
        ) {
            $data = [
                "day" => $event_data["day"],
                "month" => $event_data["month"],
                "year" => $event_data["year"],
                "title" => $event_data["title"],
                "description" => $event_data["description"],
                "hour" => $event_data["hour"],
                "minute" => $event_data["minute"],
                "is_am" => $event_data["is_am"],
                "calendar_event_id" => $_REQUEST["event_id"],
                "calendar_uri" => $_REQUEST["calendar_uri"],
                "registered_by" => Jaris\Authentication::currentUser()
            ];

            church_attendance_reunion_add($data, $_REQUEST["members"]);

            Jaris\View::addMessage("Reunion attendance successfully added.");

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
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
            . $event_data["day"]
            . "/"
            . $months_list[$event_data["month"]]
            . "/"
            . $event_data["year"]
            . "</div>"
        ;

        $parameters["name"] = "add-reunion";
        $parameters["class"] = "add-reunion";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "calendar_uri",
            "value" => $_REQUEST["calendar_uri"]
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "event_id",
            "value" => $_REQUEST["event_id"]
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "label" => t("Reunion:"),
            "value" => $event_data["title"],
            "readonly" => true
        ];

        $fieldset[] = ["fields" => $fields];

        $db = Jaris\Sql::open("church_attendance_members");

        foreach (church_attendance_group_list() as $group_id => $group_name) {
            $result = Jaris\Sql::query(
                "select * from church_attendance_members "
                . "where group_id = $group_id and (is_member=0 or is_member=1)",
                $db
            );

            $fields = [];

            while ($member_data = Jaris\Sql::fetchArray($result)) {
                $fields[] = [
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
                        . "," . $member_data["last_visit_date"]
                ];
            }

            if (!empty($fields)) {
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

                $field_select = [];

                $field_select[] = [
                    "type" => "other",
                    "html_code" => $select_code
                ];

                $fieldset[] = [
                    "name" => t($group_name),
                    "fields" => array_merge($field_select, $fields),
                    "collapsible" => true,
                    "collapsed" => true
                ];
            }
        }

        Jaris\Sql::close($db);

        $fields[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
