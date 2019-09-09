<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * @file Jaris CMS module functions file
 *
 * @note File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function (&$scripts) {
        if (Jaris\Authentication::isUserLogged()) {
            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "script/control-menu",
                    "control_menu"
                )
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_STYLES,
    function (&$styles) {
        if (Jaris\Authentication::isUserLogged()) {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    Jaris\Modules::directory("control_menu")
                        . "styles/style.css",
                    "control_menu"
                )
            );

            $styles[] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "style/control-menu",
                    "control_menu"
                )
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("Control Menu")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/control-menu",
                    "control_menu"
                ),
                "arguments" => null
            ];
        }
    }
);
