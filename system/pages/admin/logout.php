<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the logout page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("User Logout") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::logout();
    ?>

    <?php print t("Successfully logged out!") ?>
    <a href="<?php print Jaris\Uri::url(""); ?>">
        <?php print t("Click Here") ?>
    </a>
    <?php print t("to go back to home page.") ?>
    field;

    field: is_system
        1
    field;
row;
