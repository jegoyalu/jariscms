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
    Jaris\System::SIGNAL_SAVE_PAGE_TO_CACHE,
    function(&$uri, &$page_data, &$content)
    {
        $cache_expire = Jaris\Settings::get("cache_expire", "main");

        if($cache_expire <= 0 || $cache_expire > 3600)
        {
            $seconds = 3600;
        }
        elseif($cache_expire <= 3600)
        {
            $seconds = $cache_expire;
        }

        // When a page has been saved to system cache
        // it means we can also cache it on hiawatha.
        header("X-Hiawatha-Cache: $seconds");

        // Manually set the content lenght header since
        // hiawatha isn't doing it and browser keeps trying
        // to finish load the page.
        header("Content-Length: " . strlen($content)+200);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_CACHE_PAGE,
    function(&$uri, &$page_data, &$content)
    {
        $cache_expire = Jaris\Settings::get("cache_expire", "main");

        if($cache_expire <= 0 || $cache_expire > 3600)
        {
            $seconds = 3600;
        }
        elseif($cache_expire <= 3600)
        {
            $seconds = $cache_expire;
        }

        // When the hiawatha cache expires if the page is served
        // from system cache it means we can re-cache it on hiawatha.
        header("X-Hiawatha-Cache: $seconds");

        // Manually set the content lenght header since
        // hiawatha isn't doing it and browser keeps trying
        // to finish load the page.
        header("Content-Length: " . strlen($content)+200);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_CLEAR_PAGE_CACHE,
    function()
    {
        header("X-Hiawatha-Cache-Remove: all");
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function(&$page, &$new_data, &$page_path)
    {
        if(Jaris\Settings::get("enable_cache", "main"))
        {
            $uri = "/$page";

            if(Jaris\Settings::get("home_page", "main") == $page)
            {
                $uri = "/";
            }

            header("X-Hiawatha-Cache-Remove: $uri");
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function(&$page, &$page_path)
    {
        if(Jaris\Settings::get("enable_cache", "main"))
        {
            $uri = "/$page";

            if(Jaris\Settings::get("home_page", "main") == $page)
            {
                $uri = "/";
            }

            header("X-Hiawatha-Cache-Remove: $uri");
        }
    }
);