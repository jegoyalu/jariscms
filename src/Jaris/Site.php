<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Facilities to initialize and access the application configuration values.
 */
class Site
{

/**
 * Doesn't Receive parameters.
 * @var string
 */
const SIGNAL_INITIALIZATION = "hook_initialization";

/**
 * Receives parameters: $page_data
 * @var string
 */
const SIGNAL_PAGE_DATA = "hook_page_data";

/**
 * Doesn't receive parameters.
 * @var string
 */
const SIGNAL_CRONJOB = "hook_cronjob";

/**
 * Title of the website.
 * @var string
 */
public static $title;

/**
 * Base url of the website.
 * @var string
 */
public static $base_url;

/**
 * Slogan of the website.
 * @var string
 */
public static $slogan;

/**
 * Footer message of the website.
 * @var string
 */
public static $footer_message;

/**
 * Default theme name.
 * @var string
 */
public static $theme;

/**
 * Path of default theme.
 * @var string
 */
public static $theme_path;

/**
 * Current language code.
 * @var string
 */
public static $language;

/**
 * Flag that indicates if clean url's are supported.
 * @var bool
 */
public static $clean_urls;

/**
 * Flag that indicates if user profiles are public.
 * @var bool
 */
public static $user_profiles;

/**
 * Flag that indicates if site should run in development mode.
 * @var bool
 */
public static $development_mode;

/**
 * Flag used by other functions to check if static images where fully generated
 * for the current page.
 * @var bool
 */
public static $static_images_generated;

/**
 * List of menu items of primery menu of site.
 * @var array
 */
public static $primary_links;

/**
 * List of menu items of secondary menu of site.
 * @var array
 */
public static $secondary_links;

/**
 * Holds a copy of current page data.
 * @var array
 */
public static $page_data;

/**
 * Initializes the jaris application settings.
 * @original settings_override
 */
static function init()
{
    //Exit if disk is almost full to prevent write corruption.
    if(floor(disk_free_space(".") / 1024 / 1024) <= 5)
    {
        exit("Error: disk full.");
    }

    //Default settings
    self::$title = 'Jaris CMS';
    self::$base_url = 'http://localhost';
    self::$slogan = 'Where performance matters!';
    self::$user_profiles = false;
    self::$footer_message = "Powered by JarisCMS.";
    self::$theme = 'default';
    self::$theme_path = self::$base_url . '/themes/' . self::$theme;
    self::$clean_urls = true;
    self::$language = 'en';

    if($settings = Settings::getAll("main"))
    {
        self::$title = $settings["title"] ? $settings["title"] : self::$title;

        if(!empty($settings["timezone"]))
        {
            date_default_timezone_set($settings["timezone"]);
        }
        else
        {
            date_default_timezone_set("UTC");
        }

        $protocol = System::isSSLConnection() ? "https" : "http";

        if(
            $settings["auto_detect_base_url"] ||
            trim($settings["base_url"]) == ""
        )
        {
            $paths = explode("/", $_SERVER["SCRIPT_NAME"]);
            unset($paths[count($paths) - 1]); //Remove index.php
            $path = implode("/", $paths);

            self::$base_url = $protocol . "://" . $_SERVER["HTTP_HOST"];

            if(!defined("HHVM_VERSION"))
            {
                self::$base_url .= $path;
            }
        }
        else
        {
            self::$base_url = str_replace(
                "http://",
                "$protocol://",
                ($settings["base_url"] ? $settings["base_url"] : self::$base_url)
            );
        }

        self::$user_profiles = isset($settings["user_profiles"]) ?
                $settings["user_profiles"] : self::$user_profiles
        ;

        self::$slogan = isset($settings["slogan"]) ?
            $settings["slogan"] : self::$slogan
        ;

        self::$footer_message = isset($settings["footer_message"]) ?
            $settings["footer_message"] : self::$footer_message
        ;

        self::$theme = !empty($settings["theme"]) ?
            $settings["theme"] : self::$theme
        ;

        self::$language = !empty($settings["language"]) ?
            $settings["language"] : self::$language
        ;

        self::$clean_urls = $settings["clean_urls"];

        self::$theme_path = self::$base_url . "/" . rtrim(Themes::directory(self::$theme), "/");

        self::$development_mode = isset($settings["development_mode"]) ?
            $settings["development_mode"] : false
        ;
    }

    //For backward compatibility with themes that may be using these
    //global variables by using a global statement.
    global $title, $base_url, $slogan, $user_profiles, $footer_message,
        $theme, $theme_path, $clean_urls, $language
    ;

    $title = self::$title;
    $base_url = self::$base_url;
    $slogan = self::$slogan;
    $user_profiles = self::$user_profiles;
    $footer_message = self::$footer_message;
    $theme = self::$theme;
    $theme_path = self::$theme_path;
    $clean_urls = self::$clean_urls;
    $language = self::$language;
}

/**
 * Gets the current site hostname with www. stripped out.
 * @staticvar string $site
 * @return string
 * @original get_current_site
 */
static function current()
{
    static $site;

    if(!$site)
    {
        //For being able to run scripts from command line
        if(!isset($_SERVER["HTTP_HOST"]))
        {
            //Check if http host was passed on the command line
            if(isset($_REQUEST["HTTP_HOST"]))
            {
                $_SERVER["HTTP_HOST"] = $_REQUEST["HTTP_HOST"];
            }

            //if no http_host passed then return default
            else
            {
                $site = "default";

                return $site;
            }
        }

        // Strip www. from the http host
        $site = strtolower(
            preg_replace(
                "/^www\./",
                "",
                $_SERVER["HTTP_HOST"]
            )
        );
    }

    return $site;
}

/**
 * Gets the data directory for the current domain or
 * use default if not available.
 * @original data_directory
 */
static function dataDir()
{
    static $dir;

    if(!$dir)
    {
        //For being able to run scripts from command line
        if(!isset($_SERVER["HTTP_HOST"]))
        {
            //Check if http host was passed on the command line
            if(isset($_REQUEST["HTTP_HOST"]))
            {
                $_SERVER["HTTP_HOST"] = $_REQUEST["HTTP_HOST"];
            }

            //if not http_host passed then return default
            else
            {
                $dir = "sites/default/data/";
                return $dir;
            }
        }

        $host = strtolower(
            preg_replace(
                "/^www\./",
                "",
                $_SERVER["HTTP_HOST"]
            )
        );

        if(file_exists("sites/" . $host . "/data"))
        {
            $dir = "sites/" . $host . "/data/";
        }
        else
        {
            $dir = "sites/default/data/";
        }
    }

    return $dir;
}

/**
 * Sets the status header with the indicated http error status code.
 *
 * @param int $code The code number to return in the header.
 * @original http_status
 */
static function setHTTPStatus($code)
{
    switch($code)
    {
        case 400:
            header("HTTP/1.1 400 Bad Request", true);
            break;
        case 401:
            header("HTTP/1.1 401 Unauthorized", true);
            break;
        case 403:
            header("HTTP/1.1 403 Forbidden", true);
            break;
        case 404:
            header("HTTP/1.1 404 Not Found", true);
            break;
        case 500:
            header("HTTP/1.1 500 Internal Server Error", true);
            break;

        case 200:
        default:
            header("HTTP/1.1 200 OK", true);
    }
}

/**
 * Checks if the site status is offline and redirect user
 * to the offline status message page.
 * @original check_if_offline
 */
static function checkIfOffline()
{
    $online = Settings::get("site_status", "main");

    if($online)
        return;

    if(
        Uri::get() != "admin/user" &&
        Uri::get() != "offline" &&
        !Authentication::groupHasPermission(
            "offline_login",
            Authentication::currentUserGroup()
        )
    )
    {
        Uri::go("offline");
    }
}

/**
 * Load installed modules include files.
 */
static function loadModules()
{
    //Add installed modules include files here
    $installed_modules = Modules::getInstalled();

    foreach($installed_modules as $machine_name)
    {
        $module_directory = Modules::directory($machine_name) . "include/";
        if(file_exists($module_directory))
        {
            $dir_handle = opendir($module_directory);

            while(($file = readdir($dir_handle)) !== false)
            {
                if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
                {
                    if(is_file($module_directory . $file))
                    {
                        include($module_directory . $file);
                    }
                }
            }
        }
    }
}

/**
 * Start the real initialization process of the site to render a page.
 */
static function bootStrap()
{
    //Starts the main session for the user
    Session::startIfUserLogged();

    //Increase the time for session to garbage collection
    ini_set("session.gc_maxlifetime", "18000");

    //Initialize error handler
    System::initiateErrorCatchSystem();

    //Sets the language based on user selection or system default
    self::$language = Language::getCurrent();

    //Check if cms is run for the first time and run the installer
    System::checkIfNotInstalled();

    //Check if site status is online to continue
    Site::checkIfOffline();

    //In forms it keeps the fields user entered values if browser
    //page back/forward, also on browser back/forward a client
    //cached page is loaded which prevents unnecessary requests.
    if(!Authentication::isUserLogged())
    {
        header("Cache-control: private");
    }

    //Sets the page that is going to be displayed
    $page = Uri::get();

    //Stores the uri that end users will see even if the $page is changed by
    //show_category_results() sinces template functions need the visual uri
    //not the content uri
    $visual_uri = Uri::get();

    //Skips all the data procesing if image or file and display it.
    $page_type = Uri::type($page);
    if($page_type == "image")
    {
        $image_path = Uri::getImagePath($page);
        Images::show($image_path);
    }
    elseif($page_type == "user_picture")
    {
        Images::printUserPic($page);
    }
    elseif($page_type == "user_profile")
    {
        Users::showProfile($page);
    }
    elseif($page_type == "file")
    {
        Pages\Files::printIt($page);
    }
    elseif($page_type == "category")
    {
        Categories::showResults($page);
    }

    //Call initialization hooks so modules can make things before page is rendered
    Modules::hook("hook_initialization");

    //Read page data
    self::$page_data[0] = Pages::get($page, self::$language);

    //Call page data hooks so modules can modify page_data content
    Modules::hook("hook_page_data", self::$page_data);

    //Check if the current user can view the current content
    if(!Pages::userHasAccess(self::$page_data[0]))
    {
        Uri::go("access-denied");
    }

    // If rendering mode is set skip directly to printing the page content.
    if(isset(self::$page_data[0]["rendering_mode"]) && self::$page_data[0]["is_system"])
    {
        $special_rendering = false;

        //Set adequate content type and enconding
        switch(self::$page_data[0]["rendering_mode"])
        {
            case "api":
                header('Content-Type: text/plain; charset=utf-8', true);
                $special_rendering = true;
                break;
            case "javascript":
                header('Content-Type: text/javascript; charset=utf-8', true);
                $special_rendering = true;
                break;
            case "css":
                header('Content-Type: text/css; charset=utf-8', true);
                $special_rendering = true;
                break;
            case "xml":
                header('Content-Type: text/xml; charset=utf-8', true);
                $special_rendering = true;
                break;
            case "plain_html":
                header('Content-Type: text/html; charset=utf-8', true);
                $special_rendering = true;
                break;
        }

        if($special_rendering)
        {
            print System::evalPHP(self::$page_data[0]['content']);
            exit;
        }
    }

    //Check if page is cacheable and return cache if possible for performance
    System::cachePageIfPossible($page, self::$page_data[0]);

    //Append hidden parameters to $_REQUEST for the correct execution of breadcrumbs
    //TODO Fix an issue that keeps hidden parameters stored.
    System::appendHiddenParameters();

    //Read blocks
    $header_data = Data::sort(
        Data::parse(
            Language::dataTranslate(
                self::dataDir() . "blocks/header.php"
            )
        ),
        "order"
    );

    $footer_data = Data::sort(
        Data::parse(
            Language::dataTranslate(
                self::dataDir() . "blocks/footer.php"
            )
        ),
        "order"
    );

    $left_data = Data::sort(
        Data::parse(
            Language::dataTranslate(
                self::dataDir() . "blocks/left.php"
            )
        ),
        "order"
    );

    $right_data = Data::sort(
        Data::parse(
            Language::dataTranslate(
                self::dataDir() . "blocks/right.php"
            )
        ),
        "order"
    );

    $center_data = Data::sort(
        Data::parse(
            Language::dataTranslate(
                self::dataDir() . "blocks/center.php"
            )
        ),
        "order"
    );

    //Read menus
    $primary_links_data = Data::sort(
        Menus::getChildItems(Menus::getPrimaryName()),
        "order"
    );

    $secondary_links_data = Data::sort(
        Menus::getChildItems(Menus::getSecondaryName()),
        "order"
    );

    //Move blocks to other positions depending on current theme
    Blocks::moveByTheme(
        $header_data,
        $left_data,
        $right_data,
        $center_data,
        $footer_data
    );

    //In case of page not found
    if(!self::$page_data[0])
    {
        self::$page_data = System::pageNotFound();
    }

    //Format Data
    $content = View::getContentHTML(self::$page_data, $visual_uri);
    $header = View::getBlocksHTML($header_data, "header", $visual_uri);
    $footer = View::getBlocksHTML($footer_data, "footer", $visual_uri);
    $left = View::getBlocksHTML($left_data, "left", $visual_uri);
    $right = View::getBlocksHTML($right_data, "right", $visual_uri);
    $center = View::getBlocksHTML($center_data, "center", $visual_uri);

    self::$primary_links = View::getLinksHTML($primary_links_data, "primary-links");
    self::$secondary_links = View::getLinksHTML($secondary_links_data, "secondary-links");

    //Adds edit link on every page if administrator is logged.
    System::addEditTab(self::$page_data[0]);

    //Set the page title
    if(
        Pages::isSystem("", self::$page_data[0]) ||
        ($page_type == "category" && $page == "search") ||
        $page == "user"
    )
    {
        //Parse the title if is system page
        self::$title = t(System::evalPHP(self::$page_data[0]["title"]))
            . " - " . t(self::$title)
        ;
    }
    else
    {
        //Just translate if not system page
        if(trim(self::$page_data[0]["meta_title"]) != "")
        {
            //If meta title is available use it
            self::$title = t(self::$page_data[0]["meta_title"]);
        }
        else
        {
            self::$title = t(self::$page_data[0]["title"])
                . " - " . t(self::$title)
            ;
        }
    }

    //Display Page and generate cache if enabled and possible
    $page_html = View::render(
        $visual_uri,
        self::$page_data[0],
        $content,
        $left,
        $center,
        $right,
        $header,
        $footer
    );

    System::savePageToCacheIfPossible($page, self::$page_data[0], $page_html);

    print $page_html;
}

/**
 * Generates and prints basic stats like execution time and memory usage.
 * This should be called at the end of the site bootstrap to get more accurate
 * execution time.
 * @param string $from Where the print stats took place, eg: cache, fast cache.
 */
static function printStats($from="")
{
    global $time_start;

    if(Settings::get("view_script_stats", "main"))
    {
        print "<div style=\"clear: both\"></div>";

        print "<div style=\"width: 90%; border: solid #f0b656 1px; "
        . "background-color: #d0dde7; margin: 0 auto 0 auto; padding: 10px\">";

        print "<b>Script execution time:</b> " .
            ceil((microtime(true) - $time_start) * 1000) .
            " milliseconds<br />"
        ;

        print "<b>Peak memory usage:</b> " .
            number_format(memory_get_peak_usage() / 1024 / 1024, 0, '.', ',') .
            " MB<br />"
        ;

        print "<b>Final memory usage:</b> " .
            number_format(memory_get_usage() / 1024 / 1024, 0, '.', ',') .
            " MB<br />"
        ;

        if($from)
        {
            print "<b>Page retrieved from:</b> $from <br />";
        }

        print "</div>";
    }
}

}