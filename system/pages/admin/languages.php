<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the languages management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Languages") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_languages"));

        Jaris\View::addTab(t("Add Language"), "admin/languages/add");

        $languages = Jaris\Language::getInstalled();

        print "<table class=\"languages-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Code") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        $title = t("View language info.");

        foreach($languages as $code => $name)
        {
            if($code != "en")
            {
                print "<tr>\n";

                print "<td>";
                print "<a title=\"$title\" href=\"" .
                    Jaris\Uri::url("admin/languages/info", array("code" => $code)) .
                    "\">" .
                    $code .
                    "</a>"
                ;
                print "</td>\n";

                print "<td>" . $name . "</td>\n";

                $edit_url = Jaris\Uri::url(
                    "admin/languages/edit",
                    array("code" => $code)
                );

                $edit_text = t("Edit strings");

                print "<td>";
                print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                print "</td>\n";

                print "</tr>\n";
            }
        }

        print "</table>\n";
    ?>
    field;

    field: is_system
        1
    field;
row;
