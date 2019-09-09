<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * The functions to manage users.
 */
class Users
{

/**
 * Receives parameters: $username, $group, $fields, $picture
 * @var string
 */
    const SIGNAL_ADD_USER = "hook_add_user";

    /**
     * Receives parameters: $username, $group
     * @var string
     */
    const SIGNAL_DELETE_USER = "hook_delete_user";

    /**
     * Receives parameters: $username, $group, $new_data, $picture
     * @var string
     */
    const SIGNAL_EDIT_USER = "hook_edit_user";

    /**
     * Receives parameters: $username, $user_data
     * @var string
     */
    const SIGNAL_GET_USER_DATA = "hook_get_user_data";

    /**
     * Receives parameters: $username, $user_data
     * @var string
     */
    const SIGNAL_GET_USER_DATA_BY_EMAIL = "hook_get_user_data_by_email";

    /**
     * Receives parameters: $content, $tabs, $user_data
     * @var string
     */
    const SIGNAL_PRINT_USER_PAGE = "hook_print_user_page";

    /**
     * Receives parameters: $page, $username
     * @var string
     */
    const SIGNAL_SHOW_USER_PROFILE = "hook_show_user_profile";

    /**
     * Adds a new username record to the system.
     *
     * @param string $username The username to create on the system.
     * @param string $group The group where the user is going to belong.
     * @param array $fields An array with the needed fields to write to the username.
     * @param array $picture An array in the format returned by $_FILES["element"] array.
     *
     * @return string "true" string on success error message on false.
     */
    public static function add(
    string $username,
    string $group,
    array $fields,
    array $picture = []
): string {
        $username = strtolower($username);
        $user_exist = self::exists($username);

        $fields["group"] = $group;

        if (!$user_exist) {
            //Call add_user hook before adding the user
            Modules::hook("hook_add_user", $username, $group, $fields, $picture);

            $user_data_path = self::getPath($username, $group);

            //Create user directory
            $path = str_replace("data.php", "", $user_data_path);
            FileSystem::makeDir($path, 0755, true);

            //If uploaded picture save it
            if (isset($picture["tmp_name"]) && trim($picture["tmp_name"]) != "") {
                if (Images::isValid($picture["tmp_name"])) {
                    $picture_path = $path . $picture["name"];

                    $picture_name = FileSystem::move(
                    $picture["tmp_name"],
                    $picture_path
                );

                    $fields["picture"] = $picture_name;
                }
            }

            //Encrypt user password
            $fields["password"] = crypt($fields["password"]);

            if (
            isset($fields["devices"])
            &&
            is_array($fields["devices"])
        ) {
                $fields["devices"] = serialize(
                $fields["devices"]
            );
            }

            if (!Data::add($fields, $user_data_path)) {
                return System::errorMessage("write_error_data");
            }
        } else {
            return System::errorMessage("user_exist");
        }

        self::addIndex($username, $fields);

        //Update cache_events folder
        if (!file_exists(Site::dataDir() . "cache_events")) {
            FileSystem::makeDir(Site::dataDir() . "cache_events");
        }

        file_put_contents(Site::dataDir() . "cache_events/new_user", "");

        return "true";
    }

    /**
     * Deletes an existing username.
     *
     * @param string $username The username to delete.
     *
     * @return bool True on success false on fail.
     */
    public static function delete(string $username): bool
    {
        $username = strtolower($username);
        $user_exist = self::exists($username);

        if ($user_exist) {
            //Call delete_user hook before deleting the user
            Modules::hook("hook_delete_user", $username, $user_exist["group"]);

            $user_data_path = $user_exist["path"];

            $user_path = str_replace("/data.php", "", $user_data_path);

            //Remove main user directory
            if (!FileSystem::recursiveRemoveDir($user_path)) {
                return false;
            }

            self::deleteIndex($username);

            //Remove old data/users/group_name/X/XX if empty
            rmdir(
            Site::dataDir() . "users/{$user_exist['group']}/" .
            substr($username, 0, 1) . "/" . substr($username, 0, 2)
        );

            //Remove old data/users/group_name/X if empty
            rmdir(
            Site::dataDir() . "users/{$user_exist['group']}/" .
            substr($username, 0, 1)
        );
        }

        return true;
    }

