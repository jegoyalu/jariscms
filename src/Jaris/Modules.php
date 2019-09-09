<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to manage modules
 */
class Modules
{

/**
 * Calls a hook function from a module functions.php file if available
 *
 * @param string $hook the name of the hook to call.
 * @param mixed $var1 Optional argument passed to the hook function.
 * @param mixed $var2 Optional argument passed to the hook function.
 * @param mixed $var3 Optional argument passed to the hook function.
 * @param mixed $var4 Optional argument passed to the hook function.
 * @param mixed $var5 Optional argument passed to the hook function.
 */
    public static function hook(
    string $hook,
    &$var1 = "null",
    &$var2 = "null",
    &$var3 = "null",
    &$var4 = "null",
    &$var5 = "null"
): void {
        static $signals_loaded;

        if (!$signals_loaded) {
            $development_mode = Site::$development_mode;

            $installed_modules = self::getInstalled();

            foreach ($installed_modules as $name) {
                $function_file = "";

                if (!$development_mode) {
                    $functions_file = Site::dataDir() . "modules/$name/functions.php";
                } else {
                    $functions_file = self::directory($name) . "functions.php";
                }

                if (!file_exists($functions_file)) {
                    //Skip if functions file doesnt exist on the current module
                    continue;
                }

                include_once($functions_file);
            }

            $signals_loaded = true;
        }

        Signals\SignalHandler::sendWithParams(
        $hook,
        $var1,
        $var2,
        $var3,
        $var4,
        $var5
    );
    }

    /**
     * Gets a module path with trailing slash included.
     *
     * @param string $name
     */
    public static function directory(string $name=""): string
    {
        static $modules = [];

        if ($name != "") {
            $name .= "/";
        }

        if (!isset($modules[$name])) {
            $site = Site::current();

            $path = "sites/" . $site;

            if (is_dir($path)) {
                if (is_dir($path . "/modules/$name")) {
                    $modules[$name] = $path . "/modules/$name";
                }
            } elseif ($site != "default") {
                if (is_dir("sites/default/modules/$name")) {
                    $modules[$name] = "sites/default/modules/$name";
                }
            }

            if (!isset($modules[$name])) {
                $modules[$name] = "modules/$name";
            }
        }

        return $modules[$name];
    }

    /**
     * Get path where modules should be uploaded with trailing slash.
     */
    public static function getUploadPath(): string
    {
        $path = "sites/" . Site::current();

        if (is_dir($path)) {
            if (is_dir($path . "/modules/")) {
                return $path . "/modules/";
            }
        }

        return "sites/default/modules/";
    }

    /**
     * Retreive all the info of available modules.
     *
     * @return array Info of all modules in the format
     * modules["module_machine_name"] = array("field"=>"value")
     * or empty array if no module available.
     */
    public static function getAll(): array
    {
        $modules = [];

        // Search for a site specific modules.
        $site_modules_dir = self::directory();

        if ($site_modules_dir != "modules/") {
            $dir_handle = opendir($site_modules_dir);

            while (($file = readdir($dir_handle)) !== false) {
                //Deletes previous module data
                $module = [];

                if (
                strcmp($file, ".") != 0 &&
                strcmp($file, "..") != 0 &&
                strcmp($file, "readme.txt") != 0
            ) {
                    $info_file = $site_modules_dir . $file . "/info.php";

                    if (file_exists($info_file)) {
                        include($info_file);
                        $modules[$file] = $module;
                    }
                }
            }

            closedir($dir_handle);
        }

        // Search for global modules.
        $module_dir = "modules/";
        $dir_handle = opendir($module_dir);

        while (($file = readdir($dir_handle)) !== false) {
            //Deletes previous module data
            $module = [];

            if (strcmp($file, ".") != 0 && strcmp($file, "..") != 0) {
                if (isset($modules[$file])) {
                    continue;
                }

                $info_file = $module_dir . $file . "/info.php";

                if (file_exists($info_file)) {
                    include($info_file);
                    $modules[$file] = $module;
                }
            }
        }

        closedir($dir_handle);

        ksort($modules);

        return $modules;
    }

