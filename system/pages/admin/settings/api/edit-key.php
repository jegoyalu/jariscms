<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the user add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Api Key") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_keys_api"));

        if(!Jaris\Sql::dbExists("api_keys") || !isset($_REQUEST["id"]))
        {
            Jaris\Uri::go("admin/settings/api");
        }

        $key_data = Jaris\ApiKey::getDataById($_REQUEST["id"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("api-edit-key")
        )
        {
            $key_data["description"] =$_REQUEST["description"];
            $key_data["username"] =$_REQUEST["username"];
            $key_data["ip_host"] =$_REQUEST["ip_host"];

            Jaris\ApiKey::edit($key_data["key"], $key_data);

            if(isset($_REQUEST["permissions"]))
                Jaris\ApiKey::setPermissions($key_data["key"], $_REQUEST["permissions"]);
            else
                Jaris\ApiKey::setPermissions($key_data["key"], array());

            Jaris\View::addMessage(t("The api key has been successfully updated."));

            t("Edited api key '{key}'.");

            Jaris\Logger::info(
                "Edited api key '{key}'.",
                array(
                    "key" => $key_data["key"]
                )
            );

            Jaris\Uri::go("admin/settings/api");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings/api");
        }

        $permissions = Jaris\Api::getPermissionsList();

        $parameters["name"] = "api-edit-key";
        $parameters["class"] = "api-edit-key";
        $parameters["action"] = Jaris\Uri::url("admin/settings/api/edit-key");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "key",
            "value" => $key_data["key"],
            "label" => t("Key:"),
            "readonly" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "value" => $key_data["description"],
            "label" => t("Description:"),
            "required" => true,
            "description" => t("A brief description of the key purpose.")
        );

        $fields[] = array(
            "type" => "user",
            "name" => "username",
            "value" => $key_data["username"],
            "label" => t("Username:"),
            "description" => t("An optional username to associate to the key.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "ip_host",
            "value" => $key_data["ip_host"],
            "label" => t("IP or Host:"),
            "description" => t("A list separated by comma (,) of ip addresses or host that can use the apikey. Eg: 192.168.1.1, host.mydynip.com")
        );

        if(count($permissions) > 0)
        {
            $fields[] = array(
                "type" => "other",
                "html_code" => "<h2>"
                    . t("Permissions")
                    . "</h2>"
            );
        }

        $fieldset[] = array("fields" => $fields);

        foreach($permissions as $group => $permissions_list)
        {
            $fields = array();

            foreach($permissions_list as $machine_name => $human_name)
            {
                $fields[] = array(
                    "type" => "checkbox",
                    "checked" => isset($key_data["permissions"][$machine_name]) ?
                        $key_data["permissions"][$machine_name]
                        :
                        false,
                    "name" => "permissions[$machine_name]",
                    "label" => $human_name,
                    "id" => $machine_name
                );
            }

            $fieldset[] = array(
                "name" => $group,
                "fields" => $fields,
                "collapsible" => true,
                "collapsed" => true
            );
        }

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_submit);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
