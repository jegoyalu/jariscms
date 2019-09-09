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
 */
static function getList(): array
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
 *
 * @param string $name
 *
 * @return string Path to a theme.
 */
static function directory(string $name=""): string
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
 */
static function getUploadPath(): string
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
 *
 * @return array
 */
static function getEnabled(): array
{
    $themes = unserialize(Settings::get("themes_enabled", "main"));

    if(!is_array($themes))
    {
        $themes = array();
    }

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
 */
static function get(string $path): array
{
    $theme_info = array();

    $info_file = self::directory($path) . "info.php";

    if(file_exists($info_file))
    {
        $theme = array();
        include($info_file);
        $theme_info = $theme;
    }

    return $theme_info;
}

/**
 * Get list of themes useful for select boxes.
 *
 * @return array
 */
static function getSelectList(): array
{
    $themes_list = self::getEnabled();
    $theme_default = self::getDefault();
    $themes_select = array();

    foreach($themes_list as $theme_path)
    {
        $theme_info = self::get($theme_path);

        if($theme_path != $theme_default)
        {
            $themes_select[t($theme_info["name"])] = $theme_path;
        }
        else
        {
            $themes_select[
                t($theme_info["name"]) . " (" . t("Default") . ")"
            ] = "";
        }
    }

    return $themes_select;
}

/**
 * Gets the default theme.
 *
 * @return string
 */
static function getDefault(): string
{
   $theme = Settings::get("theme", "main");

   return $theme ?? "default";
}

/**
 * Check which is the user preferred website theme.
 *
 * @return string
 */
static function getUserTheme(): string
{
    if(
        Authentication::isUserLogged()
        &&
        Authentication::groupHasPermission(
            "select_user_theme", Authentication::currentUserGroup()
        )
    )
    {
        $user_data = Users::get(Authentication::currentUser());

        if(!empty($user_data["theme"]))
        {
            $themes = self::getEnabled();

            if(in_array($user_data["theme"], $themes))
            {
                return $user_data["theme"];
            }
        }
    }

    return self::getDefault();
}

}