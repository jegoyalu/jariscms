<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris\Pages;

use Jaris\Uri;
use Jaris\Data;
use Jaris\Pages;
use Jaris\System;
use Jaris\Modules;
use Jaris\FileSystem;
use Jaris\Authentication;

/**
 * The functions to manage page files
 */
class Files
{

/**
 * Receives parameters: $uri, $page_data, $file_uri, $file_data
 * @var string
 */
const SIGNAL_PRINT_FILE = "hook_print_file";

/**
 * Adds a new file record to a page.
 *
 * @param array $file_array An array with the needed
 * fields to write to the block.
 * @param string $description A brief description of the file.
 * @param string $page The page where the file reside.
 * @param string $file_name Returns the file name.
 * @param bool $move_file If true source file is moved otherwise is copied.
 *
 * @return string "true" string on success or error message.
 * @original add_file
 */
static function add(
    $file_array, $description, $page = "", &$file_name = null, $move_file = true
)
{
    //TODO: Check file mime type before adding the file.
    $file_data_path = self::getPath($page);

    //Create file directory in case is not present
    $path = str_replace("files.php", "files", $file_data_path);
    if(!file_exists($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    $destination = $path . "/" . $file_array["name"];

    if($move_file)
    {
        $file_name = FileSystem::move($file_array["tmp_name"], $destination);
    }
    else
    {
        $file_name = FileSystem::copy($file_array["tmp_name"], $destination);
    }

    if(!$file_name)
    {
        return System::errorMessage("write_error_data");
    }

    $fields["name"] = $file_name;
    $fields["description"] = $description;
    $fields["size"] = filesize(
        str_replace(
            $file_array["name"], $file_name, $destination
        )
    );
    $fields["mime-type"] = $file_array["type"];

    Data::add($fields, $file_data_path);

    return "true";
}

/**
 * Deletes an existing file record from a file.php file.
 *
 * @param int $id Unique identifier of the file.
 * @param string $page The page uri where the file reside.
 *
 * @return bool True on success false on fail.
 * @original delete_file
 */
static function delete($id, $page)
{
    $file_data_path = self::getPath($page);

    $file_data = self::get($id, $page);

    //For not having problems clean any \n\t and many others
    $file_data["name"] = trim($file_data["name"]);

    $file_file_path = str_replace(
        "files.php",
        "files/{$file_data['name']}",
        $file_data_path
    );

    //Remove file
    if(!unlink($file_file_path))
    {
        return false;
    }

    //Remove file record from files.php data file
    Data::delete($id, $file_data_path);

    return true;
}

/**
 * Deletes an existing file record from a file.php file.
 *
 * @param string $name Name of the file to delete.
 * @param string $page The page uri where the file reside.
 * @original delete_file_by_name
 */
static function deleteByName($name, $page)
{
    $file_data_path = self::getPath($page);

    $files = Data::parse($file_data_path);

    foreach($files as $file_id=>$file)
    {
        if($file["name"] == $name)
        {
            self::delete($file_id, $page);
            break;
        }
    }
}

/**
 * Edits or changes the data of an existing file from a file.php file.
 *
 * @param int $id Unique identifier of the file.
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param string $page The page uri where the file reside.
 *
 * @return bool True on success false on fail.
 * @original edit_file
 */
static function edit($id, $new_data, $page)
{
    $file_data_path = self::getPath($page);

    return Data::edit($id, $new_data, $file_data_path);
}

/**
 * Edit a page file by name.
 *
 * @param string $name Name of the file.
 * @param array $new_data
 * @param string $page The page uri where the file reside.
 *
 * @return bool False if failed to edit data otherwise true.
 */
static function editByName($name, $new_data, $page)
{
    static $page_name, $files;

    $file_data_path = self::getPath($page);

    if($page_name != $page)
    {
        $files = Data::parse($file_data_path);

        $page_name = $page;
    }

    foreach($files as $id=>$file)
    {
        if($file["name"] == $name)
        {
            return Data::edit($id, $new_data, $file_data_path);
        }
    }

    return false;
}

/**
 * Get an array with data of a specific file.
 *
 * @param int $id Unique identifier of the file.
 * @param string $page The page uri where the file reside.
 *
 * @return array All the fields of the file.
 * @original get_file_data
 */
static function get($id, $page)
{
    $file_data_path = self::getPath($page);

    $files = Data::parse($file_data_path);

    return $files[$id];
}

/**
 * Get an array with data of a specific file.
 *
 * @param string $name Name of the file.
 * @param string $page The page uri where the file reside.
 *
 * @return array All the fields of the file.
 * @original get_file_data_by_name
 */
static function getByName($name, $page)
{
    static $page_name, $files;

    if($page_name != $page)
    {
        $file_data_path = self::getPath($page);

        $files = Data::parse($file_data_path);

        $page_name = $page;
    }

    foreach($files as $file)
    {
        if($file["name"] == $name)
        {
            return $file;
        }
    }

    return array();
}

/**
 * Gets the full list of files from the file.php file of a page.
 *
 * @param string $page The page where the file.php file reside.
 *
 * @return array List of files or empty array if no files.
 * @original get_file_list
 */
static function getList($page)
{
    $file_data_path = self::getPath($page);

    $files = Data::parse($file_data_path);

    if(!$files)
    {
        return array();
    }
    else
    {
        return $files;
    }
}

/**
 * Prints a file to the browser using the file uri scheme.
 *
 * @param string $file_uri The file uri on the format
 * file/pageuri/filename_or_fileid
 * @original print_file
 */
static function printIt($file_uri)
{
    //Remove the file/ part
    $uri = substr_replace($file_uri, "", 0, 5);

    $uri = explode("/", $uri);
    unset($uri[count($uri) - 1]);
    $uri = implode("/", $uri);

    $page_data = Pages::get($uri);

    if(!$page_data)
    {
        return;
    }

    if(Pages::userHasAccess($page_data))
    {
        $file_array = Uri::getFilePath($file_uri);

        if(!$file_array)
        {
            return;
        }

        $file_data = self::get($file_array["id"], $file_array["page_uri"]);

        //If file doesnt exist go to home page
        //TODO: Replace home page with file not found page.
        if(!isset($file_array["path"]))
        {
            Uri::go("");
        }

        //Call print file hooks to allow modules customization
        Modules::hook("hook_print_file", $uri, $page_data, $file_uri, $file_data);

        //First reset headers
        header("Pragma: ");   //This one is set to no-cache so we disable it
        header("Cache-Control: ");  //also set to no cache
        header("Last-Modified: ");  //We try to reset to only send one date
        header("Expires: ");   //We try to reset to only send one expiration date
        header("X-Powered-By: "); //We remove the php powered by since we want to pass as normal file
        //Set headers
        header("Content-Disposition: inline; filename=" . urlencode($file_data['name']));
        header("Content-Type: {$file_data['mime-type']}");
        header("Content-Description: File Transfer");

        //Strange problem with swf files not been fully downloaded.
        if("" . strpos($file_data['name'], ".swf") . "" == "")
            header("Content-Length: " . filesize($file_array["path"]));

        //Print file to browser
        ob_clean();
        flush();

        $fp = fopen($file_array["path"], "r");
        while(!feof($fp))
        {
            echo fread($fp, 65536);
            flush();
        }
        fclose($fp);

        exit;
    }
    else
    {
        Authentication::protectedPage();
    }
}

/**
 * Generates the data path where the file database resides.
 *
 * @param string $page The page uri to translate to a valid file.php data path.
 *
 * @return string Path to file containing files list.
 * @original generate_file_path
 */
static function getPath($page)
{
    $file_data_path = Pages::getPath($page) . "/files.php";

    return $file_data_path;
}

}