    /**
     * Edits or changes the data of an existing user.
     *
     * @param string $username The username.
     * @param string $group A group where we want to change the user
     * or the same actual group
     * @param array $new_data An array of the fields that will
     * substitue the old values.
     * @param array $picture An array in the format returned
     * by $_FILES["element"] array.
     *
     * @return string "true" string on success error message on false.
     */
    public static function edit(
    string $username,
    string $group,
    array $new_data,
    array $picture = []
): string {
        $username = strtolower($username);
        $user_exist = self::exists($username);

        if ($user_exist) {
            //Call edit_user hook before editing the user
            Modules::hook(
            "hook_edit_user",
            $username,
            $user_exist["group"],
            $new_data,
            $picture
        );

            $user_data_path = $user_exist["path"];

            if ($picture && strlen($picture["tmp_name"]) > 0) {
                if (Images::isValid($picture["tmp_name"])) {
                    //Delete static image
                    $static_image_path = Files::get(
                    "user-".$username.".png",
                    "static_image"
                );

                    if ($static_image_path != "") {
                        @unlink($static_image_path);
                    }

                    $path = str_replace("data.php", "", $user_data_path);
                    $picture_path = $path . $picture["name"];
                    $previous_picture = self::getPicturePath($username);

                    //In case picture already exist with same name delete it.
                    @unlink($picture_path);

                    //Delete previous picture if any.
                    if (
                    $previous_picture != System::CSS_PATH."images/male.png" &&
                    $previous_picture != System::CSS_PATH."images/female.png"
                ) {
                        @unlink($previous_picture);
                    }

                    $picture_name = FileSystem::move($picture["tmp_name"], $picture_path);

                    $new_data["picture"] = $picture_name;
                }
            }

            if (
            isset($new_data["devices"])
            &&
            is_array($new_data["devices"])
        ) {
                $new_data["devices"] = serialize(
                $new_data["devices"]
            );
            }

            if (!Data::edit(0, $new_data, $user_data_path)) {
                return System::errorMessage("write_error_data");
            }

            self::editIndex($username, $new_data);

            //Change user group
            if ($group != $user_exist["group"]) {
                $user_path = str_replace("/data.php", "", $user_data_path);

                $new_path = self::getPath($username, $group);
                $new_path = str_replace("/data.php", "", $new_path);

                //Make new user path
                FileSystem::makeDir($new_path, 0755, true);

                //Move user data to new group
                FileSystem::recursiveMoveDir($user_path, $new_path);

                //Remove old main user directory
                FileSystem::recursiveRemoveDir($user_path);

                //Remove old data/users/group_name/X/XX if empty
                @rmdir(
                Site::dataDir() . "users/{$user_exist['group']}/" .
                substr($username, 0, 1) . "/" . substr($username, 0, 2)
            );

                //Remove old data/users/group_name/X if empty
                @rmdir(
                Site::dataDir() . "users/{$user_exist['group']}/" .
                substr($username, 0, 1)
            );
            }
        } else {
            return System::errorMessage("user_not_exist");
        }

        return "true";
    }

    /**
     * Get an array with data of a specific user.
     *
     * @param string $username The username.
     *
     * @return array An array with all the rows and fields of
     * the username or empty array if not exists.
     */
    public static function get(string $username): array
    {
        $username = strtolower($username);
        $user_exist = self::exists($username);

        if ($user_exist) {
            $user_data_path = $user_exist["path"];

            $user_data = Data::parse($user_data_path);

            if ($user_data) {
                $user_data[0]["password"] = trim($user_data[0]["password"]);
                $user_data[0]["group"] = $user_exist["group"];

                if (
                isset($user_data[0]["devices"])
                &&
                !empty($user_data[0]["devices"])
            ) {
                    $user_data[0]["devices"] = unserialize(
                    $user_data[0]["devices"]
                );
                } else {
                    $user_data[0]["devices"] = [];
                }

                if (!isset($user_data[0]["picture"])) {
                    $user_data[0]["picture"] = "";
                }

                $user_data[0]["picture"] = trim($user_data[0]["picture"]);

                //Call get_user_data hook before returning the user data
                Modules::hook("hook_get_user_data", $username, $user_data);

                return $user_data[0];
            }
        } else {
            return [];
        }
    }

    /**
     * Get an array with data of a specific user by its email.
     *
     * @param string $email The email of the user.
     *
     * @return array User data array or empty array if fail.
     */
    public static function getByEmail(string $email): array
    {
        if (trim($email) == "") {
            return [];
        }

        $email = str_replace("'", "''", trim($email));

        if (Sql::dbExists("users")) {
            $db = Sql::open("users");

            Sql::turbo($db);

            $result = Sql::query(
            "select * from users where email = '$email'",
            $db
        );

            $user_data_sqlite = Sql::fetchArray($result);

            Sql::close($db);

            if ($user_data_sqlite) {
                $user_data = self::get($user_data_sqlite["username"]);
                $user_data["username"] = $user_data_sqlite["username"];

                //Call get_user_data_by_email hook before returning the user data
                Modules::hook(
                "hook_get_user_data_by_email",
                $user_data_sqlite["username"],
                $user_data
            );

                return $user_data;
            }
        }

        return [];
    }

