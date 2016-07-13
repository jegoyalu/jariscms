<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * @note File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_STYLES,
    function(&$styles)
    {
        if(
            Jaris\System::getUserBrowser() == "ie" && 
            !isset($_COOKIE["ie_check"])
        )
        {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("ieupdate") 
                    . "css/style.css"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        if(
            Jaris\System::getUserBrowser() == "ie" && 
            !isset($_COOKIE["ie_check"])
        )
        {
            $scripts[] = Jaris\Uri::url("ie-update-script");
        }
        else
        {
            Jaris\Session::addCookie("ie_check", 1);
        }
    }
);