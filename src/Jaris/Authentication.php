<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to manage login and logout as protected pages.
 */
class Authentication
{

/**
 * Checks if a user is logged in.
 *
 * @return bool true if user is logged or false if not.
 */
    public static function isUserLogged(): bool
    {
        static $user_data;

        if (!isset($_COOKIE["logged"]) || !isset($_SESSION["logged"])) {
            return false;
        }

        //To reduce file access
        if (!$user_data) {
            $user_data = Users::get($_SESSION["logged"]["username"]);
        }

        //Remove the optional www for problems from www and non www links
        $logged_site = str_replace(
        ["http://", "https://", "www."],
        "",
        isset($_SESSION["logged"]) ? $_SESSION["logged"]["site"] : ""
    );

        $base_url_parsed = str_replace(
        ["http://", "https://", "www."],
        "",
        Site::$base_url
    );

        if (
        $logged_site == $base_url_parsed &&
        $user_data["password"] == $_SESSION["logged"]["password"] &&
        (
            $_SESSION["logged"]["user_agent"] == $_SERVER["HTTP_USER_AGENT"] ||
            //Enable flash uploaders that send another agent
            ($_SERVER["HTTP_USER_AGENT"] == "Shockwave Flash" && isset($_FILES))
        )
    ) {
            //If validation by ip is enabled check if ip the same to continue
            if (Settings::get("validate_ip", "main")) {
                if ($_SESSION["logged"]["ip_address"] != $_SERVER["REMOTE_ADDR"]) {
                    self::logout();
                    return false;
                }
            }

            $_SESSION["logged"]["group"] = $user_data["group"];

            return true;
        } else {
            self::logout();
            return false;
        }
    }

