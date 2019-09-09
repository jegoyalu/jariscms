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
        <?php print t("Edit Parallax Background"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $backgrounds_settings = Jaris\Settings::getAll("parallax");
        $backgrounds = unserialize($backgrounds_settings["parallax_backgrounds"]);

        $background = $backgrounds[intval($_REQUEST["id"])];

        if(
            isset($_REQUEST["btnSave"])
            &&
            !Jaris\Forms::requiredFieldEmpty("parallax-edit")
        )
        {
            $fields["description"] = $_REQUEST["description"];
            $fields["element"] = trim($_REQUEST["element"]);
            $fields["vertical_position"] = $_REQUEST["vertical_position"];
            $fields["horizontal_position"] = $_REQUEST["horizontal_position"];
            $fields["background_size"] = $_REQUEST["background_size"];
            $fields["background_color"] = $_REQUEST["background_color"];
            $fields["display_rule"] = $_REQUEST["display_rule"];
            $fields["pages"] = $_REQUEST["pages"];
            $fields["image"] = $background["image"];

            if(
                isset($_FILES["image"])
                &&
                file_exists($_FILES["image"]["tmp_name"])
            )
            {
                if($_FILES["image"]["type"] == "image/png" ||
                    $_FILES["image"]["type"] == "image/jpeg" ||
                    $_FILES["image"]["type"] == "image/pjpeg" ||
                    $_FILES["image"]["type"] == "image/gif"
                )
                {
                    $fields["image"] = Jaris\Files::addUpload(
                        $_FILES["image"],
                        "parallax"
                    );
                }
                else
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("image_file_type"),
                        "error"
                    );
                }
            }

            if($fields["image"])
            {
                $current_image = $background["image"];

                $backgrounds[intval($_REQUEST["id"])] = $fields;

                if(
                    Jaris\Settings::save(
                        "parallax_backgrounds",
                        serialize($backgrounds),
                        "parallax"
                    )
                )
                {
                    //Remove old background
                    if($current_image != $fields["image"])
                    {
                        Jaris\Files::delete($current_image, "parallax");

                        chmod(
                            Jaris\Files::get($fields["image"], "parallax"),
                            0755
                        );
                    }

                    Jaris\View::addMessage(t("Changes successfully saved."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/settings/parallax",
                            "parallax"
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
            }
            else
            {
                Jaris\View::addMessage(
                    t("The image could not be moved to files/parallax directory."),
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
                    "admin/settings/parallax",
                    "parallax"
                )
            );
        }

        $parameters["name"] = "parallax-edit";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $description = isset($_REQUEST["description"]) ?
            $_REQUEST["description"]
            :
            $background["description"]
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
            "description" => t("Description of the background image like for example: January 2011 special product promotion."),
            "required" => true
        );

        $fields_main[] = array(
            "type" => "other",
            "html_code" => "<div style=\"margin-top: 10px;\"><strong>" .
            t("Current image:") .
            "</strong><hr /><img width=\"300px\" src=\"" .
            Jaris\Uri::url(Jaris\Files::get($background["image"], "parallax")) .
            "\" /></div>"
        );

        $fields_main[] = array(
            "type" => "file",
            "name" => "image",
            "label" => t("New background image file:")
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Element:"),
            "value" => $_REQUEST["element"] ?
                $_REQUEST["element"]
                :
                $background["element"],
            "name" => "element",
            "description" => t("The css selector of an explicit element to put the background image.")
        );

        $fields_main[] = array(
            "type" => "color",
            "label" => t("Background color:"),
            "value" => $_REQUEST["background_color"] ?
                $_REQUEST["background_color"]
                :
                $background["background_color"],
            "name" => "background_color",
            "description" => t("The overall background color of the body.")
        );

        $fieldset[] = array("fields" => $fields_main);

        $position_fields[] = array(
            "type" => "radio",
            "name" => "vertical_position",
            "label" => "Vertical:",
            "value" => array(
                t("Top") => "top",
                t("Center") => "center",
                t("Bottom") => "bottom"
            ),
            "checked" => $_REQUEST["vertical_position"] ?
                $_REQUEST["vertical_position"]
                :
                $background["vertical_position"]
        );

        $position_fields[] = array(
            "type" => "radio",
            "name" => "horizontal_position",
            "label" => "Horizontal:",
            "value" => array(
                t("Left") => "left",
                t("Center") => "center",
                t("Right") => "right"
            ),
            "checked" => $_REQUEST["horizontal_position"] ?
                $_REQUEST["horizontal_position"]
                :
                $background["horizontal_position"]
        );

        $fieldset[] = array(
            "name" => t("Position"),
            "fields" => $position_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $size_fields[] = array(
            "type" => "radio",
            "name" => "background_size",
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
            "description" => t("List of uri's separated by comma (,). Also supports the wildcard (*), for example: my-section/*")
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
