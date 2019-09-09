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
                        "title" => t("Backgrounds"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/backgrounds",
                                "backgrounds"
                            )
                        ),
                        "description" => t("To see, add and edit the background images of the site.")
                    ];

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function (&$scripts) {
        $base_url = Jaris\Site::$base_url;

        $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        if (Jaris\Pages::isSystem(Jaris\Uri::get())) {
            return;
        }

        if (is_array($backgrounds) && count($backgrounds) > 0) {
            //Sort array from just_listed to all_except_listed
            $just_listed = [];
            $all_except_listed = [];
            foreach ($backgrounds as $id => $data) {
                if ($data["display_rule"] == "just_listed") {
                    $just_listed[$id] = $data;
                } else {
                    $all_except_listed[$id] = $data;
                }
            }

            $backgrounds = [];

            foreach ($just_listed as $id => $data) {
                $backgrounds[$id] = $data;
            }

            foreach ($all_except_listed as $id => $data) {
                $backgrounds[$id] = $data;
            }
            //end sort

            $current_language = Jaris\Language::getCurrent();

            foreach ($backgrounds as $id => $data) {
                // Skip backgrounds with sepcific language rule.
                if (
                    isset($data["background_language"])
                    &&
                    $data["background_language"] != ""
                ) {
                    if ($current_language != $data["background_language"]) {
                        continue;
                    }
                }

                $display_rule = $data["display_rule"];
                $pages = explode(",", $data["pages"]);

                if ($display_rule == "all_except_listed") {
                    foreach ($pages as $page_check) {
                        $page_check = trim($page_check);

                        //Check if no pages listed and print jquery lightbox styles.
                        if ($page_check == "") {
                            if ($data["multi"]) {
                                $scripts[] = Jaris\Uri::url(
                                    Jaris\Modules::directory("backgrounds")
                                        . "scripts/backstretch/jquery.backstretch.mod.js"
                                );
                            }

                            $scripts[] = Jaris\Uri::url(
                                Jaris\Modules::getPageUri(
                                    "script/background",
                                    "backgrounds"
                                ),
                                ["id" => $id]
                            );

                            return;
                        }

                        $page_check = str_replace(
                            ["/", "/*"],
                            ["\\/", "/.*"],
                            $page_check
                        );

                        $page_check = "/^$page_check\$/";

                        if (preg_match($page_check, Jaris\Uri::get())) {
                            return;
                        }
                    }

                    if ($data["multi"]) {
                        $scripts[] = Jaris\Uri::url(
                            Jaris\Modules::directory("backgrounds")
                                . "scripts/backstretch/jquery.backstretch.mod.js"
                        );
                    }

                    $scripts[] = Jaris\Uri::url(
                        Jaris\Modules::getPageUri(
                            "script/background",
                            "backgrounds"
                        ),
                        ["id" => $id]
                    );
                } elseif ($display_rule == "just_listed") {
                    foreach ($pages as $page_check) {
                        $page_check = trim($page_check);

                        $page_check = str_replace(
                            ["/", "/*"],
                            ["\\/", "/.*"],
                            $page_check
                        );

                        $page_check = "/^$page_check\$/";

                        if (preg_match($page_check, Jaris\Uri::get())) {
                            if ($data["multi"]) {
                                $scripts[] = Jaris\Uri::url(
                                    Jaris\Modules::directory("backgrounds")
                                        . "scripts/backstretch/jquery.backstretch.mod.js"
                                );
                            }

                            $scripts[] = Jaris\Uri::url(
                                Jaris\Modules::getPageUri(
                                    "script/background",
                                    "backgrounds"
                                ),
                                ["id" => $id]
                            );
                            return;
                        }
                    }
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("Backgrounds")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/backgrounds",
                    "backgrounds"
                ),
                "arguments" => []
            ];
        }
    }
);
