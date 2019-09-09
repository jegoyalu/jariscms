<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Terms Generator"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("terms-generator")
        ) {
            // Add / Edit terms page
            $terms_page = Jaris\Pages::get($_REQUEST["terms_uri"]);

            if (!$terms_page) {
                $terms_page = [
                    "title" => "Terms and Conditions",
                    "content" => "",
                    "input_format" => "full_html",
                    "groups" => [],
                    "users" => [],
                    "created_date" => time(),
                    "author" => Jaris\Authentication::currentUser(),
                    "type" => "page"
                ];

                Jaris\Pages::add($_REQUEST["terms_uri"], $terms_page, $terms_uri);
            }

            $terms_page["content"] = str_replace(
                [
                    "[the company]",
                    "[contact us]",
                    "[last updated]"
                ],
                [
                    $_REQUEST["company"],
                    Jaris\Uri::url($_REQUEST["contact"]),
                    date("n/j/Y", time())
                ],
                file_get_contents(
                    Jaris\Modules::directory("terms_generator")
                        . "templates/terms_and_conditions.txt"
                )
            );

            Jaris\Pages::edit($_REQUEST["terms_uri"], $terms_page);

            // Add / Edit privacy page
            $privacy_page = Jaris\Pages::get($_REQUEST["privacy_uri"]);

            if (!$privacy_page) {
                $privacy_page = [
                    "title" => "Privacy Policy",
                    "content" => "",
                    "input_format" => "full_html",
                    "groups" => [],
                    "users" => [],
                    "created_date" => time(),
                    "author" => Jaris\Authentication::currentUser(),
                    "type" => "page"
                ];

                Jaris\Pages::add($_REQUEST["privacy_uri"], $privacy_page, $privacy_uri);
            }

            $privacy_page["content"] = str_replace(
                [
                    "[the company]",
                    "[contact us]",
                    "[last updated]"
                ],
                [
                    $_REQUEST["company"],
                    Jaris\Uri::url($_REQUEST["contact"]),
                    date("n/j/Y", time())
                ],
                file_get_contents(
                    Jaris\Modules::directory("terms_generator")
                        . "templates/privacy_policy.txt"
                )
            );

            Jaris\Pages::edit($_REQUEST["privacy_uri"], $privacy_page);

            // Add / Edit return policy page
            if (trim($_REQUEST["return_uri"]) != "") {
                $returns_page = Jaris\Pages::get($_REQUEST["return_uri"]);

                if (!$returns_page) {
                    $returns_page = [
                        "title" => "Return Policy",
                        "content" => "",
                        "input_format" => "full_html",
                        "groups" => [],
                        "users" => [],
                        "created_date" => time(),
                        "author" => Jaris\Authentication::currentUser(),
                        "type" => "page"
                    ];

                    Jaris\Pages::add($_REQUEST["return_uri"], $returns_page, $returns_uri);
                }

                $returns_page["content"] = str_replace(
                    [
                        "[the company]",
                        "[contact us]",
                        "[last updated]",
                        "[return days]"
                    ],
                    [
                        $_REQUEST["company"],
                        Jaris\Uri::url($_REQUEST["contact"]),
                        date("n/j/Y", time()),
                        trim($_REQUEST["return_days"]) == "" ?
                            "30" : $_REQUEST["return_days"]
                    ],
                    file_get_contents(
                        Jaris\Modules::directory("terms_generator")
                            . "templates/return_policy.txt"
                    )
                );

                Jaris\Pages::edit($_REQUEST["return_uri"], $returns_page);
            }

            Jaris\View::addMessage(t("Terms generated successfully!"));

            Jaris\Uri::go("admin/settings");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "terms-generator";
        $parameters["class"] = "terms-generator";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/terms-generator",
                "terms_generator"
            )
        );
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "text",
            "name" => "company",
            "label" => t("Company name:"),
            "description" => t("The company name as it will appear on the generated terms."),
            "required" => true
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "contact",
            "label" => t("Contact us:"),
            "description" => t("The uri of existing contact us page."),
            "required" => true
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "terms_uri",
            "label" => t("Terms and conditions:"),
            "value" => "terms-and-conditions",
            "description" => t("The uri of the terms and conditions page to create. Note that if the provided uri exists the content of that page will be overwritten."),
            "required" => true
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "privacy_uri",
            "label" => t("Privacy policy:"),
            "value" => "privacy-policy",
            "description" => t("The uri of the privacy policy page to create. Note that if the provided uri exists the content of that page will be overwritten."),
            "required" => true
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "return_uri",
            "label" => t("Return policy:"),
            "description" => t("The uri of the return policy page to create. Note that if the provided uri exists the content of that page will be overwritten.")
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "return_days",
            "label" => t("Return policy:"),
            "value" => 30,
            "description" => t("The amount of days for applicable return after doing the order.")
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


