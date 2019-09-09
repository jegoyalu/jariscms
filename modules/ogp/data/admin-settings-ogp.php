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
        <?php print t("Open Graph Settings"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("ogp-settings")
        ) {
            $ogp_settings = Jaris\Settings::getAll("ogp");

            Jaris\Settings::save(
                "image_width",
                $_REQUEST["image_width"],
                "ogp"
            );
            Jaris\Settings::save(
                "image_height",
                $_REQUEST["image_height"],
                "ogp"
            );
            Jaris\Settings::save(
                "image_count",
                $_REQUEST["image_count"],
                "ogp"
            );
            Jaris\Settings::save(
                "image_keep_aspect",
                $_REQUEST["image_keep_aspect"],
                "ogp"
            );

            $new_image = "";

            if (
                isset($_FILES["image"])
                &&
                file_exists($_FILES["image"]["tmp_name"])
            ) {
                if (
                    $_FILES["image"]["type"] == "image/png" ||
                    $_FILES["image"]["type"] == "image/jpeg" ||
                    $_FILES["image"]["type"] == "image/pjpeg" ||
                    $_FILES["image"]["type"] == "image/gif"
                ) {
                    $new_image = Jaris\Files::addUpload(
                        $_FILES["image"],
                        "ogp"
                    );

                    chmod(
                        Jaris\Files::get($new_image, "ogp"),
                        0755
                    );
                } else {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("image_file_type"),
                        "error"
                    );
                }
            }

            if ($new_image != "") {
                $current_image = $ogp_settings["current_image"];

                if (Jaris\Settings::save("current_image", $new_image, "ogp")) {
                    //Remove old original ogp
                    if ($current_image != $new_image) {
                        Jaris\Files::delete($current_image, "ogp");
                    }

                    Jaris\View::addMessage(t("Changes successfully saved."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/settings/ogp",
                            "ogp"
                        )
                    );
                } else {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );
                }
            }
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings");
        }

        $ogp_settings = Jaris\Settings::getAll("ogp");

        $parameters["name"] = "ogp-settings";
        $parameters["class"] = "ogp-settings";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/ogp",
                "ogp"
            )
        );
        $parameters["method"] = "post";

        if (isset($ogp_settings["current_image"])) {
            if (file_exists(Jaris\Files::get($ogp_settings["current_image"], "ogp"))) {
                $fields[] = [
                    "type" => "other",
                    "html_code" => "<div style=\"margin-top: 10px;\">"
                        . "<strong>" .
                        t("Current image:") .
                        "</strong>"
                        . "<hr />"
                        . '<div style="padding: 7px; border: solid 1px #000; background-color: #d3d3d3">'
                        . "<img width=\"300px\" src=\"" .
                        Jaris\Uri::url(
                            Jaris\Files::get(
                                $ogp_settings["current_image"],
                                "ogp"
                            )
                        )
                        ."\" />"
                        . "</div>"
                        . "</div>"
                ];
            }
        }

        $fields[] = [
            "type" => "file",
            "name" => "image",
            "label" => t("Default image file:"),
            "description" => t("Please upload a 200px X 200px image that will be used as default image if current section doesn't have one. If non is uploaded, a logo.png or logo.jpg image will be scanned from the current theme and used if found.")
        ];

        $fields[] = [
            "type" => "text",
            "name" => "image_width",
            "label" => t("Image width:"),
            "value" => $ogp_settings["image_width"] ?? 498,
            "description" => t("The width of the graph generated images.")
        ];

        $fields[] = [
            "type" => "text",
            "name" => "image_height",
            "label" => t("Image height:"),
            "value" => $ogp_settings["image_height"] ?? 375,
            "description" => t("The height of the graph generated images.")
        ];

        $fields[] = [
            "type" => "number",
            "name" => "image_count",
            "label" => t("Image count:"),
            "value" => $ogp_settings["image_count"] ?? 1,
            "description" => t("The amount of images to add.")
        ];

        $fields[] = [
            "type" => "radio",
            "name" => "image_keep_aspect",
            "label" => t("Keep aspect ratio?"),
            "value" => [t("Yes") => true, t("No") => false],
            "selected" => $ogp_settings["image_keep_aspect"] ?? true
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