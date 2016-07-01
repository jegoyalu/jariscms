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
        <?php print t("Edit Page Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_content_blocks"));

        if(
            !isset($_REQUEST["uri"]) ||
            !isset($_REQUEST["id"]) ||
            !isset($_REQUEST["position"])
        )
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        $block_data = Jaris\Blocks::get(
            $_REQUEST["id"],
            $_REQUEST["position"],
            $_REQUEST["uri"]
        );

        if($block_data["post_block"])
        {
            Jaris\Uri::go(
                "admin/pages/blocks/edit/post",
                array(
                    "uri" => $_REQUEST["uri"],
                    "id" => $_REQUEST["id"],
                    "position" => $_REQUEST["position"]
                )
            );
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-page-block")
        )
        {
            //Trim uri spaces
            $_REQUEST["page_uri"] = trim($_REQUEST["page_uri"]);

            $block_data["description"] = $_REQUEST["description"];
            $block_data["title"] = $_REQUEST["title"];
            $block_data["content"] = $_REQUEST["content"];
            $block_data["display_rule"] = "all_except_listed";
            $block_data["groups"] = $_REQUEST["groups"];
            $block_data["post_block"] = false;
            $block_data["uri"] = "";

            if(
                Jaris\Authentication::groupHasPermission(
                    "return_code_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                $block_data["return"] = $_REQUEST["return"];
            }

            if(!$block_data["is_system"])
            {
                $block_data["content"] = $_REQUEST["content"];
            }

            if(
                Jaris\Authentication::groupHasPermission(
                    "input_format_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged() &&
                !$block_data["is_system"]
            )
            {
                $block_data["input_format"] = $_REQUEST["input_format"];
            }

            if(
                Jaris\Blocks::edit(
                    $_REQUEST["id"],
                    $_REQUEST["position"],
                    $block_data,
                    $_REQUEST["uri"]
                )
            )
            {
                if($_REQUEST["position"] != $_REQUEST["new_position"])
                {
                    Jaris\Blocks::move(
                        $_REQUEST["id"],
                        $_REQUEST["position"],
                        $_REQUEST["new_position"],
                        $_REQUEST["uri"]
                    );
                }

                Jaris\View::addMessage(
                    t("Your changes have been saved to the block.")
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
                "admin/pages/blocks",
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                "admin/pages/blocks",
                array("uri" => $_REQUEST["uri"])
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            ) &&
            Jaris\Pages::userIsOwner($_REQUEST["uri"])
        )
        {
            Jaris\View::addTab(
                t("Delete"),
                "admin/pages/blocks/delete",
                array(
                    "id" => $_REQUEST["id"],
                    "position" => $_REQUEST["position"],
                    "uri" => $_REQUEST["uri"]
                )
            );
        }

        Jaris\View::addTab(
            t("Blocks"),
            "admin/pages/blocks",
            array("uri" => $_REQUEST["uri"])
        );

        //Print block edit form

        $parameters["name"] = "edit-page-block";
        $parameters["class"] = "edit-page-block";
        $parameters["action"] = Jaris\Uri::url("admin/pages/blocks/edit");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        );

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields[] = array(
            "type" => "hidden",
            "name" => "position",
            "value" => $_REQUEST["position"]
        );

        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $fields[] = array(
            "type" => "select",
            "name" => "new_position",
            "label" => t("Position:"),
            "id" => "new_position",
            "value" => $positions,
            "selected" => $_REQUEST["position"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "value" => $block_data["description"],
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "value" => $block_data["title"]
        );

        if(!$block_data["is_system"])
        {
            $fields[] = array(
                "type" => "textarea",
                "name" => "content",
                "label" => t("Content:"),
                "id" => "content",
                "value" => $block_data["content"]
            );
        }

        $fieldset[] = array("fields" => $fields);

        if(
            Jaris\Authentication::groupHasPermission(
                "input_format_content_blocks",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::isAdminLogged() &&
            !$block_data["is_system"]
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {

                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == $block_data["input_format"] ?
                        true
                        :
                        false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => array($fields_formats["title"] => $machine_name)
                );
            }

            $fieldset[] = array(
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            );
        }

        $fieldset[] = array(
            "fields" => Jaris\Groups::generateFields($block_data["groups"]),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        );

        if(
            Jaris\Authentication::groupHasPermission(
                "return_code_content_blocks",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_other[] = array(
                "type" => "textarea",
                "name" => "return",
                "label" => t("Return Code:"),
                "id" => "return",
                "value" => $block_data["return"],
                "description" => t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(Jaris\Authentication::isUserLogged()) print \"true\"; else print \"false\"; ?&gt;")
            );
        }

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
