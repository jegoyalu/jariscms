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
        Navigations comment
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_comments"]);

        if (
            isset($_REQUEST["uri"]) &&
            isset($_REQUEST["page"]) &&
            isset($_REQUEST["type"])
        ) {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            $comment_settings = comments_get_settings($page_data["type"]);

            if ($comment_settings["enabled"]) {
                print comments_print(
                    $_REQUEST["uri"],
                    $page_data[0]["type"],
                    $_REQUEST["page"],
                    $comment_settings["ordering"],
                    $comment_settings["replies"]
                );
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
