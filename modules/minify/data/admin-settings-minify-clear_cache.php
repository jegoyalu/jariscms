<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the clear image cache page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Clear Minify Cache") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\FileSystem::recursiveRemoveDir(Jaris\Files::getDir("minify"), true))
            {
                Jaris\View::addMessage(t("Minify cache cleared successfully."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/settings");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings");
        }
    ?>

    <form class="clear-minify-cache" method="post"
          action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/settings/minify/clear-cache", "minify")) ?>"
    >
        <div>
            <?php print t("Are you sure you want to clear the minify cache?") ?>
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
