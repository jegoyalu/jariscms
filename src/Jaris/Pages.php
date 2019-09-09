<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions related to the management of the file system based database.
 */
class Pages
{

/**
 * Receives parameters: $page, $is_system_page
 * @var string
 */
const SIGNAL_IS_SYSTEM_PAGE = "hook_is_system_page";

/**
 * Receives parameters: $uri, $data, $path
 * @var string
 */
const SIGNAL_CREATE_PAGE = "hook_create_page";

/**
 * Receives parameters: $page, $page_path
 * @var string
 */
const SIGNAL_DELETE_PAGE = "hook_delete_page";

/**
 * Receives parameters: $page, $new_data, $page_path
 * @var string
 */
const SIGNAL_EDIT_PAGE_DATA = "hook_edit_page_data";

/**
 * Receives parameters: $page, $data, $language_code
 * @var string
 */
const SIGNAL_GET_PAGE_DATA = "hook_get_page_data";

/**
 * Receives parameters: $actual_uri, $new_uri
 * @var string
 */
const SIGNAL_MOVE_PAGE = "hook_move_page";

/**
 * Creates a page data directory. if the given uri to create the page already
 * exist is automatically renamed, example: section/home (already exist)
 * renamed to section/home-0
 *
 * @param string $page The uri of the page to create, example: mysection/mypage
 * @param array $data An array of data to store to the page in the format:
 * data = array("title"=>"value", "content"=>value)
 * @param ?string $uri Reference to return the page uri in case
 * it was renamed because
 * it already exist.
 *
 * @return bool True on success or false on fail.
 */
static function add(string $page, array $data, ?string &$uri): bool
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }

    $path = self::getPath($page);
    $path = FileSystem::renameIfExist($path);

    //Returns the page uri to the argument reference.
    $uri = Uri::getFromPath(
        str_replace(Site::dataDir() . "pages/", "", $path)
    );

    FileSystem::makeDir($path, 0755, true);
    FileSystem::makeDir($path . "/files", 0755, true);
    FileSystem::makeDir($path . "/images", 0755, true);
    FileSystem::makeDir($path . "/blocks", 0755, true);

    //Call create_page hook before creating the page
    Modules::hook("hook_create_page", $uri, $data, $path);

    $categories = $data["categories"];

    $data["users"] = serialize($data["users"]);
    $data["groups"] = serialize($data["groups"]);
    $data["categories"] = serialize($data["categories"]);

    if(!isset($data["approved"]) || empty($data["approved"]))
    {
        if(
            isset($data["type"])
            &&
            Types::groupRequiresApproval(
                $data["type"],
                current_user_group()
            )
        )
        {
            $data["approved"] = "p";
        }
        else
        {
            $data["approved"] = "a";
        }
    }

    if(Data::add($data, $path . "/data.php"))
    {
        //In case a module is installing a system page skip it of the database
        if(!$data["is_system"])
        {
            self::addIndex($uri, $data);

            //Update cache_events folder
            if(!file_exists(Site::dataDir() . "cache_events"))
            {
                FileSystem::makeDir(Site::dataDir() . "cache_events");
            }
            file_put_contents(Site::dataDir() . "cache_events/new_page", "");
        }

        Categories::incrementContent($categories ?? array());

        return true;
    }

    return false;
}

/**
 * Deletes a page data directory.
 *
 * @param string $page The uri of the page to delete, example: mysection/mypage
 * @param bool $disable_hook Do not call any of the registered hooks.
 *
 * @return bool True on success or false on fail.
 */
static function delete(string $page, bool $disable_hook=false): bool
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }

    $page_path = self::getPath($page);

    //Call delete_page hook before deleting the page
    if(!$disable_hook)
        Modules::hook("hook_delete_page", $page, $page_path);

    // We retrieve the page data to get the categories
    $page_data = self::get($page);

    //Clears the page directory to be able to delete it
    if(!FileSystem::recursiveRemoveDir($page_path, true))
    {
        return false;
    }

    Categories::decrementContent($page_data["categories"] ?? array());

    self::removeEmptyDirectories($page_path);

    self::deleteIndex($page);

    if(Settings::get("enable_cache", "main"))
    {
        System::removeCachedPage($page);
    }

    return true;
}

