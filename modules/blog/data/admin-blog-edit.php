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
        <?php print t("Blog Settings") ?>
    field;

    field: content
    <?php
        if(!Jaris\Authentication::hasTypeAccess("blog", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\Authentication::protectedPage();
        }

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $blog_settings = blog_get_main_settings();

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("blog-edit"))
        {
            $fields["title"] = substr(Jaris\Util::stripHTMLTags($_REQUEST["title"]), 0, 80);
            $fields["description"] = substr(Jaris\Util::stripHTMLTags($_REQUEST["description"]), 0, 500);
            $fields["tags"] = substr(Jaris\Util::stripHTMLTags($_REQUEST["tags"]), 0, 300);
            $fields["category"] = $_REQUEST[$blog_settings["main_category"]][0];

            blog_create_if_not_exists(Jaris\Authentication::currentUser());

            blog_edit_from_db(Jaris\Authentication::currentUser(), $fields);

            Jaris\View::addMessage(t("Blog settings successfully updated."));

            Jaris\Uri::go(Jaris\Modules::getPageUri("users/blog", "blog"));
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(Jaris\Modules::getPageUri("users/blog", "blog"));
        }

        $blog_data = blog_get_from_db(Jaris\Authentication::currentUser());

        $parameters["name"] = "blog-edit";
        $parameters["class"] = "blog-edit";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/blog/edit", "blog")
        );
        $parameters["method"] = "post";

        if($blog_settings["main_category"] != "")
        {
            $fields = Jaris\Categories::generateFields(
                array(
                    "{$blog_settings['main_category']}" => array($blog_data["category"])
                ),
                $blog_settings["main_category"]
            );

            $fields[0]["label"] = t("Category:");
        }

        $fields[] = array(
            "type" => "text",
            "limit" => 80,
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "value" => $blog_data["title"],
            "description" => t("The title or name of the blog.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "description",
            "limit" => 500,
            "label" => t("Description:"),
            "id" => "description",
            "value" => $blog_data["description"],
            "description" => t("A brief description of the blog.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "tags",
            "limit" => 300,
            "label" => t("Tags:"),
            "id" => "tags",
            "value" => $blog_data["tags"],
            "description" => t("A list of words seperated by space that describe the blog.")
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
