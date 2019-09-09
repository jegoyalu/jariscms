<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the administration page for fancybox.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Fancybox Settings
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (isset($_REQUEST["btnSave"])) {
            if (Jaris\Settings::save("display_rule", $_REQUEST["display_rule"], "jquery-fancybox")) {
                Jaris\Settings::save("pages", $_REQUEST["pages"], "jquery-fancybox");
                Jaris\View::addMessage(t("Your changes have been saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"));
            }

            Jaris\Uri::go("admin/settings");
        }

        $fancybox_settings = Jaris\Settings::getAll("jquery-fancybox");

        $parameters["name"] = "jquery-fancybox-settings";
        $parameters["class"] = "jquery-fancybox-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/jquery/fancybox",
                "jquery_fancybox"
            )
        );
        $parameters["method"] = "post";

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = [
            "type" => "radio",
            "checked" => $fancybox_settings["display_rule"],
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        ];

        $fields_pages[] = [
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $fancybox_settings["pages"]
        ];

        $fieldset[] = [
            "fields" => $fields_pages,
            "name" => "Pages to display",
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
