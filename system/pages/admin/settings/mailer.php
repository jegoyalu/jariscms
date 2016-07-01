<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the site mailer settings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Mailer Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $site_settings = Jaris\Settings::getAll("main");

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("mailer-settings")
        )
        {
            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save("mailer", $_REQUEST["mailer"], "main"))
            {
                Jaris\Settings::save("mailer_from_name", $_REQUEST["mailer_from_name"], "main");
                Jaris\Settings::save("mailer_from_email", $_REQUEST["mailer_from_email"], "main");

                Jaris\Settings::save("smtp_auth", $_REQUEST["smtp_auth"], "main");
                Jaris\Settings::save("smtp_ssl", $_REQUEST["smtp_ssl"], "main");
                Jaris\Settings::save("smtp_host", $_REQUEST["smtp_host"], "main");
                Jaris\Settings::save("smtp_port", $_REQUEST["smtp_port"], "main");
                Jaris\Settings::save("smtp_user", $_REQUEST["smtp_user"], "main");
                Jaris\Settings::save("smtp_pass", $_REQUEST["smtp_pass"], "main");

                Jaris\View::addMessage(t("Your settings have been successfully saved."));

                Jaris\Site::$clean_urls = $_REQUEST["clean_urls"];
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );

                Jaris\Uri::go("admin/settings/mailer");
            }

            Jaris\Uri::go("admin/settings");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "mailer-settings";
        $parameters["class"] = "mailer-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings/mailer");
        $parameters["method"] = "post";

        $mailer[t("Mail (default)")] = "mail";
        $mailer[t("Sendmail")] = "sendmail";
        $mailer[t("SMTP")] = "smtp";

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