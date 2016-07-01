<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for faqs module.
 */

function faq_install()
{
    $string = t("A frequently asked questions page.");

    //Create new faq type
    $new_type["name"] = "FAQ";
    $new_type["description"] = "A frequently asked questions page.";

    Jaris\Types::add("faq", $new_type);
}

?>