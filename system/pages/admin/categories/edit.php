<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the categories edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Category") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_categories", "edit_categories"]
        );

        if (!isset($_REQUEST["category"])) {
            Jaris\Uri::go("admin/categories");
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-category")
        ) {
            $fields = Jaris\Categories::get($_REQUEST["category"]);

            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];
            $fields["multiple"] = $_REQUEST["multiple"];
            $fields["sorting"] = $_REQUEST["sorting"];
            $fields["display_subcategories"] = $_REQUEST["display_subcategories"];

            if (Jaris\Categories::edit($_REQUEST["category"], $fields)) {
                Jaris\View::addMessage(t("Your changes have been saved."));

                t("Edited category '{machine_name}'.");

                Jaris\Logger::info(
                    "Edited category '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["category"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/categories");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/categories");
        }

        $category_data = Jaris\Categories::get($_REQUEST["category"]);

        $parameters["name"] = "edit-category";
        $parameters["class"] = "edit-category";
        $parameters["action"] = Jaris\Uri::url("admin/categories/edit");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "value" => $_REQUEST["category"],
            "name" => "category"
        ];

        $fields[] = [
            "type" => "text",
            "readonly" => true,
            "value" => $_REQUEST["category"],
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine-name",
            "description" => t("The machine name of the category.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => $category_data["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Category.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => $category_data["description"],
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
            "id" => "multiple",
            "checked" => $category_data["multiple"]
        ];

        $fieldset[] = [
            "fields" => $fields_multiple,
            "name" => t("Multiple")
        ];

        $fields_sorting[] = [
            "type" => "checkbox",
            "name" => "sorting",
            "label" => t("Enable subcategory name sorting?:"),
            "id" => "sorting",
            "checked" => $category_data["sorting"]
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
            "id" => "display_subcategories",
            "description" => t("Display all the subcategories on the generated menu, otherwise it will display only the root subcategories."),
            "checked" => $category_data["display_subcategories"]
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
