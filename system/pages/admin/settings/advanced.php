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
        <?php print t("Advanced Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addTab(t("Api Keys"), "admin/settings/advanced/keys");
        Jaris\View::addTab(t("Cron Jobs"), "admin/settings/cron");
        Jaris\View::addTab(t("Clear Image Cache"), "admin/settings/clear-image-cache");
        Jaris\View::addTab(t("Clear Page Cache"), "admin/settings/clear-page-cache");
        Jaris\View::addTab(t("Sqlite Backups"), "admin/settings/sqlite");
        Jaris\View::addTab(t("Re-index SQLite Search"), "admin/settings/reindex-search");

        //Get exsiting settings or defualt ones if main
        //settings table doesn't exist
        $site_settings = null;

        if(!($site_settings = Jaris\Settings::getAll("main")))
        {
            $site_settings["clean_urls"] = Jaris\Site::$clean_urls;
            $site_settings["validate_ip"] = false;
            $site_settings["login_ssl"] = false;
            $site_settings["development_mode"] = false;
            $site_settings["enable_cache"] = false;
            $site_settings["cache_php_pages"] = false;
            $site_settings["cache_ignore_db"] = "";
            $site_settings["cache_ignore_types"] = "";
            $site_settings["cache_expire"] = 0;
            $site_settings["data_cache"] = false;
            $site_settings["image_compression"] = false;
            $site_settings["image_compression_maxwidth"] = "640";
            $site_settings["image_compression_quality"] = "75";
            $site_settings["image_static_serving"] = false;
            $site_settings["home_page"] = "home";
            $site_settings["page_not_found"] = "";
        }

        $site_settings["cache_ignore_db"] = unserialize(
            $site_settings["cache_ignore_db"]
        );

        $site_settings["cache_ignore_types"] = unserialize(
            $site_settings["cache_ignore_types"]
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-site-advanced-settings")
        )
        {
            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save("clean_urls", $_REQUEST["clean_urls"], "main"))
            {
                Jaris\Settings::save("validate_ip", $_REQUEST["validate_ip"], "main");
                Jaris\Settings::save("login_ssl", $_REQUEST["login_ssl"], "main");
                Jaris\Settings::save("development_mode", $_REQUEST["development_mode"], "main");
                Jaris\Settings::save("enable_cache", $_REQUEST["enable_cache"], "main");
                Jaris\Settings::save("enable_fast_cache", $_REQUEST["enable_fast_cache"], "main");
                Jaris\Settings::save("cache_php_pages", $_REQUEST["cache_php_pages"], "main");
                Jaris\Settings::save("cache_ignore_db", serialize($_REQUEST["cache_ignore_db"]), "main");
                Jaris\Settings::save("cache_ignore_types", serialize($_REQUEST["cache_ignore_types"]), "main");
                Jaris\Settings::save("cache_expire", $_REQUEST["cache_expire"], "main");
                Jaris\Settings::save("data_cache", $_REQUEST["data_cache"], "main");
                Jaris\Settings::save("classic_views_count", $_REQUEST["classic_views_count"], "main");
                Jaris\Settings::save("view_script_stats", $_REQUEST["view_script_stats"], "main");

                //If data cache was enabled or disabled
                //Create data cache directory if it doesnt exists
                if($_REQUEST["data_cache"])
                {
                    if(!file_exists(Jaris\Site::dataDir() . "data_cache"))
                        ;
                    Jaris\FileSystem::makeDir(Jaris\Site::dataDir() . "data_cache");
                }
                //Empty data cache directory and remove it
                else
                {
                    if(function_exists("apc_store"))
                    {
                        apc_clear_cache();
                    }

                    $data_cache_dir = Jaris\Site::dataDir() . "data_cache";

                    if(file_exists($data_cache_dir))
                    {
                        $dir = opendir($data_cache_dir);

                        while(($file = readdir($dir)) !== false)
                        {
                            if($file != "." && $file != "..")
                                unlink($data_cache_dir . "/" . $file);
                        }

                        rmdir($data_cache_dir);
                    }
                }

                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                Jaris\Site::$clean_urls = $_REQUEST["clean_urls"];
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/settings/advanced");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings/advanced");
        }

        $parameters["name"] = "edit-site-advanced-settings";
        $parameters["class"] = "edit-site-advanced-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings/advanced");
        $parameters["method"] = "post";

        $enable_disable[t("Enable")] = true;
        $enable_disable[t("Disable")] = false;

        $development[t("Enable")] = true;
        $development[t("Disable")] = false;

        $development_fields[] = array(
            "type" => "radio",
            "name" => "development_mode",
            "id" => "development_mode",
            "value" => $enable_disable,
            "checked" => $site_settings["development_mode"]
        );

        $fieldset[] = array(
            "name" => t("Development mode"),
            "fields" => $development_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("When enabled a module changes will reflect instantly without having to re-install it, but this will affect the system performance. Use when developing modules for easier testing.")
        );

        $login_authentication[t("Enable")] = true;
        $login_authentication[t("Disable")] = false;

        $login_authentication_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Enables or disable the validation of user ip address") . "</h4>"
        );

        $login_authentication_fields[] = array(
            "type" => "radio",
            "name" => "validate_ip",
            "id" => "validate_ip",
            "value" => $login_authentication,
            "checked" => $site_settings["validate_ip"],
            "description" => t("This increases security but may result on user logout on dynamic mobile connections that constantly change ip address.")
        );

        $login_authentication_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Force login over encrypted connection (https)") . "</h4>"
        );

        $login_authentication_fields[] = array(
            "type" => "radio",
            "name" => "login_ssl",
            "id" => "login_ssl",
            "value" => $login_authentication,
            "checked" => $site_settings["login_ssl"]
        );

        $fieldset[] = array(
            "name" => t("Login and Authentication"),
            "fields" => $login_authentication_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $cache[t("Enable")] = true;
        $cache[t("Disable")] = false;

        $cache_fields[] = array(
            "type" => "radio",
            "name" => "enable_cache",
            "id" => "enable_cache",
            "value" => $cache,
            "checked" => $site_settings["enable_cache"]
        );

        $cache_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("PHP pages caching?") . "</h4>"
        );

        $cache_fields[] = array(
            "type" => "radio",
            "name" => "cache_php_pages",
            "id" => "cache_php_pages",
            "value" => $cache,
            "checked" => $site_settings["cache_php_pages"]
        );

        $cache_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Select databases to ignore on timestamp check") . "</h4>"
        );

        $cache_databases = Jaris\Sql::listDB();
        foreach($cache_databases as $db_name)
        {
            $checked = false;

            if(
                $db_name == "search_engine" ||
                $db_name == "users" ||
                $db_name == "cache" ||
                $db_name == "errors_log" ||
                $db_name == "api_keys" ||
                $db_name == "readme.txt"
            )
            {
                continue;
            }

            if(is_array($site_settings["cache_ignore_db"]))
            {

                foreach($site_settings["cache_ignore_db"] as $selected_db)
                {
                    if($db_name == $selected_db)
                    {
                        $checked = true;
                        break;
                    }
                }
            }

            $cache_fields[] = array(
                "type" => "checkbox",
                "checked" => $checked,
                "label" => $db_name,
                "name" => "cache_ignore_db[]",
                "id" => "cache_ignore_db",
                "value" => $db_name
            );
        }

        $cache_fields[] = array(
            "type" => "other",
            "html_code" => "<h4>" . t("Select types to disable page caching") . "</h4>"
        );

        $cache_types = Jaris\Types::getList();
        foreach($cache_types as $type_name => $type_data)
        {
            $checked = false;

            if(is_array($site_settings["cache_ignore_types"]))
            {

                foreach($site_settings["cache_ignore_types"] as $selected_db)
                {
                    if($type_name == $selected_db)
                    {
                        $checked = true;
                        break;
                    }
                }
            }

            $cache_fields[] = array(
                "type" => "checkbox",
                "checked" => $checked,
                "label" => t($type_data["name"]),
                "name" => "cache_ignore_types[]",
                "id" => "cache_ignore_types",
                "value" => $type_name
            );
        }

        $cache_fields[] = array(
            "type" => "text",
            "label" => t("Expiration time:"),
            "name" => "cache_expire",
            "id" => "cache_expire",
            "value" => $site_settings["cache_expire"],
            "description" => t("Amount of seconds until a cached page is regenerated. Leave blank or set to 0 to disable.")
        );

        $cache_fields[] = array(
            "type" => "radio",
            "name" => "enable_fast_cache",
            "label" => t("Enable Fast Page Caching"),
            "id" => "enable_fast_cache",
            "value" => $cache,
            "checked" => $site_settings["enable_fast_cache"],
            "description" => t("Enabling this option will prevent the system from fully loading and proceed to send a cached file if available.")
        );

        $fieldset[] = array(
            "name" => t("Page Caching"),
            "fields" => $cache_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("Enables or disable the caching of pages content for fast retrieving.")
        );

        $cache_data[t("Enable")] = true;
        $cache_data[t("Disable")] = false;

        $cache_data_fields[] = array(
            "type" => "radio",
            "name" => "data_cache",
            "id" => "validate_ip",
            "value" => $cache_data,
            "checked" => $site_settings["data_cache"]
        );

        $fieldset[] = array(
            "name" => t("Data Caching"),
            "fields" => $cache_data_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("Special option that improves performance on embedded devices or low performance servers.")
        );

        $classic_views_count[t("Enable")] = true;
        $classic_views_count[t("Disable")] = false;

        $classic_views_count_fields[] = array(
            "type" => "radio",
            "name" => "classic_views_count",
            "id" => "classic_views_count",
            "value" => $classic_views_count,
            "checked" => $site_settings["classic_views_count"]
        );

        $fieldset[] = array(
            "name" => t("Classic Views Counting"),
            "fields" => $classic_views_count_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("When enabled, instead of using new json api to count page views after load, the old server side mechanism is used which can slow down page load time.")
        );

        $scrip_stats[t("Enable")] = true;
        $scrip_stats[t("Disable")] = false;

        $script_stats_fields[] = array(
            "type" => "radio",
            "name" => "view_script_stats",
            "id" => "view_script_stats",
            "value" => $scrip_stats,
            "checked" => $site_settings["view_script_stats"]
        );

        $fieldset[] = array(
            "name" => t("Script stats"),
            "fields" => $script_stats_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("Enables or disable the display of script stats at the end of page. For the purpose of measuring JarisCMS performance.")
        );

        $cleanurl[t("Enable")] = true;
        $cleanurl[t("Disable")] = false;

        $clean_fields[] = array(
            "type" => "radio",
            "name" => "clean_urls",
            "id" => "cleanurl",
            "value" => $cleanurl,
            "checked" => $site_settings["clean_urls"]
        );

        $fieldset[] = array(
            "name" => t("Clean url"),
            "fields" => $clean_fields,
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