/**
 * Modifies the data of a page.
 *
 * @param string $page The uri of the page to modify.
 * @param array $new_data Array of new data in the format:
 * $data = array("title"=>value, "content"=>value)
 *
 * @return bool True on success or false on fail.
 */
static function edit(string $page, array $new_data): bool
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }

    $page_path = Pages::getPath($page);

    if(!isset($new_data["approved"]) || empty($new_data["approved"]))
    {
        $new_data["approved"] = "a";
    }

    //Call edit_page_data hook before editing the page
    Modules::hook("hook_edit_page_data", $page, $new_data, $page_path);

    $new_data["users"] = serialize($new_data["users"]);
    $new_data["groups"] = serialize($new_data["groups"]);
    $new_data["categories"] = serialize($new_data["categories"]);

    if(Data::edit(0, $new_data, $page_path . "/data.php"))
    {
        self::editIndex($page, $new_data);

        if(Settings::get("enable_cache", "main"))
        {
            System::removeCachedPage($page);
        }

        return true;
    }

    return false;
}

/**
 * Approve a page.
 *
 * @param string $page The uri of the page to approve,
 * example: mysection/mypage
 *
 * @return bool True on success or false on fail.
 */
static function approve(string $page): bool
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }

    $page_data = self::get($page);

    if(!$page_data)
    {
        return false;
    }

    $page_data["approved"] = "a";

    return self::edit($page, $page_data);
}

/**
 * Count a view to a page.
 *
 * @param string $page The uri of the page to count a view.
 *
 * @return int The total count of page views.
 */
static function countView(string $page): int
{
    if(!self::isSystem($page))
    {
        $file_path = self::getPath($page);
        if(file_exists($file_path . "/data.php"))
        {
            $file_path .= "/views.php";

            Data::edit(
                0,
                array(),
                $file_path,
                function(&$actual_data, &$new_data){
                    if(isset($actual_data[0]["views"]))
                    {
                        $new_data["views"] = $actual_data[0]["views"]+1;
                    }
                    else
                    {
                        $new_data["views"] = 1;
                    }
                }
            );

            $views_data = Data::get(0, $file_path);

            $search_database_views = null;
            if(Sql::dbExists("search_engine"))
            {
                $db = Sql::open("search_engine", "", 60000);

                if(Settings::get("classic_views_count", "main"))
                {
                    Sql::turbo($db, "OFF");
                }
                else
                {
                    Sql::turbo($db, "NORMAL");
                }

                $select = "select * from uris where "
                    . "uri='" . str_replace("'", "''", $page) . "' "
                    . "limit 1"
                ;

                $result = Sql::query($select, $db);

                if($data = Sql::fetchArray($result))
                {
                    $search_database_views = $data["views"] + 1;

                    $current_day = date("j", time());
                    $current_week = date("W", time());
                    $current_month = date("n", time());

                    if($data["views_day"] != $current_day)
                    {
                        $day_set = "views_day=$current_day, views_day_count=1";
                    }
                    else
                    {
                        $day_set = "views_day_count=views_day_count+1";
                    }

                    if($data["views_week"] != $current_week)
                    {
                        $week_set = "views_week=$current_week, views_week_count=1";
                    }
                    else
                    {
                        $week_set = "views_week_count=views_week_count+1";
                    }

                    if($data["views_month"] != $current_month)
                    {
                        $month_set = "views_month=$current_month, views_month_count=1";
                    }
                    else
                    {
                        $month_set = "views_month_count=views_month_count+1";
                    }

                    unset($result);

                    $update = "update uris set views=views+1, " .
                        "$day_set, $week_set, $month_set where uri='" .
                        str_replace("'", "''", $page) . "'"
                    ;

                    Sql::query($update, $db);
                }

                Sql::close($db);
            }

            //In case search_engine database views values is most up
            //to date than views.php file
            if(
                $search_database_views &&
                $search_database_views >= $views_data["views"]
            )
            {
                $views_data["views"] = $search_database_views;
            }

            return $views_data["views"];
        }
    }

    return 0;
}

/**
 * Get the amount of views for a page.
 *
 * @param string $page The uri of the page.
 *
 * @return int The total count of page views.
 */
static function getViews(string $page): int
{
    $file_path = self::getPath($page) . "/views.php";

    if(file_exists($file_path))
    {
        $views_data = Data::get(0, $file_path);

        if(isset($views_data["views"]))
        {
            return $views_data["views"];
        }
    }

    return 0;
}

