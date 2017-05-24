<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the input format edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Input Format") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_input_formats"));

        if(!isset($_REQUEST["input_format"]))
        {
            Jaris\Uri::go("admin/input-formats");
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-input-format")
        )
        {
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["allowed_tags"] = $_REQUEST["allowed_tags"];
            $fields["allowed_atts"] = $_REQUEST["allowed_atts"];
            $fields["parse_url"] = $_REQUEST["parse_url"];
            $fields["parse_email"] = $_REQUEST["parse_email"];
            $fields["parse_line_breaks"] = $_REQUEST["parse_line_breaks"];

            $error = false;

            if($_REQUEST["name"] == "" || $_REQUEST["description"] == "")
            {
                $error = true;

                Jaris\View::addMessage(
                    t("You need to provide all the fields"),
                    "error"
                );
            }

            if(!$error)
            {
                if(Jaris\InputFormats::edit($_REQUEST["input_format"], $fields))
                {
                    Jaris\View::addMessage(t("Your changes have been saved."));

                    t("Edited input format '{machine_name}'.");

                    Jaris\Logger::info(
                        "Edited input format '{machine_name}'.",
                        array(
                            "machine_name" => $_REQUEST["input_format"]
                        )
                    );
                }
                else
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );
                }

                Jaris\Uri::go("admin/input-formats");
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/input-formats");
        }

        $input_format_data = Jaris\InputFormats::get($_REQUEST["input_format"]);

        $parameters["name"] = "edit-input-format";
        $parameters["class"] = "edit-input-format";
        $parameters["action"] = Jaris\Uri::url("admin/input-formats/edit");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "value" => $_REQUEST["input_format"],
            "name" => "input_format"
        );

        $fields[] = array(
            "type" => "text",
            "readonly" => true,
            "value" => $_REQUEST["input_format"],
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine-name",
            "description" => t("The machine name of the input format.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $input_format_data["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Input Format.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $input_format_data["description"],
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the input format.")
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => $input_format_data["allowed_tags"],
            "name" => "allowed_tags",
            "label" => t("Allowed tags:"),
            "id" => "allowed_tags",
            "description" => t("A list of the allowed tags for this input format. Example: &lt;a&gt;&lt;img&gt;&lt;p&gt;&lt;h1&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;&lt;h5&gt;&lt;h6&gt;&lt;address&gt;&lt;pre&gt;&lt;br&gt;&lt;b&gt;&lt;i&gt;&lt;strong&gt;&lt;em&gt;&lt;u&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;")
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => $input_format_data["allowed_atts"],
            "name" => "allowed_atts",
            "label" => t("Allowed attributes:"),
            "id" => "allowed_atts",
            "description" => t("A list of the allowed attributes separated by comma. Example: href,src,alt,align,target")
        );

        $fieldset[] = array("fields" => $fields);

        $true_false[t("Enable")] = true;
        $true_false[t("Disable")] = false;

        $parse_url_fields[] = array(
            "type" => "radio",
            "name" => "parse_url",
            "id" => "parse_url",
            "value" => $true_false,
            "checked" => $input_format_data["parse_url"]
        );

        $fieldset[] = array(
            "name" => t("Parse url's"),
            "fields" => $parse_url_fields,
            "collapsible" => true,
            "description" => t("To enable or disable parsing of url's.")
        );

        $parse_email_fields[] = array(
            "type" => "radio",
            "name" => "parse_email",
            "id" => "parse_email",
            "value" => $true_false,
            "checked" => $input_format_data["parse_email"]
        );

        $fieldset[] = array(
            "name" => t("Parse emails"),
            "fields" => $parse_email_fields,
            "collapsible" => true,
            "description" => t("To enable or disable parsing of emails.")
        );

        $parse_line_ends_fields[] = array(
            "type" => "radio",
            "name" => "parse_line_breaks",
            "id" => "parse_line_breaks",
            "value" => $true_false,
            "checked" => $input_format_data["parse_line_breaks"]
        );

        $fieldset[] = array(
            "name" => t("Convert line breaks"),
            "fields" => $parse_line_ends_fields,
            "collapsible" => true,
            "description" => t("To enable or disable conversion of line breaks to &lt;br&gt; tag.")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array(
            "fields" => $fields_submit
        );

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
