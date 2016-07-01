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
 * @original is_user_logged
 */
static function isUserLogged()
{
    static $user_data;

    if(!isset($_SESSION))
        return false;

    //To reduce file access
    if(!$user_data)
    {
        $user_data = Users::get($_SESSION["logged"]["username"]);
    }

    //Remove the optional www for problems from www and non www links
    $logged_site = str_replace(
        array("http://", "https://", "www."),
        "",
        isset($_SESSION["logged"]) ? $_SESSION["logged"]["site"] : ""
    );

    $base_url_parsed = str_replace(
        array("http://", "https://", "www."),
        "",
        Site::$base_url
    );

    if(
        $logged_site == $base_url_parsed &&
        $user_data["password"] == $_SESSION["logged"]["password"] &&
        (
            $_SESSION["logged"]["user_agent"] == $_SERVER["HTTP_USER_AGENT"] ||
            //Enable flash uploaders that send another agent
            ($_SERVER["HTTP_USER_AGENT"] == "Shockwave Flash" && isset($_FILES))
        )
    )
    {
        //If validation by ip is enabled check if ip the same to continue
        if(Settings::get("validate_ip", "main"))
        {
            if($_SESSION["logged"]["ip_address"] != $_SERVER["REMOTE_ADDR"])
            {
                self::logout();
                return false;
            }
        }

        $_SESSION["logged"]["group"] = $user_data["group"];

        return true;
    }
    else
    {
        self::logout();
        return false;
    }
}

/**
 * Checks if the administrator is logged in.
 *
 * @return bool true if the admin is logged or false if not.
 * @original is_admin_logged
 */
static function isAdminLogged()
{
    if(self::currentUserGroup() == "administrator")
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Login a user to the site if username and password
 * is correct on a form submit.
 *
 * @return bool true on success or false on incorrect login.
 * @original user_login
 */
static function login()
{
    $is_logged = false;

    //Remove the optional www for problems from www and non www links
    $logged_site = str_replace(
        array("http://", "https://", "www."),
        "",
        $_SESSION["logged"]["site"]
    );

    $base_url_parsed = str_replace(
        array("http://", "https://", "www."),
        "",
        Site::$base_url
    );

    if($logged_site != $base_url_parsed)
    {
        $user_data = array();

        if(Forms::validEmail($_REQUEST["username"]))
        {
            $user_data = Users::getByEmail($_REQUEST["username"]);
            $_REQUEST["username"] = $user_data["username"];
        }
        else
        {
            $user_data = Users::get($_REQUEST["username"]);
        }

        if(
            $user_data &&
            crypt($_REQUEST["password"], $user_data["password"]) == $user_data["password"]
        )
        {
            $groups_approval = unserialize(
                Settings::get("registration_groups_approval", "main")
            );

            if(
                (
                    Settings::get("registration_needs_approval", "main") &&
                    $user_data["status"] == "0" &&
                    !Settings::get("registration_can_select_group", "main")
                ) ||
                (
                    Settings::get("registration_can_select_group", "main") &&
                    $user_data["status"] == "0" &&
                    in_array($user_data["group"], $groups_approval)
                )
            )
            {
                View::addMessage(t("Your registration is awaiting for approval. If the registration is approved you will receive an email notification."));

                return $is_logged;
            }

            $_SESSION["logged"]["site"] = Site::$base_url;
            $_SESSION["logged"]["username"] = strtolower($_REQUEST["username"]);
            $_SESSION["logged"]["password"] = $user_data["password"];
            $_SESSION["logged"]["group"] = $user_data["group"];
            $_SESSION["logged"]["ip_address"] = $_SERVER["REMOTE_ADDR"];
            $_SESSION["logged"]["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

            setcookie("logged", "1", 0, "/");
            $_COOKIE["logged"] = 1;

            //Save last ip used
            $user_data["ip_address"] = $_SERVER["REMOTE_ADDR"];
            Users::edit($_REQUEST["username"], $user_data["group"], $user_data);

            //Keep user uploads dir clean
            Forms::deleteUploads();

            $is_logged = true;
        }
        else
        {
            $_SESSION["logged"]["site"] = false;
            $is_logged = false;
        }

        if(
            isset($_REQUEST["username"]) &&
            isset($_REQUEST["password"]) &&
            $is_logged == false
        )
        {
            View::addMessage(
                t("The username or password you entered is incorrect."),
                "error"
            );
        }
    }

    return $is_logged;
}

/**
 * Logs out the user from the system by clearing the needed session variables.
 * @original user_logout
 */
static function logout()
{
    unset($_SESSION["logged"]);

    setcookie("logged", "", -1, "/");
    unset($_COOKIE["logged"]);
}

/**
 * Get the group of the current logged user.
 *
 * @return string The user group if logged or guest if anonymous.
 * @original current_user_group
 */
static function currentUserGroup()
{
    if(self::isUserLogged())
    {
        return $_SESSION["logged"]["group"];
    }
    else
    {
        return "guest";
    }
}

/**
 * Get the current logged user.
 *
 * @return string The machine name of the logged user.
 * @original current_user
 */
static function currentUser()
{
    if(self::isUserLogged())
    {
        return $_SESSION["logged"]["username"];
    }
    else
    {
        return "Guest";
    }
}

/**
 * Protects a page from guess access redirecting to an access denied page.
 * Used on pages where the administrator should be logged in or user with
 * proper permissions.
 *
 * @param array $permissions In the format permissions[] = machine_name
 * @original protected_page
 */
static function protectedPage($permissions = array())
{
    if(self::isAdminLogged())
    {
        return;
    }
    elseif($permissions)
    {
        $group = self::currentUserGroup();

        foreach($permissions as $machine_name)
        {
            if(!self::groupHasPermission($machine_name, $group))
            {
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
 * @original user_has_permissions
 */
static function userHasPermissions($permissions, $username = null)
{
    if(!self::isAdminLogged())
    {
        $group = self::currentUserGroup();

        if($username != null)
        {
            $user_data = Users::get($username);
            $group = $user_data["group"];
        }

        foreach($permissions as $machine_name)
        {
            if(!self::groupHasPermission($machine_name, $group))
            {
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
 * @original get_group_permission
 */
static function groupHasPermission($permission_name, $group_name)
{
    static $permission_table;

    if($group_name == "administrator")
    {
        return true;
    }

    if(!$permission_table)
    {
        $permissions_data_path = Site::dataDir()
            . "groups/$group_name/data.php"
        ;

        $permissions_data_path = str_replace(
            "/data.php", "/permissions.php", $permissions_data_path
        );

        if(file_exists($permissions_data_path))
        {
            $permission_table = Data::parse($permissions_data_path);
        }
    }

    if($permission_table)
    {
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
 * @original get_type_permission
 */
static function hasTypeAccess($type, $group_name, $username = "")
{
    if(self::groupHasPermission($type . "_type", $group_name))
    {
        if($username)
        {
            if(Types::userReachedMaxPosts($type, $username))
                return false;
        }

        return true;
    }

    return false;
}

}