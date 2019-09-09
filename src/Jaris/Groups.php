<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * The functions to manage groups.
 */
class Groups
{

/**
 * Receives parameters: $permissions, $group
 * @var string
 */
    const SIGNAL_SET_GROUP_PERMISSION = "hook_set_group_permission";

    /**
     * Adds a new group to the system.
     *
     * @param string $group_name The machine readable froup to create on the system.
     * @param array $fields An array with the needed fields to write to the group
     * in the format array(name=>value, description=>value).
     *
     * @return string "true" string on success or error message on fail.
     */
    public static function add(string $group_name, array $fields): string
    {
        $group_data_path = self::getPath($group_name);

        //Check if group file already exist
        if (!file_exists($group_data_path)) {
            //Create group directory
            FileSystem::makeDir(Site::dataDir() . "groups/$group_name", 0755, true);

            if (!Data::add($fields, $group_data_path)) {
                return System::errorMessage("write_error_data");
            }

            //Create user group directory
            FileSystem::makeDir(Site::dataDir() . "users/$group_name", 0755, true);
        } else {
            //if file exist then group exist so return error message
            return System::errorMessage("group_exist");
        }

        return "true";
    }

    /**
     * Deletes an existing group.
     *
     * @param string $group_name The machine readable group to delete.
     *
     * @return string "true" string on success or error message on fail.
     */
    public static function delete(string $group_name): string
    {
        //Check if group is not from system
        if (
        $group_name != "administrator" &&
        $group_name != "regular" &&
        $group_name != "guest"
    ) {
            //Delete group files
            if (!FileSystem::recursiveRemoveDir(Site::dataDir() . "groups/$group_name")) {
                return System::errorMessage("write_error_data");
            }

            //Move existing users from deleted group to regular group
            FileSystem::recursiveMoveDir(
            Site::dataDir() . "users/$group_name",
            Site::dataDir() . "users/regular"
        );

            //Delete users group directory
            FileSystem::recursiveRemoveDir(Site::dataDir() . "users/$group_name");
        } else {
            //This is a system group and can not be deleted
            return System::errorMessage("delete_system_group");
        }

        return "true";
    }

    /**
     * Edits or changes the data of an existing group.
     *
     * @param string $group_name The machine readable group.
     * @param array $new_data An array of the fields that will
     * substitue the old values.
     * @param string $new_name The new machine readable name.
     *
     * @return string "true" string on success or error message on fail.
     */
    public static function edit(
    string $group_name,
    array $new_data,
    string $new_name = ""
): string {
        $group_data_path = self::getPath($group_name);

        if (!Data::edit(0, $new_data, $group_data_path)) {
            return System::errorMessage("write_error_data");
        }

        //Check if group is not from system
        if (
            $group_name != "administrator" &&
            $group_name != "regular" &&
            $group_name != "guest"
    ) {
            //If a new machine readable group name is passed make appropriate changes.
            if ($new_name != "" && $new_name != $group_name) {
                //If the new group name already exist skip
                if (file_exists(Site::dataDir() . "groups/$new_name")) {
                    return System::errorMessage("group_exist");
                }

                //Move group and data files
                rename(
                Site::dataDir() . "groups/$group_name",
                Site::dataDir() . "groups/$new_name"
            );

                //Move users to new group directory
                rename(
                Site::dataDir() . "users/$group_name",
                Site::dataDir() . "users/$new_name"
            );
            }
        } else {
            return System::errorMessage("edit_system_group");
        }

        return "true";
    }

    /**
     * Get an array with data of a specific group.
     *
     * @param string $group_name The group.
     *
     * @return array An array with all the fields of the group or empty array
     * if the group was not found.
     */
    public static function get(string $group_name): array
    {
        $group_data_path = self::getPath($group_name);

        $group_data = Data::parse($group_data_path);

        if ($group_data) {
            $group_data[0]["name"] = trim($group_data[0]["name"]);
            $group_data[0]["description"] = trim($group_data[0]["description"]);

            return $group_data[0];
        }

        return [];
    }

    /**
     * Gets a list of existing groups on the system.
     *
     * @return array An array of groups in the format
     * array(name=>"group directory name").
     */
    public static function getList(): array
    {
        static $groups = [];

        if (empty($groups)) {
            $dir_handle = opendir(Site::dataDir() . "groups");

            while (($group_directory = readdir($dir_handle)) !== false) {
                //just check directories inside and skip the guest user group
                if (
                strcmp($group_directory, ".") != 0 &&
                strcmp($group_directory, "..") != 0 &&
                strcmp($group_directory, "guest") != 0
            ) {
                    $group_data = self::get($group_directory);

                    $groups[$group_data["name"]] = $group_directory;
                }
            }
        }

        return $groups;
    }

