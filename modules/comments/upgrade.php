<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module upgrade file
 *
 * Stores the upgrade script for comments module.
 */

function comments_upgrade()
{
    //Adds the notification field if not present on comments table
    if (Jaris\Sql::dbExists("comments")) {
        $db = Jaris\Sql::open("comments");
        Jaris\Sql::turbo($db);

        $result = Jaris\Sql::query("select * from comments", $db);

        $data = Jaris\Sql::fetchArray($result);

        if (!isset($data["notification"])) {
            $db_new = Jaris\Sql::open("comments_new");
            Jaris\Sql::turbo($db_new);

            Jaris\Sql::query(
                "create table comments ("
                . "id integer, "
                . "created_timestamp text, "
                . "uri text, "
                . "type text, "
                . "notification integer, "
                . "flags integer"
                . ")",
                $db_new
            );

            Jaris\Sql::query(
                "create index comments_index on comments ("
                . "created_timestamp desc, "
                . "uri desc, "
                . "type desc, "
                . "notification desc, "
                . "flags desc"
                . ")",
                $db_new
            );

            $data["notification"] = 1;
            Jaris\Sql::insertArrayToTable("comments", $data, $db_new);

            while ($data = Jaris\Sql::fetchArray($result)) {
                $data["notification"] = 1;
                Jaris\Sql::insertArrayToTable("comments", $data, $db_new);
            }

            Jaris\Sql::close($db);
            Jaris\Sql::close($db_new);

            unlink(Jaris\Site::dataDir() . "sqlite/comments");

            rename(
                Jaris\Site::dataDir() . "sqlite/comments_new",
                Jaris\Site::dataDir() . "sqlite/comments"
            );
        } else {
            unset($result);
            Jaris\Sql::close($db);
        }
    }
}
