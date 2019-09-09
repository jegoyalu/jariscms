<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function animated_blocks_get_effects_list()
{
    $effects["blindX"] = "blindX";
    $effects["blindY"] = "blindY";
    $effects["blindZ"] = "blindZ";
    $effects["cover"] = "cover";
    $effects["curtainX"] = "curtainX";
    $effects["curtainY"] = "curtainY";
    $effects["fade"] = "fade";
    $effects["fadeZoom"] = "fadeZoom";
    $effects["growX"] = "growX";
    $effects["growY"] = "growY";
    $effects["none"] = "none";
    $effects["scrollUp"] = "scrollUp";
    $effects["scrollDown"] = "scrollDown";
    $effects["scrollLeft"] = "scrollLeft";
    $effects["scrollRight"] = "scrollRight";
    $effects["scrollHorz"] = "scrollHorz";
    $effects["scrollVert"] = "scrollVert";
    $effects["shuffle"] = "shuffle";
    $effects["slideX"] = "slideX";
    $effects["slideY"] = "slideY";
    $effects["toss"] = "toss";
    $effects["turnUp"] = "turnUp";
    $effects["turnDown"] = "turnDown";
    $effects["turnLeft"] = "turnLeft";
    $effects["turnRight"] = "turnRight";
    $effects["uncover"] = "uncover";
    $effects["wipe"] = "wipe";
    $effects["zoom"] = "zoom";

    return $effects;
}

function animated_blocks_get_settings($block_data)
{
    $block_data["effects"] = unserialize($block_data["effects"]);

    if (is_array($block_data["effects"])) {
        return $block_data["effects"];
    }

    /* Get default settings */

    //Animation area
    $settings["width"] = "575px";
    $settings["height"] = "350px";
    $settings["background_color"] = "333333";
    $settings["background_transparent"] = false;
    $settings["auto_info"] = true;
    $settings["border_style"] = "none";
    $settings["border_width"] = "1px";
    $settings["border_color"] = "333333";

    //Content
    $settings["title_color"] = "FFFFFF";
    $settings["title_size"] = "28px";
    $settings["title_margin"] = "0px 0px 5px 0px";
    $settings["title_padding"] = "10px 10px 0px 10px";
    $settings["description_color"] = "DEDEDE";
    $settings["description_size"] = "13px";
    $settings["description_margin"] = "0px 0px 0px 0px";
    $settings["description_padding"] = "10px 10px 10px 10px";
    $settings["description_word_count"] = "40";
    $settings["content_background_color"] = "4D4D4D";
    $settings["content_opacity"] = "0.8";
    $settings["content_position"] = "bottom";
    $settings["content_width"] = "287px";

    //Image
    $settings["image_as_background"] = true;
    $settings["image_width"] = "300px";
    $settings["image_height"] = "350px";
    $settings["image_margin"] = "0px 0px 0px 0px";
    $settings["image_padding"] = "0px 0px 0px 0px";
    $settings["image_border_style"] = "none";
    $settings["image_border_width"] = "1px";
    $settings["image_border_color"] = "FFFFFF";
    $settings["image_position"] = "top left";

    //Navigation
    $settings["display_navigation"] = false;
    $settings["navigation_foreground_color"] = "FFFFFF";
    $settings["navigation_background_color"] = "333333";
    $settings["navigation_size"] = "14px";

    //Pager
    $settings["display_pager"] = true;
    $settings["pager_position"] = "bottom";
    $settings["pager_align"] = "right";
    $settings["pager_color"] = "FFFFFF";
    $settings["pager_background_color"] = "000000";
    $settings["pager_active_color"] = "FFFFFF";
    $settings["pager_active_background_color"] = "4A4A4A";
    $settings["pager_margin"] = "0px 0px 0px 0px";
    $settings["pager_padding"] = "5px 8px 5px 8px";
    $settings["pager_border_style"] = "none";
    $settings["pager_border_width"] = "1px";
    $settings["pager_border_color"] = "FFFFFF";
    $settings["pager_size"] = "14px";
    $settings["pager_bar_background_color"] = "333333";
    $settings["pager_bar_margin"] = "0px 0px 0px 0px";
    $settings["pager_bar_padding"] = "0px 0px 0px 0px";
    $settings["pager_bar_border_style"] = "none";
    $settings["pager_bar_border_width"] = "1px";
    $settings["pager_bar_border_color"] = "FFFFFF";

    //Effect
    $settings["effect_name"] = "scrollHorz";
    $settings["transition_speed"] = "5000";
    $settings["effect_speed"] = "1000";
    $settings["hover_pause"] = true;

    return $settings;
}

