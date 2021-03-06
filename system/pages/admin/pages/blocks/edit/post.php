<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content blocks edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Post Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_content_blocks"]);

        if (
            !isset($_REQUEST["uri"]) ||
            !isset($_REQUEST["id"]) ||
            !isset($_REQUEST["position"])
        ) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $block_id = intval($_REQUEST["id"]);

        $block_data = Jaris\Blocks::get(
            $block_id,
            $_REQUEST["position"],
            $_REQUEST["uri"]
        );

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-post-block")
        ) {
            //Trim uri spaces
            $_REQUEST["page_uri"] = trim($_REQUEST["page_uri"]);

            $block_data["description"] = $_REQUEST["description"];
            $block_data["title"] = $_REQUEST["title"];
            $block_data["content"] = "";
            $block_data["display_rule"] = "all_except_listed";
            $block_data["groups"] = $_REQUEST["groups"];
            $block_data["post_block"] = true;
            $block_data["uri"] = $_REQUEST["page_uri"];

            if (
                Jaris\Authentication::groupHasPermission(
                    "return_code_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                $block_data["return"] = $_REQUEST["return"];
            }

            if (!$block_data["is_system"]) {
                $block_data["content"] = $_REQUEST["content"];
            }

            if (
                Jaris\Authentication::groupHasPermission(
                    "input_format_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged() &&
                !$block_data["is_system"]
            ) {
                $block_data["input_format"] = $_REQUEST["input_format"];
            }

            if (
                Jaris\Blocks::edit(
                    $block_id,
                    $_REQUEST["position"],
                    $block_data,
                    $_REQUEST["uri"]
                )
            ) {
                if ($_REQUEST["position"] != $_REQUEST["new_position"]) {
                    Jaris\Blocks::move(
                        $block_id,
                        $_REQUEST["position"],
                        $_REQUEST["new_position"],
                        $_REQUEST["uri"]
                    );
                }

                Jaris\View::addMessage(
                    t("Your changes have been saved to the block.")
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/pages/blocks",
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                "admin/pages/blocks",
                ["uri" => $_REQUEST["uri"]]
            );
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            ) &&
            Jaris\Pages::userIsOwner($_REQUEST["uri"])
        ) {
            Jaris\View::addTab(
                t("Delete"),
                "admin/pages/blocks/delete",
                [
                    "id" => $_REQUEST["id"],
                    "position" => $_REQUEST["position"],
                    "uri" => $_REQUEST["uri"]
                ]
            );
        }

        Jaris\View::addTab(
            t("Blocks"),
            "admin/pages/blocks",
            ["uri" => $_REQUEST["uri"]]
        );

        //Print block edit form

        $parameters["name"] = "edit-post-block";
        $parameters["class"] = "edit-post-block";
        $parameters["action"] = Jaris\Uri::url("admin/pages/blocks/edit/post");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        ];

        $fields[] = [
            "type" => "hidden",
            "name" => "position",
            "value" => $_REQUEST["position"]
        ];

        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $fields[] = [
            "type" => "select",
            "name" => "new_position",
            "label" => t("Position:"),
            "id" => "new_position",
            "value" => $positions,
            "selected" => $_REQUEST["position"]
        ];

        $fields[] = [
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "value" => $block_data["description"],
            "required" => true
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "value" => $block_data["title"]
        ];

        $fields[] = [
            "type" => "uri",
            "name" => "page_uri",
            "label" => t("Uri:"),
            "id" => "page_uri",
            "value" => $block_data["uri"],
            "required" => true,
            "description" => t("The uri of the page to display a summary.")
        ];

        $fieldset[] = ["fields" => $fields];

        if (
            Jaris\Authentication::groupHasPermission(
                "input_format_content_blocks",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::isAdminLogged() &&
            !$block_data["is_system"]
        ) {
            $fields_inputformats = [];

            foreach (Jaris\InputFormats::getAll() as $machine_name => $fields_formats) {
                $fields_inputformats[] = [
                    "type" => "radio",
                    "checked" => $machine_name == $block_data["input_format"] ?
                        true
                        :
                        false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => [$fields_formats["title"] => $machine_name]
                ];
            }

            $fieldset[] = [
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            ];
        }

        $fieldset[] = [
            "fields" => Jaris\Groups::generateFields($block_data["groups"]),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        ];

        if (
            Jaris\Authentication::groupHasPermission(
                "return_code_content_blocks",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::isAdminLogged()
        ) {
            $fields_other[] = [
                "type" => "textarea",
                "name" => "return",
                "label" => t("Return Code:"),
                "id" => "return",
                "value" => $block_data["return"],
                "description" => t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(Jaris\Authentication::isUserLogged()) print \"true\"; else print \"false\"; ?&gt;")
            ];
        }

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_other];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
