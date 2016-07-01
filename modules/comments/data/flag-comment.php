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
        Flag comment
    field;

    field: content
    <?php
        if(isset($_REQUEST["s"]) && Jaris\Authentication::currentUser() == "Guest")
        {
            session_destroy();
            session_id($_REQUEST["s"]);
            session_start();
        }

        Jaris\Authentication::protectedPage(array("flag_comments"));

        if(
            isset($_REQUEST["id"]) &&
            isset($_REQUEST["page"]) &&
            isset($_REQUEST["type"]) &&
            isset($_REQUEST["user"])
        )
        {
            $type_settings = comments_get_settings($_REQUEST["type"]);

            if($type_settings["enabled"])
            {
                comments_flag(
                    $_REQUEST["id"],
                    $_REQUEST["page"],
                    $_REQUEST["user"]
                );

                print $_REQUEST["id"];
            }
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
