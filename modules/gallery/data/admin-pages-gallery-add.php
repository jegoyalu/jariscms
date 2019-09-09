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
        <?php print t("Create Gallery") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_content"));

        if(!Jaris\Authentication::hasTypeAccess("gallery", Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser()))
        {
            Jaris\Authentication::protectedPage();
        }

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("add-gallery"))
        {
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

            if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
            {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
            {
                $fields["groups"] = $_REQUEST["groups"];

                $users = explode(",", $_REQUEST["users"]);

                if(count($users) > 0)
                {
                    foreach($users as $user_position=>$username)
                    {
                        $users[$user_position] = trim($username);
                    }
                }

                $fields["users"] = $users;
            }
            else
            {
                $fields["groups"] = array();
                $fields["user"] = array();
            }

            $categories = array();
            $categories_list = Jaris\Categories::getList("gallery");

            if($categories_list)
            {
                foreach($categories_list as $machine_name => $values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }

            $fields["categories"] = $categories;

            if(
                Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
                Jaris\Authentication::isAdminLogged()
            )
            {
                $fields["input_format"] = $_REQUEST["input_format"];
            }
            else
            {
                $fields["input_format"] = Jaris\Types::getDefaultInputFormat("gallery");
            }

            $fields["created_date"] = time();
            $fields["author"] = Jaris\Authentication::currentUser();
            $fields["type"] = "gallery";

            Jaris\Fields::appendFields($fields["type"], $fields);

            //Stores the uri of the page to display the edit page after saving.
            $uri = "";

            if(
                !Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()) ||
                $_REQUEST["uri"] == ""
            )
            {
                $_REQUEST["uri"] = Jaris\Types::generateURI($fields["type"], $fields["title"], $fields["author"]);
            }

            if(Jaris\Pages::add($_REQUEST["uri"], $fields, $uri))
            {
                Jaris\View::addMessage(t("The gallery was successfully created."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/gallery/edit", "gallery"),
                array("uri" => $uri)
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        $parameters["name"] = "add-gallery";
        $parameters["class"] = "add-gallery";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/gallery/add", "gallery")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList("gallery");

        if($categories)
        {
            $fields_categories = Jaris\Categories::generateFields(
                [], "", "gallery"
            );

            $fieldset[] = array(
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            );
        }

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "value" => $_REQUEST["title"],
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => $_REQUEST["content"],
            "label" => t("Content:"),
            "id" => "content"
        );

        $sorting[t("Old first")] = "asc";
        $sorting[t("Newest first")] = "desc";

        $fields[] = array(
            "type" => "radio",
            "value" => $sorting,
            "checked" => $_REQUEST["gallery_sorting"],
            "label" => t("Sorting:"),
            "name" => "gallery_sorting"
        );

        $fields[] = array(
            "type" => "text",
            "name" => "thumbnails_width",
            "value" => $_REQUEST["thumbnails_width"] ?
                $_REQUEST["thumbnails_width"]
                :
                100,
            "label" => t("Thumbnails width:"),
            "id" => "thumbnails_width",
            "required" => true,
            "description" => t("The width of the thumbnail in pixels.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "thumbnails_height",
            "value" => $_REQUEST["thumbnails_height"] ?
                $_REQUEST["thumbnails_height"]
                :
                75,
            "label" => t("Thumbnails height:"),
            "id" => "thumbnails_height",
            "description" => t("The height of the image in pixels.")
        );

        $fields[] = array(
            "type" => "color",
            "name" => "background_color",
            "value" => $_REQUEST["background_color"],
            "label" => t("Background color:"),
            "id" => "background_color"
        );

        $fields[] = array(
            "type" => "text",
            "name" => "images_per_page",
            "value" => $_REQUEST["images_per_page"] ?
                $_REQUEST["images_per_page"]
                :
                9,
            "label" => t("Images per page:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "images_per_row",
            "value" => $_REQUEST["images_per_row"] ?
                $_REQUEST["images_per_row"]
                :
                3,
            "label" => t("Images per row:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array("type" => "other", "html_code" => "<br />");

        $fields[] = array(
            "type" => "checkbox",
            "checked" => $_REQUEST["aspect_ratio"],
            "label" => t("Keep aspect ratio?"),
            "name" => "aspect_ratio",
            "id" => "aspect_ratio"
        );

        $fieldset[] = array("fields" => $fields);

        $fields_image_title[] = array(
            "type" => "other",
            "html_code" => "<br />"
        );

        $fields_image_title[] = array(
            "type" => "checkbox",
            "checked" => $_REQUEST["show_title"],
            "label" => t("Show image title?"),
            "name" => "show_title",
            "id" => "show_title"
        );

        $positions[t("Top")] = "top";
        $positions[t("Bottom")] = "bottom";

        $fields_image_title[] = array(
            "type" => "radio",
            "value" => $positions,
            "checked" => $_REQUEST["title_position"],
            "label" => t("Position:"),
            "name" => "title_position",
            "id" => "title_position"
        );

        $fieldset[] = array(
            "fields" => $fields_image_title,
            "name" => t("Image title"),
            "collapsible" => true,
            "collapsed" => true
        );

        if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "meta_title",
                "value" => $_REQUEST["meta_title"],
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "description",
                "value" => $_REQUEST["description"],
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "keywords",
                "value" => $_REQUEST["keywords"],
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            );

            $fieldset[] = array(
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            );
        }

        if(
            Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {
                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == "full_html" ?
                        true
                        :
                        false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => array($fields_formats["title"] => $machine_name)
                );
            }

            $fieldset[] = array(
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            );
        }

        $extra_fields = Jaris\Fields::generateFields("gallery");

        if($extra_fields)
        {
            $fieldset[] = array("fields" => $extra_fields);
        }

        if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
        {
            $fields_users_access[] = array(
                "type" => "other",
                "html_code" => "<h4>"
                    . t("Select the groups that can see this content. Don't select anything to display content to everyone.")
                    . "</h4>"
            );

            $fields_users_access = array_merge(
                $fields_users_access,
                Jaris\Groups::generateFields()
            );

            $fields_users_access[] = array(
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => $_REQUEST["users"],
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            );

            $fieldset[] = array(
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            );
        }

        if(Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_other[] = array(
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => $_REQUEST["uri"],
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
            );
        }

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;