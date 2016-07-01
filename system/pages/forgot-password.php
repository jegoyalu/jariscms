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
        print t("Forgot your password?")
    ?>
    field;

    field: content
    <?php
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
                    t("Your password has been reset successfully. Check your e-mail inbox for details.")
                );
            }
            else
            {
                Jaris\View::addMessage($message, "error");
                Jaris\Uri::go("forgot-password");
            }

            Jaris\Uri::go("");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("");
        }

        $parameters["name"] = "reset-user-password";
        $parameters["class"] = "reset-user-password";
        $parameters["action"] = Jaris\Uri::url("forgot-password");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "username",
            "label" => t("Username:"),
            "id" => "username",
            "description" => t("If you remember your username write it down.")
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "<h3>" . t("OR") . "</h3>"
        );

        $fields[] = array(
            "type" => "text",
            "name" => "email",
            "label" => t("E-mail:"),
            "id" => "email",
            "description" => t("If you remember the e-mail that you used to register the account write it down.")
        );

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
    ?>
    field;

    field: is_system
        1
    field;
row;
