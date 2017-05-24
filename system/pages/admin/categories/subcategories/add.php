<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the subcategories add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Subcategory") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_subcategories", "add_subcategories")
        );

        if(!isset($_REQUEST["category"]))
        {
            Jaris\Uri::go("admin/categories");
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-subcategory")
        )
        {
            $fields["title"] = $_REQUEST["title"];
            $fields["description"] = $_REQUEST["description"];
            $fields["parent"] = $_REQUEST["parent"];
            $fields["order"] = 0;

            if(Jaris\Categories::addSubcategory($_REQUEST["category"], $fields))
            {
                Jaris\View::addMessage(
                    t("The subcategory was successfully created.")
                );

                t("Added subcategory '{title}' on '{machine_name}'.");

                Jaris\Logger::info(
                    "Added subcategory '{title}' on '{machine_name}'.",
                    array(
                        "title" => $fields["title"],
                        "machine_name" => $_REQUEST["category"]
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

            Jaris\Uri::go(
                "admin/categories/subcategories",
                array("category" => $_REQUEST["category"])
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                "admin/categories/subcategories",
                array("category" => $_REQUEST["category"])
            );
        }

        $parameters["name"] = "add-subcategory";
        $parameters["class"] = "add-subcategory";
        $parameters["action"] = Jaris\Uri::url("admin/categories/subcategories/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "category",
            "value" => $_REQUEST["category"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "value" => isset($_REQUEST["title"]) ?
                $_REQUEST["title"] : "",
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "label" => t("Description:"),
            "id" => "description"
        );

        $subcategories["&lt;root&gt;"] = "root";

        $subcategories_array = Jaris\Categories::getSubcategories(
            $_REQUEST["category"]
        );

        if($subcategories_array)
        {
            foreach($subcategories_array as $id => $items)
            {
                $subcategories[$items["title"]] = "$id";
            }
        }

        $fields[] = array(
            "type" => "select",
            "name" => "parent",
            "selected" => "root",
            "label" => t("Parent:"),
            "id" => "parent",
            "value" => $subcategories
        );

        $fieldset[] = array("fields" => $fields);

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

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
