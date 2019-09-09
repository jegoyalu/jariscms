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

$display_whizzywig_on_current_page = false;

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        global $display_whizzywig_on_current_page;

        $page_data = Jaris\Site::$page_data;

        $actual_items = unserialize(
            Jaris\Settings::get("toolbar_items", "whizzywig")
        );
        $textarea_id = unserialize(
            Jaris\Settings::get("teaxtarea_id", "whizzywig")
        );
        $forms_to_display = unserialize(
            Jaris\Settings::get("forms", "whizzywig")
        );
        $groups = unserialize(
            Jaris\Settings::get("groups", "whizzywig")
        );
        $disable_editor = unserialize(
            Jaris\Settings::get("disable_editor", "whizzywig")
        );

        $user_group = Jaris\Authentication::currentUserGroup();

        if(!is_array($actual_items))
            $actual_items = array();

        if(!is_array($textarea_id))
            $textarea_id = array();

        if(!is_array($forms_to_display))
            $forms_to_display = array();

        if(!is_array($groups))
            $groups = array();

        if(!is_array($disable_editor))
            $disable_editor = array();

        if(!$actual_items[$user_group])
        {
            $actual_items[$user_group] = "all";
        }

        if(!$textarea_id[$user_group])
        {
            $textarea_id[$user_group] = "content";
        }
        else
        {
            $textarea_id[$user_group] = explode(
                ",",
                $textarea_id[$user_group]
            );
        }

        if(!$forms_to_display[$user_group])
        {
            $forms_to_display[] = "add-page,edit-page,translate-page,"
                . "add-page-block,block-page-edit,add-block,block-edit,"
                . "add-page-block-page"
            ;
        }
        else
        {
            $forms_to_display[$user_group] = explode(
                ",",
                $forms_to_display[$user_group]
            );
        }

        //Check if current user is on one of the groups that can use the editor
        if(!$groups[$user_group])
        {
            return;
        }

        foreach($forms_to_display[$user_group] as $form_name)
        {
            $form_name = trim($form_name);

            if($parameters["name"] == $form_name)
            {
                if($disable_editor[$user_group])
                {
                    if(isset($_REQUEST["disable_whizzywig"]))
                    {
                        Jaris\Session::addCookie("disable_whizzywig", 1);
                    }
                    if(isset($_REQUEST["enable_whizzywig"]))
                    {
                        Jaris\Session::removeCookie("disable_whizzywig");
                    }
                }

                foreach($textarea_id[$user_group] as $id)
                {
                    $id = trim($id);

                    $full_id = $parameters["name"] . "-" . $id;

                    /*
                     * Whizzywig Configuration variables
                     *
                     * buttonPath = "/btn/"; //path to toolbar button images; "textbuttons" (the default) means don't use images
                     * cssFile = "stylesheet.css"; //url of CSS stylesheet to attach to edit area
                     * imageBrowse = "whizzypic.php"; //path to page for image browser (see below)
                     * linkBrowse = "picklink.php"; //path to page for link browser (see below)
                     */

                    $disable = "<input type=\"submit\" "
                        . "name=\"disable_whizzywig\" "
                        . "value=\"" . t("Disable Editor") . "\" />"
                    ;

                    $editor_buttons = Jaris\Uri::url(
                        Jaris\Modules::directory("whizzywig")
                            . "whizzywig/icons.png"
                    );

                    $editor_image_browser = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("whizzypic", "whizzywig"),
                        array(
                            "uri" => $_REQUEST["uri"],
                            "element_id" => $full_id
                        )
                    );

                    $editor_link_browser = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("whizzylink", "whizzywig"),
                        array(
                            "uri" => $_REQUEST["uri"],
                            "element_id" => $full_id
                        )
                    );

                    $editor = "
                    <script type=\"text/javascript\">
                    whizzywig.btn._f = \"$editor_buttons\";
                    whizzywig.linkBrowse = \"$editor_link_browser\";
                    whizzywig.imageBrowse = \"$editor_image_browser\";
                    whizzywig.makeWhizzyWig(\"$full_id\", \"" . $actual_items[$user_group] . "\");
                    </script>";

                    $fields = array();

                    foreach($fieldsets as $fieldsets_index => $fieldset_fields)
                    {
                        $found = false;
                        $fields = array();

                        foreach(
                            $fieldset_fields["fields"]
                            as
                            $fields_index => $values
                        )
                        {
                            if(!isset($values["id"]))
                            {
                                $values["id"] = $values["name"];
                            }

                            if(
                                $values["type"] == "textarea"
                                &&
                                $values["id"] == $id
                                &&
                                ("" . strpos($values["value"], "<?php") . "" == "")
                            )
                            {
                                if($disable_editor[$user_group])
                                {
                                    if($_COOKIE["disable_whizzywig"])
                                    {
                                        $fields[] = array(
                                            "type" => "submit",
                                            "name" => "enable_whizzywig",
                                            "value" => t("Enable Editor")
                                        );

                                        $fields[] = $values;
                                    }
                                    else
                                    {
                                        $values["code"] = "style=\"width: 100%\" width=\"100%\"";
                                        $values["class"] = "whizzywig";

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
                                    $values["class"] = "whizzywig";

                                    $fields[] = $values;

                                    $fields[] = array(
                                        "type" => "other",
                                        "html_code" => $editor
                                    );
                                }

                                $new_fields = array();

                                foreach(
                                    $fieldset_fields["fields"]
                                    as
                                    $check_index => $field_data
                                )
                                {
                                    //Copy new fields to the position of
                                    //replaced textarea with whizzywig
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
                                //fields with whizzywig added
                                $fieldsets[$fieldsets_index]["fields"] = $new_fields;

                                //Exit the fields check loop and fieldsets loop
                                break 2;
                            }
                        }
                    }
                }

                //Indicates that a field that matched was
                //found and whizzywig should be displayed
                $display_whizzywig_on_current_page = true;

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
        global $display_whizzywig_on_current_page;

        if($display_whizzywig_on_current_page)
        {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("whizzywig")
                    . "whizzywig.css"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        global $display_whizzywig_on_current_page;

        if($display_whizzywig_on_current_page)
        {
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("whizzywig")
                    . "whizzywig/whizzywig-v63.js"
            );
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::getPageUri("whizzylang", "whizzywig")
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
            $tabs_array[0][t("Whizzywig Editor")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/whizzywig",
                    "whizzywig"
                ),
                "arguments" => array()
            );
        }
    }
);