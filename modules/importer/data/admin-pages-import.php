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
        <?php print t("Content Importer") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("import_content_importer"));

        if(
            isset($_REQUEST["btnUpload"]) &&
            !Jaris\Forms::requiredFieldEmpty("upload-csv-importer")
        )
        {
            $_SESSION["importer"] = array(
                "delimeter" => $_REQUEST["delimeter"],
                "enclosure" => $_REQUEST["enclosure"],
                "escape" => $_REQUEST["escape"],
                "file" => $_FILES["csv"]["tmp_name"]
            );

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/import", "importer"),
                array("action"=>"setup")
            );
        }
        elseif(
            isset($_REQUEST["btnImport"]) &&
            !Jaris\Forms::requiredFieldEmpty("importing-options-importer")
        )
        {
            if(!in_array("title", $_REQUEST["column"]))
            {
                Jaris\View::addMessage(
                    t("Please match a column as the title of the content."),
                    "error"
                );

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri("admin/pages/import", "importer"),
                    array("action"=>"setup")
                );
            }
            else
            {
                //Disables execution time and enables unlimited
                //execution time
                ini_set('max_execution_time', '0');

                $csv = fopen($_SESSION["importer"]["file"], "r");

                // Skip first row of labels
                $columns = fgetcsv(
                    $csv,
                    0,
                    $_SESSION["importer"]["delimeter"],
                    $_SESSION["importer"]["enclosure"],
                    $_SESSION["importer"]["escape"]
                );

                $update_only = false;

                if(in_array("uri", $columns))
                {
                    $update_only = true;
                }

                //Store some default values to increase performance
                $default_format = Jaris\Types::getDefaultInputFormat(
                    $_REQUEST["type"]
                );

                $author = Jaris\Authentication::currentUser();

                $categories = Jaris\Categories::getList($_REQUEST["type"]);

                $break_lines = $_REQUEST["newlines_to_brakes"] ? true : false;

                //Set images path and index
                $image_indexes = array_keys($_REQUEST["column"], "image");

                $images_path = trim($_REQUEST["images_path"]);

                if($images_path == "" || !is_dir($images_path))
                {
                    $images_path = false;
                }

                //Set files path and index
                $file_indexes = array_keys($_REQUEST["column"], "file");

                $files_path = trim($_REQUEST["files_path"]);

                if($files_path == "" || !is_dir($files_path))
                {
                    $files_path = false;
                }

                $imported_pages = 0;

                //Start reading the csv lines and creating pages
                while(
                    $columns = fgetcsv(
                        $csv,
                        0,
                        $_SESSION["importer"]["delimeter"],
                        $_SESSION["importer"]["enclosure"],
                        $_SESSION["importer"]["escape"]
                    )
                )
                {
                    $uri = "";

                    $page_data = array();

                    $page_data["users"] = array();
                    $page_data["groups"] = array();

                    $page_categories = array();

                    foreach($_REQUEST["column"] as $index=>$match)
                    {
                        switch($match)
                        {
                            case "uri":
                                $uri = $columns[$index];
                                break;

                            case "title":
                                $page_data["title"] = $columns[$index];
                                break;

                            case "content":

                                if($break_lines)
                                {
                                    $page_data["content"] = preg_replace(
                                        "/\n+/",
                                        "<br />",
                                        $columns[$index]
                                    );
                                }
                                else
                                {
                                    $page_data["content"] = $columns[$index];
                                }

                                break;

                            case "category":

                                foreach($categories as $machine_name => $values)
                                {
                                    $sub_categories = Jaris\Categories::getSubcategories(
                                        $machine_name
                                    );

                                    foreach($sub_categories as $sub_id=>$sub_data)
                                    {
                                        if(
                                            trim(strtolower($sub_data["title"]))
                                            ==
                                            trim(strtolower($columns[$index]))
                                        )
                                        {
                                            $page_categories[$machine_name][] = $sub_id;

                                            break 2;
                                        }
                                    }
                                }

                                break;

                            case "categories":
                                $categories_column = explode(",", $columns[$index]);

                                foreach($categories_column as $category_value)
                                {
                                    foreach($categories as $machine_name => $values)
                                    {
                                        $sub_categories = Jaris\Categories::getSubcategories(
                                            $machine_name
                                        );

                                        foreach($sub_categories as $sub_id=>$sub_data)
                                        {
                                            if(
                                                trim(strtolower($sub_data["title"]))
                                                ==
                                                trim(strtolower($category_value))
                                            )
                                            {
                                                $page_categories[$machine_name][] = $sub_id;

                                                break 2;
                                            }
                                        }
                                    }
                                }

                                break;

                            case "users":
                                $users_column = explode(",", $columns[$index]);

                                foreach($users_column as $user_value)
                                {
                                    if(trim($user_value) == "")
                                        continue;

                                    $page_data["users"][] = $user_value;
                                }

                                break;

                             case "groups":
                                $groups_column = explode(",", $columns[$index]);

                                foreach($groups_column as $group_value)
                                {
                                    if(trim($group_value) == "")
                                        continue;

                                    $page_data["groups"][] = $group_value;
                                }

                                break;

                            case "input_format":
                                if(trim($columns[$index]) != "")
                                    $page_data["input_format"] = $columns[$index];

                                break;

                            case "type":
                                if(trim($columns[$index]) != "")
                                    $page_data["type"] = $columns[$index];

                                break;

                            case "meta_title":
                                $page_data["meta_title"] = $columns[$index];
                                break;

                            case "meta_description":
                                $page_data["description"] = $columns[$index];
                                break;

                            case "meta_keywords":
                                $page_data["keywords"] = $columns[$index];
                                break;

                            case "custom":
                                $custom_column = trim(
                                    $_REQUEST["custom_column"][$index]
                                );

                                $page_data[$custom_column] = $columns[$index];
                                break;
                        }
                    }

                    $page_data["categories"] = $page_categories;

                    if(!isset($page_data["input_format"]))
                        $page_data["input_format"] = $default_format;

                    if(count($page_data["groups"]) < 1)
                            $page_data["groups"] = $_REQUEST["groups"];

                    if(!$update_only)
                    {
                        $page_data["created_date"] = time();
                        $page_data["author"] = $author;
                    }
                    else
                    {
                        $page_data["last_edit_date"] = time();
                        $page_data["last_edit_by"] = $author;
                    }

                    if(!isset($page_data["type"]))
                        $page_data["type"] = $_REQUEST["type"];


                    $page_uri = "";

                    $page_created_modified = false;

                    if(!$update_only)
                    {
                        $uri = Jaris\Types::generateURI(
                            $page_data["type"],
                            $page_data["title"],
                            $author
                        );

                        $page_created_modified = Jaris\Pages::add($uri, $page_data, $page_uri);
                    }
                    else
                    {
                        $page_uri = $uri;

                        $old_data = Jaris\Pages::get($uri);

                        $page_data = array_merge($old_data, $page_data);

                        $page_created_modified = Jaris\Pages::edit($uri, $page_data);
                    }

                    if($page_created_modified)
                    {
                        $imported_pages++;

                        //Add images
                        if(count($image_indexes) > 0)
                        {
                            if($images_path)
                            {
                                foreach($image_indexes as $image_index)
                                {
                                    if(trim($columns[$image_index]) == "")
                                    {
                                        continue;
                                    }

                                    Jaris\FileSystem::search(
                                        $images_path,
                                        "/{$columns[$image_index]}/i",
                                        function($file_full_path, &$stop_search)
                                            use($columns, $image_index, $page_uri)
                                        {
                                            $file = array(
                                                "name" => $columns[$image_index],
                                                "tmp_name" => $file_full_path,
                                                "type" => Jaris\FileSystem::getMimeTypeLocal($file_full_path)
                                            );

                                            $file_name = null;

                                            Jaris\Pages\Images::add(
                                                $file,
                                                "",
                                                $page_uri,
                                                $file_name,
                                                false
                                            );

                                            $stop_search = true;
                                        }
                                    );
                                }
                            }
                        }

                        //Add files
                        if(count($file_indexes) > 0)
                        {
                            if($files_path)
                            {
                                foreach($file_indexes as $file_index)
                                {
                                    if(trim($columns[$file_index]) == "")
                                    {
                                        continue;
                                    }

                                    Jaris\FileSystem::search(
                                        $files_path,
                                        "/{$columns[$file_index]}/i",
                                        function($file_full_path, &$stop_search)
                                            use($columns, $file_index, $page_uri)
                                        {
                                            $file = array(
                                                "name" => $columns[$file_index],
                                                "tmp_name" => $file_full_path,
                                                "type" => Jaris\FileSystem::getMimeTypeLocal($file_full_path)
                                            );

                                            $file_name = null;

                                            Jaris\Pages\Files::add(
                                                $file,
                                                "",
                                                $page_uri,
                                                $file_name,
                                                false
                                            );

                                            $stop_search = true;
                                        }
                                    );
                                }
                            }
                        }
                    }
                }

                unlink($_SESSION["importer"]["file"]);

                unset($_SESSION["importer"]);

                if(!$update_only)
                {
                    Jaris\View::addMessage(
                        sprintf(t("Imported a total of %s files."), $imported_pages)
                    );
                }
                else
                {
                    Jaris\View::addMessage(
                        sprintf(t("Updated a total of %s files."), $imported_pages)
                    );
                }

                Jaris\Uri::go("admin/pages/list");
            }
        }
        elseif(isset($_REQUEST["btnImportCancel"]))
        {
            unlink($_SESSION["importer"]["file"]);

            unset($_SESSION["importer"]);

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/import", "importer")
            );
        }

        // Upload CSV form
        if(!isset($_REQUEST["action"]))
        {
            $parameters["name"] = "upload-csv-importer";
            $parameters["class"] = "upload-csv-importer";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/pages/import", "importer")
            );
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
                "id" => "image",
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
            $parameters["name"] = "importing-options-importer";
            $parameters["class"] = "importing-options-importer";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/pages/import", "importer")
            );
            $parameters["method"] = "post";

            $csv = fopen($_SESSION["importer"]["file"], "r");

            // Generate a file content preview
            $field_preview[] = array(
                "type" => "other",
                "html_code" => '<table class="navigation-list">'
            );

            for($row=1; $row<=5; $row++)
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
                    "html_code" => '<tr>'
                );

                $columns = fgetcsv(
                    $csv,
                    0,
                    $_SESSION["importer"]["delimeter"],
                    $_SESSION["importer"]["enclosure"],
                    $_SESSION["importer"]["escape"]
                );

                if($columns)
                {
                    // Limit the amount of columns to display to 7
                    $columns_count = count($columns) > 7 ? 7 : count($columns);

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
                $_SESSION["importer"]["delimeter"],
                $_SESSION["importer"]["enclosure"],
                $_SESSION["importer"]["escape"]
            );

            $update_only = false;

            if(in_array("uri", $columns))
            {
                $update_only = true;

                Jaris\View::addMessage(
                    t("The uri column was detected, this import will only update existing content.")
                );
            }

            fclose($csv);

            $column_fields = array();

            $field_types = array(
                t("None") => "none",
                t("Title") => "title",
                t("Content") => "content",
                t("Category") => "category",
                t("Image") => "image",
                t("File") => "file",
                t("Meta Title") => "meta_title",
                t("Meta Description") => "meta_description",
                t("Meta Keywords") => "meta_keywords",
                t("Custom") => "custom"
            );

            if($update_only)
            {
                $field_types[t("Uri")] = "uri";
                $field_types[t("Users List")] = "users";
                $field_types[t("Groups List")] = "groups";
                $field_types[t("Categories List")] = "categories";
                $field_types[t("Input Format")] = "input_format";
                $field_types[t("Content Type")] = "type";
            }

            foreach($columns as $column)
            {
                $column_fields[] = array(
                    "type" => "other",
                    "html_code" => '<div style="display: inline-block; margin-right: 15px;">'
                );

                $selected = "";
                $custom_value = "";

                if($update_only)
                {
                    switch($column)
                    {
                        case "uri":
                            $selected = "uri";
                            break;
                        case "users":
                            $selected = "users";
                            break;
                        case "groups":
                            $selected = "groups";
                            break;
                        case "categories":
                            $selected = "categories";
                            break;
                        case "title":
                            $selected = "title";
                            break;
                        case "content":
                            $selected = "content";
                            break;
                        case "meta_title":
                            $selected = "meta_title";
                            break;
                        case "description":
                            $selected = "meta_description";
                            break;
                        case "keywords":
                            $selected = "meta_keywords";
                            break;
                        case "input_format":
                            $selected = "input_format";
                            break;
                        case "type":
                            $selected = "type";
                            break;
                        default:
                            $selected = "custom";
                            $custom_value = $column;
                            break;
                    }
                }
                else
                {
                    if(stristr($column, "meta_title") !== false)
                    {
                        $selected = "meta_title";
                    }
                    elseif(stristr($column, "title") !== false)
                    {
                        $selected = "title";
                    }
                    elseif(stristr($column, "content") !== false)
                    {
                        $selected = "content";
                    }
                    elseif(stristr($column, "category") !== false)
                    {
                        $selected = "category";
                    }
                    elseif(stristr($column, "image") !== false)
                    {
                        $selected = "image";
                    }
                    elseif(stristr($column, "file") !== false)
                    {
                        $selected = "file";
                    }
                    elseif(stristr($column, "description") !== false)
                    {
                        $selected = "meta_description";
                    }
                    elseif(stristr($column, "keywords") !== false)
                    {
                        $selected = "meta_keywords";
                    }
                    else
                    {
                        $selected = "custom";
                        $custom_value = $column;
                    }
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

            $types_list = Jaris\Types::getList();
            $types = array();

            foreach($types_list as $machine_name=>$type_data)
            {
                $types[t($type_data["name"])] = $machine_name;
            }

            $option_fields[] = array(
                "type" => "select",
                "name" => "type",
                "label" => t("Type:"),
                "value" => $types,
                "description" => t("Type used when creating pages from the csv file.")
            );

            $option_fields[] = array(
                "type" => "other",
                "html_code" => "<br />"
            );

            $option_fields[] = array(
                "type" => "checkbox",
                "name" => "newlines_to_brakes",
                "label" => t("Convert new lines to &lt;br&gt;?"),
                "description" => t("Enabling this option will convert new lines on the content field into html break tags.")
            );

            $option_fields[] = array(
                "type" => "text",
                "name" => "images_path",
                "label" => t("Images path:"),
                "description" => t("Relative path to directory which contains all images. Example: resources/images")
            );

            $option_fields[] = array(
                "type" => "text",
                "name" => "files_path",
                "label" => t("Files path:"),
                "description" => t("Relative path to directory which contains all files. Example: resources/files")
            );

            $fieldset[] = array(
                "name" => t("Import Options"),
                "fields" => $option_fields,
                "collapsible" => true,
                "collapsed" => false
            );

            $fieldset[] = array(
                "fields" => Jaris\Groups::generateFields(),
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true,
                "description" => t("Select the groups that can see this content. Don't select anything to display content to everyone.")
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
