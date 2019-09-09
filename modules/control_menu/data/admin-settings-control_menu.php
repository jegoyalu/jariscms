<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the license.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Control Menu Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $menu_settings = Jaris\Settings::getAll("control_menu");

        if (isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("edit-control-menu-settings")) {
            //Check if write is possible and continue to write settings
            if (Jaris\Settings::save("help_link", $_REQUEST["help_link"], "control_menu")) {
                Jaris\Settings::save("main_bar_background", $_REQUEST["main_bar_background"], "control_menu");
                Jaris\Settings::save("main_bar_border", $_REQUEST["main_bar_border"], "control_menu");
                Jaris\Settings::save("user_button", $_REQUEST["user_button"], "control_menu");
                Jaris\Settings::save("user_button_text", $_REQUEST["user_button_text"], "control_menu");
                Jaris\Settings::save("image_hover", $_REQUEST["image_hover"], "control_menu");
                Jaris\Settings::save("image_color", $_REQUEST["image_color"], "control_menu");
                Jaris\Settings::save("main_menu_text", $_REQUEST["main_menu_text"], "control_menu");
                Jaris\Settings::save("main_menu_text_hover", $_REQUEST["main_menu_text_hover"], "control_menu");
                Jaris\Settings::save("main_menu_background_hover", $_REQUEST["main_menu_background_hover"], "control_menu");
                Jaris\Settings::save("submenu_background", $_REQUEST["submenu_background"], "control_menu");
                Jaris\Settings::save("submenu_border", $_REQUEST["submenu_border"], "control_menu");
                Jaris\Settings::save("submenu_text", $_REQUEST["submenu_text"], "control_menu");
                Jaris\Settings::save("submenu_text_hover", $_REQUEST["submenu_text_hover"], "control_menu");
                Jaris\Settings::save("submenu_text_background_hover", $_REQUEST["submenu_text_background_hover"], "control_menu");
                Jaris\Settings::save("submenu_text_border", $_REQUEST["submenu_text_border"], "control_menu");

                Jaris\View::addMessage(t("Your settings have been successfully saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(Jaris\Modules::getPageUri("admin/settings/control-menu", "control_menu"));
        } elseif (isset($_REQUEST["btnReset"])) {
            if (Jaris\Settings::save("main_bar_background", "E9F2F5", "control_menu")) {
                Jaris\Settings::save("main_bar_border", "D3D3D3", "control_menu");
                Jaris\Settings::save("user_button", "235C96", "control_menu");
                Jaris\Settings::save("user_button_text", "FFFFFF", "control_menu");
                Jaris\Settings::save("image_hover", "D5E7ED", "control_menu");
                Jaris\Settings::save("image_color", "", "control_menu");
                Jaris\Settings::save("main_menu_text", "00576D", "control_menu");
                Jaris\Settings::save("main_menu_text_hover", "00576D", "control_menu");
                Jaris\Settings::save("main_menu_background_hover", "D5E7ED", "control_menu");
                Jaris\Settings::save("submenu_background", "E9F2F5", "control_menu");
                Jaris\Settings::save("submenu_border", "D3D3D3", "control_menu");
                Jaris\Settings::save("submenu_text", "00576D", "control_menu");
                Jaris\Settings::save("submenu_text_hover", "FFFFFF", "control_menu");
                Jaris\Settings::save("submenu_text_background_hover", "DF7500", "control_menu");
                Jaris\Settings::save("submenu_text_border", "D3D3D3", "control_menu");

                Jaris\View::addMessage(t("Colors have been successfully reset."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings");
        }

        // If colors not set use default ones.
        $menu_settings["main_bar_background"] = $menu_settings["main_bar_background"] ?
            $menu_settings["main_bar_background"] : "E9F2F5"
        ;

        $menu_settings["main_bar_border"] = $menu_settings["main_bar_border"] ?
            $menu_settings["main_bar_border"] : "D3D3D3"
        ;

        $menu_settings["user_button"] = $menu_settings["user_button"] ?
            $menu_settings["user_button"] : "235C96"
        ;

        $menu_settings["user_button_text"] = $menu_settings["user_button_text"] ?
            $menu_settings["user_button_text"] : "FFFFFF"
        ;

        $menu_settings["image_hover"] = $menu_settings["image_hover"] ?
            $menu_settings["image_hover"] : "D5E7ED"
        ;

        $menu_settings["image_color"] = $menu_settings["image_color"] ?
            $menu_settings["image_color"] : ""
        ;

        $menu_settings["main_menu_text"] = $menu_settings["main_menu_text"] ?
            $menu_settings["main_menu_text"] : "00576D"
        ;

        $menu_settings["main_menu_text_hover"] = $menu_settings["main_menu_text_hover"] ?
            $menu_settings["main_menu_text_hover"] : "00576D"
        ;

        $menu_settings["main_menu_background_hover"] = $menu_settings["main_menu_background_hover"] ?
            $menu_settings["main_menu_background_hover"] : "D5E7ED"
        ;

        $menu_settings["submenu_background"] = $menu_settings["submenu_background"] ?
            $menu_settings["submenu_background"] : "E9F2F5"
        ;

        $menu_settings["submenu_border"] = $menu_settings["submenu_border"] ?
            $menu_settings["submenu_border"] : "D3D3D3"
        ;

        $menu_settings["submenu_text"] = $menu_settings["submenu_text"] ?
            $menu_settings["submenu_text"] : "00576D"
        ;

        $menu_settings["submenu_text_hover"] = $menu_settings["submenu_text_hover"] ?
            $menu_settings["submenu_text_hover"] : "FFFFFF"
        ;

        $menu_settings["submenu_text_background_hover"] = $menu_settings["submenu_text_background_hover"] ?
            $menu_settings["submenu_text_background_hover"] : "DF7500"
        ;

        $menu_settings["submenu_text_border"] = $menu_settings["submenu_text_border"] ?
            $menu_settings["submenu_text_border"] : "D3D3D3"
        ;

        $parameters["name"] = "edit-control-menu-settings";
        $parameters["class"] = "edit-control-menu-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/settings/control-menu", "control_menu")
        );
        $parameters["method"] = "post";

        $fields_links[] = [
            "type" => "text",
            "name" => "help_link",
            "label" => t("Help link:"),
            "id" => "help_link",
            "value" => $menu_settings["help_link"],
            "description" => t("A link to the help page for administrators.")
        ];

        $fieldset[] = ["fields" => $fields_links];

        $fields_colors[] = [
            "type" => "color",
            "name" => "main_bar_background",
            "label" => t("Main bar:"),
            "id" => "main_bar_background",
            "value" => $menu_settings["main_bar_background"],
            "description" => t("Background color of the main bar.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "main_bar_border",
            "label" => t("Main bar border:"),
            "id" => "main_bar_border",
            "value" => $menu_settings["main_bar_border"],
            "description" => t("The border color of the main bar.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "user_button",
            "label" => t("User button:"),
            "id" => "user_button",
            "value" => $menu_settings["user_button"],
            "description" => t("Background color of the user account button.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "user_button_text",
            "label" => t("User button text:"),
            "id" => "user_button_text",
            "value" => $menu_settings["user_button_text"],
            "description" => t("Text color for the user account button.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "image_hover",
            "label" => t("Images hover:"),
            "id" => "image_hover",
            "value" => $menu_settings["image_hover"],
            "description" => t("Background color when hovering an image button.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "main_menu_text",
            "label" => t("Main menus text:"),
            "id" => "main_menu_text",
            "value" => $menu_settings["main_menu_text"],
            "description" => t("Text color for main menus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "main_menu_text_hover",
            "label" => t("Main menus text hover:"),
            "id" => "main_menu_text_hover",
            "value" => $menu_settings["main_menu_text_hover"],
            "description" => t("Hover effect text color for main menus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "main_menu_background_hover",
            "label" => t("Main menus background hover:"),
            "id" => "main_menu_background_hover",
            "value" => $menu_settings["main_menu_background_hover"],
            "description" => t("Hover effect background color for main menus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_background",
            "label" => t("Submenu:"),
            "id" => "submenu_background",
            "value" => $menu_settings["submenu_background"],
            "description" => t("Background color for submenus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_border",
            "label" => t("Submenu border:"),
            "id" => "submenu_border",
            "value" => $menu_settings["submenu_border"],
            "description" => t("Border color for submenus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_text",
            "label" => t("Submenu text:"),
            "id" => "submenu_text",
            "value" => $menu_settings["submenu_text"],
            "description" => t("Text color for submenus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_text_border",
            "label" => t("Submenu text border:"),
            "id" => "submenu_text_border",
            "value" => $menu_settings["submenu_text_border"],
            "description" => t("Text border color for submenus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_text_hover",
            "label" => t("Submenu text hover:"),
            "id" => "submenu_text_hover",
            "value" => $menu_settings["submenu_text_hover"],
            "description" => t("Hover effect text color for submenus.")
        ];

        $fields_colors[] = [
            "type" => "color",
            "name" => "submenu_text_background_hover",
            "label" => t("Submenu background hover:"),
            "id" => "submenu_text_background_hover",
            "value" => $menu_settings["submenu_text_background_hover"],
            "description" => t("Hover effect background color for submenus.")
        ];

        $fieldset[] = [
            "fields" => $fields_colors,
            "name" => t("Colors"),
            "collapsible" => true,
            "collapsed" => true
        ];

        $image_colors[t("Blue")] = "";
        $image_colors[t("Black")] = "-blk";
        $image_colors[t("White")] = "-wht";

        $fields_image_color[] = [
            "type" => "radio",
            "checked" => $menu_settings["image_color"],
            "name" => "image_color",
            "id" => "image_color",
            "value" => $image_colors
        ];

        $fieldset[] = [
            "fields" => $fields_image_color,
            "name" => t("Images color"),
            "collapsible" => true,
            "collapsed" => true
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnReset",
            "value" => t("Reset Colors")
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
