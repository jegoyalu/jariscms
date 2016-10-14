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
        $compressed = Jaris\Uri::url(
            Jaris\Modules::directory("minify") . "min/index.php?f="
        );
        $cache_file = Jaris\Files::getDir("minify");
        $cache_files = "";

        foreach($styles as $url)
        {
            $file = str_replace($main_url, "", $url);

            if(file_exists($file))
            {
                $compressed .= $file . ",";
                $cache_files .= $file . "-";
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

                $compressed .= $output . ",";
                $cache_files .= $output . "-";
            }
        }

        $compressed = trim($compressed, ",");
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
                    file_get_contents($compressed)
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
        $compressed = Jaris\Uri::url(
            Jaris\Modules::directory("minify")
                . "min/index.php?f="
        );
        $cache_file = Jaris\Files::getDir("minify");
        $cache_files = "";

        foreach($scripts as $url)
        {
            $file = str_replace($main_url, "", $url);

            if(
                file_exists($file) &&
                strpos($url, "jscolor.js") === false &&
                strpos($url, "ckeditor.js") === false
            )
            {
                $compressed .= $file . ",";
                $cache_files .= $file . "-";
            }
            elseif(
                strpos($url, "jscolor.js") === false &&
                strpos($url, "ckeditor.js") === false &&
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

                $compressed .= $output . ",";
                $cache_files .= $output . "-";
            }
            else //Add scripts with arguments normally
            {
                $last_scripts_codes .= "<script type=\"text/javascript\" src=\"$url\"></script>\n";
            }
        }

        $compressed = trim($compressed, ",");
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
                    file_get_contents($compressed)
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
                "arguments" => null
            );
        }
    }
);