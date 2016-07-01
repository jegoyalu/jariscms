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
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        $uri = Jaris\Uri::get();
        switch($uri)
        {
            case "admin/settings":
                $tabs_array[1][t("Google Translate")] = array(
                    "uri" => Jaris\Modules::getPageUri(
                        "admin/settings/google-translate",
                        "google_translate"
                    ),
                    "arguments" => null
                );

                break;
        }
    }
);