<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the input format add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Input Format") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_input_formats"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-input-format")
        )
        {
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["allowed_tags"] = $_REQUEST["allowed_tags"];
            $fields["allowed_atts"] = $_REQUEST["allowed_atts"];
            $fields["parse_url"] = $_REQUEST["parse_url"];
            $fields["parse_email"] = $_REQUEST["parse_email"];
            $fields["parse_line_breaks"] = $_REQUEST["parse_line_breaks"];

            $message = Jaris\InputFormats::add($_REQUEST["machine_name"], $fields);

            if($message == "true")
            {
                Jaris\View::addMessage(
                    t("The input format has been successfully created.")
                );
            }
            else
            {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/input-formats");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/input-formats");
        }

        $parameters["name"] = "add-input-format";
        $parameters["class"] = "add-input-format";
        $parameters["action"] = Jaris\Uri::url("admin/input-formats/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["machine_name"]) ?
                $_REQUEST["machine_name"] : "",
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine_name",
            "required" => true,
            "description" => t("A readable machine name, like for example: my-input-format.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["name"]) ?
                $_REQUEST["name"] : "",
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Input Format.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the input format.")
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => isset($_REQUEST["allowed_tags"]) ?
                $_REQUEST["allowed_tags"] : "",
            "name" => "allowed_tags",
            "label" => t("Allowed tags:"),
            "id" => "allowed_tags",
            "description" => t("A list of the allowed tags for this input format. Example: &lt;a&gt;&lt;img&gt;&lt;p&gt;&lt;h1&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;&lt;h5&gt;&lt;h6&gt;&lt;address&gt;&lt;pre&gt;&lt;br&gt;&lt;b&gt;&lt;i&gt;&lt;strong&gt;&lt;em&gt;&lt;u&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;")
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => isset($_REQUEST["allowed_atts"]) ?
                $_REQUEST["allowed_atts"] : "",
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
            "checked" => isset($_REQUEST["parse_url"]) ?
                $_REQUEST["parse_url"] : false
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
            "checked" => isset($_REQUEST["parse_email"]) ?
                $_REQUEST["parse_email"] : false
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
            "checked" => isset($_REQUEST["parse_line_breaks"]) ?
                $_REQUEST["parse_line_breaks"] : false
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

        $fieldset[] = array("fields" => $fields_submit);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