    /**
     * Generates the neccesary array for the form fields.
     *
     * @param ?array $selected The array of selected groups on the control.
     * @param string $field_name
     * @param array $skip_groups
     * @param bool $inline
     *
     * @return array wich represent a series of fields that can
     * be used when generating a form on a fieldset.
     */
    public static function generateFields(
    ?array $selected = [],
    string $field_name="groups",
    array $skip_groups = [],
    bool $inline=false
): array {
        $fields = [];

        $groups_list = self::getList();
        $groups_list[] = "guest";

        foreach ($groups_list as $machine_name) {
            if (in_array($machine_name, $skip_groups)) {
                continue;
            }

            $group_data = self::get($machine_name);

            $checked = false;
            if ($selected) {
                foreach ($selected as $value) {
                    if ($value == $machine_name) {
                        $checked = true;
                        break;
                    }
                }
            }

            $fields[] = [
            "type" => "checkbox",
            "checked" => $checked,
            "label" => t($group_data["name"]),
            "name" => $field_name."[]",
            "id" => $field_name,
            "value" => $machine_name,
            "inline" => $inline
        ];
        }

        return $fields;
    }

    /**
     * Sets the value of a given permission.
     *
     * @param string $permission_name The machine name of the permission to set.
     * @param string $value The new value given to the permission.
     * @param string $group_name The name of the group the set the permission on.
     *
     * @return bool True on success or false on fail.
     */
    public static function setPermission(
    string $permission_name,
    ?string $value,
    string $group_name
): bool {
        $permissions_data_path = self::getPath($group_name);

        $permissions_data_path = str_replace(
        "/data.php",
        "/permissions.php",
        $permissions_data_path
    );

        $permissions_data = [];

        if (file_exists($permissions_data_path)) {
            $permissions_data = Data::get(0, $permissions_data_path);
        }

        $permissions_data[$permission_name] = $value;

        return Data::edit(0, $permissions_data, $permissions_data_path);
    }

