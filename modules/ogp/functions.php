<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 */

function ogp_meta_tags_generate($uri, $page_data)
{
    $theme = Jaris\Site::$theme;

    $meta_tags = "";

    // Add url and title tags
    $url = str_replace("https://", "http://", Jaris\Uri::url($uri));

    $title = "";
    $description = "";

    if($page_data["is_system"])
    {
        $title .= trim(
            Jaris\Util::stripHTMLTags(
                Jaris\System::evalPHP($page_data["title"])
            )
        );
    }
    else
    {
        $title .= isset($page_data["meta_title"]) && trim($page_data["meta_title"]) != "" ?
            trim(str_replace("\n", " ", Jaris\Util::stripHTMLTags($page_data["meta_title"])))
            :
            trim(str_replace("\n", " ", Jaris\Util::stripHTMLTags($page_data["title"])))
        ;

        if(trim($page_data["description"]) != "")
        {
            $description .= trim(
                str_replace(
                    array("\n", '"'),
                    array(" ", "'"),
                    $page_data["description"])
            );
        }
        else
        {
            $description .= trim(
                str_replace(
                    array("\n", '"'),
                    array(" ", "'"),
                    Jaris\Util::contentPreview($page_data["content"], 40)
                )
            );
        }
    }

    $site_title = t(Jaris\Settings::get("title", "main"));

    $meta_tags .= '<meta property="og:site_name" content="'.$site_title.'" />' . "\n"
        . '<meta property="og:title" content="'.$title.'" />' . "\n"
        . '<meta property="og:type" content="article" />' . "\n"
        . '<meta property="og:url" content="'.$url.'" />' . "\n"
        //. '<meta property="og:locale" content="'.Jaris\Language::getCurrent().'" />' . "\n"
    ;

    if($description != "")
    {
        $meta_tags .= '<meta property="og:description" content="'.$description.'" />'
            . "\n"
        ;
    }

    // Add image meta tag if at least 1 image is available
    $images = Jaris\Pages\Images::getList($uri);

    if($images)
    {
        $generate_static = Jaris\Settings::get(
            "image_static_serving", "main"
        );

        $first_image = true;

        foreach($images as $image)
        {
            $arguments = null;

            if($first_image)
            {
                $arguments = array(
                    "w"=>200,
                    "h"=>200,
                    "ar"=>1,
                    "bg"=>"ffffff"
                );

                $image_static = Jaris\Images::getStaticName(
                    Jaris\Uri::url(
                        "image/".$uri."/".$image["name"],
                        $arguments
                    )
                );

                // If a 200x200 static image doesn't exists already
                // we proceed to create one, we do this check
                // to speed up the tags generation.
                if($image_static == "")
                {
                    ogp_image_create_cache(
                        "image/".$uri."/".$image["name"],
                        200,
                        200,
                        true
                    );
                }
            }
            elseif($generate_static)
            {
                $image_static = Jaris\Images::getStaticName(
                    Jaris\Uri::url(
                        "image/".$uri."/".$image["name"]
                    )
                );

                // If a 200x200 static image doesn't exists already
                // we proceed to create one, we do this check
                // to speed up the tags generation.
                if($image_static == "")
                {
                    ogp_image_create_cache(
                        "image/".$uri."/".$image["name"]
                    );
                }
            }

            $image_url = Jaris\Uri::url(
                "image/".$uri."/".$image["name"],
                $arguments
            );

            if($first_image)
            {
                $image_static = Jaris\Images::getStaticName($image_url);

                if($image_static != "")
                {
                    $image_url = $image_static;
                }

                $first_image = false;
            }

            $meta_tags .= '<meta property="og:image" content="'.$image_url.'" />'
                . "\n"
            ;
        }
    }
    else
    {
        $ogp_settings = Jaris\Settings::getAll("ogp");

        if(
            isset($ogp_settings["current_image"]) &&
            trim($ogp_settings["current_image"]) != ""
        )
        {
            $image = Jaris\Uri::url(
                Jaris\Files::get(
                    $ogp_settings["current_image"],
                    "ogp"
                )
            );

            $meta_tags .= '<meta property="og:image" content="'.$image.'" />'
                . "\n"
            ;
        }
        else
        {
            $theme_path = Jaris\Themes::directory($theme);

            if(file_exists($theme_path . "images/logo.png"))
            {
                $image = Jaris\Uri::url(
                    $theme_path . "images/logo.png"
                );

                $meta_tags .= '<meta property="og:image" content="'.$image.'" />'
                    . "\n"
                ;
            }
            elseif(file_exists($theme_path . "images/logo.jpg"))
            {
                $image = Jaris\Uri::url(
                    $theme_path . "images/logo.jpg"
                );

                $meta_tags .= '<meta property="og:image" content="'.$image.'" />'
                    . "\n"
                ;
            }
        }
    }

    return $meta_tags;
}

function ogp_image_create_cache($image_uri, $width=0, $height=0, $ar=false, $bg="ffffff")
{
    $image_url = "";

    // Generate image url
    if($width > 0)
    {
        $image_url .= Jaris\Uri::url(
            $image_uri,
            array(
                "w"=>$width,
                "h"=>$height,
                "ar"=>$ar,
                "bg"=>$bg,
            )
        );
    }
    else
    {
        $image_url .= Jaris\Uri::url(
            $image_uri
        );
    }

    $image_cache_name = Jaris\Images::getStaticName(
        $image_url,
        false
    );

    if(file_exists($image_cache_name))
    {
        return;
    }

    // Load image data
    $image = Jaris\Images::get(Jaris\Uri::getImagePath($image_uri), $width, $height, $ar, $bg);

    // Preparations to statically store the image
    if(!is_dir(Jaris\Files::getDir("static_image")))
    {
        Jaris\FileSystem::makeDir(Jaris\Files::getDir("static_image"), 0755, true);
    }

    // Store the image statically
    switch($image["mime"])
    {
        case "image/jpeg":
            $image_quality = Jaris\Settings::get("image_compression_quality", "main");

            if($image_quality == "" || $image_quality == null)
            {
                $image_quality = 100;
            }

            //Save to image cache
            imagejpeg(
                $image["binary_data"],
                $image_cache_name,
                $image_quality
            );

            break;

        case "image/png":
            //Save to image cache
            imagepng($image["binary_data"], $image_cache_name);

            break;

        case "image/gif":
            //Save to image cache
            imagegif($image["binary_data"], $image_cache_name);

            break;
    }

    chmod($image_cache_name, 0755);

    imagedestroy($image["binary_data"]);
}

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_GET_META_TAGS,
    function(&$meta_tags)
    {
        $page_data = Jaris\Site::$page_data;

        $meta_tags .= ogp_meta_tags_generate(Jaris\Uri::get(), $page_data[0]);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_SAVE_PAGE_TO_CACHE,
    function(&$uri, &$page_data, &$content)
    {
        $base_url = Jaris\Site::$base_url;

        // Re-establish the full url of each element on the generated
        // open graph data which is made relative
        // by the system save_page_to_cache() function.

        $base_url_path = str_replace(
            array("https://", "http://" . $_SERVER["HTTP_HOST"]),
            array("http://", ""),
            $base_url
        );

        $meta_tags = ogp_meta_tags_generate($uri, $page_data);

        $meta_tags_relative = str_replace(
            $base_url, $base_url_path, $meta_tags
        );

        $content = str_replace(
            $meta_tags_relative, $meta_tags, $content
        );
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Open Graph")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/ogp",
                    "ogp"
                ),
                "arguments" => null
            );
        }
    }
);