<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the themes management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Themes") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        Jaris\View::addTab(t("Upload"), "admin/themes/upload");

        if (isset($_REQUEST["btnSave"])) {
            if (is_array($_REQUEST["themes_enabled"])) {
                if (
                    !in_array(
                        $_REQUEST["theme"],
                        $_REQUEST["themes_enabled"]
                    )
                ) {
                    $_REQUEST["themes_enabled"][] = $_REQUEST["theme"];
                }
            }

            Jaris\Settings::save("theme", $_REQUEST["theme"], "main");

            Jaris\Settings::save(
                "themes_enabled",
                serialize($_REQUEST["themes_enabled"]),
                "main"
            );

            Jaris\View::addMessage(t("Changes successfully saved."));

            Jaris\Uri::go("admin/themes");
        }
    ?>

    <form 
        class="themes" 
        action="<?php print Jaris\Uri::url("admin/themes"); ?>" 
        method="post"
    >

    <?php
        print "<table "
            . "class=\"themes-list navigation-list navigation-list-hover\""
            . ">\n"
        ;

        print "<thead><tr>\n";

        print "<td>" . t("Preview") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Enabled") . "</td>\n";
        print "<td>" . t("Default") . "</td>\n";

        print "</tr></thead>\n";

        print "<tbody>";

        $themes = Jaris\Themes::getList();

        $default_theme = Jaris\Settings::get("theme", "main");
        $themes_enabled = unserialize(
            Jaris\Settings::get("themes_enabled", "main")
        );

        //Used to print the theme preview
        $base_url = Jaris\Site::$base_url;

        foreach ($themes as $theme_path => $theme_info) {
            $alt = t("Preview not available");
            $title = t("View theme info.");
            $more_url = Jaris\Uri::url(
                "admin/themes/view",
                ["path" => $theme_path]
            );
            $thumbnail = $base_url
                . "/"
                . Jaris\Themes::directory($theme_path)
                . "preview.png"
            ;
            $selected = $default_theme == $theme_path ?
                "checked=\"checked\"" : ""
            ;
            $checked = "";

            if (is_array($themes_enabled)) {
                if (in_array($theme_path, $themes_enabled)) {
                    $checked = "checked=\"checked\"";
                }
            }

            print "<tr>\n";

            if ($theme_info != null) {
                print "<td>"
                    . "<a title=\"$title\" href=\"$more_url\">"
                    . "<img alt=\"$alt\" src=\"$thumbnail\" />"
                    . "</a>"
                    . "</td>\n"
                ;

                print "<td>" . t($theme_info['name']) . "</td>\n";
            } else {
                print "<td>"
                    . "<img alt=\"$alt\" src=\"$thumbnail\" />"
                    . "</td>\n"
                ;

                print "<td>$theme_path</td>\n";
            }

            print "<td>"
                . "<input $checked "
                . "type=\"checkbox\" "
                . "name=\"themes_enabled[]\" "
                . "value=\"$theme_path\" "
                . "/>"
                . "</td>\n"
            ;

            print "<td>"
                . "<input $selected "
                . "type=\"radio\" "
                . "name=\"theme\" "
                . "value=\"$theme_path\" "
                . "/>"
                . "</td>\n"
            ;

            print "</tr>\n";
        }

        print "</tbody>";

        print "</table>"
    ?>

        <div>
            <br />
            <input class="form-submit" type="submit"
                name="btnSave" value="<?php print t("Save") ?>"
            />
            &nbsp;
            <input class="form-submit" type="submit"
                name="btnCancel" value="<?php print t("Cancel") ?>"
            />
        </div>
    </form>
    field;

    field: is_system
        1
    field;
row;