    /**
     * Gets the path where user temporary uploads are stored.
     *
     * @param string $username The user we are getting the picture from.
     *
     * @return string The path to the upload dir or empty string if user not exists.
     */
    public static function getUploadsPath(string $username): string
    {
        if ($user_info = self::exists($username)) {
            $upload_dir = str_replace(
            "data.php",
            "uploads/",
            self::getPath($username, $user_info["group"])
        );

            if (!is_dir($upload_dir)) {
                FileSystem::makeDir($upload_dir);
            }

            return $upload_dir;
        }

        return "";
    }

    /**
     * Gets the path of the user picture.
     *
     * @param string $username The user we are getting the picture from.
     *
     * @return string The path to the user picture or empty
     * string if user not exists.
     */
    public static function getPicturePath(string $username): string
    {
        $username = strtolower($username);

        if ($user_info = self::exists($username)) {
            $user_data = self::get($username);

            if ($user_data && strlen($user_data["picture"]) > 0) {
                $user_picture = $user_info["path"];
                $user_picture = str_replace("data.php", "", $user_picture);
                $user_picture .= $user_data["picture"];

                return $user_picture;
            } else {
                switch ($user_data["gender"]) {
                case "m":
                    return System::CSS_PATH."images/male.png";
                case "f":
                    return System::CSS_PATH."images/female.png";
                default:
                    return System::CSS_PATH."images/male.png";
            }
            }
        }

        return "";
    }

    /**
     * static function to retrieve a user uploaded profile picture or
     * generic one in case none available.
     *
     * @param string $username The login name of the user.
     *
     * @return string Path of the user picture file.
     */
    public static function getPictureUrl(string $username): string
    {
        $username = strtolower($username);

        if ($user_info = self::exists($username)) {
            $user_picture = Uri::url("image/user/$username");

            return $user_picture;
        }

        return "";
    }

    /**
     * Checks if a user already exists.
     *
     * @param string $username The username to check for existence.
     *
     * @return array Array in the format array(path, group) if exist or
     * empty array if not.
     */
    public static function exists(string $username): array
    {
        $username = strtolower($username);

        $dir_handle = opendir(Site::dataDir() . "users");

        if (!is_bool($dir_handle)) {
            while (($group_directory = readdir($dir_handle)) !== false) {
                //just check directories inside
                if (
                strcmp($group_directory, ".") != 0 &&
                strcmp($group_directory, "..") != 0
            ) {
                    $user_data_path = self::getPath(
                    $username,
                    $group_directory
                );

                    if (file_exists($user_data_path)) {
                        return [
                        "path" => $user_data_path,
                        "group" => $group_directory
                    ];
                    }
                }
            }
        }

        return [];
    }

    /**
     * Add a username and its email to the users sqlite database.
     *
     * @param string $username The username used to log in on the system.
     * @param array $data All the user data to extract only the email.
     */
    public static function addIndex(string $username, array $data): void
    {
        $username = strtolower($username);
        if (!Sql::dbExists("users")) {
            $db = Sql::open("users");

            Sql::query("PRAGMA journal_mode=WAL", $db);

            Sql::query(
            "create table users (username text, email text, " .
            "register_date text, user_group text, picture text, " .
            "ip_address text, gender text, birth_date text, status text)",
            $db
        );

            Sql::query(
            "create index users_index on users " .
            "(username desc, email desc, register_date desc, " .
            "user_group asc, gender desc, birth_date desc, status desc)",
            $db
        );

            Sql::close($db);
        }

        $db = Sql::open("users");

        $data["username"] = $username;

        Sql::escapeArray($data);

        Sql::query(
        "insert into users (username, email, register_date, user_group, " .
        "picture, ip_address, gender, birth_date, status) " .
        "values ('{$data['username']}', '{$data['email']}'," .
        "'{$data['register_date']}', '{$data['group']}', '{$data['picture']}'," .
        "'{$data['ip_address']}', '{$data['gender']}'," .
        "'{$data['birth_date']}', '{$data['status']}')",
        $db
    );

        Sql::close($db);
    }

