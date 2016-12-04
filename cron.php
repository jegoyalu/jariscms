<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Script to run all cron jobs.
 *
 * @example To run cron job from system an example command is the following:
 * /usr/bin/php-cgi /home/username/public_html/cron.php "HTTP_HOST=www.mysite.com" or
 * /usr/bin/php-cgi /home/username/public_html/cron.php 'HTTP_HOST=www.mysite.com' or
 * /usr/bin/php /home/username/public_html/cron.php www.mysite.com
 */

//Disables execution time and enables unlimited execution time for cron jobs
ini_set('max_execution_time', '0');

//If running in cli mode
if(php_sapi_name() == "cli")
{
    chdir(__DIR__);

    if(isset($_SERVER["argv"][1]))
    {
        $_REQUEST["HTTP_HOST"] = $_SERVER["argv"][1];
    }
    else
    {
        $_REQUEST["HTTP_HOST"] = "localhost";
    }
}

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//Shorthand functions commonly used on legacy templates
require 'src/Aliases.php';

//Include backward compatible functions if include dir exists
if(file_exists("include/forms.php"))
{
    require 'src/DeprecatedFunctions.php';
}

//Initialize settings.
Jaris\Site::init();

//Starts the main session for the user
if(isset($_SERVER["SERVER_NAME"]))
{
    Jaris\Session::startIfUserLogged();
}

//Initialize error handler
Jaris\System::initiateErrorCatchSystem();

//Check if cms is run for the first time and run the installer
Jaris\System::checkIfNotInstalled();

//Check if site status is online to continue
Jaris\Site::checkIfOffline();

//Check if cron is already running and if running exit cron script
if(!file_exists(Jaris\Site::dataDir() . "cron_running.lock"))
{
    file_put_contents(Jaris\Site::dataDir() . "cron_running.lock", "");
}
else
{
    exit;
}

//Load installed modules
Jaris\Site::loadModules();

//Calls the cron job function of each module that requires it
Jaris\Modules::hook("hook_cronjob");

//Save execution time
Jaris\Settings::save("last_cron_jobs_run", (string)time(), "main");

//Remove cron lock file
unlink(Jaris\Site::dataDir() . "cron_running.lock");

//If script was executed from control panel return to it
if(isset($_REQUEST["return"]))
{
    Jaris\View::addMessage(t("All jobs successfully executed."));
    Jaris\Uri::go($_REQUEST["return"]);
}
else if(!Jaris\Authentication::isAdminLogged() && isset($_SERVER["SERVER_NAME"]))
{
    Jaris\Uri::go("");
}