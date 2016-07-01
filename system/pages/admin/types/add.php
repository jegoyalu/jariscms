<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the types add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Type") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_types", "add_types"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-type")
        )
        {
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["categories"] = $_REQUEST["categories"];
            $fields["uri_scheme"] = $_REQUEST["uri_scheme"];
            $fields["input_format"] = $_REQUEST["input_format"];
            $fields["title_label"] = $_REQUEST["title_label"];
            $fields["title_description"] = $_REQUEST["title_description"];
            $fields["content_label"] = $_REQUEST["content_label"];
            $fields["content_description"] = $_REQUEST["content_description"];

            if(isset($_FILES["image"]))
            {
                $fields["image"] = $_FILES["image"];
            }

            $message = Jaris\Types::add($_REQUEST["machine_name"], $fields);

            if($message == "true")
            {
                Jaris\View::addMessage(t("The type has been successfully created."));
            }
            else
            {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/types");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/types");
        }

        $parameters["name"] = "add-type";
        $parameters["class"] = "add-type";
        $parameters["action"] = Jaris\Uri::url("admin/types/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "value" => empty($_REQUEST["machine_name"]) ?
                "" : $_REQUEST["machine_name"],
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine_name",
            "required" => true,
            "description" => t("A readable machine name, like for example: my-type.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => empty($_REQUEST["name"]) ?
                "" : $_REQUEST["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Type.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => empty($_REQUEST["description"]) ?
                "" : $_REQUEST["description"],
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the type.")
        );

        $fieldset[] = array("fields" => $fields);

        $fields_image[] = array(
            "id" => "image",
            "type" => "file",
            "name" => "image",
            "valid_types" => "gif,jpg,jpeg,png",
            "description" => t("The image displayed by default if content of this type doesn't have one.")
        );

        $fieldset[] = array(
            "name" => t("Image"),
            "fields" => $fields_image
        );

        if(Jaris\Categories::getList())
        {
            $fieldset[] = array(
                "name" => t("Categories"),
                "fields" => Jaris\Types::generateCategoriesFields(),
                "collapsible" => true,
                "description" => t("The categories a user can select for this type of content.")
            );
        }

        $fields_uri_scheme[] = array(
            "type" => "text",
            "name" => "uri_scheme",
            "id" => "uri_scheme",
            "value" => isset($_REQUEST["uri_scheme"]) ?
                $_REQUEST["uri_scheme"] : "{user}/{type}/{title}"
        );

        $fieldset[] = array(
            "name" => t("Uri Scheme"),
            "fields" => $fields_uri_scheme,
            "collapsible" => true,
            "description" => t("The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}")
        );

        $fields_inputformats = array();

        foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
        {
            $fields_inputformats[] = array(
                "type" => "radio",
                "checked" => $machine_name == "full_html" ? true : false,
                "name" => "input_format",
                "description" => $fields_formats["description"],
                "value" => array($fields_formats["title"] => $machine_name)
            );
        }

        $fieldset[] = array(
            "fields" => $fields_inputformats,
            "name" => t("Default Input Format")
        );

        $fields_labels[] = array(
            "type" => "text",
            "label" => t("Title:"),
            "name" => "title_label",
            "id" => "title_label",
            "value" => isset($_REQUEST["title_label"]) ?
                $_REQUEST["title_label"]
                :
                "Title:",
            "description" => t("The label of the input title.")
        );

        $fields_labels[] = array(
            "type" => "textarea",
            "label" => t("Title description:"),
            "name" => "title_description",
            "id" => "title_description",
            "value" => isset($_REQUEST["title_description"]) ?
                $_REQUEST["title_description"]
                :
                "Displayed on the web browser title bar and inside the website.",
            "description" => t("The description of the title.")
        );

        $fields_labels[] = array(
            "type" => "text",
            "label" => t("Content:"),
            "name" => "content_label",
            "id" => "content_label",
            "value" => isset($_REQUEST["content_label"]) ?
                $_REQUEST["content_label"] : "Content:",
            "description" => t("The label of the input content.")
        );

        $fields_labels[] = array(
            "type" => "textarea",
            "label" => t("Content description:"),
            "name" => "content_description",
            "id" => "content_description",
            "value" => isset($_REQUEST["content_description"]) ?
                $_REQUEST["content_description"] : "",
            "description" => t("The description of the content.")
        );

        $fieldset[] = array(
            "name" => t("Labels"),
            "description" => t("To replace original labels of title and content when user is adding or editing content of this type."),
            "fields" => $fields_labels,
            "collapsible" => true
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