    /**
     * Retreive the info of a specific module
     *
     * @param string $name the machine name of the module usually its
     * directory name on the modules directory.
     *
     * @return array Info of the module or false if doesnt exist.
     */
    public static function get(string $name): array
    {
        $module_dir = self::directory($name);

        $info_file = $module_dir . "info.php";

        $module = [];

        if (file_exists($info_file)) {
            include($info_file);

            $info_content = file_get_contents($module_dir . "info.php");

            if (
            ($changes_start = strpos($info_content, "/*\n"))
            !==
            false
        ) {
                $changes_start += 2;

                $module["changes"] = trim(
                trim(
                    substr($info_content, $changes_start),
                    "\n */"
                )
            );
            } else {
                $module["changes"] = "";
            }

            return $module;
        }

        return [];
    }

    /**
     * Check the modules that are installed.
     *
     * @return array Machine names of each installed module.
     */
    public static function getInstalled(): array
    {
        static $modules;

        if (!$modules) {
            $modules = [];

            $module_dir = Site::dataDir() . "modules/";

            if (is_dir($module_dir)) {
                $dir_handle = opendir($module_dir);

                if (!is_bool($dir_handle)) {
                    while (($file = readdir($dir_handle)) !== false) {
                        if (strcmp($file, ".") != 0 && strcmp($file, "..") != 0) {
                            if (is_dir($module_dir . $file)) {
                                $modules[] = $file;
                            }
                        }
                    }
                }

                sort($modules);
            }
        }

        return $modules;
    }

    /**
     * Get the current installed version of a module.
     *
     * @param string $name Name of the module to retrieve its version.
     *
     * @return string Installed version of given module or empty string if not
     * installed.
     */
    public static function getInstalledVersion(string $name): string
    {
        $module_dir = Site::dataDir() . "modules/$name/";

        $info_file = $module_dir . "info.php";

        $module = [];

        if (file_exists($info_file)) {
            include($info_file);
            return $module["version"];
        }

        return "";
    }

    /**
     * Checks if a module is installed to the system.
     *
     * @param string $name Machine name of the module.
     *
     * @return bool true if installed false if not.
     */
    public static function isInstalled(string $name): bool
    {
        if (file_exists(Site::dataDir() . "modules/$name")) {
            return true;
        }

        return false;
    }

