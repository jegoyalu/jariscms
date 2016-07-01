<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for rss module.
 */

function rss_install()
{
    Jaris\View::addMessage(
        t("Remember to set the rss configurations to work properly.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/rss",
                "rss"
            )
        ) . "\">" . t("Configure Now") . "</a>"
    );

    Jaris\View::addMessage(
        t("You can use the rss selecter tool to generate rss by content type.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri("rss/selector", "rss")
        ) . "\">" . t("Goto the Rss Selector Page") . "</a>"
    );
}

?>