    /**
     * Gets an array of existing permissions.
     *
     * @param string $group The machine name of the group.
     *
     * @return array Array in the format
     * permissions["group"] = array("machine_name"=>"Human Name").
     */
    public static function getPermissions(string $group): array
    {
        //Login Permissions
        $login = [];
        $login["offline_login"] = t("Enable login in offline mode");

        //Block Permissions
        $blocks = [];
        $blocks["view_blocks"] = t("View");
        $blocks["add_blocks"] = t("Create");
        $blocks["edit_blocks"] = t("Edit");
        $blocks["delete_blocks"] = t("Delete");
        $blocks["return_code_blocks"] = t("Return Code");
        $blocks["input_format_blocks"] = t("Select input format");

        //Content Block Permissions
        $content_blocks = [];
        $content_blocks["view_content_blocks"] = t("View");
        $content_blocks["add_content_blocks"] = t("Create");
        $content_blocks["edit_content_blocks"] = t("Edit");
        $content_blocks["delete_content_blocks"] = t("Delete");
        $content_blocks["edit_post_settings_content_blocks"] = t("Edit post settings");
        $content_blocks["return_code_content_blocks"] = t("Return Code");
        $content_blocks["input_format_content_blocks"] = t("Select input format");

        //Content Permissions
        $content = [];
        $content["view_content"] = t("View");
        $content["add_content"] = t("Create");
        $content["edit_content"] = t("Edit");
        $content["approve_content"] = t("Approve");
        $content["duplicate_content"] = t("Duplicate");
        $content["delete_content"] = t("Delete");
        $content["select_type_content"] = t("Select type");
        $content["select_content_groups"] = t("Select groups");
        $content["add_edit_meta_content"] = t("Add/Edit Meta Tags");
        $content["input_format_content"] = t("Select input format");
        $content["manual_uri_content"] = t("Permit manually enter uri");
        $content["edit_all_user_content"] = t("Can edit all users content");

        //File permissions
        $files = [];
        $files["view_files"] = t("View");
        $files["add_files"] = t("Create");
        $files["edit_files"] = t("Edit");
        $files["delete_files"] = t("Delete");

        //Image permissions
        $images = [];
        $images["view_images"] = t("View");
        $images["add_images"] = t("Create");
        $images["edit_images"] = t("Edit");
        $images["delete_images"] = t("Delete");
        $images["edit_upload_width"] = t("Edit upload width");

        //Input formats permissions
        $input_formats = [];
        $input_formats["view_input_formats"] = t("View");
        $input_formats["add_input_formats"] = t("Create");
        $input_formats["edit_input_formats"] = t("Edit");
        $input_formats["delete_input_formats"] = t("Delete");

        //Content types access
        $types_list = Types::getList();
        $types_access = [];
        foreach ($types_list as $machine_name => $type_data) {
            $types_access[$machine_name . "_type"] = t($type_data["name"]);
        }

        //Types
        $types = [];
        $types["view_types"] = t("View");
        $types["add_types"] = t("Create");
        $types["edit_types"] = t("Edit");
        $types["delete_types"] = t("Delete");

        //Categories
        $categories = [];
        $categories["view_categories"] = t("View");
        $categories["add_categories"] = t("Create");
        $categories["edit_categories"] = t("Edit");
        $categories["delete_categories"] = t("Delete");

        //Subcategories
        $subcategories = [];
        $subcategories["view_subcategories"] = t("View");
        $subcategories["add_subcategories"] = t("Create");
        $subcategories["edit_subcategories"] = t("Edit");
        $subcategories["delete_subcategories"] = t("Delete");

        //Menu Permissions
        $menus = [];
        $menus["view_menus"] = t("View");
        $menus["configure_menus"] = t("Configure");
        $menus["add_menus"] = t("Create");
        $menus["edit_menus"] = t("Edit");
        $menus["delete_menus"] = t("Delete");

        //Menu Item Permissions
        $menu_items = [];
        $menu_items["add_menu_items"] = t("Create");
        $menu_items["edit_menu_items"] = t("Edit");
        $menu_items["delete_menu_items"] = t("Delete");

        //User Permissions
        $users = [];
        $users["autocomplete_users"] = t("Auto complete");
        $users["view_users"] = t("View");
        $users["add_users"] = t("Create");
        $users["edit_users"] = t("Edit");
        $users["delete_users"] = t("Delete");

        //Group Permissions
        $groups = [];
        $groups["view_groups"] = t("View");
        $groups["add_groups"] = t("Create");
        $groups["edit_groups"] = t("Edit");
        $groups["delete_groups"] = t("Delete");

        //Site Settings
        $settings = [];
        $settings["edit_settings"] = t("Edit");

        //Theme
        $theme = [];
        $theme["select_theme"] = t("Select");
        $theme["select_user_theme"] = t("Select user theme");
        $theme["delete_theme"] = t("Delete");

        //Languages
        $languages = [];
        $languages["view_languages"] = t("View");
        $languages["add_languages"] = t("Create");
        $languages["edit_languages"] = t("Edit");
        $languages["translate_languages"] = t("Translate");

        //Modules
        $modules = [];
        $modules["view_modules"] = t("View");
        $modules["install_modules"] = t("Install");
        $modules["uninstall_modules"] = t("Uninstall");
        $modules["upgrade_modules"] = t("Upgrade");
        $modules["delete_modules"] = t("Delete");

        //Api
        $api = [];
        $api["view_keys_api"] = t("View Keys");
        $api["add_keys_api"] = t("Add Keys");
        $api["edit_keys_api"] = t("Edit Keys");
        $api["delete_keys_api"] = t("Delete Keys");


        //Group all permissions
        $permissions = [];
        $permissions[t("Login")] = $login;
        $permissions[t("Blocks")] = $blocks;
        $permissions[t("Content Blocks")] = $content_blocks;
        $permissions[t("Content")] = $content;
        $permissions[t("Content Types")] = $types;
        $permissions[t("Categories")] = $categories;
        $permissions[t("Files")] = $files;
        $permissions[t("Images")] = $images;
        $permissions[t("Input Formats")] = $input_formats;
        $permissions[t("Subcategories")] = $subcategories;
        $permissions[t("Menus")] = $menus;
        $permissions[t("Menu Items")] = $menu_items;
        $permissions[t("Users")] = $users;
        $permissions[t("Groups")] = $groups;
        $permissions[t("Site Settings")] = $settings;
        $permissions[t("Themes")] = $theme;
        $permissions[t("Types Access")] = $types_access;
        $permissions[t("Languages")] = $languages;
        $permissions[t("Modules")] = $modules;

        //Call set_group_permission hook before returning the permissions
        Modules::hook("hook_set_group_permission", $permissions, $group);

        ksort($permissions);

        return $permissions;
    }

    /**
     * Generates the data path for a group.
     *
     * @param string $group_name The group to translate to a valid user data path.
     */
    public static function getPath(string $group_name): string
    {
        $group_data_path = Site::dataDir() . "groups/$group_name/data.php";

        return $group_data_path;
    }
}
