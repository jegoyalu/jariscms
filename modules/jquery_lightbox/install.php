<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for jquery lightbox module.
 */

function jquery_lightbox_install()
{
    Jaris\View::addMessage(
        t("Remember to set the jquery lightbox configurations to work properly.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/jquery/lightbox",
                "jquery_lightbox"
            )
        ) . "\">" . t("Configure Now") . "</a>"
    );
}
