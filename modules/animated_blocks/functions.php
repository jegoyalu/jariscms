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
    function()
    {
        if(isset($_REQUEST["id"], $_REQUEST["position"]))
        {
            if(Jaris\Uri::get() == "admin/blocks/edit")
            {
                $block_data = Jaris\Blocks::get(
                    $_REQUEST["id"], $_REQUEST["position"]
                );

                if($block_data["is_animated_block"])
                {
                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/animated-blocks/edit", "animated_blocks"
                        ),
                        array(
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"]
                        )
                    );
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_DELETE_BLOCK,
    function(&$id, &$position, &$data, &$page)
    {
        // Delete all images associated to the animated block.
        if(isset($data["is_animated_block"]))
        {
            $slides = unserialize($data["content"]);

            foreach($slides as $slide)
            {
                if($slide["type"] == "image")
                {
                    Jaris\Files::delete(
                        $slide["image"],
                        "animated_blocks"
                    );
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/blocks")
        {
            $tabs_array[0][t("Create Animated Block")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/animated-blocks/add", "animated_blocks"
                ),
                "arguments" => null
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        if(
            Jaris\Authentication::groupHasPermission(
                "add_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $content = array(
                "title" => t("Add Animated Block"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/add", "animated_blocks"
                    )
                ),
                "description" => t("Create blocks with a transition of slides of images or content.")
            );
        }

        if(isset($content))
        {
            foreach($sections as $section_index => $section_data)
            {
                if($section_data["class"] == "blocks")
                {
                    $sections[$section_index]["sub_sections"][] = $content;
                    break;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_BLOCK_TEMPLATE,
    function(&$position, &$page, &$id, &$template_path)
    {
        $theme = Jaris\Site::$theme;

        $default_block = Jaris\Themes::directory($theme) . "block.php";

        if($template_path == $default_block)
        {
            $block_data = Jaris\Blocks::get($id, $position);

            if($block_data["is_animated_block"])
            {
                Jaris\View::addScript(
                    Jaris\Modules::directory("animated_blocks")
                        . "scripts/cycle/jquery.cycle.all.min.js"
                );

                $template_path = Jaris\Modules::directory("animated_blocks")
                    . "templates/block-animated.php"
                ;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_BLOCK,
    function(&$position, &$page, &$field)
    {
        if($field["is_animated_block"])
        {
            $field["content"] = "<div></div>";
            $field["is_system"] = true;
        }
    }
);