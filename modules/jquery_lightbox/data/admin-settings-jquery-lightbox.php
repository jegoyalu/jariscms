<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the administration page for lightbox.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Lightbox Settings
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnSave"]))
        {
            if(Jaris\Settings::save("display_rule", $_REQUEST["display_rule"], "jquery-lightbox"))
            {
                Jaris\Settings::save("pages", $_REQUEST["pages"], "jquery-lightbox");
                Jaris\View::addMessage(t("Your changes have been saved."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"));
            }

            Jaris\Uri::go("admin/settings");
        }

        $lightbox_settings = Jaris\Settings::getAll("jquery-lightbox");

        $parameters["name"] = "jquery-lightbox-settings";
        $parameters["class"] = "jquery-lightbox-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/jquery/lightbox",
                "jquery_lightbox"
            )
        );
        $parameters["method"] = "post";

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => $lightbox_settings["display_rule"],
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array("type" => "uriarea", "name" => "pages", "label" => t("Pages:"), "id" => "pages", "value" => $lightbox_settings["pages"]);

        $fieldset[] = array("fields" => $fields_pages, "name" => "Pages to display", "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));

        $fields[] = array("type" => "submit", "name" => "btnSave", "value" => t("Save"));

        $fields[] = array("type" => "submit", "name" => "btnCancel", "value" => t("Cancel"));

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
