<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 */

function terms_generator_install()
{
    $url = Jaris\Uri::url(
        Jaris\Modules::getPageUri(
            "admin/settings/terms-generator",
            "terms_generator"
        )
    );

    Jaris\View::addMessage(
        t("Remember to generate your terms on the Settings section of the Control Center.") .
        " <a href=\"".$url."\">"
        . t("Generate Now")
        . "</a>"
    );
}

?>