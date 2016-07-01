<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the sqlite search reindex page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Reindex SQLite Search Engine Database") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            ini_set('max_execution_time', '0');

            if(Jaris\Search::reindex())
            {
                Jaris\View::addMessage(
                    t("Indexation of SQLite search database completed.")
                );
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

    <form class="reindex-search-engine" method="post"
          action="<?php Jaris\Uri::url("admin/settings/reindex-search") ?>"
    >
        <div>
            <?php print t("The proces of recreating sqlite search engine index could take some time. Are you sure you want do this?") ?>
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
