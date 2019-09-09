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
        Categories Api
    field;

    field: content
    <?php
        Jaris\Api::init(
            array(
                "add" => array(
                    "description" => "Adds a new category.",
                    "parameters" => array(
                        "name" => "Machine name of the category.",
                        "data" => array(
                            "description" => "The category data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Category.",
                                "description" => "A brief description of the category.",
                                "multiple" => "Enable multiple selection (bool).",
                                "sorting" => "To enable or disable automatic sorting (bool).",
                                "display_subcategories" => "Display all the subcategories on the generated menu (bool).",
                                "order" => "The order in which the category is displayed (int)."
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
                        "1010" => "Category already exists.",
                        "1020" => "Failed to create the category."
                    ),
                    "permissions" => "add_category_core"
                ),
                "add_update" => array(
                    "description" => "Edit an existing category or creates it if doesn't exists.",
                    "parameters" => array(
                        "name" => "Machine name of the category.",
                        "data" => array(
                            "description" => "The category data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Category.",
                                "description" => "A brief description of the category.",
                                "multiple" => "Enable multiple selection (bool).",
                                "sorting" => "To enable or disable automatic sorting (bool).",
                                "display_subcategories" => "Display all the subcategories on the generated menu (bool).",
                                "order" => "The order in which the category is displayed (int)."
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
                        "1020" => "Failed to create the category.",
                        "1040" => "Failed to edit the category."
                    ),
                    "permissions" => array(
                        "add_category_core",
                        "edit_category_core"
                    )
                ),
                "edit" => array(
                    "description" => "Edit an existing category.",
                    "parameters" => array(
                        "name" => "Machine name of the category.",
                        "data" => array(
                            "description" => "The category data array.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Category.",
                                "description" => "A brief description of the category.",
                                "multiple" => "Enable multiple selection (bool).",
                                "sorting" => "To enable or disable automatic sorting (bool).",
                                "display_subcategories" => "Display all the subcategories on the generated menu (bool).",
                                "order" => "The order in which the category is displayed (int)."
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
                        "1030" => "Category does not exists.",
                        "1040" => "Failed to edit the category."
                    ),
                    "permissions" => "edit_category_core"
                ),
                "delete" => array(
                    "description" => "Delete an existing category.",
                    "parameters" => array(
                        "name" => "Machine name of the category."
                    ),
                    "parameters_required" => array(
                        "name"
                    ),
                    "errors" => array(
                        "1030" => "Category does not exists.",
                        "1050" => "Failed to delete the category."
                    ),
                    "permissions" => "delete_category_core"
                ),
                "get" => array(
                    "description" => "Get an existing category.",
                    "parameters" => array(
                        "name" => "Machine name of the category."
                    ),
                    "parameters_required" => array(
                        "name"
                    ),
                    "response" => array(
                        "data" => array(
                            "description" => "The category data.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Category.",
                                "description" => "A brief description of the category.",
                                "multiple" => "Enable multiple selection (bool).",
                                "sorting" => "To enable or disable automatic sorting (bool).",
                                "display_subcategories" => "Display all the subcategories on the generated menu (bool).",
                                "order" => "The order in which the category is displayed (int)."
                            )
                        )
                    ),
                    "errors" => array(
                        "1030" => "Category does not exists."
                    ),
                    "permissions" => "get_category_core"
                ),
                "get_all" => array(
                    "description" => "Get the list of all categories.",
                    "parameters" => array(
                        "type" => "Optional content type where categories belong."
                    ),
                    "response" => array(
                        "categories" => array(
                            "description" => "The array of categories using the category machine name as elements index.",
                            "elements" => array(
                                "name" => "A human readable name like for example: My Category.",
                                "description" => "A brief description of the category.",
                                "multiple" => "Enable multiple selection (bool).",
                                "sorting" => "To enable or disable automatic sorting (bool).",
                                "display_subcategories" => "Display all the subcategories on the generated menu (bool).",
                                "order" => "The order in which the category is displayed (int)."
                            )
                        )
                    ),
                    "permissions" => "get_category_core"
                )
            )
        );

        $action = Jaris\Api::getAction();

        if($action == "add")
        {
            if(file_exists(Jaris\Categories::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Category already exists."
                );
            }

            if(
                Jaris\Categories::add(
                    $_REQUEST["name"],
                    Jaris\Api::decodeParam("data")
                )
                !=
                "true"
            )
            {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "Failed to create the category."
                );
            }
        }
        elseif($action == "add_update")
        {
            if(!file_exists(Jaris\Categories::getPath($_REQUEST["name"])))
            {
                if(
                    Jaris\Categories::add(
                        $_REQUEST["name"],
                        Jaris\Api::decodeParam("data")
                    )
                    !=
                    "true"
                )
                {
                    Jaris\Api::sendErrorResponse(
                        1020,
                        "Failed to create the category."
                    );
                }
            }
            elseif(
                !Jaris\Categories::edit(
                    $_REQUEST["name"],
                    Jaris\Api::decodeParam("data")
                )
            )
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the category."
                );
            }
        }
        elseif($action == "edit")
        {
            if(!file_exists(Jaris\Categories::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Category does not exists."
                );
            }

            if(
                !Jaris\Categories::edit(
                    $_REQUEST["name"],
                    Jaris\Api::decodeParam("data")
                )
            )
            {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the category."
                );
            }
        }
        elseif($action == "delete")
        {
            if(!file_exists(Jaris\Categories::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Category does not exists."
                );
            }

            if(!Jaris\Categories::delete($_REQUEST["name"]))
            {
                Jaris\Api::sendErrorResponse(
                    1050,
                    "Failed to delete the category."
                );
            }
        }
        elseif($action == "get")
        {
            if(!file_exists(Jaris\Categories::getPath($_REQUEST["name"])))
            {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Category does not exists."
                );
            }

            $category = Jaris\Categories::get($_REQUEST["name"]);

            Jaris\Api::addResponse("data", $category);
        }
        elseif($action == "get_all")
        {
            $categories = Jaris\Categories::getList($_REQUEST["type"] ?? "");

            Jaris\Api::addResponse("categories", $categories);
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
