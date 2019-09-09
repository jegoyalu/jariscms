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
        Rss Settings
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (isset($_REQUEST["btnSave"])) {
            if (
                Jaris\Settings::save(
                    "description_words",
                    strval(intval($_REQUEST["description_words"])),
                    "rss"
                )
            ) {
                Jaris\Settings::save(
                    "images_enable",
                    strval(intval($_REQUEST["images_enable"])),
                    "rss"
                );

                Jaris\Settings::save(
                    "images_keep_aspect_raio",
                    $_REQUEST["images_keep_aspect_raio"],
                    "rss"
                );

                Jaris\Settings::save(
                    "images_width",
                    strval(intval($_REQUEST["images_width"])),
                    "rss"
                );

                Jaris\Settings::save(
                    "images_height",
                    strval(intval($_REQUEST["images_height"])),
                    "rss"
                );

                Jaris\View::addMessage(t("Your changes have been saved."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data")
                );
            }

            Jaris\Uri::go("admin/settings");
        }

        Jaris\View::addTab(
            "RSS Selector",
            Jaris\Modules::getPageUri("rss/selector", "rss")
        );

        $rss_settings = Jaris\Settings::getAll("rss");

        $parameters["name"] = "rss-settings";
        $parameters["class"] = "rss-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/rss",
                "rss"
            )
        );
        $parameters["method"] = "post";

        $fields_main[] = [
            "type" => "text",
            "name" => "description_words",
            "label" => t("Description lenght:"),
            "value" => $rss_settings["description_words"] ?
                $rss_settings["description_words"]
                :
                "45",
            "required" => true,
            "description" => t("The amount of words to include on the description of the rss item. Default is 45")
        ];

        $fieldset[] = [
            "fields" => $fields_main
        ];

        $display_images[t("Enable")] = true;
        $display_images[t("Disable")] = false;

        $fields_images[] = [
            "type" => "radio",
            "name" => "images_enable",
            "value" => $display_images,
            "checked" => isset($rss_settings["images_enable"]) ?
                $rss_settings["images_enable"]
                :
                true
        ];

        $fields_images[] = [
            "type" => "text",
            "name" => "images_width",
            "label" => t("Images width:"),
            "value" => $rss_settings["images_width"] ?
                $rss_settings["images_width"]
                :
                "512",
            "required" => true,
            "description" => t("The widht in pixels for the rss images. Default is 512")
        ];

        $fields_images[] = [
            "type" => "text",
            "name" => "images_height",
            "label" => t("Images height:"),
            "value" => $rss_settings["images_height"] ?
                $rss_settings["images_height"]
                :
                "384",
            "required" => true,
            "description" => t("The height in pixels for the rss images. Default is 384")
        ];

        $fields_images[] = [
            "type" => "other",
            "html_code" => "<br />"
        ];

        $fields_images[] = [
            "type" => "checkbox",
            "name" => "images_keep_aspect_raio",
            "label" => t("Keep aspect ratio?"),
            "checked" => isset($rss_settings["images_keep_aspect_raio"]) ?
                $rss_settings["images_keep_aspect_raio"]
                :
                true,
            "value" => true
        ];

        $fieldset[] = [
            "fields" => $fields_images,
            "name" => t("Images"),
            "collasible" => true,
            "collapsed" => false
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
