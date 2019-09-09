<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for jquery fancybox module.
 */

function jquery_fancybox_install()
{
    Jaris\View::addMessage(
        t("Remember to set the jquery fancybox configurations to work properly.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/jquery/fancybox",
                "jquery_fancybox"
            )
        ) . "\">" . t("Configure Now") . "</a>"
    )
    ;
}
