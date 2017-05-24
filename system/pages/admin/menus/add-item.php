<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu add item page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Menu Item") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_menus", "add_menu_items")
        );

        if(!isset($_REQUEST["menu"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-menu-item")
        )
        {
            if(trim($_REQUEST["url"]) == "")
            {
                $_REQUEST["url"] = Jaris\Uri::fromText($_REQUEST["title"]);
            }

            $fields["title"] = $_REQUEST["title"];
            $fields["url"] = $_REQUEST["url"];
            $fields["description"] = $_REQUEST["description"];
            $fields["target"] = $_REQUEST["target"];
            $fields["parent"] = $_REQUEST["parent"];
            $fields["expanded"] = $_REQUEST["expanded"];
            $fields["disabled"] = $_REQUEST["disabled"];
            $fields["order"] = 0;

            if(Jaris\Menus::addItem($_REQUEST["menu"], $fields))
            {
                Jaris\View::addMessage(
                    t("The menu item was successfully created.")
                );

                t("Added menu item '{title}' to '{machine_name}'.");

                Jaris\Logger::info(
                    "Added menu item '{title}' to '{machine_name}'.",
                    array(
                        "title" => $fields["title"],
                        "machine_name" => $_REQUEST["menu"]
                    )
                );
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/menus");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        $parameters["name"] = "add-menu-item";
        $parameters["class"] = "add-menu-item";
        $parameters["action"] = Jaris\Uri::url("admin/menus/add-item");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "menu",
            "value" => $_REQUEST["menu"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "uri",
            "name" => "url",
            "label" => t("Url:"),
            "id" => "url",
            "description" => t("The relative path to access a page, for example: section/page, section or the full url like http://domain.com/section. Leave empty to auto-generate.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "description" => t("Small descriptive popup shown to user on mouse over.")
        );

        $targets[t("New Window")] = "_blank";
        $targets[t("Current Window")] = "_self";
        $targets[t("Parent frameset")] = "_parent";
        $targets[t("Full body of window")] = "_top";

        $fields[] = array(
            "type" => "select",
            "value" => $targets,
            "selected" => $_REQUEST["target"] ? $_REQUEST["target"] : "_self",
            "name" => "target",
            "label" => t("Target:"),
            "id" => "target"
        );

        $menus["&lt;root&gt;"] = "root";

        $menu_items_array = Jaris\Menus::getItemsList($_REQUEST["menu"]);

        if(empty($_REQUEST["menu"]))
        {
            Jaris\View::addMessage(t("Parent menu does not exists."));
            Jaris\Uri::go("admin/menus");
        }

        foreach($menu_items_array as $id => $items)
        {
            $menus[$items["title"]] = "$id";
        }

        $fields[] = array(
            "type" => "select",
            "name" => "parent",
            "selected" => "root",
            "label" => t("Parent:"),
            "id" => "parent",
            "value" => $menus
        );

        $fieldset[] = array("fields" => $fields);

        $fields_expanded[] = array(
            "type" => "checkbox",
            "name" => "expanded",
            "label" => t("Show item elements?:"),
            "id" => "expanded",
            "checked" => false
        );

        $fieldset[] = array(
            "fields" => $fields_expanded,
            "name" => t("Expanded")
        );

        $fields_disabled[] = array(
            "type" => "checkbox",
            "name" => "disabled",
            "label" => t("Disable item?:"),
            "id" => "disabled",
            "checked" => false
        );

        $fieldset[] = array(
            "fields" => $fields_disabled,
            "name" => t("Disabled")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_submit);

        print "<h3>" . t("Menu:") . " " . t($_REQUEST["menu"]) . "</h3>";

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
