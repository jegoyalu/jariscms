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
        <?php print t("Content Exporter") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["export_content_importer"]);

        if (
            isset($_REQUEST["btnExport"]) &&
            !Jaris\Forms::requiredFieldEmpty("exporting-options-importer")
        ) {
            //Disables execution time and enables unlimited
            //execution time
            set_time_limit(0);

            $db = Jaris\Sql::open("search_engine");

            $type = trim(str_replace("'", "''", $_REQUEST["type"]));

            $result = Jaris\Sql::query(
                "select * from uris where type='$type'",
                $db
            );

            $csv_filename = $_REQUEST["type"] . "-" . $_REQUEST["language_code"]
                . "-" . date("d-m-Y", time()) . ".csv"
            ;

            $csv = fopen(
                Jaris\Users::getUploadsPath(Jaris\Authentication::currentUser()) . $csv_filename,
                "w"
            );

            $delimeter = $_REQUEST["delimeter"];
            $enclosure = $_REQUEST["enclosure"];

            $exported_pages = 0;
            $header_row = false;
            $column_names = [];

            $categories = Jaris\Categories::getList($_REQUEST["type"]);
            $type_fields = Jaris\Fields::getList($_REQUEST["type"]);

            while ($data = Jaris\Sql::fetchArray($result)) {
                $page_data = [];
                if ($_REQUEST["language_code"] != "en") {
                    $page_data = Jaris\Pages::get(
                        $data["uri"],
                        $_REQUEST["language_code"]
                    );
                }

                $columns = unserialize($data["data"]);

                foreach ($type_fields as $field_id => $field_data) {
                    if (!isset($columns[$field_data["variable_name"]])) {
                        $columns[$field_data["variable_name"]] = "";
                    }
                }

                if (!$header_row) {
                    $row = [];
                    $row[] = "uri";

                    foreach ($columns as $name=>$value) {
                        if ($name == "views") {
                            continue;
                        } elseif ($name == "last_edit_by") {
                            continue;
                        } elseif ($name == "last_edit_date") {
                            continue;
                        }

                        $row[] = $name;
                        $column_names[] = $name;
                    }

                    fputcsv($csv, $row, $delimeter, $enclosure);

                    $header_row = true;
                }

                $row = [];
                $row[] = $data["uri"];

                foreach ($column_names as $name) {
                    $value = $columns[$name];

                    if ($name == "views") {
                        continue;
                    } elseif ($name == "last_edit_by") {
                        continue;
                    } elseif ($name == "last_edit_date") {
                        continue;
                    } elseif ($name == "users" || $name == "groups") {
                        if (is_array($value)) {
                            $value = implode(",", $value);
                        }
                    } elseif ($name == "categories") {
                        if (is_array($value)) {
                            $categories_list = [];

                            foreach ($value as $machine_name => $values) {
                                $sub_categories = Jaris\Categories::getSubcategories(
                                    $machine_name
                                );

                                foreach ($values as $value_id) {
                                    foreach ($sub_categories as $sub_id=>$sub_data) {
                                        if ($value_id == $sub_id) {
                                            $categories_list[] = $sub_data["title"];

                                            break;
                                        }
                                    }
                                }
                            }

                            $value = implode(",", $categories_list);
                        }
                    } elseif ($_REQUEST["language_code"] != "en") {
                        if ($name == "title") {
                            $value = $page_data["title"];
                        } elseif ($name == "content") {
                            if ($_REQUEST["strip_html"]) {
                                $value = preg_replace(
                                    "/(\n|\r\n|\n\n)+/",
                                    "\n",
                                    Jaris\Util::stripHTMLTags(
                                        $page_data["content"]
                                    )
                                );
                            } else {
                                $value = $page_data["content"];
                            }
                        }
                    } elseif ($name == "content" && $_REQUEST["strip_html"]) {
                        $value = preg_replace(
                            "/(\n|\r\n|\n\n)+/",
                            "\n",
                            Jaris\Util::stripHTMLTags(
                                $value
                            )
                        );
                    }

                    $row[] = $value;
                }

                fputcsv($csv, $row, $delimeter, $enclosure);

                $exported_pages++;
            }

            Jaris\View::addMessage(
                sprintf(t("Exported a total of %s files."), $exported_pages)
            );

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/export", "importer"),
                ["action"=>"view-download", "file"=>$csv_filename]
            );
        }

        // Export options form
        if (!isset($_REQUEST["action"])) {
            Jaris\Forms::deleteUploads();

            $parameters["name"] = "exporting-options-importer";
            $parameters["class"] = "exporting-options-importer";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/pages/export", "importer")
            );
            $parameters["method"] = "post";

            $text_fields[] = [
                "type" => "text",
                "name" => "delimeter",
                "label" => t("Delimiter:"),
                "value" => ",",
                "required" => true,
                "description" => t("The character used to seperate fields on the csv file.")
            ];

            $text_fields[] = [
                "type" => "text",
                "name" => "enclosure",
                "label" => t("Enclosure:"),
                "value" => '"',
                "description" => t("The character used to enclose fields on the csv file.")
            ];

            $fieldset[] = [
                "name" => t("CSV File Options"),
                "fields" => $text_fields,
                "collapsible" => true,
                "collapsed" => true
            ];

            $types_list = Jaris\Types::getList();
            $types = [];

            foreach ($types_list as $machine_name=>$type_data) {
                $types[t($type_data["name"])] = $machine_name;
            }

            $languages = array_flip(Jaris\Language::getInstalled());

            $option_fields[] = [
                "type" => "select",
                "name" => "language_code",
                "label" => t("Language:"),
                "value" => $languages,
                "selected" => "en",
                "description" => t("In which language to export the content.")
            ];

            $option_fields[] = [
                "type" => "select",
                "name" => "type",
                "label" => t("Type:"),
                "value" => $types,
                "description" => t("Type of content to export into the csv file.")
            ];

            $option_fields[] = [
                "type" => "other",
                "html_code" => "<br />"
            ];

            $option_fields[] = [
                "type" => "checkbox",
                "name" => "strip_html",
                "label" => t("Remove html code from the content?"),
                "description" => t("Enabling this option will remove all html tags and styling of the content, leaving only a plain text.")
            ];

            /*$option_fields[] = array(
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
            );*/

            $fieldset[] = [
                "name" => t("Export Options"),
                "fields" => $option_fields,
                "collapsible" => true,
                "collapsed" => false
            ];

            $fields[] = [
                "type" => "other",
                "html_code" => "<p>"
                    .t("Before proceeding to export please take into account that the process can take a huge amount of time.")
                    ."</p>"
            ];

            $fields[] = [
                "type" => "submit",
                "name" => "btnExport",
                "value" => t("Export")
            ];

            $fieldset[] = ["fields" => $fields];

            print Jaris\Forms::generate($parameters, $fieldset);
        }

        // Display the download link.
        if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "view-download") {
            $url = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/pages/export", "importer"),
                ["action"=>"download", "file"=>$_REQUEST["file"]]
            );

            print '<a href="'.$url.'">'
                . t('Download') . " - " . $_REQUEST["file"]
                . '</a>'
            ;
        }

        // Start download
        if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "download") {
            Jaris\FileSystem::printFile(
                Jaris\Users::getUploadsPath(Jaris\Authentication::currentUser()) . $_REQUEST["file"],
                $_REQUEST["file"],
                true
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
