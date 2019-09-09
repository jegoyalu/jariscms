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
        <?php print t("Video Embed Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (isset($_REQUEST["btnSave"])) {
            if (
                Jaris\Settings::save(
                    "default_width",
                    strval(intval($_REQUEST["default_width"])),
                    "video_embed"
                )
            ) {
                Jaris\Settings::save(
                    "default_height",
                    strval(intval($_REQUEST["default_height"])),
                    "video_embed"
                );

                Jaris\Settings::save(
                    "content_types",
                    serialize($_REQUEST["types"]),
                    "video_embed"
                );

                Jaris\View::addMessage(t("Your changes have been saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"));
            }

            Jaris\Uri::go("admin/settings");
        }

        $settings = Jaris\Settings::getAll("video_embed");

        $parameters["name"] = "video-embed-settings";
        $parameters["class"] = "video-embed-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/video-embed",
                "video_embed"
            )
        );
        $parameters["method"] = "post";

        $fields_main[] = [
            "type" => "text",
            "name" => "default_width",
            "label" => t("Default width:"),
            "value" => isset($settings["default_width"]) ?
                $settings["default_width"]
                :
                "640",
            "required" => true,
            "description" => t("The default width of the video player.")
        ];

        $fields_main[] = [
            "type" => "text",
            "name" => "default_height",
            "label" => t("Default height:"),
            "value" => isset($settings["default_height"]) ?
                $settings["default_height"]
                :
                "385",
            "required" => true,
            "description" => t("The default height of the video player.")
        ];

        $fieldset[] = [
            "fields" => $fields_main
        ];

        $fieldset[] = [
            "name" => t("Types to scan for video links"),
            "fields" => Jaris\Types::generateFields(
                unserialize(
                    isset($settings["content_types"]) ?
                        $settings["content_types"]
                        :
                        ""
                )
            ),
            "collapsible" => true,
            "description" => t("Do not select everything to scan all content types.")
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
