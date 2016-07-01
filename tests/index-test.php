<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Test to execute a jariscms page and store the rendered page
 * for inspection.
 */

if(php_sapi_name() != "cli")
    exit;

chdir(__DIR__ . "/../");

// Read the parameters passed as first command line option
// for example: php index-tests.php "p=admin/user&var=123"
parse_str($argv[1], $_GET);
parse_str($argv[1], $_REQUEST);

$_SERVER["HTTP_HOST"] = "localhost";

// Set error handling
error_reporting(E_ALL);

set_error_handler(
    function ($errno, $errmsg, $filename, $linenum, $vars)
    {
        static $errortype=null;

        // Skip errors that are supressed by a @
        if(error_reporting() === 0)
        {
            return true;
        }

        //Since searching for translation is slow with do it just once.
        if(!is_array($errortype))
        {
            $errortype = array(
                E_ERROR => 'Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parsing Error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_STRICT => 'Runtime Notice',
                E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
            );
        }

        // Comment the if line in order to uncover more issues like
        // trying to access uninitialized variables, etc...
        //if($errno != E_NOTICE)
        {
            $output = $errortype[$errno] . " - ($errmsg)"
                . " in " . $filename . " on line " . $linenum
            ;

            if(strpos($output, "eval()") !== false)
            {
                print "<strong style=\"color: red;\">"
                    . $output . " - " . $_REQUEST["p"]
                    . "</strong>"
                ;

                $output .= " - " . $_REQUEST["p"];
            }

            fwrite(STDOUT, $output . "\n");
        }

        // Don't execute PHP internal error handler
        return true;
    }
);


//Time when script started executing useful to count page execution
//time on the theme.
$time_start = microtime(true);

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//Shorthand functions commonly used on legacy templates
require 'src/Aliases.php';

//Initialize settings.
Jaris\Site::init();

//Try to do a fast cache page retreival
Jaris\System::fastCacheIfPossible(Jaris\Uri::get());

//Load installed modules
Jaris\Site::loadModules();

//Starts the main session for the user
session_start();

// Set server and session variables to proper values for testing
$_SESSION["logged"]["username"] = "test";
$_SESSION["logged"]["site"] = "localhosttests";
$_SESSION["logged"]["password"] = "test";
$_SESSION["logged"]["user_agent"] = "test";
$_SERVER["HTTP_USER_AGENT"] = "test";
$_SESSION["logged"]["group"] = "administrator";

//Initialize error handler (disable since we use a custom one to get errors)
//System::initiateErrorCatchSystem();

//Sets the language based on user selection or system default
Jaris\Site::$language = Jaris\Language::getCurrent();

//Sets the page that is going to be displayed
$page = Jaris\Uri::get();

//Stores the uri that end users will see even if the $page is changed by
//show_category_results() sinces template functions need the visual uri
//not the content uri
$visual_uri = Jaris\Uri::get();

//Skips all the data procesing if image or file and display it.
$page_type = Jaris\Uri::type($page);
if($page_type == "image")
{
    $image_path = Jaris\Uri::getImagePath($page);
    Jaris\Images::show($image_path);
}
elseif($page_type == "user_picture")
{
    Jaris\Images::printUserPic($page);
}
elseif($page_type == "user_profile")
{
    Jaris\Users::showProfile($page);
}
elseif($page_type == "file")
{
    Jaris\Pages\Files::printIt($page);
}
elseif($page_type == "category")
{
    Jaris\Categories::showResults($page);
}

//Call initialization hooks so modules can make things before page is rendered
Jaris\Modules::hook("hook_initialization");

//Read page data
Jaris\Site::$page_data[0] = Jaris\Pages::get($page, Jaris\Site::$language);

//Call page data hooks so modules can modify page_data content
Jaris\Modules::hook("hook_page_data", Jaris\Site::$page_data);

//Check if the current user can view the current content
if(!Jaris\Pages::userHasAccess(Jaris\Site::$page_data[0]))
{
    Jaris\Uri::go("access-denied");
}

