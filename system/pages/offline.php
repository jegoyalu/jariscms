<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * The page that serve for restricted areas.
 */
exit;
?>

row: 0
    field: title
    <?php
        if($title = Jaris\Settings::get("site_status_title", "main"))
        {
            print t($title);
        }
        else
        {
            print t("Under mantainance");
        }
    ?>
    field;

    field: content
    <?php
        if($description = Jaris\Settings::get("site_status_description", "main"))
        {
            print t($description);
        }
        else
        {
            print t("The site is down for mantainance, sorry for any inconvenience it may cause you. Try again later.");
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
