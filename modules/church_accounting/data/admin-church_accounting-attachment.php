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
        <?php print t("Attachment not found") ?>
    field;

    field: content
        <?php
            Jaris\Authentication::protectedPage(array("edit_income_church_accounting"));

            $elements = explode("/", $_REQUEST["f"]);
            $file = Jaris\Site::dataDir() . "church_accounting/{$_REQUEST["f"]}";

            if(file_exists($file))
            {
                Jaris\FileSystem::printFile($file, $elements[2]);
            }
            else
            {
                Jaris\Site::setHTTPStatus(404);
                print t("The file does not exist.");
            }
        ?>
    field;

    field: is_system
        1
    field;
row;