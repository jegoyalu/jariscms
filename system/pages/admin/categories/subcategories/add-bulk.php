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
        <?php print t("Add Subcategories") ?>
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
            !Jaris\Forms::requiredFieldEmpty("add-bulk-subcategory")
        )
        {
            $subcategories = explode("\n", $_REQUEST["subcategories"]);

            foreach($subcategories as $subcategory)
            {
                $subcategory = trim(Jaris\Util::stripHTMLTags($subcategory));

                if($subcategory == "")
                    continue;

                $fields = array(
                    "title" => $subcategory,
                    "description" => "",
                    "parent" => "root",
                    "order" => 0
                );

                if(Jaris\Categories::addSubcategory($_REQUEST["category"], $fields))
                {
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

                    Jaris\Uri::go(
                        "admin/categories/subcategories",
                        array("category" => $_REQUEST["category"])
                    );
                }
            }

            Jaris\View::addMessage(
                t("The subcategories were successfully created.")
            );

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

        $parameters["name"] = "add-bulk-subcategory";
        $parameters["class"] = "add-bulk-subcategory";
        $parameters["action"] = Jaris\Uri::url("admin/categories/subcategories/add-bulk");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "category",
            "value" => $_REQUEST["category"]
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "subcategories",
            "value" => isset($_REQUEST["subcategories"]) ?
                $_REQUEST["subcategories"] : "",
            "label" => t("Subcategories:"),
            "description" => "Add one subcategory per line.",
            "required" => true
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
