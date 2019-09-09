<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the user add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create User") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_users"]);

        $email_login = false;

        if (isset($_REQUEST["btnSaveAndEmail"])) {
            $_REQUEST["btnSave"] = 1;
            $email_login = true;
        }

        $valid_email = true;
        if (isset($_REQUEST["email"]) && isset($_REQUEST["btnSave"])) {
            $valid_email = Jaris\Forms::validEmail($_REQUEST["email"]);

            if (!$valid_email) {
                Jaris\View::addMessage(
                    t("The email you entered is not a valid one."),
                    "error"
                );
            } else {
                //Check that the email is not in use by other account
                $db_users = Jaris\Sql::open("users");
                $select = "select email from users where email='" . trim($_REQUEST["email"]) . "'";
                $result = Jaris\Sql::query($select, $db_users);

                if ($data = Jaris\Sql::fetchArray($result)) {
                    $valid_email = false;

                    Jaris\View::addMessage(
                        t("The email you entered already has a registered account associated to it."),
                        "error"
                    );
                }

                Jaris\Sql::close($db_users);
            }
        }

        $valid_username = true;
        if (isset($_REQUEST["username"]) && isset($_REQUEST["btnSave"])) {
            $valid_username = Jaris\Forms::validUsername($_REQUEST["username"]);

            if (!$valid_username) {
                Jaris\View::addMessage(
                    t("The username you provided has invalid characters."),
                    "error"
                );
            }
        }

        if (
            $valid_username
            &&
            isset($_REQUEST["username"])
            &&
            isset($_REQUEST["btnSave"])
        ) {
            if (strlen($_REQUEST["username"]) < 3) {
                Jaris\View::addMessage(
                    t("The username should be at least 3 characters long."),
                    "error"
                );

                $valid_username = false;
            } elseif (strlen($_REQUEST["username"]) > 60) {
                Jaris\View::addMessage(
                    t("The username exceeds from 60 characters."),
                    "error"
                );

                $valid_username = false;
            }
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-user") &&
            $valid_email &&
            $valid_username
        ) {
            $fields["name"] = substr(
                Jaris\Util::stripHTMLTags($_REQUEST["full_name"]),
                0,
                65
            );

            $fields["group"] = $_REQUEST["group"];
            $fields["status"] = $_REQUEST["status"];
            $fields["register_date"] = time();
            $fields["gender"] = $_REQUEST["gender"];

            $fields["birth_date"] = mktime(
                0,
                0,
                0,
                intval($_REQUEST["month"]),
                intval($_REQUEST["day"]),
                intval($_REQUEST["year"])
            );

            $fields["theme"] = $_REQUEST["theme"];

            $error = false;

            if (
                strlen($_REQUEST["password"]) >= 6
            ) {
                $fields["password"] = $_REQUEST["password"];
            } else {
                Jaris\View::addMessage(
                    t("The Password should be at least 6 characters long."),
                    "error"
                );
                $error = true;
            }

            $fields["email"] = trim($_REQUEST["email"]);

            $fields["website"] = trim(
                Jaris\Util::stripHTMLTags($_REQUEST["website"])
            );

            if (!$error) {
                $message = "";

                if (Jaris\Settings::get("user_picture", "main")) {
                    $message = Jaris\Users::add(
                        $_REQUEST["username"],
                        $fields["group"],
                        $fields,
                        $_FILES["picture"]
                    );
                } else {
                    $message = Jaris\Users::add(
                        $_REQUEST["username"],
                        $fields["group"],
                        $fields
                    );
                }

                if ($message == "true") {
                    Jaris\View::addMessage(
                        t("The user has been successfully created.")
                    );

                    t("Added user '{username}'.");

                    Jaris\Logger::info(
                        "Added user '{username}'.",
                        [
                            "username" => $_REQUEST["username"]
                        ]
                    );

                    if ($email_login) {
                        if (
                            Jaris\Mail::send(
                                [
                                    $fields["name"] => $fields["email"]
                                ],
                                t("Account Created"),
                                sprintf(
                                    t("Hi %s,<br /><br /> We have created an account for you on %s. Your login details are:<br /><br /><strong>Username:</strong> %s or %s <br /><strong>Password:</strong> %s <br /><br />You can login by visitng:<br /><a href=\"%s\">%s</a>"),
                                    $fields['name'],
                                    Jaris\Settings::get("title", "main"),
                                    $_REQUEST["username"],
                                    $fields['email'],
                                    $_REQUEST["password"],
                                    Jaris\Uri::url("admin/user"),
                                    Jaris\Uri::url("admin/user")
                                )
                            )
                        ) {
                            Jaris\View::addMessage(
                                t("Login details successfully sent to the user.")
                            );
                        } else {
                            Jaris\View::addMessage(
                                t("An error occured while sending the login details to the user.")
                            );
                        }
                    }

                    Jaris\Uri::go("admin/users/list");
                } else {
                    Jaris\View::addMessage($message, "error");
                }
            }
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/users/list");
        }

        unset($fields);

        $parameters["name"] = "add-user";
        $parameters["class"] = "add-user";
        $parameters["action"] = Jaris\Uri::url("admin/users/add");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = [
            "type" => "text",
            "limit" => 65,
            "value" => empty($_REQUEST["full_name"]) ?
                "" : $_REQUEST["full_name"],
            "name" => "full_name",
            "label" => t("Fullname:"),
            "id" => "full_name",
            "required" => true,
            "description" => t("Your full real name.")
        ];

        $fields[] = [
            "type" => "text",
            "limit" => 60,
            "value" => empty($_REQUEST["username"]) ?
                "" : $_REQUEST["username"],
            "name" => "username",
            "label" => t("Username:"),
            "id" => "name",
            "required" => true,
            "description" => t("The name that you are going to use to log in, at least 3 characters long. Permitted characters are A to Z, 0 to 9 and underscores.")
        ];

        $fields[] = [
            "type" => "password",
            "name" => "password",
            "label" => t("Password:"),
            "id" => "password",
            "value" => empty($_REQUEST["password"]) ?
                "" : $_REQUEST["password"],
            "reveal" => true,
            "required" => true,
            "description" => t("The password used to login, should be at least 6 characters long.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => empty($_REQUEST["email"]) ?
                "" : $_REQUEST["email"],
            "name" => "email",
            "label" => t("E-mail:"),
            "id" => "email",
            "required" => true,
            "description" => t("The email used in case you forgot your password.")
        ];

        $fields[] = [
            "type" => "text",
            "value" => empty($_REQUEST["website"]) ?
                "" : $_REQUEST["website"],
            "name" => "website",
            "label" => t("Website:"),
            "id" => "website",
            "description" => t("Corporate or personal website.")
        ];

        $fieldset[] = ["fields" => $fields];

        //Gender Fields
        $gender[t("Male")] = "m";
        $gender[t("Female")] = "f";

        $gender_fields[] = [
            "type" => "radio",
            "name" => "gender",
            "id" => "gender",
            "value" => $gender,
            "checked" => empty($_REQUEST["gender"]) ?
                "" : $_REQUEST["gender"],
            "required" => true
        ];

        $fieldset[] = [
            "name" => t("Gender"),
            "fields" => $gender_fields
        ];

        //Birthdate fields
        $birth_date_fields[] = [
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "id" => "day",
            "required" => true,
            "value" => Jaris\Date::getDays(),
            "selected" => empty($_REQUEST["day"]) ?
                "" : $_REQUEST["day"],
            "required" => true,
            "inline" => true
        ];

        $birth_date_fields[] = [
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "id" => "month",
            "required" => true,
            "value" => Jaris\Date::getMonths(),
            "selected" => empty($_REQUEST["month"]) ?
                "" : $_REQUEST["month"],
            "required" => true,
            "inline" => true
        ];

        $birth_date_fields[] = [
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "id" => "year",
            "required" => true,
            "value" => Jaris\Date::getYears(),
            "selected" => empty($_REQUEST["year"]) ?
                "" : $_REQUEST["year"],
            "required" => true,
            "inline" => true
        ];

        $fieldset[] = [
            "name" => t("Birth date"),
            "fields" => $birth_date_fields
        ];

        //If user pictures are activated.
        if (Jaris\Settings::get("user_picture", "main")) {
            $size = null;

            if (!($size = Jaris\Settings::get("user_picture_size", "main"))) {
                $size = "150x150";
            }

            $fields_picture[] = [
                "id" => "picture",
                "type" => "file",
                "name" => "picture",
                "description" => t("A picture displayed in user post, comments, etc...") . "&nbsp;" . $size
            ];

            $fieldset[] = [
                "name" => t("Picture"),
                "fields" => $fields_picture
            ];
        }

        $fields_extra[] = [
            "type" => "select",
            "name" => "group",
            "label" => t("Group:"),
            "id" => "group",
            "value" => Jaris\Groups::getList(),
            "selected" => empty($_REQUEST["group"]) ?
                "" : $_REQUEST["group"],
            "description" => t("The group where the user belongs.")
        ];

        $fields_extra[] = [
            "type" => "select",
            "name" => "status",
            "label" => t("Status:"),
            "id" => "status",
            "value" => Jaris\Users::getStatuses(),
            "selected" => empty($_REQUEST["status"]) ?
                "" : $_REQUEST["status"],
            "description" => t("The account status of this user.")
        ];

        $fields_extra[] = [
            "type" => "select",
            "name" => "theme",
            "label" => t("Theme:"),
            "value" => Jaris\Themes::getSelectList(),
            "selected" => $_REQUEST["theme"],
            "description" => t("The theme for the site.")
        ];

        $fieldset[] = ["fields" => $fields_extra];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnSaveAndEmail",
            "value" => t("Save and Send Login by E-mail")
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_submit];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
