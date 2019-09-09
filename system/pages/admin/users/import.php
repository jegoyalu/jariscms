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
        <?php print t("Import Users") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_users"));
    ?>
    <script>
    $(document).ready(function(){
        $("#preview").css("max-width", $("#preview").parent().width() + "px");
        $("#tbl-preview").show();
    });
    </script>
    <?php
        Jaris\View::addTab(t("Navigation View"), "admin/users");
        Jaris\View::addTab(t("List View"), "admin/users/list");
        Jaris\View::addTab(t("Create User"), "admin/users/add");
        Jaris\View::addTab(t("Groups"), "admin/groups");
        Jaris\View::addTab(t("Import"), "admin/users/import");
        Jaris\View::addTab(t("Export"), "admin/users/export");

        if(
            isset($_REQUEST["btnUpload"])
            &&
            !Jaris\Forms::requiredFieldEmpty("users-upload-csv")
        )
        {
            $_SESSION["users_import"] = array(
                "delimeter" => $_REQUEST["delimeter"],
                "enclosure" => $_REQUEST["enclosure"],
                "escape" => $_REQUEST["escape"],
                "file" => $_FILES["csv"]["tmp_name"]
            );

            Jaris\Uri::go(
                "admin/users/import",
                array("action"=>"setup")
            );
        }
        elseif(
            isset($_REQUEST["btnImport"])
            &&
            !Jaris\Forms::requiredFieldEmpty("users-importing-options")
        )
        {
            if(
                !in_array("email", $_REQUEST["column"])
            )
            {
                Jaris\View::addMessage(
                    t("Please match a column as the e-mail of the user."),
                    "error"
                );

                Jaris\Uri::go(
                    "admin/users/import",
                    array("action"=>"setup")
                );
            }
            else
            {
                //Disables execution time and enables unlimited
                //execution time
                set_time_limit(0);

                $csv = fopen($_SESSION["users_import"]["file"], "r");

                // Skip first row of labels
                $columns = fgetcsv(
                    $csv,
                    0,
                    $_SESSION["users_import"]["delimeter"],
                    $_SESSION["users_import"]["enclosure"],
                    $_SESSION["users_import"]["escape"]
                );

                //Store some default values to increase performance
                $default_group = !empty($_REQUEST["group"]) ?
                    $_REQUEST["group"]
                    :
                    "regular"
                ;

                $default_date = time();

                $images_path = trim($_REQUEST["images_path"]);

                $groups = Jaris\Groups::getList();

                if($images_path == "" || !is_dir($images_path))
                {
                    $images_path = false;
                }

                $imported_items = 0;
                $updated_items = 0;

                //Start reading the csv lines and creating pages
                while(
                    $columns = fgetcsv(
                        $csv,
                        0,
                        $_SESSION["users_import"]["delimeter"],
                        $_SESSION["users_import"]["enclosure"],
                        $_SESSION["users_import"]["escape"]
                    )
                )
                {
                    $username = "";
                    $email = "";
                    $group = $default_group;
                    $user_data = array();
                    $picture = array();

                    foreach($_REQUEST["column"] as $index=>$match)
                    {
                        switch($match)
                        {
                            case "username":
                                if(Jaris\Forms::validUsername($columns[$index]))
                                {
                                    $username = $columns[$index];
                                }
                                break;

                            case "email":
                                if(Jaris\Forms::validEmail($columns[$index], false))
                                {
                                    $email = $columns[$index];
                                }
                                break;

                            case "password":
                                if(!empty($columns[$index]))
                                {
                                    $user_data["password"] = $columns[$index];
                                }
                                break;

                            case "name":
                                if(
                                    !empty($user_data["name"])
                                    &&
                                    !empty($columns[$index])
                                )
                                {
                                    $user_data["name"] .= " "
                                        . $columns[$index]
                                    ;
                                }
                                elseif(!empty($columns[$index]))
                                {
                                    $user_data["name"] = $columns[$index];
                                }
                                break;

                            case "group":
                                if(
                                    !empty(!empty($columns[$index]))
                                    ||
                                    in_array(
                                        strtolower($columns[$index]),
                                        $groups
                                    )
                                    ||
                                    isset($groups[$columns[$index]])
                                )
                                {
                                    $group = $columns[$index];
                                }
                                break;

                            case "gender":
                                if(
                                    in_array(
                                        trim(strtolower($columns[$index])),
                                        array(
                                            "m",
                                            "male",
                                            "men",
                                            "man",
                                            "gentlemen"
                                        )
                                    )
                                )
                                {
                                    $user_data["gender"] = "m";
                                }
                                else
                                {
                                    $user_data["gender"] = "f";
                                }
                                break;

                            case "status":
                                if(
                                    in_array(
                                        trim(strtolower($columns[$index])),
                                        array(
                                            "0",
                                            "1",
                                            "2"
                                        )
                                    )
                                )
                                {
                                    $user_data["status"] = $columns[$index];
                                }
                                else
                                {
                                    $user_data["status"] = 1;
                                }
                                break;

                            case "birth_date":
                                if(is_int($columns[$index]))
                                {
                                    $user_data["birth_date"] = $columns[$index];
                                }
                                else
                                {
                                    $birth_date = strtotime($columns[$index]);
                                    if($birth_date)
                                    {
                                        $user_data["birth_date"] = $birth_date;
                                    }
                                    else
                                    {
                                        $user_data["birth_date"] = $default_date;
                                    }
                                }
                                break;

                            case "register_date":
                                if(is_int($columns[$index]))
                                {
                                    $user_data["register_date"] = $columns[$index];
                                }
                                else
                                {
                                    $date = strtotime($columns[$index]);
                                    if($date)
                                    {
                                        $user_data["register_date"] = $date;
                                    }
                                    else
                                    {
                                        $user_data["register_date"] = $default_date;
                                    }
                                }
                                break;

                            case "website":
                                $user_data["website"] = $columns[$index];
                                break;

                            case "personal_text":
                                $user_data["personal_text"] = Jaris\Util::stripHTMLTags(
                                    $columns[$index]
                                );
                                break;

                            case "image":
                                if(
                                    !empty($columns[$index])
                                    &&
                                    $images_path
                                    &&
                                    trim($columns[$index]) != ""
                                )
                                {
                                    Jaris\FileSystem::search(
                                        $images_path,
                                        "/{$columns[$index]}/i",
                                        function($file_full_path, &$stop_search)
                                            use($columns, $index, &$picture)
                                        {
                                            $picture = array(
                                                "name" => $columns[$index],
                                                "tmp_name" => $file_full_path,
                                                "type" => Jaris\FileSystem::getMimeTypeLocal($file_full_path)
                                            );

                                            $stop_search = true;
                                        }
                                    );
                                }
                                break;

                            case "image_url":
                                if(
                                    stristr($columns[$index], "http://") !== false
                                    ||
                                    stristr($columns[$index], "https://") !== false
                                )
                                {
                                    $image_content = file_get_contents(
                                        trim($columns[$index])
                                    );

                                    if($image_content !== false)
                                    {
                                        $image_name_parts = explode(
                                            "/",
                                            trim($columns[$image_index])
                                        );

                                        $image_name = Jaris\Users::generatePassword()
                                            . end(
                                                $image_name_parts
                                            )
                                        ;

                                        $image_path = Jaris\Site::dataDir()
                                            . $image_name
                                        ;

                                        file_put_contents(
                                            $images_path,
                                            $image_content
                                        );

                                        $picture = array(
                                            "name" => $image_name,
                                            "tmp_name" => $images_path,
                                            "type" => Jaris\FileSystem::getMimeTypeLocal(
                                                Jaris\Site::dataDir() . $image_name
                                            )
                                        );
                                    }
                                }
                                break;

                            case "custom":
                                $custom_column = trim(
                                    $_REQUEST["custom_column"][$index]
                                );

                                $user_data[$custom_column] = $columns[$index];
                                break;
                        }
                    }

                    if($email == "")
                    {
                        continue;
                    }

                    $user_exists = false;
                    $current_user_data = array();

                    if($username != "")
                    {
                        if($current_user_data = Jaris\Users::get($username))
                        {
                            $user_exists = true;
                        }
                    }
                    if(!$user_exists)
                    {
                        if($current_user_data = Jaris\Users::getByEmail($email))
                        {
                            $username = $current_user_data["username"];
                            $user_exists = true;
                        }
                    }

                    if($username == "")
                    {
                        // Generate a username with the given e-mail
                        $email_parts = explode("@", $email);
                        $username_original = $email_parts[0];
                        $username = $username_original;
                        $username_id = 1;

                        while(Jaris\Users::get($username))
                        {
                            $username = $username_original . $username_id;
                            $username_id++;
                        }
                    }

                    $user_data["email"] = $email;

                    if($user_exists)
                    {
                        $user_data += $current_user_data;

                        if(
                            Jaris\Users::edit(
                                $username, $group, $user_data, $picture
                            )
                            !=
                            "true"
                        )
                        {
                            Jaris\View::addMessage(
                                t("An error occured while trying to import the users, try again later."),
                                "error"
                            );

                            Jaris\Uri::go(
                                "admin/users/import",
                                array("action" => "setup")
                            );
                        }
                    }
                    else
                    {
                        if(empty($user_data["password"]))
                        {
                            $user_data["password"] = Jaris\Users::generatePassword();
                        }
                        if(empty($user_data["name"]))
                        {
                            $user_data["name"] = $username;
                        }
                        if(empty($_REQUEST["gender"]))
                        {
                            $user_data["gender"] = "m";
                        }
                        if(empty($_REQUEST["status"]))
                        {
                            $user_data["status"] = "1";
                        }
                        if(empty($_REQUEST["birth_date"]))
                        {
                            $user_data["birth_date"] = $default_date;
                        }
                        if(empty($_REQUEST["register_date"]))
                        {
                            $user_data["register_date"] = $default_date;
                        }
                        if(empty($_REQUEST["website"]))
                        {
                            $user_data["website"] = "";
                        }
                        if(empty($_REQUEST["personal_text"]))
                        {
                            $user_data["personal_text"] = "";
                        }

                        if(
                            Jaris\Users::add(
                                $username, $group, $user_data, $picture
                            )
                            !=
                            "true"
                        )
                        {
                            Jaris\View::addMessage(
                                t("An error occured while trying to import the users, try again later."),
                                "error"
                            );

                            Jaris\Uri::go(
                                "admin/users/import",
                                array("action" => "setup")
                            );
                        }
                    }
                }

                unlink($_SESSION["users_import"]["file"]);

                unset($_SESSION["users_import"]);

                Jaris\View::addMessage(
                    sprintf(t("Imported a total of %s users."), $imported_items)
                );

                Jaris\View::addMessage(
                    sprintf(t("Updated a total of %s users."), $updated_items)
                );

                Jaris\Uri::go("admin/users/list");
            }
        }
        elseif(isset($_REQUEST["btnImportCancel"]))
        {
            unlink($_SESSION["users_import"]["file"]);

            unset($_SESSION["users_import"]);

            Jaris\Uri::go("admin/users/import");
        }

        // Upload CSV form
        if(!isset($_REQUEST["action"]))
        {
            $parameters["name"] = "users-upload-csv";
            $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
            $parameters["method"] = "post";

            $text_fields[] = array(
                "type" => "text",
                "name" => "delimeter",
                "label" => t("Delimiter:"),
                "value" => ",",
                "required" => true,
                "description" => t("The character used to seperate fields on the csv file.")
            );

            $text_fields[] = array(
                "type" => "text",
                "name" => "enclosure",
                "label" => t("Enclosure:"),
                "value" => '"',
                "description" => t("The character used to enclose fields on the csv file.")
            );

            $text_fields[] = array(
                "type" => "text",
                "name" => "escape",
                "label" => t("Escape sequence:"),
                "value" => "\\",
                "description" => t("The character used to escape special characters like the delimeter and enclosure.")
            );

            $fieldset[] = array(
                "name" => t("CSV File Parsing Options"),
                "fields" => $text_fields,
                "collapsible" => true,
                "collapsed" => false
            );

            $fields[] = array(
                "type" => "file",
                "name" => "csv",
                "label" => t("Comma Seperated Values (CSV) file:"),
                "valid_types" => "csv",
                "required" => true
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnUpload",
                "value" => t("Proceed")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }


        // Importing options form
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "setup")
        {
            $parameters["name"] = "users-importing-options";
            $parameters["class"] = "users-importing-options";
            $parameters["action"] = Jaris\Uri::url("admin/users/import");
            $parameters["method"] = "post";

            $csv = fopen($_SESSION["users_import"]["file"], "r");

            // Generate a file content preview
            $field_preview[] = array(
                "type" => "other",
                "html_code" =>
                    '<div id="preview" style="overflow: scroll; height: 300px; width: 100%;">'
                    . '<table id="tbl-preview" style="display: none;" class="navigation-list">'
            );

            for($row=1; $row<=10; $row++)
            {
                if($row == 1)
                {
                    $field_preview[] = array(
                        "type" => "other",
                        "html_code" => '<thead>'
                    );
                }

                $field_preview[] = array(
                    "type" => "other",
                    "html_code" => '<tr style="overflow: scroll;">'
                );

                $columns = fgetcsv(
                    $csv,
                    0,
                    $_SESSION["users_import"]["delimeter"],
                    $_SESSION["users_import"]["enclosure"],
                    $_SESSION["users_import"]["escape"]
                );

                if($columns)
                {
                    // Limit the amount of columns to display to 7
                    $columns_count = count($columns);

                    for($pos=0; $pos<$columns_count; $pos++)
                    {
                        $field_preview[] = array(
                            "type" => "other",
                            "html_code" => "<td>"
                                . Jaris\Util::contentPreview($columns[$pos], 5, false)
                                . "</td>"
                        );
                    }
                }
                else
                {
                    break;
                }

                $field_preview[] = array(
                    "type" => "other",
                    "html_code" => '</tr>'
                );

                if($row == 1)
                {
                    $field_preview[] = array(
                        "type" => "other",
                        "html_code" => '</thead>'
                    );
                }
            }

            $field_preview[] = array(
                "type" => "other",
                "html_code" => '</table>'
                    . '</div>'
            );

            $fieldset[] = array(
                "name" => t("CSV file preview"),
                "fields" => $field_preview,
                "collapsible" => true,
                "collapsed" => false
            );

            // Column matching
            fseek($csv, 0);

            $columns = fgetcsv(
                $csv,
                0,
                $_SESSION["users_import"]["delimeter"],
                $_SESSION["users_import"]["enclosure"],
                $_SESSION["users_import"]["escape"]
            );

            fclose($csv);

            $column_fields = array();

            $field_types = array(
                t("None") => "none",
                t("Username") => "username",
                t("E-mail") => "email",
                t("Password") => "password",
                t("Name") => "name",
                t("Group") => "group",
                t("Name") => "name",
                t("Gender") => "gender",
                t("Status") => "status",
                t("Birthdate") => "birth_date",
                t("Registration date") => "register_date",
                t("Image") => "image",
                t("Image URL") => "image_url",
                t("Website") => "website",
                t("Personal Text") => "personal_text",
                t("Custom") => "custom"
            );

            foreach($columns as $column)
            {
                $column_fields[] = array(
                    "type" => "other",
                    "html_code" => '<div style="display: inline-block; margin-right: 15px;">'
                );

                $selected = "";
                $custom_value = "";

                if(stristr($column, "username") !== false)
                {
                    $selected = "username";
                }
                elseif(stristr($column, "email") !== false)
                {
                    $selected = "email";
                }
                elseif(stristr($column, "password") !== false)
                {
                    $selected = "password";
                }
                elseif(stristr($column, "gender") !== false)
                {
                    $selected = "gender";
                }
                elseif(stristr($column, "status") !== false)
                {
                    $selected = "status";
                }
                elseif(
                    stristr($column, "birth") !== false
                    &&
                    stristr($column, "date") !== false
                )
                {
                    $selected = "birth_date";
                }
                elseif(
                    (
                        stristr($column, "register") !== false
                        ||
                        stristr($column, "registration") !== false
                    )
                    &&
                    stristr($column, "date") !== false
                )
                {
                    $selected = "register_date";
                }
                elseif(stristr($column, "image") !== false)
                {
                    $selected = "image";
                }
                elseif(stristr($column, "website") !== false)
                {
                    $selected = "website";
                }
                elseif(
                    stristr($column, "personal") !== false
                    &&
                    stristr($column, "text") !== false
                )
                {
                    $selected = "personal_text";
                }
                else
                {
                    $selected = "custom";
                    $custom_value = $column;
                }

                $column_fields[] = array(
                    "type" => "select",
                    "name" => "column[]",
                    "label" => $column,
                    "value" => $field_types,
                    "selected" => $selected
                );

                $column_fields[] = array(
                    "type" => "text",
                    "name" => "custom_column[]",
                    "value" => $custom_value,
                    "label" => t("Custom field:")
                );

                $column_fields[] = array(
                    "type" => "other",
                    "html_code" => '</div>'
                );
            }

            $fieldset[] = array(
                "name" => t("Columns matching"),
                "fields" => $column_fields,
                "collapsible" => true,
                "collapsed" => false,
                "description" => t("Select how to match all the columns.")
            );

            $option_fields[] = array(
                "type" => "select",
                "name" => "group",
                "label" => t("Group:"),
                "value" => Jaris\Groups::getList(),
                "description" => t("Default group used when creating users from the csv file.")
            );

            $option_fields[] = array(
                "type" => "other",
                "html_code" => "<br />"
            );

            $option_fields[] = array(
                "type" => "text",
                "name" => "images_path",
                "label" => t("Images path:"),
                "description" => t("Relative path to directory which contains all images. Example: resources/images")
            );

            $fieldset[] = array(
                "name" => t("Import Options"),
                "fields" => $option_fields,
                "collapsible" => true,
                "collapsed" => false
            );

            $fields[] = array(
                "type" => "other",
                "html_code" => "<p>"
                    .t("Before proceeding to import please take into account that the process can take a huge amount of time.")
                    ."</p>"
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnImport",
                "value" => t("Import")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnImportCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
