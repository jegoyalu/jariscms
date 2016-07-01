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

function revision_install()
{
    Jaris\View::addMessage(
        t("Remember to set the group permissions for the revisions system to meet your needs.") .
        " <a href=\"" .
        Jaris\Uri::url("admin/groups") .
        "\">" .
        t("Revise Permissions") .
        "</a>"
    );
}

?>
