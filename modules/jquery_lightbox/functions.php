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
        $display_rule = Jaris\Settings::get("display_rule", "jquery-lightbox");
        $pages = explode(",", Jaris\Settings::get("pages", "jquery-lightbox"));

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery lightbox styles.
                if($page_check == "")
                {
                    $styles[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_lightbox") 
                            . "lightbox/css/lightbox.css"
                    );

                    return;
                }

                $page_check = str_replace(
                    array("/", "*"),
                    array("\\/", ".*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    return;
                }
            }

            $styles[] = Jaris\Uri::url(
                Jaris\Modules::directory("jquery_lightbox") 
                    . "lightbox/css/lightbox.css"
            );
        }
        else if($display_rule == "just_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                $page_check = str_replace(
                    array("/", "*"),
                    array("\\/", ".*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    $styles[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_lightbox") 
                            . "lightbox/css/lightbox.css"
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
        global $jquery_lightbox_display;

        $display_rule = Jaris\Settings::get(
            "display_rule", 
            "jquery-lightbox"
        );
        
        $pages = explode(
            ",", 
            Jaris\Settings::get("pages", "jquery-lightbox")
        );

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery lightbox styles.
                if($page_check == "")
                {
                    $scripts[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_lightbox") 
                            . "lightbox/jquery.lightbox.js"
                    );

                    $jquery_lightbox_display = true;

                    return;
                }

                $page_check = str_replace(
                    array("/", "*"),
                    array("\\/", ".*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    return;
                }
            }

            $scripts[] = Jaris\Uri::url(
                Jaris\Modules::directory("jquery_lightbox") 
                    . "lightbox/jquery.lightbox.js"
            );

            $jquery_lightbox_display = true;
        }
        else if($display_rule == "just_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                $page_check = str_replace(
                    array("/", "*"),
                    array("\\/", ".*"),
                    $page_check
                );

                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, Jaris\Uri::get()))
                {
                    $scripts[] = Jaris\Uri::url(
                        Jaris\Modules::directory("jquery_lightbox") 
                            . "lightbox/jquery.lightbox.js"
                    );

                    $jquery_lightbox_display = true;

                    return;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_SCRIPTS,
    function(&$scripts, &$scripts_code)
    {
        global $jquery_lightbox_display;

        if($jquery_lightbox_display)
        {
            $url = Jaris\Uri::url(
                Jaris\Modules::directory("jquery_lightbox") 
                    . "lightbox/images"
            );

            $scripts_code .= <<<SCRIPT
<script>
$(document).ready(function(){
    $(".lightbox").lightbox({
        fileLoadingImage : '$url/loading.gif',
        fileBottomNavCloseImage : '$url/close.gif',
        strings : {
            help: '',
            prevLinkTitle: '',
            nextLinkTitle: '',
            prevLinkText:  '&laquo;',
            nextLinkText:  '&raquo;',
            closeTitle: '',
            image: '',
            of: ' / '
        }
    });
});
</script>

SCRIPT;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Jquery Lightbox")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/jquery/lightbox",
                    "jquery_lightbox"
                ),
                "arguments" => null
            );
        }
    }
);