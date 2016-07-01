<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * @note File that stores all hook functions.
 */

$display_simplemde_on_current_page = false;

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        global $display_simplemde_on_current_page;

        $textarea_id = unserialize(Jaris\Settings::get("teaxtarea_id", "simplemde"));
        $forms_to_display = unserialize(Jaris\Settings::get("forms", "simplemde"));
        $groups = unserialize(Jaris\Settings::get("groups", "simplemde"));
        $disable_editor = unserialize(Jaris\Settings::get("disable_editor", "simplemde"));

        if(!is_array($textarea_id))
            $textarea_id = array();

        if(!is_array($forms_to_display))
            $forms_to_display = array();

        if(!is_array($groups))
            $groups = array();

        if(!is_array($disable_editor))
            $disable_editor = array();

        if(!$textarea_id[Jaris\Authentication::currentUserGroup()])
        {
            $textarea_id[Jaris\Authentication::currentUserGroup()] = "content,pre_content,sub_content";
        }
        else
        {
            $textarea_id[Jaris\Authentication::currentUserGroup()] = explode(
                ",",
                $textarea_id[Jaris\Authentication::currentUserGroup()]
            );
        }

        if(!$forms_to_display[Jaris\Authentication::currentUserGroup()])
        {
            $forms_to_display[] = "add-page-pages,edit-page-pages,translate-page,"
                . "add-page-block,block-page-edit,add-block,block-edit,"
                . "translate-block, add-page-block-page, duplicate-page-product,"
                . "edit-page-blog,add-page-blog,"
                . "add-exam,edit-exam,"
                . "add-page-product, edit-page-product,"
                . "add-listing,edit-listing,"
                . "add-gallery,edit-gallery,"
                . "add-page-contact-form,edit-page-contact-form,"
                . "add-page-calendar,edit-page-calendar,"
                . "add-page-faq,edit-page-faq,"
                . "add-page-book,edit-page-book,"
                . "add-page-book-page,edit-page-book-page,"
                . "add-page-layaway_product,edit-page-layaway_product,"
                . "realty-add-listing,realty-edit-listing,"
                . "animated-blocks-add,animated-blocks-edit,"
                . "listing-blocks-add,listing-blocks-edit"
            ;
        }
        else
        {
            $forms_to_display[Jaris\Authentication::currentUserGroup()] = explode(
                ",",
                $forms_to_display[Jaris\Authentication::currentUserGroup()]
            );
        }

        //Check if current user is on one of the groups that can use the editor
        if(!$groups[Jaris\Authentication::currentUserGroup()])
        {
            return;
        }

        foreach($forms_to_display[Jaris\Authentication::currentUserGroup()] as $form_name)
        {
            $form_name = trim($form_name);

            if($parameters["name"] == $form_name)
            {
                if($disable_editor[Jaris\Authentication::currentUserGroup()])
                {
                    if(isset($_REQUEST["disable_simplemde"]))
                    {
                        $_SESSION["disable_simplemde"] = 1;
                    }
                    if(isset($_REQUEST["enable_simplemde"]))
                    {
                        $_SESSION["disable_simplemde"] = 0;
                    }
                }

                foreach($textarea_id[Jaris\Authentication::currentUserGroup()] as $id)
                {
                    $id = trim($id);

                    $full_id = $parameters["name"] . "-" . $id;

                    $disable = "<input type=\"submit\" "
                        . "name=\"disable_simplemde\" "
                        . "value=\"" . t("Disable Editor") . "\" />"
                    ;

                    $editor = "
                    <script type=\"text/javascript\">
                    var simplemde = new SimpleMDE({ element: $('#$full_id')[0] });
                    </script>";

                    $fields = array();

                    foreach($fieldsets as $fieldsets_index => $fieldset_fields)
                    {
                        $found = false;
                        $fields = array();

                        foreach($fieldset_fields["fields"] as $fields_index => $values)
                        {
                            if(!isset($values["id"]))
                            {
                                $values["id"] = $values["name"];
                            }

                            if(
                                $values["type"] == "textarea" &&
                                $values["id"] == $id
                            )
                            {
                                if($disable_editor[Jaris\Authentication::currentUserGroup()])
                                {
                                    if($_SESSION["disable_simplemde"])
                                    {
                                        $fields[] = array(
                                            "type" => "submit",
                                            "name" => "enable_simplemde",
                                            "value" => t("Enable Editor")
                                        );

                                        $fields[] = $values;
                                    }
                                    else
                                    {
                                        $values["code"] = "style=\"width: 100%\" width=\"100%\"";
                                        $values["class"] = "simplemde";

                                        $fields[] = $values;

                                        $fields[] = array(
                                            "type" => "other",
                                            "html_code" => $disable . $editor
                                        );
                                    }
                                }
                                else
                                {
                                    $values["code"] = "style=\"width: 100%\" width=\"100%\"";
                                    $values["class"] = "simplemde";

                                    $fields[] = $values;

                                    $fields[] = array(
                                        "type" => "other",
                                        "html_code" => $editor
                                    );
                                }

                                $new_fields = array();

                                foreach($fieldset_fields["fields"] as $check_index => $field_data)
                                {
                                    //Copy new fields to the position of
                                    //replaced textarea with simplemde
                                    if($check_index == $fields_index)
                                    {
                                        foreach($fields as $field)
                                        {
                                            $new_fields[] = $field;
                                        }
                                    }

                                    //Copy the other fields on the fieldset
                                    else
                                    {
                                        $new_fields[] = $field_data;
                                    }
                                }

                                //Replace original fields with newly
                                //fields with simplemde added
                                $fieldsets[$fieldsets_index]["fields"] = $new_fields;

                                //Exit the fields check loop and fieldsets loop
                                break 2;
                            }
                        }
                    }
                }

                //Indicates that a field that matched was
                //found and simplemde should be displayed
                $display_simplemde_on_current_page = true;

                //Exit the form name search loop since
                //the form name was already found
                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_STYLES,
    function(&$styles)
    {
        global $display_simplemde_on_current_page;

        if($display_simplemde_on_current_page)
        {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("simplemde")
                    . "simplemde/simplemde.min.css"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        global $display_simplemde_on_current_page;

        if($display_simplemde_on_current_page)
        {
            //$scripts[] = "//cdn.simplemde.com/4.5.8/standard/simplemde.js";

            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("simplemde") . "simplemde/simplemde.min.js"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Simple Markdown Editor")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/simplemde",
                    "simplemde"
                ),
                "arguments" => null
            );
        }
    }
);
