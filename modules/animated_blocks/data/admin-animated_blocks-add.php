<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the add animated block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Animated Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_blocks"]);

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("animated-blocks-add")
        ) {
            $fields["description"] = $_REQUEST["description"];
            $fields["title"] = $_REQUEST["title"];
            $fields["groups"] = $_REQUEST["groups"];
            $fields["themes"] = $_REQUEST["themes"];
            $fields["order"] = 0;
            $fields["display_rule"] = $_REQUEST["display_rule"];
            $fields["pages"] = $_REQUEST["pages"];

            if (
                Jaris\Authentication::groupHasPermission(
                    "return_code_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged()
            ) {
                $fields["return"] = $_REQUEST["return"];
            }

            $fields["pre_content"] = $_REQUEST["pre_content"];
            $fields["sub_content"] = $_REQUEST["sub_content"];

            $fields["is_animated_block"] = true;

            if (Jaris\Blocks::add($fields, $_REQUEST["position"], $page = "")) {
                Jaris\View::addMessage(t("The block was successfully created."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/blocks");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/blocks");
        }

        $parameters["name"] = "animated-blocks-add";
        $parameters["class"] = "animated-blocks-add";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/animated-blocks/add", "animated_blocks")
        );
        $parameters["method"] = "post";

        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $fields[] = [
            "type" => "select",
            "name" => "position",
            "label" => t("Position:"),
            "id" => "position",
            "value" => $positions,
            "selected" => "none"
        ];

        $fields[] = [
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "required" => true
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title"
        ];

        $fields[] = [
            "type" => "textarea",
            "name" => "pre_content",
            "id" => "pre_content",
            "label" => t("Pre-content:"),
            "value" => $_REQUEST["pre_content"],
            "description" => t("Content that will appear above the slides.")
        ];

        $fields[] = [
            "type" => "textarea",
            "name" => "sub_content",
            "id" => "sub_content",
            "label" => t("Sub-content:"),
            "value" => $_REQUEST["sub_content"],
            "description" => t("Content that will appear below the slides.")
        ];

        $fieldset[] = ["fields" => $fields];

        $fieldset[] = [
            "fields" => Jaris\Groups::generateFields(),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        ];

        $fieldset[] = [
            "fields" => Jaris\Blocks::generateThemesSelect(),
            "name" => t("Positions Per Theme"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the position where the block is going to be displayed per theme.")
        ];

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = [
            "type" => "radio",
            "checked" => "all_except_listed",
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        ];

        $fields_pages[] = [
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages"
        ];

        $fieldset[] = [
            "fields" => $fields_pages,
            "name" => "Pages to display",
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        ];

        if (
            Jaris\Authentication::groupHasPermission("return_code_blocks", Jaris\Authentication::currentUserGroup()) ||
            Jaris\Authentication::isAdminLogged()
        ) {
            $fields_other[] = [
                "type" => "textarea",
                "name" => "return",
                "label" => t("Return Code:"),
                "id" => "return",
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
