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
        <?php print t("Account E-mail Activation"); ?>
    field;

    field: content
    <?php
        $message = "";
        $reactivation_link = "";

        if (!isset($_REQUEST["u"])) {
            $message = t("Error: Invalid account.");
        }

        $user_data = Jaris\Users::get($_REQUEST["u"]);

        if (!$user_data) {
            $message = t("Error: Invalid account.");
        }

        if (
            $message == ""
        ) {
            if ($user_data["email_activated"] == "0") {
                if (Jaris\Mail::sendEmailActivation($_REQUEST["u"])) {
                    $message = t("The activation e-mail was successfully sent.");

                    t("Account re-activation e-mail sent for '{username}'.");

                    Jaris\Logger::info(
                        "Account re-activation e-mail sent for '{username}'.",
                        [
                            "username" => $_REQUEST["u"]
                        ]
                    );
                } else {
                    $message = t("Error: An error occured while trying to send the activation email, please try again later.");
                }
            } else {
                Jaris\View::addMessage(
                    t("Account already activated. Please login.")
                );

                Jaris\Uri::go(
                    "admin/user",
                    ["username" => $user_data["email"]]
                );
            }
        }

        print '<h3>'.$message.'</h3>';
    ?>
    field;

    field: is_system
        1
    field;
row;
