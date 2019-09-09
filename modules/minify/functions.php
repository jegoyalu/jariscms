<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file.
 */
use Jaris\Signals;


Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_STYLES,
    function(&$styles, &$styles_code)
    {
        $base_url = Jaris\Site::$base_url;

        $base_url_path = str_replace(
            array("https://", "http://" . $_SERVER["HTTP_HOST"]),
            array("http://", ""),
            $base_url
        );

        $main_url = Jaris\Uri::url("");

        $base_url_parse = parse_url(rtrim($main_url, "/"));

        $styles_array = explode("\n", $styles_code);
        $styles_code = "";
        $last_styles_code = "";
        $cache_file = Jaris\Files::getDir("minify");
        $cache_files = "";
        $files = array();

        foreach($styles as $url)
        {
            // Strip theme version information
            $url = current(explode("?v=", $url));

            $file = str_replace($main_url, "", $url);

            if(file_exists($file))
            {
                $cache_files .= $file . "-";
                $files[] = $file;
            }
            else
            {
                $url_parse = parse_url($url);

                if(isset($base_url_parse["path"]))
                {
                    $url_parse["path"] = trim(
                        str_replace(
                            $base_url_parse["path"],
                            "",
                            $url_parse["path"]
                        ),
                        "/"
                    );
                }

                $page_path = Jaris\Pages::getPath($url_parse["path"]) . "/data.php";

                $output = Jaris\Files::getDir("minify")
                    . Jaris\Uri::fromText($file) . "_" . Jaris\Language::getCurrent()
                    . "_" . Jaris\Authentication::currentUserGroup() . ".css"
                ;

                if(file_exists($page_path) && !file_exists($output))
                {
                    $request_copy = array();

                    if($url_parse["query"])
                    {
                        $request_copy = $_REQUEST;

                        unset($_REQUEST);

                        $parameters = explode("&", $url_parse["query"]);

                        foreach($parameters as $parameter_data)
                        {
                            $parameter = explode("=", $parameter_data);

                            $_REQUEST[$parameter[0]] = $parameter[1];
                        }
                    }

                    $page_data = Jaris\Pages::get(
                        $url_parse["path"],
                        Jaris\Language::getCurrent()
                    );

                    file_put_contents(
                        $output,
                        Jaris\System::evalPHP($page_data["content"])
                    );

                    if($url_parse["query"])
                    {
                        $_REQUEST = $request_copy;
                    }
                }

                $files[] = $output;
                $cache_files .= $output . "-";
            }
        }

        $cache_file .= md5(
            $cache_files . Jaris\Authentication::currentUserGroup()
        ) . ".css";

        if(!file_exists($cache_file))
        {
            file_put_contents(
                $cache_file,

                //Don't store transport protocol on minified css.
                str_replace(
                    $base_url,
                    $base_url_path,
                    minify_js_or_css($files)
                )
            );

            chmod($cache_file, 0755);
        }

        $styles_code .= '<link href="'.Jaris\Uri::url($cache_file).'" '
            . 'rel="stylesheet" type="text/css" media="all" />'
            . "\n"
        ;

        $styles_code .= $last_styles_code;

        $i = count($styles);
        $count = count($styles_array);

        for($i; $i<$count; $i++)
        {
            $styles_code .= $styles_array[$i] . "\n";
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_SCRIPTS,
    function(&$scripts, &$scripts_code)
    {
        $base_url = Jaris\Site::$base_url;

        $base_url_path = str_replace(
            array("https://", "http://" . $_SERVER["HTTP_HOST"]),
            array("http://", ""),
            $base_url
        );

        $main_url = Jaris\Uri::url("");

        $base_url_parse = parse_url(rtrim($main_url, "/"));

        $scripts_array = explode("\n", $scripts_code);
        $scripts_code = "";
        $last_scripts_codes = "";
        $cache_file = Jaris\Files::getDir("minify");
        $cache_files = "";
        $files = array();

        foreach($scripts as $url)
        {
            // Strip theme version information
            $url = current(explode("?v=", $url));

            $file = str_replace($main_url, "", $url);

            if(
                file_exists($file) &&
                strpos($url, "jscolor.js") === false &&
                strpos($url, "ckeditor.js") === false &&
                strpos($url, "simplemde.min.js") === false
            )
            {
                $files[] = $file;
                $cache_files .= $file . "-";
            }
            elseif(
                strpos($url, "jscolor.js") === false &&
                strpos($url, "ckeditor.js") === false &&
                strpos($url, "simplemde.min.js") === false &&
                strpos($url, $main_url) !== false
            )
            {
                $url_parse = parse_url($url);

                if(isset($base_url_parse["path"]))
                {
                    $url_parse["path"] = trim(
                        str_replace(
                            $base_url_parse["path"],
                            "",
                            $url_parse["path"]
                        ),
                        "/"
                    );
                }

                $page_path = Jaris\Pages::getPath($url_parse["path"])
                    . "/data.php"
                ;

                $output = Jaris\Files::getDir("minify")
                    . Jaris\Uri::fromText($file) . "_" . Jaris\Language::getCurrent()
                    . "_" . Jaris\Authentication::currentUserGroup() . ".js"
                ;

                if(file_exists($page_path) && !file_exists($output))
                {
                    $request_copy = array();

                    if($url_parse["query"])
                    {
                        $request_copy = $_REQUEST;

                        unset($_REQUEST);

                        $parameters = explode("&", $url_parse["query"]);

                        foreach($parameters as $parameter_data)
                        {
                            $parameter = explode("=", $parameter_data);

                            $_REQUEST[$parameter[0]] = $parameter[1];
                        }
                    }

                    $page_data = Jaris\Pages::get(
                        $url_parse["path"],
                        Jaris\Language::getCurrent()
                    );

                    file_put_contents(
                        $output,
                        Jaris\System::evalPHP($page_data["content"])
                    );

                    if($url_parse["query"])
                    {
                        $_REQUEST = $request_copy;
                    }
                }

                $files[] = $output;
                $cache_files .= $output . "-";
            }
            else //Add scripts with arguments normally
            {
                $last_scripts_codes .= "<script type=\"text/javascript\" src=\"$url\"></script>\n";
            }
        }

        $cache_file .= md5(
            $cache_files . Jaris\Authentication::currentUserGroup()
        ) . ".js";

        if(!file_exists($cache_file))
        {
            file_put_contents(
                $cache_file,

                //Don't store transport protocol on minified css.
                str_replace(
                    $base_url,
                    $base_url_path,
                    minify_js_or_css($files)
                )
            );

            chmod($cache_file, 0755);
        }

        $scripts_code .= '<script type="text/javascript" src="'.Jaris\Uri::url($cache_file).'"></script>' . "\n";
        $scripts_code .= $last_scripts_codes;

        $i = count($scripts);
        $count = count($scripts_array);

        for($i; $i<$count; $i++)
        {
            $scripts_code .= $scripts_array[$i] . "\n";
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Clear Minify Cache")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/minify/clear-cache",
                    "minify"
                ),
                "arguments" => array()
            );
        }
    }
);

Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_SAVE_PAGE_TO_CACHE,
    function(&$uri, &$page_data, &$content)
    {
        $content = minify_html($content);
    },
    1000
);

function minify_html($html)
{
    static $is_loaded = false;

    if(!$is_loaded)
    {
        $path = Jaris\Modules::directory("minify");
        require_once $path . "tiny-html-minifier/tiny-html-minifier.php";
        $is_loaded = true;
    }

    return TinyMinify::html($html);
}

function minify_js_or_css($files)
{
    static $is_loaded = false;

    if(!$is_loaded)
    {
        $path = Jaris\Modules::directory("minify");
        require_once $path . "min/lib/Minify/Loader.php";
        Minify_Loader::register();

        $is_loaded = true;
    }

    Minify::$isDocRootSet = true;

    $options = array(
        "bubbleCssImports" => "",
        "maxAge" => 1800
    );

    return Minify::combine($files, $options);
}
