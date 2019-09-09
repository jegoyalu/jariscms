<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the group add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Group") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_groups", "add_groups"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-group")
        )
        {
            $fields["name"] = $_REQUEST["name"];
            $fields["description"] = $_REQUEST["description"];

            $message = Jaris\Groups::add($_REQUEST["machine_name"], $fields);

            if($message == "true")
            {
                Jaris\View::addMessage(
                    t("The group has been successfully created.")
                );

                t("Added group '{machine_name}'.");

                Jaris\Logger::info(
                    "Added group '{machine_name}'.",
                    array(
                        "machine_name" => $_REQUEST["machine_name"]
                    )
                );
            }
            else
            {
                //An error ocurred so display the error message
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/groups");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/groups");
        }

        $parameters["name"] = "add-group";
        $parameters["class"] = "add-group";
        $parameters["action"] = Jaris\Uri::url("admin/groups/add");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["machine_name"]) ?
                $_REQUEST["machine_name"] : "",
            "name" => "machine_name",
            "label" => t("Machine name:"),
            "id" => "machine_name",
            "required" => true,
            "description" => t("A readable machine name, like for example: my-group.")
        );

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["name"]) ?
                $_REQUEST["name"] : "",
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name like for example: My Group.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true,
            "description" => t("A brief description of the group.")
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
