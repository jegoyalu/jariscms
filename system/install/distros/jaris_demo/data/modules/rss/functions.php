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
    Jaris\View::SIGNAL_GET_META_TAGS,
    function (&$meta_tags) {
        $title = t(Jaris\Settings::get("title", "main"));

        $meta_tags .= "<link rel=\"alternate\" title=\"RSS - $title\" href=\"" .
            Jaris\Uri::url(Jaris\Modules::getPageUri("rss", "rss")) .
            "\" type=\"application/rss+xml\">\n"
        ;
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("RSS")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/rss",
                    "rss"
                ),
                "arguments" => null
            ];
        }
    }
);
