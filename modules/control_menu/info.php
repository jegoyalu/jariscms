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

$module["name"] = t("Control Menu");
$module["description"] = t("To add a menu for users and admins at the bottom of the page that facilitates access.");
$module["version"] = "1.4.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

Version 1.4.1 - Date 004/02/2019

    * Menu always stay on bottom even if scrolling on mobile browser that
      hides/shows the address bar.

Version 1.4.0 - Date 28/01/2019

    * Use empty array instead of null for arguments of settings tab.

Version 1.3.2 - Date 11/12/2017

    * Use empty array instead of null for arguments of settings tab.

Version 1.3.1 - Date 05/26/2016

    * Included global refactorization.

Version 1.3 - Date 08/15/2014

    * Make use of jariscms rendering mode for scripts and styles.
    * Use "my account" label instead of username on the my account button.

Version 1.2 - Date 29/12/2012

    * Added colors customization.
    * Added help link customization.

Version 1.1 - Date 29/12/2012

    * Fixes for touch devices.

Version 1.0

    * Initial version

*/