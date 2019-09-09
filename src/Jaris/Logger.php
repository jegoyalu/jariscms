<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Logger class based on the PSR\Log interface.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
Class Logger
{

const EMERGENCY = 'emergency';
const ALERT = 'alert';
const CRITICAL = 'critical';
const ERROR = 'error';
const WARNING = 'warning';
const NOTICE = 'notice';
const INFO = 'info';
const DEBUG = 'debug';

/**
 * System is unusable.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function emergency(string $message, array $context=[]): void
{
    self::log(self::EMERGENCY, $message, $context);
}

/**
 * Action must be taken immediately.
 *
 * Example: Entire website down, database unavailable, etc. This should
 * trigger the SMS alerts and wake you up.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function alert(string $message, array $context=[]): void
{
    self::log(self::ALERT, $message, $context);
}

/**
 * Critical conditions.
 *
 * Example: Application component unavailable, unexpected exception.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function critical(string $message, array $context=[]): void
{
    self::log(self::CRITICAL, $message, $context);
}

/**
 * Runtime errors that do not require immediate action but should typically
 * be logged and monitored.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function error(string $message, array $context=[]): void
{
    self::log(self::ERROR, $message, $context);
}

/**
 * Exceptional occurrences that are not errors.
 *
 * Example: Use of deprecated APIs, poor use of an API, undesirable things
 * that are not necessarily wrong.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function warning(string $message, array $context=[]): void
{
    self::log(self::WARNING, $message, $context);
}

/**
 * Normal but significant events.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function notice(string $message, array $context=[]): void
{
    self::log(self::NOTICE, $message, $context);
}

/**
 * Interesting events.
 *
 * Example: User logs in, SQL logs.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function info(string $message, array $context=[]): void
{
    self::log(self::INFO, $message, $context);
}

/**
 * Detailed debug information.
 *
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function debug(string $message, array $context=[]): void
{
    self::log(self::DEBUG, $message, $context);
}

/**
 * Logs with an arbitrary level.
 *
 * @param string  $level
 * @param string $message
 * @param array  $context
 *
 * @return void
 */
public static function log(
    string $level, string $message, array $context=[]
): void
{
    if(!Sql::dbExists("log"))
    {
        $db = Sql::open("log");

        Sql::query(
            "create table log ("
            . "date text, "
            . "author text, "
            . "level text, "
            . "message text, "
            . "uri text,"
            . "module text,"
            . "context text"
            . ")",
            $db
        );

        Sql::query(
            "create index log_index on log ("
            . "date desc, "
            . "author desc, "
            . "level desc, "
            . "module desc"
            . ")",
            $db
        );

        Sql::close($db);
    }

    $module = "";

    if(count($context) > 0)
    {
        if(isset($context["module"]))
        {
            $module = $context["module"];
            unset($context["module"]);
        }
    }

    $db = Sql::open("log");

    Sql::query(
        "insert into log values ("
        . "'".time()."', "
        . "'".Authentication::currentUser()."',"
        . "'$level', "
        . "'".str_replace("'", "''", $message)."', "
        . "'".str_replace("'", "''", Uri::get())."', "
        . "'$module', "
        . "'".str_replace("'", "''", serialize($context))."'"
        . ")",
        $db
    );

    Sql::close($db);
}

}