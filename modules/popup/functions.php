<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        $group = Jaris\Authentication::currentUserGroup();

        $title = t("Settings");

        foreach($sections as $index => $sub_section)
        {
            if($sub_section["title"] == $title)
            {
                if(
                    Jaris\Authentication::groupHasPermission(
                        "edit_settings",
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    $sub_section["sub_sections"][] = array(
                        "title" => t("Popups"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/popup",
                                "popup"
                            )
                        ),
                        "description" => t("Create or manage popup messages.")
                    );

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_INITIALIZATION,
    function()
    {
        $popups = Jaris\Data::parse(
            Jaris\Site::dataDir() . "settings/popup.php",
            false
        );

        foreach($popups as $popup_id=>$popup)
        {
            $groups = unserialize($popup["groups"]);
            $display_rule = $popup["display_rule"];
            $pages = array_map('trim', explode(",", trim($popup["pages"])));

            // Check groups
            if(
                count($groups) > 0
                &&
                !in_array(Jaris\Authentication::currentUserGroup(), $groups)
            )
            {
                continue;
            }

            // Check display rule
            $can_display = true;

            if($display_rule == "all_except_listed")
            {
                foreach($pages as $page_check)
                {
                    $page_check = str_replace(
                        array("/", "*"),
                        array("\\/", ".*"),
                        $page_check
                    );

                    $page_check = "/^$page_check\$/";

                    if(preg_match($page_check, Jaris\Uri::get()))
                    {
                        $can_display = false;
                        break;
                    }
                }
            }
            else if($display_rule == "just_listed")
            {
                $page_found = false;

                foreach($pages as $page_check)
                {
                    $page_check = str_replace(
                        array("/", "*"),
                        array("\\/", ".*"),
                        $page_check
                    );

                    $page_check = "/^$page_check\$/";

                    if(preg_match($page_check, Jaris\Uri::get()))
                    {
                        $page_found = true;
                        break;
                    }
                }

                if(!$page_found)
                {
                    $can_display = false;
                }
            }

            if(!$can_display)
            {
                continue;
            }

            // Check return condition
            if(trim($popup["condition"]) != "")
            {
                if(!eval('?>' . $popup["condition"]))
                {
                    continue;
                }
            }

            Jaris\Session::start();

            $_SESSION["popup"][Jaris\Uri::get()] = array(
                "id" => $popup_id,
                "message" => Jaris\System::evalPHP($popup["message"]),
                "shown" => false
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function(&$content, &$content_title, &$content_data)
    {
        if(!Jaris\Session::exists())
        {
            return;
        }

        Jaris\Session::start();

        if(isset($_SESSION["popup"]))
        {
            foreach($_SESSION["popup"] as $popup_uri=>$popup_data)
            {
                if(Jaris\Uri::get() == $popup_uri)
                {
                    $content .= '<div class="popup-message" '
                        . 'id="popup-'.$popup_data["id"].'" '
                        . 'style="display: none;">'
                        . Jaris\System::evalPHP($popup_data["message"])
                        . '</div>'
                    ;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_STYLES,
    function(&$styles)
    {
        if(!Jaris\Session::exists())
        {
            return;
        }

        Jaris\Session::start();

        if(isset($_SESSION["popup"]))
        {
            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("popup")
                    . "styles/jquery.simplepopup.css"
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_SCRIPTS,
    function(&$scripts, &$scripts_code)
    {
        if(!Jaris\Session::exists())
        {
            return;
        }

        Jaris\Session::start();

        if(isset($_SESSION["popup"]))
        {
            $script_file_added = false;
            $popups = $_SESSION["popup"];

            foreach($popups as $popup_uri=>$popup_data)
            {
                if(Jaris\Uri::get() == $popup_uri)
                {
                    $popup = Jaris\Data::get(
                        $popup_data["id"],
                        Jaris\Site::dataDir() . "settings/popup.php"
                    );

                    if(!$script_file_added)
                    {
                        $script_file = Jaris\Uri::url(
                            Jaris\Modules::directory("popup")
                                . "scripts/jquery.simplepopup.js"
                        );

                        $scripts_code .= "<script type=\"text/javascript\" "
                            . "src=\"$script_file\""
                            . ">"
                            . "</script>\n"
                        ;

                        $script_file_added = true;
                    }

                    $scripts_code .= '<script>'
                        . '$(document).ready(function(){'
                        . '$("#popup-'.$popup_data["id"].'").simplePopup({'
                        . 'delay: '.(intval($popup["delay"])*1000).','
                        . 'onMouseLeave: '.(!empty($popup["onmouseleave"])?"true":"false").','
                        . 'displayOnce: '.(!empty($popup["display_once"])?"true":"false")
                        . '});'
                        . '});'
                        . '</script>' . "\n"
                    ;

                    $_SESSION["popup"][$popup_uri]["shown"] = true;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_DISPLAY,
    function()
    {
        if(!Jaris\Session::exists())
        {
            return;
        }

        Jaris\Session::start();

        if(isset($_SESSION["popup"]))
        {
            $popups = $_SESSION["popup"];
            foreach($popups as $popup_uri=>$popup_data)
            {
                if(Jaris\Uri::get() == $popup_uri && $popup_data["shown"])
                {
                    unset($_SESSION["popup"][$popup_uri]);
                }
            }

            if(count($_SESSION["popup"]) <= 0)
            {
                unset($_SESSION["popup"]);
            }
        }

        Jaris\Session::destroyIfEmpty();
    }
);
