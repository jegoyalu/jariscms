<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Contains some misc but important system functions.
 */
class System
{

/**
 * Stores JarisCMS version number.
 * @var string
 */
const VERSION = "6.8.0 MS";

/**
 * Receives parameters: $page, $tabs
 * @var string
 */
const SIGNAL_PAGE_NOT_FOUND = "hook_page_not_found";

/**
 * Receives parameters: $styles
 * @var string
 */
const SIGNAL_GET_SYSTEM_STYLES = "hook_get_system_styles";

/**
 * Receives parameters: $scripts
 * @var string
 */
const SIGNAL_GET_SYSTEM_SCRIPTS = "hook_get_system_scripts";

/**
 * Receives parameters: $list
 * @var string
 */
const SIGNAL_SYSTEM_PAGES_BLACKLIST = "hook_system_pages_blacklist";

/**
 * Receives parameters: $sections
 * @var string
 */
const SIGNAL_GENERATE_ADMIN_PAGE = "hook_generate_admin_page";

/**
 * Receives parameters: $breadcrumb, $found_sections
 * @var string
 */
const SIGNAL_BREADCRUMB = "hook_breadcrumb";

/**
 * Receives parameters: $uri, $page_data, $is_page_owner
 * @var string
 */
const SIGNAL_ADD_EDIT_TAB = "hook_add_edit_tab";

/**
 * Receives parameters: $uri, $page_data, $file_content
 * @var string
 */
const SIGNAL_CACHE_PAGE = "hook_cache_page";

/**
 * Receives parameters: $uri, $page_data, $content
 * @var string
 */
const SIGNAL_SAVE_PAGE_TO_CACHE = "hook_save_page_to_cache";

/**
 * Doesn't receives parameters.
 * @var string
 */
const SIGNAL_CLEAR_PAGE_CACHE = "hook_clear_page_cache";

/**
 * Path of system js files.
 * @var string
 */
const JS_PATH = "system/js/";

/**
 * Path of system js files.
 * @var string
 */
const CSS_PATH = "system/css/";

/**
 * Generates a page array for page not found and sets http status to 404.
 *
 * @return array Page not found data.
 */
static function pageNotFound(): array
{
    Site::setHTTPStatus(404);

    $page = array(
        array(
            "title" => t("Page not found"),
            "content" => t("The page you was searching doesn't exists."),
            "input_format" => "full_html"
        )
    );

    if($page_not_found = Settings::get("page_not_found", "main"))
    {
        if($page_data = Pages::get($page_not_found, Language::getCurrent()))
        {
            $page[0] = $page_data;
        }
    }

    $tabs = array();

    $page_type = Uri::type(Uri::get());

    if(
        $page_type != "image" &&
        $page_type != "user_picture" &&
        $page_type != "user_profile" &&
        $page_type != "file" &&
        $page_type != "category"
    )
    {
        if(
            Authentication::userHasPermissions(
                array("view_content", "add_content")
            )
        )
        {
            $tabs[t("Create Page")] = array(
                "uri" => "admin/pages/types",
                "arguments" => array("uri" => Uri::get())
            );
        }
    }

    //Call page_not_found modules hook before returning data
    Modules::hook("hook_page_not_found", $page, $tabs);

    foreach($tabs as $title => $data)
    {
        if(!isset($data["arguments"]))
        {
            View::addTab($title, $data["uri"]);
        }
        else
        {
            View::addTab($title, $data["uri"], $data["arguments"]);
        }
    }

    return $page;
}

/**
 * Gets all the css files available on the system.
 *
 * @return array List with the full path to files example:
 * files[0] = "http://localhost/system/css/system.css"
 */
static function getStyles(): array
{
    $additional_styles = View::$additional_styles;

    $styles = array(
        Uri::url(self::CSS_PATH."system.css")
    );

    foreach($additional_styles as $url)
    {
        $styles[] = $url;
    }

    //Call get_system_styles modules hook before returning data
    Modules::hook("hook_get_system_styles", $styles);

    return $styles;
}

/**
 * Gets all the java script files available on the system
 *
 * @return array List with the full path to files example:
 * files[0] = "http://localhost/scripts/system.js"
 */
static function getScripts(): array
{
    $additional_scripts = View::$additional_scripts;

    $scripts = array(
        Uri::url(self::JS_PATH."jquery-3.3.1.min.js"),
        Uri::url(self::JS_PATH."jquery.textarearesizer.min.js"),
        Uri::url(self::JS_PATH."system.js")
    );

    foreach($additional_scripts as $url)
    {
        $scripts[] = $url;
    }

    //Call get_system_scripts modules hook before returning data
    Modules::hook("hook_get_system_scripts", $scripts);

    return $scripts;
}

/**
 * Gets the correct url for a system available css file.
 *
 * @param string $path Path of the file inside the systems css dir.
 *
 * @return string
 */
static function getStyleUrl(string $path, array $args = array()): string
{
    return Uri::url(self::CSS_PATH.$path, $args);
}

/**
 * Gets the correct url for a system available js file.
 *
 * @param string $path Path of the file inside the systems script dir.
 *
 * @return string
 */
static function getScriptUrl(string $path, array $args = array()): string
{
    return Uri::url(self::JS_PATH.$path, $args);
}

/**
 * Gets an array with a list of directories with sections
 * marked as system ones. Useful to know in what pages to block
 * certain actions as editing task.
 *
 * @param string $check_path used to make a check with a given
 * path to see if it is a system page or not.
 *
 * @return array|bool List of system sections or true, false if check_path is
 * specified.
 */
static function pagesBlackList(string $check_path = "")
{
    static $list = null;

    if(!is_array($list))
    {
        $list = array();

        //$list[] = Site::dataDir() . "pages/sections/admin";
        $list[] = Site::dataDir() . "pages/singles/s/se/search/data.php";
        $list[] = Site::dataDir() . "pages/singles/a/ac/access-denied/data.php";
        $list[] = Site::dataDir() . "pages/singles/h/h/home/data.php";
        $list[] = Site::dataDir() . "pages/singles/u/us/user/data.php";
    }

    //Call system_pages_blacklist hook before returning data
    Modules::hook("hook_system_pages_blacklist", $list);

    if($check_path)
    {
        foreach($list as $value)
        {
            $path = strtolower($check_path);
            $value = strtolower($value);

            if(strstr($path, $value))
            {
                return true;
            }
        }

        return false;
    }

    return $list;
}

/**
 * Check if jaris cms is currently installed and if
 * not redirect to install page.
 */
static function checkIfNotInstalled(): void
{
    $base_url = Site::$base_url;

    if(
        $base_url == "http://localhost" &&
        !file_exists(Site::dataDir() . "settings/main.php")
    )
    {
        $port = $_SERVER["SERVER_PORT"] != "80" ? ":{$_SERVER["SERVER_PORT"]}" : "";
        $query = str_replace("p=", "", $_SERVER["QUERY_STRING"]);

        if(strstr($_SERVER["PHP_SELF"], "index.php") !== false)
            header(
                "Location: http://" . $_SERVER["SERVER_NAME"] . $port .
                str_replace(
                    "index.php",
                    "system/install/install.php",
                    $_SERVER["PHP_SELF"]
                )
            );
        else
            header(
                "Location: http://" . $_SERVER["SERVER_NAME"] . $port .
                str_replace(
                    "/$query",
                    "/system/install/install.php",
                    $_SERVER["PHP_SELF"])
                );

        exit;
    }
}

/**
 * Function that stores all neccesary error messages.
 *
 * @param string $type The type of error message to retrieve.
 *
 * @return string An error message already translated if available.
 */
static function errorMessage(string $type = ""): string
{
    $message = "";
    $message_orig = "";

    switch($type)
    {
        case "write_error_data":
            $message .= t("Check your write permissions on the data directory.");
            $message_orig .= "Check your write permissions on the data directory.";
            break;

        case "write_error_language":
            $message .= t("Check your write permissions on the language directory.");
            $message_orig .= "Check your write permissions on the language directory.";
            break;

        case "translations_not_moved":
            $message .= t("Translations could not be repositioned with the new uri. Check your write permissions on the language directory.");
            $message_orig .= "Translations could not be repositioned with the new uri. Check your write permissions on the language directory.";
            break;

        case "translations_not_deleted":
            $message .= t("Translations could not be deleted. Check your write permissions on the language directory.");
            $message_orig .= "Translations could not be deleted. Check your write permissions on the language directory.";
            break;

        case "image_file_type":
            $message .= t("The file type must be JPEG, PNG or GIF.");
            $message_orig .= "The file type must be JPEG, PNG or GIF.";
            break;

        case "group_exist":
            $message .= t("The group machine name is already in use.");
            $message_orig .= "The group machine name is already in use.";
            break;

        case "delete_system_group":
            $message .= t("This is a system group and can not be deleted.");
            $message_orig .= "This is a system group and can not be deleted.";
            break;

        case "edit_system_group":
            $message .= t("This is a system group and its machine name can not be modified.");
            $message_orig .= "This is a system group and its machine name can not be modified.";
            break;

        case "menu_exist":
            $message .= t("The menu machine name is already in use.");
            $message_orig .= "The menu machine name is already in use.";
            break;

        case "type_exist":
            $message .= t("The type machine name is already in use.");
            $message_orig .= "The type machine name is already in use.";
            break;

        case "input_format_exist":
            $message .= t("The input format machine name is already in use.");
            $message_orig .= "The input format machine name is already in use.";
            break;

        case "category_exist":
            $message .= t("The category machine name is already in use.");
            $message_orig .= "The category machine name is already in use.";
            break;

        case "delete_system_type":
            $message .= t("This is a system type and can not be deleted.");
            $message_orig .= "This is a system type and can not be deleted.";
            break;

        case "user_exist":
            $message .= t("The username is already in use.");
            $message_orig .= "The username is already in use.";
            break;

        case "user_not_exist":
            $message .= t("Theres no user that match your criteria on the system.");
            $message_orig .= "Theres no user that match your criteria on the system.";
            break;

        default:
            $message .= t("Operation could not be completed.");
            $message_orig .= "Operation could not be completed.";
            break;
    }

    Logger::error($message_orig);

    return $message;
}

/**
 * Checks if ssl is supported by current webserver.
 */
static function isSSLSupported(): bool
{
    $base_url = Site::$base_url;

    $hostname = str_replace(
        array("http://", "https://"), "ssl://", $base_url
    );

    $connection = fsockopen($hostname, 443);

    if($connection)
    {
        return true;
    }

    return false;
}

/**
 * Checks if the current connection is ssl.
 *
 * @return bool True on success false otherwise.
 */
static function isSSLConnection(): bool
{
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
    {
        return true;
    }

    return false;
}

/**
 * Generates edit tab for current page when administrator is logged in.
 *
 * @param ?array $page_data If set checks if tab can be added to the
 * given page data instead of loading page from file.
 */
static function addEditTab(?array &$page_data=[]): void
{
    $uri = Uri::get();

    //Do not add edit tab to page not found
    $data_path = Pages::getPath($uri) . "/data.php";
    if(!file_exists($data_path))
    {
        return;
    }

    $is_page_owner = false;

    if(
        Authentication::groupHasPermission(
            "edit_content",
            Authentication::currentUserGroup()
        ) &&
        !Pages::isSystem("", $page_data)
    )
    {
        if(Pages::userIsOwner($uri, $page_data))
        {
            View::addTab(t("Edit"), "admin/pages/edit", array("uri" => $uri));

            View::addTab(t("View"), $uri);

            if(
                Authentication::groupHasPermission(
                    "duplicate_content",
                    Authentication::currentUserGroup()
                )
            )
            {
                View::addTab(
                    t("Duplicate"),
                    "admin/pages/duplicate",
                    array("uri" => $uri)
                );
            }

            $is_page_owner = true;
        }
    }

    Modules::hook("hook_add_edit_tab", $uri, $page_data, $is_page_owner);
}

/**
 * Parses a string as actual php code using the eval function
 *
 * @param string $text The string to be parsed.
 * @param ?bool $eval_return The return value of the evaluated code.
 *
 * @return string The evaluated output captured by ob_get_contents function.
 */
static function evalPHP(?string $text, ?bool &$eval_return=false): string
{
    //Prepares the text to be evaluated
    if(is_null($text))
    {
        return "";
    }
    else
    {
        $text = trim($text, "\n\r\t\0\x0B ");
    }

    if($text == "")
    {
        return "";
    }

    ob_start();
    $eval_return = eval('?>' . $text);
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * Override the php default error reporting system.
 */
static function initiateErrorCatchSystem(): void
{
    if(Site::$development_mode)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('html_errors', 1);

        error_reporting(E_ALL);
    }
    else
    {
        ini_set('display_errors', 0);
        error_reporting(E_ALL ^ E_NOTICE);
    }

    set_error_handler(array('\Jaris\System', 'errorCatchHook'));

    set_exception_handler(array('\Jaris\System', "exceptionCatchHook"));
}

/**
 * Catch the php errors and dysplay them as an error message.
 *
 * @param  int $errno
 * @param  string $errmsg
 * @param  string $filename
 * @param  int $linenum
 * @param  array $vars
 *
 * @return bool
 */
static function errorCatchHook(
    int $errno, 
    string $errmsg, 
    string $filename, 
    int $linenum, 
    array $vars = []
): bool
{
    static $errortype=null, $in=null, $on_line=null;

    // Skip errors that are supressed by a @
    if(error_reporting() === 0)
    {
        return true;
    }

    //Since searching for translation is slow we do it just once.
    if(!is_array($errortype))
    {
        $errortype = array(
            E_ERROR => t('Error'),
            E_WARNING => t('Warning'),
            E_PARSE => t('Parsing Error'),
            E_NOTICE => t('Notice'),
            E_CORE_ERROR => t('Core Error'),
            E_CORE_WARNING => t('Core Warning'),
            E_COMPILE_ERROR => t('Compile Error'),
            E_COMPILE_WARNING => t('Compile Warning'),
            E_USER_ERROR => t('User Error'),
            E_USER_WARNING => t('User Warning'),
            E_USER_NOTICE => t('User Notice'),
            E_STRICT => t('Runtime Notice'),
            E_RECOVERABLE_ERROR => t('Catchable Fatal Error')
        );

        $in = t("in");
        $on_line = t("on line");
    }

    if($errno != E_NOTICE && $errno != E_STRICT)
    {
        if($errno != E_WARNING || Site::$development_mode)
        {
            View::addMessage(
                "<b>" . $errortype[$errno] . "</b> - $errmsg" .
                "  " . $in . " $filename " . $on_line . " $linenum",
                "error"
            );
        }

        if(!Sql::dbExists("errors_log"))
        {
            $db = Sql::open("errors_log");

            Sql::query(
                "create table errors_log ("
                . "error_date text, "
                . "error_type text, "
                . "error_message text, "
                . "error_file text, "
                . "error_line text, "
                . "error_page text"
                . ")",
                $db
            );

            Sql::query(
                "create index errors_log_index on errors_log ("
                . "error_date desc, "
                . "error_type desc"
                . ")",
                $db
            );

            Sql::close($db);
        }

        $db = Sql::open("errors_log");

        Sql::query(
            "insert into errors_log values ("
            . "'".time()."', "
            . "'$errno', "
            . "'".str_replace("'", "''", $errmsg)."', "
            . "'".str_replace("'", "''", $filename)."', "
            . "'$linenum', "
            . "'".str_replace("'", "''", Uri::get())."'"
            . ")",
            $db
        );

        Sql::close($db);
    }

    // Don't execute PHP internal error handler
    return true;
}

/**
 * The default exception handler that gets triggered when an exception
 * is thrown outside of a try/catch block.
 *
 * @param \Throwable|\Exception $e
 * 
 * @return void
 */
static function exceptionCatchHook($e): void
{
    static $ex=null, $in=null, $on_line=null;

    //Since searching for translation is slow we do it just once.
    if(is_null($in))
    {
        $ex = t("Exception");
        $in = t("in");
        $on_line = t("on line");
    }

    ob_clean();
    Site::setHTTPStatus(500);
    print "<h1>" . "500 Framework Server Error</h1>";

    if(Site::$development_mode)
    {
        print
            "<b>" . $ex . " (" . get_class($e) . ")" . "</b> - " 
                . $e->getMessage()  . "  " . $in . " " 
                . $e->getFile() . " " . $on_line . " " 
                . $e->getLine()
        ;
    }

    if(!Sql::dbExists("errors_log"))
    {
        $db = Sql::open("errors_log");

        Sql::query(
            "create table errors_log ("
            . "error_date text, "
            . "error_type text, "
            . "error_message text, "
            . "error_file text, "
            . "error_line text, "
            . "error_page text"
            . ")",
            $db
        );

        Sql::query(
            "create index errors_log_index on errors_log ("
            . "error_date desc, "
            . "error_type desc"
            . ")",
            $db
        );

        Sql::close($db);
    }

    $db = Sql::open("errors_log");

    Sql::query(
        "insert into errors_log values ("
        . "'".time()."', "
        . "'exception', "
        . "'".str_replace("'", "''", $e->getMessage())."', "
        . "'".str_replace("'", "''", $e->getFile())."', "
        . "'".$e->getLine()."', "
        . "'".str_replace("'", "''", Uri::get())."'"
        . ")",
        $db
    );

    Sql::close($db);

    exit;
}

/**
 * Generates array with all the sections to display on the control center.
 *
 * @return array
 */
static function generateAdminPageSections(): array
{
    $group = Authentication::currentUserGroup();

    $sections = array();

    //Content
    $content = array();

    if(Authentication::groupHasPermission("add_content", $group))
    {
        $content[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/pages/types"),
            "description" => t("Create new content.")
        );
    }

    if(Authentication::groupHasPermission("view_content", $group))
    {
        $content[] = array(
            "title" => t("Navigate"),
            "url" => Uri::url("admin/pages/list"),
            "description" => t("View and edit existing content.")
        );
    }

    if(Authentication::groupHasPermission("add_types", $group))
    {
        $content[] = array(
            "title" => t("Add Type"),
            "url" => Uri::url("admin/types/add"),
            "description" => t("Create new content type.")
        );
    }

    if(Authentication::groupHasPermission("view_types", $group))
    {
        $content[] = array(
            "title" => t("Manage Types"),
            "url" => Uri::url("admin/types"),
            "description" => t("View and edit existing content types.")
        );
    }

    if(Authentication::groupHasPermission("add_input_formats", $group))
    {
        $content[] = array(
            "title" => t("Add Input Format"),
            "url" => Uri::url("admin/input-formats/add"),
            "description" => t("Create new content input format.")
        );
    }

    if(Authentication::groupHasPermission("view_input_formats", $group))
    {
        $content[] = array(
            "title" => t("Manage Input Formats"),
            "url" => Uri::url("admin/input-formats"),
            "description" => t("View and edit existing content input formats.")
        );
    }

    if(Authentication::groupHasPermission("add_categories", $group))
    {
        $content[] = array(
            "title" => t("Add Category"),
            "url" => Uri::url("admin/categories/add"),
            "description" => t("Create new content categories.")
        );
    }

    if(Authentication::groupHasPermission("view_categories", $group))
    {
        $content[] = array(
            "title" => t("Manage Categories"),
            "url" => Uri::url("admin/categories"),
            "description" => t("View and edit existing content categories.")
        );
    }

    if(Authentication::groupHasPermission("approve_content", $group))
    {
        $content[] = array(
            "title" => t("Approve Content"),
            "url" => Uri::url("admin/pages/approve"),
            "description" => t("View and approve pending content to be published.")
        );
    }

    if($content)
    {
        $sections[] = array(
            "class" => "content",
            "title" => t("Content"),
            "sub_sections" => $content
        );
    }

    //Blocks
    $blocks = array();

    if(Authentication::groupHasPermission("add_blocks", $group))
    {
        $blocks[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/blocks/add"),
            "description" => t("Create new blocks.")
        );
    }

    if(Authentication::groupHasPermission("view_blocks", $group))
    {
        $blocks[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/blocks"),
            "description" => t("View and edit existing blocks.")
        );
    }

    if($blocks)
    {
        $sections[] = array(
            "class" => "blocks",
            "title" => t("Blocks"),
            "sub_sections" => $blocks
        );
    }

    //Menus
    $menus = array();

    if(Authentication::groupHasPermission("add_menus", $group))
    {
        $menus[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/menus/add"),
            "description" => t("Create new menu.")
        );
    }

    if(Authentication::groupHasPermission("view_menus", $group))
    {
        $menus[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/menus"),
            "description" => t("View and edit existing menus and its menu items.")
        );
    }

    if($menus)
    {
        $sections[] = array(
            "class" => "menus",
            "title" => t("Menus"),
            "sub_sections" => $menus
        );
    }

    //Users
    $users = array();

    if(Authentication::groupHasPermission("add_users", $group))
    {
        $users[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/users/add"),
            "description" => t("Create new user.")
        );
    }

    if(Authentication::groupHasPermission("view_users", $group))
    {
        $users[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/users/list"),
            "description" => t("View and edit existing users.")
        );
    }

    if($users)
    {
        $sections[] = array(
            "class" => "users",
            "title" => t("Users"),
            "sub_sections" => $users
        );
    }

    //Groups
    $groups = array();

    if(Authentication::groupHasPermission("add_groups", $group))
    {
        $groups[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/groups/add"),
            "description" => t("Create new group.")
        );
    }

    if(Authentication::groupHasPermission("view_groups", $group))
    {
        $groups[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/groups"),
            "description" => t("View and edit existing groups.")
        );
    }

    if($groups)
    {
        $sections[] = array(
            "class" => "groups",
            "title" => t("Groups"),
            "sub_sections" => $groups
        );
    }

    //Settings
    $settings = array();

    if(Authentication::groupHasPermission("edit_settings", $group))
    {
        $settings[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/settings"),
            "description" => t("Modify site settings.")
        );

        $settings[] = array(
            "title" => t("Advanced"),
            "url" => Uri::url("admin/settings/advanced"),
            "description" => t("Modify the avanced system settings.")
        );
    }

    if(Authentication::groupHasPermission("edit_settings", $group))
    {
        $settings[] = array(
            "title" => t("Search Engine"),
            "url" => Uri::url("admin/settings/search"),
            "description" => t("Change the settings of the search page.")
        );
    }

    if(Authentication::groupHasPermission("select_theme", $group))
    {
        $settings[] = array(
            "title" => t("Theme"),
            "url" => Uri::url("admin/themes"),
            "description" => t("View and choose site theme.")
        );
    }

    if(Authentication::groupHasPermission("view_keys_api", $group))
    {
        $settings[] = array(
            "title" => t("Api Access"),
            "url" => Uri::url("admin/settings/api"),
            "description" => t("View and manage api keys.")
        );
    }

    if(Authentication::groupHasPermission("edit_settings", $group))
    {
        $settings[] = array(
            "title" => t("Log"),
            "url" => Uri::url("admin/settings/log"),
            "description" => t("View the system log.")
        );
    }

    if(Authentication::groupHasPermission("edit_settings", $group))
    {
        $settings[] = array(
            "title" => t("About JarisCMS"),
            "url" => Uri::url("admin/settings/about"),
            "description" => t("View current jaris version and developer information.")
        );
    }

    if($settings)
    {
        $sections[] = array(
            "class" => "settings",
            "title" => t("Settings"),
            "sub_sections" => $settings
        );
    }

    //Languages
    $language = array();

    if(Authentication::groupHasPermission("add_languages", $group))
    {
        $language[] = array(
            "title" => t("Add"),
            "url" => Uri::url("admin/languages/add"),
            "description" => t("Add another language to the system.")
        );
    }

    if(Authentication::groupHasPermission("view_languages", $group))
    {
        $language[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/languages"),
            "description" => t("Manage available languages on the system.")
        );
    }

    if($language)
    {
        $sections[] = array(
            "class" => "languages",
            "title" => t("Languages"),
            "sub_sections" => $language
        );
    }

    //Modules
    $modules = array();

    if(Authentication::groupHasPermission("view_modules", $group))
    {
        $modules[] = array(
            "title" => t("Manage"),
            "url" => Uri::url("admin/modules"),
            "description" => t("Install or uninstall modules to the system.")
        );

        $sections[] = array(
            "class" => "modules",
            "title" => t("Modules"),
            "sub_sections" => $modules
        );
    }

    return $sections;
}

/**
 * Function that generates/prints the html for the administration page.
 *
 * @param array $sections In the format sections[] = array(
 *   "class"=>"css class",
 *   "title"=>"string",
 *   "sub_sections"[]=> array(
 *     "title"=>"string",
 *     "description"=>"string",
 *     "url"=>"string"
 *   )
 * )
 */
static function generateAdminPage(array $sections): void
{
    //Call generate_admin_page hook before generating sections
    Modules::hook("hook_generate_admin_page", $sections);

    if(count($sections) <= 0)
    {
        View::addMessage("No task assigned to you on the control center.");
        Uri::go("admin/user");
    }

    $html = "<div class=\"administration-list\">\n";

    foreach($sections as $section_details)
    {
        $html .= "<div class=\"section section-{$section_details['class']}\">\n";
        $html .= "<h2 class=\"section-title\">{$section_details['title']}</h2>\n";
        $html .= "<div class=\"section-content\">\n";

        if(count($section_details["sub_sections"]) > 0)
        {
            foreach($section_details["sub_sections"] as $fields)
            {
                $html .= "<div class=\"subsection-title\">\n";
                $html .= "<a href=\"{$fields['url']}\">{$fields['title']}</a>\n";
                $html .= "</div>\n";

                $html .= "<div class=\"description\">\n";
                $html .= "{$fields['description']}\n";
                $html .= "</div>\n";
            }
        }

        $html .= "</div>\n";
        $html .= "</div>\n";
    }

    $html .= "</div>\n";

    print $html;
}

/**
 * Checks what browser the visitor is using.
 *
 * @return string Value could be ie, firefox, chrome, safari, opera or other.
 */
static function getUserBrowser(): string
{
    if("" . stristr($_SERVER['HTTP_USER_AGENT'], "MSIE") . "" != "")
    {
        return "ie";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Firefox") . "" != "")
    {
        return "firefox";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Chrome") . "" != "")
    {
        return "chrome";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Safari") . "" != "")
    {
        return "safari";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Opera") . "" != "")
    {
        return "opera";
    }
    else
    {
        return "other";
    }
}

/**
 * Prints a generaic navigation bar for any kind of results
 *
 * @param int $total_count The total amount of results.
 * @param int $page The actual page number displaying results.
 * @param string $uri The uri used on navigation bar links.
 * @param string $module Optional module name to generate uri.
 * @param int $amount Optional amount of results to display per page, Default: 30
 * @param array $arguments Optional arguments to pass to the navigation links.
 */
static function printNavigation(
    int $total_count,
    int $page,
    string $uri,
    string $module = "",
    int $amount = 30,
    array $arguments = []
): bool
{
    $page_count = 0;
    $remainder_pages = 0;

    if($total_count <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor($total_count / $amount);
        $remainder_pages = $total_count % $amount;

        if($remainder_pages > 0)
        {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if($page > $page_count || $page < 0 || $page_count == 1)
    {
        return false;
    }

    print "<div class=\"search-results\">\n";
    print "<div class=\"navigation\">\n";
    if($page != 1)
    {
        $arguments["page"] = $page - 1;
        $previous_page = Uri::url(Modules::getPageUri($uri, $module), $arguments);
        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
    {
        if($start_page > $page || $start_page < $page)
        {
            $arguments["page"] = $start_page;
            $url = Uri::url(Modules::getPageUri($uri, $module), $arguments);
            print "<a class=\"page\" href=\"$url\">$start_page</a>";
        }
        else
        {
            print "<a class=\"current-page page\">$start_page</a>";
        }
    }

    if($page < $page_count)
    {
        $arguments["page"] = $page + 1;
        $next_page = Uri::url(Modules::getPageUri($uri, $module), $arguments);
        $next_text = t("Next");
        print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
    }
    print "</div>\n";
    print "</div>\n";

    return true;
}

/**
 * To generate a breadcrumb using the available path sections on a uri.
 *
 * @param string $separator The sections separator.
 *
 * @return string Breadcrumb html or
 * empty string if a path section doesn't exists.
 */
static function generateBreadcrumb(string $separator = "&gt;"): string
{
    $paths = explode("/", Uri::get());

    $breadcrumb = "";

    $loop_count = 1;
    $paths_count = count($paths);
    $paths_implode = "";
    $found_sections = 0;

    if($paths_count > 1)
    {
        foreach($paths as $path)
        {
            $page_data = Pages::get(
                $paths_implode . $path,
                Language::getCurrent()
            );

            if($page_data)
            {
                if($loop_count < $paths_count)
                {
                    $breadcrumb .= "<a href=\"" .
                        Uri::url($paths_implode . $path) . "\">" .
                        self::evalPHP($page_data['title']) . "</a> &gt; "
                    ;
                }
                else
                {
                    $breadcrumb .= "<span class=\"current\">" .
                        self::evalPHP($page_data['title']) . "</span>"
                    ;
                }

                $found_sections++;
            }

            $paths_implode .= $path . "/";
            $loop_count++;
        }
    }

    Modules::hook("hook_breadcrumb", $breadcrumb, $found_sections);

    if($found_sections <= 1)
    {
        return "";
    }
    else
    {
        self::addHiddenUrlParameters($_GET, "get");
        self::addHiddenUrlParameters($_POST, "post");
    }

    return $breadcrumb;
}

/**
 * Helper function for breadcrumbs to store current url parameters.
 *
 * @param array $parameters Array of parameters
 * array("parameter_name"=>"value")
 * @param string $type Could be get or post
 */
static function addHiddenUrlParameters(
    array $parameters,  string $type = "get"
): void
{
    if(is_array($parameters) && count($parameters) > 0)
    {
        foreach($parameters as $name => $value)
        {
            Session::start();

            if($name != "p")
            {
                $_SESSION["hidden_parameters"][$type][$name] = $value;
            }
        }
    }
}

/**
 * Breadcrumbs function assitant that should be called on
 * jariscms initialization to append hidden url parameters to
 * $_REQUEST variable.
 */
static function appendHiddenParameters(): void
{
    //Only execute if current breadcrumb generation is valid
    if(self::generateBreadcrumb() && Settings::get("breadcrumbs", "main"))
    {
        if(isset($_SESSION["hidden_parameters"]))
        {
            if(is_array($_SESSION["hidden_parameters"]["get"]))
            {
                foreach($_SESSION["hidden_parameters"]["get"] as $name => $value)
                {
                    $_GET[$name] = $value;
                    $_REQUEST[$name] = $value;
                }
            }

            if(is_array($_SESSION["hidden_parameters"]["post"]))
            {
                foreach($_SESSION["hidden_parameters"]["post"] as $name => $value)
                {
                    $_POST[$name] = $value;
                    $_REQUEST[$name] = $value;
                }
            }

            unset($_SESSION["hidden_parameters"]);

            Session::destroyIfEmpty();
        }
    }
}

/**
 * Check if page cache expired and if not display the cached page.
 *
 * @param string $uri The uri of the page to check.
 * @param array $page_data The actual data of the page to check.
 */
static function cachePageIfPossible(string $uri, array $page_data): void
{
    if(
        !$page_data["is_system"] &&
        !Authentication::isUserLogged() &&
        Settings::get("enable_cache", "main")
    )
    {
        //Skip administrator selected pages types
        $types_to_ignore = unserialize(
            Settings::get("cache_ignore_types", "main")
        );

        if(is_array($types_to_ignore))
        {
            if(in_array($page_data['type'], $types_to_ignore))
            {
                return;
            }
        }

        //Do not cache php pages if not enabled
        if(
            $page_data["input_format"] == "php_code" &&
            !Settings::get("cache_php_pages", "main")
        )
        {
            return;
        }

        //Create database that stores a global md5 timestamp of changes
        if(!Sql::dbExists("cache"))
        {
            $db = Sql::open("cache");

            Sql::query("PRAGMA journal_mode=WAL", $db);

            $query = "create table last_change (id int primary key, value text)";

            Sql::query($query, $db);

            $query = "insert into last_change (id, value) values(1, '" . time() . "')";

            Sql::query($query, $db);

            Sql::close($db);
        }

        $file_updated = false;
        $times_string = "";

        //Check sqlite directory and bypass check on
        //cache and search_engine database
        $databases = FileSystem::getFiles(Site::dataDir() . "sqlite");

        $databases_to_ignore = unserialize(
            Settings::get("cache_ignore_db", "main")
        );

        foreach($databases as $path)
        {
            //Skip administrator selected ignored databases
            if(is_array($databases_to_ignore))
            {
                foreach($databases_to_ignore as $db_name)
                {
                    $full_db_path = Site::dataDir() . "sqlite/" . $db_name;
                    if($path == $full_db_path)
                    {
                        continue 2;
                    }
                }
            }

            if(
                "" . strpos($path, "sqlite/cache") . "" == "" &&
                "" . strpos($path, "sqlite/search_engine") . "" == "" &&
                "" . strpos($path, "sqlite/users") . "" == "" &&
                "" . strpos($path, "sqlite/log") . "" == "" &&
                "" . strpos($path, "sqlite/errors_log") . "" == "" &&
                "" . strpos($path, "sqlite/api_keys") . "" == "" &&
                "" . strpos($path, "sqlite/readme.txt") . "" == ""
            )
            {
                $times_string .= filemtime($path);
            }
        }

        //Check the cache directory timestamp for changes
        $data_dir = Site::dataDir();

        $times_string .= filemtime(Site::dataDir() . "cache_events");

        // Open database
        $db = Sql::open("cache");

        //Calculate times md5
        $times_string = md5($times_string);

        //Obtain current timestamp
        $select = "select value from last_change where id=1";
        $result = Sql::query($select, $db);
        $last_change_data = Sql::fetchArray($result);
        $current_time = $last_change_data["value"];

        if($current_time != $times_string)
        {
            $file_updated = true;
        }

        //Create new timestamp in case a file was updated
        $new_time = time();

        if($file_updated)
        {
            Sql::turbo($db);

            //Update last change timestamp
            $update = "update last_change set
            value='$times_string' where id=1";
            Sql::query($update, $db);

            //Create cache directory if not exists
            if(!file_exists(Site::dataDir() . "cache/"))
            {
                FileSystem::makeDir(Site::dataDir() . "cache/");
            }

            $current_time = $times_string;
        }

        Sql::close($db);

        $cache_file = Site::dataDir() . "cache/" .
            Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
        ;

        $cache_time_file = Site::dataDir() . "cache/" .
            Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
        ;

        //Append get variables to cache name in order to support caching
        //pages like some-results?page=1
        if(count($_GET) > 1)
        {
            $cache_file .= "_get_";
            $cache_time_file .= "_get_";

            foreach($_GET as $name=>$value)
            {
                if($name == "p")
                    continue;

                if(is_array($value))
                {
                    $cache_file .= $name . "_" . implode("_", $value);
                    $cache_time_file .= $name . "_" . implode("_", $value);
                }
                else
                {
                    $cache_file .= $name . "_" . $value;
                    $cache_time_file .= $name . "_" . $value;
                }
            }
        }

        $cache_time_file .= ".time";

        $cache_expire = Settings::get("cache_expire", "main");

        if($cache_expire > 0)
        {
            if(file_exists($cache_time_file))
            {
                $cache_data = Data::get(0, $cache_time_file);

                //Regenerating cache is needed
                if((time() - $cache_data["cache_time"]) >= $cache_expire)
                {
                    return;
                }
            }
        }

        if(file_exists($cache_file))
        {
            $cache_data = Data::get(0, $cache_time_file);
            $page_path = Pages::getPath($uri) . "/data.php";
            $page_time = filemtime($page_path);

            if(
                $cache_data["time"] == $current_time &&
                $cache_data["page_time"] == $page_time
            )
            {
                $file_content = file_get_contents($cache_file);

                Modules::hook("hook_cache_page", $uri, $page_data, $file_content);

                print $file_content;

                if(Settings::get("classic_views_count", "main"))
                {
                    Pages::countView($uri);
                }

                //Print execution stats
                Site::printStats("cache");
                exit;
            }
        }
    }
}

/**
 * Retreive page from cache without any major expiration checkings.
 *
 * @param string $uri Uri of page.
 */
static function fastCacheIfPossible(string $uri): void
{
    if(
        isset($_COOKIE["logged"])
        ||
        isset($_COOKIE["signed"])
        ||
        (
            !Settings::get("enable_cache", "main") ||
            !Settings::get("enable_fast_cache", "main")
        )
    )
    {
        return;
    }

    $cache_file = Site::dataDir() . "cache/" .
        Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
    ;

    //Append get variables to cache name in order to support caching
    //pages like some-results?page=1
    if(count($_GET) > 1)
    {
        $cache_file .= "_get_";

        foreach($_GET as $name=>$value)
        {
            if($name == "p")
                continue;

            if(is_array($value))
            {
                $cache_file .= $name . "_" . implode("_", $value);
            }
            else
            {
                $cache_file .= $name . "_" . $value;
            }
        }
    }

    if(file_exists($cache_file))
    {
        $cache_expire = Settings::get("cache_expire", "main");

        if($cache_expire > 0)
        {
            $page_time = filemtime($cache_file);

            if((time() - $page_time) > $cache_expire)
                return;
        }

        readfile($cache_file);

        if(Settings::get("classic_views_count", "main"))
        {
            //TODO: How can we disable autoloading of this class
            //function Modules::hook(){}

            Pages::countView($uri);
        }

        //Print stats if enabled
        Site::printStats("fast cache");

        exit;
    }
}

/**
 * If cache is enabled creates a cache file for a given page
 * for later fast retreival.
 *
 * @param string $uri The uri of the page to store.
 * @param array $page_data The actual data of the page to store.
 * @param string $content The html output of the page to store.
 *
 * @return string Cached content or empty string if nothing cached.
 */
static function savePageToCacheIfPossible(
    string $uri, array $page_data, string $content
): string
{
    $static_images_generated = Site::$static_images_generated;
    $base_url = Site::$base_url;

    //If static images where generated for the current page we wait to cache
    //on next page run in order to cache the html that points to the static
    //image url's
    if($static_images_generated)
    {
        foreach(Site::$static_images_to_generate as $image_url=>$image_set)
        {
            if(strpos($content, $image_url) !== false)
                return "";
        }
    }

    $page_path = Pages::getPath($uri) . "/data.php";

    //Skip visual uris
    if(!file_exists($page_path))
    {
        return "";
    }

    if(
        !Pages::isSystem($uri) &&
        !Authentication::isUserLogged() &&
        Settings::get("enable_cache", "main")
    )
    {
        //Skip administrator selected pages types
        $types_to_ignore = unserialize(
            Settings::get("cache_ignore_types", "main")
        );

        if(is_array($types_to_ignore))
        {
            foreach($types_to_ignore as $type_name)
            {
                if($type_name == $page_data["type"])
                {
                    return "";
                }
            }
        }

        if(
            $page_data["input_format"] == "php_code" &&
            !Settings::get("cache_php_pages", "main")
        )
        {
            return "";
        }

        $cache_file = Site::dataDir() . "cache/" .
            Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
        ;

        $cache_time_file = Site::dataDir() . "cache/" .
            Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
        ;

        //Append get variables to cache name in order to support caching
        //pages like some-results?page=1
        if(count($_GET) > 1)
        {
            $cache_file .= "_get_";
            $cache_time_file .= "_get_";

            foreach($_GET as $name=>$value)
            {
                if($name == "p")
                    continue;

                if(is_array($value))
                {
                    $cache_file .= $name . "_" . implode("_", $value);
                    $cache_time_file .= $name . "_" . implode("_", $value);
                }
                else
                {
                    $cache_file .= $name . "_" . $value;
                    $cache_time_file .= $name . "_" . $value;
                }
            }
        }

        $cache_time_file .= ".time";

        $db = Sql::open("cache");

        $select = "select value from last_change where id=1";

        $result = Sql::query($select, $db);

        $data = Sql::fetchArray($result);

        Sql::close($db);

        //Don't store host name and protocol on cached html.
        $base_url_path = str_replace(
            array("https://", "http://" . $_SERVER["HTTP_HOST"]),
            array("http://", ""),
            $base_url
        );

        $content = str_replace($base_url, $base_url_path, $content);

        Modules::hook("hook_save_page_to_cache", $uri, $page_data, $content);

        file_put_contents($cache_file, $content);

        $page_time = filemtime($page_path);

        $fields = array();

        $fields["time"] = $data["value"];
        $fields["page_time"] = $page_time;
        $fields["cache_time"] = time();

        Data::edit(0, $fields, $cache_time_file);

        return $content;
    }

    return "";
}

/**
 * Removes a cached page.
 *
 * @param string $uri
 * @param ?array $get_params
 *
 * @return bool True on succes or false if cached page didn't exist.
 */
static function removeCachedPage(string $uri, ?array &$get_params=[]): bool
{
    $cache_file = Site::dataDir() . "cache/" .
        Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
    ;

    $cache_time_file = Site::dataDir() . "cache/" .
        Uri::fromText($_SERVER["HTTP_HOST"] . $uri) . Language::getCurrent()
    ;

    //Append get variables to cache name in order to support caching
    //pages like some-results?page=1
    if(count($get_params) > 1)
    {
        $cache_file .= "_get_";
        $cache_time_file .= "_get_";

        foreach($get_params as $name=>$value)
        {
            if($name == "p")
                continue;

            if(is_array($value))
            {
                $cache_file .= $name . "_" . implode("_", $value);
                $cache_time_file .= $name . "_" . implode("_", $value);
            }
            else
            {
                $cache_file .= $name . "_" . $value;
                $cache_time_file .= $name . "_" . $value;
            }
        }
    }

    $cache_time_file .= ".time";

    if(file_exists($cache_file))
    {
        unlink($cache_file);
        unlink($cache_time_file);

        return true;
    }

    return false;
}

}
