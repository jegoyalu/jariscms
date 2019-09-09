<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the groups permissions page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Group Permissions") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_groups", "edit_groups"]
        );

        if (!isset($_REQUEST["group"])) {
            Jaris\Uri::go("admin/groups");
        }

        Jaris\View::addTab(t("Groups"), "admin/groups");

        $permissions = Jaris\Groups::getPermissions($_REQUEST["group"]);

        if (isset($_REQUEST["btnSave"])) {
            //Save new permissions value
            $permissions_saved = true;
            foreach ($permissions as $group => $permissions_list) {
                foreach ($permissions_list as $machine_name => $human_name) {
                    if (
                        !Jaris\Groups::setPermission(
                            $machine_name,
                            $_REQUEST[$machine_name],
                            $_REQUEST["group"]
                        )
                    ) {
                        $permissions_saved = false;
                        break 2;
                    }
                }
            }

            if ($permissions_saved) {
                Jaris\View::addMessage(
                    t("The changes have been successfully saved.")
                );

                t("Updated permissions for group '{machine_name}'.");

                Jaris\Logger::info(
                    "Updated permissions for group '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["group"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/groups/permissions",
                ["group" => $_REQUEST["group"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/groups");
        }

        $parameters["name"] = "group-permissions";
        $parameters["class"] = "group-permissions";
        $parameters["action"] = Jaris\Uri::url("admin/groups/permissions");
        $parameters["method"] = "post";

        foreach ($permissions as $group => $permissions_list) {
            $fields = [];

            foreach ($permissions_list as $machine_name => $human_name) {
                $fields[] = [
                    "type" => "checkbox",
                    "checked" => Jaris\Authentication::groupHasPermission(
                        $machine_name,
                        $_REQUEST["group"]
                    ),
                    "name" => $machine_name,
                    "label" => $human_name,
                    "id" => $machine_name
                ];
            }

            $fieldset[] = [
                "name" => $group,
                "fields" => $fields,
                "collapsible" => true,
                "collapsed" => true
            ];
        }

        $fields_submit[] = [
            "type" => "hidden",
            "name" => "group",
            "value" => $_REQUEST["group"]
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_submit[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_submit];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
