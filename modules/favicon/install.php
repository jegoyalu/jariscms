<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 */

function favicon_install()
{
    Jaris\View::addMessage(
        t("Remember to add your favorite icon on the Settings section of the Control Center.") .
        " <a href=\"" .
        Jaris\Uri::url(Jaris\Modules::getPageUri("admin/settings/favicon", "favicon")) .
        "\">" . t("Add Icon Now") . "</a>"
    );
}
