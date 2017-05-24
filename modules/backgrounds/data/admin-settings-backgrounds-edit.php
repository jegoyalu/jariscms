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
        <?php print t("Edit Background"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        $background = $backgrounds[intval($_REQUEST["id"])];

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("backgrounds-edit"))
        {
            $fields["description"] = $_REQUEST["description"];
            $fields["top"] = $_REQUEST["top"];
            $fields["position"] = $_REQUEST["position"];
            $fields["mode"] = $_REQUEST["mode"];
            $fields["attachment"] = $_REQUEST["attachment"];
            $fields["background_size"] = $_REQUEST["background_size"];
            $fields["background_color"] = $_REQUEST["background_color"];
            $fields["responsive"] = $_REQUEST["responsive"];
            $fields["max_width"] = $_REQUEST["max_width"];
            $fields["display_rule"] = $_REQUEST["display_rule"];
            $fields["pages"] = $_REQUEST["pages"];
            $fields["image"] = $background["image"];

            if(isset($_FILES["image"]) && file_exists($_FILES["image"]["tmp_name"]))
            {
                if($_FILES["image"]["type"] == "image/png" ||
                    $_FILES["image"]["type"] == "image/jpeg" ||
                    $_FILES["image"]["type"] == "image/pjpeg" ||
                    $_FILES["image"]["type"] == "image/gif"
                )
                {
                    $fields["image"] = Jaris\Files::addUpload(
                        $_FILES["image"],
                        "backgrounds"
                    );
                }
                else
                {
                    Jaris\View::addMessage(Jaris\System::errorMessage("image_file_type"), "error");
                }
            }

            if($fields["image"])
            {
                $current_image = $background["image"];

                $backgrounds[intval($_REQUEST["id"])] = $fields;

                if(Jaris\Settings::save("backgrounds", serialize($backgrounds), "backgrounds"))
                {
                    //Remove old background
                    if($current_image != $fields["image"])
                    {
                        Jaris\Files::delete($current_image, "backgrounds");

                        chmod(
                            Jaris\Files::get($fields["image"], "backgrounds"),
                            0755
                        );
                    }

                    Jaris\View::addMessage(t("Changes successfully saved."));

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
                    t("The image could not be moved to files/backgrounds directory."),
                    "error"
                );
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

        $parameters["name"] = "backgrounds-edit";
        $parameters["class"] = "backgrounds-edit";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/edit",
                "backgrounds"
            )
        );
        $parameters["method"] = "post";

        $description = isset($_REQUEST["description"]) ?
            $_REQUEST["description"]
            :
            $background["description"]
        ;

        $top = isset($_REQUEST["top"]) ?
            $_REQUEST["top"]
            :
            $background["top"]
        ;

        $display_rule = isset($_REQUEST["display_rule"]) ?
            $_REQUEST["display_rule"]
            :
            $background["display_rule"]
        ;

        $pages = isset($_REQUEST["pages"]) ?
            $_REQUEST["pages"]
            :
            $background["pages"]
        ;

        $fields_main[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Description:"),
            "value" => $description,
            "name" => "description",
            "id" => "description",
            "description" => t("Description of the background image like for example: January 2011 special product promotion."),
            "required" => true
        );

        $fields_main[] = array(
            "type" => "other",
            "html_code" => "<div style=\"margin-top: 10px;\"><strong>" .
            t("Current image:") .
            "</strong><hr /><img width=\"300px\" src=\"" .
            Jaris\Uri::url(Jaris\Files::get($background["image"], "backgrounds")) .
            "\" /></div>"
        );

        $fields_main[] = array(
            "type" => "file",
            "name" => "image",
            "label" => t("New background image file:"),
            "id" => "image"
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Top position:"),
            "value" => $top,
            "name" => "top",
            "id" => "top",
            "description" => t("The top position of the background in pixels, for example 200. Default is 0")
        );

        $fields_main[] = array(
            "type" => "color",
            "label" => t("Background color:"),
            "value" => $_REQUEST["background_color"] ?
                $_REQUEST["background_color"]
                :
                $background["background_color"],
            "name" => "background_color",
            "id" => "background_color",
            "description" => t("The overall background color of the body.")
        );

        $fieldset[] = array("fields" => $fields_main);


        $position[t("Left")] = "left";
        $position[t("Center")] = "center";
        $position[t("Right")] = "right";

        $position_fields[] = array(
            "type" => "radio",
            "name" => "position",
            "id" => "position",
            "value" => $position,
            "checked" => $_REQUEST["position"] ?
                $_REQUEST["position"]
                :
                $background["position"]
        );

        $fieldset[] = array(
            "name" => t("Position"),
            "fields" => $position_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $mode[t("Repeat")] = "repeat";
        $mode[t("No Repeat")] = "no-repeat";

        $mode_fields[] = array(
            "type" => "radio",
            "name" => "mode",
            "id" => "mode",
            "value" => $mode,
            "checked" => $_REQUEST["mode"] ?
                $_REQUEST["mode"]
                :
                $background["mode"]
        );

        $fieldset[] = array(
            "name" => t("Mode"),
            "fields" => $mode_fields,
            "collapsible" => true,
            "collapsed" => true
        );

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
                $background["attachment"]
        );

        $fieldset[] = array(
            "name" => t("Attachment"),
            "fields" => $attachment_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $size_fields[] = array(
            "type" => "radio",
            "name" => "background_size",
            "id" => "background_size",
            "value" => array(
                "cover" => "cover",
                "auto" => "auto",
                "contain" => "contain"
            ),
            "checked" => $_REQUEST["background_size"] ?
                $_REQUEST["background_size"]
                :
                $background["background_size"]
        );

        $fieldset[] = array(
            "name" => t("Size"),
            "fields" => $size_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $responsive[t("Enable")] = true;
        $responsive[t("Disable")] = false;

        $fields_responsive[] = array(
            "type" => "radio",
            "checked" => $_REQUEST["responsive"] ?
                $_REQUEST["responsive"]
                :
                $background["responsive"],
            "name" => "responsive",
            "id" => "responsive",
            "value" => $responsive
        );

        $fields_responsive[] = array(
            "type" => "text",
            "name" => "max_width",
            "value" => $_REQUEST["max_width"] ?
                $_REQUEST["max_width"]
                :
                $background["max_width"],
            "label" => t("Maximum width:"),
            "id" => "max_width",
            "description" => t("The maximum width for resized images. Default: 0 to disable this feature.")
        );

        $fieldset[] = array(
            "fields" => $fields_responsive,
            "name" => t("Responsive"),
            "collapsible" => true,
            "collapsed" => true
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => $display_rule,
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $pages
        );

        $fieldset[] = array(
            "fields" => $fields_pages,
            "name" => "Pages to display",
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
