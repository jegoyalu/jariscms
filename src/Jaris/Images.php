<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to get images from pages, since there location is not accessed
 * directly as resize them, etc.
 */
class Images
{

/**
 * Prepares and print an image file to a browser.
 *
 * @param string $image_path The full path to the image to display.
 * @original show_image
 */
static function show($image_path)
{
    //Make sure image exists before trying to output it.
    if($image_path == "")
    {
        return;
    }

    $uri = str_replace("image/", "", Uri::get());
    $uri = explode("/", $uri);
    unset($uri[count($uri) - 1]);
    $uri = implode("/", $uri);

    $page_data = Pages::get($uri);

    if(Pages::userHasAccess($page_data))
    {
        // Do not lock subsequent requests.
        Session::close();

        //Try to get image from cache
        $cache_name = self::getCacheName($image_path);
        if(
            file_exists($cache_name) &&
            !Settings::get("image_static_serving", "main")
        )
        {
            self::printCached($cache_name);
        }
        //else process image and store it to cache
        else
        {
            $image = self::get(
                $image_path,
                intval($_REQUEST['w']),
                intval($_REQUEST['h']),
                boolval($_REQUEST["ar"]),
                $_REQUEST["bg"]
            );

            self::printIt($image);
        }
    }
    else
    {
        Authentication::protectedPage();
    }
}

/**
 * Gets the cache path of an image.
 *
 * @param string $original_image The path of the original image.
 *
 * @return string Path to image cache or original if not found.
 * @original get_image_cache_name
 */
static function getCacheName($original_image = null)
{
    $page = Uri::get();

    $size = "";

    if(isset($_REQUEST["w"]))
    {
        $size .= "-" . $_REQUEST["w"];
    }

    if(isset($_REQUEST["h"]))
    {
        $size .= "x" . $_REQUEST["h"];
    }

    if(isset($_REQUEST["ar"]) && $_REQUEST["ar"] == "1")
    {
        $size .= "-ar";
    }

    if(isset($_REQUEST["bg"]) && $_REQUEST["bg"] != "")
    {
        $size .= "-" . $_REQUEST["bg"];
    }

    //Return resized image path in cache
    if($size)
    {
        $image_page_uri = str_replace("/", "-", $page);

        $cache_path = Site::dataDir() . "image_cache/{$image_page_uri}{$size}";
    }

    //Returns original image path
    else
    {
        $cache_path = $original_image;
    }

    return $cache_path;
}

/**
 * Gets the static path of an image.
 *
 * @param string $image_url The url of non static image version.
 * @param bool $full_url Control if returned value is a full url.
 *
 * @return string Url to static image or empty string if not found.
 * @original get_image_static_name
 */
static function getStaticName($image_url, $full_url=true)
{
    //Default image extension
    $image_extension = "png";

    $size = "";

    $matches = array();

    if(preg_match("/w=([0-9]{1,5})/", $image_url, $matches))
    {
        $size = "-" . $matches[1];
    }

    if(preg_match("/h=([0-9]{1,5})/", $image_url, $matches))
    {
        $size .= "x" . $matches[1];
    }

    if("".strpos($image_url, "ar=1")."" != "")
    {
        $size .= "-ar";
    }

    if(preg_match("/bg=([0-9A-Fa-f]{6})/", $image_url, $matches))
    {
        $size .= "-" . $matches[1];
    }

    $url_elements = parse_url($image_url);

    $image_uri = str_replace(
        array(
            Site::$base_url . "/image/",
            "/"
        ),
        array("", "-"),
        $image_url
    );

    if(isset($url_elements["query"]))
    {
        $image_uri = str_replace(
            "?" . $url_elements["query"],
            "",
            $image_uri
        );
    }

    $image_uri = rtrim($image_uri, "?");

    $uri_parts = array();
    $parts_count = 0;

    if(($parts_count = count($uri_parts = explode(".", $image_uri))) <= 1)
    {
        $image_uri .= $size . "." . $image_extension;
    }
    else
    {
        $extension = strtolower($uri_parts[$parts_count-1]);

        if(
            $extension != "jpg" &&
            $extension != "jpeg" &&
            $extension != "gif" &&
            $extension != "png"
        )
        {
            $extension = $image_extension;
        }

        unset($uri_parts[$parts_count-1]);

        $image_uri = implode(".", $uri_parts) . $size . "." . $extension;
    }

    if(!$full_url)
        return Files::getDir("static_image") . $image_uri;

    if(!file_exists(getcwd() . "/" . Files::getDir("static_image") . $image_uri))
        return "";

    return Site::$base_url . "/" . Files::getDir("static_image") . $image_uri;
}

/**
 * Gets an imaga binary data to work with it.
 *
 * @param string $path The path of the image to work on.
 * @param int $width The width in which the image will be displayed.
 * @param int $height The height in which the image will be displayed.
 * @param bool $aspect_ratio Flag to keep the original aspect ratio.
 * @param string $background_color Hex color value for the background of the image.
 *
 * @return array Image mime type and image in form of binary data.
 * Example: array("mime"=>"string", "path"=>"string", "binary_data"=>"bytes")
 * @original get_image
 */
static function get($path, $width, $height = 0, $aspect_ratio = false, $background_color = "ffffff")
{
    $image_info = getimagesize($path);

    switch($image_info['mime'])
    {
        case "image/jpeg":
            $original_image = imagecreatefromjpeg($path);
            break;
        case "image/png":
            $original_image = imagecreatefrompng($path);
            break;
        case "image/gif":
            $original_image = imagecreatefromgif($path);
            break;
    }

    $image_data["mime"] = $image_info["mime"];
    $image_data["path"] = $path;

    if($width > 0 && $height > 0 && $aspect_ratio && $background_color)
    {
        $image_data["binary_data"] = imagecreatetruecolor($width, $height);

        $rgb_array = self::hexToRGB($background_color);

        //If background is white make it transparent by default.
        if($rgb_array["r"] == 255 && $rgb_array["g"] == 255 && $rgb_array["b"] == 255)
        {
            self::makeTransparent($image_data["binary_data"], $image_data["mime"]);
        }
        else
        {
            $bg_color = imagecolorallocate(
                $image_data["binary_data"],
                $rgb_array["r"],
                $rgb_array["g"],
                $rgb_array["b"]
            );

            imagefill($image_data["binary_data"], 0, 0, $bg_color);
        }

        $current_width = $image_info[0];
        $current_height = $image_info[1];

        //Calculate size to keep aspect ratio
        $aspect_ratio = $current_width / $current_height;
        $new_width = $height * $aspect_ratio;
        $new_height = $height;

        //Coordinates to center the image
        $x = ($width - $new_width) / 2;
        $y = 0;

        //Scale by height if width is greater than the wanted result
        if($new_width > $width)
        {
            $aspect_ratio = $current_height / $current_width;
            $new_width = $width;
            $new_height = $width * $aspect_ratio;

            //Coordinates to center the image
            $x = 0;
            $y = ($height - $new_height) / 2;
        }

        imagecopyresampled(
            $image_data["binary_data"],
            $original_image, $x, $y, 0, 0,
            $new_width, $new_height,
            $current_width, $current_height
        );
    }
    else if($width > 0 && $height > 0 && $aspect_ratio)
    {
        $image_data["binary_data"] = imagecreatetruecolor($width, $height);
        self::makeTransparent($image_data["binary_data"], $image_data["mime"]);

        $current_width = $image_info[0];
        $current_height = $image_info[1];

        //Calculate size to keep aspect ratio
        $aspect_ratio = $current_width / $current_height;
        $new_width = $height * $aspect_ratio;
        $new_height = $height;

        //Coordinates to center the image
        $x = ($width - $new_width) / 2;
        $y = 0;

        //Scale by height if width is greater than the wanted result
        if($new_width > $width)
        {
            $aspect_ratio = $current_height / $current_width;
            $new_width = $width;
            $new_height = $width * $aspect_ratio;

            //Coordinates to center the image
            $x = 0;
            $y = ($height - $new_height) / 2;
        }

        imagecopyresampled(
            $image_data["binary_data"],
            $original_image, $x, $y, 0, 0,
            $new_width, $new_height,
            $current_width, $current_height
        );
    }
    else if($width > 0 && $height > 0)
    {
        $image_data["binary_data"] = imagecreatetruecolor($width, $height);
        self::makeTransparent($image_data["binary_data"], $image_data["mime"]);

        $cw = $image_info[0];
        $ch = $image_info[1];

        $arw = $cw/$ch;
        $arh = $ch/$cw;

        $nh = $height;
        $nw = intval($nh * $arw);

        if($nw < $width)
        {
            $nw = $width;
            $nh = intval($nw * $arh);
        }

        $sx = 0;
        $sy = 0;
        $dx = 0;
        $dy = 0;

        if($nw < $width)
        {
            $sx = ($nw / 2) - ($width / 2);
        }
        else
        {
            $dx = ($width / 2) - ($nw / 2);
        }

        if($nh < $height)
        {
            $sy = ($nh / 2) - ($height / 2);
        }
        else
        {
            $dy = ($height / 2) - ($nh / 2);
        }

        imagecopyresampled(
            $image_data["binary_data"],
            $original_image, $dx, $dy, $sx, $sy,
            $nw, $nh,
            $cw, $ch
        );
    }
    else if($width > 0)
    {
        $new_height = ($width / $image_info[0]) * $image_info[1];
        $image_data["binary_data"] = imagecreatetruecolor($width, $new_height);
        self::makeTransparent($image_data["binary_data"], $image_data["mime"]);

        imagecopyresampled(
            $image_data["binary_data"],
            $original_image, 0, 0, 0, 0,
            $width, $new_height,
            $image_info[0], $image_info[1]
        );
    }
    else
    {
        $image_data["binary_data"] = imagecreatetruecolor($image_info[0], $image_info[1]);
        self::makeTransparent($image_data["binary_data"], $image_data["mime"]);

        imagecopyresampled(
            $image_data["binary_data"],
            $original_image, 0, 0, 0, 0,
            $image_info[0], $image_info[1],
            $image_info[0], $image_info[1]
        );
    }

    imagedestroy($original_image);

    return $image_data;
}

/**
 * Resizes and compress a given image.
 *
 * @param string $path The path of the image to resize.
 * @param int $width The width in which the image will be displayed.
 * @param int $height The height in which the image will be displayed.
 * @param bool $aspect_ratio Flag to keep the original aspect ratio.
 * @param string $background_color Hex color value for the background of the image.
 *
 * @return bool True on success or false on failure.
 * @original image_resize
 */
static function resize(
    $path, $width, $height = 0, $aspect_ratio = false, $background_color = "ffffff"
)
{
    $image_quality = Settings::get("image_compression_quality", "main");

    $image_info = getimagesize($path);

    $return = false;

    if($image_info[0] > $width)
    {
        $image = self::get(
            $path, $width, $height, $aspect_ratio, $background_color
        );

        switch($image_info["mime"])
        {
            case "image/jpeg":
                $return = imagejpeg(
                    $image["binary_data"],
                    $path,
                    $image_quality ? intval($image_quality) : 100
                );
                break;
            case "image/png":
                $return = imagepng(
                    $image["binary_data"],
                    $path
                );
                break;
            case "image/gif":
                $return = imagegif(
                    $image["binary_data"],
                    $path
                );
                break;
        }
    }

    return $return;
}

/**
 * Makes an image resource transparent if gif or png.
 *
 * @param resource &$image Reference to the resource image.
 * @param string $mime To check if png of gif.
 * @original make_image_transparent
 */
static function makeTransparent(&$image, $mime)
{
    switch($mime)
    {
        case "image/png":
        case "image/gif":

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
            imagefill($image, 0, 0, $transparent);

            return;
    }

    $bg_color = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bg_color);
}

