<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the categories add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Category") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_categories", "add_categories"]
        );

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-category")
        ) {
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["multiple"] = $_REQUEST["multiple"];
            $fields["sorting"] = $_REQUEST["sorting"];
            $fields["display_subcategories"] = $_REQUEST["display_subcategories"];
            $fields["order"] = 0;

            $message = Jaris\Categories::add($_REQUEST["machine_name"], $fields);

            if ($message == "true") {
                Jaris\View::addMessage(
                    t("The category has been successfully created.")
                );

                t("Added category '{machine_name}'.");

                Jaris\Logger::info(
                    "Added category '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["machine_name"]
                    ]
                );
            } else {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/categories");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/categories");
        }

        $parameters["name"] = "add-category";
        $parameters["class"] = "add-category";
        $parameters["action"] = Jaris\Uri::url("admin/categories/add");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["machine_name"]) ?
                $_REQUEST["machine_name"] : "",
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine_name",
            "required" => true,
            "description" => t("A readable machine name, like for example: my-category.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["name"]) ?
                $_REQUEST["name"] : "",
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Category.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the category.")
        ];

        $fieldset[] = ["fields" => $fields];

        $fields_multiple[] = [
            "type" => "checkbox",
            "name" => "multiple",
            "label" => t("Enable multiple selection?:"),
            "id" => "multiple"
        ];

        $fieldset[] = [
            "fields" => $fields_multiple,
            "name" => t("Multiple")
        ];

        $fields_sorting[] = [
            "type" => "checkbox",
            "name" => "sorting",
            "label" => t("Enable subcategory name sorting?:"),
            "id" => "sorting"
        ];

        $fieldset[] = [
            "fields" => $fields_sorting,
            "name" => t("Subcategory sorting"),
            "description" => t("To enable or disable automatic sorting.")
        ];

        $fields_block[] = [
            "type" => "checkbox",
            "name" => "display_subcategories",
            "label" => t("Display subcategories?:"),
            "description" => t("Display all the subcategories on the generated menu, otherwise it will display only the root subcategories."),
            "id" => "display_subcategories"
        ];

        $fieldset[] = [
            "fields" => $fields_block,
            "name" => t("Block options"),
            "description" => t("Options that apply to the generated menu block for this category.")
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_submit];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
