<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the deault home page.
 */
exit;
?>

row: 0
    field: title
        <?php print t("Welcome to your new Jaris website!") ?>
    field;

    field: content
    <?php
        if (!Jaris\Authentication::isAdminLogged()) {
            print t("Enjoy your new webiste, to start working on it login on the left block with your administration account");
        } else {
            print t("Now that you are logged in you can start by using the administration navigation menu to modify your web page as you like.");
            print " ";

            $link = '<a href="'.Jaris\Uri::url("admin/settings").'">'
                . t("site settings")
                . '</a>'
            ;

            print sprintf(
                t("You can set the default home page by going to the %s."),
                $link
            );
        }
        ?>
    field;

    field: is_system
        1
    field;

row;