/**
 * Sends an image to the browser.
 *
 * @param array $image Array returned from get_image() function to display it.
 * @original print_image
 */
static function printIt($image)
{
    $page = Uri::get();

    //First reset headers
    header("Pragma: ");         //This one is set to no-cache so we disable it
    header("Cache-Control: ");  //also set to no cache
    header("Last-Modified: ");  //We try to reset to only send one date
    header("Expires: ");        //We try to reset to only send one expiration date
    header("X-Powered-By: ");   //We remove the php powered by since we want to pass as normal file

    header("Etag: \"" . md5_file($image["path"]) . "\"");
    header("Cache-Control: max-age=1209600");
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($image["path"])) . 'GMT');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)) . 'GMT');
    header("Accept-Ranges: bytes");
    header("Content-Lenght: " . filesize($image["path"]));

    $image_cache_name = self::getCacheName();

    if(Settings::get("image_static_serving", "main"))
    {
        if(!is_dir(Files::getDir("static_image")))
        {
            FileSystem::makeDir(Files::getDir("static_image"), 0755, true);
        }

        $image_url = Uri::url(
            $page,
            array(
                "w"=>$_REQUEST["w"],
                "h"=>$_REQUEST["h"],
                "ar"=>$_REQUEST["ar"],
                "bg"=>$_REQUEST["bg"],
            )
        );

        $image_cache_name = self::getStaticName(
            $image_url,
            false
        );
    }

    switch($image["mime"])
    {
        case "image/jpeg":
            header("Content-Type: image/jpeg");

            $image_quality = Settings::get("image_compression_quality", "main");

            if($image_quality == "")
            {
                $image_quality = 100;
            }

            //Save to image cache
            imagejpeg(
                $image["binary_data"],
                $image_cache_name,
                $image_quality
            );

            //Output image
            imagejpeg(
                $image["binary_data"],
                null,
                $image_quality
            );

            break;

        case "image/png":
            header("Content-Type: image/png");

            //Save to image cache
            imagepng($image["binary_data"], $image_cache_name);

            //Output image
            imagepng($image["binary_data"]);
            break;

        case "image/gif":
            header("Content-Type: image/gif");

            //Save to image cache
            imagegif($image["binary_data"], $image_cache_name);

            //Output image
            imagegif($image["binary_data"]);
            break;
    }

    if(file_exists($image_cache_name))
    {
        chmod($image_cache_name, 0755);
    }

    imagedestroy($image["binary_data"]);
    exit;
}

