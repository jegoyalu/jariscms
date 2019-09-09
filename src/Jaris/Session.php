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
     *
     * @return bool True if started false if already started.
     */
    public static function start(): bool
    {
        if (!self::$session_started) {
            // Set the session cookie attributes
            if (
            ini_get("session.use_cookies")
            &&
            session_status() != PHP_SESSION_ACTIVE
        ) {
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
     *
     * @return bool True if started false if no user logged.
     */
    public static function startIfUserLogged(): bool
    {
        if (!empty($_COOKIE["logged"])) {
            self::start();

            return true;
        } elseif (!empty($_COOKIE["signed"])) {
            return Authentication::loginByDevice();
        }

        return false;
    }

    /**
     * Close current session. Useful if current task
     * doesn't needs a session anymore and we need to unlock
     * the session for other requests to succeed in parallel.
     */
    public static function close(): void
    {
        if (self::$session_started) {
            session_write_close();

            self::$session_started = false;
        }
    }

    /**
     * Unregisters and destroys a session with all its data.
     */
    public static function destroy(): void
    {
        if (self::$session_started) {
            unset($_SESSION);

            self::destroyIfEmpty();
        }
    }

    /**
     * Unregisters and destroys a session only if current session data is empty.
     */
    public static function destroyIfEmpty(): void
    {
        if (self::$session_started) {
            if (empty($_SESSION)) {
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();

                    setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
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
     *
     * @return array
     */
    public static function getCookieParams(): array
    {
        $url = parse_url(Site::$base_url);

        $domain = $url["host"];
        $domain_parts = explode(".", $url["host"]);

        // cookie should work on www/non-www domains.
        if (count($domain_parts) > 1) {
            if ($domain_parts[0] == "www") {
                $domain_parts[0] = "";
                $domain = implode(".", $domain_parts);
            }
        }

        // If site is not running on main / path then set its current path
        $path = "/";
        if (isset($url["path"])) {
            $path = $url["path"];
        }

        return [
        "lifetime" => ini_get("session.cookie_lifetime"),
        "path" => $path,
        "domain" => $domain,
        "secure" => ini_get("session.cookie_secure"),
        "httponly" => ini_get("session.cookie_httponly")
    ];
    }

    /**
     * Check if a session exists for current connection.
     *
     * @return bool True if a session is registered for current connection.
     */
    public static function exists(): bool
    {
        if (ini_get("session.use_cookies")) {
            if (isset($_COOKIE[session_name()])) {
                return true;
            }
        }

        if (self::$session_started) {
            return true;
        }

        return false;
    }

    /**
     * Send a cookie to client.
     *
     * @param string  $name
     * @param mixed  $value
     * @param int $expires 0 = expires on browser exit, > 0 duration in seconds.
     * @param string  $path
     */
    public static function addCookie(
    string $name,
    $value,
    int $expires=0,
    string $path="/"
): void {
        if (is_array($value)) {
            $value = serialize($value);
        }

        $cookie_params = self::getCookieParams();

        setcookie(
        $name,
        strval($value),
        $expires,
        $path == "/" ? $cookie_params["path"] : $path,
        $cookie_params["domain"]
    );

        $name_parts = explode("[", $name);

        if (count($name_parts) > 1) {
            $name_parts = array_map(
            function ($e) {
                return trim($e, "]");
            },
            $name_parts
        );
            $name_parts_count = count($name_parts);
            $last_index = $name_parts_count - 1;

            for ($i=0; $i<$name_parts_count; $i++) {
                if ($i != $last_index) {
                    if ($i == 0) {
                        if (!is_array($_COOKIE[$name_parts[$i]])) {
                            $_COOKIE[$name_parts[$i]] = [];
                        }
                    } elseif ($i == 1) {
                        if (
                        !is_array(
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[$i]]
                        )
                    ) {
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[$i]] = []
                        ;
                        }
                    } elseif ($i == 2) {
                        if (
                        !is_array(
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[1]]
                                [$name_parts[$i]]
                        )
                    ) {
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[1]]
                                [$name_parts[$i]] = []
                        ;
                        }
                    } elseif ($i == 3) {
                        if (
                        !is_array(
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[1]]
                                [$name_parts[2]]
                                [$name_parts[$i]]
                        )
                    ) {
                            $_COOKIE[$name_parts[0]]
                                [$name_parts[1]]
                                [$name_parts[2]]
                                [$name_parts[$i]] = []
                        ;
                        }
                    } elseif ($i > 3) {
                        throw new \Exception("Cookie index too deep.");
                    }
                } else {
                    if ($i == 0) {
                        $_COOKIE[$name_parts[$i]] = $value;
                    } elseif ($i == 1) {
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[$i]] = $value
                    ;
                    } elseif ($i == 2) {
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[$i]] = $value
                    ;
                    } elseif ($i == 3) {
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[2]]
                            [$name_parts[$i]] = $value
                    ;
                    }
                }
            }
        } else {
            $_COOKIE[$name] = $value;
        }
    }

    /**
     * Remove a cookie from client.
     *
     * @param string  $name
     * @param string  $path
     */
    public static function removeCookie(string $name, string $path="/")
    {
        $cookie_params = self::getCookieParams();

        setcookie(
        $name,
        "",
        -1,
        $path == "/" ? $cookie_params["path"] : $path,
        $cookie_params["domain"]
    );

        $name_parts = explode("[", $name);

        if (count($name_parts) > 1) {
            $name_parts = array_map(
            function ($e) {
                return trim($e, "]");
            },
            $name_parts
        );
            $last_index = count($name_parts) - 1;

            switch ($last_index) {
            case 1:
            {
                if (
                    isset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                    )
                ) {
                    unset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                    );
                }
                break;
            }
            case 2:
            {
                if (
                    isset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[2]]
                    )
                ) {
                    unset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[2]]
                    );
                }
                break;
            }
            case 3:
            {
                if (
                    isset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[2]]
                            [$name_parts[3]]
                    )
                ) {
                    unset(
                        $_COOKIE[$name_parts[0]]
                            [$name_parts[1]]
                            [$name_parts[2]]
                            [$name_parts[3]]
                    );
                }
                break;
            }
            default:
                throw new \Exception("Cookie index too deep.");
        }
        } else {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
            }
        }
    }
}
