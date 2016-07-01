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

$module["name"] = "Blog";
$module["description"] = "Provides a better blog functionality than content blocks.";
$module["version"] = "1.4.5";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 1.4.5 - Date 05/26/2016

    * Included global refactorization.

Version 1.4.4 - Date 31/01/2015

    * Make use of Jaris\Types::getImageUrl()

Version 1.4.3 - Date 3/08/2014

    * Formatted the blog archive block tu use a ul.

Version 1.4.2 - Date 21/10/2013

    * Added jaris_sqlite_turbo to blog_get_from_db.

Version 1.4.1 - Date 15/10/2012

    * Changed html of the recents post block.

Version 1.4 - Date 4/5/2011

    * Fixed a bug when the content type of a blog page was changed to another it was kept on blog list
    * Fixed bug of month and year not passed as arguments on pages navigation on users post list

Version 1.3 - Date 22/2/2011

    * Added the ability to change post templates

*/