/**
 * Gets all the data of a page.
 *
 * @param string $page The uri of the page to retrive data.
 * @param string $language_code Optional parameter to get the page data of a
 * translation if available.
 *
 * @return array All the data fields of the page in the format
 * $data["field_name"] = "value" or empty array if page not found.
 */
static function get(string $page, string $language_code = ""): array
{
    $development_mode = Site::$development_mode;

    $page = trim($page);

    if($page == "")
    {
        return array();
    }

    $system_page_path = self::getSystemPath($page);

    $module_page_path = "";

    if($development_mode)
    {
        $module_page_path .= Modules::getPagePath($page);
    }

    if($system_page_path != "")
    {
        //get system page data
        $data = Data::get(0, $system_page_path);
    }
    elseif($module_page_path != "")
    {
        //get module page data
        $data = Data::get(0, $module_page_path);
    }
    else
    {
        $page_path = "";

        if(!$language_code)
        {
            $page_path = self::getPath($page);
        }
        else
        {
            $page_path = Language::dataTranslate(
                self::getPath($page),
                $language_code
            );
        }

        if(!file_exists($page_path . "/data.php"))
        {
            return array();
        }

        //get page data
        $data = Data::get(0, $page_path . "/data.php");

        //get views count data
        $views_data = Data::get(0, $page_path . "/views.php");

        //append views count to page data
        if(isset($views_data["views"]) && $views_data["views"])
        {
            $data["views"] = $views_data["views"];
        }
        else
        {
            $data["views"] = 0;
        }

        $data["users"] = unserialize($data["users"]);
        $data["groups"] = unserialize($data["groups"]);
        $data["categories"] = unserialize($data["categories"]);

        if(!isset($data["approved"]) || empty($data["approved"]))
        {
            $data["approved"] = "a";
        }
    }

    //Call get_page_data hook before returning the data
    Modules::hook("hook_get_page_data", $page, $data, $language_code);

    return $data;
}

/**
 * Check the current type of a page.
 *
 * @param string $page The page to check its type.
 *
 * @return string The type of the page.
 */
static function getType(string $page): string
{
    $page = trim($page);
    if($page == "")
    {
        return "";
    }

    $data = self::get($page);

    return $data["type"] ?? "";
}

/**
 * Check if the current user is owner of the page.
 *
 * @param string $page The page to check its ownership.
 * @param ?array &$page_data If set checks the given page data for
 * ownership details instead of loading from file.
 *
 * @return bool True if current user is the owner or is the
 * admin logged, otherwise false.
 */
static function userIsOwner(string $page, ?array &$page_data=[]): bool
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }

    if(Authentication::isAdminLogged())
    {
        return true;
    }

    if(!$page_data)
    {
        $page_data = self::get($page);
    }

    if(
        Authentication::groupHasPermission(
            "edit_all_user_content",
            Authentication::currentUserGroup()
        ) &&
        Authentication::hasTypeAccess(
            $page_data['type'],
            Authentication::currentUserGroup()
        )
    )
    {
        return true;
    }

    if($page_data["author"] == Authentication::currentUser())
    {
        return true;
    }

    return false;
}

/**
 * Moves a page data path to another one.
 *
 * @param string $actual_uri The actual page to move.
 * @param ?string $new_uri The path to new page data. Returns the
 * new uri of the page useful since it renames the uri in case it exist.
 *
 * @return bool True on success or false if fail.
 */
static function move(string $actual_uri, ?string &$new_uri): bool
{
    $new_uri = strval($new_uri);

    $actual_uri = trim($actual_uri);
    $new_uri = trim($new_uri);
    if($actual_uri == "" || $new_uri == "")
    {
        return false;
    }

    $actual_path = self::getPath($actual_uri);
    $new_path = FileSystem::renameIfExist(self::getPath($new_uri));

    $new_uri = Uri::getFromPath(
        str_replace(Site::dataDir() . "pages/", "", $new_path)
    );

    //Call move_page hook before moving the page
    Modules::hook("hook_move_page", $actual_uri, $new_uri);

    if(FileSystem::makeDir($new_path, 0755, true))
    {
        FileSystem::recursiveMoveDir($actual_path, $new_path);

        //Clears the page directory to be able to delete it
        FileSystem::recursiveRemoveDir($actual_path, true);

        self::removeEmptyDirectories($actual_path);

        self::editIndexUri($actual_uri, $new_uri);

        return true;
    }

    return false;
}

