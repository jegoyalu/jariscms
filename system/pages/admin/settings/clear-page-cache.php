<?php
use Jaris;
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the clear page cache page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Clear Page Cache") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            if(
                Jaris\FileSystem::recursiveRemoveDir(
                    Jaris\Site::dataDir() . "cache", true
                )
            )
            {
                Jaris\Modules::hook(Jaris\System::SIGNAL_CLEAR_PAGE_CACHE);

                Jaris\View::addMessage(t("Page cache cleared successfully."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/settings/advanced");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings/advanced");
        }
    ?>

    <form class="clear-page_cache" method="post"
          action="<?php Jaris\Uri::url("admin/settings/clear-page-cache") ?>"
    >
        <div>
            <?php print t("Are you sure you want to clear the page cache?") ?>
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
