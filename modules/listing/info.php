<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module info file
 */

$module["name"] = t("Content Listing");
$module["description"] = t("To create pages that display a list of content by a given set of criteria.");
$module["version"] = "1.8.3";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/*

Version 1.8.3 - Date 12/04/2019

    * Make use of $with_content parementer on
      Categirues::getSubcategoriesInParentOrder().
    * Only display categories with content on listing pages with categories
      enabled.

Version 1.8.2 - Date 12/03/2019

    * Added radiobox option to filters selector.
    * Fixed error on filter selectors box when no content type filter selected.

Version 1.8.1 - Date 04/03/2019

    * Fixed issue on listing block using null content type.

Version 1.8.0 - Date 19/01/2019

    * Added realty module filtering options.

Version 1.7.1 - Date 31/07/2018

    * Fixed issues reported by phan.
    * Adjustments for scalar type hints on core.

Version 1.7 - Date 05/06/2018

    * Added additional functionality if reviews module is installed.
    * Added new 'From current date' ordering mechanism.

Version 1.6.2 - Date 21/04/2018

    * Added id to listing-block-container.
    * Added skip current page option on listing blocks.
    * Added ecommerce listing options to listing blocks.

Version 1.6.1 - Date 02/04/2018

    * Fixed summary not showing up if input format was php_code due to
      strip_tags not working on html content with <?php tags.

Version 1.6 - Date 21/11/2017

    * Added option to display a filters selector block.

Version 1.5.1 - Date 10/05/2017

    * Added option to only display on sale products on ecommerce mode.

Version 1.5 - Date 08/05/2017

    * Added additional functionality if ecommerce module is installed.
    * Added display sorting selector.
    * Added display amount to show selector.

Version 1.4.6 - Date 24/08/2016

    * Only display approved results.

Version 1.4.5 - Date 15/08/2016

    * Added container to listing blocks.

Version 1.4.4 - Date 26/05/2016

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

*/
