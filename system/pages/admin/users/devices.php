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
            print t("Edit User Devices");
        }
        else
        {
            print t("My Devices");
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

        $user_data = Jaris\Users::get($username);

        if(
            isset($_REQUEST["remove"])
        )
        {
            $token = $_REQUEST["remove"];

            if(isset($user_data["devices"][$token]))
            {
                unset($user_data["devices"][$token]);

                if(Jaris\Users::edit($username, $user_data["group"], $user_data))
                {
                    Jaris\View::addMessage(
                        t("Device removed successfully.")
                    );
                }
                else
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );
                }
            }
        }

        if(
            Jaris\Authentication::isAdminLogged()
            &&
            $username != Jaris\Authentication::currentUser()
        )
        {
            Jaris\View::addTab(
                t("Edit User"),
                "admin/users/edit",
                array(
                    "username" => $username
                )
            );
        }
        else
        {
            Jaris\View::addTab(
                t("My Account"),
                "admin/user"
            );
        }


        if(is_array($user_data["devices"]) && count($user_data["devices"]) > 0)
        {

            print "<table class=\"navigation-list navigation-list-hover\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("OS") . "</td>\n";
            print "<td>" . t("Browser") . "</td>\n";
            print "<td>" . t("IP") . "</td>\n";
            print "<td></td>\n";

            print "</tr></thead>\n";

            foreach($user_data["devices"] as $device_token => $device_data)
            {
                print "<tr>\n";

                print "<td>" . $device_data["device"]["platform"] . "</td>\n";
                print "<td>"
                    . $device_data["device"]["browser"]
                    . " v" . $device_data["device"]["version"]
                    . "</td>\n"
                ;
                print "<td>" . $device_data["last_ip"] . "</td>\n";

                $delete_url = Jaris\Uri::url(
                    Jaris\Uri::get(),
                    array(
                        "username" => $username,
                        "remove" => $device_token
                    )
                );

                $delete_text = t("Revoke Access");

                print "<td>";
                print "<a href=\"$delete_url\">$delete_text</a>";
                print "</td>\n";

                print "</tr>\n";
            }

            print "</table>\n";
        }
        else
        {
            print "<h3>" . t("No devices registered.") . "</h3>";
        }
    ?>
    field;


    field: is_system
        1
    field;
row;
