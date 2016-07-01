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
        Delete comment
    field;

    field: content
    <?php
        if(isset($_REQUEST["s"]) && Jaris\Authentication::currentUser() == "Guest")
        {
            session_destroy();
            session_id($_REQUEST["s"]);
            session_start();
        }

        Jaris\Authentication::protectedPage(array("delete_comments"));

        if(!Jaris\Authentication::isAdminLogged())
        {
            if(!comments_is_from_current_user($_REQUEST["id"], $_REQUEST["page"]))
            {
                Jaris\Authentication::protectedPage();
            }
        }

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
                comments_delete(
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