    /**
     * Check if a module dependencies are installed.
     *
     * @param string $name Machine name of the module currently its directory name
     * on the modules directory.
     *
     * @return bool true if dependencies are installed false if not.
     */
    public static function checkDependecies(string $name): bool
    {
        $module_data = self::get($name);

        if (isset($module_data["dependencies"])) {
            $some_modules_not_installed = false;
            $modules_not_installed = "";

            foreach ($module_data["dependencies"] as $dependency_name) {
                if (!self::isInstalled($dependency_name)) {
                    $dependency_data = self::get($dependency_name);
                    $some_modules_not_installed = true;

                    if ($dependency_data) {
                        $modules_not_installed .= $dependency_data["name"] . ", ";
                    } else {
                        $modules_not_installed .= $dependency_name . ", ";
                    }

                    unset($dependency_data);
                }
            }

            if ($some_modules_not_installed) {
                $modules_not_installed = trim($modules_not_installed, ", ");

                View::addMessage(
                t("The following modules need to be installed first:") .
                " $modules_not_installed",
                "error"
            );

                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given module is a dependency of other.
     *
     * @param string $name Machine name of the module currently its directory name
     * on the modules directory.
     *
     * @return bool true if is dependency false if not.
     */
    public static function isDependency(string $name): bool
    {
        $installed_modules = self::getInstalled();

        foreach ($installed_modules as $module_name) {
            $module_data = self::get($module_name);

            if (isset($module_data["dependencies"])) {
                foreach ($module_data["dependencies"] as $dependency_name) {
                    if ($dependency_name == $name) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Enable a module to be usable by the system if all dependecies are satisfied.
     *
     * @param string $name Machine name of the module usually its directory name
     * on the modules directory.
     * @param bool $needs_dependency Reference that returns true
     * if current module needs dependency.
     *
     * @return bool true on success false on fail.
     */
    public static function install(string $name, bool &$needs_dependency = false): bool
    {
        if (!self::checkDependecies($name)) {
            $needs_dependency = true;
            return false;
        }

        $module_dir = self::directory($name);
        $module_installation = Site::dataDir() . "modules/$name";

        //Firt we make the directory holding module installation files.
        if (!FileSystem::makeDir($module_installation, 0755, true)) {
            return false;
        }

        //Copy current module info file used to store the version
        copy($module_dir . "info.php", $module_installation . "/info.php");

        //Copy current module functions file.
        if (file_exists($module_dir . "functions.php")) {
            copy($module_dir . "functions.php", $module_installation . "/functions.php");
        }

        //Copy current module uninstall function file.
        if (file_exists($module_dir . "uninstall.php")) {
            copy($module_dir . "uninstall.php", $module_installation . "/uninstall.php");
        }

        //Copy current blocks file.
        if (file_exists($module_dir . "blocks.php")) {
            copy($module_dir . "blocks.php", $module_installation . "/blocks.php");

            //Install blocks
            $blocks = Data::parse($module_dir . "blocks.php");

            foreach ($blocks as $block_fields) {
                $block_position = $block_fields["position"] ?
                $block_fields["position"]
                :
                "none"
            ;

                Blocks::add($block_fields, $block_position);
            }
        }

        //Install module pages
        if (file_exists($module_dir . "pages.php")) {
            //Store the uri of each page created in case uri is renamed since already
            //exist
            $pages_uri = [];

            $pages = Data::parse($module_dir . "pages.php");

            foreach ($pages as $id => $fields) {
                $uri = trim($fields["uri"]);

                //Reference that stores the new page uri in case original already exist
                $new_uri = "";

                $data_file = $module_dir . "data/" .
                str_replace("/", "-", str_replace("-", "_", $uri)) . ".php"
            ;

                $data = Data::get(0, $data_file);

                if (!Pages::add($uri, $data, $new_uri)) {
                    return false;
                }

                $pages_uri[$id] = [
                "original_uri" => $uri,
                "new_uri" => $new_uri
            ];
            }

            if (!Data::write($pages_uri, $module_installation . "/pages.php")) {
                return false;
            }
        }

        //Execute module install script function if available
        //This function is named with the module name and install word.
        //for example: modulename_install()
        if (file_exists($module_dir . "install.php")) {
            include($module_dir . "install.php");

            $install_function = $name . "_install";

            $install_function();
        }

        return true;
    }

    /**
     * Removes a module from the system if not dependency.
     *
     * @param string $name Machine name of the module to remove.
     * @param bool $is_dependency reference variable that returns
     * true if current module is dependency.
     *
     * @return bool true on success false on fail.
     */
    public static function uninstall(string $name, bool &$is_dependency = false): bool
    {
        if (self::isDependency($name)) {
            $is_dependency = true;

            View::addMessage(
            t("This module is a dependency and can't be uninstalled."),
            "error"
        );

            return false;
        }

        $module_dir = Site::dataDir() . "modules/$name";

        //Remove module pages
        if (file_exists($module_dir . "/pages.php")) {
            $pages = Data::parse($module_dir . "/pages.php");

            foreach ($pages as $id => $fields) {
                if (!Pages::delete($fields["new_uri"])) {
                    if (file_exists(Pages::getPath($fields["new_uri"]))) {
                        return false;
                    }
                }
            }
        }

        //Execute module uninstall script function if available
        //This function is named with the module name and uninstall word.
        //for example: modulename_uninstall()
        if (file_exists($module_dir . "/uninstall.php")) {
            include($module_dir . "/uninstall.php");

            $uninstall_function = $name . "_uninstall";

            $uninstall_function();
        }

        //Remove module blocks
        if (file_exists($module_dir . "/blocks.php")) {
            $blocks = Data::parse($module_dir . "/blocks.php");

            foreach ($blocks as $block_fields) {
                Blocks::deleteByField(
                "module_identifier",
                $block_fields["module_identifier"]
            );
            }
        }

        if (!FileSystem::recursiveRemoveDir($module_dir)) {
            return false;
        }

        return true;
    }

    /**
     * Upgrades a module if installed version is different from
     * uploaded to modules directory.
     *
     * @param string $name Machine name of the module, currently
     * its directory name on the modules directory.
     *
     * @return bool true on success false on fail.
     */
    public static function upgrade(string $name): bool
    {
        $module_dir = self::directory($name);
        $module_installation = Site::dataDir() . "modules/$name";

        //Remove module pages
        if (file_exists($module_installation . "/pages.php")) {
            $pages = Data::parse($module_installation . "/pages.php");

            foreach ($pages as $id => $fields) {
                if (!Pages::delete($fields["new_uri"])) {
                    return false;
                }
            }
        }

        //Stores current state of blocks
        $blocks_data = [];

        //Remove module blocks
        if (file_exists($module_installation . "/blocks.php")) {
            $blocks = Data::parse($module_installation . "/blocks.php");

            foreach ($blocks as $block_fields) {
                $block_position = "";

                $block_data = Blocks::getByField(
                "module_identifier",
                $block_fields["module_identifier"],
                "",
                $block_position
            );

                $blocks_data[$block_fields["module_identifier"]] = [
                "position" => $block_position,
                "data" => $block_data
            ];

                Blocks::deleteByField(
                "module_identifier",
                $block_fields["module_identifier"],
                ""
            );
            }
        }

        //Copy current module info file used to store the version
        copy($module_dir . "info.php", $module_installation . "/info.php");

        //Copy current module functions file.
        if (file_exists($module_dir . "functions.php")) {
            copy($module_dir . "functions.php", $module_installation . "/functions.php");
        }

        //Copy current module uninstall function file.
        if (file_exists($module_dir . "uninstall.php")) {
            copy($module_dir . "uninstall.php", $module_installation . "/uninstall.php");
        }

        //Copy current blocks file.
        if (file_exists($module_dir . "blocks.php")) {
            copy($module_dir . "blocks.php", $module_installation . "/blocks.php");

            //Install blocks
            $blocks = Data::parse($module_dir . "blocks.php");

            foreach ($blocks as $block_fields) {
                $block_position = $block_fields["position"] ?
                $block_fields["position"]
                :
                "none"
            ;

                if (
                isset(
                    $blocks_data[$block_fields["module_identifier"]]
                )
            ) {
                    $identifier = $block_fields["module_identifier"];

                    $position = trim(
                    $blocks_data[$identifier]["position"]
                );

                    $block_position = !empty($position) ?
                    $position
                    :
                    $block_position
                ;

                    $block_fields["position"] = $blocks_data
                    [$identifier]
                    ["position"]
                ;

                    $block_fields["title"] = $blocks_data
                    [$identifier]
                    ["data"]["title"]
                ;

                    $block_fields["order"] = $blocks_data
                    [$identifier]
                    ["data"]["order"]
                ;

                    $block_fields["display_rule"] = $blocks_data
                    [$identifier]
                    ["data"]["display_rule"]
                ;

                    $block_fields["pages"] = $blocks_data
                    [$identifier]
                    ["data"]["pages"]
                ;

                    $block_fields["groups"] = $blocks_data
                    [$identifier]
                    ["data"]["groups"]
                ;

                    $block_fields["themes"] = $blocks_data
                    [$identifier]
                    ["data"]["themes"]
                ;
                }

                Blocks::add($block_fields, $block_position);
            }
        }

        //Install module pages
        if (file_exists($module_dir . "pages.php")) {
            //Store the uri of each page created in case uri is
            //renamed since already exist
            $pages_uri = [];

            $pages = Data::parse($module_dir . "pages.php");

            foreach ($pages as $id => $fields) {
                $uri = trim($fields["uri"]);

                //Reference that stores the new page uri in case original already exist
                $new_uri = "";

                $data_file = $module_dir . "data/" .
                str_replace("/", "-", str_replace("-", "_", $uri)) .
                ".php"
            ;

                $data = Data::get(0, $data_file);

                if (!Pages::add($uri, $data, $new_uri)) {
                    return false;
                }

                $pages_uri[$id] = [
                "original_uri" => $new_uri,
                "new_uri" => $new_uri
            ];
            }

            if (!Data::write($pages_uri, $module_installation . "/pages.php")) {
                return false;
            }
        }

        //Execute module upgrade script function if available
        //This function is named with the module name and upgrade word.
        //for example: modulename_upgrade()
        if (file_exists($module_dir . "upgrade.php")) {
            include($module_dir . "upgrade.php");

            $upgrade_function = $name . "_upgrade";

            $upgrade_function();
        }

        return true;
    }

    /**
     * Function to retrieve the uri of a page installed with a module. This function
     * is used in case the page installed with a module had to be renamed to another
     * uri since it already existed.
     *
     * @param string $original_uri Original uri of the page installed.
     * @param string $module_name Machine name of the module.
     *
     * @return string New uri of the page installed or the original one.
     */
    public static function getPageUri(string $original_uri, string $module_name): string
    {
        static $module_pages;

        if ($module_name != "") {
            if (empty($module_pages[$module_name])) {
                $module_pages[$module_name] = Data::parse(
                Site::dataDir() . "modules/$module_name/pages.php"
            );
            }

            if (is_array($module_pages[$module_name])) {
                foreach ($module_pages[$module_name] as $id => $fields) {
                    if ($fields["original_uri"] == $original_uri) {
                        return $fields["new_uri"];
                    }
                }
            }
        }

        return $original_uri;
    }

    /**
     * Checks if a given page uri belongs to an installed module and return
     * the full path to it.
     *
     * @param string $page A module page uri.
     *
     * @return string Empty string if the given page uri
     * doesn't belongs to a module.
     */
    public static function getPagePath(string $page): string
    {
        static $pages = [];

        $installed_modules = self::getInstalled();

        foreach ($installed_modules as $name) {
            if (!isset($pages[$name])) {
                if (file_exists(self::directory($name) . "pages.php")) {
                    $module_pages = Data::parse(
                    self::directory($name)  . "pages.php"
                );

                    $pages_list = [];

                    foreach ($module_pages as $fields) {
                        $pages_list[$fields['uri']] = self::directory($name)
                        . "data/"
                        . str_replace(
                            ["-", "/"],
                            ["_", "-"],
                            $fields['uri']
                        )
                        . ".php"
                    ;
                    }

                    $pages[$name] = $pages_list;
                }
            }

            if (isset($pages[$name])) {
                if (isset($pages[$name][$page])) {
                    return $pages[$name][$page];
                }
            }
        }

        return "";
    }

    /**
     * Gets a module block data directly from the module blocks.php file
     * which is useful when running the site in development mode.
     *
     * @param array $block_data
     *
     * @return array
     */
    public static function getBlockData(array $block_data): array
    {
        static $block_files = [];

        if (count($block_files) <= 0) {
            $installed_modules = self::getInstalled();

            foreach ($installed_modules as $name) {
                if (file_exists(self::directory($name) . "blocks.php")) {
                    $block_files[] = self::directory($name) . "blocks.php";
                }
            }
        }

        foreach ($block_files as $file) {
            $module_blocks = Data::parse($file);

            foreach ($module_blocks as $fields) {
                if ($fields["module_identifier"] == $block_data["module_identifier"]) {
                    return $fields;
                }
            }
        }

        return $block_data;
    }
}
