<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for module.
 */

function listing_install()
{
    $string = t("Content Listing");
    $string = t("Page that display a list of content by a given criteria.");

    //Create new properties type
    $new_type["name"] = "Content Listing";
    $new_type["description"] = "Page that display a list of content by a given criteria.";

    Jaris\Types::add("listing", $new_type);
}

?>