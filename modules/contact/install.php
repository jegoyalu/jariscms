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

function contact_install()
{
    //To facilitate translation
    $text = t("Contact Form");
    $text = t("To create a contact form page.");
    $text = t("Message");

    //Create new contact form type
    $new_type["name"] = "Contact Form";
    $new_type["description"] = "To create a contact form page.";

    Jaris\Types::add("contact-form", $new_type);
}

?>
