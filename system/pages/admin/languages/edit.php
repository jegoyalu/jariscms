<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the language edit strings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit language strings") ?>
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

        $lang_code = $_REQUEST["code"];

        $current_page = $_REQUEST["current_page"] ?
            $_REQUEST["current_page"] : 1
        ;

        if(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                "admin/languages/edit",
                array("code" => $lang_code, "current_page" => $current_page)
            );
        }

        //Add string form
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "add")
        {
            $parameters["name"] = "add-language-string";
            $parameters["class"] = "add-language-string";
            $parameters["action"] = Jaris\Uri::url("admin/languages/edit");
            $parameters["method"] = "get";

            $fields[] = array(
                "type" => "hidden",
                "value" => "save_new",
                "name" => "action"
            );

            $fields[] = array(
                "type" => "hidden",
                "value" => $current_page,
                "name" => "current_page"
            );

            $fields[] = array(
                "type" => "hidden",
                "value" => $lang_code,
                "name" => "code"
            );

            $fields[] = array(
                "type" => "text",
                "code" => "style=\"width: 100%\"",
                "name" => "original",
                "label" => t("Original text:"),
                "id" => "original",
                "required" => true,
                "description" => t("Original string to translate.")
            );

            $fields[] = array(
                "type" => "text",
                "code" => "style=\"width: 100%\"",
                "name" => "translation",
                "label" => t("Translation:"),
                "id" => "translation",
                "required" => true,
                "description" => t("Meaning of the original text in this language.")
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
        }

        //Edit exisiting string form
        elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "edit")
        {
            //Get and sort strings
            $strings_sorted = Jaris\Language::getStrings($lang_code);
            $strings_sorted = Jaris\Data::sort($strings_sorted, "original");
            $strings = array();
            $string_index = 0;
            foreach($strings_sorted as $string_fields)
            {
                $strings[$string_index] = $string_fields;
                $string_index++;
            }

            $position = $_REQUEST["position"];

            $parameters["name"] = "edit-language-string";
            $parameters["class"] = "edit-language-string";
            $parameters["action"] = Jaris\Uri::url("admin/languages/edit#$position");
            $parameters["method"] = "get";

            $fields[] = array(
                "type" => "hidden",
                "value" => "save_changes",
                "name" => "action"
            );

            $fields[] = array(
                "type" => "hidden",
                "value" => $current_page,
                "name" => "current_page"
            );

            $fields[] = array(
                "type" => "hidden",
                "value" => $lang_code,
                "name" => "code"
            );

            $fields[] = array(
                "type" => "hidden",
                "value" => $position,
                "name" => "position"
            );

            $fields[] = array(
                "type" => "text",
                "code" => "style=\"width: 100%\"",
                "value" => $strings[$position]["original"],
                "name" => "original",
                "label" => t("Original text:"),
                "id" => "original",
                "readonly" => true,
                "description" => t("Original string to translate.")
            );

            $fields[] = array(
                "type" => "text",
                "code" => "style=\"width: 100%\"",
                "value" => $strings[$position]["translation"],
                "name" => "translation",
                "label" => t("Translation:"),
                "id" => "translation",
                "description" => t("Meaning of the original text in this language.")
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
        }

        //Save new string function
        elseif(
            isset($_REQUEST["action"]) &&
            $_REQUEST["action"] == "save_new" &&
            !Jaris\Forms::requiredFieldEmpty("add-language-string")
        )
        {
            $original = $_REQUEST["original"];
            $translation = $_REQUEST["translation"];

            if(isset($_REQUEST["btnSave"]))
            {
                if(Jaris\Language::addString($lang_code, $original, $translation))
                {
                    Jaris\View::addMessage(t("Changes successfully saved."));
                }
                else
                {
                    Jaris\View::addMessage(
                        t("Check your write permissions on the <b>language</b> directory."),
                        "error"
                    );
                }
            }

            $_REQUEST["action"] = null;
        }

        //Save modified string function
        elseif(
            isset($_REQUEST["action"]) &&
            $_REQUEST["action"] == "save_changes"
        )
        {
            //Get and sort strings
            $strings_sorted = Jaris\Language::getStrings($lang_code);
            $strings_sorted = Jaris\Data::sort($strings_sorted, "original");
            $strings = array();
            $string_index = 0;
            foreach($strings_sorted as $string_fields)
            {
                $strings[$string_index] = $string_fields;
                $string_index++;
            }

            $position = $_REQUEST["position"];
            $translation = $_REQUEST["translation"];

            if(isset($_REQUEST["btnSave"]))
            {
                if(
                    Jaris\Language::addString(
                        $lang_code,
                        $strings[$position]["original"],
                        $translation
                    )
                )
                {
                    Jaris\View::addMessage(t("Changes successfully saved."));
                }
                else
                {
                    Jaris\View::addMessage(
                        t("Check your write permissions on the <b>language</b> directory."),
                        "error"
                    );
                }
            }

            $_REQUEST["action"] = null;
        }

        //Delete exisiting string form
        elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete")
        {
            //Get and sort strings
            $strings_sorted = Jaris\Language::getStrings($lang_code);
            $strings_sorted = Jaris\Data::sort($strings_sorted, "original");
            $strings = array();
            $string_index = 0;
            foreach($strings_sorted as $string_fields)
            {
                $strings[$string_index] = $string_fields;
                $string_index++;
            }

            $position = $_REQUEST["position"];

            print "<form class=\"delete-language-string\" method=\"post\" action=\"" . Jaris\Uri::url("admin/languages/edit") . "\">";
            print "<input type=\"hidden\" name=\"action\" value=\"delete_now\" />";
            print "<input type=\"hidden\" name=\"current_page\" value=\"$current_page\" />";
            print "<input type=\"hidden\" name=\"code\" value=\"$lang_code\" />";
            print "<input type=\"hidden\" name=\"position\" value=\"$position\" />";
            print "<br />";
            print "<div>";
            print t("Are you sure you want to delete the string?");
            print "<div><b>" . t("Original string:") . " " . $strings[$position]["original"] . "</b></div>";
            print "</div>";
            print "<input class=\"form-submit\" type=\"submit\" name=\"btnYes\" value=\"" . t("Yes") . "\" />";
            print "<input class=\"form-submit\" type=\"submit\" name=\"btnNo\" value=\"" . t("No") . "\" />";
            print "</form>";
        }

        //Delete exisiting string function
        elseif(
            isset($_REQUEST["action"]) &&
            $_REQUEST["action"] == "delete_now"
        )
        {
            //Get and sort strings
            $strings_sorted = Jaris\Language::getStrings($lang_code);
            $strings_sorted = Jaris\Data::sort($strings_sorted, "original");
            $strings = array();
            $string_index = 0;
            foreach($strings_sorted as $string_fields)
            {
                $strings[$string_index] = $string_fields;
                $string_index++;
            }

            $position = $_REQUEST["position"];

            if(isset($_REQUEST["btnYes"]))
            {
                if(Jaris\Language::deleteString($lang_code, $strings[$position]["original"]))
                {
                    Jaris\View::addMessage(t("String successfully removed."));
                }
                else
                {
                    Jaris\View::addMessage(
                        t("Check your write permissions on the <b>language</b> directory."),
                        "error"
                    );
                }
            }

            $_REQUEST["action"] = null;
        }


        //Print list of strings
        if(!isset($_REQUEST["action"]))
        {
            Jaris\View::addTab(
                t("Add string"),
                "admin/languages/edit",
                array("action" => "add", "code" => $lang_code)
            );

            Jaris\View::addTab(
                t("Import strings"),
                "admin/languages/import",
                array("code" => $lang_code)
            );

            $amount_translated = Jaris\Language::amountTranslated($lang_code);

            //Display amount of strings translated.
            print "<div class=\"total-translated\">\n";
            print "<span>" . t("Translated:") . "</span>" . " ";
            print $amount_translated["translated_strings"] . "<br />\n";
            print "<span>" . t("Total system strings:") . "</span>" . " ";
            print $amount_translated["total_strings"] . "<br />\n";
            print "<span>" . t("Percent translated:") . "</span>" . " ";
            print $amount_translated["percent"] . "%\n";
            print "</div>";

            //Get and sort strings
            $strings_sorted = Jaris\Language::getStrings($lang_code);
            $strings_sorted = Jaris\Data::sort($strings_sorted, "original");
            $strings = array();
            $string_index = 0;
            foreach($strings_sorted as $string_fields)
            {
                $strings[$string_index] = $string_fields;
                $string_index++;
            }
            $strings_amount = count($strings);
            $strings_per_page = 20;

            $page_count = ceil($strings_amount / $strings_per_page);

            print "<br />\n";

            //Print page navigation
            $previous_page = $current_page > 1 ?
                "<a href=\"" . Jaris\Uri::url(
                    "admin/languages/edit",
                    array(
                        "code" => $lang_code,
                        "current_page" => $current_page - 1
                    )
                ) . "\"> &lt;&lt; " . t("Previous") . "</a>"
                :
                ""
            ;

            $next_page = $current_page >= 1 && $current_page != $page_count ?
                "<a href=\"" . Jaris\Uri::url(
                    "admin/languages/edit",
                    array(
                        "code" => $lang_code,
                        "current_page" => $current_page + 1
                    )
                ) . "\">" . t("Next") . " &gt;&gt;</a>"
                :
                ""
            ;

            print "<div class=\"language-navigation\" />\n";
            print "<div style=\"float: left\">" . $previous_page . "</div>\n";
            print "<div style=\"float: right\">" . $next_page . "</div>\n";
            print "</div>\n";

            print "<br />\n";

            //Print available strings
            print "<table class=\"languages-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Original") . "</td>\n";
            print "<td>" . t("Translation") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";

            print "</tr></thead>\n";

            if($current_page > 1)
            {
                for(
                    $i = ($current_page - 1) * $strings_per_page;
                    $i < ($current_page - 1) * $strings_per_page + 20 && $i < $strings_amount;
                    $i++
                )
                {
                    print "<tr>\n";

                    print "<td>";
                    print "<a name=\"$i\">" . $strings[$i]["original"] . "</a>";
                    print "</td>\n";

                    print "<td>" . $strings[$i]["translation"] . "</td>\n";

                    $edit_url = Jaris\Uri::url(
                        "admin/languages/edit",
                        array(
                            "code" => $lang_code,
                            "action" => "edit",
                            "position" => $i,
                            "current_page" => $current_page
                        )
                    );

                    $edit_text = t("Edit");

                    $delete_url = Jaris\Uri::url(
                        "admin/languages/edit",
                        array(
                            "code" => $lang_code,
                            "action" => "delete",
                            "position" => $i,
                            "current_page" => $current_page
                        )
                    );

                    $delete_text = t("Delete");

                    print "<td>";
                    print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                    print "<a href=\"$delete_url\">$delete_text</a>";
                    print "</td>\n";

                    print "</tr>\n";
                }
            }
            else
            {
                for($i = 0; $i < $strings_per_page; $i++)
                {
                    if(!isset($strings[$i]))
                        continue;

                    print "<tr>\n";

                    print "<td>";
                    print "<a name=\"$i\">" . $strings[$i]["original"] . "</a>";
                    print "</td>\n";

                    print "<td>" . $strings[$i]["translation"] . "</td>\n";

                    $edit_url = Jaris\Uri::url(
                        "admin/languages/edit",
                        array(
                            "code" => $lang_code,
                            "action" => "edit",
                            "position" => $i,
                            "current_page" => $current_page
                        )
                    );

                    $edit_text = t("Edit");

                    $delete_url = Jaris\Uri::url(
                        "admin/languages/edit",
                        array(
                            "code" => $lang_code,
                            "action" => "delete",
                            "position" => $i,
                            "current_page" => $current_page
                        )
                    );

                    $delete_text = t("Delete");

                    print "<td>";
                    print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                    print "<a href=\"$delete_url\">$delete_text</a>";
                    print "</td>\n";

                    print "</tr>\n";
                }
            }

            print "</table>\n";

            print "<br />\n";

            print "<center>";
            print "<form action=\"" . Jaris\Uri::url("admin/languages/edit") . "\" method=\"get\">";
            print "<input type=\"hidden\" name=\"code\" value=\"$lang_code\" />";
            print t("Goto Page:") . " <select name=\"current_page\">";

            for($i = 1; $i <= $page_count; $i++)
            {
                $selected = "";
                if($current_page == $i)
                {
                    $selected = "selected";
                }

                print "<option $selected value=\"$i\">$i</option>";
            }

            print "</select>";
            print "<input type=\"submit\" value=\"" . t("Go") . "\" />";
            print "</form>";
            print "</center>";

            //Print page navigation
            $previous_page = $current_page > 1 ?
                "<a href=\"" . Jaris\Uri::url(
                    "admin/languages/edit",
                    array(
                        "code" => $lang_code,
                        "current_page" => $current_page - 1
                    )
                ) . "\"> &lt;&lt; " . t("Previous") . "</a>"
                :
                ""
            ;

            $next_page = $current_page >= 1 && $current_page != $page_count ?
                "<a href=\"" . Jaris\Uri::url(
                    "admin/languages/edit",
                    array(
                        "code" => $lang_code,
                        "current_page" => $current_page + 1
                    )
                ) . "\">" . t("Next") . " &gt;&gt;</a>"
                :
                ""
            ;

            print "<div class=\"language-navigation\" />\n";
            print "<div style=\"float: left\">" . $previous_page . "</div>\n";
            print "<div style=\"float: right\">" . $next_page . "</div>\n";
            print "</div>\n";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
