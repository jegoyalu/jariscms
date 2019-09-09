<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Pages Api
    field;

    field: content
    <?php
        // We execute this without api functions processing for better
        // performance without the impact of api validation.
        if(
            !empty($_REQUEST["action"])
            &&
            $_REQUEST["action"] == "count_view"
        )
        {
            if(!empty($_REQUEST["uri"]))
            {
                Jaris\Pages::countView($_REQUEST["uri"]);
            }

            exit;
        }

        Jaris\Api::init(
            array(
                "add" => array(
                    "description" => "Adds a new page.",
                    "parameters" => array(
                        "data" => array(
                            "description" => "The page data array.",
                            "elements" => array(
                                "uri" => "Uri or path of the page.",
                                "title" => "The title of the page.",
                                "content" => "HTML or PHP content of the page.",
                                "meta_title" => "The meta title of the page.",
                                "description" => "Short description of the page for the meta description.",
                                "keywords" => "Comma separated keywords for the meta keywords of the page.",
                                "groups" => "Array of groups that can access the page, empty array for all groups.",
                                "users" => "Array of users that can access the page, empty array for all users.",
                                "categories" => "Array of categories eg: array('cat_machine_name' => array(subcat_id1, subcat_id2, ...)).",
                                "input_format" => "Machine name of the page input format.",
                                "created_date" => "A valid timestamp.",
                                "author" => "Username of author.",
                                "type" => "Machine name of the page type."
                            ),
                            "elements_required" => array("title")
                        )
                    ),
                    "parameters_required" => array(
                        "data"
                    ),
                    "response" => array(
                        "uri" => "Uri of the created page."
                    ),
                    "errors" => array(
                        "1010" => "Failed to create the page."
                    ),
                    "permissions" => "add_page_core"
                ),
                "add_bulk" => array(
                    "description" => "Adds multiple pages from a sqlite database.",
                    "parameters" => array(
                        "db" => array(
                            "description" => "Gzipped base64 encoded sqlite database with a table named 'pages' and a json encoded 'data' column containing the following fields:",
                            "elements" => array(
                                "uri" => "Uri or path of the page.",
                                "title" => "The title of the page (required).",
                                "content" => "HTML or PHP content of the page.",
                                "meta_title" => "The meta title of the page.",
                                "description" => "Short description of the page for the meta description.",
                                "keywords" => "Comma separated keywords for the meta keywords of the page.",
                                "groups" => "Array of groups that can access the page, empty array for all groups.",
                                "users" => "Array of users that can access the page, empty array for all users.",
                                "categories" => "Array of categories eg: array('cat_machine_name' => array(subcat_id1, subcat_id2, ...)).",
                                "input_format" => "Machine name of the page input format.",
                                "created_date" => "A valid timestamp.",
                                "author" => "Username of author.",
                                "type" => "Machine name of the page type."
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "db"
                    ),
                    "response" => array(
                        "uris" => "Array of the created page URI's."
                    ),
                    "errors" => array(
                        "1060" => "Failed to uncompress database.",
                        "1070" => "Invalid database format.",
                        "1080" => "Failed to create all the pages."
                    ),
                    "permissions" => "add_page_core"
                ),
                "edit" => array(
                    "description" => "Edit an existing page.",
                    "parameters" => array(
                        "uri" => "Uri or path of page.",
                        "data" => array(
                            "description" => "The page data array.",
                            "elements" => array(
                                "title" => "The title of the page.",
                                "content" => "HTML or PHP content of the page.",
                                "meta_title" => "The meta title of the page.",
                                "description" => "Short description of the page for the meta description.",
                                "keywords" => "Comma separated keywords for the meta keywords of the page.",
                                "groups" => "Array of groups that can access the page, empty array for all groups.",
                                "users" => "Array of users that can access the page, empty array for all users.",
                                "categories" => "Array of categories eg: array('cat_machine_name' => array(subcat_id1, subcat_id2, ...)).",
                                "input_format" => "Machine name of the page input format.",
                                "created_date" => "A valid timestamp.",
                                "last_edit_date" => "A valid timestamp",
                                "author" => "Username of author.",
                                "last_edit_by" => "Username of latest changes author.",
                                "type" => "Machine name of the page type."
                            ),
                            "elements_required" => array("title")
                        )
                    ),
                    "parameters_required" => array(
                        "uri",
                        "data"
                    ),
                    "errors" => array(
                        "1020" => "The page does not exists.",
                        "1030" => "Failed to edit the page."
                    ),
                    "permissions" => "edit_page_core"
                ),
                "edit_bulk" => array(
                    "description" => "Edits multiple pages from a sqlite database.",
                    "parameters" => array(
                        "db" => array(
                            "description" => "Gzipped base64 encoded sqlite database with a table named 'pages' and a json encoded 'data' column containing the following fields:",
                            "elements" => array(
                                "uri" => "Uri or path of the page (required).",
                                "title" => "The title of the page.",
                                "content" => "HTML or PHP content of the page.",
                                "meta_title" => "The meta title of the page.",
                                "description" => "Short description of the page for the meta description.",
                                "keywords" => "Comma separated keywords for the meta keywords of the page.",
                                "groups" => "Array of groups that can access the page, empty array for all groups.",
                                "users" => "Array of users that can access the page, empty array for all users.",
                                "categories" => "Array of categories eg: array('cat_machine_name' => array(subcat_id1, subcat_id2, ...)).",
                                "input_format" => "Machine name of the page input format.",
                                "created_date" => "A valid timestamp.",
                                "author" => "Username of author.",
                                "type" => "Machine name of the page type."
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "db"
                    ),
                    "response" => array(
                        "uris" => "Array of the edited page URI's."
                    ),
                    "errors" => array(
                        "1060" => "Failed to uncompress database.",
                        "1070" => "Invalid database format.",
                        "1090" => "Provided an item without URI.",
                        "1100" => "Failed to edit all the pages."
                    ),
                    "permissions" => "edit_page_core"
                ),
                "delete" => array(
                    "description" => "Delete an existing page.",
                    "parameters" => array(
                        "uri" => "Uri or path of page."
                    ),
                    "parameters_required" => array(
                        "uri"
                    ),
                    "errors" => array(
                        "1020" => "The page does not exists.",
                        "1040" => "Failed to delete the page."
                    ),
                    "permissions" => "delete_page_core"
                ),
                "delete_bulk" => array(
                    "description" => "Deletes a list of existing pages.",
                    "parameters" => array(
                        "uris" => "Array of page URI's."
                    ),
                    "parameters_required" => array(
                        "uris"
                    ),
                    "response" => array(
                        "uris" => "Array of the deleted page URI's."
                    ),
                    "errors" => array(
                        "1110" => "Failed to delete all pages."
                    ),
                    "permissions" => "delete_page_core"
                ),
                "get" => array(
                    "description" => "Get an existing page.",
                    "parameters" => array(
                        "uri" => "Uri or path of page."
                    ),
                    "parameters_required" => array(
                        "uri"
                    ),
                    "response" => array(
                        "data" => array(
                            "description" => "The page data.",
                            "elements" => array(
                                "title" => "The title of the page.",
                                "content" => "HTML or PHP content of the page.",
                                "meta_title" => "The meta title of the page.",
                                "description" => "Short description of the page for the meta description.",
                                "keywords" => "Comma separated keywords for the meta keywords of the page.",
                                "groups" => "Array of groups that can access the page, empty array for all groups.",
                                "users" => "Array of users that can access the page, empty array for all users.",
                                "categories" => "Array of categories eg: array('cat_machine_name' => array(subcat_id1, subcat_id2, ...)).",
                                "input_format" => "Machine name of the page input format.",
                                "created_date" => "A valid timestamp.",
                                "last_edit_date" => "A valid timestamp",
                                "author" => "Username of author.",
                                "last_edit_by" => "Username of latest changes author.",
                                "type" => "Machine name of the page type."
                            )
                        )
                    ),
                    "errors" => array(
                        "1020" => "Page does not exists."
                    ),
                    "permissions" => "get_page_core"
                ),
                "get_db" => array(
                    "description" => "Get the pages database.",
                    "response" => array(
                        "db" => "Gzipped base64 encoded sqlite database with all pages."
                    ),
                    "errors" => array(
                        "1050" => "Pages database does not exists."
                    ),
                    "permissions" => "get_page_core"
                ),
                "count_view" => array(
                    "description" => "Increment the page views.",
                    "parameters" => array(
                        "uri" => "Uri or path of page."
                    ),
                    "parameters_required" => array(
                        "uri"
                    )
                )
            )
        );

        $action = Jaris\Api::getAction();

        if($action == "add")
        {
            $data = Jaris\Api::decodeParam("data");

            if(
                empty($data["uri"])
            )
            {
                $data["uri"] = Jaris\Types::generateURI(
                    $data["type"],
                    $data["title"],
                    !empty($data["author"]) ? $data["author"] : "system"
                );
            }

            if(empty($data["categories"]))
            {
                $data["categories"] = array();
            }

            if(empty($data["groups"]))
            {
                $data["groups"] = array();
            }

            if(empty($data["users"]))
            {
                $data["users"] = array();
            }

            if(empty($data["created_date"]))
            {
                $data["created_date"] = time();
            }

            if(empty($data["input_format"]))
            {
                $data["input_format"] = "full_html";
            }

            if(empty($data["type"]))
            {
                $data["type"] = "page";
            }

            // Remove uri field
            $page_uri = $data["uri"];
            unset($data["uri"]);

            $uri = "";

            if(Jaris\Pages::add($page_uri, $data, $uri))
            {
                Jaris\Api::addResponse("uri", $uri);
            }
            else
            {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Failed to create the page."
                );
            }
        }
        elseif($action == "add_bulk")
        {
            $db_name = Jaris\Users::generatePassword();
            $db_file = Jaris\Site::dataDir()
                . "sqlite/"
                . $db_name
            ;

            if(
                !file_put_contents(
                    $db_file,
                    Jaris\Api::uncompressData($_REQUEST["db"])
                )
            )
            {
                Jaris\Api::sendErrorResponse(
                    1060,
                    "Failed to uncompress database."
                );
            }

            $db = Jaris\Sql::open($db_name);

            $result = Jaris\Sql::query("select data from pages", $db);

            if(!$result)
            {
                Jaris\Sql::close($db);
                unlink($db_file);

                Jaris\Api::sendErrorResponse(
                    1070,
                    "Invalid database format."
                );
            }

            $uris = array();

            while($data = Jaris\Sql::fetchArray($result))
            {
                $data = Jaris\Api::decodeData($data["data"]);

                if(empty($data["uri"]))
                {
                    $data["uri"] = Jaris\Types::generateURI(
                        $data["type"] ?? "product",
                        $data["title"],
                        !empty($data["author"]) ? $data["author"] : "system"
                    );
                }

                if(empty($data["categories"]))
                {
                    $data["categories"] = array();
                }

                if(empty($data["groups"]))
                {
                    $data["groups"] = array();
                }

                if(empty($data["users"]))
                {
                    $data["users"] = array();
                }

                if(empty($data["created_date"]))
                {
                    $data["created_date"] = time();
                }

                if(empty($data["input_format"]))
                {
                    $data["input_format"] = "full_html";
                }

                if(empty($data["type"]))
                {
                    $data["type"] = "page";
                }

                // Remove uri field
                $page_uri = $data["uri"];
                unset($data["uri"]);

                $uri = "";

                if(Jaris\Pages::add($page_uri, $data, $uri))
                {
                    $uris[] = $uri;
                }
                else
                {
                    Jaris\Sql::close($db);
                    unlink($db_file);

                    // Add the pages that where added.
                    Jaris\Api::addResponse("uris", $uris);

                    Jaris\Api::sendErrorResponse(
                        1080,
                        "Failed to create all the pages."
                    );
                }
            }

            Jaris\Sql::close($db);
            unlink($db_file);

            Jaris\Api::addResponse("uris", $uris);
        }
        elseif($action == "edit")
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if(!$page_data)
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            $data = Jaris\Api::decodeParam("data");

            foreach($data as $index => $value)
            {
                if($index == "uri")
                    continue;

                $page_data[$index] = $value;
            }

            if(!Jaris\Pages::edit($data["uri"], $page_data))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Failed to edit the page."
                );
            }
        }
        elseif($action == "edit_bulk")
        {
            $db_name = Jaris\Users::generatePassword();
            $db_file = Jaris\Site::dataDir()
                . "sqlite/"
                . $db_name
            ;

            if(
                !file_put_contents(
                    $db_file,
                    Jaris\Api::uncompressData($_REQUEST["db"])
                )
            )
            {
                Jaris\Api::sendErrorResponse(
                    1060,
                    "Failed to uncompress database."
                );
            }

            $db = Jaris\Sql::open($db_name);

            $result = Jaris\Sql::query("select data from pages", $db);

            if(!$result)
            {
                Jaris\Sql::close($db);
                unlink($db_file);

                Jaris\Api::sendErrorResponse(
                    1070,
                    "Invalid database format."
                );
            }

            $uris = array();

            while($data = Jaris\Sql::fetchArray($result))
            {
                $data = Jaris\Api::decodeData($data["data"]);
                $page_data = array();

                if(empty($data["uri"]))
                {
                    Jaris\Sql::close($db);
                    unlink($db_file);

                    Jaris\Api::sendErrorResponse(
                        1090,
                        "Provided an item without URI."
                    );
                }
                else
                {
                    $page_data = Jaris\Pages::get($data["uri"]);
                }

                if(!$page_data)
                    continue;

                foreach($data as $index => $value)
                {
                    if($index == "uri")
                        continue;

                    $page_data[$index] = $value;
                }

                if(!Jaris\Pages::edit($data["uri"], $page_data))
                {
                    Jaris\Sql::close($db);
                    unlink($db_file);

                    // Add the pages that where edited.
                    Jaris\Api::addResponse("uris", $uris);

                    Jaris\Api::sendErrorResponse(
                        1100,
                        "Failed to edit all the pages."
                    );
                }
                else
                {
                    $uris[] = $data["uri"];
                }
            }

            Jaris\Sql::close($db);
            unlink($db_file);

            Jaris\Api::addResponse("uris", $uris);
        }
        elseif($action == "delete")
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if(!$page_data)
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            if(
                !Jaris\Pages::delete(
                    $_REQUEST["uri"]
                )
            )
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to delete the page."
                );
            }
        }
        elseif($action == "delete_bulk")
        {
            $uris_recv = Jaris\Api::decodeParam("uris");

            $uris = array();

            foreach($uris_recv as $uri)
            {
                if(empty($uri))
                    continue;

                $page_data = Jaris\Pages::get($uri);

                if(!$page_data)
                    continue;

                if(!Jaris\Pages::delete($uri))
                {
                    // Add the pages that where deleted.
                    Jaris\Api::addResponse("uris", $uris);

                    Jaris\Api::sendErrorResponse(
                        1110,
                        "Failed to delete all the pages."
                    );
                }
                else
                {
                    $uris[] = $uri;
                }
            }

            Jaris\Api::addResponse("uris", $uris);
        }
        elseif($action == "get")
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if(!$page_data)
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            Jaris\Api::addResponse("data", $page_data);
        }
        elseif($action == "get_db")
        {
            if(Jaris\Sql::dbExists("search_engine"))
            {
                Jaris\Api::addResponse(
                    "db",
                    Jaris\Api::compressData(
                        file_get_contents(
                            Jaris\Site::dataDir()
                                . "sqlite/search_engine"
                        )
                    )
                );
            }
            else
            {
                Jaris\Api::sendErrorResponse(
                    1050,
                    "Pages database does not exists."
                );
            }
        }

        Jaris\Api::sendResponse();
    ?>
    field;

    field: rendering_mode
        api
    field;

    field: is_system
        1
    field;
row;
