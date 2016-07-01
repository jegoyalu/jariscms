<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the administration login page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("My Account") ?>
    field;

    field: content
    <?php
        //Store return url
        if(isset($_REQUEST["return"]))
        {
            $_SESSION["return_url"] = $_REQUEST["return"];
        }

        if(
            !Jaris\Authentication::isUserLogged() &&
            Jaris\Settings::get("login_ssl", "main") &&
            !Jaris\System::isSSLConnection()
        )
        {
            if(Jaris\System::isSSLSupported())
                Jaris\Uri::go("admin/user", array(), true);
        }

        if(Jaris\Authentication::login() || Jaris\Authentication::isUserLogged())
        {
            // Redirect to prevent resend data browser message
            // if user clicks back.
            if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]))
                Jaris\Uri::go("admin/user");

            $online = Jaris\Settings::get("site_status", "main");

            if(
                !$online &&
                !Jaris\Authentication::groupHasPermission(
                    "offline_login",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                Jaris\View::addMessage(
                    t("Only users with special permissions can login while the site is offline."),
                    "error"
                );

                Jaris\Authentication::logout();

                Jaris\Uri::go("admin/user");
            }

            //Goto return url if it is set
            if(isset($_SESSION["return_url"]))
            {
                $return = $_SESSION["return_url"];
                unset($_SESSION["return_url"]);

                Jaris\Uri::go($return);
            }

            //Display user page
            Jaris\Users::printPage();
        }
        else
        {
            //To remove any login session data
            Jaris\Authentication::logout();

            $parameters["action"] = Jaris\Uri::url("admin/user");
            $parameters["method"] = "post";

            $fields[] = array(
                "type" => "text",
                "name" => "username",
                "label" => t("Username or E-mail:"),
                "value" => !empty($_REQUEST["username"]) ?
                    $_REQUEST["username"] : "",
                "id" => "page-username"
            );

            $fields[] = array(
                "type" => "password",
                "name" => "password",
                "label" => t("Password:"),
                "id" => "page-password",
                "description" => t("the password is case sensitive")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "login",
                "value" => t("Login")
            );

            $fields[] = array(
                "type" => "reset",
                "name" => "reset",
                "value" => t("Reset")
            );

            $fieldset[] = array("fields" => $fields);

            print "<table id=\"my-account\">";
            print "<tbody>";
            print "<tr>";

            print "<td class=\"login\">";
            if(Jaris\Settings::get("new_registrations", "main"))
            {
                print "<h2>" . t("Existing User") . "</h2>";
            }
            print Jaris\Forms::generate($parameters, $fieldset);
            print "<div style=\"margin-top: 15px\">";
            print "<a href=\"" . Jaris\Uri::url("forgot-password") .
                "\">" .
                t("Forgot Password?") .
                "</a>"
            ;
            print "</div>";
            print "</td>";

            if(Jaris\Settings::get("new_registrations", "main"))
            {
                print "<td class=\"register\">";
                print "<h2>" . t("Create Account") . "</h2>";
                print "<a class=\"register-link\" href=\"" .
                    Jaris\Uri::url(
                        "register",
                        array("return" => $_REQUEST["return"])
                    ) . "\">" .
                    t("Register") .
                    "</a>"
                ;
                print Jaris\System::evalPHP(
                    Jaris\Settings::get("registration_benefits", "main")
                );
                print "</td>";
            }

            print "</tr>";
            print "</tbody>";
            print "</table>";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
