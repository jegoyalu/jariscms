<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the type edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Type") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_types", "edit_types"));

        if(!isset($_REQUEST["type"]))
        {
            Jaris\Uri::go("admin/types");
        }

        Jaris\View::addTab(t("Types"), "admin/types");

        Jaris\View::addTab(
            t("Uploads"),
            "admin/types/uploads",
            array("type" => $_REQUEST["type"])
        );

        Jaris\View::addTab(
            t("Maximum Posts"),
            "admin/types/posts",
            array("type" => $_REQUEST["type"])
        );

        $type_data = Jaris\Types::get($_REQUEST["type"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-type")
        )
        {
            $fields = $type_data;

            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["categories"] = $_REQUEST["categories"];
            $fields["uri_scheme"] = $_REQUEST["uri_scheme"];
            $fields["input_format"] = $_REQUEST["input_format"];
            $fields["requires_approval"] = $_REQUEST["requires_approval"];
            $fields["title_label"] = $_REQUEST["title_label"];
            $fields["title_description"] = $_REQUEST["title_description"];
            $fields["content_label"] = $_REQUEST["content_label"];
            $fields["content_description"] = $_REQUEST["content_description"];

            if(isset($_FILES["image"]) && trim($_FILES["image"]["name"]) != "")
            {
                $fields["image"] = $_FILES["image"];

                if(isset($type_data["image"]) && trim($type_data["image"]) != "")
                {
                    Jaris\Pages\Images::deleteByName(
                        $type_data["image"], "admin/types/image"
                    );
                }
            }

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
                if(Jaris\Types::edit($_REQUEST["type"], $fields))
                {
                    Jaris\View::addMessage(t("Your changes have been saved."));

                    t("Edited content type '{machine_name}'.");

                    Jaris\Logger::info(
                        "Edited content type '{machine_name}'.",
                        array(
                            "machine_name" => $_REQUEST["type"]
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

                Jaris\Uri::go("admin/types");
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/types");
        }

        $parameters["name"] = "edit-type";
        $parameters["class"] = "edit-type";
        $parameters["action"] = Jaris\Uri::url("admin/types/edit");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "value" => $_REQUEST["type"],
            "name" => "type"
        );

        $fields[] = array(
            "type" => "text",
            "readonly" => true,
            "value" => $_REQUEST["type"],
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine-name",
            "description" => t("The machine name of the type also used on the content template.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $type_data["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Type.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $type_data["description"],
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the type.")
        );

        $fieldset[] = array("fields" => $fields);

        $fields_image = array();

        if(isset($type_data["image"]) && trim($type_data["image"]) != "")
        {
            $image_src = Jaris\Types::getImageUrl($_REQUEST["type"], 100);
            $code = "<div class=\"edit-type-image\">\n";
            $code .= "<img src=\"$image_src\" />\n";
            $code .= "</div>\n";

            $fields_image[] = array(
                "type" => "other",
                "html_code" => $code
            );
        }

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
                "fields" => Jaris\Types::generateCategoriesFields(
                    $type_data["categories"]
                ),
                "collapsible" => true,
                "description" => t("The categories a user can select for this type of content.")
            );
        }

        $fields_uri_scheme[] = array(
            "type" => "text",
            "name" => "uri_scheme",
            "id" => "uri_scheme",
            "value" => $type_data["uri_scheme"] ? $type_data["uri_scheme"] : "{user}/{type}/{title}"
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
                "checked" => $machine_name == $type_data["input_format"] ? true : false,
                "name" => "input_format",
                "description" => $fields_formats["description"],
                "value" => array($fields_formats["title"] => $machine_name)
            );
        }

        $fieldset[] = array(
            "fields" => $fields_inputformats,
            "name" => t("Default Input Format")
        );

        $fields_approval = array();

        foreach(Jaris\Groups::getList() as $group_name => $machine_name)
        {
            if($machine_name == "administrator")
            {
                continue;
            }

            $fields_approval[] = array(
                "type" => "checkbox",
                "label" => t($group_name),
                "checked" => $type_data["requires_approval"][$machine_name] ?
                    true : false,
                "name" => "requires_approval[$machine_name]",
                "description" => t(Jaris\Groups::get($machine_name)["description"])
            );
        }

        $fieldset[] = array(
            "fields" => $fields_approval,
            "name" => t("Moderation"),
            "description" => t("List of groups that require approval. Check all the groups that require approval on the content moderation queue for content to be published.")
        );

        $fields_labels[] = array(
            "type" => "text",
            "label" => t("Title:"),
            "name" => "title_label",
            "id" => "title_label",
            "value" => $type_data["title_label"] ? $type_data["title_label"] : "Title:",
            "description" => t("The label of the input title.")
        );

        $fields_labels[] = array(
            "type" => "textarea",
            "label" => t("Title description:"),
            "name" => "title_description",
            "id" => "title_description",
            "value" => $type_data["title_description"] ?
                $type_data["title_description"]
                :
                "Displayed on the web browser title bar and inside the website.",
            "description" => t("The description of the title.")
        );

        $fields_labels[] = array(
            "type" => "text",
            "label" => t("Content:"),
            "name" => "content_label",
            "id" => "content_label",
            "value" => $type_data["content_label"] ?
                $type_data["content_label"]
                :
                "Content:",
            "description" => t("The label of the input content.")
        );

        $fields_labels[] = array(
            "type" => "textarea",
            "label" => t("Content description:"),
            "name" => "content_description",
            "id" => "content_description",
            "value" => $type_data["content_description"] ?
                $type_data["content_description"]
                :
                "",
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
