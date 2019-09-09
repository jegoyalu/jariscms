<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the global edit block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_blocks", "edit_blocks"]);

        if (!isset($_REQUEST["id"]) || !isset($_REQUEST["position"])) {
            Jaris\Uri::go("admin/blocks");
        }

        $block_data = Jaris\Blocks::get(
            intval($_REQUEST["id"]),
            $_REQUEST["position"]
        );

        if (isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("block-edit")) {
            $block_data["description"] = $_REQUEST["description"];
            $block_data["title"] = $_REQUEST["title"];
            $block_data["display_rule"] = $_REQUEST["display_rule"];
            $block_data["pages"] = $_REQUEST["pages"];
            $block_data["groups"] = $_REQUEST["groups"];
            $block_data["themes"] = $_REQUEST["themes"];
            if (
                Jaris\Authentication::groupHasPermission(
                    "return_code_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged()
            ) {
                $block_data["return"] = $_REQUEST["return"];
            }
            if (!$block_data["is_system"]) {
                $block_data["content"] = $_REQUEST["content"];

                if (
                    Jaris\Authentication::groupHasPermission(
                        "input_format_blocks",
                        Jaris\Authentication::currentUserGroup()
                    ) ||
                    Jaris\Authentication::isAdminLogged() &&
                    !$block_data["is_system"]
                ) {
                    $block_data["input_format"] = $_REQUEST["input_format"];
                }
            }

            if (
                Jaris\Blocks::edit(
                    intval($_REQUEST["id"]),
                    $_REQUEST["position"],
                    $block_data
                )
            ) {
                if ($_REQUEST["position"] != $_REQUEST["new_position"]) {
                    Jaris\Blocks::move(
                        intval($_REQUEST["id"]),
                        $_REQUEST["position"],
                        $_REQUEST["new_position"]
                    );
                }

                Jaris\View::addMessage(
                    t("Your changes have been saved to the block.")
                );

                t("Edited global block '{title}' on {position}.");

                Jaris\Logger::info(
                    "Edited global block '{title}' on {position}.",
                    [
                        "title" => $block_data["title"],
                        "position" => $_REQUEST["position"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/blocks");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/blocks");
        }
    ?>

    <?php
        $arguments = [
            "id" => $_REQUEST["id"],
            "position" => $_REQUEST["position"]
        ];

        //Tabs
        if (!$block_data["is_system"]) {
            Jaris\View::addTab(t("Delete"), "admin/blocks/delete", $arguments);
        }

        Jaris\View::addTab(t("Blocks"), "admin/blocks");
        Jaris\View::addTab(t("Translate"), "admin/blocks/translate", $arguments);

        //Print block edit form

        $parameters["name"] = "block-edit";
        $parameters["class"] = "block-edit";
        $parameters["action"] = Jaris\Uri::url("admin/blocks/edit");
        $parameters["method"] = "post";

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
            "selected" => isset($_REQUEST["new_position"]) ?
                $_REQUEST["new_position"] : $_REQUEST["position"]
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

        if (!$block_data["is_system"]) {
            $fields[] = [
                "type" => "textarea",
                "name" => "content",
                "label" => t("Content:"),
                "id" => "content",
                "value" => $block_data["content"]
            ];
        }

        $fieldset[] = ["fields" => $fields];

        if (!$block_data["is_system"]) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "input_format_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged() &&
                !$block_data["is_system"]
            ) {
                $fields_inputformats = [];

                foreach (Jaris\InputFormats::getAll() as $machine_name => $fields_formats) {
                    $fields_inputformats[] = [
                        "type" => "radio",
                        "checked" => $machine_name == $block_data["input_format"] ? true : false,
                        "name" => "input_format",
                        "description" => $fields_formats["description"],
                        "value" => [
                            $fields_formats["title"] => $machine_name
                        ]
                    ];
                }

                $fieldset[] = [
                    "fields" => $fields_inputformats,
                    "name" => t("Input Format")
                ];
            }
        }

        $fieldset[] = [
            "fields" => Jaris\Groups::generateFields($block_data["groups"]),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        ];

        $fieldset[] = [
            "fields" => Jaris\Blocks::generateThemesSelect($block_data["themes"]),
            "name" => t("Positions Per Theme"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the position where the block is going to be displayed per theme.")
        ];

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";

        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = [
            "type" => "radio",
            "checked" => $block_data["display_rule"],
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        ];

        $fields_pages[] = [
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $block_data["pages"]
        ];

        $fieldset[] = [
            "fields" => $fields_pages,
            "name" => "Pages to display",
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        ];

        if (
            Jaris\Authentication::groupHasPermission(
                "return_code_blocks",
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
