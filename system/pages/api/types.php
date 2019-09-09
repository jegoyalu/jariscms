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
        Content Types Api
    field;

    field: content
    <?php
        Jaris\Api::init(
            array(
                "add" => array(
                    "description" => "Adds a new type.",
                    "parameters" => array(
                        "name" => "Machine name of the type.",
                        "data" => array(
                            "description" => "The type data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Type.",
                                "description" => "A brief description of the type.",
                                "image" => "Array with a string 'name' index and a 'data' index with image binary data gzipped and base64 encoded. Supported formats are: gif, jpg and png.",
                                "categories" => "Array of category machine names a user can select for this type of content.",
                                "uri_scheme" => "The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}.",
                                "input_format" => "Default Input Format, eg: full_html, php_code.",
                                "requires_approval" => "Array of user group machine names that require approval.",
                                "title_label" => "The order in which the category is displayed (int).",
                                "title_description" => "The label of the input title.",
                                "content_label" => "The description of the title.",
                                "content_description" => "The label of the input content."
                            ),
                            "elements_required" => array(
                                "name", "description"
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "name",
                        "data"
                    ),
                    "errors" => array(
                        "1010" => "Type already exists.",
                        "1020" => "Failed to create the type."
                    ),
                    "permissions" => "add_type_core"
                ),
                "add_update" => array(
                    "description" => "Edit an existing type or creates it if doesn't exists.",
                    "parameters" => array(
                        "name" => "Machine name of the type.",
                        "data" => array(
                            "description" => "The type data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Type.",
                                "description" => "A brief description of the type.",
                                "image" => "Array with a string 'name' index and a 'data' index with image binary data gzipped and base64 encoded. Supported formats are: gif, jpg and png.",
                                "categories" => "Array of category machine names a user can select for this type of content.",
                                "uri_scheme" => "The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}.",
                                "input_format" => "Default Input Format, eg: full_html, php_code.",
                                "requires_approval" => "Array of user group machine names that require approval.",
                                "title_label" => "The order in which the category is displayed (int).",
                                "title_description" => "The label of the input title.",
                                "content_label" => "The description of the title.",
                                "content_description" => "The label of the input content."
                            ),
                            "elements_required" => array(
                                "name", "description"
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "name",
                        "data"
                    ),
                    "errors" => array(
                        "1020" => "Failed to create the type.",
                        "1040" => "Failed to edit the type."
                    ),
                    "permissions" => array(
                        "add_type_core",
                        "edit_type_core"
                    )
                ),
                "edit" => array(
                    "description" => "Edit an existing type.",
                    "parameters" => array(
                        "name" => "Machine name of the type.",
                        "data" => array(
                            "description" => "The type data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Type.",
                                "description" => "A brief description of the type.",
                                "image" => "Array with a string 'name' index and a 'data' index with image binary data gzipped and base64 encoded. Supported formats are: gif, jpg and png.",
                                "categories" => "Array of category machine names a user can select for this type of content.",
                                "uri_scheme" => "The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}.",
                                "input_format" => "Default Input Format, eg: full_html, php_code.",
                                "requires_approval" => "Array of user group machine names that require approval.",
                                "title_label" => "The order in which the category is displayed (int).",
                                "title_description" => "The label of the input title.",
                                "content_label" => "The description of the title.",
                                "content_description" => "The label of the input content."
                            ),
                            "elements_required" => array(
                                "name", "description"
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "name",
                        "data"
                    ),
                    "errors" => array(
                        "1030" => "Type does not exists.",
                        "1040" => "Failed to edit the type."
                    ),
                    "permissions" => "edit_type_core"
                ),
                "delete" => array(
                    "description" => "Delete an existing type.",
                    "parameters" => array(
                        "name" => "Machine name of the type."
                    ),
                    "parameters_required" => array(
                        "name"
                    ),
                    "errors" => array(
                        "1030" => "Type does not exists.",
                        "1050" => "Failed to delete the type."
                    ),
                    "permissions" => "delete_type_core"
                ),
                "get" => array(
                    "description" => "Get an existing type.",
                    "parameters" => array(
                        "name" => "Machine name of the type."
                    ),
                    "parameters_required" => array(
                        "name"
                    ),
                    "response" => array(
                        "data" => array(
                            "description" => "The type data.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Type.",
                                "description" => "A brief description of the type.",
                                "image" => "Url of the type image.",
                                "categories" => "Array of category machine names a user can select for this type of content.",
                                "uri_scheme" => "The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}.",
                                "input_format" => "Default Input Format, eg: full_html, php_code.",
                                "requires_approval" => "Array of user group machine names that require approval.",
                                "title_label" => "The order in which the category is displayed (int).",
                                "title_description" => "The label of the input title.",
                                "content_label" => "The description of the title.",
                                "content_description" => "The label of the input content."
                            )
                        )
                    ),
                    "errors" => array(
                        "1030" => "Type does not exists."
                    ),
                    "permissions" => "get_type_core"
                ),
                "get_all" => array(
                    "description" => "Get the list of all types.",
                    "parameters" => array(
                        "group" => "Get only types that can be accessed from the given group machine name.",
                        "username" => "Get only types that can be accessed from the given username."
                    ),
                    "response" => array(
                        "types" => array(
                            "description" => "The array of types using the type machine name as elements index.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Type.",
                                "description" => "A brief description of the type.",
                                "image" => "Url of the type image.",
                                "categories" => "Array of category machine names a user can select for this type of content.",
                                "uri_scheme" => "The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}.",
                                "input_format" => "Default Input Format, eg: full_html, php_code.",
                                "requires_approval" => "Array of user group machine names that require approval.",
                                "title_label" => "The order in which the category is displayed (int).",
                                "title_description" => "The label of the input title.",
                                "content_label" => "The description of the title.",
                                "content_description" => "The label of the input content."
                            )
                        )
                    ),
                    "permissions" => "get_type_core"
                )
            )
        );

        $action = Jaris\Api::getAction();

        if($action == "add")
        {
            if(file_exists(Jaris\Types::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Type already exists."
                );
            }

            $data = Jaris\Api::decodeParam("data");

            if(empty($data["image"]))
            {
                $data["image"] = array();
            }
            elseif(
                !empty($data["image"]["name"])
                &&
                !empty($data["image"]["data"])
            )
            {
                $image_name = Jaris\Site::dataDir()
                    . Jaris\Users::generatePassword()
                    . $data["image"]["name"]
                ;
                if(
                    file_put_contents(
                        $image_name,
                        Jaris\Api::uncompressData($data["image"]["data"])
                    )
                )
                {
                    $image_type = Jaris\FileSystem::getMimeTypeLocal($image_name);

                    $data["image"] = array(
                        "name" => $data["image"],
                        "type" => $image_type,
                        "tmp_name" => $image_name
                    );
                }
                else
                {
                    Jaris\Api::sendErrorResponse(
                        1020,
                        "Failed to create the type."
                    );
                }
            }

            if(empty($data["categories"]))
            {
                $data["categories"] = array();
            }
            if(empty($data["uri_scheme"]))
            {
                $data["uri_scheme"] = "{user}/{type}/{title}";
            }
            if(empty($data["input_format"]))
            {
                $data["input_format"] = "full_html";
            }
            if(empty($data["requires_approval"]))
            {
                $data["requires_approval"] = array();
            }
            if(empty($data["title_label"]))
            {
                $data["title_label"] = "Title:";
            }
            if(empty($data["title_description"]))
            {
                $data["title_description"] = "Displayed on the web browser title bar and inside the website.";
            }
            if(empty($data["content_label"]))
            {
                $data["content_label"] = "Content:";
            }
            if(empty($data["content_description"]))
            {
                $data["content_description"] = "";
            }

            if(
                Jaris\Types::add(
                    $_REQUEST["name"],
                    $data
                )
                !=
                "true"
            )
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "Failed to create the type."
                );
            }
            elseif(!empty($data["image"]["tmp_name"]))
            {
                unlink($data["image"]["tmp_name"]);
            }
        }
        elseif($action == "add_update")
        {
            $data = Jaris\Api::decodeParam("data");

            if(empty($data["image"]))
            {
                $data["image"] = array();
            }
            elseif(
                !empty($data["image"]["name"])
                &&
                !empty($data["image"]["data"])
            )
            {
                $image_name = Jaris\Site::dataDir()
                    . Jaris\Users::generatePassword()
                    . $data["image"]["name"]
                ;
                if(
                    file_put_contents(
                        $image_name,
                        Jaris\Api::uncompressData($data["image"]["data"])
                    )
                )
                {
                    $image_type = Jaris\FileSystem::getMimeTypeLocal($image_name);

                    $data["image"] = array(
                        "name" => $data["image"],
                        "type" => $image_type,
                        "tmp_name" => $image_name
                    );
                }
                else
                {
                    Jaris\Api::sendErrorResponse(
                        1020,
                        "Failed to create the type."
                    );
                }
            }

            if(empty($data["categories"]))
            {
                $data["categories"] = array();
            }
            if(empty($data["uri_scheme"]))
            {
                $data["uri_scheme"] = "{user}/{type}/{title}";
            }
            if(empty($data["input_format"]))
            {
                $data["input_format"] = "full_html";
            }
            if(empty($data["requires_approval"]))
            {
                $data["requires_approval"] = array();
            }
            if(empty($data["title_label"]))
            {
                $data["title_label"] = "Title:";
            }
            if(empty($data["title_description"]))
            {
                $data["title_description"] = "Displayed on the web browser title bar and inside the website.";
            }
            if(empty($data["content_label"]))
            {
                $data["content_label"] = "Content:";
            }
            if(empty($data["content_description"]))
            {
                $data["content_description"] = "";
            }

            if(!file_exists(Jaris\Types::getPath($_REQUEST["name"])))
            {
                if(
                    Jaris\Types::add($_REQUEST["name"], $data)
                    !=
                    "true"
                )
                {
                    Jaris\Api::sendErrorResponse(
                        1020,
                        "Failed to create the image."
                    );
                }
                elseif(!empty($data["image"]["tmp_name"]))
                {
                    unlink($data["image"]["tmp_name"]);
                }
            }
            elseif(!Jaris\Types::edit($_REQUEST["name"], $data))
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the image."
                );
            }
            elseif(!empty($data["image"]["tmp_name"]))
            {
                unlink($data["image"]["tmp_name"]);
            }
        }
        elseif($action == "edit")
        {
            if(!file_exists(Jaris\Types::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Type does not exists."
                );
            }

            $data = Jaris\Api::decodeParam("data");

            if(empty($data["image"]))
            {
                $data["image"] = array();
            }
            elseif(
                !empty($data["image"]["name"])
                &&
                !empty($data["image"]["data"])
            )
            {
                $image_name = Jaris\Site::dataDir()
                    . Jaris\Users::generatePassword()
                    . $data["image"]["name"]
                ;
                if(
                    file_put_contents(
                        $image_name,
                        Jaris\Api::uncompressData($data["image"]["data"])
                    )
                )
                {
                    $image_type = Jaris\FileSystem::getMimeTypeLocal($image_name);

                    $data["image"] = array(
                        "name" => $data["image"],
                        "type" => $image_type,
                        "tmp_name" => $image_name
                    );
                }
                else
                {
                    Jaris\Api::sendErrorResponse(
                        1020,
                        "Failed to create the type."
                    );
                }
            }

            if(!Jaris\Types::edit($_REQUEST["name"], $data))
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the type."
                );
            }
        }
        elseif($action == "delete")
        {
            if(!file_exists(Jaris\Types::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Type does not exists."
                );
            }

            if(!Jaris\Types::delete($_REQUEST["name"]))
            {
                Jaris\Api::sendErrorResponse(
                    1050,
                    "Failed to delete the type."
                );
            }
        }
        elseif($action == "get")
        {
            if(!file_exists(Jaris\Types::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Type does not exists."
                );
            }

            $type = Jaris\Types::get($_REQUEST["name"]);

            if(trim($type["image"]) != "")
            {
                $type["image"] = Jaris\Types::getImageUrl($_REQUEST["name"]);
            }

            Jaris\Api::addResponse("data", $type);
        }
        elseif($action == "get_all")
        {
            $types = Jaris\Types::getList(
                $_REQUEST["group"] ?? "",
                $_REQUEST["username"] ?? ""
            );

            foreach($types as $name => &$type_data)
            {
                if(trim($type_data["image"]) != "")
                {
                    $type_data["image"] = Jaris\Types::getImageUrl($name);
                }
            }

            Jaris\Api::addResponse("types", $types);
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
