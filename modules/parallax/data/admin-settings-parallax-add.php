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
        <?php print t("Add Parallax Background"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (
            isset($_REQUEST["btnSave"])
            &&
            !Jaris\Forms::requiredFieldEmpty("parallax-add")
        ) {
            if ($_FILES["image"]["type"] == "image/png" ||
                $_FILES["image"]["type"] == "image/jpeg" ||
                $_FILES["image"]["type"] == "image/pjpeg" ||
                $_FILES["image"]["type"] == "image/gif"
            ) {
                $fields["description"] = $_REQUEST["description"];
                $fields["image"] = Jaris\Files::addUpload(
                    $_FILES["image"],
                    "parallax"
                );

                $fields["element"] = trim($_REQUEST["element"]);
                $fields["vertical_position"] = $_REQUEST["vertical_position"];
                $fields["horizontal_position"] = $_REQUEST["horizontal_position"];
                $fields["background_size"] = $_REQUEST["background_size"];
                $fields["background_color"] = $_REQUEST["background_color"];
                $fields["display_rule"] = $_REQUEST["display_rule"];
                $fields["pages"] = $_REQUEST["pages"];

                if ($fields["image"]) {
                    chmod(
                        Jaris\Files::get($fields["image"], "parallax"),
                        0755
                    );

                    $backgrounds_settings = Jaris\Settings::getAll("parallax");

                    $backgrounds = unserialize(
                        $backgrounds_settings["parallax_backgrounds"]
                    );

                    if (!is_array($backgrounds)) {
                        $backgrounds = [];
                    }

                    $backgrounds[] = $fields;

                    if (
                        Jaris\Settings::save(
                            "parallax_backgrounds",
                            serialize($backgrounds),
                            "parallax"
                        )
                    ) {
                        Jaris\View::addMessage(
                            t("Background successfully added.")
                        );

                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/settings/parallax",
                                "parallax"
                            )
                        );
                    } else {
                        Jaris\View::addMessage(
                            Jaris\System::errorMessage("write_error_data"),
                            "error"
                        );
                    }
                } else {
                    Jaris\View::addMessage(
                        t("The image could not be moved to files/parallax directory."),
                        "error"
                    );
                }
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("image_file_type"),
                    "error"
                );
            }

            //Uninitialize fields variable to not
            //conflict with form generation below
            $fields = [];
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/parallax",
                    "parallax"
                )
            );
        }

        $parameters["name"] = "parallax-add";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields_main[] = [
            "type" => "text",
            "label" => t("Description:"),
            "value" => $_REQUEST["description"],
            "name" => "description",
            "description" => t("Description of the parallax like for example: January 2011 special product promotion."),
            "required" => true
        ];

        $fields_main[] = [
            "type" => "file",
            "name" => "image",
            "label" => t("Background image file:"),
            "required" => true
        ];

        $fields_main[] = [
            "type" => "text",
            "label" => t("Element:"),
            "value" => $_REQUEST["element"],
            "name" => "element",
            "description" => t("The css selector of an explicit element to put the background image.")
        ];

        $fields_main[] = [
            "type" => "color",
            "label" => t("Background color:"),
            "value" => $_REQUEST["background_color"] ?
                $_REQUEST["background_color"]
                :
                "FFFFFF",
            "name" => "background_color",
            "description" => t("The background color for the element.")
        ];

        $fieldset[] = ["fields" => $fields_main];

        $position_fields[] = [
            "type" => "radio",
            "name" => "vertical_position",
            "label" => t("Vertical:"),
            "value" => [
                t("Top") => "top",
                t("Center") => "center",
                t("Bottom") => "bottom"
            ],
            "checked" => $_REQUEST["vertical_position"] ?
                $_REQUEST["vertical_position"]
                :
                "center"
        ];

        $position_fields[] = [
            "type" => "radio",
            "name" => "horizontal_position",
            "label" => t("Horizontal:"),
            "value" => [
                t("Left") => "left",
                t("Center") => "center",
                t("Right") => "right"
            ],
            "checked" => $_REQUEST["horizontal_position"] ?
                $_REQUEST["horizontal_position"]
                :
                "center"
        ];

        $fieldset[] = [
            "name" => t("Position"),
            "fields" => $position_fields,
            "collapsible" => true,
            "collapsed" => true
        ];

        $size_fields[] = [
            "type" => "radio",
            "name" => "background_size",
            "value" => [
                "cover" => "cover",
                "auto" => "auto",
                "contain" => "contain"
            ],
            "checked" => $_REQUEST["background_size"] ?
                $_REQUEST["background_size"]
                :
                "cover"
        ];

        $fieldset[] = [
            "name" => t("Size"),
            "fields" => $size_fields,
            "collapsible" => true,
            "collapsed" => true
        ];

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = [
            "type" => "radio",
            "checked" => "all_except_listed",
            "name" => "display_rule",
            "value" => $display_rules
        ];

        $fields_pages[] = [
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "value" => $_REQUEST["pages"]
        ];

        $fieldset[] = [
            "fields" => $fields_pages,
            "name" => t("Pages to display"),
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        ];

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
