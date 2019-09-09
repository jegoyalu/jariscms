<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for jaris realty module.
 */

function gallery_install()
{
    $string = t("Gallery");
    $string = t("For creating image galleries using lightbox.");

    //Create new properties type
    $new_type["name"] = "Gallery";
    $new_type["description"] = "For creating image galleries using lightbox.";

    Jaris\Types::add("gallery", $new_type);
}
