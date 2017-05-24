<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the site search settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Search Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        //Get exsiting settings or defualt ones
        //if main settings table doesn't exist
        $site_settings = Jaris\Settings::getAll("main");

        if(isset($_REQUEST["btnSave"]))
        {
            //Check if write is possible and continue to write settings
            if(
                Jaris\Settings::save(
                    "search_display_category_titles",
                    $_REQUEST["search_display_category_titles"],
                    "main"
                )
            )
            {
                Jaris\Settings::save(
                    "search_display_images",
                    $_REQUEST["search_display_images"],
                    "main"
                );

                Jaris\Settings::save(
                    "search_images_width",
                    $_REQUEST["search_images_width"],
                    "main"
                );

                Jaris\Settings::save(
                    "search_images_height",
                    $_REQUEST["search_images_height"],
                    "main"
                );

                Jaris\Settings::save(
                    "search_images_aspect_ratio",
                    $_REQUEST["search_images_aspect_ratio"],
                    "main"
                );

                Jaris\Settings::save(
                    "search_images_background_color",
                    $_REQUEST["search_images_background_color"],
                    "main"
                );

                Jaris\Settings::save(
                    "search_images_types",
                    serialize($_REQUEST["types"]),
                    "main"
                );

                Jaris\Settings::save(
                    "search_results_not_found",
                    $_REQUEST["search_results_not_found"],
                    "main"
                );

                foreach(Jaris\Types::getList() as $machine_name => $data)
                {
                    Jaris\Settings::save(
                        "{$machine_name}_fields",
                        $_REQUEST["{$machine_name}_fields"],
                        "main"
                    );
                }

                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                t("Edited search settings.");

                Jaris\Logger::info("Edited search settings.");
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/settings/search");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings/search");
        }

        $parameters["name"] = "edit-search-settings";
        $parameters["class"] = "edit-search-settings";
        $parameters["action"] = Jaris\Uri::url("admin/settings/search");
        $parameters["method"] = "post";

        $display_category_titles[t("Enable")] = true;
        $display_category_titles[t("Disable")] = false;

        $category_fields[] = array(
            "type" => "radio",
            "name" => "search_display_category_titles",
            "id" => "search_display_category_titles",
            "value" => $display_category_titles,
            "checked" => isset($site_settings["search_display_category_titles"]) ?
                $site_settings["search_display_category_titles"] : false,
            "description" => t("Enables displaying the searched categories as the title of the search page.")
        );

        $fieldset[] = array(
            "name" => t("Display category titles?"),
            "fields" => $category_fields,
            "collapsible" => true
        );

        $display_images[t("Enable")] = true;
        $display_images[t("Disable")] = false;

        $image_fields[] = array(
            "type" => "radio",
            "name" => "search_display_images",
            "id" => "search_display_images",
            "value" => $display_images,
            "checked" => isset($site_settings["search_display_images"]) ?
                $site_settings["search_display_images"] : false
        );

        $image_fields[] = array(
            "type" => "text",
            "label" => t("Width:"),
            "name" => "search_images_width",
            "id" => "search_images_width",
            "value" => isset($site_settings["search_images_width"]) ?
                $site_settings["search_images_width"] : 60,
            "description" => t("The pixels width of the image displayed on search results. Default: 60px")
        );

        $image_fields[] = array(
            "type" => "text",
            "label" => t("Height:"),
            "name" => "search_images_height",
            "id" => "search_images_height",
            "value" => isset($site_settings["search_images_height"]) ?
                $site_settings["search_images_height"] : 60,
            "description" => t("The pixels height of the image displayed on search results. Default: 60px")
        );

        $image_fields[] = array(
            "type" => "other",
            "html_code" => "<br />"
        );

        $image_fields[] = array(
            "type" => "checkbox",
            "checked" => isset($site_settings["search_images_aspect_ratio"]) ?
                $site_settings["search_images_aspect_ratio"] : false,
            "label" => t("Keep aspect ratio?"),
            "name" => "search_images_aspect_ratio",
            "id" => "search_images_aspect_ratio"
        );

        $image_fields[] = array(
            "type" => "color",
            "label" => t("Background color:"),
            "name" => "search_images_background_color",
            "id" => "search_images_background_color",
            "value" => isset($site_settings["search_images_background_color"]) ?
                $site_settings["search_images_background_color"] : "",
            "description" => t("The background color of images when forced aspect ratio in html notation, example: d3d3d3.")
        );

        $fieldset[] = array(
            "name" => t("Display Images"),
            "fields" => $image_fields,
            "collapsible" => true
        );

        $fieldset[] = array(
            "name" => t("Types where displaying images"),
            "fields" => Jaris\Types::generateFields(
                unserialize(
                    isset($site_settings["search_images_types"]) ?
                        $site_settings["search_images_types"]
                        :
                        ""
                )
            ),
            "collapsible" => true
        );

        $type_fields = array();
        foreach(Jaris\Types::getList() as $machine_name => $data)
        {
            $type_fields[] = array(
                "type" => "textarea",
                "label" => t($data["name"]),
                "name" => "{$machine_name}_fields",
                "id" => "{$machine_name}_fields",
                "value" => isset($site_settings["{$machine_name}_fields"]) ?
                    $site_settings["{$machine_name}_fields"] : "content"
            );
        }

        $fieldset[] = array(
            "name" => t("Content type fields"),
            "fields" => $type_fields,
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("A list of field names in the format Label:field_name separated by comma for each content type that are displayed on search results. Example: Description:content, Page Views:views, etc.")
        );

        $fields[] = array(
            "type" => "textarea",
            "label" => t("Results not found"),
            "name" => "search_results_not_found",
            "value" => isset($site_settings["search_results_not_found"]) ?
                $site_settings["search_results_not_found"] : "",
            "description" => t("Optional content that is displayed when search results are not found, supports php code.")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
