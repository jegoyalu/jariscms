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
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        if ($content_data["input_format"] == "markdown") {
            require_once(
                Jaris\Modules::directory("markdown")
                    . "phpmarkdown/markdown.php"
            );

            $content = "<div class=\"markdown\">"
                . Markdown($content)
                . "</div>"
            ;

            $result = [];

            if (
                preg_match_all(
                    '/src="([a-zA-Z0-9\-\_\?\=\&\;\+\.\/ ]+)"/',
                    $content,
                    $result
                )
            ) {
                $search = [];
                $replace = [];

                foreach ($result[0] as $position => $src) {
                    $search[] = $src;
                    $replace[] = 'src="' . Jaris\Uri::url($result[1][$position]) . '"';
                }

                $content = str_replace($search, $replace, $content);
            }

            if (
                preg_match_all(
                    '/href="([a-zA-Z0-9\-\_\?\=\&\;\+\.\/ ]+)"/',
                    $content,
                    $result
                )
            ) {
                $search = [];
                $replace = [];

                foreach ($result[0] as $position => $src) {
                    $search[] = $src;
                    $replace[] = 'href="' . Jaris\Uri::url($result[1][$position]) . '"';
                }

                $content = str_replace($search, $replace, $content);
            }
        }
    }
);
