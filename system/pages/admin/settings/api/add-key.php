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
        <?php print t("Add Api Key") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_keys_api"));

        if(!Jaris\Sql::dbExists("api_keys"))
        {
            Jaris\Uri::go("admin/settings/api");
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("api-add-key")
        )
        {
            $data = array(
                "description" => $_REQUEST["description"],
                "username" => $_REQUEST["username"],
                "ip_host" => $_REQUEST["ip_host"],
            );

            $key = api_key_add(
                $data
            );

            if(api_key_valid($key))
            {
                if(isset($_REQUEST["permissions"]))
                    api_key_set_permissions($key, $_REQUEST["permissions"]);

                Jaris\View::addMessage(
                    t("The api key has been successfully created.")
                );

                Jaris\Uri::go("admin/settings/api");
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings/api");
        }

        $permissions = api_get_permissions_list();

        $parameters["name"] = "api-add-key";
        $parameters["class"] = "api-add-key";
        $parameters["action"] = Jaris\Uri::url("admin/settings/api/add-key");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "name" => "description",
            "label" => t("Description:"),
            "required" => true,
            "description" => t("A brief description of the key purpose.")
        );

        $fields[] = array(
            "type" => "user",
            "value" => isset($_REQUEST["username"]) ?
                $_REQUEST["username"] : "",
            "name" => "username",
            "label" => t("Username:"),
            "description" => t("An optional username to associate to the key.")
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => isset($_REQUEST["ip_host"]) ?
                $_REQUEST["ip_host"] : "",
            "name" => "ip_host",
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
                    "checked" => $_REQUEST["permissions"][$machine_name],
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