    /**
     * Edit an existing user email on the sqlite users database,
     * used when updating user data.
     *
     * @param string $username The username used to log in.
     * @param array $data All the data of the username to extract email.
     */
    public static function editIndex(string $username, array $data): void
    {
        $username = strtolower($username);
        if (Sql::dbExists("users")) {
            $db = Sql::open("users");

            Sql::escapeArray($data);

            Sql::query(
            "update users set " .
            "email = '{$data['email']}'," .
            "user_group = '{$data['group']}'," .
            "picture = '{$data['picture']}'," .
            "ip_address = '{$data['ip_address']}'," .
            "gender = '{$data['gender']}'," .
            "birth_date = '{$data['birth_date']}'," .
            "status = '{$data['status']}'" .
            "where username = '$username'",
            $db
        );

            Sql::close($db);
        }
    }

    /**
     * To retrieve a list of users from sqlite database
     * to generate users list page
     *
     * @param int $page the current page count of
     * users list the admin is viewing.
     * @param int $limit The amount of users per page to display.
     *
     * @return array Each username not longer than $limit
     */
    public static function getNavigationList(int $page = 0, int $limit = 30): array
    {
        $db = null;
        $page *= $limit;
        $users = [];

        if (Sql::dbExists("users")) {
            $db = Sql::open("users");

            Sql::turbo($db);

            $result = Sql::query(
            "select username from users order by " .
            "username asc limit $page, $limit",
            $db
        );
        } else {
            return $users;
        }

        $fields = [];

        if ($fields = Sql::fetchArray($result)) {
            $users[] = $fields["username"];

            while ($fields = Sql::fetchArray($result)) {
                $users[] = $fields["username"];
            }

            Sql::close($db);
            return $users;
        } else {
            Sql::close($db);
            return $users;
        }
    }

    /**
     * Removes a username from the users sqlite database.
     *
     * @param string $username The username to delete.
     */
    public static function deleteIndex(string $username): void
    {
        $username = strtolower($username);

        if (Sql::dbExists("users")) {
            $db = Sql::open("users");

            Sql::query(
            "delete from users where username = '$username'",
            $db
        );

            Sql::close($db);
        }
    }

    /**
     * Gets an array with the status messages and its id as
     * stored on users database. This static function is useful when
     * generating select elements on forms. A user status can be
     * Pending Approval, Active, Blocked.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        $status = [];

        $status[t("Active")] = "1";
        $status[t("Pending Approval")] = "0";
        $status[t("Blocked")] = "2";

        return $status;
    }

    /**
     * Resets the password of a user giving its username to search in.
     *
     * @param string $username The username of the user to resets its password.
     *
     * @return string "true" string on success or error message.
     */
    public static function resetPassword(string $username): string
    {
        $username = strtolower($username);
        $user_data = self::get($username);

        if (!$user_data) {
            return System::errorMessage("user_not_exist");
        }

        $user_data["token"] = self::generatePassword(128);
        $user_data["token_expire"] = time() + (60 * 60 * 24 * 1); // 1 day

        $message = self::edit($username, $user_data["group"], $user_data);

        if ($message == "true") {
            Mail::sendPasswordNotification($username, $user_data);
        }

        return $message;
    }

    /**
     * Resets the password of a user giving its email to search in.
     *
     * @param string $email The email of the user to resets its password.
     *
     * @return string "true" string on success or error message.
     */
    public static function resetPasswordByEmail(string $email): string
    {
        $email = str_replace("'", "''", $email);

        if (Sql::dbExists("users")) {
            $db = Sql::open("users");
            Sql::turbo($db);

            $result = Sql::query(
            "select username from users where email = '$email'",
            $db
        );

            $data = Sql::fetchArray($result);

            Sql::close($db);

            if (isset($data["username"]) && $data["username"] != "") {
                $username = $data["username"];
                $user_data = self::get($username);

                $user_data["token"] = self::generatePassword(128);
                $user_data["token_expire"] = time() + (60 * 60 * 24 * 1); // 1 day

                $message = self::edit($username, $user_data["group"], $user_data);

                if ($message == "true") {
                    Mail::sendPasswordNotification($username, $user_data);
                }

                return $message;
            } else {
                return System::errorMessage("user_not_exist");
            }
        } else {
            return System::errorMessage("user_not_exist");
        }
    }

    /**
     * Generates a unique username using the given e-mail.
     *
     * @param string $email A valid e-mail to generate the username.
     *
     * @return string Unique username or empty string if given e-mail is invalid.
     */
    public static function generateUsername(string $email): string
    {
        $username = "";

        if (Forms::validEmail($email, false)) {
            $username = preg_replace(
            "/[^\w]+/",
            "",
            explode("@", $email)[0]
        );

            if (strlen($username) < 3) {
                $username .= substr(self::generatePassword(), 1, 4);
            }

            $username_original = $username;

            $number = 1;

            while (!empty(self::get($username))) {
                $username = $username_original . $number;
                $number++;
            }
        }

        return $username;
    }

