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
    Jaris\Site::SIGNAL_PAGE_DATA,
    function (&$page_data) {
        $uri = Jaris\Uri::get();

        if ($uri == "biblia/buscar") {
            return;
        }

        $segments = explode("/", $uri);

        if ($segments[0] == "biblia") {
            if (isset($_REQUEST["btnView"])) {
                $versiculo = trim($_REQUEST["versiculo"]) != "" ?
                    "#" . $_REQUEST["versiculo"]
                    :
                    ""
                ;

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri("biblia", "biblia")
                    . "/"
                    . $_REQUEST["biblia"]
                    . "/"
                    . $_REQUEST["libro"]
                    . "/"
                    . $_REQUEST["capitulo"]
                    . $versiculo
                );
            }


            if (count($segments) == 2) {
                $_REQUEST["biblia"] = $segments[1];
                $_REQUEST["libro"] = "genesis";
                $_REQUEST["capitulo"] = 1;
            } elseif (count($segments) == 3) {
                $_REQUEST["biblia"] = $segments[1];
                $_REQUEST["libro"] = $segments[2];
                $_REQUEST["capitulo"] = 1;
            } elseif (count($segments) == 4) {
                $_REQUEST["biblia"] = $segments[1];
                $_REQUEST["libro"] = $segments[2];
                $_REQUEST["capitulo"] = $segments[3];
            } elseif (count($segments) == 5) {
                $_REQUEST["biblia"] = $segments[1];
                $_REQUEST["libro"] = $segments[2];
                $_REQUEST["capitulo"] = $segments[3];
                $_REQUEST["versiculo"] = $segments[4];
            }

            $page_data[0] = Jaris\Pages::get(
                Jaris\Modules::getPageUri(
                    "biblia",
                    "biblia"
                )
            );

            $page_data[0]["title"] = Jaris\System::evalPHP(
                $page_data[0]["title"]
            );

            if (
                isset($_REQUEST["libro"])
                &&
                isset($_REQUEST["capitulo"])
                &&
                isset($_REQUEST["versiculo"])
            ) {
                $page_data[0]["meta_title"] = biblia_get_libro_label(
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                    )
                    . " "
                    . intval($_REQUEST["capitulo"])
                    . ":"
                    . biblia_get_versiculo_text($_REQUEST["versiculo"])
                    . " - "
                    . biblia_get_title($_REQUEST["biblia"]);
                ;
            } elseif (isset($_REQUEST["libro"]) && isset($_REQUEST["capitulo"])) {
                $page_data[0]["meta_title"] = biblia_get_libro_label(
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                    )
                    . " "
                    . intval($_REQUEST["capitulo"])
                    . " - "
                    . biblia_get_title($_REQUEST["biblia"]);
                ;
            } elseif (isset($_REQUEST["libro"])) {
                $page_data[0]["meta_title"] = biblia_get_libro_label(
                    $_REQUEST["libro"],
                    $_REQUEST["biblia"]
                    )
                    . " - "
                    . biblia_get_title($_REQUEST["biblia"]);
                ;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        $biblia_settings = biblia_get_settings($content_data["type"]);

        if ($biblia_settings["enabled"]) {
            $content = biblia_convertir_versos(
                $content,
                $biblia_settings["biblia"]
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/types/edit") {
            $tabs_array[0]["Biblia"] = [
                "uri" => Jaris\Modules::getPageUri("admin/types/biblia", "biblia"),
                "arguments" => ["type" => $_REQUEST["type"]]
            ];
        }
    }
);
