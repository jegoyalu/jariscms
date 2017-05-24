<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Menu") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_menus", "add_menus"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-menu")
        )
        {
            $message = Jaris\Menus::add($_REQUEST["menu_name"]);

            if($message == "true")
            {
                //Create block for the menu
                $menu_block["description"] = $_REQUEST["menu_name"] . " " . "menu";
                $menu_block["title"] = $_REQUEST["menu_name"] . " " . "menu";

                $menu_block["content"] = "<?php\nprint ";
                $menu_block["content"] .= "Jaris\View::getLinksHTML(";
                $menu_block["content"] .= "Jaris\Data::sort(";
                $menu_block["content"] .= "Jaris\Menus::getChildItems(";
                $menu_block["content"] .= "\"{$_REQUEST['menu_name']}\"";
                $menu_block["content"] .= "), \"order\"";
                $menu_block["content"] .= "), ";
                $menu_block["content"] .= "\"{$_REQUEST['menu_name']}\"";
                $menu_block["content"] .= ")";
                $menu_block["content"] .= ";\n?>";

                $menu_block["order"] = "0";
                $menu_block["display_rule"] = "all_except_listed";
                $menu_block["pages"] = "";
                $menu_block["return"] = "";
                $menu_block["is_system"] = "1";
                $menu_block["menu_name"] = $_REQUEST["menu_name"];

                Jaris\Blocks::add($menu_block, "none");

                Jaris\View::addMessage(t("Menu successfully created."));

                t("Added menu '{machine_name}'.");

                Jaris\Logger::info(
                    "Added menu '{machine_name}'.",
                    array(
                        "machine_name" => $_REQUEST["menu_name"]
                    )
                );
            }
            else
            {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/menus");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        $parameters["name"] = "add-menu";
        $parameters["class"] = "add-menu";
        $parameters["action"] = Jaris\Uri::url("admin/menus/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "menu_name",
            "label" => t("Name:"),
            "id" => "menu_name",
            "description" => t("A machine readable name. For example: my-menu"),
            "required" => true
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
