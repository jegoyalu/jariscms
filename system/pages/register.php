<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the create account page.
 */
exit;
?>

row: 0
    field: title
        <?php print t("Create My Account") ?>
    field;

    field: content
    <?php
        if(
            !Jaris\Settings::get("new_registrations", "main") &&
            !Jaris\Authentication::isAdminLogged()
        )
        {
            Jaris\View::addMessage(
                t("Registrations are disabled, sorry for any inconvinience."),
                "error"
            );

            Jaris\Uri::go("");
        }

        //Store return url
        if(isset($_REQUEST["return"]))
        {
            Jaris\Session::addCookie("return_url", $_REQUEST["return"]);
        }

        if(Jaris\Authentication::isUserLogged())
        {
            Jaris\Uri::go("admin/user");
        }

        $valid_email = true;
        if(isset($_REQUEST["email"]))
        {
            $valid_email = Jaris\Forms::validEmail($_REQUEST["email"]);

            if(!$valid_email)
            {
                Jaris\View::addMessage(
                    t("The email you entered is not a valid one."),
                    "error"
                );
            }
            else
            {
                //Check that the email is not in use by other account
                $db_users = Jaris\Sql::open("users");
                $select = "select email from users where email='" . trim($_REQUEST["email"]) . "'";
                $result = Jaris\Sql::query($select, $db_users);

                if($data = Jaris\Sql::fetchArray($result))
                {
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
        if(isset($_REQUEST["username"]))
        {
            $valid_username = Jaris\Forms::validUsername($_REQUEST["username"]);

            if(!$valid_username)
            {
                Jaris\View::addMessage(
                    t("The username you provided has invalid characters."),
                    "error"
                );
            }
        }

        if($valid_username && isset($_REQUEST["username"]))
        {
            if(strlen($_REQUEST["username"]) < 3)
            {
                Jaris\View::addMessage(
                    t("The username should be at least 3 characters long."),
                    "error"
                );

                $valid_username = false;
            }
            else if(strlen($_REQUEST["username"]) > 60)
            {
                Jaris\View::addMessage(
                    t("The username exceeds from 60 characters."),
                    "error"
                );

                $valid_username = false;
            }
        }

        $agree_terms = true;
        if(
            Jaris\Settings::get("registration_terms", "main") &&
            isset($_REQUEST["accept_terms_conditions"])
        )
        {
            $agree_terms = $_REQUEST["accept_terms_conditions"];

            if(!$agree_terms)
            {
                Jaris\View::addMessage(
                    t("You must agree to the terms and conditions of this website in order to register."),
                    "error"
                );
            }
        }

        $groups = unserialize(
            Jaris\Settings::get("registration_groups", "main")
        );

        $groups_approval = unserialize(
            Jaris\Settings::get("registration_groups_approval", "main")
        );

        $valid_group = true;

        if(
            Jaris\Settings::get("registration_can_select_group", "main") &&
            isset($_REQUEST["btnSave"])
        )
        {
            if(count($groups) > 0)
            {
                if(
                    !in_array($_REQUEST["group"], $groups) ||
                    !isset($_REQUEST["group"])
                )
                {
                    Jaris\View::addMessage(
                        t("Please select a valid Account Type."),
                        "error"
                    );

                    $valid_group = false;
                }
            }
            else
            {
                $_REQUEST["group"] = "regular";
            }
        }
        else
        {
            $_REQUEST["group"] = "regular";
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("register-user") &&
            $valid_email && $valid_username && $valid_group && $agree_terms
        )
        {
            $fields["name"] = substr(
                Jaris\Util::stripHTMLTags($_REQUEST["full_name"]),
                0,
                65
            );

            $fields["group"] = $_REQUEST["group"];
            $fields["register_date"] = time();
            $fields["ip_address"] = $_SERVER["REMOTE_ADDR"];
            $fields["gender"] = $_REQUEST["gender"];

            $fields["birth_date"] = mktime(
                0, 0, 0,
                intval($_REQUEST["month"]),
                intval($_REQUEST["day"]),
                intval($_REQUEST["year"])
            );

            if(
                (Jaris\Settings::get("registration_needs_approval", "main") &&
                !Jaris\Settings::get("registration_can_select_group", "main")) ||

                (Jaris\Settings::get("registration_can_select_group", "main") &&
                in_array($_REQUEST["group"], $groups_approval))
            )
            {
                $fields["status"] = "0";
            }
            else
            {
                $fields["status"] = "1";
            }

            $error = false;

            if(
                $_REQUEST["password"] != "" &&
                $_REQUEST["password"] == $_REQUEST["verify_password"]
            )
            {
                $fields["password"] = $_REQUEST["password"];
            }
            elseif(
                $_REQUEST["password"] == "" ||
                $_REQUEST["password"] != $_REQUEST["verify_password"]
            )
            {
                Jaris\View::addMessage(
                    t("The Password and Verify password doesn't match."),
                    "error"
                );

                $error = true;
            }

            if($_REQUEST["email"] == $_REQUEST["verify_email"])
            {
                $fields["email"] = trim($_REQUEST["email"]);
            }
            else
            {
                Jaris\View::addMessage(
                    t("The e-mail and verify e-mail doesn't match."),
                    "error"
                );

                $error = true;
            }

            $fields["website"] = trim(
                Jaris\Util::stripHTMLTags($_REQUEST["website"])
            );

            if(!$error)
            {
                $message = "";

                if(Jaris\Settings::get("user_picture", "main"))
                {
                    $message = Jaris\Users::add(
                        $_REQUEST["username"],
                        $fields["group"],
                        $fields,
                        $_FILES["picture"]
                    );
                }
                else
                {
                    $message = Jaris\Users::add(
                        $_REQUEST["username"],
                        $fields["group"],
                        $fields
                    );
                }

                if($message == "true")
                {
                    if(
                        (Jaris\Settings::get("registration_needs_approval", "main") &&
                        !Jaris\Settings::get("registration_can_select_group", "main")) ||

                        (Jaris\Settings::get("registration_can_select_group", "main") &&
                        in_array($_REQUEST["group"], $groups_approval))
                    )
                    {
                        Jaris\View::addMessage(t("Your registration is awaiting for approval. If the registration is approved you will receive an email notification."));

                        Jaris\Mail::sendRegistrationNotification($_REQUEST["username"]);
                    }
                    else
                    {
                        Jaris\View::addMessage(t("Your account has been successfully created. Enter your details to login."));
                    }

                    Jaris\Uri::go("admin/user");
                }
                else
                {
                    Jaris\View::addMessage($message, "error");
                }
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("");
        }

        unset($fields);

        $parameters["name"] = "register-user";
        $parameters["class"] = "register-user";
        $parameters["action"] = Jaris\Uri::url("register");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "text",
            "limit" => 65,
            "value" => $_REQUEST["full_name"],
            "name" => "full_name",
            "label" => t("Fullname:"),
            "id" => "full_name",
            "required" => true,
            "description" => t("Your full real name.")
        );

        $fields[] = array(
            "type" => "text",
            "limit" => 60,
            "value" => $_REQUEST["username"],
            "name" => "username",
            "label" => t("Username:"),
            "id" => "name",
            "required" => true,
            "description" => t("The name that you are going to use to log in, at least 3 characters long. Permitted characters are A to Z, 0 to 9 and underscores.")
        );

        $fields[] = array(
            "type" => "password",
            "name" => "password",
            "label" => t("Password:"),
            "id" => "password",
            "required" => true,
            "inline" => true,
            "description" => t("The password used to login, should be at least 6 characters long.")
        );

        $fields[] = array(
            "type" => "password",
            "name" => "verify_password",
            "label" => t("Verify password:"),
            "id" => "verify_password",
            "required" => true,
            "inline" => true,
            "description" => t("Re-enter the password to verify it.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $_REQUEST["email"],
            "name" => "email",
            "label" => t("E-mail:"),
            "id" => "email",
            "required" => true,
            "inline" => true,
            "description" => t("The email used in case you forgot your password.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $_REQUEST["verify_email"],
            "name" => "verify_email",
            "label" => t("Verify the e-mail:"),
            "id" => "verify_email",
            "required" => true,
            "inline" => true,
            "description" => t("Re-enter the e-mail to verify is correct.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $_REQUEST["website"],
            "name" => "website",
            "label" => t("Website:"),
            "id" => "website",
            "description" => t("Corporate or personal website.")
        );

        $fieldset[] = array("fields" => $fields);

        //Gender Fields
        $gender[t("Male")] = "m";
        $gender[t("Female")] = "f";

        $gender_fields[] = array(
            "type" => "radio",
            "name" => "gender",
            "id" => "gender",
            "value" => $gender,
            "checked" => $_REQUEST["gender"],
            "required" => true
        );

        $fieldset[] = array("name" => t("Gender"), "fields" => $gender_fields);

        //Birthdate fields
        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "id" => "day",
            "required" => true,
            "value" => Jaris\Date::getDays(),
            "selected" => $_REQUEST["day"],
            "required" => true,
            "inline" => true
        );

        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "id" => "month",
            "required" => true,
            "value" => Jaris\Date::getMonths(),
            "selected" => $_REQUEST["month"],
            "required" => true,
            "inline" => true
        );

        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "id" => "year",
            "required" => true,
            "value" => Jaris\Date::getYears(),
            "selected" => $_REQUEST["year"],
            "required" => true,
            "inline" => true
        );

        $fieldset[] = array(
            "name" => t("Birth date"),
            "fields" => $birth_date_fields
        );

        //If user pictures are activated.
        if(Jaris\Settings::get("user_picture", "main"))
        {
            $size = null;
            if(!($size = Jaris\Settings::get("user_picture_size", "main")))
            {
                $size = "150x150";
            }

            $fields_picture[] = array(
                "id" => "picture",
                "type" => "file",
                "name" => "picture",
                "valid_types" => "gif,jpg,jpeg,png",
                "description" => t("A logo or picture of your self.") .
                "&nbsp;" . $size
            );

            $fieldset[] = array(
                "name" => t("Picture"),
                "fields" => $fields_picture
            );
        }

        if(Jaris\Settings::get("registration_can_select_group", "main"))
        {
            if(count($groups) > 0)
            {
                $fields_group = array();
                foreach($groups as $group_machine_name)
                {
                    $group_data = Jaris\Groups::get($group_machine_name);

                    $requires_approval = "";
                    if(in_array($group_machine_name, $groups_approval))
                    {
                        $requires_approval .= t("(requires approval)");
                    }

                    $fields_group[] = array(
                        "type" => "radio",
                        "checked" => (
                            $group_machine_name == $_REQUEST["group"] ? true : false
                        ),
                        "name" => "group",
                        "description" => $group_data["description"] . " " . $requires_approval,
                        "value" => array(
                            $group_data["name"] => $group_machine_name
                        )
                    );
                }

                $fieldset[] = array(
                    "name" => t("Account Type"),
                    "fields" => $fields_group
                );
            }
        }

        if(Jaris\Settings::get("registration_terms", "main"))
        {
            $terms[t("I do not agree")] = false;
            $terms[t("I agree")] = true;

            $fields_submit[] = array(
                "type" => "textarea",
                "name" => "terms_conditions",
                "label" => t("Terms and Conditions:"),
                "id" => "terms_conditions",
                "value" => Jaris\Settings::get("registration_terms", "main"),
                "readonly" => true,
                "description" => t("The terms and conditions that you have to accept in order to register.")
            );

            $fields_submit[] = array(
                "type" => "radio",
                "name" => "accept_terms_conditions",
                "id" => "accept_terms_conditions",
                "value" => $terms,
                "checked" => false
            );
        }

        $fields_submit[] = array(
            "type" => "validate_sum",
            "label" => t("Validation:"),
            "required" => true,
            "name" => "captcha",
            "id" => "captcha"
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Register")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_submit);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
