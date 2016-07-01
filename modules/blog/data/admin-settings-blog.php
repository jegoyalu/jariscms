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
        Jaris\Authentication::protectedPage(array("edit_settings"));

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $blog_settings = blog_get_main_settings();

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-blog-settings")
        )
        {
            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save("main_category", $_REQUEST["main_category"], "blogs"))
            {
                Jaris\View::addMessage(t("Your settings have been successfully saved."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/settings");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "edit-blog-settings";
        $parameters["class"] = "edit-blog-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/settings/blog", "blog")
        );
        $parameters["method"] = "post";

        $categories[t("-None Selected-")] = "";
        $category_data = Jaris\Categories::getList();

        if($category_data)
        {
            foreach($category_data as $machine_name => $data)
            {
                $categories[t($data["name"])] = $machine_name;
            }
        }

        $fields[] = array(
            "type" => "select",
            "name" => "main_category",
            "label" => t("Blog categories:"),
            "id" => "main_category",
            "value" => $categories,
            "selected" => $blog_settings["main_category"],
            "description" => t("The main category where users can select a sub category that represent its blog content.")
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
