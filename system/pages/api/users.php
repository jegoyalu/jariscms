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
        Users Api
    field;

    field: content
    <?php
        Jaris\Api::init(
    [
                "add" => [
                    "description" => "Adds a new user.",
                    "parameters" => [
                        "username" => "Username of the user a-zA-Z0-9.",
                        "email" => "Valid e-mail address.",
                        "password" => "A predefined password.",
                        "name" => "Full name of the user.",
                        "group" => "Machine name of user group eg: regular, administrator",
                        "gender" => "Sexual gender of the user: m for male and f for female.",
                        "status" => "Status of the account: 0 for awaiting approval, 1 for activated and 2 for blocked.",
                        "birth_date" => "A timestamp in seconds.",
                        "picture" => [
                            "description" => "The user picture.",
                            "elements" => [
                                "name" => "File name of the picture.",
                                "data" => "Gzipped base64 encoded jpg, png or gif image."
                            ]
                        ],
                        "website" => "A valid website url.",
                        "personal_text" => "A small bio of the user."
                    ],
                    "parameters_required" => [
                        "email"
                    ],
                    "errors" => [
                        "1010" => "User already exists.",
                        "1020" => "Failed to create the user.",
                        "1060" => "Invalid e-mail address provided."
                    ],
                    "permissions" => "add_user_core"
                ],
                "edit" => [
                    "description" => "Edit an existing user.",
                    "parameters" => [
                        "username" => "Username of the user a-zA-Z0-9.",
                        "email" => "Valid e-mail address.",
                        "new_email" => "A new valid e-mail address.",
                        "password" => "A predefined password.",
                        "name" => "Full name of the user.",
                        "group" => "Machine name of user group eg: regular, administrator",
                        "gender" => "Sexual gender of the user: m for male and f for female.",
                        "status" => "Status of the account: 0 for awaiting approval, 1 for activated and 2 for blocked.",
                        "birth_date" => "A timestamp in seconds.",
                        "picture" => [
                            "description" => "The user picture.",
                            "elements" => [
                                "name" => "File name of the picture.",
                                "data" => "Gzipped base64 encoded jpg, png or gif image."
                            ]
                        ],
                        "website" => "A valid website url.",
                        "personal_text" => "A small bio of the user."
                    ],
                    "parameters_required" => [
                        "username,email"
                    ],
                    "errors" => [
                        "1030" => "User does not exists.",
                        "1040" => "Failed to edit the user."
                    ],
                    "permissions" => "edit_user_core"
                ],
                "delete" => [
                    "description" => "Delete an existing user.",
                    "parameters" => [
                        "username" => "Username of the user a-zA-Z0-9.",
                        "email" => "Valid e-mail address.",
                    ],
                    "parameters_required" => [
                        "username,email"
                    ],
                    "errors" => [
                        "1030" => "User does not exists.",
                        "1050" => "Failed to delete the user."
                    ],
                    "permissions" => "delete_user_core"
                ],
                "get" => [
                    "description" => "Get an existing user.",
                    "parameters" => [
                        "username" => "Username of the user a-zA-Z0-9.",
                        "email" => "Valid e-mail address."
                    ],
                    "parameters_required" => [
                        "username,email"
                    ],
                    "response" => [
                        "username" => "Username of the user a-zA-Z0-9.",
                        "email" => "Valid e-mail address.",
                        "password" => "A predefined password.",
                        "name" => "Full name of the user.",
                        "group" => "Machine name of user group eg: regular, administrator",
                        "gender" => "Sexual gender of the user: m for male and f for female.",
                        "status" => "Status of the account: 0 for awaiting approval, 1 for activated and 2 for blocked.",
                        "birth_date" => "A timestamp in seconds.",
                        "picture" => [
                            "description" => "The user picture.",
                            "elements" => [
                                "name" => "File name of the picture.",
                                "data" => "Gzipped base64 encoded jpg, png or gif image."
                            ]
                        ],
                        "website" => "A valid website url.",
                        "personal_text" => "A small bio of the user."
                    ],
                    "errors" => [
                        "1030" => "User does not exists."
                    ],
                    "permissions" => "get_user_core"
                ],
                "get_db" => [
                    "description" => "Get the list of all users.",
                    "response" => [
                        "db" => [
                            "description" => "GZipped base64 encoded sqlite database of users with a table called users and the following fields:",
                            "elements" => [
                                "username" => "Username of the user a-zA-Z0-9.",
                                "email" => "Valid e-mail address.",
                                "register_date" => "The user registration date in seconds.",
                                "user_group" => "Machine name of user group eg: regular, administrator",
                                "picture" => "Picture file name.",
                                "ip_address" => "Status of the account: 0 for awaiting approval, 1 for activated and 2 for blocked.",
                                "gender" => "Sexual gender of the user: m for male and f for female.",
                                "birth_date" => "A timestamp in seconds.",
                                "status" => "Status of the account: 0 for awaiting approval, 1 for activated and 2 for blocked.",
                            ]
                        ]
                    ],
                    "errors" => [
                        "1070" => "Users database does not exists."
                    ],
                    "permissions" => "get_user_core"
                ]
            ]
        );

        $action = Jaris\Api::getAction();

        if ($action == "add") {
            if (
                (
                    isset($_REQUEST["username"])
                    &&
                    Jaris\Users::get($_REQUEST["username"])
                )
                ||
                (
                    isset($_REQUEST["email"])
                    &&
                    Jaris\Users::getByEmail($_REQUEST["email"])
                )
            ) {
                Jaris\Api::sendErrorResponse(
                    1010,
                    "User already exists."
                );
            }

            $data = [];
            $username = "";
            $group = "regular";
            $picture = [];

            if (
                !empty($_REQUEST["username"])
                &&
                Jaris\Forms::validUsername($_REQUEST["username"])
            ) {
                $username = $_REQUEST["username"];
            }
            if (
                Jaris\Forms::validEmail($_REQUEST["email"], false)
            ) {
                $data["email"] = $_REQUEST["email"];

                if (!$username) {
                    // Generate a username with the given e-mail
                    $email_parts = explode("@", $_REQUEST["email"]);
                    $username_original = $email_parts[0];
                    $username = $username_original;
                    $username_id = 1;

                    while (Jaris\Users::get($username)) {
                        $username = $username_original . $username_id;
                        $username_id++;
                    }
                }
            } else {
                Jaris\Api::sendErrorResponse(
                    1060,
                    "Invalid e-mail address provided."
                );
            }
            if (!empty($_REQUEST["password"])) {
                $data["password"] = $_REQUEST["password"];
            } else {
                $data["password"] = Jaris\Users::generatePassword();
            }
            if (!empty($_REQUEST["name"])) {
                $data["name"] = $_REQUEST["name"];
            } else {
                $data["name"] = $username;
            }
            if (
                !empty($_REQUEST["group"])
                &&
                Jaris\Groups::get($_REQUEST["group"])
            ) {
                $group = $_REQUEST["group"];
            }
            if (
                !empty($_REQUEST["gender"])
                &&
                in_array($_REQUEST["gender"], ["m", "f"])
            ) {
                $data["gender"] = $_REQUEST["gender"];
            } else {
                $data["gender"] = "m";
            }
            if (
                !empty($_REQUEST["status"])
                &&
                in_array($_REQUEST["status"], ["0", "1", "2"])
            ) {
                $data["status"] = $_REQUEST["status"];
            } else {
                $data["gender"] = "1";
            }
            if (
                !empty($_REQUEST["birth_date"])
                &&
                is_int($_REQUEST["birth_date"])
            ) {
                $data["birth_date"] = $_REQUEST["birth_date"];
            } else {
                $data["birth_date"] = time();
            }
            if (
                !empty($_REQUEST["picture"])
            ) {
                $picture = Jaris\Api::decodeParam("picture");

                if (!empty($picture["name"]) && !empty($picture["data"])) {
                    $picture["tmp_name"] = Jaris\Site::dataDir()
                        . Jaris\Users::generatePassword()
                        . $picture["name"]
                    ;

                    file_put_contents(
                        $picture["tmp_name"],
                        Jaris\Api::uncompressData($picture["data"])
                    );

                    unset($picture["data"]);
                } else {
                    $picture = [];
                }
            }
            if (
                !empty($_REQUEST["website"])
            ) {
                $data["website"] = $_REQUEST["website"];
            } else {
                $data["website"] = "";
            }
            if (
                !empty($_REQUEST["personal_text"])
            ) {
                $data["personal_text"] = Jaris\Util::stripHTMLTags(
                    $_REQUEST["personal_text"]
                );
            } else {
                $data["personal_text"] = "";
            }

            if (
                Jaris\Users::add(
                    $username,
                    $group,
                    $data,
                    $picture
                )
                !=
                "true"
            ) {
                if (!empty($picture["tmp_name"])) {
                    unlink($picture["tmp_name"]);
                }

                Jaris\Api::sendErrorResponse(
                    1020,
                    "Failed to create the user."
                );
            } elseif (
                !empty($picture["tmp_name"])
                &&
                file_exists($picture["tmp_name"])
            ) {
                unlink($picture["tmp_name"]);
            }
        } elseif ($action == "edit") {
            $user_data = [];
            $username = "";
            $group = "";

            if (
                empty($_REQUEST["username"])
                ||
                !($user_data = Jaris\Users::get($_REQUEST["username"]))
            ) {
                if (!($user_data = Jaris\Users::getByEmail($_REQUEST["email"]))) {
                    Jaris\Api::sendErrorResponse(
                        1030,
                        "User does not exists."
                    );
                } else {
                    $username = $user_data["username"];
                }
            } else {
                $username = $_REQUEST["username"];
            }

            $group = $user_data["group"];

            $picture = [];

            if (
                !empty($_REQUEST["new_email"])
                &&
                Jaris\Forms::validEmail($_REQUEST["new_email"], false)
                &&
                !Jaris\Users::getByEmail($_REQUEST["new_email"])
            ) {
                $user_data["email"] = $_REQUEST["new_email"];
            }
            if (!empty($_REQUEST["password"])) {
                $user_data["password"] = $_REQUEST["password"];
            }
            if (!empty($_REQUEST["name"])) {
                $user_data["name"] = $_REQUEST["name"];
            }
            if (
                !empty($_REQUEST["group"])
                &&
                Jaris\Groups::get($_REQUEST["group"])
            ) {
                $group = $_REQUEST["group"];
            }
            if (
                !empty($_REQUEST["gender"])
                &&
                in_array($_REQUEST["gender"], ["m", "f"])
            ) {
                $user_data["gender"] = $_REQUEST["gender"];
            }
            if (
                !empty($_REQUEST["status"])
                &&
                in_array($_REQUEST["status"], ["0", "1", "2"])
            ) {
                $user_data["status"] = $_REQUEST["status"];
            }
            if (
                !empty($_REQUEST["birth_date"])
                &&
                is_int($_REQUEST["birth_date"])
            ) {
                $user_data["birth_date"] = $_REQUEST["birth_date"];
            }
            if (
                !empty($_REQUEST["picture"])
            ) {
                $picture = Jaris\Api::decodeParam("picture");

                if (!empty($picture["name"]) && !empty($picture["data"])) {
                    $picture["tmp_name"] = Jaris\Site::dataDir()
                        . Jaris\Users::generatePassword()
                        . $picture["name"]
                    ;

                    file_put_contents(
                        $picture["tmp_name"],
                        Jaris\Api::uncompressData($picture["data"])
                    );

                    unset($picture["data"]);
                } else {
                    $picture = [];
                }
            }
            if (
                !empty($_REQUEST["website"])
            ) {
                $user_data["website"] = $_REQUEST["website"];
            }
            if (
                !empty($_REQUEST["personal_text"])
            ) {
                $user_data["personal_text"] = Jaris\Util::stripHTMLTags(
                    $_REQUEST["personal_text"]
                );
            }

            if (
                Jaris\Users::edit(
                    $username,
                    $group,
                    $user_data,
                    $picture
                )
                !=
                "true"
            ) {
                if (!empty($picture["tmp_name"])) {
                    unlink($picture["tmp_name"]);
                }

                Jaris\Api::sendErrorResponse(
                    1040,
                    "Failed to edit the user."
                );
            } elseif (
                !empty($picture["tmp_name"])
                &&
                file_exists($picture["tmp_name"])
            ) {
                unlink($picture["tmp_name"]);
            }
        } elseif ($action == "delete") {
            $username = "";
            $user_data = [];

            if (
                empty($_REQUEST["username"])
                ||
                !($user_data = Jaris\Users::get($_REQUEST["username"]))
            ) {
                if (!($user_data = Jaris\Users::getByEmail($_REQUEST["email"]))) {
                    Jaris\Api::sendErrorResponse(
                        1030,
                        "User does not exists."
                    );
                } else {
                    $username = $user_data["username"];
                }
            } else {
                $username = $_REQUEST["username"];
            }

            if (!Jaris\Users::delete($username)) {
                Jaris\Api::sendErrorResponse(
                    1050,
                    "Failed to delete the user."
                );
            }
        } elseif ($action == "get") {
            $username = "";
            $user_data = [];

            if (
                empty($_REQUEST["username"])
                ||
                !($user_data = Jaris\Users::get($_REQUEST["username"]))
            ) {
                if (!($user_data = Jaris\Users::getByEmail($_REQUEST["email"]))) {
                    Jaris\Api::sendErrorResponse(
                        1030,
                        "User does not exists."
                    );
                } else {
                    $username = $user_data["username"];
                }
            } else {
                $username = $_REQUEST["username"];
            }

            if (trim($user_data["picture"]) != "") {
                $user_data["picture"] = [
                    "name" => $user_data["picture"],
                    "data" => Jaris\Api::compressData(file_get_contents(
                        Jaris\Users::getPicturePath($username)
                    ))
                ];
            }

            foreach ($user_data as $key => $value) {
                Jaris\Api::addResponse($key, $value);
            }
        } elseif ($action == "get_db") {
            if (Jaris\Sql::dbExists("users")) {
                Jaris\Api::addResponse(
                    "db",
                    Jaris\Api::compressData(
                        file_get_contents(
                            Jaris\Site::dataDir()
                                . "sqlite/users"
                        )
                    )
                );
            } else {
                Jaris\Api::sendErrorResponse(
                    1070,
                    "Users database does not exists."
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
