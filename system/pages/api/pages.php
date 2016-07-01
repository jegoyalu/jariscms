<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Pages Api
    field;

    field: content
    <?php
        if(empty($_REQUEST["uri"]))
        {
            exit;
        }

        if(!empty($_REQUEST["action"]) && $_REQUEST["action"] == "count_view")
        {
            Jaris\Pages::countView($_REQUEST["uri"]);
        }
    ?>
    field;

    field: rendering_mode
        api
    field;

    field: is_system
        1
    field;
row;
