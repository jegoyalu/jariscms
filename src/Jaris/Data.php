<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to manage and parse php files with a special syntax that
 * is used as a simple data storage system. This files are used to store
 * pages, menus, settings, blocks, categories, etc...
 *
 * The syntax of this files is as follow:
 *
 * ============================================================================
 * <?php exit; ?>   //This line prevent some one else from viewing the content
 *
 * row: 0           //Represents a row in the data file of id 0
 *
 *  field: myfield  //Starts a field stored on row 0 with name myfield
 *      some value  //Represents the value of the field myfield
 *  field;          //Terminates a field declaration
 *
 * row;             //Ends the row declaration.
 * ============================================================================
 */
class Data
{

/**
 * Holds a list of files that have been locked to prevent editing them.
 * @var array
 */
private static $m_dataLockedFiles;

/**
 * Parses a file that has the custom php data file syntax.
 *
 * @param string $file The path of the file to parse.
 * @param bool $lock_wait Lock the parsed file until parser process
 * finishes and unlocks it just befor returning the result.
 * @param bool $cache Generate data cache file if possible. Useful
 * to set as false when calling data_writer after data_parser so
 * the data cache file isn't written twice in a single process.
 *
 * @return array All the rows with a subarray of it fields
 * in the format row[id] = array(field_name=>value) or empty array if error.
 * @original data_parser
 */
static function parse($file, $lock_wait=true, $cache=true)
{
    if(!file_exists($file))
    {
        return array();
    }

    $cache_file = "";

    $is_cache_enabled = is_dir(Site::dataDir() . "data_cache");

    //If data is stored serialized get that instead since it is faster
    //than reparsing. This is specially useful for less powered embedded devices
    if($is_cache_enabled)
    {
        if(function_exists("apc_store"))
        {
            $cache_file = Uri::fromText(realpath($file));

            $data = apc_fetch($cache_file);

            if(is_array($data))
            {
                return $data;
            }
        }
        else
        {
            $cache_file = Site::dataDir() . "data_cache/" .
                Uri::fromText(str_replace(Site::dataDir(), "", $file))
            ;

            if(file_exists($cache_file))
            {
                self::lock($cache_file);
                $data = unserialize(file_get_contents($cache_file));
                self::unlock($cache_file);

                return $data;
            }
        }
    }

    if($lock_wait)
    {
        //In case file is been written wait to not get empty content
        self::lock($file);
        $arrFile = file($file);
        self::unlock($file);
    }
    else
    {
        $arrFile = file($file);
    }

    $row = array();

    $insideRow = false;
    $insideField = false;
    $currentRow = "";
    $currentField = "";

    $lines = count($arrFile);

    for($i = 0; $i < $lines; ++$i)
    {
        $line_trim = trim($arrFile[$i]);

        if($insideField)
        {
            if($line_trim == "field;")
            {
                $insideField = false;

                $row[$currentRow][$currentField] = rtrim(
                    $row[$currentRow][$currentField]
                );
            }
            else
            {
                $field = preg_replace("/^([\t]{2}|[ ]{8})/", "", $arrFile[$i], 1);

                $field = str_replace("\\field;", "field;", $field);

                $row[$currentRow][$currentField] .= $field;
            }
        }
        else if($insideRow)
        {
            if(substr($line_trim, 0, 6) == "field:")
            {
                $currentField = ltrim(substr($line_trim, 6));
                $insideField = true;

                $row[$currentRow][$currentField] = "";
            }
            else if($line_trim == "row;")
            {
                $insideRow = false;
            }
        }
        else if(!$insideRow)
        {
            if(substr($line_trim, 0, 4) == "row:")
            {
                $currentRow = ltrim(substr($line_trim, 4));
                $insideRow = true;
            }
        }
    }

    unset($arrFile);

    //Store retrieved data in serialized form for faster retreival next time
    //This is specially useful for less powered embedded devices
    if($is_cache_enabled && $cache)
    {
        if(function_exists("apc_store"))
        {
            apc_store($cache_file, $row);
        }
        else
        {
            self::lock($cache_file, LOCK_EX);
            file_put_contents($cache_file, serialize($row));
            self::unlock($cache_file);
        }
    }

    return $row;
}

/**
 * Writes a php data file from a given array.
 *
 * @param array $data With the format
 * array[row_number] = array("field_name"=>"field_value")
 * used to populate the content of the file.
 * @param string $file The path of the file to write on.
 * @param callable $callback A static function to call after locking the file
 * for more precise modification of data. The static function should be as follows:
 * callback(&$data)
 *
 * @return bool false if failed to write data otherwise true.
 * @original data_writer
 */
static function write($data, $file, $callback=null)
{
    //Register cache invalid by touching the cache_events dir
    $data_dir = Site::dataDir();

    $cache_invlidate = array(
        $data_dir . "blocks/",
        $data_dir . "categories/",
        $data_dir . "groups/",
        $data_dir . "language/",
        $data_dir . "menus/",
        $data_dir . "modules/",
        $data_dir . "settings/",
        $data_dir . "types/",
        $data_dir . "modules/"
    );

    foreach($cache_invlidate as $data_path)
    {
        if(strstr($file, $data_path) !== false)
        {
            touch($data_dir . "cache_events");
            break;
        }
    }

    //Wait if file is been modified
    self::lock($file, LOCK_EX);

    if($callback != null)
    {
        $callback($data);
    }

    //For security we place this at the top of the file to
    //make it unreadable by external users
    $content = "<?php exit; ?>\n\n\n";

    foreach($data as $row => $fields)
    {
        $content .= "row: $row\n\n";

        foreach($fields as $name => $value)
        {
            $value = str_replace(
                array("\n", "field;"),
                array("\n\t\t", "\\field;"),
                $value
            );

            $content .= "\tfield: $name\n";
            $content .= "\t\t" . trim($value);
            $content .= "\n\tfield;\n\n";
        }

        $content .= "row;\n\n\n";
    }

    if(!file_put_contents($file, $content))
    {
        //Unlock file
        self::unlock($file);

        return false;
    }

    //Store data in serialized form for faster reads by data_parser() function
    //This is specially useful for less powered embedded devices
    if(is_dir(Site::dataDir() . "data_cache"))
    {
        if(function_exists("apc_store"))
        {
            $cache_file = Uri::fromText(realpath($file));

            apc_store($cache_file, $data);
        }
        else
        {
            $cache_file = Site::dataDir() . "data_cache/" .
                Uri::fromText(str_replace(Site::dataDir(), "", $file))
            ;

            self::lock($cache_file, LOCK_EX);
            file_put_contents($cache_file, serialize($data));
            self::unlock($cache_file);
        }
    }

    //Unlock file
    self::unlock($file);

    return true;
}

/**
 * Has the same purpose of data_writer() but you are responsible
 * of locking before calling it and unlocking after calling it.
 *
 * @param array $data With the format
 * array[row_number] = array("field_name"=>"field_value")
 * used to populate the content of the file.
 * @param string $file The path of the file to write on.
 *
 * @return bool false if failed to write data otherwise true.
 * @original data_writer_no_lock
 */
static function writeNoLock($data, $file)
{
    //Register cache invalid by touching the cache_events dir
    $data_dir = Site::dataDir();

    $cache_invlidate = array(
        $data_dir . "blocks/",
        $data_dir . "categories/",
        $data_dir . "groups/",
        $data_dir . "language/",
        $data_dir . "menus/",
        $data_dir . "modules/",
        $data_dir . "settings/",
        $data_dir . "types/",
        $data_dir . "modules/"
    );

    foreach($cache_invlidate as $data_path)
    {
        if(strstr($file, $data_path) !== false)
        {
            touch($data_dir . "cache_events");
            break;
        }
    }

    //For security we place this at the top of the file to
    //make it unreadable by external users
    $content = "<?php exit; ?>\n\n\n";

    foreach($data as $row => $fields)
    {
        $content .= "row: $row\n\n";

        foreach($fields as $name => $value)
        {
            $value = str_replace(
                array("\n", "field;"),
                array("\n\t\t", "\\field;"),
                $value
            );

            $content .= "\tfield: $name\n";
            $content .= "\t\t" . trim($value);
            $content .= "\n\tfield;\n\n";
        }

        $content .= "row;\n\n\n";
    }

    if(!file_put_contents($file, $content))
    {
        return false;
    }

    //Store data in serialized form for faster reads by data_parser() function
    //This is specially useful for less powered embedded devices
    if(is_dir(Site::dataDir() . "data_cache"))
    {
        if(function_exists("apc_store"))
        {
            $cache_file = Uri::fromText(realpath($file));

            apc_store($cache_file, $data);
        }
        else
        {
            $cache_file = Site::dataDir() . "data_cache/" .
                Uri::fromText(str_replace(Site::dataDir(), "", $file))
            ;

            self::lock($cache_file, LOCK_EX);
            file_put_contents($cache_file, serialize($data));
            self::unlock($cache_file);
        }
    }

    return true;
}

/**
 * Gets a row and all its fields from a php data file.
 *
 * @param int $position The number or id of the row to retrieve.
 * @param string $file Path to the data file.
 *
 * @return array Array list in the format fields["name"] = "value"
 * @original get_data
 */
static function get($position, $file)
{
    $actual_data = array();

    if(file_exists($file))
    {
        $actual_data = self::parse($file);
    }

    if(!isset($actual_data[$position]))
    {
        return array();
    }

    return $actual_data[$position];
}

/**
 * Appends a new row to a php data file and
 * creates the file if doesnt exist.
 *
 * @param array $fields Fields in the format fields["name"] = "value"
 * @param string $file The path to the data file.
 *
 * @return bool False if failed to add data otherwise true.
 * @original add_data
 */
static function add($fields, $file)
{
    self::lock($file, LOCK_EX);

    $actual_data = array();

    if(file_exists($file))
    {
        $actual_data = self::parse($file, false, false);
    }

    $actual_data[] = $fields;

    $return_value = self::writeNoLock($actual_data, $file);

    self::unlock($file);

    return $return_value;
}

/**
 * Delete a row from a php data file and all its fields.
 *
 * @param int $position The position or id of the row to delete.
 * @param string $file The path to the file.
 *
 * @return bool False if failed to delete data otherwise true.
 * @original delete_data
 */
static function delete($position, $file)
{
    self::lock($file, LOCK_EX);

    $actual_data = self::parse($file, false, false);

    unset($actual_data[$position]);

    $return_value = self::writeNoLock($actual_data, $file);

    self::unlock($file);

    return $return_value;
}

/**
 * Deletes a row from a php data file when a field matches a specific value.
 *
 * @param string $field_name Name of the field to match.
 * @param string $value Value of the field.
 * @param string $file The path to the file.
 *
 * @return bool False if failed to delete data otherwise true.
 * @original delete_data_by_field
 */
static function deleteByField($field_name, $value, $file)
{
    $data = self::parse($file);

    foreach($data as $position => $fields)
    {
        if($fields[$field_name] == $value)
        {
            if(!self::delete($position, $file))
            {
                return false;
            }
        }
    }

    return true;
}

/**
 * Edits all the fields from a row on a php data file.
 *
 * @param int $position The position or id of the row to edit.
 * @param array $new_data Fields in the format fields["name"] = "value"
 * with the new data to be written to the row.
 * @param string $file The path to the database file.
 * @param callable $callback A static function to call after locking the file
 * for more precise modification of data. The static function should be as follows:
 * callback(&actual_data, &$new_data)
 *
 * @return bool False if failed to edit data otherwise true.
 * @original edit_data
 */
static function edit($position, $new_data, $file, $callback=null)
{
    self::lock($file, LOCK_EX);

    $actual_data = self::parse($file, false, false);

    if($callback != null)
    {
        $callback($actual_data, $new_data);
    }

    if(!$actual_data)
    {
        $actual_data = array($position => $new_data);
    }
    else
    {
        $actual_data[$position] = $new_data;
    }

    $return_value = self::writeNoLock($actual_data, $file);

    self::unlock($file);

    return $return_value;
}

/**
 * Locks a file for write protection.
 *
 * @param string $file The file path to lock.
 * @param int $lock_type The lock mode that can be shared or exclusive.
 *
 * @original lock_data
 */
static function lock($file, $lock_type = LOCK_SH)
{
    if(!is_array(self::$m_dataLockedFiles))
        self::$m_dataLockedFiles = array();

    if(isset(self::$m_dataLockedFiles[$file]))
    {
        if(is_resource(self::$m_dataLockedFiles[$file]))
            return;
    }

    $fp = null;

    if($lock_type == LOCK_SH)
        $fp = @fopen($file, "r+");
    else
        $fp = @fopen($file, "rw+");

    if(is_resource($fp))
    {
        self::$m_dataLockedFiles[$file] = $fp;

        while(!flock(self::$m_dataLockedFiles[$file], $lock_type));
    }
}

/**
 * Unlocks a write protected file.
 *
 * @param string $file The path of the file to unlock.
 * @original unlock_data
 */
static function unlock($file)
{
    if(isset(self::$m_dataLockedFiles[$file]))
    {
        if(is_resource(self::$m_dataLockedFiles[$file]))
        {
            flock(self::$m_dataLockedFiles[$file], LOCK_UN);
            fclose(self::$m_dataLockedFiles[$file]);
            self::$m_dataLockedFiles[$file] = null;
        }

        unset(self::$m_dataLockedFiles[$file]);
    }
}

/**
 * Sorts an array returned by the data_parser() static function using bubble sort.
 *
 * @param array $data_array The array to sort in the format returned by data_parser().
 * @param string $field_name The field name we are using to sort the array by.
 * @param int $sort_method The type of sorting, default is ascending.
 *
 * @return array The same array but sorted by the given field name.
 * @original sort_data
 */
static function sort($data_array, $field_name, $sort_method = SORT_ASC)
{
    $sorted_array = array();

    if(is_array($data_array))
    {
        $field_to_sort_by = array();
        $new_id_position = array();

        foreach($data_array as $key => $fields)
        {
            $field_to_sort_by[$key] = isset($fields[$field_name]) ?
                $fields[$field_name]
                :
                0
            ;

            $new_id_position[$key] = $key;
        }

        array_multisort(
            $field_to_sort_by,
            $sort_method,
            $new_id_position,
            $sort_method
        );

        foreach($new_id_position as $id)
        {
            $sorted_array[$id] = $data_array[$id];
        }
    }

    return $sorted_array;
}

}
