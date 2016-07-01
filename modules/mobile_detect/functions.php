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
    Jaris\Site::SIGNAL_INITIALIZATION,
    function()
    {
        $theme = Jaris\Site::$theme;

        if(isset($_REQUEST["device"]))
        {
            if(
                $_REQUEST["device"] == "desktop" ||
                $_REQUEST["device"] == "phone" ||
                $_REQUEST["device"] == "tablet"
            )
                $_SESSION["device"] = $_REQUEST["device"];
        }

        $mobile_theme = Jaris\Settings::get("mobile_theme", "mobile_detect");
        $tablet_theme = Jaris\Settings::get("tablet_theme", "mobile_detect");

        if(!$mobile_theme)
        {
            if(file_exists(Jaris\Themes::directory($theme) . "mobile/info.php"))
            {
                $mobile_theme = "$theme/mobile";
            }
        }

        if(!$tablet_theme)
        {
            if(file_exists(Jaris\Themes::directory($theme) . "tablet/info.php"))
            {
                $tablet_theme = "$theme/tablet";
            }
        }

        if(isset($_SESSION["device"]))
        {
            switch($_SESSION["device"])
            {
                case "phone":
                    if($mobile_theme)
                        $theme = $mobile_theme;
                    break;
                case "tablet":
                    if($tablet_theme)
                        $theme = $tablet_theme;
                    break;
            }
        }
        else
        {
            $device = new Mobile_Detect();

            if($device->isMobile() && !$device->isTablet())
            {
                if($mobile_theme)
                    $theme = $mobile_theme;
            }
            elseif($device->isTablet())
            {
                if($tablet_theme)
                    $theme = $tablet_theme;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Themes::SIGNAL_GET_ENABLED_THEMES,
    function(&$themes)
    {
        $themes_copy = array();

        foreach($themes as $theme)
        {
            $themes_copy[] = $theme;

            if(is_dir(Jaris\Themes::directory($theme) . "mobile"))
                $themes_copy[] = "$theme/mobile";

            if(is_dir(Jaris\Themes::directory($theme) . "tablet"))
                $themes_copy[] = "$theme/tablet";
        }

        $themes = $themes_copy;
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(
            Jaris\Uri::get() == "admin/themes" ||
            Jaris\Uri::get() == "admin/themes/mobile" ||
            Jaris\Uri::get() == "admin/themes/tablet"
        )
        {
            $tabs_array[0][t("Desktop")] = array(
                "uri" => "admin/themes",
                "arguments" => null
            );

            $tabs_array[0][t("Mobile")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/themes/mobile",
                    "mobile_detect"
                ),
                "arguments" => null
            );

            $tabs_array[0][t("Tablet")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/themes/tablet",
                    "mobile_detect"
                ),
                "arguments" => null
            );
        }
    }
);