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
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("Terms Generator")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/terms-generator",
                    "terms_generator"
                ),
                "arguments" => []
            ];
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function (&$sections) {
        $group = Jaris\Authentication::currentUserGroup();

        $title = t("Settings");

        foreach ($sections as $index => $sub_section) {
            if ($sub_section["title"] == $title) {
                if (
                    Jaris\Authentication::groupHasPermission(
                        "edit_settings",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    $sub_section["sub_sections"][] = [
                        "title" => t("Terms Generator"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/terms-generator",
                                "terms_generator"
                            )
                        ),
                        "description" => t("Helps you generate terms and conditions, privacy policy and return policy.")
                    ];

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
);
