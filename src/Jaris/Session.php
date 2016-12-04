<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Helpers to handle sessions.
 */
class Session
{

/**
 * Stores the status of session initialization.
 * @var bool
 */
private static $session_started;

/**
 * Starts a session if not already started.
 * @return bool True if started false if already started.
 */
static function start()
{
    if(!self::$session_started)
    {
        session_start();
        self::$session_started = true;

        return true;
    }

    return false;
}

/**
 * Starts a session if a user is logged.
 * @return bool True if started false if no user logged.
 */
static function startIfUserLogged()
{
    if(!empty($_COOKIE["logged"]))
    {
        self::start();

        return true;
    }

    return false;
}

/**
 * Unregisters and destroys a session with all its data.
 */
static function destroy()
{
    if(self::$session_started)
    {
        unset($_SESSION);

        self::destroyIfEmpty();
    }
}

/**
 * Unregisters and destroys a session only if current session data is empty.
 */
static function destroyIfEmpty()
{
    if(self::$session_started)
    {
        if(empty($_SESSION))
        {
            if(ini_get("session.use_cookies"))
            {
                $params = session_get_cookie_params();

                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            self::$session_started = false;

            session_destroy();
        }
    }
}

/**
 * Check if a session exists for current connection.
 * @return bool True if a session is registered for current connection.
 */
static function exists()
{
    if(ini_get("session.use_cookies"))
    {
        if(isset($_COOKIE[session_name()]))
        {
            return true;
        }
    }

    if(self::$session_started)
    {
        return true;
    }

    return false;
}

/**
 * Send a cookie to client.
 * @param string  $name
 * @param mixed  $value
 * @param int $expires 0 = expires on browser exit, > 0 duration in seconds.
 * @param string  $path
 */
static function addCookie($name, $value, $expires=0, $path="/")
{
    if(is_array($value))
    {
        $value = serialize($value);
    }

    setcookie($name, $value, $expires, $path);
    $_COOKIE[$name] = $value;
}

/**
 * Remove a cookie from client.
 * @param string  $name
 * @param string  $path
 */
static function removeCookie($name, $path="/")
{
    setcookie($name, "", -1, $path="/");

    if(isset($_COOKIE[$name]))
    {
        unset($_COOKIE[$name]);
    }
}

}