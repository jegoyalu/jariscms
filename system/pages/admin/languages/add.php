<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the language add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Language") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_languages", "add_languages")
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-language")
        )
        {
            if(!isset($_REQUEST["code"]))
            {
                Jaris\Uri::go("admin/languages");
            }

            $languages = Jaris\Language::getCodes();

            foreach($languages as $name => $code)
            {
                if($code == $_REQUEST["code"])
                {
                    $_REQUEST["name"] = $name;
                    break;
                }
            }

            if(
                Jaris\Language::add(
                    $_REQUEST["code"],
                    $_REQUEST["name"],
                    $_REQUEST["translator"],
                    $_REQUEST["translator_email"],
                    $_REQUEST["contributors"]
                )
            )
            {
                Jaris\View::addMessage(
                    t("The language was successfully created.")
                );

                t("Added language '{code}'.");

                Jaris\Logger::info(
                    "Added language '{code}'.",
                    array(
                        "code" => $_REQUEST["code"]
                    )
                );

                Jaris\Uri::go("admin/languages");
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/languages");
        }

        $parameters["name"] = "add-language";
        $parameters["class"] = "add-language";
        $parameters["action"] = Jaris\Uri::url("admin/languages/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "select",
            "value" => Jaris\Language::getCodes(),
            "name" => "code",
            "label" => t("Language:"),
            "id" => "code",
            "description" => t("Select the language you want to add to the system."),
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "translator",
            "label" => t("Translator:"),
            "id" => "translator",
            "description" => t("Main translator for this language.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "translator_email",
            "label" => t("E-mail:"),
            "id" => "translator_email",
            "description" => t("E-mail of the main translator.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "contributors",
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
