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
        <?php print t("Add Member or Visitor") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("manage_members_church_attendance")
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-member")
        )
        {
            $data = array(
                "first_name" => $_REQUEST["first_name"],
                "last_name" => $_REQUEST["last_name"],
                "maiden_name" => $_REQUEST["maiden_name"],
                "gender" => $_REQUEST["gender"],
                "birth_day" => $_REQUEST["day"],
                "birth_month" => $_REQUEST["month"],
                "birth_year" => $_REQUEST["year"],
                "is_member" => $_REQUEST["is_member"],
                "taken_discipleship" => $_REQUEST["taken_discipleship"],
                "year_accepted_christ" => $_REQUEST["year_accepted_christ"],
                "baptized" => $_REQUEST["baptized"],
                "family_at_home" => $_REQUEST["family_at_home"],
                "talents" => $_REQUEST["talents"],
                "postal_address" => $_REQUEST["postal_address"],
                "residential_address" => $_REQUEST["residential_address"],
                "email" => $_REQUEST["email"],
                "phone" => $_REQUEST["phone"],
                "mobile_phone" => $_REQUEST["mobile_phone"],
                "group_id" => $_REQUEST["group_id"],
                "notes" => $_REQUEST["notes"]
            );

            church_attendance_member_add($data);

            Jaris\View::addMessage("Member or visitor successfully added.");

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members",
                    "church_attendance"
                )
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members",
                    "church_attendance"
                )
            );
        }

        $parameters["name"] = "add-member";
        $parameters["class"] = "add-member";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "first_name",
            "value" => $_REQUEST["first_name"],
            "label" => t("First name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "last_name",
            "value" => $_REQUEST["last_name"],
            "label" => t("Last name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "maiden_name",
            "value" => $_REQUEST["maiden_name"],
            "label" => t("Maiden name:")
        );

        $fields[] = array(
            "type" => "radio",
            "name" => "gender",
            "value" => array(
                t("Male") => "m",
                t("Female") => "f"
            ),
            "checked" => isset($_REQUEST["gender"]) ?
                $_REQUEST["gender"]
                :
                "m",
            "label" => t("Gender:")
        );

        $fieldset[] = array("fields" => $fields);

        //Birthdate fields
        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "value" => Jaris\Date::getDays(),
            "selected" => $_REQUEST["day"],
            "inline" => true
        );

        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "value" => Jaris\Date::getMonths(),
            "selected" => $_REQUEST["month"],
            "inline" => true
        );

        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "value" => Jaris\Date::getYears(),
            "selected" => $_REQUEST["year"],
            "inline" => true
        );

        $fieldset[] = array(
            "name" => t("Birth date"),
            "fields" => $birth_date_fields
        );

        $fields_other[] = array(
            "type" => "radio",
            "name" => "taken_discipleship",
            "value" => array(
                t("No") => "0",
                t("Yes") => "1"
            ),
            "checked" => isset($_REQUEST["taken_discipleship"]) ?
                $_REQUEST["taken_discipleship"]
                :
                "0",
            "label" => t("Has taken discipleship:")
        );

        $fields_other[] = array(
            "type" => "select",
            "name" => "year_accepted_christ",
            "value" => array(t("Not Yet")=>1) + Jaris\Date::getYears(),
            "selected" => $_REQUEST["year_accepted_christ"],
            "label" => t("Year that accepted Jesus Christ:")
        );

        $fields_other[] = array(
            "type" => "radio",
            "name" => "baptized",
            "value" => array(
                t("No") => "0",
                t("Yes") => "1"
            ),
            "checked" => isset($_REQUEST["baptized"]) ?
                $_REQUEST["baptized"]
                :
                "0",
            "label" => t("Has been baptized:")
        );

        $fields_other[] = array(
            "type" => "select",
            "name" => "family_at_home",
            "value" => Jaris\Date::getDays(),
            "selected" => $_REQUEST["family_at_home"],
            "label" => t("Amount of family members:")
        );

        $fields_other[] = array(
            "type" => "radio",
            "name" => "is_member",
            "value" => array(
                t("Visitor") => "0",
                t("Member") => "1",
                t("Inactive") => "2"
            ),
            "checked" => isset($_REQUEST["is_member"]) ?
                $_REQUEST["is_member"]
                :
                "1",
            "label" => t("Member status:")
        );

        $fields_other[] = array(
            "type" => "textarea",
            "name" => "postal_address",
            "value" => $_REQUEST["postal_address"],
            "label" => t("Postal address:")
        );

        $fields_other[] = array(
            "type" => "textarea",
            "name" => "residential_address",
            "value" => $_REQUEST["residential_address"],
            "label" => t("Residential address:")
        );

        $fields_other[] = array(
            "type" => "text",
            "name" => "email",
            "value" => $_REQUEST["email"],
            "label" => t("E-mail:")
        );

        $fields_other[] = array(
            "type" => "text",
            "name" => "phone",
            "value" => $_REQUEST["phone"],
            "label" => t("Phone:")
        );

        $fields_other[] = array(
            "type" => "text",
            "name" => "mobile_phone",
            "value" => $_REQUEST["mobile_phone"],
            "label" => t("Mobile phone:")
        );

        $talents = array();

        foreach(church_attendance_talent_list() as $talent_id=>$talent_name)
        {
            $talents[t($talent_name)] = $talent_id;
        }

        $fields_other[] = array(
            "type" => "checkbox",
            "name" => "talents",
            "value" => $talents,
            "checked" => $_REQUEST["talents"],
            "label" => t("Talents:")
        );

        $groups = array();

        foreach(church_attendance_group_list() as $group_id=>$group_name)
        {
            $groups[t($group_name)] = $group_id;
        }

        $fields_other[] = array(
            "type" => "select",
            "name" => "group_id",
            "value" => $groups,
            "selected" => $_REQUEST["group_id"],
            "label" => t("Group:")
        );

        $fields_other[] = array(
            "type" => "textarea",
            "name" => "notes",
            "value" => $_REQUEST["notes"],
            "label" => t("Notes:")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
