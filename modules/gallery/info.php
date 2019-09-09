<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module info file
 *
 * You always need to create an info.php file for your modules.
 */

$module["name"] = "Image Gallery";
$module["description"] = "To create pages that display uploaded images as a gallery.";
$module["version"] = "1.9.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

$module["dependencies"][] = "jquery_lightbox";

/*

Version 1.9.1 - Date 31/07/2018

    * Fixed issues reported by phan.
    * Adjustments for scalar type hints on core.

Version 1.9 - Date 07/04/2018

    * Added sorting support.

Version 1.8.4 - Date 05/26/2016

    * Included global refactorization.

Version 1.8.3 - Date 21/05/2014

    * Implemented usernames content access.

Version 1.8.2 - Date 18/01/2014

    * Limit amount of characters for meta title and description.

Version 1.8.1 - Date 30/03/2012

    * Applied meta title change.

Version 1.8 - Date 23/11/2010

    * Added files tab to edit gallery page

Version 1.7 - Date 22/08/2010

    * Now using image name instead of id

*/