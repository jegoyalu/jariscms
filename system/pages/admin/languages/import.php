<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the language import page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Import POT File") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_languages", "edit_languages"]
        );

        //Prevent importing non existing language code
        if (!isset($_REQUEST["code"])) {
            Jaris\Uri::go("admin/languages");
        }

        $lang_code = $_REQUEST["code"];

        if (
            !isset($_REQUEST["btnUpload"]) &&
            !isset($_REQUEST["btnCancel"])
        ) {
            Jaris\View::addMessage(
                t("Here you can update language strings by uploading a po translation file.")
            );
        }

        if (
            isset($_REQUEST["btnUpload"]) &&
            !Jaris\Forms::requiredFieldEmpty("language-import")
        ) {
            if ("" . stristr($_FILES["po_file"]["type"], ".po") . "" == "") {
                $main_file = Jaris\Site::dataDir() . "language/strings.po";

                $language_file = Jaris\Site::dataDir() .
                    "language/" . $lang_code . "/" . "strings.po"
                ;

                //Parse the uploaded file
                $new_strings = Jaris\Language::poParse($_FILES["po_file"]["tmp_name"]);

                //First update empty strings pot file
                $empty_strings = Jaris\Language::poParse($main_file);
                foreach ($new_strings as $original => $translation) {
                    if (!isset($empty_strings[$original])) {
                        $empty_strings[$original] = "";
                    }
                }

                Jaris\Language::poWrite($empty_strings, $main_file);

                if ($_REQUEST["option"] == "insert_new") {
                    $count_new = 0;
                    $language_strings = Jaris\Language::poParse($language_file);
                    foreach ($new_strings as $original => $translation) {
                        if (!isset($language_strings[$original])) {
                            $language_strings[$original] = $translation;
                            $count_new++;
                        }
                    }

                    Jaris\Language::poWrite($language_strings, $language_file);

                    Jaris\View::addMessage(
                        t("Imported a total of") .
                        " <b>$count_new</b> " .
                        t("new strings")
                    );
                } elseif ($_REQUEST["option"] == "update_all") {
                    $count_new = 0;
                    $count_updated = 0;
                    $language_strings = Jaris\Language::poParse($language_file);
                    foreach ($new_strings as $original => $translation) {
                        if (!isset($language_strings[$original])) {
                            $language_strings[$original] = $translation;
                            $count_new++;
                        } else {
                            $language_strings[$original] = $translation;
                            $count_updated++;
                        }
                    }

                    Jaris\Language::poWrite($language_strings, $language_file);

                    Jaris\View::addMessage(
                        t("Imported a total of") .
                        " <b>$count_new</b> " .
                        t("new strings and updated a total of") .
                        " <b>$count_updated</b> " .
                        t("strings")
                    );

                    t("Imported strings for language '{code}'.");

                    Jaris\Logger::info(
                        "Imported strings for language '{code}'.",
                        [
                            "code" => $_REQUEST["code"]
                        ]
                    );
                }

                Jaris\Uri::go(
                    "admin/languages/edit",
                    ["code" => $lang_code]
                );
            } else {
                Jaris\View::addMessage(
                    t("The uploaded file is not supported."),
                    "error"
                );

                Jaris\Uri::go(
                    "admin/languages/import",
                    ["code" => $lang_code]
                );
            }
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/languages/edit", ["code" => $lang_code]);
        }

        $parameters["name"] = "language-import";
        $parameters["class"] = "language-import";
        $parameters["action"] = Jaris\Uri::url("admin/languages/import");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = [
            "type" => "hidden",
            "name" => "code",
            "value" => $lang_code
        ];

        $fields[] = [
            "type" => "file",
            "label" => t("PO file:"),
            "name" => "po_file",
            "id" => "po_file",
            "valid_types" => "po",
            "description" => t("A po translation file to import into current translations.")
        ];

        $fieldset[] = ["fields" => $fields];

        $options[t("Just insert new strings")] = "insert_new";
        $options[t("Update and insert new strings")] = "update_all";

        $options_fields[] = [
            "type" => "radio",
            "name" => "option",
            "id" => "option",
            "value" => $options,
            "checked" => "insert_new"
        ];

        $fieldset[] = [
            "name" => t("Import method"),
            "fields" => $options_fields,
            "collapsible" => true,
            "collapsed" => false
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnUpload",
            "value" => t("Upload")
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
