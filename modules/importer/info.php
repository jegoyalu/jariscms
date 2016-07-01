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

$module["name"] = t("Importer");
$module["description"] = t("Module to add content into the system by reading a csv file.");
$module["version"] = "2.1.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

Version 2.1.1 - Date 05/26/2016

    * Included global refactorization.

Version 2.1 - Date 04/10/2014

    * Fixed exporting issues because a content type custom fields where
      not being read.

Version 2.0 - Date 30/06/2014

    * Added ability to also export content.

Version 1.1 - Date 25/02/2014

    * Added ability to convert new lines to <br> on the content field.

Version 1.0 - Date 27/01/2014

    * Initial version.

*/