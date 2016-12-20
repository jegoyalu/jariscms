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
        Jaris\Authentication::protectedPage(array("add_users"));

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

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-user") &&
            $valid_email &&
            $valid_username
        )
        {
            $fields["name"] = substr(
                Jaris\Util::stripHTMLTags($_REQUEST["full_name"]), 0, 65
            );

            $fields["group"] = $_REQUEST["group"];
            $fields["status"] = $_REQUEST["status"];
            $fields["register_date"] = time();
            $fields["gender"] = $_REQUEST["gender"];

            $fields["birth_date"] = mktime(
                0, 0, 0,
                $_REQUEST["month"],
                $_REQUEST["day"],
                $_REQUEST["year"]
            );

            $error = false;

            if(
                strlen($_REQUEST["password"]) >= 6
            )
            {
                $fields["password"] = $_REQUEST["password"];
            }
            else
            {
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
                    Jaris\View::addMessage(
                        t("The user has been successfully created.")
                    );

                    Jaris\Uri::go("admin/users");
                }
                else
                {
                    Jaris\View::addMessage($message, "error");
                }
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/users");
        }

        unset($fields);

        $parameters["name"] = "add-user";
        $parameters["class"] = "add-user";
        $parameters["action"] = Jaris\Uri::url("admin/users/add");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "text",
            "limit" => 65,
            "value" => empty($_REQUEST["full_name"]) ?
                "" : $_REQUEST["full_name"],
            "name" => "full_name",
            "label" => t("Fullname:"),
            "id" => "full_name",
            "required" => true,
            "description" => t("Your full real name.")
        );

        $fields[] = array(
            "type" => "text",
            "limit" => 60,
            "value" => empty($_REQUEST["username"]) ?
                "" : $_REQUEST["username"],
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
            "value" => empty($_REQUEST["password"]) ?
                "" : $_REQUEST["password"],
            "reveal" => true,
            "required" => true,
            "description" => t("The password used to login, should be at least 6 characters long.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => empty($_REQUEST["email"]) ?
                "" : $_REQUEST["email"],
            "name" => "email",
            "label" => t("E-mail:"),
            "id" => "email",
            "required" => true,
            "description" => t("The email used in case you forgot your password.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => empty($_REQUEST["website"]) ?
                "" : $_REQUEST["website"],
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
            "checked" => empty($_REQUEST["gender"]) ?
                "" : $_REQUEST["gender"],
            "required" => true
        );

        $fieldset[] = array(
            "name" => t("Gender"),
            "fields" => $gender_fields
        );

        //Birthdate fields
        $birth_date_fields[] = array(
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
        );

        $birth_date_fields[] = array(
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
        );

        $birth_date_fields[] = array(
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
                "description" => t("A picture displayed in user post, comments, etc...") . "&nbsp;" . $size
            );

            $fieldset[] = array(
                "name" => t("Picture"),
                "fields" => $fields_picture
            );
        }

        $fields_extra[] = array(
            "type" => "select",
            "name" => "group",
            "label" => t("Group:"),
            "id" => "group",
            "value" => Jaris\Groups::getList(),
            "selected" => empty($_REQUEST["group"]) ?
                "" : $_REQUEST["group"],
            "description" => t("The group where the user belongs.")
        );

        $fields_extra[] = array(
            "type" => "select",
            "name" => "status",
            "label" => t("Status:"),
            "id" => "status",
            "value" => Jaris\Users::getStatuses(),
            "selected" => empty($_REQUEST["status"]) ?
                "" : $_REQUEST["status"],
            "description" => t("The account status of this user.")
        );

        $fieldset[] = array("fields" => $fields_extra);

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
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
