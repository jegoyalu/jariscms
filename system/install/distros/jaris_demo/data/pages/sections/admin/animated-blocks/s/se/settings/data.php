<?php exit; ?>


row: 0

	field: title
		<?php print t("Block Effect Settings") ?>
	field;

	field: content
		<?php
		
		    Jaris\Authentication::protectedPage(array("edit_blocks"));
		
		    Jaris\View::addTab(
		        t("Edit"),
		        Jaris\Modules::getPageUri(
		            "admin/animated-blocks/edit",
		            "animated_blocks"
		        ),
		        array(
		            "id"=>$_REQUEST["id"],
		            "position"=>$_REQUEST["position"]
		        )
		    );
		
		    Jaris\View::addTab(
		        t("Settings"),
		        Jaris\Modules::getPageUri(
		            "admin/animated-blocks/settings",
		            "animated_blocks"
		        ),
		        array(
		            "id"=>$_REQUEST["id"],
		            "position"=>$_REQUEST["position"]
		        )
		    );
		
		    Jaris\View::addTab(
		        t("Slides"),
		        Jaris\Modules::getPageUri(
		            "admin/animated-blocks/slides",
		            "animated_blocks"
		        ),
		        array(
		            "id"=>$_REQUEST["id"],
		            "position"=>$_REQUEST["position"]
		        )
		    );
		
		    Jaris\View::addTab(
		        t("Delete"),
		        "admin/blocks/delete",
		        array(
		            "id"=>$_REQUEST["id"],
		            "position"=>$_REQUEST["position"]
		        )
		    );
		
		    Jaris\View::addTab(t("Blocks"), "admin/blocks");
		
		    $block_data = Jaris\Blocks::get(
		        $_REQUEST["id"],
		        $_REQUEST["position"]
		    );
		
		    $settings = animated_blocks_get_settings($block_data);
		
		    if(
		        isset($_REQUEST["btnSave"]) &&
		        !Jaris\Forms::requiredFieldEmpty("animated-blocks-effects")
		    )
		    {
		        unset($block_data["effects"]);
		
		         //Animation area
		        $block_data["effects"]["width"] = $_REQUEST["width"];
		        $block_data["effects"]["height"] = $_REQUEST["height"];
		        $block_data["effects"]["background_color"] = $_REQUEST["background_color"];
		        $block_data["effects"]["background_transparent"] = $_REQUEST["background_transparent"];
		        $block_data["effects"]["auto_info"] = $_REQUEST["auto_info"];
		        $block_data["effects"]["border_style"] = $_REQUEST["border_style"];
		        $block_data["effects"]["border_width"] = $_REQUEST["border_width"];
		        $block_data["effects"]["border_color"] = $_REQUEST["border_color"];
		
		        //Content
		        $block_data["effects"]["title_color"] = $_REQUEST["title_color"];
		        $block_data["effects"]["title_size"] = $_REQUEST["title_size"];
		        $block_data["effects"]["title_margin"] = $_REQUEST["title_margin"];
		        $block_data["effects"]["title_padding"] = $_REQUEST["title_padding"];
		        $block_data["effects"]["description_color"] = $_REQUEST["description_color"];
		        $block_data["effects"]["description_size"] = $_REQUEST["description_size"];
		        $block_data["effects"]["description_margin"] = $_REQUEST["description_margin"];
		        $block_data["effects"]["description_padding"] = $_REQUEST["description_padding"];
		        $block_data["effects"]["description_word_count"] = $_REQUEST["description_word_count"];
		        $block_data["effects"]["content_background_color"] = $_REQUEST["content_background_color"];
		        $block_data["effects"]["content_opacity"] = $_REQUEST["content_opacity"];
		        $block_data["effects"]["content_position"] = $_REQUEST["content_position"];
		        $block_data["effects"]["content_width"] = $_REQUEST["content_width"];
		
		        //Image
		        $block_data["effects"]["image_as_background"] = $_REQUEST["image_as_background"];
		        $block_data["effects"]["image_width"] = $_REQUEST["image_width"];
		        $block_data["effects"]["image_height"] = $_REQUEST["image_height"];
		        $block_data["effects"]["image_margin"] = $_REQUEST["image_margin"];
		        $block_data["effects"]["image_padding"] = $_REQUEST["image_padding"];
		        $block_data["effects"]["image_border_style"] = $_REQUEST["image_border_style"];
		        $block_data["effects"]["image_border_width"] = $_REQUEST["image_border_width"];
		        $block_data["effects"]["image_border_color"] = $_REQUEST["image_border_color"];
		        $block_data["effects"]["image_position"] = $_REQUEST["image_position"];
		
		        //Navigation
		        $block_data["effects"]["display_navigation"] = $_REQUEST["display_navigation"];
		        $block_data["effects"]["navigation_foreground_color"] = $_REQUEST["navigation_foreground_color"];
		        $block_data["effects"]["navigation_background_color"] = $_REQUEST["navigation_background_color"];
		        $block_data["effects"]["navigation_size"] = $_REQUEST["navigation_size"];
		
		        //Pager
		        $block_data["effects"]["display_pager"] = $_REQUEST["display_pager"];
		        $block_data["effects"]["pager_position"] = $_REQUEST["pager_position"];
		        $block_data["effects"]["pager_align"] = $_REQUEST["pager_align"];
		        $block_data["effects"]["pager_color"] = $_REQUEST["pager_color"];
		        $block_data["effects"]["pager_background_color"] = $_REQUEST["pager_background_color"];
		        $block_data["effects"]["pager_active_color"] = $_REQUEST["pager_active_color"];
		        $block_data["effects"]["pager_active_background_color"] = $_REQUEST["pager_active_background_color"];
		        $block_data["effects"]["pager_margin"] = $_REQUEST["pager_margin"];
		        $block_data["effects"]["pager_padding"] = $_REQUEST["pager_padding"];
		        $block_data["effects"]["pager_border_style"] = $_REQUEST["pager_border_style"];
		        $block_data["effects"]["pager_border_width"] = $_REQUEST["pager_border_width"];
		        $block_data["effects"]["pager_border_color"] = $_REQUEST["pager_border_color"];
		        $block_data["effects"]["pager_size"] = $_REQUEST["pager_size"];
		        $block_data["effects"]["pager_bar_background_color"] = $_REQUEST["pager_bar_background_color"];
		        $block_data["effects"]["pager_bar_margin"] = $_REQUEST["pager_bar_margin"];
		        $block_data["effects"]["pager_bar_padding"] = $_REQUEST["pager_bar_padding"];
		        $block_data["effects"]["pager_bar_border_style"] = $_REQUEST["pager_bar_border_style"];
		        $block_data["effects"]["pager_bar_border_width"] = $_REQUEST["pager_bar_border_width"];
		        $block_data["effects"]["pager_bar_border_color"] = $_REQUEST["pager_bar_border_color"];
		
		        //Effect
		        $block_data["effects"]["effect_name"] = $_REQUEST["effect_name"];
		        $block_data["effects"]["transition_speed"] = $_REQUEST["transition_speed"];
		        $block_data["effects"]["effect_speed"] = $_REQUEST["effect_speed"];
		        $block_data["effects"]["hover_pause"] = $_REQUEST["hover_pause"];
		
		        $block_data["effects"] = serialize($block_data["effects"]);
		
		        if(
		            Jaris\Blocks::edit(
		                $_REQUEST["id"],
		                $_REQUEST["position"],
		                $block_data
		            )
		        )
		        {
		            Jaris\View::addMessage(t("Effect changes saved."));
		        }
		
		        Jaris\Uri::go(
		            Jaris\Modules::getPageUri(
		                "admin/animated-blocks/settings",
		                "animated_blocks"
		            ),
		            array(
		                "id"=>$_REQUEST["id"],
		                "position"=>$_REQUEST["position"]
		            )
		        );
		    }
		    elseif(isset($_REQUEST["btnCancel"]))
		    {
		        Jaris\Uri::go(
		            Jaris\Modules::getPageUri(
		                "admin/animated-blocks/settings",
		                "animated_blocks"
		            ),
		            array(
		            "id"=>$_REQUEST["id"],
		            "position"=>$_REQUEST["position"]
		            )
		        );
		    }
		
		    $parameters["name"] = "animated-blocks-effects";
		    $parameters["class"] = "animated-blocks-effects";
		    $parameters["action"] = Jaris\Uri::url(
		        Jaris\Modules::getPageUri(
		            "admin/animated-blocks/settings",
		            "animated_blocks"
		        )
		    );
		    $parameters["method"] = "post";
		
		    $true_false[t("Enable")] = true;
		    $true_false[t("Disable")] = false;
		
		    $border_style["none"] = "none";
		    $border_style["dotted"] = "dotted";
		    $border_style["dashed"] = "dashed";
		    $border_style["solid"] = "solid";
		    $border_style["double"] = "double";
		    $border_style["groove"] = "groove";
		    $border_style["ridge"] = "ridge";
		    $border_style["inset"] = "inset";
		    $border_style["outset"] = "outset";
		
		    $fields_area[] = array(
		        "type"=>"hidden",
		        "name"=>"id",
		        "value"=>$_REQUEST["id"]
		    );
		
		    $fields_area[] = array(
		        "type"=>"hidden",
		        "name"=>"position",
		        "value"=>$_REQUEST["position"]
		    );
		
		    $fields_area[] = array(
		        "type"=>"text",
		        "name"=>"width",
		        "id"=>"width",
		        "label"=>t("Width:"),
		        "value"=>$settings["width"],
		        "description"=>t("The width in pixels of the animated area. Example: 400px")
		    );
		
		    $fields_area[] = array(
		        "type"=>"text",
		        "name"=>"height",
		        "id"=>"height",
		        "label"=>t("Height:"),
		        "value"=>$settings["height"],
		        "description"=>t("The height in pixels of the animated area. Example: 350px")
		    );
		
		    $fields_area[] = array(
		        "type"=>"color",
		        "name"=>"background_color",
		        "id"=>"background_color",
		        "label"=>t("Background color:"),
		        "value"=>$settings["background_color"],
		        "description"=>t("The main background color.")
		    );
		
		    $fields_area[] = array(
		        "type"=>"radio",
		        "name"=>"background_transparent",
		        "id"=>"background_transparent",
		        "label"=>t("Transparent background?"),
		        "value"=>$true_false,
		        "checked"=>$settings["background_transparent"],
		        "description"=>t("To disable the background color.")
		    );
		
		    $fields_area[] = array(
		        "type"=>"radio",
		        "name"=>"auto_info",
		        "id"=>"auto_info",
		        "label"=>t("Autogenerate information?"),
		        "value"=>$true_false,
		        "checked"=>$settings["auto_info"],
		        "description"=>t("To automatically get page title and description.")
		    );
		
		    $fields_area[] = array(
		        "type"=>"select",
		        "name"=>"border_style",
		        "id"=>"border_style",
		        "label"=>t("Border style:"),
		        "value"=>$border_style,
		        "selected"=>$settings["border_style"],
		        "description"=>t("The border style.")
		    );
		
		    $fields_area[] = array(
		        "type"=>"text",
		        "name"=>"border_width",
		        "id"=>"border_width",
		        "label"=>t("Border width:"),
		        "value"=>$settings["border_width"],
		        "description"=>t("The border width in pixels. Example: 2px")
		    );
		
		    $fields_area[] = array(
		        "type"=>"color",
		        "name"=>"border_color",
		        "id"=>"border_color",
		        "label"=>t("Border color:"),
		        "value"=>$settings["border_color"],
		        "description"=>t("The border color.")
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Area"),
		        "fields"=>$fields_area,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields_content[] = array(
		        "type"=>"color",
		        "name"=>"title_color",
		        "id"=>"title_color",
		        "label"=>t("Title color:"),
		        "value"=>$settings["title_color"],
		        "description"=>t("The text color of the title.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"title_size",
		        "id"=>"title_size",
		        "label"=>t("Title size:"),
		        "value"=>$settings["title_size"],
		        "description"=>t("The font size in pixels of the title. Example: 12px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"title_margin",
		        "id"=>"title_margin",
		        "label"=>t("Title margin:"),
		        "value"=>$settings["title_margin"],
		        "description"=>t("The margin of title in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"title_padding",
		        "id"=>"title_padding",
		        "label"=>t("Title padding:"),
		        "value"=>$settings["title_padding"],
		        "description"=>t("The padding of title in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"color",
		        "name"=>"description_color",
		        "id"=>"description_color",
		        "label"=>t("Description color:"),
		        "value"=>$settings["description_color"],
		        "description"=>t("The text color of the description.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"description_size",
		        "id"=>"description_size",
		        "label"=>t("Description size:"),
		        "value"=>$settings["description_size"],
		        "description"=>t("The font size in pixels of the description. Example: 12px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"description_margin",
		        "id"=>"description_margin",
		        "label"=>t("Description margin:"),
		        "value"=>$settings["description_margin"],
		        "description"=>t("The margin of description in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"description_padding",
		        "id"=>"description_padding",
		        "label"=>t("Description padding:"),
		        "value"=>$settings["description_padding"],
		        "description"=>t("The padding of description in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"description_word_count",
		        "id"=>"description_word_count",
		        "label"=>t("Description word count:"),
		        "value"=>$settings["description_word_count"],
		        "description"=>t("The maximun amount of words for the description.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"color",
		        "name"=>"content_background_color",
		        "id"=>"content_background_color",
		        "label"=>t("Background color:"),
		        "value"=>$settings["content_background_color"],
		        "description"=>t("The background color of the content.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"content_opacity",
		        "id"=>"content_opacity",
		        "label"=>t("Opacity:"),
		        "value"=>$settings["content_opacity"],
		        "description"=>t("The amount of opacity for background color.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"select",
		        "name"=>"content_position",
		        "id"=>"content_position",
		        "label"=>t("Position:"),
		        "value"=>array(
		            t("Top")=>"top",
		            t("Left")=>"left",
		            t("Bottom")=>"bottom",
		            t("Right")=>"right"
		        ),
		        "selected"=>$settings["content_position"],
		        "description"=>t("The position of the content.")
		    );
		
		    $fields_content[] = array(
		        "type"=>"text",
		        "name"=>"content_width",
		        "id"=>"content_width",
		        "label"=>t("Width:"),
		        "value"=>$settings["content_width"],
		        "description"=>t("The width of the content if right or left position is selected. Example: 300px")
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Content"),
		        "fields"=>$fields_content,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields_image[] = array(
		        "type"=>"radio",
		        "name"=>"image_as_background",
		        "id"=>"image_as_background",
		        "label"=>t("Image as background?"),
		        "value"=>$true_false,
		        "checked"=>$settings["image_as_background"]
		    );
		
		    $fields_image[] = array(
		        "type"=>"text",
		        "name"=>"image_width",
		        "id"=>"image_width",
		        "label"=>t("Width:"),
		        "value"=>$settings["image_width"],
		        "description"=>t("The width in pixels of the displayed image in case image as background not enabled.")
		    );
		
		    $fields_image[] = array(
		        "type"=>"text",
		        "name"=>"image_height",
		        "id"=>"image_height",
		        "label"=>t("Height:"),
		        "value"=>$settings["image_height"],
		        "description"=>t("The height in pixels of the displayed image in case image as background not enabled.")
		    );
		
		    $fields_image[] = array(
		        "type"=>"text",
		        "name"=>"image_margin",
		        "id"=>"image_margin",
		        "label"=>t("Margin:"),
		        "value"=>$settings["image_margin"],
		        "description"=>t("The margin of image in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_image[] = array(
		        "type"=>"text",
		        "name"=>"image_padding",
		        "id"=>"image_padding",
		        "label"=>t("Padding:"),
		        "value"=>$settings["image_padding"],
		        "description"=>t("The padding of image in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_image[] = array(
		        "type"=>"select",
		        "name"=>"image_border_style",
		        "id"=>"image_border_style",
		        "label"=>t("Border style:"),
		        "value"=>$border_style,
		        "checked"=>$settings["image_border_style"],
		        "description"=>t("The border style of the image if not as background.")
		    );
		
		    $fields_image[] = array(
		        "type"=>"text",
		        "name"=>"image_border_width",
		        "id"=>"image_border_width",
		        "label"=>t("Border width:"),
		        "value"=>$settings["image_border_width"],
		        "description"=>t("The border width in pixels of the image if not as background. Example: 2px")
		    );
		
		    $fields_image[] = array(
		        "type"=>"color",
		        "name"=>"image_border_color",
		        "id"=>"image_border_color",
		        "label"=>t("Border color:"),
		        "value"=>$settings["image_border_color"],
		        "description"=>t("The border color of the image if not as background.")
		    );
		
		    $fields_image[] = array(
		        "type"=>"select",
		        "name"=>"image_position",
		        "id"=>"image_position",
		        "label"=>t("Position:"),
		        "value"=>array(
		            t("Top Left")=>"top left",
		            t("Top Right")=>"top right",
		            t("Bottom Left")=>"bottom left",
		            t("Bottom Right")=>"bottom right"
		        ),
		        "selected"=>$settings["image_position"],
		        "description"=>t("The position of the image.")
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Images"),
		        "fields"=>$fields_image,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields_navigation[] = array(
		        "type"=>"radio",
		        "name"=>"display_navigation",
		        "id"=>"display_navigation",
		        "label"=>t("Display next and previous buttons?"),
		        "value"=>$true_false,
		        "checked"=>$settings["display_navigation"]
		    );
		
		    $fields_navigation[] = array(
		        "type"=>"color",
		        "name"=>"navigation_foreground_color",
		        "id"=>"navigation_foreground_color",
		        "label"=>t("Forground color:"),
		        "value"=>$settings["navigation_foreground_color"],
		        "description"=>t("The color of the next and previous buttons label.")
		    );
		
		    $fields_navigation[] = array(
		        "type"=>"color",
		        "name"=>"navigation_background_color",
		        "id"=>"navigation_background_color",
		        "label"=>t("Background color:"),
		        "value"=>$settings["navigation_background_color"],
		        "description"=>t("Background color of the next and previous buttons.")
		    );
		
		    $fields_navigation[] = array(
		        "type"=>"text",
		        "name"=>"navigation_size",
		        "id"=>"navigation_size",
		        "label"=>t("Labels size:"),
		        "value"=>$settings["navigation_size"],
		        "description"=>t("The font size in pixels of the next and previous labels. Example: 12px")
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Navigation"),
		        "fields"=>$fields_navigation,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields_pager[] = array(
		        "type"=>"radio",
		        "name"=>"display_pager",
		        "id"=>"display_pager",
		        "label"=>t("Pager:"),
		        "value"=>$true_false,
		        "checked"=>$settings["display_pager"]
		    );
		
		    $fields_pager[] = array(
		        "type"=>"radio",
		        "name"=>"pager_position",
		        "id"=>"pager_position",
		        "label"=>t("Position:"),
		        "value"=>array(
		            t("Top")=>"top",
		            t("Bottom")=>"bottom"
		        ),
		        "checked"=>$settings["pager_position"]
		    );
		
		    $fields_pager[] = array(
		        "type"=>"radio",
		        "name"=>"pager_align",
		        "id"=>"pager_align",
		        "label"=>t("Alignment:"),
		        "value"=>array(
		            t("Left")=>"left",
		            t("Right")=>"right"
		        ),
		        "checked"=>$settings["pager_align"]
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_background_color",
		        "id"=>"pager_background_color",
		        "label"=>t("Background color:"),
		        "value"=>$settings["pager_background_color"],
		        "description"=>t("The background color of the pager buttons.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_color",
		        "id"=>"pager_color",
		        "label"=>t("Text color:"),
		        "value"=>$settings["pager_color"],
		        "description"=>t("The text color of the pager buttons.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_active_background_color",
		        "id"=>"pager_active_background_color",
		        "label"=>t("Active background color:"),
		        "value"=>$settings["pager_active_background_color"],
		        "description"=>t("The background color of the active pager button.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_active_color",
		        "id"=>"pager_active_color",
		        "label"=>t("Active text color:"),
		        "value"=>$settings["pager_active_color"],
		        "description"=>t("The text color of the active pager button.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_margin",
		        "id"=>"pager_margin",
		        "label"=>t("Margin:"),
		        "value"=>$settings["pager_margin"],
		        "description"=>t("The margin in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_padding",
		        "id"=>"pager_padding",
		        "label"=>t("Padding:"),
		        "value"=>$settings["pager_padding"],
		        "description"=>t("The padding in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"select",
		        "name"=>"pager_border_style",
		        "id"=>"pager_border_style",
		        "label"=>t("Border style:"),
		        "value"=>$border_style,
		        "selected"=>$settings["pager_border_style"],
		        "description"=>t("The border style.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_border_width",
		        "id"=>"pager_border_width",
		        "label"=>t("Border width:"),
		        "value"=>$settings["pager_border_width"],
		        "description"=>t("The border width in pixels. Example: 2px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_border_color",
		        "id"=>"pager_border_color",
		        "label"=>t("Border color:"),
		        "value"=>$settings["pager_border_color"],
		        "description"=>t("The border color.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_size",
		        "id"=>"pager_size",
		        "label"=>t("Font size:"),
		        "value"=>$settings["pager_size"],
		        "description"=>t("The font size in pixels. Example: 12px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_bar_background_color",
		        "id"=>"pager_bar_background_color",
		        "label"=>t("Pager bar background color:"),
		        "value"=>$settings["pager_bar_background_color"],
		        "description"=>t("The background color of the pager bar.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_bar_margin",
		        "id"=>"pager_bar_margin",
		        "label"=>t("Bar margin:"),
		        "value"=>$settings["pager_bar_margin"],
		        "description"=>t("The margin in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_bar_padding",
		        "id"=>"pager_bar_padding",
		        "label"=>t("Bar padding:"),
		        "value"=>$settings["pager_bar_padding"],
		        "description"=>t("The padding in pixels in the format: top right bottom left. Example 3px 4px 3px 4px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"select",
		        "name"=>"pager_bar_border_style",
		        "id"=>"pager_bar_border_style",
		        "label"=>t("Bar border style:"),
		        "value"=>$border_style,
		        "selected"=>$settings["pager_bar_border_style"],
		        "description"=>t("The border style.")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"text",
		        "name"=>"pager_bar_border_width",
		        "id"=>"pager_bar_border_width",
		        "label"=>t("Bas border width:"),
		        "value"=>$settings["pager_bar_border_width"],
		        "description"=>t("The border width in pixels. Example: 2px")
		    );
		
		    $fields_pager[] = array(
		        "type"=>"color",
		        "name"=>"pager_bar_border_color",
		        "id"=>"pager_bar_border_color",
		        "label"=>t("Bar border color:"),
		        "value"=>$settings["pager_bar_border_color"],
		        "description"=>t("The border color.")
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Pager"),
		        "fields"=>$fields_pager,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields_effect[] = array(
		        "type"=>"select",
		        "name"=>"effect_name",
		        "id"=>"effect_name",
		        "label"=>t("Effect:"),
		        "value"=>animated_blocks_get_effects_list(),
		        "selected"=>$settings["effect_name"],
		        "description"=>t("The transition effect used for the animation.")
		    );
		
		    $fields_effect[] = array(
		        "type"=>"text",
		        "name"=>"transition_speed",
		        "id"=>"transition_speed",
		        "label"=>t("Transition speed:"),
		        "value"=>$settings["transition_speed"],
		        "description"=>t("The speed of the transition in mili seconds.")
		    );
		
		    $fields_effect[] = array(
		        "type"=>"text",
		        "name"=>"effect_speed",
		        "id"=>"effect_speed",
		        "label"=>t("Effect speed:"),
		        "value"=>$settings["effect_speed"],
		        "description"=>t("The speed of the transition effect.")
		    );
		
		    $fields_effect[] = array(
		        "type"=>"radio",
		        "name"=>"hover_pause",
		        "id"=>"hover_pause",
		        "label"=>t("Pause on mouse over?"),
		        "value"=>$true_false,
		        "checked"=>$settings["hover_pause"]
		    );
		
		    $fieldset[] = array(
		        "name"=>t("Effect"),
		        "fields"=>$fields_effect,
		        "collapsible"=>true,
		        "collapsed"=>true
		    );
		
		    $fields[] = array(
		        "type"=>"submit",
		        "name"=>"btnSave",
		        "value"=>t("Save")
		    );
		
		    $fields[] = array(
		        "type"=>"submit",
		        "name"=>"btnCancel",
		        "value"=>t("Cancel")
		    );
		
		    $fieldset[] = array("fields"=>$fields);
		
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


