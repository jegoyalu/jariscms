<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        if(!isset($_REQUEST["type"]))
        {
            $_REQUEST["type"] = "pages";
        }

        $type_data = Jaris\Types::get($_REQUEST["type"]);

        print t("Add") . " " . t($type_data["name"]);
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_content"));

        if(!isset($_REQUEST["type"]))
        {
            $_REQUEST["type"] = "pages";
        }

        if(
            !Jaris\Authentication::hasTypeAccess(
                $_REQUEST["type"],
                Jaris\Authentication::currentUserGroup(),
                Jaris\Authentication::currentUser()
            )
        )
        {
            Jaris\Authentication::protectedPage();
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-page-{$_REQUEST['type']}") &&
            Jaris\Authentication::hasTypeAccess(
                $_REQUEST["type"],
                Jaris\Authentication::currentUserGroup(),
                Jaris\Authentication::currentUser()
            ) &&
            Jaris\Fields::validUploads($_REQUEST["type"])
        )
        {
            //Trim uri spaces
            $_REQUEST["uri"] = trim($_REQUEST["uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];

            if(
                Jaris\Authentication::groupHasPermission(
                    "add_edit_meta_content",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            if(
                Jaris\Authentication::groupHasPermission(
                    "select_content_groups",
                    Jaris\Authentication::currentUserGroup()
                )
            )
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
                $fields["users"] = array();
            }

            $categories = array();
            $categories_list = Jaris\Categories::getList($_REQUEST["type"]);

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
                Jaris\Authentication::groupHasPermission(
                    "input_format_content",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged()
            )
            {
                $fields["input_format"] = $_REQUEST["input_format"];
            }
            else
            {
                $fields["input_format"] = Jaris\Types::getDefaultInputFormat(
                    $_REQUEST["type"]
                );
            }

            $fields["created_date"] = time();
            $fields["author"] = Jaris\Authentication::currentUser();
            $fields["type"] = $_REQUEST["type"];

            Jaris\Fields::appendFields($fields["type"], $fields);

            //Stores the uri of the page to display the edit page after saving.
            $uri = "";

            if(
                !Jaris\Authentication::groupHasPermission(
                    "manual_uri_content",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                $_REQUEST["uri"] == ""
            )
            {
                $_REQUEST["uri"] = Jaris\Types::generateURI(
                    $fields["type"],
                    $fields["title"],
                    $fields["author"]
                );
            }

            if(Jaris\Pages::add($_REQUEST["uri"], $fields, $uri))
            {
                Jaris\Fields::saveUploads($fields["type"], $uri);

                Jaris\View::addMessage(t("The page was successfully created."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            if(
                Jaris\Authentication::groupHasPermission(
                    "edit_content",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                Jaris\Uri::go("admin/pages/edit", array("uri" => $uri));
            }
            else
            {
                Jaris\Uri::go($uri);
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            if(
                Jaris\Authentication::groupHasPermission(
                    "view_content",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                Jaris\Uri::go("admin/pages");
            }
            else
            {
                Jaris\Uri::go("admin/pages/types");
            }
        }
        elseif(
            !Jaris\Authentication::hasTypeAccess(
                $_REQUEST["type"],
                Jaris\Authentication::currentUserGroup(),
                Jaris\Authentication::currentUser()
            )
        )
        {
            Jaris\View::addMessage(
                t("You do not have permissions to add content of that type."),
                "error"
            );
        }

        $parameters["name"] = "add-page-{$_REQUEST['type']}";
        $parameters["class"] = "add-page-{$_REQUEST['type']}";
        $parameters["action"] = Jaris\Uri::url("admin/pages/add");
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList($_REQUEST["type"]);

        if($categories)
        {

            $fields_categories = Jaris\Categories::generateFields(
                null,
                null,
                $_REQUEST["type"]
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
            "value" => isset($_REQUEST["title"]) ?
                $_REQUEST["title"] : "",
            "label" => Jaris\Types::getLabel($_REQUEST["type"], "title_label"),
            "id" => "title",
            "required" => true,
            "description" => Jaris\Types::getLabel(
                $_REQUEST["type"],
                "title_description"
            )
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => isset($_REQUEST["content"]) ?
                $_REQUEST["content"] : "",
            "label" => Jaris\Types::getLabel($_REQUEST["type"], "content_label"),
            "id" => "content",
            "description" => Jaris\Types::getLabel(
                $_REQUEST["type"],
                "content_description"
            )
        );

        $fieldset[] = array("fields" => $fields);

        if(
            Jaris\Authentication::groupHasPermission(
                "add_edit_meta_content",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "meta_title",
                "value" => isset($_REQUEST["meta_title"]) ?
                    $_REQUEST["meta_title"] : "",
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "description",
                "value" => isset($_REQUEST["description"]) ?
                    $_REQUEST["description"] : "",
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "keywords",
                "value" => isset($_REQUEST["keywords"]) ?
                    $_REQUEST["keywords"] : "",
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
            Jaris\Authentication::groupHasPermission(
                "input_format_content",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {

                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == Jaris\Types::getDefaultInputFormat($_REQUEST["type"]) ?
                        true : false,
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

        //If page has no type defaults to 'pages' type
        $current_type = trim($_REQUEST["type"]);
        if($current_type == "")
        {
            $current_type = "pages";
        }

        $extra_fields = Jaris\Fields::generateFields($current_type);

        if($extra_fields)
        {
            $fieldset[] = array("fields" => $extra_fields);
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "select_content_groups",
                Jaris\Authentication::currentUserGroup()
            )
        )
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
                "value" => isset($_REQUEST["users"]) ?
                    $_REQUEST["users"] : "",
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            );

            $fieldset[] = array(
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "manual_uri_content",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $fields_other[] = array(
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => isset($_REQUEST["uri"]) ?
                    $_REQUEST["uri"] : "",
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "select_type_content",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $types = array();
            $types_array = Jaris\Types::getList(
                Jaris\Authentication::currentUserGroup(),
                Jaris\Authentication::currentUser()
            );

            foreach($types_array as $machine_name => $type_fields)
            {
                $types[t(trim($type_fields["name"]))] = $machine_name;
            }

            $fields_other[] = array(
                "type" => "select",
                "selected" => $current_type,
                "name" => "type",
                "label" => t("Type:"),
                "id" => "type",
                "value" => $types
            );
        }
        else
        {
            $fields_other[] = array(
                "type" => "hidden",
                "name" => "type",
                "value" => $current_type
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
