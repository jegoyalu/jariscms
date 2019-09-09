<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_INITIALIZATION,
    function () {
        $uri = $_REQUEST["uri"];

        if ($uri && Jaris\Uri::get() != "admin/pages/add") {
            $page_data = Jaris\Pages::get($uri);
            if ($page_data["type"] == "gallery") {
                switch (Jaris\Uri::get()) {
                    case "admin/pages/edit":
                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/pages/gallery/edit",
                                "gallery"
                            ),
                            ["uri" => $uri]
                        );
                        // no break
                    default:
                        break;
                }
            }
        } elseif ($_REQUEST["type"]) {
            $page = Jaris\Uri::get();
            if ($page == "admin/pages/add" && $_REQUEST["type"] == "gallery") {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/pages/gallery/add",
                        "gallery"
                    ),
                    ["type" => "gallery", "uri" => $uri]
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (!Jaris\Pages::isSystem()) {
            $page_data = Jaris\Pages::get(Jaris\Uri::get());
            if ($page_data["type"] == "gallery") {
                $tabs_array = [];

                if (
                    $page_data["author"] == Jaris\Authentication::currentUser() ||
                    Jaris\Authentication::isAdminLogged() ||
                    Jaris\Authentication::groupHasPermission(
                        "edit_all_user_content",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    $tabs_array[0][t("Edit Gallery")] = [
                        "uri" => Jaris\Modules::getPageUri(
                            "admin/pages/gallery/edit",
                            "gallery"
                        ),
                        "arguments" => ["uri" => Jaris\Uri::get()]
                    ];
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_CONTENT_TEMPLATE,
    function (&$page, &$type, &$template_path) {
        $theme = Jaris\Site::$theme;

        $default_template = Jaris\Themes::directory($theme) . "content.php";

        if ($type == "gallery" && $template_path == $default_template) {
            $template_path = Jaris\Modules::directory("gallery")
                . "templates/content-gallery.php"
            ;
        }
    }
);
