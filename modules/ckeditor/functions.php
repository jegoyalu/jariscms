<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file.
 */

$display_ckeditor_on_current_page = false;

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        global $display_ckeditor_on_current_page;

        $textarea_id = unserialize(Jaris\Settings::get("teaxtarea_id", "ckeditor"));
        $uicolor = unserialize(Jaris\Settings::get("uicolor", "ckeditor"));
        $plugins = unserialize(Jaris\Settings::get("plugins", "ckeditor"));
        $forms_to_display = unserialize(Jaris\Settings::get("forms", "ckeditor"));
        $groups = unserialize(Jaris\Settings::get("groups", "ckeditor"));
        $disable_editor = unserialize(Jaris\Settings::get("disable_editor", "ckeditor"));

        $user_group = Jaris\Authentication::currentUserGroup();

        if(!is_array($textarea_id))
            $textarea_id = array();

        if(!is_array($uicolor))
            $uicolor = array();

        if(!is_array($forms_to_display))
            $forms_to_display = array();

        if(!is_array($groups))
            $groups = array();

        if(!is_array($disable_editor))
            $disable_editor = array();

        if(!$textarea_id[$user_group])
        {
            $textarea_id[$user_group] =
                "content,pre_content,sub_content,"
                    . "registration_welcome_message,registration_benefits,"
                    . "footer-message,site_status_description"
            ;
        }
        else
        {
            $textarea_id[$user_group] = explode(
                ",",
                $textarea_id[$user_group]
            );
        }

        if(empty($uicolor[$user_group]))
        {
            $uicolor[$user_group] = "FFFFFF";
        }

        if(
            empty($plugins[$user_group]) &&
            !is_array($plugins[$user_group])
        )
        {
            $plugins[Jaris\Authentication::currentUserGroup()] = array(
                "quicktable", "youtube", "codemirror"
            );
        }

        if(!$forms_to_display[$user_group])
        {
            $forms_to_display[$user_group] =
                "add-page-pages,edit-page-pages,translate-page,"
                . "add-page-block,block-page-edit,add-block,block-edit,"
                . "translate-block, add-page-block-page, duplicate-page-product,"
                . "edit-page-blog,add-page-blog,"
                . "add-exam,edit-exam,"
                . "add-page-product, edit-page-product,"
                . "add-page-listing,edit-page-listing,"
                . "add-gallery,edit-gallery,"
                . "add-page-contact-form,edit-page-contact-form,"
                . "add-page-calendar,edit-page-calendar,"
                . "add-page-faq,edit-page-faq,"
                . "add-page-book,edit-page-book,"
                . "add-page-book-page,edit-page-book-page,"
                . "add-page-layaway_product,edit-page-layaway_product,"
                . "realty-add-listing,realty-edit-listing,"
                . "animated-blocks-add,animated-blocks-edit,"
                . "listing-blocks-add,listing-blocks-edit,"
                . "edit-site-settings"
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

        if(
            !is_array(
                $forms_to_display[$user_group]
            )
        )
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
                    if(isset($_REQUEST["disable_ckeditor"]))
                    {
                        Jaris\Session::addCookie("disable_ckeditor", 1);
                    }
                    if(isset($_REQUEST["enable_ckeditor"]))
                    {
                        Jaris\Session::removeCookie("disable_ckeditor");
                    }
                }

                foreach($textarea_id[$user_group] as $id)
                {
                    $id = trim($id);

                    $full_id = $parameters["name"] . "-" . $id;

                    $disable = "<input type=\"submit\" "
                        . "name=\"disable_ckeditor\" "
                        . "value=\"" . t("Disable Editor") . "\" />"
                    ;

                    $lang = "";
                    if(Jaris\Language::getCurrent() == "es")
                    {
                        $lang .= "language: 'es',";
                    }

                    $editor_image_browser = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("ckeditorpic", "ckeditor"),
                        array("uri" => $_REQUEST["uri"])
                    );

                    $editor_image_uploader = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("ckeditorpicup", "ckeditor"),
                        array("uri" => $_REQUEST["uri"])
                    );

                    $editor_link_browser = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("ckeditorlink", "ckeditor"),
                        array("uri" => $_REQUEST["uri"])
                    );

                    $editor_link_uploader = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("ckeditorlinkup", "ckeditor"),
                        array("uri" => $_REQUEST["uri"])
                    );

                    $editor_config = Jaris\Uri::url(
                        Jaris\Modules::getPageUri("ckeditorconfig", "ckeditor"),
                        array("group" => $user_group)
                    );

                    $interface_color = $uicolor[$user_group];

                    $plugins_list = implode(",", $plugins[$user_group]);

                    $codemirror = in_array("codemirror", $plugins[$user_group]) ?
                        "codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},"
                        :
                        ""
                    ;

                    $editor = "
                    <script type=\"text/javascript\">
                    CKEDITOR.replace( '$full_id', {
                        customConfig: '$editor_config',
                        uiColor: '#$interface_color',
                        filebrowserBrowseUrl: '$editor_link_browser',
                        filebrowserImageBrowseUrl: '$editor_image_browser',
                        filebrowserUploadUrl: '$editor_link_uploader',
                        filebrowserImageUploadUrl: '$editor_image_uploader',
                        extraPlugins: '$plugins_list',
                        codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},
                        $codemirror
                        $lang
                    });
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
                                if($disable_editor[$user_group])
                                {
                                    if($_COOKIE["disable_ckeditor"])
                                    {
                                        $fields[] = array(
                                            "type" => "submit",
                                            "name" => "enable_ckeditor",
                                            "value" => t("Enable Editor")
                                        );

                                        $fields[] = $values;
                                    }
                                    else
                                    {
                                        $values["code"] = "style=\"width: 100%\" width=\"100%\"";
                                        $values["class"] = "ckeditor";

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
                                    $values["class"] = "ckeditor";

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
                                    //replaced textarea with ckeditor
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
                                //fields with ckeditor added
                                $fieldsets[$fieldsets_index]["fields"] = $new_fields;

                                //Exit the fields check loop and fieldsets loop
                                break 2;
                            }
                        }
                    }
                }

                //Indicates that a field that matched was
                //found and ckeditor should be displayed
                $display_ckeditor_on_current_page = true;

                //Exit the form name search loop since
                //the form name was already found
                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        global $display_ckeditor_on_current_page;

        if($display_ckeditor_on_current_page)
        {
            //$scripts[] = "//cdn.ckeditor.com/4.5.8/standard/ckeditor.js";

            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("ckeditor") . "ckeditor/ckeditor.js"
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
            $tabs_array[0][t("CKEditor")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/ckeditor",
                    "ckeditor"
                ),
                "arguments" => array()
            );
        }
    }
);