    /**
     * Checks if the administrator is logged in.
     *
     * @return bool true if the admin is logged or false if not.
     */
    public static function isAdminLogged(): bool
    {
        if (self::currentUserGroup() == "administrator") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Login a user to the site if username and password
     * is correct on a form submit.
     *
     * @return bool true on success or false on incorrect login.
     */
    public static function login(): bool
    {
        if (self::loginByDevice()) {
            return true;
        }

        $is_logged = false;

        //Remove the optional www for problems from www and non www links
        $logged_site = str_replace(
        ["http://", "https://", "www."],
        "",
        isset($_SESSION["logged"]) ? $_SESSION["logged"]["site"] : ""
    );

        $base_url_parsed = str_replace(
        ["http://", "https://", "www."],
        "",
        Site::$base_url
    );

        if ($logged_site != $base_url_parsed) {
            $user_data = [];
            $username = trim($_REQUEST["username"]);

            if (strstr($username, "@") !== false) {
                $user_data = Users::getByEmail($username);
                $username = $user_data["username"];
            } else {
                $user_data = Users::get($username);
            }

            $login_fails = 0;

            if (
            $user_data
            &&
            crypt(
                $_REQUEST["password"],
                $user_data["password"]
            )
            ==
            $user_data["password"]
        ) {
                if ($user_data["login_fails"] > 9) {
                    View::addMessage(
                    t("For security the failed attempts to login into the account resulted in account lock down.")
                );
                    View::addMessage(
                    t("A password reset procedure is required to regain the account.")
                );

                    Uri::go("forgot-password", ["username" => $username]);
                }

                $groups_approval = unserialize(
                Settings::get("registration_groups_approval", "main")
            );

                if (
                (
                    Settings::get("registration_needs_approval", "main")
                    &&
                    $user_data["status"] == "0"
                    &&
                    !Settings::get("registration_can_select_group", "main")
                ) ||
                (
                    Settings::get("registration_can_select_group", "main")
                    &&
                    $user_data["status"] == "0"
                    &&
                    in_array($user_data["group"], $groups_approval)
                )
            ) {
                    View::addMessage(
                    t("Your registration is awaiting for approval. If the registration is approved you will receive an email notification.")
                );

                    return $is_logged;
                }

                if (
                Settings::get("registration_needs_activation", "main")
                &&
                $user_data["email_activated"] == "0"
            ) {
                    View::addMessage(
                    t("Your account is pending activation by email.")
                        . " "
                        . t("To resend the activation e-mail click here: ")
                        . "<a target=\"_blank\" href=\""
                        . Uri::url(
                            "account/reactivate",
                            [
                                "u" => $username,
                            ]
                        )
                        . "\">"
                        . t("re-send activation e-mail")
                        . "</a>"
                );

                    return $is_logged;
                }

                Session::start();

                $_SESSION["logged"]["site"] = Site::$base_url;
                $_SESSION["logged"]["username"] = strtolower($username);
                $_SESSION["logged"]["password"] = $user_data["password"];
                $_SESSION["logged"]["group"] = $user_data["group"];
                $_SESSION["logged"]["ip_address"] = $_SERVER["REMOTE_ADDR"];
                $_SESSION["logged"]["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

                Session::addCookie("logged", "1");

                if (!empty($_REQUEST["remember_me"])) {
                    $token = Users::generatePassword(64);
                    $expires = time() + (365 * 24 * 60 * 60);

                    $user_data["devices"][$token] = [
                    "expires" => $expires,
                    "device" => Util::parseUserAgent(),
                    "last_ip" => $_SERVER["REMOTE_ADDR"]
                ];

                    Session::addCookie("signed", $username."||".$token, $expires);
                }

                //Save last ip used
                $user_data["ip_address"] = $_SERVER["REMOTE_ADDR"];

                // Remove failed login attempts
                if (isset($user_data["login_fails"])) {
                    unset($user_data["login_fails"]);
                }

                Users::edit($username, $user_data["group"], $user_data);

                //Keep user uploads dir clean
                Forms::deleteUploads();

                $is_logged = true;
            } elseif ($user_data) {
                $login_fails = true;
                if (isset($user_data["login_fails"])) {
                    $user_data["login_fails"] += 1;
                } else {
                    $user_data["login_fails"] = 1;
                }

                $login_fails = $user_data["login_fails"];

                if ($user_data["login_fails"] <= 10) {
                    Users::edit($username, $user_data["group"], $user_data);
                }

                if ($user_data["login_fails"] > 9) {
                    View::addMessage(
                    t("For security the failed attempts to login into the account resulted in account lock down.")
                );
                    View::addMessage(
                    t("A password reset procedure is required to regain the account.")
                );

                    Uri::go("forgot-password", ["username" => $username]);
                } elseif ($user_data["login_fails"] > 5) {
                    View::addMessage(
                    sprintf(
                        t("For security reasons you have %s more tries before your account is locked down."),
                        10 - $user_data["login_fails"]
                    )
                );
                    View::addMessage(t("If your account is locked down you will have to click the forgot password link to start a password reset procedure."));
                }
            } else {
                if (isset($_SESSION["logged"])) {
                    $_SESSION["logged"]["site"] = false;
                }

                $is_logged = false;
            }

            if (
            isset($_REQUEST["username"])
            &&
            isset($_REQUEST["password"])
            &&
            $is_logged == false
        ) {
                if ($login_fails == 0) {
                    View::addMessage(
                    t("The username, e-mail or password you entered is incorrect."),
                    "error"
                );
                } elseif ($login_fails <= 5) {
                    View::addMessage(
                    t("The password you entered is incorrect."),
                    "error"
                );
                }
            }
        }

        return $is_logged;
    }

    /**
     * Authenticates a user by device cookie.
     *
     * @return bool
     */
    public static function loginByDevice(): bool
    {
        if (isset($_COOKIE["signed"])) {
            $credentials = explode("||", $_COOKIE["signed"], 2);

            $username = $credentials[0];
            $token = $credentials[1];

            $user_data = Users::get($username);

            if (
            !empty($user_data)
            &&
            is_array($user_data["devices"])
            &&
            count($user_data["devices"]) > 0
        ) {
                //Remove expired devices
                $devices = $user_data["devices"];
                $devices_expired = false;
                foreach ($devices as $device_token => $device_data) {
                    if ($device_data["expires"] < time()) {
                        unset($user_data["devices"][$device_token]);
                        $devices_expired = true;
                    }
                }

                $device = isset($user_data["devices"][$token]) ?
                $user_data["devices"][$token]["device"]
                :
                []

            ;

                $agent = Util::parseUserAgent();

                if (
                !empty($device)
                &&
                $device["platform"] == $agent["platform"]
                &&
                $device["browser"] == $agent["browser"]
            ) {
                    Session::start();

                    $_SESSION["logged"]["site"] = Site::$base_url;
                    $_SESSION["logged"]["username"] = strtolower($username);
                    $_SESSION["logged"]["password"] = $user_data["password"];
                    $_SESSION["logged"]["group"] = $user_data["group"];
                    $_SESSION["logged"]["ip_address"] = $_SERVER["REMOTE_ADDR"];
                    $_SESSION["logged"]["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

                    Session::addCookie("logged", "1");

                    //Increase expiration time
                    $expires = time() + (365 * 24 * 60 * 60);
                    $user_data["devices"][$token]["expires"] = $expires;
                    Session::addCookie("signed", $username."||".$token, $expires);

                    //Save last ip used
                    $user_data["devices"][$token]["last_ip"] = $_SERVER["REMOTE_ADDR"];
                    $user_data["ip_address"] = $_SERVER["REMOTE_ADDR"];

                    //Save user changes
                    Users::edit($username, $user_data["group"], $user_data);

                    //Keep user uploads dir clean
                    Forms::deleteUploads();

                    return true;
                } elseif ($devices_expired) {
                    Users::edit($username, $user_data["group"], $user_data);
                    Session::removeCookie("signed");
                }
            }
        }

        return false;
    }

    /**
     * Logs out the user from the system by clearing the needed session variables.
     */
    public static function logout(): void
    {
        if (isset($_SESSION["logged"])) {
            if (!empty($_COOKIE["signed"])) {
                $username = $_SESSION["logged"]["username"];

                $user_data = Users::get($username);

                $token = explode("||", $_COOKIE["signed"], 2)[1];

                if (isset($user_data["devices"][$token])) {
                    unset($user_data["devices"][$token]);

                    Users::edit($username, $user_data["group"], $user_data);
                }

                Session::removeCookie("signed");
            }

            unset($_SESSION["logged"]);

            Session::removeCookie("logged");

            Session::destroyIfEmpty();
        }
    }

    /**
     * Get the group of the current logged user.
     *
     * @return string The user group if logged or guest if anonymous.
     */
    public static function currentUserGroup(): string
    {
        if (self::isUserLogged()) {
            return $_SESSION["logged"]["group"];
        } else {
            return "guest";
        }
    }

    /**
     * Get the current logged user.
     *
     * @return string The machine name of the logged user.
     */
    public static function currentUser(): string
    {
        if (self::isUserLogged()) {
            return $_SESSION["logged"]["username"];
        } else {
            return "Guest";
        }
    }

    /**
     * Protects a page from guess access redirecting to an access denied page.
     * Used on pages where the administrator should be logged in or user with
     * proper permissions.
     *
     * @param array $permissions In the format permissions[] = machine_name
     */
    public static function protectedPage(array $permissions = []): void
    {
        if (self::isAdminLogged()) {
            return;
        } elseif ($permissions) {
            $group = self::currentUserGroup();

            foreach ($permissions as $machine_name) {
                if (!self::groupHasPermission($machine_name, $group)) {
                    Site::setHTTPStatus(401);
                    Uri::go("access-denied");
                }
            }

            return;
        }

        Site::setHTTPStatus(401);
        Uri::go("access-denied");
    }

    /**
     * Check if a user has the given permissions.
     *
     * @param array $permissions In the format permissions[] = machine_name
     * @param string $username If not specified current user permissions are checked.
     *
     * @return bool True if has permissions false otherwise.
     */
    public static function userHasPermissions(
    array $permissions,
    string $username = ""
): bool {
        if (!self::isAdminLogged()) {
            $group = self::currentUserGroup();

            if ($username) {
                $user_data = Users::get($username);
                $group = $user_data["group"];
            }

            foreach ($permissions as $machine_name) {
                if (!self::groupHasPermission($machine_name, $group)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Gets the value of a given permission.
     *
     * @param string $permission_name The machine name of the permission.
     * @param string $group_name The group we want to get permission value from.
     *
     * @return bool True if the group has the permissions or false.
     */
    public static function groupHasPermission(
    string $permission_name,
    string $group_name
): bool {
        static $permission_table;

        if ($group_name == "administrator") {
            return true;
        }

        if (!$permission_table) {
            $permissions_data_path = Site::dataDir()
            . "groups/$group_name/data.php"
        ;

            $permissions_data_path = str_replace(
            "/data.php",
            "/permissions.php",
            $permissions_data_path
        );

            if (file_exists($permissions_data_path)) {
                $permission_table = Data::parse($permissions_data_path);
            }
        }

        if ($permission_table) {
            return (bool) trim($permission_table[0][$permission_name]);
        }

        return false;
    }

    /**
     * Gets the permission status of a given type for a users group.
     *
     * @param string $type The machine name of the type.
     * @param string $group_name The group we want to get permission value from.
     * @param string $username If passed also checks max posts amount hasnt
     * been reached for the user.
     *
     * @return bool True if the group has the permissions or false.
     */
    public static function hasTypeAccess(
    string $type,
    string $group_name,
    string $username = ""
): bool {
        if (self::groupHasPermission($type . "_type", $group_name)) {
            if ($username) {
                if (Types::userReachedMaxPosts($type, $username)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