/**
 * Checks if the current page is a core system one.
 *
 * @param string $uri Optional parameter to indicate a specific page to check.
 * @param ?array $page_data If set checks the given page data instead of loading
 * it from a file.
 *
 * @return bool True if system page false if not.
 */
static function isSystem(string $uri = "", ?array &$page_data=[]): bool
{
    $page = Uri::get();

    if($uri)
    {
        $page = $uri;
    }

    if(!$page_data)
    {
        if(self::getSystemPath($page))
        {
            $page_data = array(
                "is_system"=>true
            );
        }
        else
        {
            $page_data = self::get($page);
        }
    }

    $is_system_page = false;

    if(isset($page_data["is_system"]))
    {
        $is_system_page = (bool) trim($page_data["is_system"]);
    }

    //Call is_system_page hook before returning data
    Modules::hook("hook_is_system_page", $page, $is_system_page);

    return $is_system_page;
}

/**
 * Checks if the current user group has access to the page
 *
 * @param array $page_data Data array of the content to check.
 *
 * @return bool True if has access or false if not
 */
static function userHasAccess(array $page_data): bool
{
    if(Authentication::isAdminLogged())
        return true;

    if(is_array($page_data["users"]))
    {
        if(count($page_data["users"]) == 1)
        {
            if($page_data["users"][0] == "")
                $page_data["users"] = false;
        }
    }

    //If administrator not selected any user or group return true.
    if(!$page_data["users"] && !$page_data["groups"])
        return true;

    if(is_array($page_data["users"]))
    {
        $current_user = Authentication::currentUser();

        foreach($page_data["users"] as $username)
        {
            if($username == $current_user)
            {
                return true;
            }
        }
    }

    if(is_array($page_data["groups"]))
    {
        $current_group = Authentication::currentUserGroup();

        foreach($page_data["groups"] as $machine_name)
        {
            if($machine_name == $current_group)
            {
                return true;
            }
        }
    }

    return false;
}

/**
 * Starts deleting empty directories from the deepest one to its root
 *
 * @param string $path The path wich the empty directories are
 * going to be deleted.
 *
 * @return bool True if path removed or false if the path doesn't exists.
 */
static function removeEmptyDirectories(string $path): bool
{
    $path = trim($path);

    if($path == "")
    {
        return false;
    }

    //This is the directory that is not going to be deleted
    $main_dir = Site::dataDir() . "pages/singles/";

    //Checks if the path belongs to the sections path
    $path = str_replace(
        Site::dataDir() . "pages/sections/", "", $path, $count
    );

    if($count > 0)
    {
        $main_dir = Site::dataDir() . "pages/sections/";
    }
    else
    {
        $path = str_replace(
            Site::dataDir() . "pages/singles/", "", $path, $count
        );
    }

    $directories = explode("/", $path);
    $directory_count = count($directories);

    for($i = 0; $i < $directory_count; $i++)
    {

        $sub_directory = "";
        for($c = 0; $c < $directory_count - $i; $c++)
        {
            $sub_directory .= $directories[$c] . "/";
        }

        @rmdir($main_dir . $sub_directory);
    }

    return true;
}

/**
 * Add a created page uri to the search_engine sqlite database
 * for faster searching.
 *
 * @param string $uri The uri to add.
 * @param array $data Page data to store.
 */
