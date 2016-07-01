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
        <?php print t("Mobile Themes") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $is_override_on = Jaris\Settings::get("override", "main");

        if(!$is_override_on)
        {
            Jaris\View::addMessage(
                t("In order to change the default theme you need to enable Override settings"),
                "error"
            );
        }

        if(isset($_REQUEST["btnSave"]))
        {
            Jaris\Settings::save(
                "mobile_theme",
                $_REQUEST["theme"] . "/mobile",
                "mobile_detect"
            );

            Jaris\View::addMessage(t("Changes successfully saved."));

            Jaris\Uri::go("admin/themes/mobile");
        }
    ?>

    <form class="themes" method="post"
          action="<?php print Jaris\Uri::url(Jaris\Modules::getPageUri("admin/themes/mobile", "mobile_detect")); ?>"
    >

    <?php
        print "<table class=\"themes-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Preview") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Default") . "</td>\n";

        print "</tr></thead>\n";

        $current_theme = Jaris\Settings::get("mobile_theme", "mobile_detect");

        $mobile_themes = array();
        $themes = Jaris\Themes::getList();

        foreach($themes as $theme_path => $theme_info)
        {
            if(
                file_exists(Jaris\Themes::directory($theme_path) . "mobile/info.php")
            )
            {
                $mobile_themes[$theme_path] = $theme_info;
            }
        }

        if(count($mobile_themes) <= 0)
        {
            Jaris\View::addMessage(t("None of the current themes has mobile support."));
        }

        //Used to print the theme preview
        $base_url = Jaris\Site::$base_url;

        foreach($mobile_themes as $theme_path => $theme_info)
        {
            $alt = t("Preview not available");
            $title = t("View theme info.");
            $more_url = Jaris\Uri::url("admin/themes/view", array("path" => $theme_path));
            $thumbnail = $base_url . "/" . Jaris\Themes::directory($theme_path) . "preview.png";
            $selected = $current_theme == $theme_path . "/mobile" ? "checked=\"checked\"" : "";

            print "<tr>\n";
            if($theme_info != null)
            {
                print "<td><a title=\"$title\" href=\"$more_url\"><img alt=\"$alt\" src=\"$thumbnail\" /></a></td>\n";
                print "<td>" . t($theme_info['name']) . "</td>\n";
                print "<td><input $selected type=\"radio\" name=\"theme\" value=\"$theme_path\" /></td>\n";
            }
            else
            {
                print "<td><img alt=\"$alt\" src=\"$thumbnail\" /></td>\n";
                print "<td>$theme_path</td>\n" .
                    print "<td><input $selected type=\"radio\" name=\"theme\" value=\"$theme_path\" /></td>\n";
            }
            print "</tr>\n";
        }

        print "</table>"
    ?>

        <div>
            <br />
            <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
            &nbsp;
            <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
    </form>
    field;

    field: is_system
        1
    field;
row;
