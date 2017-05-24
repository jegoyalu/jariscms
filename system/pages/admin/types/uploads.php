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
        <?php print t("Type Upload Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_types"));

        if(!isset($_REQUEST["type"]))
        {
            Jaris\Uri::go("admin/types");
        }

        Jaris\View::addTab(
            t("Type"),
            "admin/types/edit",
            array("type" => $_REQUEST["type"])
        );

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $type_settings = Jaris\Types::get($_REQUEST["type"]);

        if(isset($_REQUEST["btnSave"]))
        {
            foreach(Jaris\Groups::getList() as $name => $machine_name)
            {
                $type_settings["uploads"][$machine_name]["maximum_images"] =
                    $_REQUEST["{$machine_name}_maximum_images"]
                ;

                $type_settings["uploads"][$machine_name]["maximum_files"] =
                    $_REQUEST["{$machine_name}_maximum_files"]
                ;
            }

            //Check if save was successful
            if(Jaris\Types::edit($_REQUEST["type"], $type_settings))
            {
                Jaris\View::addMessage(
                    t("Your settings have been successfully saved.")
                );

                t("Edited content type '{machine_name}' upload settings.");

                Jaris\Logger::info(
                    "Edited content type '{machine_name}' upload settings.",
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

        $parameters["name"] = "type-upload-settings";
        $parameters["class"] = "type-upload-settings";
        $parameters["action"] = Jaris\Uri::url("admin/types/uploads");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "type",
            "value" => $_REQUEST["type"]
        );

        foreach(Jaris\Groups::getList() as $name => $machine_name)
        {
            unset($file_fields);

            $file_fields[] = array(
                "type" => "text",
                "label" => t("Images:"),
                "name" => "{$machine_name}_maximum_images",
                "id" => "maximum_images",
                "value" => $type_settings["uploads"][$machine_name]["maximum_images"] != "" ?
                    $type_settings["uploads"][$machine_name]["maximum_images"]
                    :
                    "-1",
                "description" => t("Maximum images user can upload per post. -1 for unlimited")
            );

            $file_fields[] = array(
                "type" => "text",
                "label" => t("Files:"),
                "name" => "{$machine_name}_maximum_files",
                "id" => "maximum_files",
                "value" => $type_settings["uploads"][$machine_name]["maximum_files"] != "" ?
                    $type_settings["uploads"][$machine_name]["maximum_files"]
                    :
                    "-1",
                "description" => t("Maximum files user can upload per post. -1 for unlimited")
            );

            $fieldset[] = array(
                "name" => t($name),
                "fields" => $file_fields,
                "collapsible" => true
            );
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
