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

$display_codemirror_on_current_page = false;

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        global $display_codemirror_on_current_page;

        $textarea_id = unserialize(Jaris\Settings::get("teaxtarea_id", "codemirror"));
        $forms_to_display = unserialize(Jaris\Settings::get("forms", "codemirror"));
        $groups = unserialize(Jaris\Settings::get("groups", "codemirror"));

        if(!is_array($textarea_id))
            $textarea_id = array();
        if(!is_array($forms_to_display))
            $forms_to_display = array();
        if(!is_array($groups))
            $groups = array();

        if(!$textarea_id[Jaris\Authentication::currentUserGroup()])
        {
            $textarea_id[Jaris\Authentication::currentUserGroup()] = "content, return";
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
            $forms_to_display[] = "add-page,edit-page,translate-page,add-page-block,block-page-edit,add-block,block-edit,add-page-block-page";
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
                foreach($textarea_id[Jaris\Authentication::currentUserGroup()] as $id)
                {
                    $id = trim($id);

                    $full_id = $parameters["name"] . "-" . $id;

                    $editor = '
                    <script type="text/javascript">
                    var textarea = document.getElementById("' . $full_id . '");
                    var uiOptions = {
                        path : "' . Jaris\Uri::url(Jaris\Modules::directory("codemirror") . "codemirror-ui/js/") . '",
                        imagePath : "' . Jaris\Uri::url(Jaris\Modules::directory("codemirror") . "codemirror-ui/images/silk") . '",
                        searchMode : "popup"
                    };
                    var codeMirrorOptions = {
                        lineNumbers: true,
                        matchBrackets: true,
                        mode: "application/x-httpd-php",
                        indentUnit: 4,
                        indentWithTabs: true,
                        lineWrapping: false,
                        tabMode: "shift"
                    };

                    var editor = new CodeMirrorUI(textarea,uiOptions,codeMirrorOptions);
                    </script>';

                    $fields = array();

                    foreach($fieldsets as $fieldsets_index => $fieldset_fields)
                    {
                        $fields = array();

                        foreach($fieldset_fields["fields"] as $fields_index => $values)
                        {
                            if($values["type"] == "textarea" && $values["id"] == $id)
                            {
                                $values["class"] = "codemirror";
                                $fields[] = $values;
                                $fields[] = array("type" => "other", "html_code" => $editor);

                                $new_fields = array();

                                foreach($fieldset_fields["fields"] as $check_index => $field_data)
                                {
                                    //Copy new fields to the position of replaced textarea with codemirror
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

                                //Replace original fields with newly fields with codemirror added
                                $fieldsets[$fieldsets_index]["fields"] = $new_fields;

                                //Exit the fields check loop and fieldsets loop
                                break 2;
                            }
                        }
                    }
                }

                //Indicates that a field that matched was found and codemirror should be displayed
                $display_codemirror_on_current_page = true;

                //Exit the form name search loop since the form name was already found
                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_STYLES,
    function(&$styles)
    {
        global $display_codemirror_on_current_page;

        if($display_codemirror_on_current_page)
        {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/lib/codemirror.css"
            );

            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-ui/css/codemirror-ui.css"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        global $display_codemirror_on_current_page;

        if($display_codemirror_on_current_page)
        {
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/lib/codemirror.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/lib/util/matchbrackets.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/lib/util/searchcursor.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/htmlmixed/htmlmixed.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/xml/xml.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/javascript/javascript.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/css/css.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/clike/clike.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-3.0/mode/php/php.js"
            );
            
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("codemirror") 
                    . "codemirror-ui/js/codemirror-ui.js"
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
            $tabs_array[0][t("Codemirror Editor")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/codemirror",
                    "codemirror"
                ),
                "arguments" => null
            );
        }
    }
);