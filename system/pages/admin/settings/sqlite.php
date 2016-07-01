<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the sqlite backup center.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Sqlite Database Center") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addTab(
            t("Upload Database Backup"),
            "admin/settings/sqlite/upload"
        );

        $databases = Jaris\Sql::listDB();

        print "<table class=\"languages-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Database") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        sort($databases);

        foreach($databases as $name)
        {
            if($name == "readme.txt")
                continue;

            print "<tr>\n";

            print "<td>" . $name . "</td>\n";

            $backup_url = Jaris\Uri::url(
                "admin/settings/sqlite/backup",
                array("name" => $name)
            );

            $backup_text = t("Backup");

            $delete_url = Jaris\Uri::url(
                "admin/settings/sqlite/delete",
                array("name" => $name)
            );

            $delete_text = t("Delete");

            print "<td>
                <a href=\"$backup_url\">$backup_text</a>&nbsp;
                <a href=\"$delete_url\">$delete_text</a>
               </td>\n";

            print "</tr>\n";
        }

        print "</table>\n";
    ?>
    field;

    field: is_system
        1
    field;
row;
