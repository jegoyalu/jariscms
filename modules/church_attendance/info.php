<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module info file
 */

$module["name"] = t("Church Attendance");
$module["description"] = t("Provides a an easy way to keep track of the church attendance.");
$module["version"] = "1.2.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

$module["dependencies"][] = "calendar";

/*

Version 1.2.1 - Date 07/03/2019

    * Fixed spanish translation errors.
    * Added automatic calculation on year of accepted christ
      when adding/editing a member.

Version 1.2 - Date 03/01/2019

    * Added civil status, work place, work phone and time
      following jesus fields.
    * Added taken courses functionality similar to talents.

Version 1.1 - Date 11/04/2017

    * Added inactive member status.

Version 1.0 - Date 17/02/2017

    * Initial Version

*/
