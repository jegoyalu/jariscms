<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 */

function popup_install()
{
    Jaris\View::addMessage(
        t("You can now configure your popup messages.") .
        " <a href=\"" .
        Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/popup",
                "popup"
            )
        ) . "\">" . t("Configure Now") . "</a>"
    );
}