    /**
     * Generates a random password that can be used to reset originals user password.
     *
     * @param int $len The lenght of the password to generate.
     *
     * @return string A random password.
     */
    public static function generatePassword(int $len=10): string
    {
        $password = "";

        while (strlen($password) < $len) {
            $password .= str_replace(
            ["\$", ".", "/"],
            "",
            crypt(uniqid((string)rand($len, intval($len*rand())), true))
        );
        }

        if (strlen($password) > $len) {
            $password = substr($password, 0, $len);
        }

        return $password;
    }

    /**
     * Prints the content of admin/user and
     * calls a hook for modules to be able to modify user page content.
     */
    public static function printPage(): void
    {
        $base_url = Site::$base_url;

        $tabs = [];

        $tabs[t("Edit My Account")] = [
        "uri" => "admin/users/edit",
        "arguments" => ["username" => Authentication::currentUser()]
    ];

        $tabs[t("Devices")] = [
        "uri" => "admin/users/devices",
        "arguments" => ["username" => Authentication::currentUser()]
    ];

        if (Settings::get("user_profiles", "main")) {
            $tabs[t("View My Profile")] = ["uri" => "user/" . Authentication::currentUser()];
        }

        if (
        Authentication::groupHasPermission(
            "add_content",
            Authentication::currentUserGroup()
        )
    ) {
            $tabs[t("My Content")] = ["uri" => "admin/user/content"];
        }

        $content = "";

        $user_data = self::get(Authentication::currentUser());

        if (Authentication::isAdminLogged()) {
            $tabs[t("Control Center")] = ["uri" => "admin/start"];

            $content = t("Welcome Administrator!") . "<br /><br />" .
            t("Now that you are logged in you can start modifying the website as you need.")
        ;
        } else {
            $content = t("Welcome") . " " . $user_data["name"] . "!" . "<br /><br />" .
            t("Now that you are logged in you can enjoy the privileges of registered users on") .
            " " . str_replace("http://", "", $base_url) . "."
        ;
        }

        //Call print user page hooks so modules can modify user page content
        Modules::hook("hook_print_user_page", $content, $tabs, $user_data);

        foreach ($tabs as $title => $data) {
            if (!isset($data["arguments"])) {
                View::addTab(
                $title,
                $data["uri"],
                [],
                (isset($data["row"]) ? $data["row"] : 0)
            );
            } else {
                View::addTab(
                $title,
                $data["uri"],
                $data["arguments"],
                (isset($data["row"]) ? $data["row"] : 0)
            );
            }
        }

        View::addTab(t("Logout"), "admin/logout");

        print $content;
    }

    /**
     * Used on initialization of index when an uri scheme like
     * user/username was used to set needed arguments and target
     * page to display the user profile.
     *
     * @param string $page
     */
    public static function showProfile(string &$page): void
    {
        $sections = explode("/", $page);
        $username = $sections[1];

        $_REQUEST["username"] = $username;

        $page = "user";

        //Call show user profile hooks so modules can modify output profile page
        Modules::hook("hook_show_user_profile", $page, $username);
    }

    /**
     * Convertes any given string into a ready to use username.
     *
     * @param string $string The string to convert to valid username.
     *
     * @return string username ready to use
     */
    public static function formatUsername(string $string): string
    {
        $username = str_ireplace(
        [
            "á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "ñ",
            "Á", "É", "Í", "Ó", "Ú", "Ä", "Ë", "Ï", "Ö", "Ü", "Ñ"
        ],
        [
            "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n",
            "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n"
        ],
        $string
    );

        $username = trim($username);

        $username = strtolower($username);

        // only take alphanumerical characters, but keep the spaces and underscores
        $username = preg_replace('/[^a-zA-Z0-9 _]/', '', $username);

        $username = str_replace(' ', '_', $username);

        //Replace consecutive underscores by a single one
        $username = preg_replace('/([_]+)/', '_', $username);

        return $username;
    }

    /**
     * Generates the data path for a username.
     *
     * @param string $username The username to translate to a valid user data path.
     * @param string $group The group user belongs to.
     *
     * @return string Path to user data file.
     */
    public static function getPath(string $username, string $group): string
    {
        $username = strtolower($username);

        //We use the generate page path static function and substitue some values
        $user_data_path = Pages::getPath($username) . "/data.php";

        //substitute the data page path with the data users path
        $user_data_path = str_replace(
        Site::dataDir() . "pages/singles",
        Site::dataDir() . "users/$group",
        $user_data_path
    );

        return $user_data_path;
    }
}
