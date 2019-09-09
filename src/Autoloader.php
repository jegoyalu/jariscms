<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Autoloader for JarisCMS.
 */
class Autoloader
{
    /**
     * The jaris content management system class autoloader.
     * @param string $class_name
     */
    public static function load($class_name)
    {
        $file = str_replace("\\", "/", $class_name) . ".php";

        if (file_exists(__DIR__ . "/" . $file)) {
            include(__DIR__ . "/" . $file);

            return;
        }

        // Check if file to load belongs to a module
        // ---------------------------------------------------------------
        // we strip Jaris\Module namespace so class files on the
        // src directory can be stored as ModuleName/Class.php instead of
        // Jaris/Module/ModuleName/Class.php
        $file = str_replace("Jaris/Module/", "", $file);
        foreach (Modules::getInstalled() as $module) {
            $module_src = Modules::directory($module) . "src";

            if (is_dir($module_src)) {
                if (file_exists($module_src . "/" . $file)) {
                    include($module_src . "/" . $file);

                    return;
                }
            }
        }
    }

    /**
     * Provides an easy way to register the jaris autoloader for you.
     */
    public static function register()
    {
        spl_autoload_register(['Jaris\Autoloader', 'load']);
    }
}
