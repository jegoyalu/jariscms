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
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (isset($_REQUEST["name"])) {
            $file = Jaris\Site::dataDir() . "sqlite/" . $_REQUEST["name"];

            if (file_exists($file)) {
                t("Backup sql database '{database}'.");

                Jaris\Logger::info(
                    "Backup sql database '{database}'.",
                    [
                        "database" => $_REQUEST["name"]
                    ]
                );

                Jaris\FileSystem::printFile($file, $_REQUEST["name"], true, true);
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
