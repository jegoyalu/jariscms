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

$module["name"] = "Animated Blocks";
$module["description"] = "Various java script animations to post content into blocks in a more interactive way.";
$module["version"] = "2.5.2";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 2.5.2 - Date 18/11/2016

    * Updated block-animated template to use the $row_id instead of $id.

Version 2.5.1 - Date 05/26/2016

    * Included global refactorization.

Version 2.5 - Date 04/05/2016

    * Fixed "please provide required fields" issue when editing or adding
      slides of type uri.

Version 2.4 - Date 17/04/2016

    * Fixed issue when editing image slides.

Version 2.3 - Date 17/02/2016

    * Fixed issue when adding uri slides.

Version 2.2 - Date 28/11/2015

    * Added visual ordering support.
    * Enabled upload of images directly when adding a slide.

Version 2.1 - Date 08/15/2014

    * Make use of jariscms rendering mode for scripts and styles.

Version 2.0 - Date 08/07/2014

    * Initial work to make slides responsive.

Version 1.3 - Date 27/01/2013

    * Enabled support for positioning blocks per theme.

Version 1.2.4 - Date 15/10/2012

    * Fix to animated-blocks/script declaring variables as global instead of local.

Version 1.2.3 - Date 22/09/2012

    * Changed effects terminology by settings on various parts of the module.

Version 1.2.2 - Date 21/03/2012

    * Added transparent option to main area options

Version 1.2.1 - Date 20/03/2012

    * Fixed bug on next and previous symbols size not working.

Version 1.2 - Date 08/03/2012

    * Added optional next and previous buttons to traverse the slides.
    * Added "Add Animated Block" link to blocks section on control center
    * Added Pre-content and Sub-content
    * Now the slide description supports php code

Version 1.1 - Date 25/08/2010

    * Fixed theme_block hook function to comply with new jaris cms 4.3.4 changes

**/