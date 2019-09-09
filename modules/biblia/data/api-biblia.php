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
        Biblia Api
    field;

    field: content
    <?php
        if(!defined("JSON_PRETTY_PRINT"))
        {
            define("JSON_PRETTY_PRINT", 128);
        }

        // Return versiculos
        if(isset($_REQUEST["libro"]) && isset($_REQUEST["capitulo"]))
        {
            print json_encode(
                range(
                    1,
                    biblia_get_versiculos(
                        $_REQUEST["capitulo"],
                        $_REQUEST["libro"],
                        $_REQUEST["biblia"]
                    )
                ),
                JSON_PRETTY_PRINT
            );

            return;
        }
        // Get states/provinces
        elseif(isset($_REQUEST["libro"]))
        {
            print json_encode(
                range(
                    1,
                    biblia_get_capitulos(
                        $_REQUEST["libro"],
                        $_REQUEST["biblia"]
                    )
                ),
                JSON_PRETTY_PRINT
            );

            return;
        }

        print json_encode(
            array(
                "error" => "Nothing found.",
                JSON_PRETTY_PRINT
            )
        );

        return;
    ?>
    field;

    field: rendering_mode
        api
    field;

    field: is_system
        1
    field;
row;
