<?php exit; ?>


row: 0

	field: title
		<?php print t("Edit Animated Block") ?>
	field;

	field: content
		<?php
        Jaris\Authentication::protectedPage(["edit_blocks"]);
        
        $block_data = Jaris\Blocks::get($_REQUEST["id"], $_REQUEST["position"]);
        
        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("animated-blocks-edit")
        ) {
            $block_data["description"] = $_REQUEST["description"];
            $block_data["title"] = $_REQUEST["title"];
            $block_data["display_rule"] = $_REQUEST["display_rule"];
            $block_data["pages"] = $_REQUEST["pages"];
            $block_data["groups"] = $_REQUEST["groups"];
            $block_data["themes"] = $_REQUEST["themes"];
            $block_data["pre_content"] = $_REQUEST["pre_content"];
            $block_data["sub_content"] = $_REQUEST["sub_content"];
        
            if (
                Jaris\Authentication::groupHasPermission(
                    "return_code_blocks",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::isAdminLogged()
            ) {
                $block_data["return"] = $_REQUEST["return"];
            }
        
            if (Jaris\Blocks::edit($_REQUEST["id"], $_REQUEST["position"], $block_data)) {
                if ($_REQUEST["position"] != $_REQUEST["new_position"]) {
                    Jaris\Blocks::move(
                        $_REQUEST["id"],
                        $_REQUEST["position"],
                        $_REQUEST["new_position"]
                    );
                }
        
                Jaris\View::addMessage(t("Your changes have been saved to the block."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }
        
            Jaris\Uri::go("admin/blocks");
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/blocks");
        }
        
        Jaris\View::addTab(
            t("Edit"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/edit",
                "animated_blocks"
            ),
            [
                "id" => $_REQUEST["id"],
                "position" => $_REQUEST["position"]]
        );
        
        Jaris\View::addTab(
            t("Settings"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/settings",
                "animated_blocks"
            ),
            [
                "id" => $_REQUEST["id"],
                "position" => $_REQUEST["position"]
            ]
        );
        
        Jaris\View::addTab(
            t("Slides"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/slides",
                "animated_blocks"
            ),
            [
                "id" => $_REQUEST["id"],
                "position" => $_REQUEST["position"]
            ]
        );
        
        Jaris\View::addTab(
            t("Delete"),
            "admin/blocks/delete",
            [
                "id" => $_REQUEST["id"],
                "position" => $_REQUEST["position"]
            ]
        );
        
        Jaris\View::addTab(t("Blocks"), "admin/blocks");
        
        //Print block edit form
        
        $parameters["name"] = "animated-blocks-edit";
        $parameters["class"] = "animated-blocks-edit";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/edit",
                "animated_blocks"
            )
        );
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
            "selected" => $_REQUEST["new_position"] ?
                $_REQUEST["new_position"]
                :
                $_REQUEST["position"]
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
            "type" => "textarea",
            "name" => "pre_content",
            "id" => "pre_content",
            "label" => t("Pre-content:"),
            "value" => $_REQUEST["pre_content"] ?
                $_REQUEST["pre_content"]
                :
                $block_data["pre_content"],
            "description" => t("Content that will appear above the slides.")
        ];
        
        $fields[] = [
            "type" => "textarea",
            "name" => "sub_content",
            "id" => "sub_content",
            "label" => t("Sub-content:"),
            "value" => $_REQUEST["sub_content"] ?
                $_REQUEST["sub_content"]
                :
                $block_data["sub_content"],
            "description" => t("Content that will appear below the slides.")
        ];
        
        $fieldset[] = ["fields" => $fields];
        
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
            Jaris\Authentication::groupHasPermission("return_code_blocks", Jaris\Authentication::currentUserGroup()) ||
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

	field: users
		N;
	field;

	field: groups
		N;
	field;

	field: categories
		N;
	field;

row;


