<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris\Pages;

use Jaris\Site;
use Jaris\Data;
use Jaris\Pages;
use Jaris\System;
use Jaris\Settings;
use Jaris\FileSystem;

/**
 * The functions to manage images for a page.
 */
class Images
{

/**
 * Adds a new image record to a image file.
 *
 * @param array $file_array An array with the needed fields to write to
 * the block in the format returned by the php $_FILES["file"] array.
 * @param string $description A description of the image to store.
 * @param string $page The page where the image reside.
 * @param string $file_name Reference to the file name assigned to the image.
 * @param bool $move_file If true source file is moved otherwise is copied.
 *
 * @return string "true" string on success or message error when failed.
 * @original add_image
 */
static function add(
    $file_array, $description, $page = "", &$file_name = null, $move_file = true
)
{
    $image_data_path = self::getPath($page);

    if(
        $file_array["type"] == "image/png" ||
        $file_array["type"] == "image/jpeg" ||
        $file_array["type"] == "image/pjpeg" ||
        $file_array["type"] == "image/gif"
    )
    {
        //Create image directory in case is not present
        $path = str_replace("images.php", "images", $image_data_path);
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

        //
        if(!$file_name)
        {
            return System::errorMessage("write_error_data");
        }

        $fields["name"] = $file_name;
        $fields["description"] = $description;
        $fields["order"] = 0;

        if(Data::add($fields, $image_data_path))
        {
            return "true";
        }
        else
        {
            return System::errorMessage("write_error_data");
        }
    }
    else
    {
        return System::errorMessage("image_file_type");
    }
}

/**
 * Deletes an existing image record from a image.php file.
 *
 * @param int $id Unique identifier of the image.
 * @param string $page The page uri where the image reside.
 *
 * @return bool True on success or false if failed.
 * @original delete_image
 */
static function delete($id, $page)
{
    $image_data_path = self::getPath($page);

    $image_data = self::get($id, $page);

    //For not having problems clean any \n\t and many others
    $image_data["name"] = trim($image_data["name"]);

    $image_file_path = str_replace(
        "images.php",
        "images/{$image_data['name']}",
        $image_data_path
    );

    //Remove Original Image
    if(!unlink($image_file_path))
    {
        //If this doesnt return false the everything should go right since it has
        //the permissions to delete the file
        return false;
    }

    //Remove cached images
    $image_path = str_replace("/", "-", $page);
    $image_name = $image_data['name'];

    if(Settings::get("image_static_serving", "main"))
    {
        $image_name_parts = explode(".", $image_name);

        if(($image_parts_count = count($image_name_parts)) > 1)
        {
            unset($image_name_parts[$image_parts_count-1]);

            $image_name = implode(".", $image_name_parts);
        }

        FileSystem::search(
            rtrim(\Jaris\Files::getDir("static_image"), "/"),
            "/$image_path-($id|$image_name).*/",
            function($full_path, &$stop_search)
            {
                unlink($full_path);
            }
        );
    }
    else
    {
        FileSystem::search(
            Site::dataDir() .
            "image_cache",
            "/image-$image_path-($id|$image_name).*/",
            function($full_path, &$stop_search)
            {
                unlink($full_path);
            }
        );
    }

    //Remove the image record from the image.php data file
    Data::delete($id, $image_data_path);

    return true;
}

/**
 * Deletes an existing image record from a data.php file.
 *
 * @param string $name Name of the image to delete.
 * @param string $page The page uri where the image reside.
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
 * Edits or changes the data of an existing image from a image.php file.
 *
 * @param int $id Unique identifier of the image.
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param string $page The page uri where the image reside.
 *
 * @return bool True on success false on fail.
 * @original edit_image
 */
static function edit($id, $new_data, $page)
{
    $image_data_path = self::getPath($page);

    return Data::edit($id, $new_data, $image_data_path);
}

/**
 * Edit a page image by name.
 *
 * @param string $name Name of the image.
 * @param array $new_data
 * @param string $page The page uri where the image reside.
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
 * Get an array with data of a specific image.
 *
 * @param int &$id Unique identifier or name of the image. If
 * the name of image is given instead of uniq identifier then this
 * parameter value is modified with the uniq identifier.
 * @param string $page The page uri where the image reside.
 *
 * @return array An array with all the fields of the image.
 * @original get_image_data
 */
static function get(&$id, $page)
{
    $image_data_path = self::getPath($page);

    $images = Data::parse($image_data_path);

    if(!$images)
    {
        return array();
    }

    if(!isset($images[$id]))
    {
        foreach($images as $image_id=>$image_data)
        {
            if($image_data["name"] == $id)
            {
                $id = $image_id;
                return $image_data;
            }
        }
    }

    return $images[$id];
}

/**
 * Get an array with data of a specific image.
 *
 * @param string $name Name of the image.
 * @param string $page The page uri where the image reside.
 *
 * @return array All the fields of the image.
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
 * Gets the full list of images from the image.php file of a page.
 *
 * @param string $page The page where the image.php file reside.
 *
 * @return array Array of images or empty array if empty.
 * @original get_image_list
 */
static function getList($page)
{
    $image_data_path = self::getPath($page);

    $images = Data::parse($image_data_path);

    if($images)
    {
        return Data::sort($images, "order");
    }

    return array();
}

/**
 * Generates the data path where the image resides.
 *
 * @param string $path The page uri to translate to a valid image.php data path.
 *
 * @return string Path to image file example: data/pages/singles/home/images.php
 * @original generate_image_path
 */
static function getPath($path)
{
    $image_data_path = Pages::getPath($path) . "/images.php";

    return $image_data_path;
}

}