/**
 * Prints to broswer or any http client an image stored on the cache.
 *
 * @param string $path The current file path of the image to print.
 * @original print_cache_image
 */
static function printCached($path)
{
    FileSystem::printFile($path, "");
}

/**
 * Prints the picture of a user.
 *
 * @param string $page the symbolic path where the picture resides.
 * @original print_user_pic
 */
static function printUserPic($page)
{
    $picture_data = Uri::getUserPicturePath($page);

    if($picture_data["path"] == false)
    {
        return;
    }

    // Do not lock subsequent requests.
    Session::close();

    $image = null;

    if($size = Settings::get("user_picture_size", "main"))
    {
        $size = strtolower($size);
        $size = explode("x", $size);

        $image = self::get($picture_data["path"], $size[0], $size[1]);
    }
    else
    {
        $image = self::get($picture_data["path"], 150, 150);
    }

    self::printIt($image);
}

/**
 * Converts a string hex like ffffff to rgb format for use on image functions.
 *
 * @param string $value The string to convert to rgb.
 *
 * @return array An array in the format $rgb["r"], $rgb["g"], $rgb["b"]
 * @original htmlhex_to_rgb
 */
static function hexToRGB($value)
{
    $rgb["r"] = hexdec($value{0} . $value{1});
    $rgb["g"] = hexdec($value{2} . $value{3});
    $rgb["b"] = hexdec($value{4} . $value{5});

    return $rgb;
}

/**
 * Removes all the content of the image_cache directory.
 *
 * @return bool true on success or false on fail.
 * @original clear_image_cache
 */
static function clearCache()
{
    $image_cache_directory = Site::dataDir() . "image_cache";

    if(is_dir(Files::getDir("static_image")))
        FileSystem::recursiveRemoveDir(
            Files::getDir("static_image"),
            true
        );

    return FileSystem::recursiveRemoveDir($image_cache_directory, true);
}

/**
 * Checks if a given file is a valid image of types, jpg, png or gif.
 *
 * @param string $path
 *
 * @return bool
 * @original image_is_valid
 */
static function isValid($path)
{
    $image_info = getimagesize($path);

    switch($image_info["mime"])
    {
        case "image/jpeg":
        case "image/png":
        case "image/gif":
            return true;
    }

    return false;
}

}