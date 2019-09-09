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
        Jaris\Authentication::protectedPage(["edit_settings"]);

        $site_settings = Jaris\Settings::getAll("main");

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("mailer-settings")
        ) {
            //Check if write is possible and continue to write settings
            if (Jaris\Settings::save("mailer", $_REQUEST["mailer"], "main")) {
                Jaris\Settings::save(
                    "mailer_from_name",
                    $_REQUEST["mailer_from_name"],
                    "main"
                );

                Jaris\Settings::save(
                    "mailer_from_email",
                    $_REQUEST["mailer_from_email"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_auth",
                    $_REQUEST["smtp_auth"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_cert_validation",
                    $_REQUEST["smtp_cert_validation"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_force_from_email",
                    $_REQUEST["smtp_force_from_email"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_encryption",
                    $_REQUEST["smtp_encryption"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_host",
                    $_REQUEST["smtp_host"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_port",
                    $_REQUEST["smtp_port"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_user",
                    $_REQUEST["smtp_user"],
                    "main"
                );

                Jaris\Settings::save(
                    "smtp_pass",
                    $_REQUEST["smtp_pass"],
                    "main"
                );

                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                t("Updated mailer settings.");

                Jaris\Logger::info("Updated mailer settings.");

                Jaris\Site::$clean_urls = $_REQUEST["clean_urls"];
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );

                Jaris\Uri::go("admin/settings/mailer");
            }

            Jaris\Uri::go("admin/settings");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "mailer-settings";
        $parameters["class"] = "mailer-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings/mailer");
        $parameters["method"] = "post";

        $mailer[t("Mail (default)")] = "mail";
        $mailer[t("Sendmail")] = "sendmail";
        $mailer[t("SMTP")] = "smtp";

        $fields_main[] = [
            "type" => "select",
            "label" => t("Mailing system:"),
            "name" => "mailer",
            "id" => "mailer",
            "value" => $mailer,
            "selected" => $site_settings["mailer"]
        ];

        $fields_main[] = [
            "type" => "text",
            "label" => t("From name:"),
            "name" => "mailer_from_name",
            "id" => "mailer_from_name",
            "value" => $site_settings["mailer_from_name"],
            "required" => true,
            "description" => t("The name used on the from email.")
        ];

        $fields_main[] = [
            "type" => "text",
            "label" => t("From e-mail:"),
            "name" => "mailer_from_email",
            "id" => "mailer_from_email",
            "value" => $site_settings["mailer_from_email"],
            "required" => true,
            "description" => t("The email used on the from email.")
        ];

        $fieldset[] = ["fields" => $fields_main];

        $stmp_options[t("Enable")] = true;
        $stmp_options[t("Disable")] = false;

        $fields_smtp[] = [
            "type" => "select",
            "label" => t("Authentication:"),
            "name" => "smtp_auth",
            "id" => "smtp_auth",
            "value" => $stmp_options,
            "selected" => $site_settings["smtp_auth"]
        ];

        $fields_smtp[] = [
            "type" => "select",
            "label" => t("Encryption method:"),
            "name" => "smtp_encryption",
            "value" => [
                "NONE" => "",
                "TLS" => "tls",
                "SSL" => "ssl"
            ],
            "selected" => $site_settings["smtp_encryption"]
        ];

        $fields_smtp[] = [
            "type" => "select",
            "label" => t("Validation of Certificate:"),
            "name" => "smtp_cert_validation",
            "value" => $stmp_options,
            "selected" => $site_settings["smtp_cert_validation"],
            "description" => t("Verifies that the smtp server certificate is valid.")
        ];

        $fields_smtp[] = [
            "type" => "select",
            "label" => t("Force FROM e-mail:"),
            "name" => "smtp_force_from_email",
            "value" => [t("No") => false, t("Yes") => true],
            "selected" => $site_settings["smtp_force_from_email"],
            "description" => t("Don't allow overriding the FROM e-mail to prevent issues with some smtp providers and use the smpt username as FROM e-mail.")
        ];

        $fields_smtp[] = [
            "type" => "text",
            "label" => t("Host:"),
            "name" => "smtp_host",
            "id" => "smtp_host",
            "value" => $site_settings["smtp_host"]
        ];

        $fields_smtp[] = [
            "type" => "text",
            "label" => t("Port:"),
            "name" => "smtp_port",
            "id" => "smtp_port",
            "value" => $site_settings["smtp_port"]
        ];

        $fields_smtp[] = [
            "type" => "text",
            "label" => t("Username:"),
            "name" => "smtp_user",
            "id" => "smtp_user",
            "value" => $site_settings["smtp_user"]
        ];

        $fields_smtp[] = [
            "type" => "password",
            "label" => t("Password:"),
            "name" => "smtp_pass",
            "id" => "smtp_pass",
            "value" => $site_settings["smtp_pass"]
        ];

        $fieldset[] = [
            "name" => t("SMTP Configuration"),
            "fields" => $fields_smtp,
            "collapsible" => true,
            "collapsed" => false
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
