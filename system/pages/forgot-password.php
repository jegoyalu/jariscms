<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Page where users can reset their password.
 */
exit;
?>

row: 0
    field: title
    <?php
        Jaris\Site::setHTTPStatus(401);
        if(isset($_REQUEST["token"]) && isset($_REQUEST["username"]))
        {
            print t("Password reset form");
        }
        else
        {
            print t("Forgot your password?");
        }
    ?>
    field;

    field: content
    <?php
        if(Jaris\Settings::get("forgot_pass_disabled", "main"))
        {
            Jaris\View::addMessage(
                t("The 'forgot password' functionality is disabled."),
                "error"
            );

            Jaris\Uri::go("admin/user");
        }

        if(isset($_REQUEST["token"]) && isset($_REQUEST["username"]))
        {
            $user_data = Jaris\Users::get($_REQUEST["username"]);

            if(!$user_data)
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("user_not_exist"),
                    "error"
                );

                Jaris\Uri::go("forgot-password");
            }

            if(
                $_REQUEST["token"] != $user_data["token"]
                ||
                $user_data["token_expire"] < time()
            )
            {
                Jaris\View::addMessage(
                    t("The password reset request has expired, please try again."),
                    "error"
                );

                Jaris\Uri::go("forgot-password");
            }

            if(
                isset($_REQUEST["btnChange"])
                &&
                !Jaris\Forms::requiredFieldEmpty("change-user-password")
            )
            {
                $error = false;

                if(
                    strlen($_REQUEST["password"]) >= 6
                )
                {
                    $user_data["password"] = crypt($_REQUEST["password"]);
                }
                else
                {
                    Jaris\View::addMessage(
                        t("The Password should be at least 6 characters long."),
                        "error"
                    );

                    $error = true;
                }

                if(!$error)
                {
                    unset($user_data["token"]);
                    unset($user_data["token_expire"]);
                    unset($user_data["login_fails"]);

                    if(
                        (
                            $message = Jaris\Users::edit(
                                $_REQUEST["username"],
                                $user_data["group"],
                                $user_data
                            )
                        )
                        !=
                        "true"
                    )
                    {
                        Jaris\View::addMessage($message, "error");
                    }
                    else
                    {
                        Jaris\View::addMessage(
                            t("Your password has been reset successfully. Now you can login with the new password.")
                        );

                        Jaris\Uri::go(
                            "admin/user",
                            array("username" => $_REQUEST["email"])
                        );
                    }
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                Jaris\Uri::go("admin/user");
            }

            $parameters["name"] = "change-user-password";
            $parameters["class"] = "change-user-password";
            $parameters["action"] = Jaris\Uri::url("forgot-password");
            $parameters["method"] = "post";


            $fields[] = array(
                "type" => "hidden",
                "name" => "token",
                "value" => $_REQUEST["token"]
            );

            $fields[] = array(
                "type" => "text",
                "name" => "username",
                "label" => t("Current username:"),
                "value" => $_REQUEST["username"],
                "readonly" => true
            );

            $fields[] = array(
                "type" => "text",
                "name" => "email",
                "label" => t("Current e-mail:"),
                "value" => $user_data["email"],
                "readonly" => true
            );

            $fields[] = array(
                "type" => "password",
                "name" => "password",
                "label" => t("New password:"),
                "value" => $_REQUEST["password"],
                "reveal" => true,
                "required" => true,
                "description" => t("The new password used to login, should be at least 6 characters long.")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnChange",
                "value" => t("Change Password")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
        else
        {
            if(isset($_REQUEST["btnReset"]))
            {
                $message = "";
                if(isset($_REQUEST["username"]))
                {
                    $message = Jaris\Users::resetPassword(
                        $_REQUEST["username"]
                    );
                }

                if(
                    $message != "true" &&
                    isset($_REQUEST["email"]) &&
                    $_REQUEST["email"] != ""
                )
                {
                    $message = Jaris\Users::resetPasswordByEmail($_REQUEST["email"]);
                }

                if($message == "true")
                {
                    Jaris\View::addMessage(
                        t("The password reset request was successfully processed. Check your e-mail inbox for details.")
                    );
                }
                else
                {
                    Jaris\View::addMessage($message, "error");
                    Jaris\Uri::go("forgot-password");
                }

                Jaris\Uri::go("admin/user");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                Jaris\Uri::go("admin/user");
            }

            $parameters["name"] = "reset-user-password";
            $parameters["class"] = "reset-user-password";
            $parameters["action"] = Jaris\Uri::url("forgot-password");
            $parameters["method"] = "post";

            if(isset($_REQUEST["username"]) || !isset($_REQUEST["email"]))
            {
                $fields[] = array(
                    "type" => "text",
                    "name" => "username",
                    "label" => t("Username:"),
                    "id" => "username",
                    "value" => $_REQUEST["username"] ?? "",
                    "description" => t("If you remember your username write it down.")
                );
            }

            if(!isset($_REQUEST["username"]) && !isset($_REQUEST["email"]))
            {
                $fields[] = array(
                    "type" => "other",
                    "html_code" => "<h3>" . t("OR") . "</h3>"
                );
            }

            if(isset($_REQUEST["email"]) || !isset($_REQUEST["username"]))
            {
                $fields[] = array(
                    "type" => "text",
                    "name" => "email",
                    "label" => t("E-mail:"),
                    "id" => "email",
                    "value" => $_REQUEST["email"] ?? "",
                    "description" => t("If you remember the e-mail that you used to register the account write it down.")
                );
            }

            $fields[] = array(
                "type" => "submit",
                "name" => "btnReset",
                "value" => t("Reset Password")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
