<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module info file.
 */

$module["name"] = "Minify";
$module["description"] = "Uses http://code.google.com/p/minify/ to compress all css and javascript output to one file for faster web download.";
$module["version"] = "1.3.0";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

=Change Log=

Version 1.3.0 - Date 30/04/2017

    * Fixed newly introduced bug of not been able to read a theme css and js
      files because of new functionality to add the theme version as parameter
      of theme files so they get refreshed on theme changes.

Version 1.2.4 - Date 22/09/2016

    * Fixed bug of module not adding externally linked scripts.

Version 1.2.3 - Date 05/26/2016

    * Included global refactorization.

Version 1.2.2 - Date 12/04/2016

    * Disable minification of ckeditor.

Version 1.2.1 - Date 20/01/2015

    * Fixed missing argument on admin/settings/minify/clear-cache.

Version 1.2 - Date 18/12/2014

    * Now cached css and js files are generated without http://
      references to prevent security warnings when running a website
      with https://.

Version 1.1 - Date 30/08/2014

    * Fixed module to also work with dynamic css/javascript.

Version 1.0 - Date 30/08/2010

    * Initial module creation

**/