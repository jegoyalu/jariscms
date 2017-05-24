<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * JarisCMS Installation script.
 */

chdir(__DIR__);

chdir("../");

//Starts the main session for the user
session_start();

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//File that includes backward compatibility functions.
include("src/Aliases.php");

//Use new settings if available.
Jaris\Site::init();

//Stores the current port if not 80
$port = $_SERVER["SERVER_PORT"] != "80" ? ":{$_SERVER["SERVER_PORT"]}" : "";

//Override settings.php base_url with an url detection.
$base_url = "http://" . $_SERVER["SERVER_NAME"] . $port .
    str_replace("/install.php", "", $_SERVER["PHP_SELF"])
;

Jaris\Site::$base_url = $base_url;

//Load installed modules for distro to work properly.
Jaris\Site::loadModules();

//Sets the language based on user selection or system default
$language = Jaris\Language::getCurrent();

Jaris\Site::$language = $language;

//For security skip page and go to index if already installed.
if(
    file_exists(Jaris\Site::dataDir() . "settings/main.php") &&
    Jaris\Settings::get("mailer_from_name", "main") &&
    $_REQUEST["action"] != "finalize_installation" &&
    $_REQUEST["action"] != "cleanurl_check"
)
{
    Jaris\Uri::go("");
}

//Create sites/default if not exists
if(!file_exists("sites/default"))
{
    //Disable max execution time in case of slow copy
    ini_set('max_execution_time', '0');

    Jaris\FileSystem::recursiveCopyDir("system/skeleton", "sites/default");
}

//Variables used to store what is showed on installation.
$step_title = "";
$content = "";
$error_message = "";

//Welcome page for installation
if(!isset($_REQUEST["action"]) || $_REQUEST["action"] == "")
{
    $step_title = t("Select a language", "install.po");

    $languages_temp = Jaris\Language::getInstalled();
    $languages = array();
    foreach($languages_temp as $value => $label)
    {
        $languages[$label] = $value;
    }

    $parameters["action"] = $_SERVER["PHP_SELF"];
    $parameters["method"] = "post";

    $fields[] = array(
        "type" => "select",
        "name" => "language",
        "id" => "language",
        "label" => t("Language:", "install.po"),
        "value" => $languages, "selected" => "en"
    );

    $fields[] = array(
        "type" => "hidden",
        "name" => "action",
        "value" => "begin"
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
    );

    $fields[] = array(
        "type" => "submit",
        "name" => "next",
        "value" => t("Continue", "install.po")
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "</div>"
    );

    $fieldset[] = array("fields" => $fields);

    $content .= Jaris\Forms::generate($parameters, $fieldset);
}
elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "begin")
{
    $step_title = t("Installation Wizard", "install.po");

    $content = t("<b>Welcome</b> to Jaris CMS installation wizard! To install it you just need some minimun system requirements to have a working enviroment. Before we continue you should know that <b>Jaris CMS</b> does not requires any relational database like mysql since it has its own engine written on php to store data. So in order to make it work you need to set write permissions to the user account that is running the php parser.<br />", "install.po")
        . "<h3>".t("Requirements:", "install.po")."</h3>"
        . "<ul>"
        . "<li>".t("PHP 5 or greater", "install.po")."</li>"
        . "<li>".t("PHP GD library for graphics processing", "install.po")."</li>"
        . "<li>".t("Write permissions on <b>sites</b> directory", "install.po")."</li>"
        . "<li>".t("Apache with mod rewrite for clean url system", "install.po")."</li>"
        . "<li>".t("Sqlite just for search engine optimizations.", "install.po")."</li>"
        . "</ul>"
    ;

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=check_requirements";
    $parameters["method"] = "post";

    $fields[] = array(
        "type" => "other",
        "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
    );

    $fields[] = array(
        "type" => "submit",
        "name" => "next",
        "value" => t("Check Requirements", "install.po")
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "</div>"
    );

    $fieldset[] = array("fields" => $fields);

    $content .= Jaris\Forms::generate($parameters, $fieldset);
}

