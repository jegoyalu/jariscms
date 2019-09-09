<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * The main execution entry point of Jaris CMS.
 */

//Check PHP version complies with the minimum required.
if (substr(PHP_VERSION, 0, 1) < 7 && substr(PHP_VERSION, 2, 1) < 1) {
    exit("Error: please use PHP 7.1.0 or later.");
}

//Time when script started executing useful to measure execution time.
$time_start = microtime(true);

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//Shorthand functions commonly used on legacy templates
require 'src/Aliases.php';

//Include backward compatible functions if include dir exists
if (file_exists("include/forms.php")) {
    require 'src/DeprecatedFunctions.php';
}

//Initialize settings.
Jaris\Site::init();

//Try to do a fast cache page retreival
Jaris\System::fastCacheIfPossible(Jaris\Uri::get());

//Load installed modules
Jaris\Site::loadModules();

//Continue normal processing and render requested page
Jaris\Site::bootStrap();

//Display amount of time that took to render the page if enabled.
Jaris\Site::printStats();
