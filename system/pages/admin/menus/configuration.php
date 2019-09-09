<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menus configuration page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Menu Configuration") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["configure_menus"]);

        if (isset($_REQUEST["btnSave"])) {
            Jaris\Settings::save("primary_menu", $_REQUEST["primary"], "main");
            Jaris\Settings::save("secondary_menu", $_REQUEST["secondary"], "main");

            Jaris\View::addMessage(t("Your changes have been successfully saved."));

            t("Updated primary/secondary menu settings.");

            Jaris\Logger::info(
                "Updated primary/secondary menu settings."
            );

            Jaris\Uri::go("admin/menus");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/menus");
        }

        $parameters["name"] = "configure-menu";
        $parameters["class"] = "configure-menu";
        $parameters["action"] = Jaris\Uri::url("admin/menus/configuration");
        $parameters["method"] = "post";

        $menu_list = Jaris\Menus::getList();

        $menus = [];

        foreach ($menu_list as $name) {
            $menus[$name] = $name;
        }

        $current_primary = Jaris\Settings::get("primary_menu", "main");
        $current_secondary = Jaris\Settings::get("secondary_menu", "main");

        $fields[] = [
            "type" => "select",
            "name" => "primary",
            "selected" => $current_primary ? $current_primary : "primary",
            "label" => t("Primary menu:"),
            "id" => "primary",
            "value" => $menus,
            "description" => t("Menu returned on the \$primary_links template variable")
        ];

        $fields[] = [
            "type" => "select",
            "name" => "secondary",
            "selected" => $current_secondary ? $current_secondary : "secondary",
            "label" => t("Secondary menu:"),
            "id" => "secondary",
            "value" => $menus,
            "description" => t("Menu returned on the \$secondary_links template variable")
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
