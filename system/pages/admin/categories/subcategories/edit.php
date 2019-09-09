<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the edit subcategory page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Subcategory") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_subcategories", "edit_subcategories"]
        );

        if (!isset($_REQUEST["id"]) || !isset($_REQUEST["category"])) {
            Jaris\Uri::go("admin/categories");
        }

        $current_subcategory_data = Jaris\Categories::getSubcategory(
            $_REQUEST["category"],
            intval($_REQUEST["id"])
        );

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-subcategories")
        ) {
            $fields = $current_subcategory_data;

            $fields["title"] = $_REQUEST["title"];
            $fields["description"] = $_REQUEST["description"];
            $fields["order"] = $current_subcategory_data["order"];

            //Checks if client is trying to move a root parent subcategory to
            //its own subcategory and makes subs category root
            if ($fields["parent"] == "root" && $_REQUEST["parent"] != "root") {
                $new_parent_subcategory = Jaris\Categories::getSubcategory(
                    $_REQUEST["category"],
                    intval($_REQUEST["id"])
                );

                if (
                    "" . $new_parent_subcategory["parent"] . "" ==
                    "" . $_REQUEST["id"] . ""
                ) {
                    $new_parent_subcategory["parent"] = "root";

                    Jaris\Categories::editSubcategory(
                        $_REQUEST["category"],
                        $new_parent_subcategory,
                        intval($_REQUEST["id"])
                    );
                }
            }

            $fields["parent"] = $_REQUEST["parent"];

            if (
                Jaris\Categories::editSubcategory(
                    $_REQUEST["category"],
                    $fields,
                    intval($_REQUEST["id"])
                )
            ) {
                Jaris\View::addMessage(
                    t("The subcategory was successfully edited.")
                );

                t("Edited subcategory '{title}' on '{machine_name}'.");

                Jaris\Logger::info(
                    "Edited subcategory '{title}' on '{machine_name}'.",
                    [
                        "title" => $fields["title"],
                        "machine_name" => $_REQUEST["category"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/categories/subcategories",
                ["category" => $_REQUEST["category"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                "admin/categories/subcategories",
                ["category" => $_REQUEST["category"]]
            );
        }

        $subcategories["&lt;root&gt;"] = "root";

        $subcategories_array = Jaris\Categories::getSubcategories(
            $_REQUEST["category"]
        );

        foreach ($subcategories_array as $id => $items) {
            if ($id != $_REQUEST["id"]) {
                $subcategories[$items["title"]] = "$id";
            }
        }

        $parameters["name"] = "edit-subcategories";
        $parameters["class"] = "edit-subcategories";
        $parameters["action"] = Jaris\Uri::url("admin/categories/subcategories/edit");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "category",
            "value" => $_REQUEST["category"]
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "value" => $current_subcategory_data["title"],
            "required" => true
        ];

        $fields[] = [
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "value" => $current_subcategory_data["description"]
        ];

        $fields[] = [
            "type" => "select",
            "name" => "parent",
            "selected" => trim($current_subcategory_data["parent"]),
            "label" => t("Parent:"),
            "id" => "parent",
            "value" => $subcategories
        ];

        $fieldset[] = ["fields" => $fields];

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
