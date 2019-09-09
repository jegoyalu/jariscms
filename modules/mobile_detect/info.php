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

$module["name"] = t("Mobile Detect");
$module["description"] = t("Provides functionality to adjust theme depending on device.");
$module["version"] = "1.0.4";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

Version 1.0.4 - Date 11/12/2017

    * Use empty array instead of null for arguments of settings tab.

Version 1.0.3 - Date 18/10/2016

    * Removed legacy detection of main override flag.

Version 1.0.2 - Date 13/06/2016

    * Modified to work with new session changes.

Version 1.0.1 - Date 05/26/2016

    * Included global refactorization.

*/