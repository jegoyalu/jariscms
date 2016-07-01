<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module info file
 *
 * @note You always need to create an info.php file for your modules.
 */

$module["name"] = t("Internet Explorer Update Needed Detector");
$module["description"] = t("Checks if the user browser is internet explorer version 8 or older and prompt the user to update.");
$module["version"] = "1.4.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 1.4.1 - Date 05/26/2016

    * Included global refactorization.

Version 1.4 - Date 29/04/2016

    * Upped the required version of Internet Explorer to 11.

Version 1.3 - Date 15/08/2014

    * Make use of jariscms rendering mode for scripts and styles.

Version 1.2 - Date 13/3/2013

    * Deprecated internet explorer 8

Version 1.1 - Date 2/3/2011

    * Fixed browser detection since it was also detecting ie 8 as old

**/