<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Script to get a json list of uri's that a match a given query. Used
 * for the auto complete functionality of uri's
 */

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//Include backward compatible functions if include dir exists
if(file_exists("include/forms.php"))
{
    require 'src/DeprecatedFunctions.php';
}

//Initialize settings.
Jaris\Site::init();

//Starts the main session for the user
session_start();

//Initialize error handler
Jaris\System::initiateErrorCatchSystem();

//Check if cms is run for the first time and run the installer
Jaris\System::checkIfNotInstalled();

//Check if site status is online to continue
Jaris\Site::checkIfOffline();

if(!isset($_REQUEST["type"]) || $_REQUEST["type"] == "uris")
{
    $query = Jaris\Uri::fromText($_REQUEST["query"], true);

    if(Jaris\Sql::dbExists("search_engine"))
    {
        $db = Jaris\Sql::open("search_engine");

        $select = "select uri, haspermission(groups, '" .
        Jaris\Authentication::currentUserGroup() . "') as has_permissions
        from uris where uri like '{$query}%' and has_permissions > 0 limit 0,20";

        $result = Jaris\Sql::query($select, $db);

        $list = array();

        while($data = Jaris\Sql::fetchArray($result))
        {
            $list[] = $data["uri"];
        }

        print json_encode(array("query" => $query, "suggestions" => $list));
    }
    else
    {
        print json_encode(array("query" => $query, "suggestions" => array()));
    }
}
elseif(
    $_REQUEST["type"] == "users" &&
    Jaris\Authentication::groupHasPermission(
        "autocomplete_users", Jaris\Authentication::currentUserGroup()
    )
)
{
    $query = Jaris\Users::formatUsername($_REQUEST["query"]);

    if(Jaris\Sql::dbExists("users"))
    {
        $db = Jaris\Sql::open("users");

        $select = "select username from users where username like '{$query}%' limit 0,20";

        $result = Jaris\Sql::query($select, $db);

        $list = array();

        while($data = Jaris\Sql::fetchArray($result))
        {
            $list[] = $data["username"];
        }

        print json_encode(array("query" => $query, "suggestions" => $list));
    }
    else
    {
        print json_encode(array("query" => $query, "suggestions" => array()));
    }
}
else
{
    print json_encode(array("query" => $_REQUEST["query"], "suggestions" => array()));
}