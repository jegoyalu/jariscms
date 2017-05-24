<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the language edit info page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Language Details") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_languages", "edit_languages")
        );

        //Prevent editing non existing language code
        if(!isset($_REQUEST["code"]) || trim($_REQUEST["code"]) == "")
        {
            Jaris\Uri::go("admin/languages");
        }

        $language_details = Jaris\Language::getInfo($_REQUEST["code"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-language")
        )
        {
            if(
                Jaris\Language::edit(
                    $_REQUEST["code"],
                    $_REQUEST["translator"],
                    $_REQUEST["translator_email"],
                    $_REQUEST["contributors"]
                )
            )
            {
                Jaris\View::addMessage(
                    t("The language was successfully modified.")
                );

                t("Edited language '{code}' info.");

                Jaris\Logger::info(
                    "Edited language '{code}' info.",
                    array(
                        "code" => $_REQUEST["code"]
                    )
                );
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_language"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/languages");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/languages");
        }

        $parameters["name"] = "edit-language";
        $parameters["class"] = "edit-language";
        $parameters["action"] = Jaris\Uri::url("admin/languages/edit-info");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "code",
            "value" => $_REQUEST["code"],
            "label" => t("Code:"),
            "id" => "code",
            "readonly" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "name",
            "value" => $language_details["name"],
            "label" => t("Name:"),
            "id" => "name",
            "readonly" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "translator",
            "value" => $language_details["translator"],
            "label" => t("Translator:"),
            "id" => "translator",
            "description" => t("Main translator for this language.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "translator_email",
            "value" => $language_details["translator_email"],
            "label" => t("E-mail:"),
            "id" => "translator_email",
            "description" => t("E-mail of the main translator.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "contributors",
            "value" => $language_details["contributors"],
            "label" => t("Contributors:"),
            "id" => "contributors",
            "description" => t("A list of contributors seperated by a new line for this translation.")
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
