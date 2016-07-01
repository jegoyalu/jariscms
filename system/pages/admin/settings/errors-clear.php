<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the errors clear page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Clear Errors Database") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            unlink(Jaris\Site::dataDir() . "sqlite/errors_log");

            $db = Jaris\Sql::open("errors_log");

            Jaris\Sql::query(
                "create table errors_log ("
                . "error_date text, "
                . "error_type text, "
                . "error_message text, "
                . "error_file text, "
                . "error_line text, "
                . "error_page text"
                . ")",
                $db
            );

            Jaris\Sql::query(
                "create index errors_log_index on errors_log ("
                . "error_date desc, "
                . "error_type desc"
                . ")",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(t("Successfully removed all logged errors."));

            Jaris\Uri::go("admin/settings/errors");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings/errors");
        }
    ?>

    <form class="errors-clear" method="post"
          action="<?php Jaris\Uri::url("admin/settings/errors-clear") ?>"
    >
        <div>
            <?php print t("Are you sure you want to remove all error messages?") ?>
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
