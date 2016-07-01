<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Site Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addTab(t("Themes"), "admin/themes");
        Jaris\View::addTab(t("Mailer"), "admin/settings/mailer");

        //Get exsiting settings or defualt ones if main
        //settings table doesn't exist
        $site_settings = null;

        if(!($site_settings = Jaris\Settings::getAll("main")))
        {
            $site_status = "The site is down for mantainance, sorry for any "
                . "inconvenience it may cause you. Try again later."
            ;

            $site_settings["site_status"] = true;
            $site_settings["site_status_title"] = "Under mantainance";
            $site_settings["site_status_description"] = $site_status;
            $site_settings["title"] = Jaris\Site::$title;
            $site_settings["description"] = "";
            $site_settings["keywords"] = "";
            $site_settings["base_url"] = Jaris\Site::$base_url;
            $site_settings["slogan"] = Jaris\Site::$slogan;
            $site_settings["footer_message"] = Jaris\Site::$footer_message;
            $site_settings["language"] = Jaris\Site::$language;
            $site_settings["new_registrations"] = false;
            $site_settings["registration_needs_approval"] = false;
            $site_settings["registration_can_select_group"] = false;
            $site_settings["registration_groups"] = "";
            $site_settings["registration_groups_approval"] = "";
            $site_settings["user_profiles"] = Jaris\Site::$user_profiles;
            $site_settings["user_profiles_public"] = false;
            $site_settings["user_profiles_personal_text_lenght"] = 300;
            $site_settings["user_picture"] = false;
            $site_settings["user_picture_size"] = "150x150";
            $site_settings["image_compression"] = false;
            $site_settings["image_compression_maxwidth"] = "640";
            $site_settings["image_compression_quality"] = "75";
            $site_settings["image_static_serving"] = false;
            $site_settings["home_page"] = "home";
            $site_settings["page_not_found"] = "";
        }
        else
        {
            $site_status = "The site is down for mantainance, sorry for any "
                . "inconvenience it may cause you. Try again later."
            ;

            $site_settings["registration_groups"] = isset($site_settings["registration_groups"]) ?
                $site_settings["registration_groups"] : ""
            ;
            $site_settings["registration_groups_approval"] = isset($site_settings["registration_groups_approval"]) ?
                $site_settings["registration_groups_approval"] : ""
            ;
            $site_settings["site_status_title"] = isset($site_settings["site_status_title"]) ?
                $site_settings["site_status_title"] : "Under mantainance"
            ;
            $site_settings["site_status_description"] = isset($site_settings["site_status_description"]) ?
                $site_settings["site_status_description"] : $site_status
            ;
            $site_settings["new_registrations"] = isset($site_settings["new_registrations"]) ?
                $site_settings["new_registrations"] : false
            ;
            $site_settings["registration_needs_approval"] = isset($site_settings["registration_needs_approval"]) ?
                $site_settings["registration_needs_approval"] : false
            ;
            $site_settings["registration_can_select_group"] = isset($site_settings["registration_can_select_group"]) ?
                $site_settings["registration_can_select_group"] : false
            ;
            $site_settings["registration_benefits"] = isset($site_settings["registration_benefits"]) ?
                $site_settings["registration_benefits"] : ""
            ;
            $site_settings["registration_terms"] = isset($site_settings["registration_terms"]) ?
                $site_settings["registration_terms"] : ""
            ;
            $site_settings["user_profiles"] = isset($site_settings["user_profiles"]) ?
                $site_settings["user_profiles"] : Jaris\Site::$user_profiles
            ;
            $site_settings["user_profiles_public"] = isset($site_settings["user_profiles_public"]) ?
                $site_settings["user_profiles_public"] : false
            ;
            $site_settings["user_profiles_personal_text_lenght"] = isset($site_settings["user_profiles_personal_text_lenght"]) ?
                $site_settings["user_profiles_personal_text_lenght"] : ""
            ;
            $site_settings["user_picture"] = isset($site_settings["user_picture"]) ?
                $site_settings["user_picture"] : false
            ;
            $site_settings["user_picture_size"] = isset($site_settings["user_picture_size"]) ?
                $site_settings["user_picture_size"] : "150x150"
            ;
            $site_settings["image_compression"] = isset($site_settings["image_compression"]) ?
                $site_settings["image_compression"] : false
            ;
            $site_settings["image_static_serving"] = isset($site_settings["image_static_serving"]) ?
                $site_settings["image_static_serving"] : false
            ;
            $site_settings["home_page"] = isset($site_settings["home_page"]) ?
                $site_settings["home_page"] : ""
            ;
            $site_settings["page_not_found"] = isset($site_settings["page_not_found"]) ?
                $site_settings["page_not_found"] : ""
            ;
        }

        $site_settings["registration_groups"] = unserialize(
            $site_settings["registration_groups"]
        );

        $site_settings["registration_groups_approval"] = unserialize(
            $site_settings["registration_groups_approval"]
        );

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("edit-site-settings"))
        {
            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save("site_status", $_REQUEST["site_status"], "main"))
            {
                Jaris\Settings::save("site_status_title", $_REQUEST["site_status_title"], "main");
                Jaris\Settings::save("site_status_description", $_REQUEST["site_status_description"], "main");
                Jaris\Settings::save("title", $_REQUEST["title"], "main");
                Jaris\Settings::save("description", $_REQUEST["description"], "main");
                Jaris\Settings::save("keywords", $_REQUEST["keywords"], "main");
                Jaris\Settings::save("auto_detect_base_url", $_REQUEST["auto_detect_base_url"], "main");
                Jaris\Settings::save("base_url", $_REQUEST["base_url"], "main");
                Jaris\Settings::save("slogan", $_REQUEST["slogan"], "main");
                Jaris\Settings::save("footer_message", $_REQUEST["footer_message"], "main");
                Jaris\Settings::save("timezone", $_REQUEST["timezone"], "main");
                Jaris\Settings::save("language", $_REQUEST["language"], "main");
                Jaris\Settings::save("new_registrations", $_REQUEST["new_registrations"], "main");
                Jaris\Settings::save("registration_needs_approval", $_REQUEST["registration_needs_approval"], "main");
                Jaris\Settings::save("registration_can_select_group", $_REQUEST["registration_can_select_group"], "main");
                Jaris\Settings::save("registration_groups", serialize($_REQUEST["registration_groups"]), "main");
                Jaris\Settings::save("registration_groups_approval", serialize($_REQUEST["registration_groups_approval"]), "main");
                Jaris\Settings::save("registration_benefits", $_REQUEST["registration_benefits"], "main");
                Jaris\Settings::save("registration_terms", $_REQUEST["registration_terms"], "main");
                Jaris\Settings::save("user_profiles", $_REQUEST["user_profiles"], "main");
                Jaris\Settings::save("user_profiles_public", $_REQUEST["user_profiles_public"], "main");
                Jaris\Settings::save("user_profiles_personal_text_lenght", $_REQUEST["user_profiles_personal_text_lenght"], "main");
                Jaris\Settings::save("user_picture", $_REQUEST["user_picture"], "main");
                Jaris\Settings::save("user_picture_size", $_REQUEST["user_picture_size"], "main");
                Jaris\Settings::save("image_compression", $_REQUEST["image_compression"], "main");
                Jaris\Settings::save("image_compression_maxwidth", $_REQUEST["image_compression_maxwidth"], "main");
                Jaris\Settings::save("image_compression_quality", $_REQUEST["image_compression_quality"], "main");
                Jaris\Settings::save("image_static_serving", $_REQUEST["image_static_serving"], "main");
                Jaris\Settings::save("home_page", $_REQUEST["home_page"], "main");
                Jaris\Settings::save("page_not_found", $_REQUEST["page_not_found"], "main");

                Jaris\View::addMessage(t("Your settings have been successfully saved."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/settings");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "edit-site-settings";
        $parameters["class"] = "edit-site-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings");
        $parameters["method"] = "post";

        $sitestatus[t("Online")] = true;
        $sitestatus[t("Offline")] = false;

        $sitestatus_fields[] = array(
            "type" => "radio",
            "name" => "site_status",
            "id" => "site_status",
            "value" => $sitestatus,
            "checked" => $site_settings["site_status"]
        );

        $sitestatus_fields[] = array(
            "type" => "text",
            "name" => "site_status_title",
            "label" => t("Status title:"),
            "id" => "site_status_title",
            "value" => $site_settings["site_status_title"],
            "description" => t("A brief description of site status like: Under Construction")
        );

        $sitestatus_fields[] = array(
            "type" => "textarea",
            "name" => "site_status_description",
            "label" => t("Status Description:"),
            "id" => "site_status_description",
            "value" => $site_settings["site_status_description"],
            "description" => t("A detailed description of the site status.")
        );

        $fieldset[] = array(
            "name" => t("Site Status"),
            "fields" => $sitestatus_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $text_fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Site title:"),
            "id" => "site-title",
            "value" => $site_settings["title"],
            "required" => true
        );

        $text_fields[] = array(
            "type" => "other",
            "html_code" => "<br />"
        );

        $text_fields[] = array(
            "type" => "checkbox",
            "checked" => $site_settings["auto_detect_base_url"],
            "label" => t("Auto detect base url?"),
            "name" => "auto_detect_base_url",
            "id" => "auto_detect_base_url"
        );

        $text_fields[] = array(
            "type" => "text",
            "name" => "base_url",
            "label" => t("Site url:"),
            "id" => "site-url",
            "value" => $site_settings["base_url"]
        );

        $text_fields[] = array(
            "type" => "textarea",
            "name" => "slogan",
            "label" => t("Slogan:"),
            "id" => "slogan",
            "value" => $site_settings["slogan"],
            "description" => t("A short phrase that describes your company or organization goals.")
        );

        $text_fields[] = array("type" => "textarea",
            "name" => "footer_message",
            "label" => t("Footer message:"),
            "id" => "footer-message",
            "value" => $site_settings["footer_message"]
        );

        $fieldset[] = array(
            "name" => t("Site info"),
            "fields" => $text_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $temp_languages = Jaris\Language::getInstalled();
        $languages[t("auto-detect")] = "autodetect";
        foreach(Jaris\Language::getInstalled() as $code => $name)
        {
            $languages[$name] = $code;
        }

        $language_fields[] = array(
            "type" => "select",
            "name" => "language",
            "label" => t("Site language:"),
            "id" => "language",
            "value" => $languages,
            "selected" => $site_settings["language"]
        );

        $timezones_list = Jaris\Timezones::getList();
        $timezones = array();

        foreach($timezones_list as $timezone_text)
        {
            $timezones["$timezone_text"] = "$timezone_text";
        }

        $language_fields[] = array(
            "type" => "select",
            "label" => t("Timezone:"),
            "name" => "timezone",
            "id" => "timezone",
            "value" => $timezones,
            "selected" => $site_settings["timezone"]
        );

        $fieldset[] = array(
            "name" => t("Language and Timezone"),
            "fields" => $language_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $new_registrations[t("Enable")] = true;
        $new_registrations[t("Disable")] = false;

        $new_registration_fields[] = array(
            "type" => "radio",
            "name" => "new_registrations",
            "id" => "new_registrations",
            "value" => $new_registrations,
            "checked" => $site_settings["new_registrations"]
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Require administrator approval?") . "</h4>"
        );

        $new_registration_fields[] = array(
            "type" => "radio",
            "name" => "registration_needs_approval",
            "id" => "registration_needs_approval",
            "value" => $new_registrations,
            "checked" => $site_settings["registration_needs_approval"]
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Registrator can select group?") . "</h4>"
        );

        $new_registration_fields[] = array(
            "type" => "radio",
            "name" => "registration_can_select_group",
            "id" => "registration_can_select_group",
            "value" => $new_registrations,
            "checked" => $site_settings["registration_can_select_group"]
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Groups the registrator can select") . "</h4>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<table class=\"groups-list\">"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<thead>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<tr>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<td>" . t("Enable") . "</td>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<td>" . t("Group") . "</td>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<td>" . t("Description") . "</td>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<td>" . t("Requires Approval") . "</td>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "</tr>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "</thead>"
        );

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "<tbody>"
        );

        foreach(Jaris\Groups::getList() as $group_name => $group_machine_name)
        {
            $group_data = Jaris\Groups::get($group_machine_name);

            $group_html_code = "<tr>";

            $group_checked = "";
            $group_approval_checked = "";

            if(is_array($site_settings["registration_groups"]))
            {
                if(
                    in_array(
                        $group_machine_name,
                        $site_settings["registration_groups"]
                    )
                )
                {
                    $group_checked = "checked=\"checked\"";
                }
            }

            if(is_array($site_settings["registration_groups_approval"]))
            {
                if(
                    in_array(
                        $group_machine_name,
                        $site_settings["registration_groups_approval"]
                    )
                )
                {
                    $group_approval_checked = "checked=\"checked\"";
                }
            }

            $group_html_code .= "<td><input type=\"checkbox\" $group_checked name=\"registration_groups[]\" value=\"$group_machine_name\" /></td>";
            $group_html_code .= "<td>" . t($group_name) . "</td>";
            $group_html_code .= "<td>" . t($group_data["description"]) . "</td>";
            $group_html_code .= "<td><input type=\"checkbox\" $group_approval_checked name=\"registration_groups_approval[]\" value=\"$group_machine_name\" /></td>";

            $group_html_code .= "</tr>";

            $new_registration_fields[] = array(
                "type" => "other",
                "html_code" => $group_html_code
            );
        }

        $new_registration_fields[] = array(
            "type" => "other",
            "html_code" => "</tbody></table>"
        );

        $new_registration_fields[] = array(
            "type" => "textarea",
            "name" => "registration_benefits",
            "label" => t("Benefits:"),
            "id" => "registration_benefits",
            "value" => $site_settings["registration_benefits"],
            "description" => t("This will be displayed on My Account (admin/user) login page. You can input html and php code.")
        );

        $new_registration_fields[] = array(
            "type" => "textarea",
            "name" => "registration_terms",
            "label" => t("Terms and conditions:"),
            "id" => "registration_terms",
            "value" => $site_settings["registration_terms"],
            "description" => t("The terms and conditions users have to agree before registering.")
        );

        $fieldset[] = array(
            "name" => t("New registrations"),
            "fields" => $new_registration_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("Enables or disable public registrations to the site at the register page.")
        );

        $user_profiles[t("Enable")] = true;
        $user_profiles[t("Disable")] = false;

        $user_profiles_fields[] = array(
            "type" => "radio",
            "name" => "user_profiles",
            "id" => "user_profiles",
            "value" => $user_profiles,
            "checked" => $site_settings["user_profiles"]
        );

        $user_profiles_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Public profiles") . "</h4>"
        );

        $user_profiles_fields[] = array(
            "type" => "radio",
            "name" => "user_profiles_public",
            "id" => "user_profiles_public",
            "value" => $user_profiles,
            "checked" => $site_settings["user_profiles_public"]
        );

        $user_profiles_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Personal Text Lenght") . "</h4>"
        );

        $user_profiles_fields[] = array(
            "type" => "text",
            "name" => "user_profiles_personal_text_lenght",
            "id" => "user_profiles_personal_text_lenght",
            "value" => $site_settings["user_profiles_personal_text_lenght"]
        );

        $fieldset[] = array(
            "name" => t("User profiles"),
            "fields" => $user_profiles_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $user_picture[t("Enable")] = true;
        $user_picture[t("Disable")] = false;

        $user_fields[] = array(
            "type" => "radio",
            "name" => "user_picture",
            "id" => "user_picture",
            "value" => $user_picture,
            "checked" => $site_settings["user_picture"]
        );

        $user_fields[] = array(
            "type" => "text",
            "label" => t("Size:"),
            "name" => "user_picture_size",
            "id" => "user_picture_size",
            "value" => $site_settings["user_picture_size"],
            "description" => t("The maximun width and height of the picture in the format 100x150 where 100 = width and 150 height.")
        );

        $fieldset[] = array(
            "name" => t("User picture"),
            "fields" => $user_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $image_compression[t("Enable")] = true;
        $image_compression[t("Disable")] = false;

        $image_uploads[] = array(
            "type" => "radio",
            "name" => "image_compression",
            "id" => "image_compression",
            "value" => $image_compression,
            "checked" => $site_settings["image_compression"]
        );

        $image_uploads[] = array(
            "type" => "text",
            "label" => t("Maximun width:"),
            "name" => "image_compression_maxwidth",
            "id" => "image_compression_maxwidth",
            "value" => $site_settings["image_compression_maxwidth"],
            "description" => t("The maximun width for uploaded images.")
        );

        $image_uploads[] = array(
            "type" => "text",
            "label" => t("Image quality:"),
            "name" => "image_compression_quality",
            "id" => "image_compression_quality",
            "value" => $site_settings["image_compression_quality"],
            "description" => t("A range from 0 (worst quality, smaller file) to 100 (best quality, biggest file) for jpeg files.")
        );

        $image_uploads[] = array(
            "type" => "radio",
            "name" => "image_static_serving",
            "id" => "image_static_serving",
            "value" => $image_compression,
            "label" => t("Static Serving"),
            "checked" => $site_settings["image_static_serving"],
            "description" => t("Enabling this option will store processed image files in a public directory named 'images', this way the web server becomes in charge of serving images directly instead of serving them from php script which improves performance. The only drawback is that images are not protected from public access.")
        );

        $fieldset[] = array(
            "name" => t("Image compression"),
            "fields" => $image_uploads,
            "collapsible" => true,
            "collapsed" => true
        );

        $home_fields[] = array(
            "type" => "uri",
            "label" => t("Uri:"),
            "name" => "home_page",
            "id" => "home_page",
            "value" => $site_settings["home_page"],
            "description" => t("The uri to the page used as the home page.")
        );

        $fieldset[] = array(
            "name" => t("Home page"),
            "fields" => $home_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $page_not_found_fields[] = array(
            "type" => "uri",
            "label" => t("Uri:"),
            "name" => "page_not_found",
            "id" => "page_not_found",
            "value" => $site_settings["page_not_found"],
            "description" => t("The uri to the page used as the page not found result.")
        );

        $fieldset[] = array(
            "name" => t("Page not found"),
            "fields" => $page_not_found_fields,
            "collapsible" => true,
            "collapsed" => true
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
