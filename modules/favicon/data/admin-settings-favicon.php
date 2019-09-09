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
        <?php print t("Favicon Settings"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        $favicon_settings = Jaris\Settings::getAll("favicon");

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("favicon-settings")
        ) {
            $new_image = "";

            Jaris\Settings::save("favicon_enable", $_REQUEST["favicon_enable"], "favicon");
            Jaris\Settings::save("application_name", $_REQUEST["application_name"], "favicon");
            Jaris\Settings::save("metro_tile_color", $_REQUEST["metro_tile_color"], "favicon");

            if (isset($_FILES["image"]) && file_exists($_FILES["image"]["tmp_name"])) {
                if ($_FILES["image"]["type"] == "image/png" ||
                    $_FILES["image"]["type"] == "image/jpeg" ||
                    $_FILES["image"]["type"] == "image/pjpeg" ||
                    $_FILES["image"]["type"] == "image/gif"
                ) {
                    $new_image = Jaris\Files::addUpload(
                        $_FILES["image"],
                        "favicon"
                    );

                    chmod(
                        Jaris\Files::get($new_image, "favicon"),
                        0755
                    );

                    // Generate icons
                    $sizes = [
                        [57, 57, "apple-touch-icon-57x57.png"],
                        [114, 114, "apple-touch-icon-114x114.png"],
                        [72, 72, "apple-touch-icon-72x72.png"],
                        [144, 144, "apple-touch-icon-144x144.png"],
                        [60, 60, "apple-touch-icon-60x60.png"],
                        [120, 120, "apple-touch-icon-120x120.png"],
                        [76, 76, "apple-touch-icon-76x76.png"],
                        [152, 152, "apple-touch-icon-152x152.png"],
                        [196, 196, "favicon-196x196.png"],
                        [96, 96, "favicon-96x96.png"],
                        [32, 32, "favicon-32x32.png"],
                        [16, 16, "favicon-16x16.png"],
                        [128, 128, "favicon-128.png"],
                        [144, 144, "mstile-144x144.png"],
                        [70, 70, "mstile-70x70.png"],
                        [150, 150, "mstile-150x150.png"],
                        [310, 150, "mstile-310x150.png"],
                        [310, 310, "mstile-310x310.png"],
                    ];

                    foreach ($sizes as $size) {
                        $image = Jaris\Images::get(
                            Jaris\Files::get($new_image, "favicon"),
                            $size[0],
                            $size[1]
                        );

                        Jaris\Files::delete($size[2], "favicon");

                        imagepng(
                            $image["binary_data"],
                            Jaris\Files::getDir("favicon") . $size[2]
                        );

                        chmod(
                            Jaris\Files::getDir("favicon") . $size[2],
                            0755
                        );
                    }
                } else {
                    Jaris\View::addMessage(Jaris\System::errorMessage("image_file_type"), "error");
                }
            }

            if ($new_image != "") {
                $current_image = $favicon_settings["current_image"];

                if (Jaris\Settings::save("current_image", $new_image, "favicon")) {
                    //Remove old original favicon
                    if ($current_image && $current_image != $new_image) {
                        Jaris\Files::delete($current_image, "favicon");
                    }

                    Jaris\View::addMessage(t("Changes successfully saved."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/settings/favicon",
                            "favicon"
                        )
                    );
                } else {
                    Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
                }
            }
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "favicon-settings";
        $parameters["class"] = "favicon-settings";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/favicon",
                "favicon"
            )
        );
        $parameters["method"] = "post";

        $favicon = [
            t("Enable") => true,
            t("Disable") => false
        ];

        $fields[] = [
            "type" => "radio",
            "name" => "favicon_enable",
            "value" => $favicon,
            "checked" => isset($_REQUEST["favicon_enable"]) ?
                $_REQUEST["favicon_enable"]
                :
                $favicon_settings["favicon_enable"],
            "description" => t("Enable or disable the generation of favicon html code.")
        ];

        if (isset($favicon_settings["current_image"])) {
            if (file_exists(Jaris\Files::get($favicon_settings["current_image"], "favicon"))) {
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
                                $favicon_settings["current_image"],
                                "favicon"
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
            "label" => t("Favicon image file:"),
            "description" => t("Please upload a 310px X 310px image.")
        ];

        $fields[] = [
            "type" => "text",
            "label" => t("Application name:"),
            "value" => isset($_REQUEST["application_name"]) ?
                $_REQUEST["application_name"]
                :
                $favicon_settings["application_name"],
            "name" => "application_name",
            "description" => t("When your site is treated like a mobile application this is the name that will be displayed for it."),
            "required" => true
        ];

        $fields[] = [
            "type" => "color",
            "label" => t("Metro tile color:"),
            "value" => isset($_REQUEST["metro_tile_color"]) ?
                $_REQUEST["metro_tile_color"]
                :
                $favicon_settings["metro_tile_color"],
            "name" => "metro_tile_color",
            "description" => t("A tile color for Windows metro interface.")
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


