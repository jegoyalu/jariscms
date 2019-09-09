<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * @file Database file that stores the content images delete page.
 */

exit;
?>

row: 0
    field: title
        Background script
    field;

    field: content
    <?php
        if (!Jaris\Authentication::isUserLogged()) {
            return;
        }

        $sections = Jaris\System::generateAdminPageSections();

        //Also get control center sections of modules
        Jaris\Modules::hook(Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE, $sections);

        function control_menu_generate_menu_code($sections)
        {
            // Generate menu mobile
            $html = "<div id=\"control-menu-mobile\">";

            $html .= "<div class=\"close\"><a>X</a></div>";

            $html .= "<ul>";

            foreach ($sections as $section_details) {
                $html .= "<li>";

                $html .= "<a>{$section_details['title']}</a>";

                if (count($section_details["sub_sections"]) > 0) {
                    $html .= "<ul>";

                    foreach ($section_details["sub_sections"] as $fields) {
                        $html .= "<li>";
                        $html .= "<a href=\"{$fields['url']}\">{$fields['title']}</a>";
                        $html .= "</li>";
                    }

                    $html .= "</ul>";
                }

                $html .= "</li>";
            }

            $html .= "</ul>";

            $html .= "</div>";

            // Generate menu bar
            $html .= "<div id=\"control-menu\">";

            $html .= "<a class=\"user\" href=\"" .
                Jaris\Uri::url("admin/user") . "\">" . t("my account") .
                "</a>"
            ;

            if (count($sections) > 0) {
                $html .= "<div class=\"view\"><a></a></div>";
            }

            $html .= "<ul>";

            foreach ($sections as $section_details) {
                $html .= "<li>";

                if (count($section_details["sub_sections"]) > 0) {
                    $html .= "<ul>";

                    foreach ($section_details["sub_sections"] as $fields) {
                        $html .= "<li>";
                        $html .= "<a href=\"{$fields['url']}\">{$fields['title']}</a>";
                        $html .= "</li>";
                    }

                    $html .= "</ul>";
                }

                $html .= "<a>{$section_details['title']}</a>";

                $html .= "</li>";
            }

            $html .= "</ul>";

            $html .= "<div class=\"right\">";

            if (Jaris\Authentication::isAdminLogged()) {
                $html .= "<a class=\"about\" title=\"" . t("about jariscms") . "\" href=\"" . Jaris\Uri::url("admin/settings/about") . "\"></a>";

                if ($help_link = Jaris\Settings::get("help_link", "control_menu")) {
                    $html .= "<a class=\"help\" target=\"_blank\" title=\"" . t("help") . "\" href=\"" . Jaris\Uri::url($help_link) . "\"></a>";
                }
            }

            $html .= "<a class=\"logout\" title=\"" . t("logout") . "\" href=\"" . Jaris\Uri::url("admin/logout") . "\"></a>";
            $html .= "</div>";

            $html .= "</div>";

            return $html;
        }
    ?>
    //<script>
    $(document).ready(function(){
        var control_menu_html_all = $('<?php print control_menu_generate_menu_code($sections) ?>');
        control_menu_html_all.appendTo("body");

        var control_menu = $("#control-menu");
        var control_menu_mobile = $("#control-menu-mobile");
        $("body").css("padding-bottom", control_menu.height()+"px");

        CalcControlMenuPosition(control_menu, control_menu_mobile);

        $(window).resize(function(){
            CalcControlMenuPosition(control_menu, control_menu_mobile);
        });

        if("ontouchstart" in document.documentElement){
            $(window).bind("touchstart", function(){
                CalcControlMenuPosition(control_menu, control_menu_mobile);
            });

            $(window).scroll(function(){
                CalcControlMenuPosition(control_menu, control_menu_mobile);
            });
        }

        $("#control-menu ul li a").click(function(){
            $(this).prev().slideToggle(100);
        });

        $("#control-menu .view a, #control-menu-mobile .close").click(function(){
            $("#control-menu-mobile").slideToggle();

            var body = $("body");

            if(body.css("overflow") == "hidden"){
                body.css("overflow", "scroll");
            } else{
                body.css("overflow", "hidden");
            }
        });
    });

    function CalcControlMenuPosition(menu, mobile)
    {
        var window_width = null;
        var window_height = null;

        if("ontouchstart" in document.documentElement){
            window_width = window.innerWidth;
            window_height = window.innerHeight;
        }
        else{
            window_height = $(window).height();
            window_width = $(window).width();
        }

        var menu_width = menu.innerWidth();
        var menu_height = menu.innerHeight();

        menu.css("left", "0px");
        menu.css("bottom", "0px");
        menu.css("width", "100%");

        mobile.css({
            bottom: menu_height + "px",
            left: "0px",
            width: window_width + "px",
            height: (window_height - menu_height) + "px"
        });
    }
    //</script>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
