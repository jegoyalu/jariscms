<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the types uploads management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Type Maximum Posts") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_types"));

        if(!isset($_REQUEST["type"]))
        {
            Jaris\Uri::go("admin/types");
        }

        Jaris\View::addTab(
            t("Type"), "admin/types/edit",
            array("type" => $_REQUEST["type"])
        );

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $type_settings = Jaris\Types::get($_REQUEST["type"]);

        if(isset($_REQUEST["btnSave"]))
        {
            foreach(Jaris\Groups::getList() as $name => $machine_name)
            {
                if($machine_name != "administrator")
                {
                    $type_settings["posts"][$machine_name] =
                        $_REQUEST["{$machine_name}_maximum_posts"]
                    ;
                }
            }

            //Check if save was successful
            if(Jaris\Types::edit($_REQUEST["type"], $type_settings))
            {
                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                t("Edited content type '{machine_name}' post settings.");

                Jaris\Logger::info(
                    "Edited content type '{machine_name}' post settings.",
                    array(
                        "machine_name" => $_REQUEST["type"]
                    )
                );
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/types/edit",
                array("type" => $_REQUEST["type"])
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                "admin/types/edit",
                array("type" => $_REQUEST["type"])
            );
        }

        $parameters["name"] = "type-max-posts-settings";
        $parameters["class"] = "type-max-posts-settings";
        $parameters["action"] = Jaris\Uri::url("admin/types/posts");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "type",
            "value" => $_REQUEST["type"]
        );

        foreach(Jaris\Groups::getList() as $name => $machine_name)
        {
            if($machine_name != "administrator")
            {
                unset($file_fields);

                $file_fields[] = array(
                    "type" => "text",
                    "label" => t("Posts:"),
                    "name" => "{$machine_name}_maximum_posts",
                    "id" => "maximum_posts",
                    "value" => $type_settings["posts"][$machine_name] != "" ?
                        $type_settings["posts"][$machine_name]
                        :
                        "0",
                    "description" => t("Maximum number of posts per user. 0 for unlimited")
                );

                $fieldset[] = array(
                    "name" => t($name),
                    "fields" => $file_fields,
                    "collapsible" => true
                );
            }
        }

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
