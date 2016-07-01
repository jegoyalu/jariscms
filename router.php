<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * File used to test jariscms with php built-in webserver as follows:
 * php -S 0.0.0.0:8080 router.php
 */

if(php_sapi_name() !== 'cli-server')
    exit;

if(isset($_SERVER["PATH_INFO"]))
{
    $_REQUEST["p"] = ltrim($_SERVER["PATH_INFO"], "/");
}

// Required for some paths not been set like {image,file}/uri/name
// in php version 7
if(isset($_SERVER["PHP_SELF"]) && !isset($_REQUEST["p"]))
{
    $path = ltrim($_SERVER["PHP_SELF"], "/");

    if(substr($path, -4) != ".php")
    {
        $_REQUEST["p"] = $path;

        // Unset this because user was logged out when loading images because
        // this variable was set to the image path: image/uri/imagename
        // ending in index.php/image/uri/imagename which caused
        // the $base_url to be set improperly.
        $_SERVER["SCRIPT_NAME"] = "";
    }

    if(
        isset($_REQUEST["p"]) &&
        file_exists($_REQUEST["p"]) &&
        !is_dir($_REQUEST["p"])
    )
    {
        return false;
    }
}

if(
    file_exists(ltrim($_SERVER["REQUEST_URI"], "/")) &&
    !is_dir(ltrim($_SERVER["REQUEST_URI"], "/"))
)
{
    if(substr($_SERVER["SCRIPT_NAME"], -4) == ".php")
    {
        include(ltrim($_SERVER["SCRIPT_NAME"], "/"));

        exit;
    }

    return false;
}
elseif(substr($_SERVER["SCRIPT_NAME"], -4) == ".php")
{
    if(file_exists(ltrim($_SERVER["SCRIPT_NAME"], "/")))
    {
        include(ltrim($_SERVER["SCRIPT_NAME"], "/"));
    }
    else
    {
        include("index.php");
    }
}
else
{
    include("index.php");
}
