<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content types field edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Content Type Field") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_types_fields", "edit_types_fields"]
        );

        if (!isset($_REQUEST["id"]) || !isset($_REQUEST["type_name"])) {
            Jaris\Uri::go("admin/types");
        }

        $field_id = intval($_REQUEST["id"]);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-type-fields")
        ) {
            $fields = Jaris\Fields::get($field_id, $_REQUEST["type_name"]);

            $fields["variable_name"] = $_REQUEST["variable_name"];
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["type"] = $_REQUEST["type"];
            $fields["readonly"] = $_REQUEST["readonly"];
            $fields["required"] = $_REQUEST["required"];
            $fields["default"] = $_REQUEST["default"];
            $fields["width"] = $_REQUEST["width"];
            $fields["image_multiple"] = $_REQUEST["image_multiple"];
            $fields["image_max"] = $_REQUEST["image_max"];
            $fields["image_description"] = $_REQUEST["image_description"];
            $fields["extensions"] = $_REQUEST["extensions"];
            $fields["size"] = intval($_REQUEST["size"]);
            $fields["file_multiple"] = $_REQUEST["file_multiple"];
            $fields["file_max"] = $_REQUEST["file_max"];
            $fields["file_description"] = $_REQUEST["file_description"];
            $fields["lat_name"] = $_REQUEST["lat_name"];
            $fields["lat_value"] = $_REQUEST["lat_value"];
            $fields["lng_name"] = $_REQUEST["lng_name"];
            $fields["lng_value"] = $_REQUEST["lng_value"];
            $fields["map_zoom"] = intval($_REQUEST["map_zoom"]);
            $fields["values"] = $_REQUEST["values"];
            $fields["captions"] = $_REQUEST["captions"];
            $fields["limit"] = $_REQUEST["limit"];
            $fields["strip_html"] = $_REQUEST["strip_html"];

            if (is_array($_REQUEST["groups"])) {
                $fields["groups"] = $_REQUEST["groups"];
            } else {
                $fields["groups"] = [];
            }

            if (Jaris\Fields::edit($field_id, $fields, $_REQUEST["type_name"])) {
                Jaris\View::addMessage(
                    t("The content type field has been successfully modified.")
                );

                t("Edited field '{name}' on content type '{machine_name}'.");

                Jaris\Logger::info(
                    "Edited field '{name}' on content type '{machine_name}'.",
                    [
                        "name" => $fields["name"],
                        "machine_name" => $_REQUEST["type_name"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/types/fields",
                ["type" => $_REQUEST["type_name"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                "admin/types/fields",
                ["type" => $_REQUEST["type_name"]]
            );
        }

        $field_data = Jaris\Fields::get(
            $field_id,
            $_REQUEST["type_name"]
        );

        $parameters["name"] = "edit-type-fields";
        $parameters["class"] = "edit-type-fields";
        $parameters["action"] = Jaris\Uri::url("admin/types/fields/edit");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "type_name",
            "value" => $_REQUEST["type_name"]
        ];

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["variable_name"]) ?
                $_REQUEST["variable_name"]
                :
                $field_data["variable_name"],
            "name" => "variable_name",
            "label" => t("Variable name:"),
            "id" => "variable_name",
            "required" => true,
            "description" => t("The name of the variable used for this field when generating the form code.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["name"]) ?
                $_REQUEST["name"]
                :
                $field_data["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name displayed when the form is generated.")
        ];

        $fields[] = [
            "type" => "textarea",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"]
                :
                $field_data["description"],
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of how the user should fill this field or it's purpose.")
        ];

        $types[t("Check box")] = "checkbox";
        $types[t("Color selector")] = "color";
        $types[t("Date picker")] = "date";
        $types[t("File upload")] = "file";
        $types[t("Image upload")] = "image";
        $types[t("Hidden")] = "hidden";
        $types[t("Other")] = "other";
        $types[t("Password")] = "password";
        $types[t("Radio box")] = "radio";
        $types[t("Select")] = "select";
        $types[t("Text")] = "text";
        $types[t("Text area")] = "textarea";
        $types[t("Uri")] = "uri";
        $types[t("Uri area")] = "uriarea";
        $types[t("Google Map Location")] = "gmap-location";

        $fields[] = [
            "type" => "select",
            "value" => $types,
            "selected" => isset($_REQUEST["type"]) ?
                $_REQUEST["type"]
                :
                $field_data["type"],
            "name" => "type",
            "label" => t("Type:"),
            "id" => "type",
            "description" => t("The type of the form field.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["limit"]) ?
                $_REQUEST["limit"]
                :
                $field_data["limit"],
            "name" => "limit",
            "label" => t("Input limit:"),
            "id" => "limit",
            "description" => t("The maximun amount of character the user can insert if this is a text or textarea field. 0 for unlimited.")
        ];

        $fields[] = [
            "type" => "textarea",
            "value" => isset($_REQUEST["default"]) ?
                $_REQUEST["default"]
                :
                $field_data["default"],
            "name" => "default",
            "label" => t("Default value:"),
            "id" => "default",
            "description" => t("The default value for a text, textarea, password, hidden, other or a list like select, radio and checkbox.")
        ];

        $fieldset[] = ["fields" => $fields];

        $multiple_upload[t("Enable")] = true;
        $multiple_upload[t("Disable")] = false;

        $fields_image[] = [
            "type" => "text",
            "value" => isset($_REQUEST["width"]) ?
                $_REQUEST["width"]
                :
                $field_data["width"],
            "name" => "width",
            "label" => t("Image width:"),
            "id" => "width",
            "description" => t("Maximum width of the image in pixels in case this field is an image upload. 0 for unlimited.")
        ];

        $fields_image[] = [
            "type" => "radio",
            "label" => t("Multiple upload?"),
            "name" => "image_multiple",
            "id" => "image_multiple",
            "value" => $multiple_upload,
            "checked" => isset($_REQUEST["image_multiple"]) ?
                $_REQUEST["image_multiple"]
                :
                $field_data["image_multiple"],
            "description" => t("Enable or disable multiple image uploads.")
        ];

        $fields_image[] = [
            "type" => "text",
            "value" => isset($_REQUEST["image_max"]) ?
                $_REQUEST["image_max"]
                :
                $field_data["image_max"],
            "name" => "image_max",
            "label" => t("Maximum images:"),
            "id" => "image_max",
            "description" => t("The maximum amount of allowed images to upload if multiple is enabled, 0 for unlimited.")
        ];

        $fields_image[] = [
            "type" => "radio",
            "value" => isset($_REQUEST["image_description"]) ?
                $_REQUEST["image_description"] : "",
            "name" => "image_description",
            "label" => t("Description field:"),
            "value" => $multiple_upload,
            "checked" => isset($field_data["image_description"]) ?
                $field_data["image_description"] : false,
            "description" => t("Allows entering a description for upload.")
        ];

        $fieldset[] = [
            "fields" => $fields_image,
            "name" => t("Image upload"),
            "description" => t("Options used in case the type selected is a image upload."),
            "collapsible" => true,
            "collapsed" => true
        ];

        $fields_file[] = [
            "type" => "textarea",
            "value" => isset($_REQUEST["extensions"]) ?
                $_REQUEST["extensions"]
                :
                $field_data["extensions"],
            "name" => "extensions",
            "label" => t("File extensions:"),
            "id" => "extensions",
            "description" => t("A comma (,) seperated list of extensions allowed for upload in case of file upload. For example: txt, doc, pdf")
        ];

        $fields_file[] = [
            "type" => "text",
            "value" => isset($_REQUEST["size"]) ?
                $_REQUEST["size"]
                :
                $field_data["size"],
            "name" => "size",
            "label" => t("File size:"),
            "id" => "size",
            "description" => t("The maximum permitted file size in kilobytes. For example: 100k") .
                " " . t("The maximum file upload size allowed by this server is:") .
                " " . ini_get("upload_max_filesize")
        ];

        $fields_file[] = [
            "type" => "radio",
            "label" => t("Multiple upload?"),
            "name" => "file_multiple",
            "id" => "file_multiple",
            "value" => $multiple_upload,
            "checked" => isset($_REQUEST["file_multiple"]) ?
                $_REQUEST["file_multiple"]
                :
                $field_data["file_multiple"],
            "description" => t("Enable or disable multiple file uploads.")
        ];

        $fields_file[] = [
            "type" => "text",
            "value" => isset($_REQUEST["file_max"]) ?
                $_REQUEST["file_max"]
                :
                $field_data["file_max"],
            "name" => "file_max",
            "label" => t("Maximum files:"),
            "id" => "file_max",
            "description" => t("The maximum amount of allowed files to upload if multiple is enabled, 0 for unlimited.")
        ];

        $fields_file[] = [
            "type" => "radio",
            "value" => isset($_REQUEST["file_description"]) ?
                $_REQUEST["file_description"] : "",
            "name" => "file_description",
            "label" => t("Description field:"),
            "value" => $multiple_upload,
            "checked" => isset($_REQUEST["file_description"]) ?
                $field_data : false,
            "description" => t("Allows entering a description for upload.")
        ];

        $fieldset[] = [
            "fields" => $fields_file,
            "name" => t("File upload"),
            "description" => t("Options used in case the type selected is a file upload."),
            "collapsible" => true,
            "collapsed" => true
        ];

        $fields_gmap[] = [
            "type" => "text",
            "value" => isset($_REQUEST["lat_name"]) ?
                $_REQUEST["lat_name"]
                :
                $field_data["lat_name"],
            "name" => "lat_name",
            "label" => t("Latitude name:"),
            "id" => "lat_name",
            "description" => t("Name of the latitude field.")
        ];

        $fields_gmap[] = [
            "type" => "text",
            "value" => isset($_REQUEST["lat_value"]) ?
                $_REQUEST["lat_value"]
                :
                $field_data["lat_value"],
            "name" => "lat_value",
            "label" => t("Latitude value:"),
            "id" => "lat_value",
            "description" => t("Default value for the latitude.")
        ];

        $fields_gmap[] = [
            "type" => "text",
            "value" => isset($_REQUEST["lng_name"]) ?
                $_REQUEST["lng_name"]
                :
                $field_data["lng_name"],
            "name" => "lng_name",
            "label" => t("Longitude name:"),
            "id" => "lng_name",
            "description" => t("Name of the longitude field.")
        ];

        $fields_gmap[] = [
            "type" => "text",
            "value" => isset($_REQUEST["lng_value"]) ?
                $_REQUEST["lng_value"]
                :
                $field_data["lng_value"],
            "name" => "lng_value",
            "label" => t("Longitude value:"),
            "id" => "lng_value",
            "description" => t("Default value for the longitude.")
        ];

        $fields_gmap[] = [
            "type" => "text",
            "value" => isset($_REQUEST["map_zoom"]) ?
                intval($_REQUEST["map_zoom"])
                :
                $field_data["map_zoom"],
            "name" => "map_zoom",
            "label" => t("Zoom:"),
            "id" => "map_zoom",
            "description" => t("Initial amount of zoom for the map control.")
        ];

        $fieldset[] = [
            "fields" => $fields_gmap,
            "name" => t("Google Map Location"),
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("Options used in case the type selected is a google map location.")
        ];

        $fields_options[] = [
            "type" => "checkbox",
            "checked" => isset($_REQUEST["readonly"]) ?
                $_REQUEST["readonly"]
                :
                $field_data["readonly"],
            "name" => "readonly",
            "label" => t("Read only:"),
            "id" => "readonly",
            "description" => t("In case the field should be readonly.")
        ];

        $fields_options[] = [
            "type" => "checkbox",
            "checked" => isset($_REQUEST["required"]) ?
                $_REQUEST["required"]
                :
                $field_data["required"],
            "name" => "required",
            "label" => t("Required:"),
            "id" => "required",
            "description" => t("In case the field should be required.")
        ];

        $fields_options[] = [
            "type" => "checkbox",
            "checked" => isset($_REQUEST["strip_html"]) ?
                $_REQUEST["strip_html"]
                :
                $field_data["strip_html"],
            "name" => "strip_html",
            "label" => t("Strip html:"),
            "id" => "strip_html",
            "description" => t("To enable stripping of any html tags.")
        ];

        $fieldset[] = [
            "fields" => $fields_options,
            "name" => t("Field options"),
            "description" => t("Special options for the field.")
        ];

        $fields_select[] = [
            "type" => "textarea",
            "value" => isset($_REQUEST["values"]) ?
                $_REQUEST["values"]
                :
                $field_data["values"],
            "name" => "values",
            "label" => t("Values:"),
            "id" => "valuess",
            "description" => t("A list of values seperated by comma for select, radio and checkbox.")
        ];

        $fields_select[] = [
            "type" => "textarea",
            "value" => isset($_REQUEST["captions"]) ?
                $_REQUEST["captions"]
                :
                $field_data["captions"],
            "name" => "captions",
            "label" => t("Captions:"),
            "id" => "captions",
            "description" => t("A list of captions seperated by comma in the same order entered in values in case it is a radio, checkbox or select.")
        ];

        $fieldset[] = [
            "fields" => $fields_select,
            "name" => t("Multiple options"),
            "description" => t("Options used in case the type selected is a select, radio or checkbox.")
        ];

        $fields_groups_access = Jaris\Groups::generateFields(
            isset($_REQUEST["groups"]) && is_array($_REQUEST["groups"]) ?
                $_REQUEST["groups"]
                :
                $field_data["groups"]
        );

        $fieldset[] = [
            "fields" => $fields_groups_access,
            "name" => t("Groups Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see this field. Don't select anything to display the field to everyone.")
        ];

        $fields_buttons[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_buttons[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_buttons];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
