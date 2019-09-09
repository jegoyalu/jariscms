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
        $display_rule = Jaris\Settings::get(
            "display_rule", "jquery-fancybox"
        );

        $pages = explode(
            ",",
            Jaris\Settings::get("pages", "jquery-fancybox")
        );

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery fancybox styles.
                if($page_check == "")
                {
                    $styles[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_fancybox")
                            . "css/jquery.fancybox.min.css"
                    );

                    return;
                }

                $page_check = str_replace(
                    array("/", "/*"),
                    array("\\/", "/.*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    return;
                }
            }

            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("jquery_fancybox")
                    . "css/jquery.fancybox.min.css"
            );
        }
        else if($display_rule == "just_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                $page_check = str_replace(
                    array("/", "/*"),
                    array("\\/", "/.*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    $styles[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_fancybox")
                            . "css/jquery.fancybox.min.css"
                    );

                    return;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GET_SYSTEM_SCRIPTS,
    function(&$scripts)
    {
        $base_url = Jaris\Site::$base_url;

        $display_rule = Jaris\Settings::get(
            "display_rule",
            "jquery-fancybox"
        );

        $pages = explode(
            ",",
            Jaris\Settings::get("pages", "jquery-fancybox")
        );

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery fancybox styles.
                if($page_check == "")
                {
                    $scripts[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_fancybox")
                            . "js/jquery.fancybox.min.js"
                    );

                    return;
                }

                $page_check = str_replace(
                    array("/", "/*"),
                    array("\\/", "/.*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    return;
                }
            }

            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("jquery_fancybox")
                    . "js/jquery.fancybox.min.js"
            );
        }
        else if($display_rule == "just_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                $page_check = str_replace(
                    array("/", "/*"),
                    array("\\/", "/.*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    $scripts[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_fancybox")
                            . "js/jquery.fancybox.min.js"
                    );

                    return;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Jquery fancybox")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/jquery/fancybox",
                    "jquery_fancybox"
                ),
                "arguments" => array()
            );
        }
    }
);