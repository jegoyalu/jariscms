<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content block post settings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Blocks Post Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["edit_post_settings_content_blocks"]
        );

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $page_uri = $_REQUEST["uri"];
        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);

        Jaris\View::addTab(t("Create Block"), "admin/pages/blocks/add", $arguments, 1);
        Jaris\View::addTab(t("Create Post Block"), "admin/pages/blocks/add/post", $arguments, 1);
        Jaris\View::addTab(t("Post Settings"), "admin/pages/blocks/post/settings", $arguments, 1);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-block-post-settings")
        ) {
            $fields["display_title"] = $_REQUEST["display_title"];
            $fields["display_image"] = $_REQUEST["display_image"];
            $fields["thumbnail_width"] = $_REQUEST["thumbnail_width"];
            $fields["thumbnail_height"] = $_REQUEST["thumbnail_height"];
            $fields["thumbnail_background_color"] = $_REQUEST["thumbnail_background_color"];
            $fields["keep_aspect_ratio"] = $_REQUEST["keep_aspect_ratio"];
            $fields["maximum_words"] = $_REQUEST["maximum_words"];
            $fields["display_view_more"] = $_REQUEST["display_view_more"];

            //Check if write is possible and continue to write settings
            if (Jaris\Blocks::setPostSettings($fields, $_REQUEST["uri"])) {
                Jaris\View::addMessage(t("Post settings successfully saved."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/pages/blocks",
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                "admin/pages/blocks",
                ["uri" => $_REQUEST["uri"]]
            );
        }

        $settings = Jaris\Blocks::getPostSettings($_REQUEST["uri"]);

        $parameters["name"] = "edit-block-post-settings";
        $parameters["class"] = "edit-block-post-settings";
        $parameters["action"] = Jaris\Uri::url("admin/pages/blocks/post/settings");
        $parameters["method"] = "post";

        $enable_disable[t("Enable")] = true;
        $enable_disable[t("Disable")] = false;

        $display_title_fields[] = [
            "type" => "radio",
            "name" => "display_title",
            "id" => "display_title",
            "value" => $enable_disable,
            "checked" => $settings["display_title"]
        ];

        $fieldset[] = [
            "name" => t("Display post title"),
            "fields" => $display_title_fields,
            "collapsible" => true,
            "collapsed" => false,
            "description" => t("Overrides the block title with the title of the page from the given uri.")
        ];

        $display_image_fields[] = [
            "type" => "radio",
            "name" => "display_image",
            "id" => "display_image",
            "value" => $enable_disable,
            "checked" => $settings["display_image"]
        ];

        $display_image_fields[] = [
            "type" => "text",
            "name" => "thumbnail_width",
            "label" => t("Thumbnail width:"),
            "id" => "thumbnail_width",
            "value" => $settings["thumbnail_width"],
            "required" => true,
            "description" => t("The maximum width of the image thumbnail in pixels.")
        ];

        $display_image_fields[] = [
            "type" => "text",
            "name" => "thumbnail_height",
            "label" => t("Thumbnail height:"),
            "id" => "thumbnail_height",
            "value" => $settings["thumbnail_height"],
            "description" => t("The maximum height of the image thumbnail in pixels.")
        ];

        $display_image_fields[] = [
            "type" => "color",
            "name" => "thumbnail_background_color",
            "label" => t("Background color:"),
            "id" => "thumbnail_background_color",
            "value" => $settings["thumbnail_background_color"],
            "description" => t("The background color of the thumbnail in case is neccesary.")
        ];

        $display_image_fields[] = [
            "type" => "other",
            "html_code" => "<br />"
        ];

        $display_image_fields[] = [
            "type" => "checkbox",
            "label" => t("Keep aspect ratio?"),
            "name" => "keep_aspect_ratio",
            "id" => "keep_aspect_ratio",
            "checked" => $settings["keep_aspect_ratio"]
        ];

        $fieldset[] = [
            "name" => t("Display image thumbnail"),
            "fields" => $display_image_fields,
            "collapsible" => true,
            "collapsed" => false
        ];

        $display_link_fields[] = [
            "type" => "radio",
            "name" => "display_view_more",
            "id" => "display_view_more",
            "value" => $enable_disable,
            "checked" => $settings["display_view_more"]
        ];

        $fieldset[] = [
            "name" => t("Display view more link"),
            "fields" => $display_link_fields,
            "collapsible" => true,
            "collapsed" => false
        ];

        $fields[] = [
            "type" => "text",
            "name" => "maximum_words",
            "id" => "maximum_words",
            "label" => t("Maximun amount of words:"),
            "value" => $settings["maximum_words"],
            "required" => true,
            "description" => t("Amount of words displayed of the page summary.")
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
