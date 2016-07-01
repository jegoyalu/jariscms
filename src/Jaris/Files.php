<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to handle global files.
 */
class Files
{
    
/**
 * Stores a new uploaded file for public access in the "files" directory
 * with the current site hostname appended, eg: files/mysite.com/myfile.txt
 *
 * @param string $source Current location of file to add.
 * @param string $filename Name used to save the file.
 * @param string $sub_path append a path after the hostname,
 * eg: files/mysite.com/some_path/myfile.txt
 * @param bool $move_file If true source file is moved otherwise is copied.
 *
 * @return string Name of the stored file on success or empty string on fail.
 * @original global_files_add
 */
static function add($source, $filename, $sub_path="", $move_file = true)
{
    $path = self::getDir($sub_path);

    // Create file directory in case is not present
    if(!is_dir($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    $destination = $path . $filename;

    if($move_file)
    {
        $file_name = FileSystem::move($source, $destination);
    }
    else
    {
        $file_name = FileSystem::copy($source, $destination);
    }

    if(!$file_name)
    {
        return "";
    }

    return $file_name;
}

/**
 * Stores a new uploaded file for public access in the "files" directory
 * with the current site hostname appended, eg: files/mysite.com/myfile.txt
 *
 * @param array $file_array An array as that created for $_FILES.
 * @param string $sub_path append a path after the hostname,
 * eg: files/mysite.com/some_path/myfile.txt
 * @param bool $move_file If true source file is moved otherwise is copied.
 *
 * @return string Name of the stored file on success or empty string on fail.
 * @original global_files_add_upload
 */
static function addUpload($file_array, $sub_path="", $move_file = true)
{
    $path = self::getDir($sub_path);

    // Create file directory in case is not present
    if(!is_dir($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    $destination = $path . $file_array["name"];

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
        return "";
    }

    return $file_name;
}

/**
 * Deletes a global file.
 * @param string $name
 * @param string $sub_path
 * @return bool
 * @original global_files_delete
 */
static function delete($name, $sub_path="")
{
    $path = self::getDir($sub_path);

    if(file_exists($path . $name))
        return unlink($path . $name);

    return true;
}

/**
 * Get a full path to a global file.
 * @param string $name
 * @param string $sub_path
 * @original global_files_get
 */
static function get($name, $sub_path="")
{
    $path = self::getDir($sub_path);

    if(file_exists($path . $name))
        return $path . $name;

    return "";
}

/**
 * Get the path where public access files reside for current site.
 * @param string $sub_path Append a sub-path to the retrieved path.
 * @original global_files_directory
 */
static function getDir($sub_path="")
{
    $sub_path = rtrim($sub_path, "/");

    if($sub_path != "")
        $sub_path .= "/";

    $site = Site::current();

    $path = "sites/" . $site;

    if(is_dir($path))
    {
        return $path . "/files/$sub_path";
    }

    return "sites/default/files/$sub_path";
}

}