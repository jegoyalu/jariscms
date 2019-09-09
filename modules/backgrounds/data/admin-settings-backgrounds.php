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
        <?php print t("Backgrounds"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        Jaris\View::addTab(
            t("Add Background"),
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/add",
                "backgrounds"
            )
        );

        Jaris\View::addTab(
            t("Add Multi-background"),
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/multi/add",
                "backgrounds"
            )
        );

        $backgrounds_settings = Jaris\Settings::getAll("backgrounds");

        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        if (is_array($backgrounds) && count($backgrounds) > 0) {
            print "<table class=\"navigation-list\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Description") . "</td>";
            print "<td>" . t("Actions") . "</td>";
            print "</tr>";
            print "</thead>";

            foreach ($backgrounds as $background_id => $background) {
                $edit_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/backgrounds/edit",
                        "backgrounds"
                    ),
                    ["id" => $background_id]
                );

                $delete_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/backgrounds/delete",
                        "backgrounds"
                    ),
                    ["id" => $background_id]
                );

                if ($background["multi"]) {
                    $edit_url = Jaris\Uri::url(
                        Jaris\Modules::getPageUri(
                            "admin/settings/backgrounds/multi/edit",
                            "backgrounds"
                        ),
                        ["id" => $background_id]
                    );
                }

                print "<tr>";

                print "<td>{$background["description"]}</td>";

                print "<td>";

                print "<a href=\"" . $edit_url . "\">" . t("Edit") . "</a>&nbsp;";

                print "<a href=\"" . $delete_url . "\">" . t("Delete") . "</a>";

                print "</td>";

                print "</tr>";
            }

            print "</table>";
        } else {
            Jaris\View::addMessage(
                t("No background images available. Click on one of the options add one.")
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
