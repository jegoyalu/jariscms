<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for visits module.
 */

function comments_install()
{
    //Create comments data base
    if(!Jaris\Sql::dbExists("comments"))
    {
        $db = Jaris\Sql::open("comments");

        Jaris\Sql::query(
            "create table comments ("
            . "id integer, "
            . "created_timestamp text, "
            . "uri text, "
            . "type text, "
            . "notification integer, "
            . "flags integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index comments_index on comments ("
            . "created_timestamp desc, "
            . "uri desc, "
            . "type desc, "
            . "notification desc, "
            . "flags desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}

?>