function animated_blocks_print_block($id, $position)
{
    $block_data = Jaris\Blocks::get($id, $position);

    $slides = unserialize($block_data["content"]);

    if (is_array($slides)) {
        $slides = Jaris\Data::sort($slides, "order");
    } else {
        $slides = [];
    }

    $settings = animated_blocks_get_settings($block_data);
    $animated_block_id = "animated-block-$position-$id";
    $animated_block_id_container = "animated-block-container-$position-$id";

    Jaris\View::addStyle(
        Jaris\Modules::getPageUri(
            "animated-blocks/style",
            "animated_blocks"
        ),
        ["id" => $id, "position" => $position]
    );

    Jaris\View::addScript(
        Jaris\Modules::getPageUri(
            "animated-blocks/script",
            "animated_blocks"
        ),
        ["id" => $id, "position" => $position]
    );

    print Jaris\System::evalPHP($block_data["pre_content"]);

    if (is_array($slides)) {
        //Display pager on top
        if ($settings["display_pager"] && $settings["pager_position"] == "top") {
            print "<div id=\"{$animated_block_id}-pager\"></div>";
        }

        print "<div id=\"$animated_block_id_container\">";
        print "<div id=\"$animated_block_id\">";

        foreach ($slides as $slide) {
            $is_article = false;
            $is_plain_image = false;

            $image_url = "";
            $title = "";
            $description = "";

            $uri_parts = explode("/", $slide["uri"]);
            if (trim($uri_parts[0]) == "image" || $slide["type"] == "image") {
                if ($slide["type"] == "image") {
                    $image_url = Jaris\Files::get(
                        $slide["image"],
                        "animated_blocks"
                    );

                    $is_plain_image = true;
                } else {
                    $image_url = $slide["uri"];
                }

                $title = trim($slide["title"]);
                $description = Jaris\System::evalPHP($slide["description"]);
            } elseif ("" . strpos($uri_parts[0], "http:") . "" != "") {
                $image_url = $slide["uri"];
                $title = trim($slide["title"]);
                $description = Jaris\System::evalPHP($slide["description"]);
            } elseif (file_exists(Jaris\Pages::getPath($slide["uri"]))) {
                $is_article = true;
                if ($settings["auto_info"]) {
                    $data = Jaris\Pages::get(
                        $slide["uri"],
                        Jaris\Language::getCurrent()
                    );

                    $title = $data["title"];
                    $description = Jaris\Util::contentPreview(
                        Jaris\InputFormats::filter(
                            $data["content"],
                            $data["input_format"]
                        ),
                        intval($settings["description_word_count"]),
                        true
                    );
                } else {
                    $title = trim($slide["title"]);
                    $description = $description = Jaris\System::evalPHP(
                        $slide["description"]
                    );
                }

                $image_list = Jaris\Pages\Images::getList($slide["uri"]);
                $image_list = Jaris\Data::sort($image_list, "order");

                foreach ($image_list as $id => $data) {
                    $image_url = "image/" . $slide["uri"] . "/$id";
                    break;
                }
            } else {
                $is_plain_image = true;

                $image_url = $slide["uri"];
                $title = trim($slide["title"]);
                $description = Jaris\System::evalPHP($slide["description"]);
            }

            $width = $settings["image_as_background"] ?
                str_replace(["auto", "px"], "", $settings["width"])
                :
                str_replace(["auto", "px"], "", $settings["image_width"])
            ;

            $height = $settings["image_as_background"] ?
                str_replace(["auto", "px"], "", $settings["height"])
                :
                str_replace(["auto", "px"], "", $settings["image_height"])
            ;

            $image_url = $is_plain_image ?
                Jaris\Uri::url($image_url)
                :
                Jaris\Uri::url($image_url, ["w" => $width, "h" => $height])
            ;

            if ($settings["image_as_background"]) {
                print "<div class=\"animated-block-slide\" style=\"background: transparent url(" . $image_url . ") no-repeat left top\">";
            } else {
                print "<div class=\"animated-block-slide\">";
            }

            if ($slide["link"]) {
                print '<a class="animated-block-link" href="'.Jaris\Uri::url($slide["link"]).'">';
            }

            if ($title != "" || $description != "") {
                print "<div class=\"animated-block-content\">";
                print "</div>";

                print "<div class=\"animated-block-content-container\">";
                print "<div class=\"animated-block-title\">";

                if ($is_article) {
                    print "<a href=\"" . Jaris\Uri::url($slide["uri"]) . "\">";
                }

                print $title;

                if ($is_article) {
                    print "</a>";
                }

                print "</div>";

                print "<div class=\"animated-block-description\">";
                print $description;
                print "</div>";
                print "</div>";
            }

            if (!$settings["image_as_background"]) {
                print "<div class=\"animated-block-image\">";
                if ($is_article) {
                    print "<a href=\"" . Jaris\Uri::url($slide["uri"]) . "\">";
                }
                print "<img style=\"width: {$width}px; height: {$height}px\" src=\"" . $image_url . "\" />";
                if ($is_article) {
                    print "</a>";
                }
                print "</div>";
            }

            if ($slide["link"]) {
                print '</a>';
            }

            print "</div>";
        }

        print "</div>";

        //Next and previous buttons
        if ($settings["display_navigation"]) {
            print "
            <a id=\"{$animated_block_id}-prev\" title=\"" . t("Previous") . "\">
                <div>&lt;</div>
            </a>
            <a id=\"{$animated_block_id}-next\" title=\"" . t("Next") . "\">
                <div>&gt;</div>
            </a>";
        }

        print "</div>";

        //Display pager on bottom
        if ($settings["display_pager"] && $settings["pager_position"] == "bottom") {
            print "<div id=\"{$animated_block_id}-pager\"></div>";
        }
    }

    print Jaris\System::evalPHP($block_data["sub_content"]);
}