//Requirements check
elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "check_requirements")
{
    $php_version_fine = false;
    $php_gd_fine = false;
    $data_writable = false;
    $php_sqlite_fine = false;

    $step_title = t("Step One - Checking Requirements", "install.po");

    $content = "<table id=\"requirements\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";

    //Check php verison.
    $content .= "<tr>";
    if(substr(PHP_VERSION, 0, 1) > 4)
    {
        $content .= "<td>";
        $content .= "<b>".t("PHP version installed:", "install.po")."</b> "
            . substr(PHP_VERSION, 0, 5)
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"ok\">"
            . t("(OK)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_version_fine = true;
    }
    else
    {
        $content .= "<td>";
        $content .= "<b>" . t("PHP version installed:", "install.po") . "</b> "
            . substr(PHP_VERSION, 0, 5) . " "
            . t("Version 5 or greater is needed.", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"bad\">"
            . t("(ERROR)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_version_fine = false;
    }
    $content .= "</tr>";

    //Check if php gd is installed
    $content .= "<tr>";
    if(extension_loaded('gd') && function_exists('gd_info'))
    {
        $content .= "<td>";
        $content .= "<b>" . t("PHP GD library:", "install.po") . "</b> "
            . t("available", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"ok\">"
            . t("(OK)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_gd_fine = true;
    }
    else
    {
        $content .= "<td>";
        $content .= "<b>" . t("PHP GD library:", "install.po") . "</b> "
            . t("not available", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"bad\">"
            . t("(ERROR)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_gd_fine = false;
    }

    //Check if sites directory is writable
    $content .= "<tr>";
    if(is_writable('sites'))
    {
        $content .= "<td>";
        $content .= "<b>" . t("Write permissions on sites directory:", "install.po") . "</b> "
            . t("yes", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"ok\">"
            . t("(OK)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $data_writable = true;
    }
    else
    {
        $content .= "<td>";
        $content .= "<b>" . t("Write permissions on sites directory:", "install.po") . "</b> "
            . t("no", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"bad\">"
            . t("(ERROR)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $data_writable = false;
    }
    $content .= "</tr>";

    //Check if sqlite is available
    $content .= "<tr>";
    if(function_exists('sqlite_open') || class_exists("SQLite3"))
    {
        $content .= "<td>";
        $content .= "<b>" . t("PHP SQLite library:", "install.po") . "</b> "
            . t("yes", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"ok\">"
            . t("(OK)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_sqlite_fine = true;
    }
    else
    {
        $content .= "<td>";
        $content .= "<b>" . t("PHP SQLite library:", "install.po") . "</b> "
            . t("no", "install.po")
        ;
        $content .= "</td>";

        $content .= "<td>";
        $content .= "<span class=\"bad\">"
            . t("(ERROR)", "install.po")
            . "</span>"
        ;
        $content .= "</td>";

        $php_sqlite_fine = false;
    }
    $content .= "</tr>";


    $content .= "</table>";

    if($php_version_fine && $php_gd_fine && $data_writable && $php_sqlite_fine)
    {
        $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=site_details";
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "other",
            "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "next",
            "value" => t("Continue", "install.po")
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "</div>"
        );

        $fieldset[] = array("fields" => $fields);

        $content .= Jaris\Forms::generate($parameters, $fieldset);
    }
    else
    {
        $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=check_requirements";
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "other",
            "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "next",
            "value" => t("Re-check Requirements.", "install.po")
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "</div>"
        );

        $fieldset[] = array("fields" => $fields);

        $content .= Jaris\Forms::generate($parameters, $fieldset);
    }
}

//Enter site details
elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "site_details")
{
    if(isset($_REQUEST["finish"]))
    {
        $username = $_REQUEST["username"];

        $fields = Jaris\Users::get($username);

        $fields["name"] = $_REQUEST["name"];
        $fields["email"] = $_REQUEST["email"];
        $fields["superadmin"] = 1;

        $error = false;

        if(trim($_REQUEST["password"]) == "")
        {
            $error = true;
        }
        else if($_REQUEST["password"] == $_REQUEST["verify_password"])
        {
            $fields["password"] = $_REQUEST["password"];
        }
        elseif($_REQUEST["password"] != $_REQUEST["verify_password"])
        {
            $error = true;
        }

        if(!$error)
        {
            if(trim($username) == "" || strlen(trim($username)) < 3)
            {
                $error_message = t("Please provide a username.", "install.po");
            }
            elseif(trim($_REQUEST["name"]) == "")
            {
                $error_message = t("Please provide your fullname.", "install.po");
            }
            elseif(trim($_REQUEST["email"]) == "")
            {
                $error_message = t("Please provide your e-mail.", "install.po");
            }
            elseif(Jaris\Forms::validUsername($username))
            {
                if($_REQUEST["distro"] != "none")
                {
                    Jaris\FileSystem::recursiveCopyDir(
                        "install/distros/{$_REQUEST["distro"]}",
                        "sites/default"
                    );
                }

                //Mark user account as active
                $fields["status"] = 1;

                if(Jaris\Users::get($username))
                {
                    $fields["password"] = crypt($_REQUEST["password"]);
                    Jaris\Users::edit($username, "administrator", $fields);
                }
                else
                {
                    $fields["register_date"] = time();
                    Jaris\Users::add($username, "administrator", $fields);
                }

                if(trim($_REQUEST["title"]) != "" && trim($_REQUEST["base_url"]) != "")
                {
                    $footer_message = t("Powered by JarisCMS.", "install.po");

                    //Check if write is possible and continue to write settings
                    if(Jaris\Settings::save("site_status", true, "main"))
                    {
                        Jaris\Settings::save("title", $_REQUEST["title"], "main");
                        Jaris\Settings::save("slogan", $_REQUEST["slogan"], "main");
                        Jaris\Settings::save("timezone", $_REQUEST["timezone"], "main");
                        Jaris\Settings::save("auto_detect_base_url", $_REQUEST["auto_detect_base_url"], "main");
                        Jaris\Settings::save("base_url", $_REQUEST["base_url"], "main");
                        Jaris\Settings::save("footer_message", $footer_message, "main");
                        Jaris\Settings::save("language", $language, "main");
                        Jaris\Settings::save("clean_urls", false, "main");
                        Jaris\Settings::save("theme", "default", "main");
                        Jaris\Settings::save("themes_enabled", serialize(array("default")), "main");
                        Jaris\Settings::save("primary_menu", "primary", "main");
                        Jaris\Settings::save("secondary_menu", "secondary", "main");
                        Jaris\Settings::save("image_compression_maxwidth", "640", "main");
                        Jaris\Settings::save("image_compression_quality", "100", "main");

                        header("Location: " . $base_url . "/install.php?action=mailing_details");
                        exit;
                    }
                    else
                    {
                        $error_message = t("Configuration could not be save. Check your write permissions on the sites directory.", "install.po");
                    }
                }
                else
                {
                    $error_message = t("You need to provide all the fields", "install.po");
                }
            }
            else
            {
                $error_message = t("The administrator login name is invalid.", "install.po");
            }
        }
        else
        {
            $error_message = t("Your password does not match. Try again.", "install.po");
        }
    }

    unset($fields);

    $step_title = t("Step Two - Site Details", "install.po");

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=site_details";
    $parameters["method"] = "post";

    $fields[] = array(
        "type" => "text",
        "name" => "title",
        "label" => t("Site title:", "install.po"),
        "value" => $_REQUEST["title"] ? $_REQUEST["title"] : $title,
        "required" => true,
        "inline" => true
    );

    $fields[] = array(
        "type" => "text",
        "name" => "slogan",
        "label" => t("Slogan:", "install.po"),
        "value" => $_REQUEST["slogan"],
        "inline" => true
    );

    $timezones_list = Jaris\Timezones::getList();
    $timezones = array();
    foreach($timezones_list as $timezone_text)
    {
        $timezones["$timezone_text"] = "$timezone_text";
    }

    $fields[] = array(
        "type" => "select",
        "label" => t("Default timezone:", "install.po"),
        "name" => "timezone",
        "id" => "timezone",
        "value" => $timezones,
        "selected" => $_REQUEST["timezone"]
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "<br />"
    );

    $fields[] = array(
        "type" => "checkbox",
        "name" => "auto_detect_base_url",
        "label" => t("Auto detect base url?", "install.po"),
        "checked" => $_REQUEST["auto_detect_base_url"] ?
            $_REQUEST["auto_detect_base_url"] : true,
        "description" => t("Automatically detects domain even if you change it. Mandatory on multisites.")
    );

    $fields[] = array(
        "type" => "text",
        "name" => "base_url",
        "label" => t("Base url:", "install.po"),
        "required" => true,
        "value" => $_REQUEST["base_url"] ?
            $_REQUEST["base_url"] : str_replace("/install", "", $base_url)
    );

    $fields[] = array(
        "type" => "text",
        "name" => "username",
        "label" => t("Administrator login name:", "install.po"),
        "value" => $_REQUEST["username"],
        "required" => true
    );

    $fields[] = array(
        "type" => "text",
        "name" => "name",
        "label" => t("Administrator full name:", "install.po"),
        "value" => $_REQUEST["name"],
        "required" => true
    );

    $fields[] = array(
        "type" => "text",
        "name" => "email",
        "label" => t("Administrator e-mail:", "install.po"),
        "value" => $_REQUEST["email"],
        "required" => true
    );

    $fields[] = array(
        "type" => "password",
        "name" => "password",
        "label" => t("Administrator password:", "install.po"),
        "required" => true
    );

    $fields[] = array(
        "type" => "password",
        "name" => "verify_password",
        "label" => t("Re-enter administrator password:", "install.po"),
        "required" => true
    );

    $distros = array(t("None") => "none");
    $distros_all = scandir("install/distros");

    foreach($distros_all as $dir_path)
    {
        if($dir_path == "." || $dir_path == "..")
            continue;

        if(!is_dir("install/distros/$dir_path"))
            continue;

        $distros[ucwords(str_replace("_", " ", $dir_path))] = $dir_path;
    }

    $fields[] = array(
        "type" => "select",
        "name" => "distro",
        "label" => t("Distribution template:", "install.po"),
        "value" => $distros,
        "selected" => $_REQUEST["distro"]
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
    );

    $fields[] = array(
        "type" => "submit",
        "name" => "finish",
        "value" => t("Continue", "install.po")
    );

    $fields[] = array("type" => "other", "html_code" => "</div>");

    $fieldset[] = array("fields" => $fields);

    $content .= Jaris\Forms::generate($parameters, $fieldset);
}

//Enter mailing details
elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "mailing_details")
{
    if(isset($_REQUEST["save_mail"]))
    {
        $error = false;

        if(
            trim($_REQUEST["mailer_from_name"]) == "" ||
            trim($_REQUEST["mailer_from_email"]) == ""
        )
        {
            $error = true;
        }

        if(!$error)
        {
            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save("mailer", $_REQUEST["mailer"], "main"))
            {
                Jaris\Settings::save(
                    "mailer_from_name", $_REQUEST["mailer_from_name"], "main"
                );
                Jaris\Settings::save(
                    "mailer_from_email", $_REQUEST["mailer_from_email"], "main"
                );

                Jaris\Settings::save("smtp_auth", $_REQUEST["smtp_auth"], "main");
                Jaris\Settings::save("smtp_ssl", $_REQUEST["smtp_ssl"], "main");
                Jaris\Settings::save("smtp_host", $_REQUEST["smtp_host"], "main");
                Jaris\Settings::save("smtp_port", $_REQUEST["smtp_port"], "main");
                Jaris\Settings::save("smtp_user", $_REQUEST["smtp_user"], "main");
                Jaris\Settings::save("smtp_pass", $_REQUEST["smtp_pass"], "main");

                header("Location: " . $base_url . "/install.php?action=cleanurl_check");
                exit;
            }
            else
            {
                $error_message = t("Configuration could not be save. Check your write permissions on the sites directory.", "install.po");
            }
        }
        else
        {
            $error_message = t("You need to provide all the fields", "install.po");
        }
    }

    unset($fields);

    $step_title = t("Step Three - Mailing Details", "install.po");

    $parameters["name"] = "mailer-settings";
    $parameters["class"] = "mailer-settings";
    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=mailing_details";
    $parameters["method"] = "post";

    $mailer[t("Mail (default)")] = "mail";
    $mailer[t("Sendmail")] = "sendmail";
    $mailer[t("SMTP")] = "smtp";

    $site_settings = Jaris\Settings::getAll("main");

    $fields_main[] = array(
        "type" => "select",
        "label" => t("Mailing system:"),
        "name" => "mailer",
        "id" => "mailer",
        "value" => $mailer,
        "selected" => $site_settings["mailer"]
    );

    $fields_main[] = array(
        "type" => "text",
        "label" => t("From name:"),
        "name" => "mailer_from_name",
        "id" => "mailer_from_name",
        "value" => $site_settings["mailer_from_name"],
        "required" => true,
        "description" => t("The name used on the from email.")
    );

    $fields_main[] = array(
        "type" => "text",
        "label" => t("From e-mail:"),
        "name" => "mailer_from_email",
        "id" => "mailer_from_email",
        "value" => $site_settings["mailer_from_email"],
        "required" => true,
        "description" => t("The email used on the from email.")
    );

    $fieldset[] = array("fields" => $fields_main);

    $stmp_options[t("Enable")] = true;
    $stmp_options[t("Disable")] = false;

    $fields_smtp[] = array(
        "type" => "select",
        "label" => t("Authentication:"),
        "name" => "smtp_auth",
        "id" => "smtp_auth",
        "value" => $stmp_options,
        "selected" => $site_settings["smtp_auth"]
    );

    $fields_smtp[] = array(
        "type" => "select",
        "label" => t("SSL:"),
        "name" => "smtp_ssl",
        "id" => "smtp_ssl",
        "value" => $stmp_options,
        "selected" => $site_settings["smtp_ssl"]
    );

    $fields_smtp[] = array(
        "type" => "text",
        "label" => t("Host:"),
        "name" => "smtp_host",
        "id" => "smtp_host",
        "value" => $site_settings["smtp_host"]
    );

    $fields_smtp[] = array(
        "type" => "text",
        "label" => t("Port:"),
        "name" => "smtp_port",
        "id" => "smtp_port",
        "value" => $site_settings["smtp_port"]
    );

    $fields_smtp[] = array(
        "type" => "text",
        "label" => t("Username:"),
        "name" => "smtp_user",
        "id" => "smtp_user",
        "value" => $site_settings["smtp_user"]
    );

    $fields_smtp[] = array(
        "type" => "password",
        "label" => t("Password:"),
        "name" => "smtp_pass",
        "id" => "smtp_pass",
        "value" => $site_settings["smtp_pass"]
    );

    $fieldset[] = array(
        "name" => t("SMTP Configuration"),
        "fields" => $fields_smtp,
        "collapsible" => true,
        "collapsed" => false
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
    );

    $fields[] = array(
        "type" => "submit",
        "name" => "save_mail",
        "value" => t("Continue", "install.po")
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "</div>"
    );

    $fieldset[] = array("fields" => $fields);

    $content .= Jaris\Forms::generate($parameters, $fieldset);
}

//Check if clean URL are available
elseif(isset($_REQUEST["action"]) && $_REQUEST["action"] == "cleanurl_check")
{
    $step_title = t("Step Four - Clean URL Check", "install.po");

    $content .= t("Url rewrites make url's more readable and easy to acess. Here are the results of the test:", "install.po");

    $content .= "<br /><br />";

    $url = Jaris\Settings::get("base_url", "main");

    $has_clean_url = false;

    if(php_sapi_name() == "cli-server")
    {
        $has_clean_url = true;
    }
    elseif(Jaris\Uri::urlExists($url . "/search"))
    {
        $has_clean_url = true;
    }

    if($has_clean_url)
    {
        $cleanurl = Jaris\Settings::get("clean_urls", "main");

        if(!$cleanurl)
        {
            Jaris\Settings::save("clean_urls", true, "main");
        }

        $content .= "<span class=\"ok\">" .
            t("SUPPORTED and Activated", "install.po") .
            "</span>"
        ;
    }
    else
    {
        $content .= "<span class=\"bad\">" .
            t("NOT SUPPORTED", "install.po") .
            "</span>"
        ;

        $content .= "<br /><br />";

        $content .= t("There are many factors that make clean url not work. If using apache server check if mod_rewrite is activated.", "install.po");
    }

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=finalize_installation";
    $parameters["method"] = "post";

    $fields[] = array(
        "type" => "other",
        "html_code" => "<div style=\"text-align: right; margin-top: 30px;\">"
    );

    $fields[] = array(
        "type" => "submit",
        "name" => "next",
        "value" => t("Continue", "install.po")
    );

    $fields[] = array(
        "type" => "other",
        "html_code" => "</div>"
    );

    $fieldset[] = array("fields" => $fields);

    $content .= Jaris\Forms::generate($parameters, $fieldset);
}

//Finalize Installation
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "finalize_installation")
{
    $step_title = t("Step Five - You are done!", "install.po");

    $content = t("<b>Congratulations,</b> you have successfully installed Jaris content management system. To visit your index site click", "install.po")
        . " <a style=\"font-weight: bold; color: #000000; text-decoration: underline\" href=\"" . str_replace("/install", "", $base_url) . "\">"
        . t("here", "install.po")
        . "</a> "
        . t("and login with the username <b>admin</b> and the password you specified", "install.po") . "."
    ;
}
?>

<!doctype html>
<html>
    <head>
        <title>
            <?php print t("Jaris CMS - Installation Script", "install.po"); ?>
        </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            body
            {
                margin: 0 0 0 0;
                background-color: #fff;
            }

            body *
            {
                font-family: Verdana, Geneva, sans-serif;
                color: #333;
                letter-spacing: 1.2px;
            }

            form
            {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            form > div:last-child
            {
                width: 100%;
            }

            form .caption
            {
                margin-top: 20px;
                margin-bottom: 3px;

                font-weight: bold;
            }

            form fieldset
            {
                margin-top: 20px;
                width: 100%;
            }

            form .field_checkbox
            {
                padding-top: 20px;
            }

            form .field
            {
                width: 50%;
            }

            form .caption .required
            {
                color: #e74c3c;
                font-weight: bold;
            }

            form .description
            {
                font-size: 12px;
            }

            form .form-text
            {
                width: 410px;
            }

            form .form-password
            {
                width: 410px;
            }

            form .form-textarea
            {
                width: 410px;
            }

            form .form-submit
            {
                margin-top: 15px;
            }

            form .edit-user-picture
            {
                margin-bottom: 15px;
            }

            input[type=text], input[type=password], select
            {
                border: solid 2px #bdc3c7;
                padding: 10px;
                display: block;
                transition: all 0.3s;
                border-radius: 5px;
                width: 90%;
                background-color: #f9f9f9 !important;
                /*Fixes issue of padding changing total width*/
                box-sizing: border-box;
            }

            input[type=text]:hover, input[type=password]:hover, select:hover
            {
                box-shadow: 0px 0px 5px #d3d3d3;
            }

            input[type=text]:focus, input[type=password]:focus, select:focus
            {
                border: solid 2px #00aad4;
            }

            input[type=submit], input[type=reset]
            {
                background-color: #ff6600;
                transition: all 0.3s;
                border: 0;
                padding: 15px 10px 15px 10px;
                color: #fff;
                text-transform: uppercase;
                cursor: pointer;
                border-radius: 5px;
                font-weight: bold;
                letter-spacing: 0.6px;
            }

            input[type=submit]:hover, input[type=reset]:hover
            {
                background-color: #00aad4;
            }

            #requirements tr
            {
                transition: all 0.3s;
            }

            #requirements tr:hover
            {
                background-color: #EAEAEA;
            }

            #requirements tr td:first-child
            {
                width: 100%;
                border-bottom: solid #333 1px;
                padding-top: 12px;
                text-align: left;
                padding-bottom: 12px;
            }

            #requirements tr td:last-child
            {
                width: 150px;
                border-bottom: solid #333 1px;
                padding-top: 12px;
                text-align: left;
                padding-bottom: 12px;
            }

            span.ok
            {
                color: #27ae60;
                font-weight: bold;
            }

            span.bad
            {
                color: #e74c3c;
                font-weight: bold;
            }

            div#title
            {
                padding: 15px;
                background-color: #00aad4;
                border-bottom: solid #ccc 3px;
            }

            div#title .container
            {
                max-width: 1200px;
                margin: 0 auto 0 auto;
                color: #fff;
                text-align: left;
                font-size: 24px;
            }

            div#title .container div
            {
                border: solid 1px #fff;
                border-radius: 5px;
                padding: 10px;
                color: #fff;
                font-size: 32px;
                max-width: 200px;
                margin: 0 20px 0 auto;
                display: inline-block;
                transition: all 0.3s;
                font-weight: bold;
                background-color: rgba(255,255,255,0.9);
                color: #00aad4;
            }

            div#content
            {
                max-width: 1200px;
                border: solid #EAEAEA 1px;
                margin-top: 30px;
                margin-bottom: 30px;
                margin-left: 10px;
                margin-right: 10px;
                transition: all 0.3s;
                box-shadow: 0px 0px 2px #d3d3d3;
            }

            div#content:hover
            {
                border: solid 1px #d3d3d3;
            }

            div#content .container
            {
                padding: 15px;
            }

            div#content .content-container
            {
                text-align: left;
                padding-left: 15px;
                font-size: 16px;
            }

            #step-title
            {
                text-align: left;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: solid 1px #00aad4;
            }

            #error
            {
                border: solid 1px #e74c3c;
                color: #e74c3c !important;
                font-weight: bold;
                padding: 7px;
                font-size: 16px;
                margin: 0 10px 0 13px;
            }

            @media all and (max-width: 640px)
            {
                form
                {
                    display: block;
                }

                form .field
                {
                    width: 100%;
                }

                div#title .container
                {
                    text-align: center;
                }

                div#title .container div
                {
                    display: block;
                    margin: 0 auto 10px auto;
                }
            }
        </style>
    </head>

    <body>
    <center>

        <div id="title">
            <div class="container">
                <?php
                    print str_replace(
                        array("Jaris CMS", " - "),
                        array("<div>Jaris CMS</div>", ""),
                        t("Jaris CMS - Installation Script", "install.po")
                    );
                ?>
            </div>
        </div>

        <div id="content">
            <div class="container">
                <h2 id="step-title">
                    <?php print $step_title ?>
                </h2>

                <?php if($error_message){ ?>
                <div id="error">
                    <?php print $error_message ?>
                </div>
                <?php } ?>

                <div class="content-container">
                    <?php print $content ?>
                </div>
            </div>
        </div>

    </center>
</body>

</html>
