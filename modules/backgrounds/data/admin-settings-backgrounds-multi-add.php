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
        <?php print t("Add Multiple Background"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("backgrounds-multi-add"))
        {
            $fields = array();

            if($_FILES["images"]["type"][0] == "image/png" ||
                $_FILES["images"]["type"][0] == "image/jpeg" ||
                $_FILES["images"]["type"][0] == "image/pjpeg" ||
                $_FILES["images"]["type"][0] == "image/gif"
            )
            {
                foreach($_FILES["images"]["name"] as $file_index => $file_name)
                {
                    if($_FILES["images"]["type"][$file_index] == "image/png" ||
                        $_FILES["images"]["type"][$file_index] == "image/jpeg" ||
                        $_FILES["images"]["type"][$file_index] == "image/pjpeg" ||
                        $_FILES["images"]["type"][$file_index] == "image/gif"
                    )
                    {
                        $file = array(
                            "name" => $file_name,
                            "tmp_name" => $_FILES["images"]["tmp_name"][$file_index]
                        );

                        $fields["images"][] = Jaris\Files::addUpload(
                            $file,
                            "backgrounds"
                        );
                    }
                }

                //Chmod all uploaded image files to 0755
                foreach($fields["images"] as $image_file)
                {
                    chmod(Jaris\Files::get($image_file, "backgrounds"), 0755);
                }

                $fields["multi"] = true;
                $fields["description"] = $_REQUEST["description"];
                $fields["top"] = intval($_REQUEST["top"]);
                $fields["attachment"] = $_REQUEST["attachment"];
                $fields["fade_speed"] = intval($_REQUEST["fade_speed"]);
                $fields["rotation_speed"] = intval($_REQUEST["rotation_speed"]);

                $fields["images"] = is_array($fields["images"]) ?
                    serialize($fields["images"])
                    :
                    false
                ;

                $fields["stretch"] = intval($_REQUEST["stretch"]);
                $fields["responsive_stretch"] = intval($_REQUEST["responsive_stretch"]);
                $fields["min_width"] = intval($_REQUEST["min_width"]);
                $fields["min_height"] = intval($_REQUEST["min_height"]);
                $fields["max_width"] = intval($_REQUEST["max_width"]);
                $fields["max_height"] = intval($_REQUEST["max_height"]);
                $fields["centerx"] = intval($_REQUEST["centerx"]);
                $fields["centery"] = intval($_REQUEST["centery"]);
                $fields["display_rule"] = $_REQUEST["display_rule"];
                $fields["pages"] = $_REQUEST["pages"];

                if($fields["images"])
                {
                    $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
                    $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

                    if(!is_array($backgrounds))
                    {
                        $backgrounds = array();
                    }

                    $backgrounds[] = $fields;

                    if(
                        Jaris\Settings::save(
                            "backgrounds",
                            serialize($backgrounds), "backgrounds"
                        )
                    )
                    {
                        Jaris\View::addMessage(t("Backgrounds successfully added."));

                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/settings/backgrounds",
                                "backgrounds"
                            )
                        );
                    }
                    else
                    {
                        Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
                    }
                }
                else
                {
                    Jaris\View::addMessage(
                        t("The images could not be moved to files/backgrounds directory."),
                        "error"
                    );
                }
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("image_file_type"), "error");
            }

            //Uninitialize fields variable to not
            //conflict with form generation below
            $fields = array();
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/backgrounds",
                    "backgrounds"
                )
            );
        }

        $parameters["name"] = "backgrounds-multi-add";
        $parameters["class"] = "backgrounds-multi-add";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/multi/add",
                "backgrounds"
            )
        );
        $parameters["method"] = "post";

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Description:"),
            "value" => $_REQUEST["description"],
            "name" => "description",
            "id" => "description",
            "description" => t("Description of the background image like for example: January 2011 special product promotion."),
            "required" => true
        );

        $fields_main[] = array(
            "type" => "file",
            "name" => "images",
            "multiple" => true,
            "valid_types" => "gif,jpg,jpeg,png",
            "label" => t("Background images:"),
            "id" => "images",
            "required" => true
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Top position:"),
            "value" => $_REQUEST["top"],
            "name" => "top",
            "id" => "top",
            "description" => t("The top position of the background images in pixels, for example 200. Default is 0")
        );

        $fields_main[] = array(
            "type" => "text",
            "name" => "fade_speed",
            "value" => $_REQUEST["fade_speed"] ?
                $_REQUEST["fade_speed"]
                :
                700,
            "label" => t("Fade speed:"),
            "id" => "fade_speed",
            "required" => true,
            "description" => t("The speed of the fade effect in milliseconds.")
        );

        $fields_main[] = array(
            "type" => "text",
            "name" => "rotation_speed",
            "value" => $_REQUEST["rotation_speed"] ?
                $_REQUEST["rotation_speed"]
                :
                5000,
            "label" => t("Rotation speed:"),
            "id" => "rotation_speed",
            "required" => true,
            "description" => t("The time in milliseconds an image is displayed before changing to the next one.")
        );

        $fieldset[] = array("fields" => $fields_main);

        $attachment[t("Scroll")] = "scroll";
        $attachment[t("Fixed")] = "fixed";

        $attachment_fields[] = array(
            "type" => "radio",
            "name" => "attachment",
            "id" => "attachment",
            "value" => $attachment,
            "checked" => $_REQUEST["attachment"] ?
                $_REQUEST["attachment"]
                :
                "fixed"
        );

        $fieldset[] = array(
            "name" => t("Attachment"),
            "fields" => $attachment_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $stretch[t("No")] = 0;
        $stretch[t("Yes")] = 1;

        $stretch_fields[] = array(
            "type" => "radio",
            "name" => "stretch",
            "id" => "stretch",
            "value" => $stretch,
            "checked" => $_REQUEST["stretch"] ? $_REQUEST["stretch"] : 1
        );

        $stretch_fields[] = array(
            "type" => "radio",
            "name" => "responsive_stretch",
            "id" => "responsive_stretch",
            "label" => t("Responsive streching?"),
            "value" => $stretch,
            "checked" => $_REQUEST["responsive_stretch"] ? $_REQUEST["responsive_stretch"] : 0,
            "description" => t("When enabled the image will be stretched according to window size")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "min_width",
            "value" => $_REQUEST["min_width"] ? $_REQUEST["min_width"] : '0',
            "label" => t("Minimum width:"),
            "id" => "min_width",
            "required" => true,
            "description" => t("The minimum width for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "min_height",
            "value" => $_REQUEST["min_height"] ? $_REQUEST["min_height"] : '0',
            "label" => t("Minimum height:"),
            "id" => "min_height",
            "required" => true,
            "description" => t("The minimum height for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "max_width",
            "value" => $_REQUEST["max_width"] ? $_REQUEST["max_width"] : '0',
            "label" => t("Maximum width:"),
            "id" => "max_width",
            "required" => true,
            "description" => t("The maximum width for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "max_height",
            "value" => $_REQUEST["max_height"] ? $_REQUEST["max_height"] : '0',
            "label" => t("Maximum height:"),
            "id" => "max_height",
            "required" => true,
            "description" => t("The maximum height for resized images. Default: 0 to disable this feature.")
        );

        $fieldset[] = array(
            "name" => t("Stretch Image"),
            "fields" => $stretch_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $center_horizontally[t("No")] = 0;
        $center_horizontally[t("Yes")] = 1;

        $center_horizontally_fields[] = array(
            "type" => "radio",
            "name" => "centerx",
            "id" => "centerx",
            "value" => $center_horizontally,
            "checked" => $_REQUEST["centerx"] ? $_REQUEST["centerx"] : 0
        );

        $fieldset[] = array(
            "name" => t("Center Image Horizontally"),
            "fields" => $center_horizontally_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $center_vertically[t("No")] = 0;
        $center_vertically[t("Yes")] = 1;

        $center_vertically_fields[] = array(
            "type" => "radio",
            "name" => "centery",
            "id" => "centery",
            "value" => $center_vertically,
            "checked" => $_REQUEST["centery"] ? $_REQUEST["centery"] : 0
        );

        $fieldset[] = array(
            "name" => t("Center Image Vertically"),
            "fields" => $center_vertically_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => "all_except_listed",
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $_REQUEST["pages"]
        );

        $fieldset[] = array(
            "fields" => $fields_pages,
            "name" => t("Pages to display"),
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        );

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


