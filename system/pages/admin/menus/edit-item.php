<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu edit item page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Menu Item") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_menus", "edit_menu_items")
        );

        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["menu"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        $current_menu_data = Jaris\Menus::getItem(
            $_REQUEST["id"],
            $_REQUEST["menu"]
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-menu-item")
        )
        {
            if(trim($_REQUEST["url"]) == "")
            {
                $_REQUEST["url"] = Jaris\Uri::fromText($_REQUEST["title"]);
            }

            $fields = $current_menu_data;

            $fields["title"] = $_REQUEST["title"];
            $fields["url"] = $_REQUEST["url"];
            $fields["description"] = $_REQUEST["description"];
            $fields["target"] = $_REQUEST["target"];
            $fields["order"] = $current_menu_data["order"];
            $fields["expanded"] = $_REQUEST["expanded"];
            $fields["disabled"] = $_REQUEST["disabled"];

            //Checks if client is trying to move a root parent menu
            //to its own submenu and makes subs menu root menu
            if($fields["parent"] == "root" && $_REQUEST["parent"] != "root")
            {
                $new_parent_item = Jaris\Menus::getItem(
                    $_REQUEST["parent"],
                    $_REQUEST["menu"]
                );

                if(
                    "" . $new_parent_item["parent"] . "" ==
                    "" . $_REQUEST["id"] . ""
                )
                {
                    $new_parent_item["parent"] = "root";

                    Jaris\Menus::editItem(
                        $_REQUEST["parent"],
                        $_REQUEST["menu"],
                        $new_parent_item
                    );
                }
            }

            $fields["parent"] = $_REQUEST["parent"];

            if(
                Jaris\Menus::editItem(
                    $_REQUEST["id"],
                    $_REQUEST["menu"],
                    $fields
                )
            )
            {
                Jaris\View::addMessage(
                    t("The menu item was successfully edited.")
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

        $menus["&lt;root&gt;"] = "root";

        $menu_items_array = Jaris\Menus::getItemsList($_REQUEST["menu"]);

        foreach($menu_items_array as $id => $items)
        {
            if($id != $_REQUEST["id"])
            {
                $menus[$items["title"]] = "$id";
            }
        }

        $parameters["name"] = "edit-menu-item";
        $parameters["class"] = "edit-menu-item";
        $parameters["action"] = Jaris\Uri::url("admin/menus/edit-item");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

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
            "value" => $current_menu_data["title"],
            "required" => true
        );

        $fields[] = array(
            "type" => "uri",
            "name" => "url",
            "label" => t("Url:"),
            "id" => "url",
            "value" => $current_menu_data["url"],
            "description" => t("The relative path to access a page, for example: section/page, section or the full url like http://domain.com/section. Leave empty to auto-generate.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "value" => $current_menu_data["description"],
            "description" => t("Small descriptive popup shown to user on mouse over.")
        );

        $targets[t("New Window")] = "_blank";
        $targets[t("Current Window")] = "_self";
        $targets[t("Parent frameset")] = "_parent";
        $targets[t("Full body of window")] = "_top";

        $fields[] = array(
            "type" => "select",
            "value" => $targets,
            "selected" => isset($_REQUEST["target"]) ?
                $_REQUEST["target"]
                :
                $current_menu_data["target"],
            "name" => "target",
            "label" => t("Target:"),
            "id" => "target"
        );

        $fields[] = array(
            "type" => "select",
            "name" => "parent",
            "selected" => trim($current_menu_data["parent"]),
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
            "checked" => $current_menu_data["expanded"]
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
            "checked" => $current_menu_data["disabled"]
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