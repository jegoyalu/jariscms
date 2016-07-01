<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for ecommerce module.
 */

function google_translate_install()
{
    //Strings to enable translations programs to scan them
    $strings[] = t("Translate");

    //User notification
    Jaris\View::addMessage(
        t("Remember to set the google translate configurations to work properly.")
        . " <a href=\""
        . Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/google-translate",
                "google_translate"
            )
        )
        . "\">"
        . t("Configure Now")
        . "</a>"
    );
}

?>