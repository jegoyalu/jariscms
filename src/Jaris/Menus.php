<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 General License Protecting Programmers
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to manage the menu system.
 */
class Menus
{

/**
 * Creates the data file for a menu.
 *
 * @param string $menu_name The name to give to the menu
 * with [a-z](-) characters only.
 *
 * @return string "true" on success or error message.
 * @original create_menu
 */
static function add($menu_name)
{
    $menu_file = self::getPath($menu_name);

    if(file_exists($menu_file))
    {
        return System::errorMessage("menu_exist");
    }

    //Create an empty menu file and supress invalid array warning
    if(@!Data::write(null, $menu_file))
    {
        return System::errorMessage("write_error_data");
    }

    return "true";
}

/**
 * Deletes a menu file.
 *
 * @param string $menu_name The name of the file to delete.
 *
 * @return bool True on success or false on fail.
 * @original delete_menu
 */
static function delete($menu_name)
{
    $menu_file = self::getPath($menu_name);

    if(!unlink($menu_file))
    {
        return false;
    }

    return true;
}

/**
 * Renames a menu file
 *
 * @param string $actual_name The actual menu file name.
 * @param string $new_name The new name given to the file.
 *
 * @return string "true" string on success or error message.
 * @original rename_menu
 */
static function rename($actual_name, $new_name)
{
    $actual_path = self::getPath($actual_name);

    $new_path = self::getPath($new_name);

    if(file_exists($new_path))
    {
        return System::errorMessage("menu_exist");
    }

    if(!rename($actual_path, $new_path))
    {
        return System::errorMessage("write_error_data");
    }

    return "true";
}

/**
 * Gets all the menu files available on the system.
 *
 * @return array The name of all existing menu files.
 * @original get_menu_list
 */
static function getList()
{
    $menu_dir = opendir(Site::dataDir() . "menus");

    $menus = array();
    while(($menu = readdir($menu_dir)) !== false)
    {
        if(filetype(Site::dataDir() . "menus/" . $menu) == "file")
        {
            $menus[] = str_replace(".php", "", $menu);
        }
    }

    closedir($menu_dir);

    return $menus;
}

/**
 * Adds a new menu item to a menu file.
 *
 * @param string $menu_name Where the new menu item is going to be added.
 * @param array $fields An array with the needed fields to write to the block.
 *
 * @return bool True on success or false on fail.
 * @original add_menu_item
 */
static function addItem($menu_name, $fields)
{
    $menu_data_path = self::getPath($menu_name);

    return Data::add($fields, $menu_data_path);
}

/**
 * Deletes an existing menu item from a menu file.
 *
 * @param int $id Unique identifier of the menu item.
 * @param string $menu_name The menu that contains the item.
 *
 * @return bool True on success false on fail.
 * @original delete_menu_item
 */
static function deleteItem($id, $menu_name)
{
    $menu_data_path = self::getPath($menu_name);

    return Data::delete($id, $menu_data_path);
}

/**
 * Edits or changes the data of an existing menu item from a menu file.
 *
 * @param int $id Unique identifier of the menu.
 * @param string $menu_name The menu were the item resides
 * @param array $new_data An array of the fields that will
 * substitue the old values.
 *
 * @return true on success false on fail.
 * @original edit_menu_item
 */
static function editItem($id, $menu_name, $new_data)
{
    $menu_data_path = self::getPath($menu_name);

    return Data::edit($id, $new_data, $menu_data_path);
}

/**
 * Get an array with data of a specific menu item.
 *
 * @param int $id Unique identifier of the menu item.
 * @param string $menu_name The menu where the item resides.
 *
 * @return array An array with all the fields of the menu.
 * @original get_menu_item_data
 */
static function getItem($id, $menu_name)
{
    $menu_data_path = self::getPath($menu_name);

    $menu = Data::parse($menu_data_path);

    return $menu[$id];
}

/**
 * Gets the full list of menu items from a file.
 *
 * @param string $menu_name The menu where the menu items reside.
 *
 * @return array List of menu items.
 * @original get_menu_items_list
 */
static function getItemsList($menu_name)
{
    static $menu_array = array();

    if(!isset($menu_array[$menu_name]))
    {
        $menu_data_path = self::getPath($menu_name);

        $menu_array[$menu_name] = Data::parse($menu_data_path);
    }

    return $menu_array[$menu_name];
}

/**
 * Recursive static function that returns the sub menu items of a menu item.
 *
 * @param string $menu_name the name of the menu.
 * @param int|string $parent_id The id of the parent item.
 *
 * @return array The parent item with its sub items and also the sub
 * items of the sub items in another array. For example:
 * $parent_item = array(..., menu_item_values, ..., "sub_items"=>array())
 * @original get_sub_menu_items
 */
static function getChildItems($menu_name, $parent_id = "root")
{
    $menu_items = self::getItemsList($menu_name);

    $menu = array();
    foreach($menu_items as $id => $items)
    {
        if(!isset($menu_items[$items["parent"]]))
            $items["parent"] = "root";

        if("" . $items["parent"] . "" == "" . $parent_id . "")
        {
            //get the sub items of this item
            $sub_items["sub_items"] = Data::sort(
                self::getChildItems($menu_name, $id),
                "order"
            );

            if(count($sub_items["sub_items"]) > 0)
            {
                $items += $sub_items;
            }

            $menu[$id] = $items;
        }
    }

    return $menu;
}

/**
 * Gets the machine name of the primary menu.
 *
 * @return string Name of primary menu.
 * @original get_primary_menu_name
 */
static function getPrimaryName()
{
    $name = Settings::get("primary_menu", "main");

    if($name)
    {
        return $name;
    }

    return "primary";
}

/**
 * Gets the machine name of the secondary menu.
 *
 * @return string Name of secondary menu.
 * @original get_secondary_menu_name
 */
static function getSecondaryName()
{
    $name = Settings::get("secondary_menu", "main");

    if($name)
    {
        return $name;
    }

    return "secondary";
}

/**
 * Generates the data path where the menu resides.
 *
 * @param string $menu The name of the menu file.
 *
 * @return string path to menu file.
 * @original generate_menu_path
 */
static function getPath($menu)
{
    $menu_path = Site::dataDir() . "menus/";

    $menu_path .= $menu . ".php";

    return $menu_path;
}

}