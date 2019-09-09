<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the sqlite download backup script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Sqlite Backup") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));
    ?>
    <?php
        function adminer_object()
        {
            class AdminerJaris extends Adminer {
                function name() {
                    // custom name in title and heading
                    return Jaris\Settings::get("title", "main");
                }

                function login($login, $password) {
                    // validate user submitted credentials
                    return true;
                }

                function databases($flush=false) {
                    $databases = Jaris\Sql::listDB();
                    sort($databases);

                    foreach($databases as &$db)
                    {
                        $db = Jaris\Site::dataDir() . "sqlite/" . $db;
                    }

                    return $databases;
                }
            }

            return new AdminerJaris;
        }

        // Remember to not edit Adminer.php with codelite, it damages it.
        include("src/Adminer.php");
    ?>
    field;

    field: rendering_mode
        plain_html
    field;

    field: is_system
        1
    field;
row;