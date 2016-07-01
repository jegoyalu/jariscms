<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the global delete theme page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Theme") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("delete_theme"));

        if(empty($_REQUEST["path"]))
        {
            Jaris\Uri::go("admin/themes");
        }

        if(
            Jaris\Themes::directory($_REQUEST["path"]) ==
            "themes/" . $_REQUEST["path"] . "/"
        )
        {
            Jaris\View::addMessage(
                "System themes can not be deleted.",
                "error"
            );

            Jaris\Uri::go("admin/themes");
        }

        if(isset($_REQUEST["btnYes"]))
        {
            if(
                Jaris\FileSystem::recursiveRemoveDir(
                    Jaris\Themes::directory($_REQUEST["path"])
                )
            )
            {
                Jaris\View::addMessage(t("Theme successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/themes");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/themes");
        }

        $theme_info = Jaris\Themes::get($_REQUEST["path"]);
    ?>

    <form class="themes-delete" method="post" action="<?php Jaris\Uri::url("admin/themes/delete") ?>">
        <input type="hidden" name="path" value="<?php print $_REQUEST["path"] ?>" />

        <div>
            <?php print t("Are you sure you want to delete the theme?") ?>
            <div>
                <b>
                    <?php print t("Theme: ") ?>
                    <?php print $theme_info["name"] ?>
                </b>
            </div>
        </div>

        <input class="form-submit" type="submit"
               name="btnYes" value="<?php print t("Yes") ?>"
        />
        <input class="form-submit" type="submit"
               name="btnNo" value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
