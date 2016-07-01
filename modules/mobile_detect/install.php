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

function mobile_detect_install()
{
    Jaris\View::addMessage(
        t("Don't forget to set the mobile and tablet themes.") .
        " <a href=\"" . Jaris\Uri::url("admin/themes") . "\">" .
        t("Themes Configuration") . "</a>"
    );
}

?>
