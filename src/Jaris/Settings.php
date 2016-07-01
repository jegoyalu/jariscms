<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 General License Protecting Programmers
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Retrieve and save configurations values.
 */
class Settings
{
    /**
     * Stores a configuration option on a php data file and
     * creates it if doesnt exist.
     *
     * @param string $name Configuration name.
     * @param string $value Configuration value.
     * @param string $table Name of database configuration
     * file stored on data/settings.
     *
     * @return bool true on success false if failed to write.
     * @original save_setting
     */
    static function save($name, $value, $table)
    {
        $settings_file = Site::dataDir() . "settings/$table.php";

        $fields["name"] = $name;
        $fields["value"] = $value;

        $current_settings = Data::parse($settings_file);

        Data::lock($settings_file);

        $setting_exists = false;
        $setting_id = 0;

        if($current_settings)
        {
            foreach($current_settings as $id => $setting)
            {
                if(trim($setting["name"]) == $name)
                {
                    $setting_exists = true;
                    $setting_id = $id;
                    break;
                }
            }
        }

        Data::unlock($settings_file);

        if($setting_exists)
        {
            if(!Data::edit($setting_id, $fields, $settings_file))
            {
                return false;
            }
        }
        else
        {
            if(!Data::add($fields, $settings_file))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a configuration value from a php data file.
     *
     * @param string $name Configuration to retrieve.
     * @param string $table PHP data file name stored on data/settings.
     *
     * @return string|null Configuration value or null if doesn't exist.
     * @original get_setting
     */
    static function get($name, $table)
    {
        static $tables_array = array();

        if(!isset($tables_array[$table]))
        {
            $data = self::getAll($table);

            if(is_array($data))
            {
                $tables_array[$table] = $data;
            }
            else
            {
                $tables_array[$table] = array();
            }
        }

        if(isset($tables_array[$table][$name]))
        {
            return $tables_array[$table][$name];
        }

        return null;
    }

    /**
     * Gets all the configurations values from a php data file.
     *
     * @param string $table PHP data file name stored on data/settings
     *
     * @return array|null All configurations in the format
     * $configurations[name] = value or null if the configuration file
     * does not exists.
     * @original get_settings
     */
    static function getAll($table)
    {
        $settings_file = Site::dataDir() . "settings/$table.php";

        $settings_data = Data::parse($settings_file);

        $settings = array();

        if($settings_data)
        {
            foreach($settings_data as $setting)
            {
                $settings[$setting["name"]] = $setting["value"];
            }
        }
        else
        {
            return null;
        }

        return $settings;
    }
}