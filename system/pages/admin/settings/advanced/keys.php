<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Third Party Api Keys") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        //Get exsiting settings or defualt ones if main
        //settings table doesn't exist
        $settings = null;

        if (!($settings = Jaris\Settings::getAll("keys"))) {
            $settings["google_maps"] = "";
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-site-advanced-settings")
        ) {
            //Check if write is possible and continue to write settings
            if (Jaris\Settings::save("google_maps", $_REQUEST["google_maps"], "keys")) {
                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                t("Edited global api keys.");

                Jaris\Logger::info("Edited global api keys.");
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/settings/advanced");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings/advanced");
        }

        $parameters["name"] = "edit-third-party-key-settings";
        $parameters["class"] = "edit-third-party-key-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings/advanced/keys");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "text",
            "label" => t("Google Maps:"),
            "name" => "google_maps",
            "id" => "google_maps",
            "value" => $settings["google_maps"],
            "description" => t("The key used for the components that depend on the google maps api.")
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
