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
        Jaris\Site::setHTTPStatus(401);
        print t("Access Denied")
    ?>
    field;

    field: content
    <?php
        print t("You dont have sufficient permissions to access the page.")
    ?>
    field;

    field: is_system
        1
    field;
row;
