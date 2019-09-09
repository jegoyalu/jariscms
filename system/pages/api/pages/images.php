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
        Page Images Api
    field;

    field: content
    <?php
        Jaris\Api::init(
            array(
                "add" => array(
                    "description" => "Adds images to existing page.",
                    "parameters" => array(
                        "uri" => "Uri of the page.",
                        "images" => array(
                            "description" => "Images array each element with the following fields:",
                            "elements" => array(
                                "name" => "File name of the image.",
                                "description" => "Short description of the image.",
                                "data" => "Gzipped base64 encoded binary data of the image."
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "uri",
                        "images"
                    ),
                    "response" => array(
                        "images" => "List of image names that where added."
                    ),
                    "errors" => array(
                        "1010" => "Failed to add the images.",
                        "1020" => "The page does not exists."
                    ),
                    "permissions" => "add_page_image_core"
                ),
                "edit" => array(
                    "description" => "Edit images description from existing page.",
                    "parameters" => array(
                        "uri" => "Uri of the page.",
                        "images" => array(
                            "description" => "Images array each element with the following fields:",
                            "elements" => array(
                                "id" => "Numerical id of the image.",
                                "name" => "File name of the image.",
                                "description" => "Short description of the image."
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "uri",
                        "images"
                    ),
                    "response" => array(
                        "images" => "List of image names that where added."
                    ),
                    "errors" => array(
                        "1020" => "The page does not exists.",
                        "1040" => "Failed to edit the images.",
                    ),
                    "permissions" => "edit_page_image_core"
                ),
                "delete" => array(
                    "description" => "Delete existing images from a page.",
                    "parameters" => array(
                        "uri" => "Uri of the page.",
                        "images" => array(
                            "description" => "Array of images to delete with one of the following fields:",
                            "elements" => array(
                                "id" => "Numerical id of the image.",
                                "name" => "Name of image file."
                            )
                        )
                    ),
                    "parameters_required" => array(
                        "uri",
                        "images"
                    ),
                    "errors" => array(
                        "1020" => "The page does not exists.",
                        "1030" => "Failed to delete the images."
                    ),
                    "permissions" => "delete_page_image_core"
                ),
                "get" => array(
                    "description" => "Get list of page images.",
                    "response" => array(
                        "uri" => "Uri or path of page.",
                        "images" => array(
                            "description" => "Array of images with the following fields.",
                            "elements" => array(
                                "id" => "Numerical id of the image.",
                                "name" => "Name of image file.",
                                "description" => "Short description of the image.",
                                "url" => "Url where image can be downloaded."
                            )
                        )
                    ),
                    "errors" => array(
                        "1020" => "Page does not exists."
                    ),
                    "permissions" => "get_page_image_core"
                )
            )
        );

        $action = Jaris\Api::getAction();

        if($action == "add")
        {
            if(!Jaris\Pages::get($_REQUEST["uri"]))
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            $_REQUEST["images"] = Jaris\Api::decodeParam("images");

            if(!is_array($_REQUEST["images"]))
            {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Failed to add the images.",
                );
            }

            $images_added_list = array();

            foreach($_REQUEST["images"] as $image_data)
            {
                $image_array = array();

                $image_path = Jaris\Site::dataDir()
                    . Jaris\Users::generatePassword()
                    . $image_data["name"]
                ;

                file_put_contents(
                    $image_path,
                    Jaris\Api::uncompressData($image_data["data"])
                );

                $image_data["tmp_name"] = $image_path;

                $image_data["type"] = Jaris\FileSystem::getMimeTypeLocal(
                    $image_path
                );

                $file_name = "";

                if(
                    Jaris\Pages\Images::add(
                        $image_data,
                        $image_data["description"] ?? "",
                        $_REQUEST["uri"],
                        $file_name
                    )
                    !=
                    "true"
                )
                {
                    if(file_exists($image_path))
                    {
                        unlink($image_path);
                    }
                }
                else
                {
                    $images_added_list[] = $file_name;
                }
            }

            Jaris\Api::addResponse("images", $images_added_list);
        }
        elseif($action == "edit")
        {
            if(!Jaris\Pages::get($_REQUEST["uri"]))
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            $_REQUEST["images"] = Jaris\Api::decodeParam("images");

            if(!is_array($_REQUEST["images"]))
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the images.",
                );
            }

            $images_edited_list = array();

            foreach($_REQUEST["images"] as $image_details)
            {
                $image_data = array();
                $by_id = false;
                $by_name = false;

                if(isset($image_details["id"]))
                {
                    $image_data = Jaris\Pages\Images::get(
                        $image_details["id"],
                        $_REQUEST["uri"]
                    );

                    if($image_data)
                    {
                        $by_id = true;
                    }
                }

                if(!$image_data && isset($image_details["name"]))
                {
                    $image_data = Jaris\Pages\Images::getByName(
                        $image_details["name"],
                        $_REQUEST["uri"]
                    );

                    if($image_data)
                    {
                        $by_name = true;
                    }
                }

                if(!$image_data)
                {
                    // We skip unexistant image
                    continue;
                }

                $image_data["description"] =
                    $image_details["description"]
                    ??
                    ""
                ;
                
                $image_data["order"] = $image_details["order"] ?? 0;

                $message = "";

                if($by_id)
                {
                    $message = Jaris\Pages\Images::edit(
                        $image_details["id"],
                        $image_data,
                        $_REQUEST["uri"]
                    );
                }
                elseif($by_name)
                {
                    $message = Jaris\Pages\Images::editByName(
                        $image_details["name"],
                        $image_data,
                        $_REQUEST["uri"]
                    );
                }

                if(
                    $message
                    ==
                    "true"
                )
                {
                    $images_edited_list[] = $file_name;
                }
            }

            Jaris\Api::addResponse("images", $images_edited_list);
        }
        elseif($action == "delete")
        {
            if(!Jaris\Pages::get($_REQUEST["uri"]))
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            $_REQUEST["images"] = Jaris\Api::decodeParam("images");

            if(!is_array($_REQUEST["images"]))
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the images.",
                );
            }

            $images_edited_list = array();

            foreach($_REQUEST["images"] as $image_details)
            {
                if(!empty($image_details["id"]))
                {
                    Jaris\Pages\Images::delete(
                        $image_details["id"],
                        $_REQUEST["uri"]
                    );
                }
                elseif(!empty($image_details["name"]))
                {
                    Jaris\Pages\Images::deleteByName(
                        $image_details["name"],
                        $_REQUEST["uri"]
                    );
                }
            }
        }
        elseif($action == "get")
        {
            if(!Jaris\Pages::get($_REQUEST["uri"]))
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "The page does not exists."
                );
            }

            $images = Jaris\Pages\Images::getList($_REQUEST["uri"], false);
            $images_list = array();

            foreach($images as $image_id => $image_data)
            {
                $image_data["id"] = $image_id;
                $image_data["url"] = Jaris\Uri::url(
                    "image/".$_REQUEST["uri"]."/".$image_data["name"]
                );

                $images_list[] = $image_data;
            }

            Jaris\Api::addResponse("images", $images_list);
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
