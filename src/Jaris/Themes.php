<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to manage themes.
 */
class Themes
{

/**
 * Receives parameters: $themes
 * @var string
 */
const SIGNAL_GET_ENABLED_THEMES = "hook_get_enabled_themes";

/**
 * Scans all the available themes on the themes folder.
 *
 * @return array Array with all the themes in the format:
 * themes[path] = array(name, description, version, author, email, website)
 * or themes[path] = null if no info file found.
 * @original get_themes
 */
static function getList()
{
    $themes = array();

    // Search for a site specific themes.
    $site_theme_dir = self::directory();

    if($site_theme_dir != "themes/")
    {
        $dir_handle = opendir($site_theme_dir);

        while(($file = readdir($dir_handle)) !== false)
        {
            // Delete previous theme array
            $theme = array();

            //just add directories
            if(
                strcmp($file, ".") != 0 &&
                strcmp($file, "..") != 0 &&
                is_dir($site_theme_dir . $file)
            )
            {
                $info_file = $site_theme_dir . $file . "/info.php";

                if(file_exists($info_file))
                {
                    include($info_file);
                    $themes[$file] = $theme;
                }
            }
        }

        closedir($dir_handle);
    }

    // Search for global themes.
    $theme_dir = "themes/";
    $dir_handle = opendir($theme_dir);

    while(($file = readdir($dir_handle)) !== false)
    {
        // Delete previous theme array
        $theme = array();

        //just add directories
        if(
            strcmp($file, ".") != 0 &&
            strcmp($file, "..") != 0 &&
            is_dir($theme_dir . $file)
        )
        {
            if(isset($themes[$file]))
                continue;

            $info_file = $theme_dir . $file . "/info.php";

            if(file_exists($info_file))
            {
                include($info_file);
                $themes[$file] = $theme;
            }
        }
    }

    closedir($dir_handle);

    return $themes;
}

/**
 * Gets a theme path with trailing slash included.
 * @param string $name
 * @return string Path to a theme.
 * @original theme_directory
 */
static function directory($name="")
{
    static $themes = array();

    if($name != "")
        $name .= "/";

    if(!isset($themes[$name]))
    {
        $site = Site::current();

        $path = "sites/" . $site;

        if(is_dir($path))
        {
            if(is_dir($path . "/themes/$name"))
            {
                $themes[$name] = $path . "/themes/$name";
            }
        }
        elseif($site != "default")
        {
            if(is_dir("sites/default/themes/$name"))
            {
                $themes[$name] = "sites/default/themes/$name";
            }
        }

        if(!isset($themes[$name]))
        {
            $themes[$name] = "themes/$name";
        }
    }

    return $themes[$name];
}

/**
 * Get path where themes should be uploaded with trailing slash.
 * @original get_themes_path
 */
static function getUploadPath()
{
    $path = "sites/" . Site::current();

    if(is_dir($path))
    {
        if(is_dir($path . "/themes/"))
        {
            return $path . "/themes/";
        }
    }

    return "sites/default/themes/";
}

/**
 * Gets the list of enabled themes.
 * @return array
 * @original get_enabled_themes
 */
static function getEnabled()
{
    $themes = unserialize(Settings::get("themes_enabled", "main"));

    Modules::hook("hook_get_enabled_themes", $themes);

    return $themes;
}

/**
 * Gets the info of a specific theme
 *
 * @param string $path The name of the theme folder inside
 * the themes main folder.
 *
 * @return array Theme information in the format:
 * info = array(name, description, version, author, email, website)
 * @original get_theme_info
 */
static function get($path)
{
    $theme_info = array();

    $info_file = self::directory($path) . "info.php";

    if(file_exists($info_file))
    {
        include($info_file);
        $theme_info = $theme;
    }

    return $theme_info;
}

/**
 * Dummy function that return the global $theme variable as it should be the
 * default one.
 * @original get_default_theme
 */
static function getDefault()
{
    return Site::$theme;
}

/**
 * @todo Check if the logged user has permissions to
 * choose the theme and change it.
 * @original get_user_theme
 */
static function getUserTheme()
{

}

}