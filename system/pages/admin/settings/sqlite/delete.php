<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the delete sqlite database page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Sqlite Database") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(!isset($_REQUEST["name"]))
        {
            Jaris\Uri::go("admin/settings/sqlite");
        }

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Sql::dbExists($_REQUEST["name"]))
            {
                unlink(Jaris\Site::dataDir() . "sqlite/" . $_REQUEST["name"]);
            }

            Jaris\View::addMessage(t("Database successfully deleted."));

            Jaris\Uri::go("admin/settings/sqlite");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings/sqlite");
        }
    ?>

    <form class="clear-image_cache" method="post"
          action="<?php Jaris\Uri::url("admin/settings/sqlite/delete") ?>"
    >
        <input type="hidden" name="name" value="<?php print $_REQUEST["name"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the database?") ?>
        </div>
        <div>
            <b><?php print t("Database:") ?></b>
            <?php print $_REQUEST["name"] ?>
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
