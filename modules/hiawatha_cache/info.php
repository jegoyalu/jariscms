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

$module["name"] = t("Hiawatha Cache");
$module["description"] = t("Stores a cgi call output to the internal cache system of hiawatha when a url was successfully cached by JarisCMS built-in cache system.");
$module["version"] = "1.0.2";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";


/*

Version 1.0.2 - Date 08/10/2016

    * Added a 200 bytes padding to the len of content because (maybe wrong strlen)
      or hiawatha causing a strip of the cached html and outputting
      incomplete html.

Version 1.0.1 - Date 05/26/2016

    * Included global refactorization.

Version 1.0 - Date 17/02/2014

    * Initial version.

*/