function ckeditor_textarea_replace($element_id)
{
    $uicolor = unserialize(Jaris\Settings::get("uicolor", "ckeditor"));
    $plugins = unserialize(Jaris\Settings::get("plugins", "ckeditor"));

    $user_group = Jaris\Authentication::currentUserGroup();

    if(!is_array($uicolor))
        $uicolor = array();

    if(empty($uicolor[$user_group]))
    {
        $uicolor[$user_group] = "FFFFFF";
    }

    if(
        empty($plugins[$user_group]) &&
        !is_array($plugins[$user_group])
    )
    {
        $plugins[$user_group] = array(
            "quicktable", "youtube", "codemirror"
        );
    }

    $lang = "";
    if(Jaris\Language::getCurrent() == "es")
    {
        $lang .= "language: 'es',";
    }

    $editor_image_browser = Jaris\Uri::url(
        Jaris\Modules::getPageUri("ckeditorpic", "ckeditor"),
        array("uri" => $_REQUEST["uri"])
    );

    $editor_image_uploader = Jaris\Uri::url(
        Jaris\Modules::getPageUri("ckeditorpicup", "ckeditor"),
        array("uri" => $_REQUEST["uri"])
    );

    $editor_link_browser = Jaris\Uri::url(
        Jaris\Modules::getPageUri("ckeditorlink", "ckeditor"),
        array("uri" => $_REQUEST["uri"])
    );

    $editor_link_uploader = Jaris\Uri::url(
        Jaris\Modules::getPageUri("ckeditorlinkup", "ckeditor"),
        array("uri" => $_REQUEST["uri"])
    );

    $editor_config = Jaris\Uri::url(
        Jaris\Modules::getPageUri("ckeditorconfig", "ckeditor"),
        array("group" => $user_group)
    );

    $interface_color = $uicolor[$user_group];

    $plugins_list = implode(",", $plugins[$user_group]);

    $codemirror = in_array("codemirror", $plugins[$user_group]) ?
        "codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},"
        :
        ""
    ;

    $editor = "CKEDITOR.replace('$element_id', {"
        . "customConfig: '$editor_config',"
        . "uiColor: '#$interface_color',"
        . "filebrowserBrowseUrl: '$editor_link_browser',"
        . "filebrowserImageBrowseUrl: '$editor_image_browser',"
        . "filebrowserUploadUrl: '$editor_link_uploader',"
        . "filebrowserImageUploadUrl: '$editor_image_uploader',"
        . "extraPlugins: '$plugins_list',"
        . "codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},"
        . "$codemirror"
        . "$lang"
        . "});"
    ;

    return $editor;
}