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

$module["name"] = "Whizzywig";
$module["description"] = "Integrates the whizzywig editor to jaris cms";
$module["version"] = "4.5.5";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

Version 4.5.5- Date 31/07/2018

    * Fixed issues reported by phan.
    * Adjustments for scalar type hints on core.

Version 4.5.4 - Date 11/12/2017

    * Use empty array instead of null for arguments of settings tab.

Version 4.5.3 - Date 13/06/2016

    * Modified to work with new session changes.

Version 4.5.2 - Date 05/26/2016

    * Included global refactorization.

Version 4.5.1 - Date 21/06/2015

    * Auto generate a field id if not set by using its name.

Version 4.5 - Date 15/08/2014

    * Make use of jariscms rendering mode for scripts and styles.

Version 4.4 - Date 2/08/2011

    * Updated to whyzzywig v63

Version 4.3 - Date 22/08/2010

    * Image insertion now uses image name instead of image id to stop problems
    * of browser not refreshing cache when using same id like 0

*/
