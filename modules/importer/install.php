<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for importer module.
 */

function importer_install()
{
    Jaris\View::addMessage(
        t("You can import pages by going to the just installed content import section.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/import", "importer")
        ) . "\">" . t("Go to importer") . "</a>"
    );
}

?>