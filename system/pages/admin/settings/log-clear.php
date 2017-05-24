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
        <?php print t("Clear Logs Database") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            unlink(Jaris\Site::dataDir() . "sqlite/log");

            Jaris\View::addMessage(t("Successfully removed all logged messages."));

            t("Cleared system log.");

            Jaris\Logger::info(
                "Cleared system log."
            );

            Jaris\Uri::go("admin/settings/log");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings/log");
        }
    ?>

    <form class="log-clear" method="post"
          action="<?php Jaris\Uri::url("admin/settings/log/clear") ?>"
    >
        <div>
            <?php print t("Are you sure you want to remove all messages?") ?>
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
