<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
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
 * @param ?string $value Configuration value.
 * @param string $table Name of database configuration
 * file stored on data/settings.
 *
 * @return bool true on success false if failed to write.
 */
    public static function save(string $name, ?string $value, string $table): bool
    {
        $settings_file = Site::dataDir() . "settings/$table.php";

        $fields = [
        "name" => $name,
        "value" => $value ?? ""
    ];

        $current_settings = Data::parse($settings_file);

        Data::lock($settings_file);

        $setting_exists = false;
        $setting_id = 0;

        if ($current_settings) {
            foreach ($current_settings as $id => $setting) {
                if (trim($setting["name"]) == $name) {
                    $setting_exists = true;
                    $setting_id = $id;
                    break;
                }
            }
        }

        Data::unlock($settings_file);

        if ($setting_exists) {
            if (!Data::edit($setting_id, $fields, $settings_file)) {
                return false;
            }
        } else {
            if (!Data::add($fields, $settings_file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes a configuration option on a php data file if exists.
     *
     * @param string $name Configuration name.
     * @param string $table Name of database configuration
     * file stored on data/settings.
     *
     * @return bool true on success false if failed to remove.
     */
    public static function remove(string $name, string $table): bool
    {
        $settings_file = Site::dataDir() . "settings/$table.php";

        $current_settings = Data::parse($settings_file);

        $setting_exists = false;
        $setting_id = 0;

        if ($current_settings) {
            foreach ($current_settings as $id => $setting) {
                if (trim($setting["name"]) == $name) {
                    $setting_exists = true;
                    $setting_id = $id;
                    break;
                }
            }
        }

        if ($setting_exists) {
            return Data::delete($setting_id, $settings_file);
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
     */
    public static function get(string $name, string $table)
    {
        static $tables_array = [];

        if (!isset($tables_array[$table])) {
            $data = self::getAll($table);

            if (is_array($data)) {
                $tables_array[$table] = $data;
            } else {
                $tables_array[$table] = [];
            }
        }

        if (isset($tables_array[$table][$name])) {
            return $tables_array[$table][$name];
        }

        return null;
    }

    /**
     * Gets all the configurations values from a php data file.
     *
     * @param string $table PHP data file name stored on data/settings
     *
     * @return array All configurations in the format
     * $configurations[name] = value or empty array if the configuration file
     * does not exists.
     */
    public static function getAll(string $table): array
    {
        $settings_file = Site::dataDir() . "settings/$table.php";

        $settings_data = Data::parse($settings_file);

        $settings = [];

        if ($settings_data) {
            foreach ($settings_data as $setting) {
                $settings[$setting["name"]] = $setting["value"];
            }
        } else {
            return [];
        }

        return $settings;
    }
}
