<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the users re-index sqlite database page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Re-index Users Database") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_users"));

        Jaris\View::addTab(t("Navigation View"), "admin/users");
        Jaris\View::addTab(t("List View"), "admin/users/list");
        Jaris\View::addTab(t("Create User"), "admin/users/add");
        Jaris\View::addTab(t("Groups"), "admin/groups");
        Jaris\View::addTab(t("Export"), "admin/users/export");

        Jaris\View::addTab(
            t("Re-index Users List"),
            "admin/users/re-index", array(), 1
        );

        if(isset($_REQUEST["btnYes"]))
        {
            ini_set('max_execution_time', '0');

            if(users_reindex_sqlite())
            {
                Jaris\View::addMessage(
                    t("Indexation of users database completed.")
                );

                t("Re-indexed users database.");

                Jaris\Logger::info(
                    "Re-indexed users database."
                );
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/users/list");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/users/list");
        }

        function users_reindex_sqlite()
        {
            if(Jaris\Sql::dbExists("users"))
            {
                unlink(Jaris\Site::dataDir() . "sqlite/users");
            }

            //Recreate database and table
            $db = Jaris\Sql::open("users");

            if(!$db)
            {
                return false;
            }

            Jaris\Sql::query("PRAGMA journal_mode=WAL", $db);

            Jaris\Sql::query(
                "create table users "
                . "("
                . "username text, "
                . "email text, "
                . "register_date text, "
                . "user_group text, "
                . "picture text, "
                . "ip_address text, "
                . "gender text, "
                . "birth_date text, "
                . "status text"
                . ")",
                $db
            );

            Jaris\Sql::query(
                "create index users_index on users "
                . "("
                . "username desc, "
                . "email desc, "
                . "register_date desc, "
                . "user_group asc, "
                . "gender desc, "
                . "birth_date desc, "
                . "status desc"
                . ")",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\FileSystem::search(
                Jaris\Site::dataDir() .
                "users",
                "/.*data\.php/",
                "users_reindex_callback"
            );

            return true;
        }

        function users_reindex_callback($content_path)
        {
            $user_path = str_replace("/data.php", "", $content_path);
            $path_array = explode("/", $user_path);
            $username = $path_array[count($path_array) - 1];

            $user_data = Jaris\Users::get($username);

            //Marks users as active on older versions of jaris cms
            //that dont had the user status field
            $status = isset($user_data["status"]) ? $user_data["status"] : "1";

            $db = Jaris\Sql::open("users");
            Jaris\Sql::turbo($db);

            $data = $user_data;

            $data["username"] = $username;

            Jaris\Sql::escapeArray($data);

            Jaris\Sql::query(
                "insert into users (
                    username,
                    email,
                    register_date,
                    user_group,
                    picture,
                    ip_address,
                    gender,
                    birth_date,
                    status
                )
                values (
                '{$data['username']}',"
                . "'{$data['email']}',"
                . "'{$data['register_date']}',"
                . "'{$data['group']}',"
                . "'{$data['picture']}',"
                . "'{$data['ip_address']}',"
                . "'{$data['gender']}',"
                . "'{$data['birth_date']}',"
                . "'$status'"
                . ")",
                $db
            );

            Jaris\Sql::close($db);
        }
    ?>

    <form class="reindex-search-engine" method="post"
          action="<?php Jaris\Uri::url("admin/users/re-index") ?>"
    >
        <div>
            <?php print t("The process of recreating the users database list could take a long time. Are you sure you want to do this?") ?>
        </div>
        <input class="form-submit" type="submit"
               name="btnYes" value="<?php print t("Yes") ?>"
        />
        <input class="form-submit" type="submit"
               name="btnNo" value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
