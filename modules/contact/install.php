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

    // Create income database
    if (!Jaris\Sql::dbExists("contact_archive")) {
        //Income database
        $db = Jaris\Sql::open("contact_archive");

        Jaris\Sql::query(
            "create table contact_archive ("
            . "id integer primary key, "
            . "created_date text, "
            . "day integer, "
            . "month integer, "
            . "year integer, "
            . "uri text, "
            . "message text, "
            . "from_info text, "
            . "fields text, "
            . "fields_value text, "
            . "attachments text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index contact_archive_index "
            . "on contact_archive ("
            . "id desc, "
            . "created_date desc, "
            . "day desc, "
            . "month desc, "
            . "year desc, "
            . "uri desc "
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}
