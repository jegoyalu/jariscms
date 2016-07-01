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
    static function load($class_name)
    {
        $file = str_replace("\\", "/", $class_name) . ".php";

        include(__DIR__ . "/" . $file);
    }

    /**
     * Provides an easy way to register the jaris autoloader for you.
     */
    static function register()
    {
        spl_autoload_register(array('Jaris\Autoloader', 'load'));
    }
}