// If rendering mode is set skip directly to printing the page content.
if(
    isset(Jaris\Site::$page_data[0]["rendering_mode"]) &&
    Jaris\Site::$page_data[0]["is_system"]
)
{
    $special_rendering = false;

    //Set adequate content type and enconding
    switch(Jaris\Site::$page_data[0]["rendering_mode"])
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
        print Jaris\System::evalPHP(Jaris\Site::$page_data[0]['content']);
        exit;
    }
}

//Append hidden parameters to $_REQUEST for the correct execution of breadcrumbs
//TODO Fix an issue that keeps hidden parameters stored.
Jaris\System::appendHiddenParameters();

//Read blocks
$header_data = Jaris\Data::sort(
    Jaris\Data::parse(
        Jaris\Language::dataTranslate(
            Jaris\Site::dataDir() . "blocks/header.php"
        )
    ),
    "order"
);

$footer_data = Jaris\Data::sort(
    Jaris\Data::parse(
        Jaris\Language::dataTranslate(
            Jaris\Site::dataDir() . "blocks/footer.php"
        )
    ),
    "order"
);

$left_data = Jaris\Data::sort(
    Jaris\Data::parse(
        Jaris\Language::dataTranslate(
            Jaris\Site::dataDir() . "blocks/left.php"
        )
    ),
    "order"
);

$right_data = Jaris\Data::sort(
    Jaris\Data::parse(
        Jaris\Language::dataTranslate(
            Jaris\Site::dataDir() . "blocks/right.php"
        )
    ),
    "order"
);

$center_data = Jaris\Data::sort(
    Jaris\Data::parse(
        Jaris\Language::dataTranslate(
            Jaris\Site::dataDir() . "blocks/center.php"
        )
    ),
    "order"
);

//Read menus
$primary_links_data = Jaris\Data::sort(
    Jaris\Menus::getChildItems(Jaris\Menus::getPrimaryName()),
    "order"
);

$secondary_links_data = Jaris\Data::sort(
    Jaris\Menus::getChildItems(Jaris\Menus::getSecondaryName()),
    "order"
);

//Move blocks to other positions depending on current theme
Jaris\Blocks::moveByTheme(
    $header_data,
    $left_data,
    $right_data,
    $center_data,
    $footer_data
);

//In case of page not found
if(!Jaris\Site::$page_data[0])
{
    Jaris\Site::$page_data = Jaris\System::pageNotFound();
}

//Format Data
$content = Jaris\View::getContentHTML(Jaris\Site::$page_data, $visual_uri);
$header = Jaris\View::getBlocksHTML($header_data, "header", $visual_uri);
$footer = Jaris\View::getBlocksHTML($footer_data, "footer", $visual_uri);
$left = Jaris\View::getBlocksHTML($left_data, "left", $visual_uri);
$right = Jaris\View::getBlocksHTML($right_data, "right", $visual_uri);
$center = Jaris\View::getBlocksHTML($center_data, "center", $visual_uri);
$primary_links = Jaris\View::getLinksHTML($primary_links_data, "primary-links");
$secondary_links = Jaris\View::getLinksHTML($secondary_links_data, "secondary-links");

//Adds edit link on every page if administrator is logged.
Jaris\System::addEditTab(Jaris\Site::$page_data[0]);

//Set the page title
if(
    Jaris\Pages::isSystem(false, Jaris\Site::$page_data[0]) ||
    ($page_type == "category" && $page == "search") ||
    $page == "user"
)
{
    //Parse the title if is system page
    Jaris\Site::$title = t(Jaris\System::evalPHP(Jaris\Site::$page_data[0]["title"]))
        . " - " . t(Jaris\Site::$title)
    ;
}
else
{
    //Just translate if not system page
    if(trim(Jaris\Site::$page_data[0]["meta_title"]) != "")
    {
        //If meta title is available use it
        Jaris\Site::$title = t(Jaris\Site::$page_data[0]["meta_title"]);
    }
    else
    {
        Jaris\Site::$title = t(Jaris\Site::$page_data[0]["title"])
            . " - " . t(Jaris\Site::$title)
        ;
    }
}

//Display Page and generate cache if enabled and possible
$page_html = Jaris\View::render(
    $visual_uri,
    Jaris\Site::$page_data[0],
    $content,
    $left,
    $center,
    $right,
    $header,
    $footer
);

print "\n-HTML-\n";

print $page_html;

Jaris\Site::printStats();