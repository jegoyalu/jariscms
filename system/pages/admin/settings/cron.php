<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the cron jobs execution and info page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Cron Jobs") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(isset($_REQUEST["btnYes"]))
        {
            Jaris\Uri::go("cron.php", array("return" => "admin/settings/advanced"));
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/settings/advanced");
        }
    ?>

    <div>
    <?php
        print t("To run cron jobs automatically you need to set a command to execute on your operating system crontab as the following examples:");
    ?>
        <br /><br />
        /usr/bin/php-cgi /home/username/public_html/cron.php "HTTP_HOST=www.mysite.com" <br />
        /usr/bin/php-cgi /home/username/public_html/cron.php 'HTTP_HOST=www.mysite.com' <br />
        /usr/bin/php /home/username/public_html/cron.php www.mysite.com
    </div>

    <hr />

    <h2><?php print t("Last time cron jobs executed:") ?></h2>

    <strong>
    <?php
        $time = Jaris\Settings::get("last_cron_jobs_run", "main");
        $date = date("m/d/Y g:i:s a", intval($time));

        if(!$time)
        {
            print t("never");
        }
        else
        {
            print $date;
        }
    ?>
    </strong>

    <hr />

    <h2><?php print t("Run cron jobs?") ?></h2>

    <form class="clear-image_cache" method="post"
          action="<?php Jaris\Uri::url("admin/settings/cron") ?>"
    >
        <div>
            <?php print t("The process of running the cron jobs could take a long time. Execute jobs now?") ?>
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
