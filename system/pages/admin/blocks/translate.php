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
        <?php print t("Translate Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["translate_languages"]);

        if (!isset($_REQUEST["id"]) || !isset($_REQUEST["position"])) {
            Jaris\Uri::go("admin/blocks");
        }

        $arguments = [
            "id" => $_REQUEST["id"],
            "position" => $_REQUEST["position"]
        ];

        //Tabs
        Jaris\View::addTab(t("Delete"), "admin/blocks/delete", $arguments);
        Jaris\View::addTab(t("Blocks"), "admin/blocks");
        Jaris\View::addTab(t("Translate"), "admin/blocks/translate", $arguments);

        $languages = Jaris\Language::getInstalled();

        print "<table class=\"languages-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Code") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        foreach ($languages as $code => $name) {
            if ($code != "en") {
                print "<tr>\n";

                print "<td>" . $code . "</td>\n";
                print "<td>" . $name . "</td>\n";

                $edit_url = Jaris\Uri::url(
                    "admin/languages/translate",
                    [
                        "code" => $code,
                        "type" => "block",
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    ]
                );

                $edit_text = t("Translate");

                print "<td>
                    <a href=\"$edit_url\">$edit_text</a>&nbsp;
                   </td>\n";

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
