<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu rename page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Rename Menu") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_menus", "edit_menus"]);

        if (!isset($_REQUEST["current_name"])) {
            Jaris\Uri::go("admin/menus");
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("rename-menu")
        ) {
            $message = Jaris\Menus::rename(
                $_REQUEST["current_name"],
                $_REQUEST["new_name"]
            );

            if ($message == "true") {
                //If it is  primary or secondary menu change main config also.
                if (
                    Jaris\Settings::get("primary_menu", "main") ==
                    $_REQUEST["current_name"]
                ) {
                    Jaris\Settings::save(
                        "primary_menu",
                        $_REQUEST["new_name"],
                        "main"
                    );
                } elseif (
                    Jaris\Settings::get("secondary_menu", "main") ==
                    $_REQUEST["current_name"]
                ) {
                    Jaris\Settings::save(
                        "secondary_menu",
                        $_REQUEST["new_name"],
                        "main"
                    );
                }

                //update the menu block
                $block = Jaris\Blocks::getByField(
                    "menu_name",
                    $_REQUEST["current_name"]
                );

                $block["menu_name"] = $_REQUEST["new_name"];

                $block["description"] = $_REQUEST["new_name"] . " menu";

                $block["content"] = "<?php\nprint Jaris\View::getLinksHTML(Jaris\Data::sort(Jaris\Menus::getChildItems(\"{$_REQUEST['new_name']}\"),\"order\"), \"{$_REQUEST['new_name']}\");\n?>";

                Jaris\Blocks::editByField(
                    "menu_name",
                    $_REQUEST["current_name"],
                    $block
                );

                Jaris\View::addMessage(t("Menu successfully renamed."));

                t("Renamed menu '{machine_name}' to '{new_machine_name}'.");

                Jaris\Logger::info(
                    "Renamed menu '{machine_name}' to '{new_machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["current_name"],
                        "new_machine_name" => $_REQUEST["new_name"]
                    ]
                );
            } else {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/menus");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/menus");
        }

        $parameters["name"] = "rename-menu";
        $parameters["class"] = "rename-menu";
        $parameters["action"] = Jaris\Uri::url("admin/menus/rename");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "value" => $_REQUEST["current_name"],
            "name" => "current_name"
        ];

        $fields[] = [
            "type" => "text",
            "value" => $_REQUEST["current_name"],
            "name" => "new_name",
            "label" => t("New name:"),
            "id" => "new_name",
            "description" => t("A machine readable name. For example: my-menu"),
            "required" => true
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