static function addIndex(string $uri, array $data): void
{
    static $exists = false;

    if(!$exists && !Sql::dbExists("search_engine"))
    {
        $exists = true;

        $db = Sql::open("search_engine");

        Sql::query("PRAGMA journal_mode=WAL", $db);

        Sql::query(
            "create table uris ("
            . "title text,"
            . "content text,"
            . "description text,"
            . "keywords text,"
            . "users text,"
            . "groups text,"
            . "categories text,"
            . "input_format text,"
            . "created_date text,"
            . "last_edit_date text,"
            . "last_edit_by text,"
            . "author text,"
            . "type text,"
            . "approved text,"
            . "views integer,"
            . "views_day integer,"
            . "views_day_count integer,"
            . "views_week integer,"
            . "views_week_count integer,"
            . "views_month integer,"
            . "views_month_count integer,"
            . "uri text,"
            . "data text"
            . ")",
            $db
        );

        Sql::query(
            "create index uris_index on uris ("
            . "title desc,"
            . "content desc,"
            . "description desc,"
            . "keywords desc,"
            . "categories desc,"
            . "created_date desc,"
            . "last_edit_date desc,"
            . "author desc,"
            . "type desc,"
            . "approved desc,"
            . "views desc,"
            . "views_day_count desc,"
            . "views_week_count desc,"
            . "views_month_count desc,"
            . "uri desc)",
            $db
        );

        Sql::close($db);
    }

    $all_data = $data;
    $all_data["users"] = unserialize($all_data["users"]);
    $all_data["groups"] = unserialize($all_data["groups"]);
    $all_data["categories"] = unserialize($all_data["categories"]);
    $all_data = str_replace("'", "''", serialize($all_data));

    // Prepare title for search
    $data["title"] = Util::stripHTMLTags($data["title"]);

    //Substitute some characters with spaces to improve search quality
    $data["title"] = str_replace(
        array(".", ",", "'", "\"", "(", ")"),
        " ",
        $data["title"]
    );

    //Remove repeated whitespaces
    $data["title"] = preg_replace('/\s\s+/', ' ', $data["title"]);
    $data["title"] = strtolower($data["title"]);

    // Prepare content
    $data["content"] = Util::stripHTMLTags($data["content"]);

    //Substitute some characters with spaces to improve search quality
    $data["content"] = str_replace(
        array(".", ",", "'", "\"", "(", ")"),
        " ",
        $data["content"]
    );

    //Remove repeated whitespaces
    $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
    $data["content"] = strtolower($data["content"]);

    if(!$data["views"])
    {
        $data["views"] = "0";
    }

    Sql::escapeArray($data);

    $data = array_map('trim', $data);

    $uri = str_replace("'", "''", $uri);

    $db = Sql::open("search_engine");
    Sql::query("insert into uris
    (title, content, description, keywords, users, groups, categories, input_format,
     created_date, last_edit_date, last_edit_by, author, type, approved, views, uri, data)

    values ('{$data['title']}', '{$data['content']}', '{$data['description']}', '{$data['keywords']}',
    '{$data['users']}', '{$data['groups']}', '{$data['categories']}','{$data['input_format']}','{$data['created_date']}',
    '{$data['last_edit_date']}', '{$data['last_edit_by']}', '{$data['author']}', '{$data['type']}',
    '{$data['approved']}', {$data['views']}, '$uri', '$all_data')", $db);

    Sql::close($db);
}

/**
 * Edit an existing uri on the sqlite search_engine database,
 * used when moving a page from location.
 *
 * @param string $old_uri The old uri to renew.
 * @param string $new_uri The new uri that is going to replace the old one.
 */
static function editIndexUri(string $old_uri, string $new_uri): void
{
    static $exists = false;

    if($exists || Sql::dbExists("search_engine"))
    {
        $exists = true;

        $db = Sql::open("search_engine");

        $old_uri = str_replace("'", "''", $old_uri);
        $new_uri = str_replace("'", "''", $new_uri);

        Sql::query(
            "update uris set uri = '$new_uri' where uri = '$old_uri'",
            $db
        );

        Sql::close($db);
    }
}

/**
 * Edit data of existing uri.
 *
 * @param string $uri The uri to edit its data.
 * @param array $data The new data to store.
 */
static function editIndex(string $uri, array $data): void
{
    static $exists = false;

    if($exists || Sql::dbExists("search_engine"))
    {
        $exists = true;

        $all_data = $data;
        $all_data["users"] = unserialize($all_data["users"]);
        $all_data["groups"] = unserialize($all_data["groups"]);
        $all_data["categories"] = unserialize($all_data["categories"]);
        $all_data = str_replace("'", "''", serialize($all_data));

        // Prepare title
        $data["title"] = Util::stripHTMLTags($data["title"]);

        //Substitute some characters with spaces to improve search quality
        $data["title"] = str_replace(
            array(".", ",", "'", "\"", "(", ")"),
            " ",
            $data["title"]
        );

        //Remove repeated whitespaces
        $data["title"] = preg_replace('/\s\s+/', ' ', $data["title"]);
        $data["title"] = strtolower($data["title"]);

        // Prepare content
        $data["content"] = Util::stripHTMLTags($data["content"]);

        //Substitute some characters with spaces to improve search quality
        $data["content"] = str_replace(
            array(".", ",", "'", "\"", "(", ")"),
            " ",
            $data["content"]
        );

        //Remove repeated whitespaces
        $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
        $data["content"] = strtolower($data["content"]);

        Sql::escapeArray($data);

        $data = array_map('trim', $data);

        $uri = str_replace("'", "''", $uri);

        $db = Sql::open("search_engine");

        //No need to save views since views are managed by separate

        Sql::query("update uris set
        title = '{$data['title']}',
        content = '{$data['content']}',
        description = '{$data['description']}',
        keywords = '{$data['keywords']}',
        users = '{$data['users']}',
        groups = '{$data['groups']}',
        categories = '{$data['categories']}',
        input_format = '{$data['input_format']}',
        created_date = '{$data['created_date']}',
        last_edit_date = '{$data['last_edit_date']}',
        last_edit_by = '{$data['last_edit_by']}',
        author = '{$data['author']}',
        type = '{$data['type']}',
        approved = '{$data['approved']}',
        data = '$all_data'

        where uri = '$uri'", $db);

        Sql::close($db);
    }
}

/**
 * Removes an uri from the search_engine sqlite database,
 * used when deleting a page that is not going to be anymore
 * available for searching.
 *
 * @param string $uri The uri to delete.
 */
static function deleteIndex(string $uri)
{
    static $exists = false;

    if($exists || Sql::dbExists("search_engine"))
    {
        $exists = true;

        $uri = str_replace("'", "''", $uri);

        $db = Sql::open("search_engine");

        Sql::query("delete from uris where uri = '$uri'", $db);

        Sql::close($db);
    }
}

/**
 * To retrieve a list of pages from sqlite database to generate pages list page.
 *
 * @param int $page The current page count of pages list the admin is viewing.
 * @param int $limit The amount of pages per page to display.
 *
 * @return array Each page uri not longer than $limit
 */
static function getNavigationList(int $page = 0, int $limit = 30): array
{
    $db = null;
    $page *= $limit;
    $pages = array();

    if(Sql::dbExists("search_engine"))
    {
        $db = Sql::open("search_engine");

        Sql::turbo($db);

        $result = Sql::query(
            "select uri from uris order by " .
            "created_date desc, last_edit_date desc limit $page, $limit",
            $db
        );
    }
    else
    {
        return $pages;
    }

    $fields = array();

    if($fields = Sql::fetchArray($result))
    {
        $pages[] = $fields["uri"];

        while($fields = Sql::fetchArray($result))
        {
            $pages[] = $fields["uri"];
        }

        Sql::close($db);
        return $pages;
    }
    else
    {
        Sql::close($db);
        return $pages;
    }
}

/**
 * Generates the system path to the page data directory.
 *
 * @param string $page The uri to generate the path
 * from, example mysection/pagename
 *
 * @return string Path to page data directory.
 */
static function getPath(string $page): string
{
    $path = Site::dataDir() . "pages/";

    $sections = explode("/", $page);

    //Last element of the array is the name of the page, so we substract it.
    $sections_available = count($sections) - 1;

    if($sections_available != 0)
    {
        //Here we replace the full $page value with sections stripped out.
        $page = $sections[count($sections) - 1];
        $path .= "sections/";

        for($i = 0; $i < $sections_available; ++$i)
        {
            $path .= $sections[$i] . "/";
        }

        $path .= substr($page, 0, 1) . "/" . substr($page, 0, 2) . "/" . $page;
    }
    else
    {
        $path .= "singles/" .
            substr($page, 0, 1) . "/" . substr($page, 0, 2) . "/" . $page
        ;
    }

    return $path;
}

/**
 * Generates the path to a system page.
 *
 * @param string $page The uri to generate the path
 * from, example admin/settings
 *
 * @return string Path to the system page of empty string if not exists.
 */
static function getSystemPath(string $page): string
{
    $path = "system/pages/" . $page . ".php";

    if(file_exists($path))
        return $path;

    return "";
}

}
