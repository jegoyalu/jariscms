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
        <?php print t("Edit Gallery") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_content"]);

        if (
            !Jaris\Pages::userIsOwner(
                trim($_REQUEST["actual_uri"]) != "" ?
                    $_REQUEST["actual_uri"]
                    :
                    $_REQUEST["uri"]
            )
        ) {
            Jaris\Authentication::protectedPage();
        }

        if (isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("edit-gallery")) {
            //Check if client is trying to submit content to a
            //system page sending variables thru GET
            if (Jaris\Pages::isSystem($_REQUEST["actual_uri"])) {
                Jaris\View::addMessage(
                    t("The content you was trying to edit is a system page."),
                    "error"
                );

                Jaris\Uri::go("admin/pages");
            }

            $fields = Jaris\Pages::get($_REQUEST["actual_uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];
            $fields["gallery_sorting"] = $_REQUEST["gallery_sorting"];
            $fields["thumbnails_width"] = $_REQUEST["thumbnails_width"];
            $fields["thumbnails_height"] = $_REQUEST["thumbnails_height"];
            $fields["background_color"] = $_REQUEST["background_color"];
            $fields["images_per_page"] = $_REQUEST["images_per_page"];
            $fields["images_per_row"] = $_REQUEST["images_per_row"];
            $fields["aspect_ratio"] = $_REQUEST["aspect_ratio"];
            $fields["show_title"] = $_REQUEST["show_title"];
            $fields["title_position"] = $_REQUEST["title_position"];

            if (Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup())) {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            if (Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup())) {
                $fields["groups"] = $_REQUEST["groups"];

                $users = explode(",", $_REQUEST["users"]);

                if (count($users) > 0) {
                    foreach ($users as $user_position=>$username) {
                        $users[$user_position] = trim($username);
                    }
                }

                $fields["users"] = $users;
            }

            $categories = [];
            $categories_list = Jaris\Categories::getList("gallery");

            if ($categories_list) {
                foreach ($categories_list as $machine_name => $values) {
                    if (isset($_REQUEST[$machine_name])) {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }

            $fields["categories"] = $categories;

            if (
                Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
                Jaris\Authentication::isAdminLogged()
            ) {
                $fields["input_format"] = $_REQUEST["input_format"];
            }

            $fields["last_edit_by"] = Jaris\Authentication::currentUser();
            $fields["last_edit_date"] = time();

            Jaris\Fields::appendFields($fields["type"], $fields);

            if (
                !Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()) ||
                $_REQUEST["uri"] == ""
            ) {
                $_REQUEST["uri"] = Jaris\Types::generateURI(
                    $fields["type"],
                    $fields["title"],
                    $fields["author"]
                );
            }

            if (Jaris\Pages::edit($_REQUEST["actual_uri"], $fields)) {
                //Update all translations
                $new_page_data = Jaris\Pages::get($_REQUEST["actual_uri"]);
                foreach (Jaris\Language::getInstalled() as $code => $name) {
                    $translation_path = Jaris\Language::dataTranslate(
                        Jaris\Pages::getPath($_REQUEST["actual_uri"]),
                        $code
                    );

                    $original_path = Jaris\Pages::getPath($_REQUEST["actual_uri"]);

                    if ($translation_path != $original_path) {
                        $translation_data = Jaris\Pages::get(
                            $_REQUEST["actual_uri"],
                            $code
                        );

                        $new_page_data["title"] = $translation_data["title"];
                        $new_page_data["content"] = $translation_data["content"];

                        Jaris\Translate::page(
                            $_REQUEST["actual_uri"],
                            $new_page_data,
                            $code
                        );
                    }
                }

                //Move page to new location
                if ($_REQUEST["actual_uri"] != $_REQUEST["uri"]) {
                    Jaris\Pages::move($_REQUEST["actual_uri"], $_REQUEST["uri"]);

                    //Also move its translations on the language directory
                    if (Jaris\Translate::movePage($_REQUEST["actual_uri"], $_REQUEST["uri"])) {
                        Jaris\View::addMessage(t("Translations repositioned."));
                    } else {
                        Jaris\View::addMessage(Jaris\System::errorMessage("translations_not_moved"), "error");
                    }
                }

                Jaris\View::addMessage(t("Your changes have been successfully saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/gallery/edit", "gallery"),
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/gallery/edit", "gallery"),
                ["uri" => $_REQUEST["actual_uri"]]
            );
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        //Tabs
        if (Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(
                t("Edit Gallery"),
                Jaris\Modules::getPageUri("admin/pages/gallery/edit", "gallery"),
                $arguments
            );
        }
        Jaris\View::addTab(t("View Gallery"), $_REQUEST["uri"]);
        if (Jaris\Authentication::groupHasPermission("view_content_blocks", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_images", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_files", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("translate_languages", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $page_data = Jaris\Pages::get($_REQUEST["uri"]);

        $parameters["name"] = "edit-gallery";
        $parameters["class"] = "edit-gallery";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/gallery/edit", "gallery")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList("gallery");

        if ($categories) {
            $fields_categories = Jaris\Categories::generateFields(
                $page_data["categories"],
                "",
                "gallery"
            );

            $fieldset[] = [
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            ];
        }

        $fields[] = [
            "type" => "hidden",
            "name" => "actual_uri",
            "value" => $_REQUEST["actual_uri"] ?
                $_REQUEST["actual_uri"]
                :
                $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "text",
            "value" => $page_data["title"],
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        ];

        $fields[] = [
            "type" => "textarea",
            "value" => $page_data["content"],
            "name" => "content",
            "label" => t("Content:"),
            "id" => "content"
        ];

        $sorting[t("Old first")] = "asc";
        $sorting[t("Newest first")] = "desc";

        $fields[] = [
            "type" => "radio",
            "value" => $sorting,
            "checked" => $page_data["gallery_sorting"],
            "label" => t("Sorting:"),
            "name" => "gallery_sorting"
        ];

        $fields[] = [
            "type" => "text",
            "name" => "thumbnails_width",
            "value" => $page_data["thumbnails_width"],
            "label" => t("Thumbnails width:"),
            "id" => "thumbnails_width",
            "required" => true,
            "description" => t("The width of the thumbnail in pixels.")
        ];

        $fields[] = [
            "type" => "text",
            "name" => "thumbnails_height",
            "value" => $page_data["thumbnails_height"],
            "label" => t("Thumbnails height:"),
            "id" => "thumbnails_height",
            "description" => t("The height of the image in pixels.")
        ];

        $fields[] = [
            "type" => "color",
            "name" => "background_color",
            "value" => $_REQUEST["background_color"] ?
                $_REQUEST["background_color"]
                :
                $page_data["background_color"],
            "label" => t("Background color:"),
            "id" => "background_color"
        ];

        $fields[] = [
            "type" => "text",
            "name" => "images_per_page",
            "value" => $page_data["images_per_page"],
            "label" => t("Images per page:"),
            "id" => "title",
            "required" => true
        ];

        $fields[] = [
            "type" => "text",
            "name" => "images_per_row",
            "value" => $page_data["images_per_row"],
            "label" => t("Images per row:"),
            "id" => "title",
            "required" => true
        ];

        $fields[] = ["type" => "other", "html_code" => "<br />"];

        $fields[] = [
            "type" => "checkbox",
            "checked" => $_REQUEST["aspect_ratio"] ?
                $_REQUEST["aspect_ratio"]
                :
                $page_data["aspect_ratio"],
            "label" => t("Keep aspect ratio?"),
            "name" => "aspect_ratio",
            "id" => "aspect_ratio"
        ];

        $fieldset[] = ["fields" => $fields];

        $fields_image_title[] = [
            "type" => "other",
            "html_code" => "<br />"
        ];

        $fields_image_title[] = [
            "type" => "checkbox",
            "checked" => $page_data["show_title"],
            "label" => t("Show image title?"),
            "name" => "show_title",
            "id" => "show_title"
        ];

        $positions[t("Top")] = "top";
        $positions[t("Bottom")] = "bottom";

        $fields_image_title[] = [
            "type" => "radio",
            "value" => $positions,
            "checked" => $page_data["title_position"],
            "label" => t("Position:"),
            "name" => "title_position",
            "id" => "title_position"
        ];

        $fieldset[] = [
            "fields" => $fields_image_title,
            "name" => t("Image title"),
            "collapsible" => true,
            "collapsed" => true
        ];

        if (Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup())) {
            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["meta_title"],
                "name" => "meta_title",
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            ];

            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["description"],
                "name" => "description",
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            ];

            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["keywords"],
                "name" => "keywords",
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            ];

            $fieldset[] = [
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            ];
        }

        if (
            Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
            Jaris\Authentication::isAdminLogged()
        ) {
            $fields_inputformats = [];

            foreach (Jaris\InputFormats::getAll() as $machine_name => $fields_formats) {
                $fields_inputformats[] = [
                    "type" => "radio",
                    "checked" => $machine_name == $page_data["input_format"] ?
                        true
                        :
                        false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => [$fields_formats["title"] => $machine_name]
                ];
            }

            $fieldset[] = [
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            ];
        }

        $extra_fields = Jaris\Fields::generateFields("gallery", $page_data);

        if ($extra_fields) {
            $fieldset[] = ["fields" => $extra_fields];
        }

        if (Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup())) {
            $fields_users_access[] = [
                "type" => "other",
                "html_code" => "<h4>"
                    . t("Select the groups that can see this content. Don't select anything to display content to everyone.")
                    . "</h4>"
            ];

            $fields_users_access = array_merge(
                $fields_users_access,
                Jaris\Groups::generateFields($page_data["groups"])
            );

            $fields_users_access[] = [
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => implode(", ", $page_data["users"]),
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            ];

            $fieldset[] = [
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            ];
        }

        if (Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup())) {
            $fields_other[] = [
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => $_REQUEST["uri"],
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
            ];
        }

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_other];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;