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
        Subcategories Api
    field;

    field: content
    <?php
        Jaris\Api::init(
    [
                "add" => [
                    "description" => "Adds a new subcategory.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "data" => [
                            "description" => "The subcategory data array.",
                            "elements" => [
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ]
                        ]
                    ],
                    "parameters_required" => [
                        "category",
                        "data"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1020" => "Failed to create the subcategory."
                    ],
                    "permissions" => "add_subcategory_core"
                ],
                "add_bulk" => [
                    "description" => "Add many subcategories in a single request.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "subcategories" => [
                            "description" => "Array of subcategories each with the following elements.",
                            "elements" => [
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ],
                        ]
                    ],
                    "parameters_required" => [
                        "category",
                        "subcategories"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1030" => "Failed to create the subcategories."
                    ],
                    "permissions" => "add_subcategory_core"
                ],
                "add_bulk_raw" => [
                    "description" => "Add many subcategories in a single request with explicit ID's. Will delete any existing subcategories.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "subcategories" => [
                            "description" => "Array of subcategories each with the following elements.",
                            "elements" => [
                                "id" => "Explicit id of the subcategory",
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ],
                        ]
                    ],
                    "parameters_required" => [
                        "category",
                        "subcategories"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1030" => "Failed to create the subcategories."
                    ],
                    "permissions" => "add_subcategory_core"
                ],
                "edit" => [
                    "description" => "Edit an existing subcategory.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "id" => "ID of the subcategory (int).",
                        "data" => [
                            "description" => "The subcategory data array.",
                            "elements" => [
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ]
                        ]
                    ],
                    "parameters_required" => [
                        "category",
                        "id",
                        "data"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1040" => "Failed to edit the subcategory."
                    ],
                    "permissions" => "edit_subcategory_core"
                ],
                "delete" => [
                    "description" => "Delete an existing subcategory.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "id" => "ID of the subcategory (int)."
                    ],
                    "parameters_required" => [
                        "category",
                        "id"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1050" => "Failed to delete the subcategory."
                    ],
                    "permissions" => "delete_subcategory_core"
                ],
                "delete_all" => [
                    "description" => "Delete all existing subcategories.",
                    "parameters" => [
                        "category" => "Machine name of the parent category."
                    ],
                    "parameters_required" => [
                        "category"
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1050" => "Failed to delete the subcategories."
                    ],
                    "permissions" => "delete_subcategory_core"
                ],
                "get" => [
                    "description" => "Get an existing subcategory.",
                    "parameters" => [
                        "category" => "Machine name of the parent category.",
                        "id" => "ID of the subcategory (int)."
                    ],
                    "parameters_required" => [
                        "category",
                        "id"
                    ],
                    "response" => [
                        "data" => [
                            "description" => "The subcategory data.",
                            "elements" => [
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ]
                        ]
                    ],
                    "errors" => [
                        "1010" => "Parent category does not exists.",
                        "1060" => "Subcategory does not exists."
                    ],
                    "permissions" => "get_subcategory_core"
                ],
                "get_all" => [
                    "description" => "Get the list of all subcategories.",
                    "parameters" => [
                        "category" => "Machine name of the parent category."
                    ],
                    "response" => [
                        "categories" => [
                            "description" => "The array of subcategories using the subcategory id as elements index.",
                            "elements" => [
                                "title" => "The subcategory title/label.",
                                "description" => "A description of the subcategory.",
                                "parent" => "Another subcategory id which is parent of this subcategory (int).",
                                "order" => "The order in which the subcategory is displayed (int)."
                            ]
                        ]
                    ],
                    "permissions" => "get_subcategory_core"
                ]
            ]
        );

        $action = Jaris\Api::getAction();

        if ($action == "add") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            if (
                !Jaris\Categories::addSubcategory(
                    $_REQUEST["category"],
                    Jaris\Api::decodeParam("data")
                )
            ) {
                Jaris\Api::sendErrorResponse(
                    1020,
                    "Failed to create the subcategory."
                );
            }
        } elseif ($action == "add_bulk") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            $subcategories = Jaris\Api::decodeParam("subcategories");

            foreach ($subcategories as $subcategory) {
                $subcategory["title"] = trim(
                    Jaris\Util::stripHTMLTags($subcategory["title"])
                );

                if ($subcategory["title"] == "") {
                    continue;
                }

                if (!isset($subcategory["description"])) {
                    $subcategory["description"] = "";
                }

                if (!isset($subcategory["order"])) {
                    $subcategory["order"] = 0;
                }

                if (!isset($subcategory["parent"])) {
                    $subcategory["parent"] = "root";
                }

                if (
                    !Jaris\Categories::addSubcategory(
                        $_REQUEST["category"],
                        $subcategory
                    )
                ) {
                    Jaris\Api::sendErrorResponse(
                        1030,
                        "Failed to create the subcategories."
                    );

                    break;
                }
            }
        } elseif ($action == "add_bulk_raw") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            $subcategories = Jaris\Api::decodeParam("subcategories");

            $subcategories_rows = [];
            $subcategories_file = Jaris\Categories::getPath(
                $_REQUEST["category"]
                ) . "/sub_categories.php"
            ;

            foreach ($subcategories as $subcategory) {
                $subcategory["title"] = trim(
                    Jaris\Util::stripHTMLTags($subcategory["title"])
                );

                if ($subcategory["title"] == "") {
                    continue;
                }

                if (!isset($subcategory["description"])) {
                    $subcategory["description"] = "";
                }

                if (!isset($subcategory["order"])) {
                    $subcategory["order"] = 0;
                }

                if (!isset($subcategory["parent"])) {
                    $subcategory["parent"] = "root";
                }

                if (isset($subcategory["id"])) {
                    $subcategories_rows[$subcategory["id"]] = [
                         "title" => $subcategory["title"],
                         "description" => $subcategory["description"],
                         "order" => $subcategory["order"],
                         "parent" => $subcategory["parent"]
                    ];
                }
            }

            if (
                !Jaris\Data::write(
                    $subcategories_rows,
                    $subcategories_file
                )
            ) {
                Jaris\Api::sendErrorResponse(
                    1030,
                    "Failed to create the subcategories."
                );
            }
        } elseif ($action == "add_update") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }
        } elseif ($action == "edit") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            if (
                !Jaris\Categories::editSubcategory(
                    $_REQUEST["category"],
                    Jaris\Api::decodeParam("data"),
                    $_REQUEST["id"]
                )
            ) {
                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the subcategory."
                );
            }
        } elseif ($action == "delete") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            if (
                !Jaris\Categories::deleteSubcategory(
                    $_REQUEST["category"],
                    $_REQUEST["id"]
                )
            ) {
                Jaris\Api::sendErrorResponse(
                    1050,
                    "Failed to delete the subcategory."
                );
            }
        } elseif ($action == "delete_all") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            $subcategories = Jaris\Categories::getSubcategories(
                $_REQUEST["category"]
            );

            foreach ($subcategories as $sub_id => $sub_data) {
                if (
                    !Jaris\Categories::deleteSubcategory(
                        $_REQUEST["category"],
                        $_REQUEST["id"]
                    )
                ) {
                    Jaris\Api::sendErrorResponse(
                        1060,
                        "Failed to delete all subcategories."
                    );

                    break;
                }
            }
        } elseif ($action == "get") {
            if (!file_exists(Jaris\Categories::getPath($_REQUEST["category"]))) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "Parent category does not exists."
                );
            }

            $subcategory = Jaris\Categories::getSubcategory(
                $_REQUEST["category"],
                $_REQUEST["id"]
            );

            if (!is_array($subcategory)) {
                Jaris\Api::sendErrorResponse(
                    1070,
                    "Subcategory does not exists."
                );
            }

            Jaris\Api::addResponse("data", $subcategory);
        } elseif ($action == "get_all") {
            $subcategories = Jaris\Categories::getSubcategories(
                $_REQUEST["category"]
            );

            Jaris\Api::addResponse(
                "subcategories",
                $subcategories
            );
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
