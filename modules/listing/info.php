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

$module["name"] = t("Content Listing");
$module["description"] = t("To create pages that display a list of content by a given set of criteria.");
$module["version"] = "1.4.4";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 1.4.4 - Date 05/26/2016

    * Included global refactorization.

Version 1.4.3 - Date 31/01/2015

    * Make use of Jaris\Types::getImageUrl()

Version 1.4.2 - Date 21/05/2014

    * Implemented usernames content access.

Version 1.4.1 - Date 21/10/2013

    * Added option to filter partially by selected categories.
    * Limit amount of characters for meta title and description.

Version 1.4 - Date 21/10/2013

    * Update to use jaris_sqlite_turbo to prevent database lockups.

Version 1.3 - Date 15/10/2013

    * Fixed incomplete columns generation on grid mode.

Version 1.2 - Date 27/01/2013

    * Enabled support for positioning blocks per theme.

Version 1.1 - Date 27/09/2012

    * Added sorting of listing by most viewed current day, week, month and all time

Version 1.0.1 - Date 30/03/2012

    * Applied meta title change.

Version 1.0 - Date 26/11/2011

    * First version

**/