<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * The functions to manage content input formats.
 */
class InputFormats
{

/**
 * Receives parameters: $name, $fields
 * @var string
 */
const SIGNAL_ADD_INPUT_FORMAT = "hook_add_input_format";

/**
 * Receives parameters: $name, $fields
 * @var string
 */
const SIGNAL_EDIT_INPUT_FORMAT = "hook_edit_input_format";

/**
 * Adds a new content input format.
 *
 * @param string $name The machine readable name of the input format.
 * @param array $fields An array with the needed fields to write
 * to the input format.
 *
 * @return string "true" string on success error message on fail.
 */
static function add(string $name, array $fields): string
{
    $input_format_data_path = self::getPath($name);

    //Create input_formats directory in case is not present
    $path = str_replace("$name.php", "", $input_format_data_path);
    if(!file_exists($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    //Check if input format already exist.
    if(file_exists($input_format_data_path))
    {
        return System::errorMessage("input_format_exist");
    }

    //Call add_input_format hook before creating the category
    Modules::hook("hook_add_input_format", $name, $fields);

    if(!Data::add($fields, $input_format_data_path))
    {
        return System::errorMessage("write_error_data");
    }

    return "true";
}

/**
 * Deletes an existing content input format.
 *
 * @param string $name Machine name of the input format.
 *
 * @return string "true" string on success error message on fail.
 */
static function delete(string $name): string
{
    $input_format_data_path = self::getPath($name);

    if(!unlink($input_format_data_path))
    {
        return System::errorMessage("write_error_data");
    }

    return "true";
}

/**
 * Edits or changes the data of an existing input format.
 *
 * @param string $name The machine name of the input format.
 * @param array $fields Array with all the new values of the input format.
 *
 * @return bool True on success false or fail.
 */
static function edit(string $name, array $fields): bool
{
    $input_format_data_path = self::getPath($name);

    //Call add_input_format hook before creating the category
    Modules::hook("hook_edit_input_format", $name, $fields);

    return Data::edit(0, $fields, $input_format_data_path);
}

/**
 * Get an array with data of a specific content input format.
 *
 * @param string $name Machine name of the input format.
 *
 * @return array An array with all the fields of the input format.
 */
static function get(string $name): array
{
    $input_format_data_path = self::getPath($name);

    $input_format = Data::parse($input_format_data_path);

    return $input_format[0];
}

/**
 * Gets the list of available content input formats.
 *
 * @return array Array with all input formats in the format
 * input_format["machine name"] = array(
 *  "name"=>"string",
 *  "description"=>"string",
 *  "parse_url"=> bool,
 *  "parse_line_breaks"=>bool
 * )
 * or null if no input format found.
 */
static function getList(): array
{
    if(!file_exists(Site::dataDir() . "types/input_formats"))
    {
        FileSystem::makeDir(Site::dataDir() . "types/input_formats");
    }

    $dir = opendir(Site::dataDir() . "types/input_formats");

    $input_formats = array();

    if(file_exists(Site::dataDir() . "types/input_formats"))
    {
        while(($file = readdir($dir)) !== false)
        {
            if(
                $file != "." &&
                $file != ".." &&
                !is_dir(Site::dataDir() . "types/input_formats/$file")
            )
            {
                $machine_name = str_replace(".php", "", $file);
                $input_formats[$machine_name] = self::get(
                    $machine_name
                );
            }
        }
    }

    closedir($dir);

    return $input_formats;
}

/**
 * Links parser.
 *
 * @param string $text The input used to parse links.
 *
 * @return string Text with links turned to html.
 */
static function parseLinks(string $text): string
{
    $pattern = "/https?:\/\/(\w*:\w*@)?[-\w.]+(:\d+)?(\/([-\w\/_.]*(\?\S+)?)?)?/";
    preg_match_all($pattern, $text, $matches);

    foreach($matches[0] as $match)
    {
        $match = trim($match);
        $html = "<a target=\"_blank\" href=\"$match\">$match</a>";
        $text = str_replace($match, $html, $text);
    }

    return $text;
}

/**
 * Emails parser.
 *
 * @param string $text The input used to parse emails.
 *
 * @return string Text with emails turned to html.
 */
static function parseEmails(string $text): string
{
    $pattern = "/(\w+\.)*\w+@(\w+\.)+[A-Za-z]+/";
    preg_match_all($pattern, $text, $matches);

    foreach($matches[0] as $match)
    {
        $html = "<a href=\"mailto:$match\">$match</a>";
        $text = str_replace($match, $html, $text);
    }

    return $text;
}

/**
 * Line breaks parser.
 *
 * @param string $text The input used to parse line breaks.
 *
 * @return string Text with \n turned to <br />.
 */
static function parseLineBreaks(string $text): string
{
    return nl2br($text);
}

/**
 * For retrieving all the system input formats to process data.
 *
 * @return array Array in the format
 * $input_formats["machine_name"] = array("title", "description")
 */
static function getAll(): array
{
    $input_formats = array(
        "full_html" => array(
            "title" => t("Full HTML"),
            "description" => t("Supports all html tags")
        ),
        "php_code" => array(
            "title" => t("PHP Code"),
            "description" => t("For executing php code with no filtering.")
        )
    );

    $input_formats_array = self::getList();

    foreach($input_formats_array as $machine_name => $data)
    {
        $input_formats[$machine_name] = array(
            "title" => t($data["name"]),
            "description" => t($data["description"])
        );
    }

    return $input_formats;
}

/**
 * For filtering data given a specific input format.
 *
 * @param string $data The data to filter.
 * @param string $input_format The format in wich to filter
 * the data, full_html or php_code.
 *
 * @return string The filtered data.
 */
static function filter(string $data, string $input_format): string
{
    static $input_formats_array;

    if(!is_array($input_formats_array))
        $input_formats_array = array();

    if($input_format == "")
        $input_format = "full_html";

    switch($input_format)
    {
        case "full_html":
            return $data;
        case "php_code":
            return System::evalPHP($data);
        default:
            if(!$input_formats_array[$input_format])
            {
                $input_formats_array[$input_format] = self::get(
                    $input_format
                );
            }

            $data = Util::stripHTMLTags(
                $data,
                $input_formats_array[$input_format]["allowed_tags"],
                $input_formats_array[$input_format]["allowed_atts"]
            );

            if($input_formats_array[$input_format]["parse_url"])
            {
                $data = self::parseLinks($data);
            }

            if($input_formats_array[$input_format]["parse_email"])
            {
                $data = self::parseEmails($data);
            }

            if($input_formats_array[$input_format]["parse_line_breaks"])
            {
                $data = self::parseLineBreaks($data);
            }

            return $data;
    }
}

/**
 * Generates the data path where content input format information resides.
 *
 * @param string $name The machine name of the content input format.
 *
 * @return string Path to input format file.
 */
static function getPath(string $name): string
{
    if(!file_exists(Site::dataDir() . "types/input_formats"))
    {
        FileSystem::makeDir(Site::dataDir() . "types/input_formats");
    }

    $input_format_path = Site::dataDir() . "types/input_formats/$name.php";

    return $input_format_path;
}

}