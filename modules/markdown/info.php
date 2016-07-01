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

$module["name"] = "Markdown";
$module["description"] = "Adds markdown as input format for content.";
$module["version"] = "1.1.2";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

=Changes Log=

Version 1.1.2 - Date 27/05/2016

    * Fixed markdown classes to not use constructos functions with the name
      of the class since that is been deprecated.

Version 1.1.1 - Date 05/26/2016

    * Included global refactorization.

Version 1.1 - Date 21/08/2014

    * Parse href and src attributes that link to site content and replace it
    * with full url so links work on installations run from a subdir.

*/