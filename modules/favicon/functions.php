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
    Jaris\View::SIGNAL_GET_META_TAGS,
    function(&$meta_tags)
    {
        $settings = Jaris\Settings::getAll("favicon");

        $url = Jaris\Uri::url(Jaris\Files::getDir("favicon"));

        if(!isset($settings["favicon_enable"]))
            return;

        if(!$settings["favicon_enable"])
            return;

        $meta_tags .=
            '<link rel="apple-touch-icon-precomposed" sizes="57x57" href="'.$url.'apple-touch-icon-57x57.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="'.$url.'apple-touch-icon-114x114.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="72x72" href="'.$url.'apple-touch-icon-72x72.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="'.$url.'apple-touch-icon-144x144.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="60x60" href="'.$url.'apple-touch-icon-60x60.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="120x120" href="'.$url.'apple-touch-icon-120x120.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="76x76" href="'.$url.'apple-touch-icon-76x76.png" />' . "\n"
            . '<link rel="apple-touch-icon-precomposed" sizes="152x152" href="'.$url.'apple-touch-icon-152x152.png" />' . "\n"
            . '<link rel="icon" type="image/png" href="'.$url.'favicon-196x196.png" sizes="196x196" />' . "\n"
            . '<link rel="icon" type="image/png" href="'.$url.'favicon-96x96.png" sizes="96x96" />' . "\n"
            . '<link rel="icon" type="image/png" href="'.$url.'favicon-32x32.png" sizes="32x32" />' . "\n"
            . '<link rel="icon" type="image/png" href="'.$url.'favicon-16x16.png" sizes="16x16" />' . "\n"
            . '<link rel="icon" type="image/png" href="'.$url.'favicon-128.png" sizes="128x128" />' . "\n"
            . '<meta name="application-name" content="'.$settings["application_name"].'"/>' . "\n"
            . '<meta name="msapplication-TileColor" content="#'.$settings["metro_tile_color"].'" />' . "\n"
            . '<meta name="msapplication-TileImage" content="'.$url.'mstile-144x144.png" />' . "\n"
            . '<meta name="msapplication-square70x70logo" content="'.$url.'mstile-70x70.png" />' . "\n"
            . '<meta name="msapplication-square150x150logo" content="'.$url.'mstile-150x150.png" />' . "\n"
            . '<meta name="msapplication-wide310x150logo" content="'.$url.'mstile-310x150.png" />' . "\n"
            . '<meta name="msapplication-square310x310logo" content="'.$url.'mstile-310x310.png" />' . "\n"
        ;
    }
);

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
                        "title" => t("Favicon"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/favicon",
                                "favicon"
                            )
                        ),
                        "description" => t("Set or modify the favorite icon of the site.")
                    );

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
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
            $tabs_array[0][t("Favicon")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/favicon",
                    "favicon"
                ),
                "arguments" => null
            );
        }
    }
);