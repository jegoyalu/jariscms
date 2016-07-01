<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content blocks add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Page Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_content_blocks"));

        if(!isset($_REQUEST["uri"]))
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-page-block")
        )
        {
            $fields["description"] = $_REQUEST["description"];
            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];
            $fields["groups"] = $_REQUEST["groups"];
            $fields["post_block"] = false;
            $fields["uri"] = "";

            if(
                Jaris\Authentication::groupHasPermission(
                    "input_format_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                $fields["input_format"] = $_REQUEST["input_format"];
            }

            $fields["order"] = 0;
            $fields["display_rule"] = "all_except_listed";

            if(
                Jaris\Authentication::groupHasPermission(
                    "return_code_content_blocks",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                $fields["return"] = $_REQUEST["return"];
            }

            if(
                Jaris\Blocks::add(
                    $fields,
                    $_REQUEST["position"],
                    $_REQUEST["uri"]
                )
            )
            {
                Jaris\View::addMessage(
                    t("The blocks was successfully created.")
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

        $parameters["name"] = "add-page-block";
        $parameters["class"] = "add-page-block";
        $parameters["action"] = Jaris\Uri::url("admin/pages/blocks/add");
        $parameters["method"] = "post";

        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $fields[] = array(
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        );

        $fields[] = array(
            "type" => "select",
            "name" => "position",
            "label" => t("Position:"),
            "id" => "position",
            "value" => $positions,
            "selected" => isset($_REQUEST["position"]) ?
                $_REQUEST["position"] : ""
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : "",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "value" => isset($_REQUEST["title"]) ?
                $_REQUEST["title"] : "",
            "label" => t("Title:"),
            "id" => "title"
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => isset($_REQUEST["content"]) ?
                $_REQUEST["content"] : "",
            "label" => t("Content:"),
            "id" => "content"
        );

        $fieldset[] = array("fields" => $fields);

        if(
            Jaris\Authentication::groupHasPermission(
                "input_format_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {

                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == "full_html" ? true : false,
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
            "fields" => Jaris\Groups::generateFields(),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        );

        if(
            Jaris\Authentication::groupHasPermission(
                "return_code_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $fields_other[] = array(
                "type" => "textarea",
                "name" => "return",
                "value" => isset($_REQUEST["return"]) ?
                    $_REQUEST["return"] : "",
                "label" => t("Return Code:"),
                "id" => "return",
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
