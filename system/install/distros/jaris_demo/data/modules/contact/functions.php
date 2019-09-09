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

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_INITIALIZATION,
    function () {
        $uri = $_REQUEST["uri"];

        if ($uri && Jaris\Uri::get() != "admin/pages/add") {
            $page_data = Jaris\Pages::get($uri);
            if ($page_data["type"] == "contact-form") {
                switch (Jaris\Uri::get()) {
                    case "admin/pages/edit":
                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/pages/contact-form/edit",
                                "contact"
                            ),
                            ["uri" => $uri]
                        );
                        // no break
                    default:
                        break;
                }
            }
        } elseif ($_REQUEST["type"]) {
            $page = Jaris\Uri::get();
            if ($page == "admin/pages/add" && $_REQUEST["type"] == "contact-form") {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/pages/contact-form/add",
                        "contact"
                    ),
                    ["type" => "contact-form", "uri" => $uri]
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        if ($content_data["type"] == "contact-form") {
            $form_name = str_replace("/", "-", Jaris\Uri::get());
            $subjects = unserialize($content_data["subjects"]);

            $valid_email = true;

            if (trim($_REQUEST["e_mail"]) != "") {
                if (!Jaris\Forms::validEmail(trim($_REQUEST["e_mail"]))) {
                    Jaris\View::addMessage(
                        t("The e-mail you entered appears to be invalid."),
                        "error"
                    );
                    
                    $valid_email = false;
                }
            }

            if (
                isset($_REQUEST["btnContact"]) &&
                !Jaris\Forms::requiredFieldEmpty($form_name) &&
                $valid_email &&
                contact_files_upload_pass(Jaris\Uri::get())
            ) {
                $fields = contact_get_fields(Jaris\Uri::get());
                $fields_values = [];

                contact_append_fields(Jaris\Uri::get(), $fields_values);

                $html_message = "<b>" . t("Contact form:") . "</b> "
                    . "<a href=\""
                    . Jaris\Uri::url(Jaris\Uri::get())
                    . "\">" . t($content_title)
                    . "</a><br />"
                ;

                $html_message .= "<hr />";

                $to = [];

                if (is_array($subjects) && count($subjects) > 0) {
                    foreach ($subjects as $subject_title => $subject_to) {
                        if ($subject_title == $_REQUEST["subject"]) {
                            if (trim($subject_to) != "") {
                                $to[$subject_to] = $subject_to;
                            }

                            $html_message .= "<b>" . t("Subject") . ":</b> "
                                . t($subject_title)
                                . "<br /><br />"
                            ;

                            break;
                        }
                    }
                }

                if (count($to) <= 0) {
                    $to[$content_data["mail_recipient"]] = $content_data["mail_recipient"];
                }

                $cc = [];

                if (trim($content_data["mail_carbon_copy"]) != "") {
                    $cc_array = explode(",", $content_data["mail_carbon_copy"]);
                    foreach ($cc_array as $cc_email) {
                        $cc[trim($cc_email)] = trim($cc_email);
                    }
                }

                $from = [];
                if (
                    trim($_REQUEST["name"]) != "" &&
                    trim($_REQUEST["e_mail"]) != ""
                ) {
                    $from[trim($_REQUEST["name"])] = trim($_REQUEST["e_mail"]);
                }

                foreach ($fields as $id => $field) {
                    $html_message .= "<b>" . t($field['name']) . ":</b> " .
                        $fields_values[$field['variable_name']] . "<br /><br />"
                    ;
                }

                $html_message .= "<hr />";
                $html_message .= t("IP address:")
                    . " "
                    . $_SERVER["REMOTE_ADDR"] . "<br />"
                ;
                $html_message .= t("User agent:")
                    . " "
                    . $_SERVER["HTTP_USER_AGENT"]
                ;

                $subject = t("Contact from ")
                    . " "
                    . Jaris\Settings::get("mailer_from_name", "main")
                ;

                $attachments = contact_get_file_attachments(Jaris\Uri::get());

                //Register hook so others module can modify the sent email.
                Jaris\Modules::hook(
                    "hook_contact_before_send_email",
                    $to,
                    $cc,
                    $subject,
                    $html_message
                );

                if (
                    Jaris\Mail::send(
                        $to,
                        $subject,
                        $html_message,
                        $alt_message = null,
                        $attachments,
                        $reply_to = [],
                        $bcc = [],
                        $cc,
                        $from
                    )
                ) {
                    //Register hook so others module can modify the sent email.
                    Jaris\Modules::hook(
                        "hook_contact_after_send_email",
                        $content_data
                    );

                    if (
                        trim($_REQUEST["e_mail"]) != "" &&
                        $content_data["mail_autoresponse"]
                    ) {
                        $autoresponse_message = $content_data["mail_autoresponse_message"];

                        foreach ($fields as $field) {
                            $autoresponse_message = str_replace(
                                '{'.$field['name'].'}',
                                $fields_values[$field['variable_name']],
                                $autoresponse_message
                            );
                        }

                        Jaris\Mail::send(
                            $from,
                            $content_data["mail_autoresponse_subject"],
                            $autoresponse_message
                        );
                    }

                    Jaris\View::addMessage(t("Message successfully sent!"));
                } else {
                    Jaris\View::addMessage(
                        t("An error occurred while sending the message. Please try again later."),
                        "error"
                    );
                }
            }

            //Generate contact form
            $parameters["name"] = "$form_name";
            $parameters["class"] = "$form_name";
            $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
            $parameters["method"] = "post";

            $fieldset = [];

            if (is_array($subjects) && count($subjects) > 0) {
                $subject_values = [];
                foreach ($subjects as $subject_title => $subject_to) {
                    $subject_values[t($subject_title)] = $subject_title;
                }

                $fields_subject[] = [
                    "type" => "select",
                    "selected" => $_REQUEST["subject"],
                    "name" => "subject",
                    "label" => t("Subject:"),
                    "id" => "subject",
                    "value" => $subject_values,
                    "required" => true
                ];

                $fieldset[] = ["fields" => $fields_subject];
            }

            $fields = contact_generate_form_fields(Jaris\Uri::get());

            if (count($fields) > 0) {
                $fieldset[] = ["fields" => $fields];
            }

            $fields_validate[] = [
                "type" => "validate_sum",
                "name" => "validation",
                "label" => t("Validation:"),
                "id" => "validation",
                "description" => ""
            ];

            $fieldset[] = ["fields" => $fields_validate];

            $fields_buttons[] = [
                "type" => "submit",
                "name" => "btnContact",
                "value" => t("Send")
            ];

            $fields_buttons[] = [
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            ];

            $fieldset[] = ["fields" => $fields_buttons];

            $contact_content = Jaris\Forms::generate($parameters, $fieldset);

            $content .= $contact_content;

            $content_data["contact_content"] = $contact_content;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (isset($_REQUEST["uri"])) {
            $type = Jaris\Pages::getType($_REQUEST["uri"]);

            if ($type == "contact-form") {
                switch (Jaris\Uri::get()) {
                    case Jaris\Modules::getPageUri("admin/pages/contact-form/edit", "contact"):
                    case "admin/pages/delete":
                    case "admin/pages/blocks":
                    case "admin/pages/files":
                    case "admin/pages/images":
                    case "admin/pages/translate":
                    case "admin/pages/blocks/post/settings":
                    {
                        $new_tab[t("Fields")] = [
                            "uri" => Jaris\Modules::getPageUri(
                                "admin/pages/contact-form/fields",
                                "contact"
                            ),
                            "arguments" => ["uri" => $_REQUEST["uri"]]
                        ];

                        $tabs_array[0] = array_merge($new_tab, $tabs_array[0]);
                    }
                }
            }
        }
    }
);
