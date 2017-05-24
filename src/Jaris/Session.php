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
        // Set the session cookie attributes
        if(ini_get("session.use_cookies"))
        {
            $cookie_params = self::getCookieParams();

            session_set_cookie_params(
                $cookie_params["lifetime"],
                $cookie_params["path"],
                $cookie_params["domain"]
            );
        }

        // Register the session
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
 * Close current session. Useful if current task
 * doesn't needs a session anymore and we need to unlock
 * the session for other requests to succeed in parallel.
 */
static function close()
{
    if(self::$session_started)
    {
        session_write_close();

        self::$session_started = false;
    }
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
 * Function that returns proper domain and path values for a cookie that
 * should work on www and non-www domains as sites installed on lower paths.
 * @return array
 */
static function getCookieParams()
{
    $url = parse_url(Site::$base_url);

    $domain = $url["host"];
    $domain_parts = explode(".", $url["host"]);

    // cookie should work on www/non-www domains.
    if(count($domain_parts) > 1)
    {
        if($domain_parts[0] == "www")
        {
            $domain_parts[0] = "";
            $domain = implode(".", $domain_parts);
        }
    }
    else
    {
        $domain = "." . $domain;
    }

    // If site is not running on main / path then set its current path
    $path = "/";
    if(isset($url["path"]))
    {
        $path = $url["path"];
    }

    return array(
        "lifetime" => ini_get("session.cookie_lifetime"),
        "path" => $path,
        "domain" => $domain,
        "secure" => ini_get("session.cookie_secure"),
        "httponly" => ini_get("session.cookie_httponly")
    );
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

    $cookie_params = self::getCookieParams();

    setcookie(
        $name,
        $value,
        $expires,
        $path == "/" ? $cookie_params["path"] : $path,
        $cookie_params["domain"]
    );

    $_COOKIE[$name] = $value;
}

/**
 * Remove a cookie from client.
 * @param string  $name
 * @param string  $path
 */
static function removeCookie($name, $path="/")
{
    $cookie_params = self::getCookieParams();

    setcookie(
        $name,
        "",
        -1,
        $path == "/" ? $cookie_params["path"] : $path,
        $cookie_params["domain"]
    );

    if(isset($_COOKIE[$name]))
    {
        unset($_COOKIE[$name]);
    }
}

}