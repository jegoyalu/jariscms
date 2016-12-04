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

$module["name"] = t("Calendar");
$module["description"] = t("Adds the ability to create calendars for publishing any kind of event or activity.");
$module["version"] = "1.6";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

=Change Log=

Version 1.6 - Date 02/11/2016

    * Fixed issue of gmap3 not loading when on https connection.

Version 1.5 - Date 30/10/2016

    * Improvements to calendar for responsive sites.
    * Improvements to calendar event template.

Version 1.4 - Date 24/10/2016

    * Added registration url.

Version 1.3 - Date 22/09/2016

    * Make traditional calendar responsive by switching to consecutive
      calendar on lower resolutions.

Version 1.2 - Date 22/09/2016

    * Fixed bug when saving uploaded attachments to a newly created event.

Version 1.1.1 - Date 05/26/2016

    * Included global refactorization.

Version 1.1 - Date 29/04/2016

    * Updated events block to display events from current date and forward.

Version 1.0 - Date 27/07/2015

    * Initial version

**/