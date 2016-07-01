<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the user edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        $username = empty($_REQUEST["username"]) ?
            Jaris\Authentication::currentUser() : $_REQUEST["username"]
        ;

        if(Jaris\Authentication::currentUser() != $username)
        {
            print t("Edit User");
        }
        else
        {
            print t("My Account Details");
        }
    ?>
    field;

    field: content
    <?php
        if(!Jaris\Authentication::isUserLogged())
        {
            Jaris\Authentication::protectedPage();
        }

        $username = empty($_REQUEST["username"]) ?
            Jaris\Authentication::currentUser() : $_REQUEST["username"]
        ;

        if(trim($username) == "")
        {
            $username = Jaris\Authentication::currentUser();
        }
        elseif(Jaris\Authentication::currentUser() != $username)
        {
            Jaris\Authentication::protectedPage(array("edit_users"));
        }

        $personal_text_lenght = Jaris\Settings::get(
            "user_profiles_personal_text_lenght",
            "main"
        );

        $personal_text_lenght = $personal_text_lenght > 0 ?
            $personal_text_lenght : 300
        ;

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("edit-user"))
        {
            $fields = Jaris\Users::get($username);

            if(trim($fields["email"]) != trim($_REQUEST["email"]))
            {
                if(!Jaris\Forms::validEmail($_REQUEST["email"]))
                {
                    Jaris\View::addMessage(
                        t("The email you entered is not a valid one."),
                        "error"
                    );

                    Jaris\Uri::go(
                        "admin/users/edit",
                        array("username"=>$username)
                    );
                }

                $other_user = Jaris\Users::getByEmail($_REQUEST["email"]);

                if($other_user)
                {
                    Jaris\View::addMessage(
                        t("The email you entered already has a registered account associated to it."),
                        "error"
                    );

                    Jaris\Uri::go(
                        "admin/users/edit",
                        array("username"=>$username)
                    );
                }
            }

            $fields["name"] = substr(Jaris\Util::stripHTMLTags($_REQUEST["name"]), 0, 65);
            $fields["email"] = Jaris\Util::stripHTMLTags($_REQUEST["email"]);
            $fields["website"] = trim(Jaris\Util::stripHTMLTags($_REQUEST["website"]));
            $fields["gender"] = Jaris\Util::stripHTMLTags($_REQUEST["gender"]);

            $fields["personal_text"] = substr(
                trim(Jaris\Util::stripHTMLTags($_REQUEST["personal_text"])),
                0,
                $personal_text_lenght
            );

            $fields["birth_date"] = mktime(
                0, 0, 0,
                intval($_REQUEST["month"]),
                intval($_REQUEST["day"]),
                intval($_REQUEST["year"])
            );

            $previous_user_status = $fields["status"];

            if(
                Jaris\Authentication::groupHasPermission(
                    "edit_users",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                $fields["group"] = $_REQUEST["group"] ?
                    $_REQUEST["group"]
                    :
                    $fields["group"]
                ;

                $fields["status"] = $_REQUEST["status"] ?
                    $_REQUEST["status"]
                    :
                    $fields["status"]
                ;
            }

            $error = false;

            if(
                $_REQUEST["password"] != "" &&
                $_REQUEST["password"] == $_REQUEST["verify_password"]
            )
            {
                $fields["password"] = crypt($_REQUEST["password"]);
            }
            elseif(
                $_REQUEST["password"] != "" &&
                $_REQUEST["password"] != $_REQUEST["verify_password"]
            )
            {
                Jaris\View::addMessage(
                    t("The New password and Verify password doesn't match."),
                    "error"
                );
                $error = true;
            }

            if(!$error)
            {
                $message = "";

                if(
                    Jaris\Settings::get("user_picture", "main") &&
                    isset($_FILES["picture"]["tmp_name"])
                )
                {
                    $message = Jaris\Users::edit(
                        $username,
                        $fields["group"],
                        $fields,
                        $_FILES["picture"]
                    );
                }
                else
                {
                    $message = Jaris\Users::edit(
                        $username,
                        $fields["group"],
                        $fields
                    );
                }

                if($message == "true")
                {
                    Jaris\View::addMessage(
                        t("Your changes have been successfully saved.")
                    );

                    if(
                        Jaris\Authentication::groupHasPermission(
                            "edit_users",
                            Jaris\Authentication::currentUserGroup()
                        )
                    )
                    {
                        //Send notification email to user if account was activated
                        if(
                            $previous_user_status == "0" &&
                            $_REQUEST["status"] == "1"
                        )
                        {
                            $to = array();
                            $to[$fields["name"]] = $fields["email"];

                            $html_message = t("Your account has been activated.") . "<br /><br />";
                            $html_message .= t("Username:") . " " . $username . "<br /><br />";
                            $html_message .= t("Login by visiting:") . " <a target=\"_blank\" href=\"" . Jaris\Uri::url("admin/user") . "\">" . Jaris\Uri::url("admin/user") . "</a>";

                            Jaris\Mail::send($to, t("Account Activated"), $html_message);
                        }
                    }
                }
                else
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );
                }
            }

            if(
                $_REQUEST["password"] != "" &&
                $_REQUEST["password"] == $_REQUEST["verify_password"] &&
                Jaris\Authentication::currentUser() == $username
            )
            {
                Jaris\Authentication::logout();
                Jaris\Authentication::login();
            }

            Jaris\Uri::go("admin/users/edit", array("username" => $username));
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            if(Jaris\Authentication::isAdminLogged())
            {
                Jaris\Uri::go("admin/users/list");
            }
            else
            {
                Jaris\Uri::go("admin/user");
            }
        }

        $arguments["username"] = $username;

        if(
            Jaris\Authentication::groupHasPermission(
                "delete_users",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Delete"),
                "admin/users/delete",
                $arguments
            );
        }

        unset($fields);

        $user_data = Jaris\Users::get($username);

        $parameters["name"] = "edit-user";
        $parameters["class"] = "edit-user";
        $parameters["action"] = Jaris\Uri::url("admin/users/edit");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "hidden",
            "name" => "username",
            "value" => $username
        );

        $fields[] = array(
            "type" => "text",
            "limit" => 65,
            "value" => $user_data["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("The name that others can see.")
        );

        $fields[] = array(
            "type" => "textarea",
            "limit" => $personal_text_lenght,
            "value" => empty($user_data["personal_text"]) ?
                "" : $user_data["personal_text"],
            "name" => "personal_text",
            "label" => t("Personal text:"),
            "id" => "personal_text",
            "description" => t("Writing displayed on your profile page.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => $user_data["email"],
            "name" => "email",
            "label" => t("Email:"),
            "id" => "email",
            "required" => true,
            "description" => t("The email used in case you forgot your password or to contact you.")
        );

        $fields[] = array(
            "type" => "password",
            "name" => "password",
            "label" => t("New password:"),
            "id" => "password",
            "description" => t("You can enter a new password to change actual one.")
        );

        $fields[] = array(
            "type" => "password",
            "name" => "verify_password",
            "label" => t("Verify password:"),
            "id" => "verify_password",
            "description" => t("Re-enter the new password to verify it.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => empty($user_data["website"]) ?
                "" : $user_data["website"],
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
            "checked" => empty($user_data["gender"]) ?
                "m" : $user_data["gender"],
            "required" => true
        );

        $fieldset[] = array(
            "name" => t("Gender"),
            "fields" => $gender_fields
        );

        $user_data["birth_date"] = empty($user_data["birth_date"]) ?
            0 : $user_data["birth_date"]
        ;

        $day = date("j", $user_data["birth_date"]);
        $month = date("n", $user_data["birth_date"]);
        $year = date("Y", $user_data["birth_date"]);

        //Birthdate fields
        $birth_date_fields[] = array(
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "id" => "day",
            "required" => true,
            "value" => Jaris\Date::getDays(),
            "selected" => $day,
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
            "selected" => $month,
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
            "selected" => $year,
            "required" => true,
            "inline" => true
        );

        $fieldset[] = array(
            "name" => t("Birth date"),
            "fields" => $birth_date_fields
        );

        //If user pictures are activated enable user to change or choose a pic.
        if(Jaris\Settings::get("user_picture", "main"))
        {
            if($picture = Jaris\Users::getPicturePath($username))
            {
                $image_src = Jaris\Uri::url("image/user/$username");
                $code = "<div class=\"edit-user-picture\">\n";
                $code .= "<img src=\"$image_src\" />\n";
                $code .= "</div>\n";

                $fields_picture[] = array(
                    "type" => "other",
                    "html_code" => $code
                );
            }

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
                "description" => t("A picture displayed in user post, comments, etc. Maximun size of:") . "&nbsp;" . $size
            );

            $fieldset[] = array(
                "name" => t("Picture"),
                "fields" => $fields_picture
            );
        }

        //Display user group and status selector if user has permissions
        if(
            Jaris\Authentication::groupHasPermission(
                "edit_users",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $fields_extra[] = array(
                "type" => "select",
                "name" => "group",
                "label" => t("Group:"),
                "id" => "group",
                "value" => Jaris\Groups::getList(),
                "selected" => $user_data["group"],
                "description" => t("The group where the user belongs.")
            );

            $fields_extra[] = array(
                "type" => "select",
                "name" => "status",
                "label" => t("Status:"),
                "id" => "status",
                "value" => Jaris\Users::getStatuses(),
                "selected" => $user_data["status"],
                "description" => t("The account status of this user.")
            );

            $fieldset[] = array("fields" => $fields_extra);
        }

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

        if(!empty($user_data["ip_address"]))
        {
            print "<p>" .
                t("Last login from ip:") . " " .
                $user_data["ip_address"] .
                "</p>"
            ;
        }

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;


    field: is_system
        1
    field;
row;
