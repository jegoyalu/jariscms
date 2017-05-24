<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * The page that serve for displaying user profiles
 */
exit;
?>

row: 0
    field: title
        <?php print t("Account Activation"); ?>
    field;

    field: content
    <style>
    .activation-link
    {
        display: inline-block;
        padding: 10px;
        background-color: #e3e3e3;
        font-weight: bold;
    }
    </style>
    <?php
        $message = "";
        $reactivation_link = "";

        if(!isset($_REQUEST["u"]) || !isset($_REQUEST["c"]))
        {
            $message = t("Error: Invalid activation data.");
        }

        $user_data = Jaris\Users::get($_REQUEST["u"]);

        if(!$user_data || $user_data["activation_code"] != $_REQUEST["c"])
        {
            $message = t("Error: Invalid activation data.");

            if($user_data)
            {
                $reactivation_link = Jaris\Uri::url(
                    "account/reactivate",
                    array("u" => $_REQUEST["u"])
                );
            }
        }

        if(
            $message == "" &&
            isset($_REQUEST["a"])
        )
        {
            if($user_data["email_activated"] == "0")
            {
                $user_data["email_activated"] = "1";

                Jaris\Users::edit(
                    $_REQUEST["u"],
                    $user_data["group"],
                    $user_data
                );

                Jaris\Mail::sendWelcomeMessage($_REQUEST["u"]);

                Jaris\View::addMessage(
                    t("The account was successfully activated. Please login.")
                );

                t("Account '{username}' activated.");

                Jaris\Logger::info(
                    "Account '{username}' activated.",
                    array(
                        "username" => $_REQUEST["u"]
                    )
                );

                Jaris\Uri::go(
                    "admin/user",
                    array("username" => $user_data["email"])
                );
            }
            else
            {
                Jaris\View::addMessage(
                    t("Account already activated. Please login.")
                );

                Jaris\Uri::go(
                    "admin/user",
                    array("username" => $user_data["email"])
                );
            }
        }

        $activation_link = Jaris\Uri::url(
            "account/activate",
            array(
                "u" => $_REQUEST["u"],
                "c" => $_REQUEST["c"],
                "a" => 1
            )
        );

        if($message == "")
        {
            print '<a class="activation-link" href="'.$activation_link.'">'
                . t("Click to Activate")
                . '</a>'
            ;
        }
        else
        {
            print '<h3>'.$message.'</h3>';

            if($reactivation_link)
            {
                print '<a class="activation-link" href="'.$reactivation_link.'">'
                    . t("Resend Account Activation E-mail")
                    . '</a>'
                ;
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
