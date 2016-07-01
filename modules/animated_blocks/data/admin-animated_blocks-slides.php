<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the animated blocks slides page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Block Slides") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_blocks"));
    ?>
    <style>
        .slides-list thead td
        {
            font-weight: bold;
        }

        .slides-list td
        {
            padding: 5px;
            border-bottom: dashed 1px #d3d3d3;
            width: auto;
        }
    </style>
    <?php if(!isset($_REQUEST["view"])){ ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".slides-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });
        });
    </script>
    <?php } ?>
    <?php if(isset($_REQUEST["view"]) && ($_REQUEST["view"] == "add" || $_REQUEST["view"] == "edit")){ ?>
    <script>
        $(document).ready(function() {
            $('input[name="slide_type"]').change(function(){
                if($(this).val() == "uri"){
                    $('input[name="slide_uri"]').parent().show();
                    $('input[name="image"]').parent().hide();
                    $('#slide-current-image').hide();
                } else{
                    $('input[name="image"]').parent().show();
                    $('#slide-current-image').show();
                    $('input[name="slide_uri"]').parent().hide();
                }
            });

            if($('input[name="slide_type"]:checked').val() == "image"){
                $('input[name="slide_uri"]').parent().hide();
                $('input[name="image"]').parent().show();
            } else{
                $('input[name="slide_uri"]').parent().show();
                $('input[name="image"]').parent().hide();
            }
        });
    </script>
    <?php } ?>
    <?php
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.js");
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

        $block_data = Jaris\Blocks::get($_REQUEST["id"], $_REQUEST["position"]);
        $block_data["content"] = unserialize($block_data["content"]);

        $settings = animated_blocks_get_settings($block_data);

        Jaris\View::addTab(
            t("Edit"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/edit",
                "animated_blocks"
            ),
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(
            t("Settings"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/settings",
                "animated_blocks"
            ),
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(
            t("Slides"),
            Jaris\Modules::getPageUri(
                "admin/animated-blocks/slides",
                "animated_blocks"
            ),
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(
            t("Delete"),
            "admin/blocks/delete",
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(t("Blocks"), "admin/blocks");

        if($_REQUEST["action"] == "add")
        {
            if(
                isset($_REQUEST["btnSave"])
            )
            {
                //empty form session data
                Jaris\Forms::requiredFieldEmpty("add-slide");

                $required = false;

                if(
                    $_REQUEST["slide_type"] == "uri" &&
                    trim($_REQUEST["slide_uri"]) == ""
                )
                {
                    $required = true;
                }
                elseif(
                    $_REQUEST["slide_type"] == "image" &&
                    (
                    empty($_FILES["image"]) ||
                    empty($_FILES["image"]["name"])
                    )
                )
                {
                    $required = true;
                }

                if(!$required)
                {
                    $slide_data = array(
                        "type" => $_REQUEST["slide_type"],
                        "uri" => $_REQUEST["slide_type"] == "uri" ?
                            $_REQUEST["slide_uri"] : "",
                        "image" => $_REQUEST["slide_type"] == "image" ?
                            Jaris\Files::addUpload(
                                $_FILES["image"],
                                "animated_blocks"
                            )
                            :
                            "",
                        "link" => $_REQUEST["link"],
                        "title" => $_REQUEST["title"],
                        "description" => $_REQUEST["description"],
                        "order" => "0"
                    );

                    if($_REQUEST["slide_type"] == "image")
                    {
                        chmod(
                            Jaris\Files::get(
                                $slide_data["image"],
                                "animated_blocks"
                            ),
                            0755
                        );

                        Jaris\Images::resize(
                            Jaris\Files::get(
                                $slide_data["image"],
                                "animated_blocks"
                            ),
                            $settings["width"]
                        );

                        $slide_data["uri"] = $slide_data["image"];
                    }

                    $block_data["content"][] = $slide_data;
                    $block_data["content"] = serialize($block_data["content"]);

                    Jaris\Blocks::edit(
                        $_REQUEST["id"],
                        $_REQUEST["position"],
                        $block_data
                    );

                    Jaris\View::addMessage(t("Slide added."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/animated-blocks/slides",
                            "animated_blocks"
                        ),
                        array(
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"]
                        )
                    );
                }
                else
                {
                    Jaris\View::addMessage(t("Please provide the required fields."), "error");
                }
            }
            else
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
        }

        if($_REQUEST["action"] == "edit")
        {
            if(
                isset($_REQUEST["btnSave"])
            )
            {
                //empty form session data
                Jaris\Forms::requiredFieldEmpty("edit-slide");

                $required = false;
                $prevent_edit = false;

                $current_image = $block_data["content"]
                    [$_REQUEST["slide_id"]]
                    ["image"]
                ;

                if(
                    $_REQUEST["slide_type"] == "uri" &&
                    trim($_REQUEST["slide_uri"]) == ""
                )
                {
                    $required = true;
                }
                elseif(
                    $_REQUEST["slide_type"] == "image" &&
                    (
                    empty($_FILES["image"]) ||
                    empty($_FILES["image"]["name"])
                    ) &&
                    trim($current_image) == ""
                )
                {
                    $required = true;
                }

                $current_type = $block_data["content"]
                    [$_REQUEST["slide_id"]]
                    ["type"]
                ;

                if(
                    $_REQUEST["slide_type"] == "uri" &&
                    $current_type == "image"
                )
                {
                    if($_REQUEST["slide_uri"] != $current_image)
                    {
                        Jaris\Files::delete(
                            $current_image,
                            "animated_blocks"
                        );
                    }
                    else
                    {
                        $_REQUEST["slide_type"] = "image";
                        $prevent_edit = true;
                    }
                }

                if(!$required && !$prevent_edit)
                {
                    $block_data["content"][$_REQUEST["slide_id"]] = array(
                        "type" => $_REQUEST["slide_type"],
                        "uri" => $_REQUEST["slide_type"] == "uri" ?
                            $_REQUEST["slide_uri"] : $current_image,
                        "image" => $current_image,
                        "link" => $_REQUEST["link"],
                        "title" => $_REQUEST["title"],
                        "description" => $_REQUEST["description"],
                        "order" => $_REQUEST["order"]
                    );

                    if(
                        $_REQUEST["slide_type"] == "image" &&
                        !empty($_FILES["image"]) &&
                        !empty($_FILES["image"]["name"])
                    )
                    {
                        $block_data["content"]
                            [$_REQUEST["slide_id"]]
                            ["image"] = Jaris\Files::addUpload(
                                $_FILES["image"],
                                "animated_blocks"
                            )
                        ;

                        chmod(
                            Jaris\Files::get(
                                $block_data["content"][$_REQUEST["slide_id"]]["image"],
                                "animated_blocks"
                            ),
                            0755
                        );

                        Jaris\Images::resize(
                            Jaris\Files::get(
                                $block_data["content"][$_REQUEST["slide_id"]]["image"],
                                "animated_blocks"
                            ),
                            $settings["width"]
                        );

                        $block_data["content"][$_REQUEST["slide_id"]]["uri"] =
                            $block_data["content"][$_REQUEST["slide_id"]]["image"]
                        ;

                        Jaris\Files::delete($current_image, "animated_blocks");
                    }

                    $block_data["content"] = serialize($block_data["content"]);

                    Jaris\Blocks::edit(
                        $_REQUEST["id"],
                        $_REQUEST["position"],
                        $block_data
                    );

                    Jaris\View::addMessage(t("Slide edited."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/animated-blocks/slides",
                            "animated_blocks"
                        ),
                        array(
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"]
                        )
                    );
                }
                elseif($required)
                {
                    Jaris\View::addMessage(t("Please provide the required fields."), "error");
                }
            }
            else
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
        }
        else if($_REQUEST["action"] == "delete")
        {
            if(isset($_REQUEST["btnYes"]))
            {
                if($block_data["content"][$_REQUEST["slide_id"]]["type"] == "image")
                {
                    Jaris\Files::delete(
                        $block_data["content"][$_REQUEST["slide_id"]]["image"],
                        "animated_blocks"
                    );
                }

                unset($block_data["content"][$_REQUEST["slide_id"]]);

                $block_data["content"] = serialize($block_data["content"]);

                Jaris\Blocks::edit($_REQUEST["id"], $_REQUEST["position"], $block_data);

                Jaris\View::addMessage(t("Slide removed."));

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
            else
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
        }
        else if($_REQUEST["action"] == "update")
        {
            if(isset($_REQUEST["btnSave"]))
            {
                foreach($_REQUEST["slide"] as $index => $slide)
                {
                    $block_data["content"][$slide]["order"] = $index;
                }

                $block_data["content"] = serialize($block_data["content"]);

                Jaris\Blocks::edit($_REQUEST["id"], $_REQUEST["position"], $block_data);

                Jaris\View::addMessage(t("Slide details updated."));

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
            else
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/animated-blocks/slides",
                        "animated_blocks"
                    ),
                    array(
                        "id" => $_REQUEST["id"],
                        "position" => $_REQUEST["position"]
                    )
                );
            }
        }

        if($_REQUEST["view"] == "add")
        {
            $parameters["name"] = "add-slide";
            $parameters["class"] = "add-slide";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/animated-blocks/slides",
                    "animated_blocks"
                )
            );
            $parameters["method"] = "post";

            $fields[] = array(
                "type" => "hidden",
                "name" => "action",
                "value" => "add"
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

            //Slideshow type
            $slide_type[t("Uri")] = "uri";
            $slide_type[t("Image Upload")] = "image";

            $fields[] = array(
                "type" => "radio",
                "name" => "slide_type",
                "id" => "slide_type",
                "value" => $slide_type,
                "checked" => isset($_REQUEST["slide_type"]) ?
                    $_REQUEST["slide_type"] : "image"
            );

            $fields[] = array(
                "type" => "uri",
                "name" => "slide_uri",
                "label" => t("Uri:"),
                "id" => "slide_uri",
                "description" => t("The uri of the slide, image or content.")
            );

            $fields[] = array(
                "type" => "file",
                "name" => "image",
                "label" => t("Image:"),
                "id" => "image",
                "valid_types" => "gif,jpg,jpeg,png",
                "description" => t("An image to display.")
            );

            $fields[] = array(
                "type" => "uri",
                "name" => "link",
                "label" => t("Link:"),
                "id" => "link",
                "description" => t("A link added over the slide.")
            );

            $fields[] = array(
                "type" => "text",
                "name" => "title",
                "label" => t("Title:"),
                "id" => "title",
                "description" => t("Optional title for the slide.")
            );

            $fields[] = array(
                "type" => "textarea",
                "name" => "description",
                "label" => t("Description:"),
                "id" => "slide_description",
                "description" => t("Optional description for the slide.")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnSave",
                "value" => t("Save")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
        else if($_REQUEST["view"] == "edit")
        {
            $parameters["name"] = "edit-slide";
            $parameters["class"] = "edit-slide";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/animated-blocks/slides",
                    "animated_blocks"
                )
            );
            $parameters["method"] = "post";

            $fields[] = array(
                "type" => "hidden",
                "name" => "action",
                "value" => "edit"
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

            $fields[] = array(
                "type" => "hidden",
                "name" => "slide_id",
                "value" => $_REQUEST["slide_id"]
            );

            //Slideshow type
            $slide_type[t("Uri")] = "uri";
            $slide_type[t("Image Upload")] = "image";

            $fields[] = array(
                "type" => "radio",
                "name" => "slide_type",
                "id" => "slide_type",
                "value" => $slide_type,
                "checked" => isset($_REQUEST["slide_type"]) ?
                    $_REQUEST["slide_type"]
                    :
                    $block_data["content"][$_REQUEST["slide_id"]]["type"]
            );

            $current_slide_type = isset($_REQUEST["slide_type"]) ?
                $_REQUEST["slide_type"]
                :
                $block_data["content"][$_REQUEST["slide_id"]]["type"]
            ;

            $fields[] = array(
                "type" => "uri",
                "name" => "slide_uri",
                "label" => t("Uri:"),
                "id" => "slide_uri",
                "value" => $block_data["content"][$_REQUEST["slide_id"]]["uri"],
                "description" => t("The uri of the slide, image or content.")
            );

            $image_url = Jaris\Uri::url(
                Jaris\Files::get(
                    $block_data["content"][$_REQUEST["slide_id"]]["image"],
                    "animated_blocks"
                )
            );

            if($current_slide_type == "image")
            {
                $fields[] = array(
                    "type" => "other",
                    "html_code" => "<div id=\"slide-current-image\" style=\"margin-top: 10px;\">"
                        . "<strong>" . t("Current image:") . "</strong>"
                        . "<hr />"
                        . "<a href=\"$image_url\" target=\"_blank\">"
                        . "<img width=\"300px\" src=\"" . $image_url . "\" />"
                        . "</a>"
                        . "</div>"
                );
            }

            $fields[] = array(
                "type" => "file",
                "name" => "image",
                "label" => t("New image:"),
                "id" => "image",
                "valid_types" => "gif,jpg,jpeg,png",
                "description" => t("An image to display.")
            );

            $fields[] = array(
                "type" => "uri",
                "name" => "link",
                "label" => t("Link:"),
                "id" => "link",
                "value" => $block_data["content"][$_REQUEST["slide_id"]]["link"],
                "description" => t("A link added over the slide.")
            );

            $fields[] = array(
                "type" => "text",
                "name" => "title",
                "label" => t("Title:"),
                "id" => "title",
                "value" => $block_data["content"][$_REQUEST["slide_id"]]["title"],
                "description" => t("Optional title for the slide.")
            );

            $fields[] = array(
                "type" => "textarea",
                "name" => "description",
                "label" => t("Description:"),
                "value" => $block_data["content"][$_REQUEST["slide_id"]]["description"],
                "id" => "slide_description",
                "description" => t("Optional description for the slide.")
            );

            $fields[] = array(
                "type" => "text",
                "name" => "order",
                "label" => t("Order:"),
                "id" => "order",
                "value" => $block_data["content"][$_REQUEST["slide_id"]]["order"],
                "description" => t("Numerical value to indicate the order in which the slide is displayed.")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnSave",
                "value" => t("Save")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
        else if($_REQUEST["view"] == "delete")
        {
            print "<form class=\"group-delete\" method=\"post\" action=\"" . Jaris\Uri::url(Jaris\Modules::getPageUri("admin/animated-blocks/slides", "animated_blocks")) . "\">
                <input type=\"hidden\" name=\"id\" value=\"" . $_REQUEST["id"] . "\" />
                <input type=\"hidden\" name=\"position\" value=\"" . $_REQUEST["position"] . "\" />
                <input type=\"hidden\" name=\"slide_id\" value=\"" . $_REQUEST["slide_id"] . "\" />
                <input type=\"hidden\" name=\"action\" value=\"delete\" />
                <br />
                <div>" . t("Are you sure you want to delete the slide?") . "
                <div><b>" . t("Uri:") . "</b> " . $block_data["content"][$_REQUEST["slide_id"]]["uri"] . "</div>
                </div>
                <input class=\"form-submit\" type=\"submit\" name=\"btnYes\" value=\"" . t("Yes") . "\" />
                <input class=\"form-submit\" type=\"submit\" name=\"btnNo\" value=\"" . t("No") . "\" />
                </form>";
        }
        else
        {
            Jaris\View::addTab(
                t("Add Slide"),
                Jaris\Modules::getPageUri(
                    "admin/animated-blocks/slides",
                    "animated_blocks"
                ),
                array(
                    "view" => "add",
                    "id" => $_REQUEST["id"],
                    "position" => $_REQUEST["position"]
                ),
                1
            );

            if(is_array($block_data["content"]) && count($block_data["content"]) > 0)
            {
                $block_data["content"] = Jaris\Data::sort($block_data["content"], "order");

                print "<form class=\"slides\" method=\"post\" action=\"" . Jaris\Uri::url(Jaris\Modules::getPageUri("admin/animated-blocks/slides", "animated_blocks"), array("action" => "update", "id" => $_REQUEST["id"], "position" => $_REQUEST["position"])) . "\" >";
                print "<input type=\"hidden\" name=\"id\" value=\"" . $_REQUEST["id"] . "\" />";
                print "<input type=\"hidden\" name=\"position\" value=\"" . $_REQUEST["position"] . "\" />";
                print "<table class=\"slides-list\">\n";

                print "<thead><tr>\n";

                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Uri") . "</td>\n";
                print "<td>" . t("Operation") . "</td>\n";

                print "</tr></thead>\n";

                print "<tbody>\n";

                foreach($block_data["content"] as $id => $fields)
                {
                    print "<tr>\n";

                    print "<td>";
                    print "<a class=\"sort-handle\"></a>";
                    print "<input type=\"hidden\" name=\"slide[]\" value=\"$id\" />";
                    print "<input type=\"hidden\" name=\"order[]\" "
                        . "value=\"{$fields['order']}\" />"
                    ;
                    print "</td>";

                    print "<td>"
                        . "{$fields['uri']}"
                        . "</td>\n"
                    ;

                    $edit_url = Jaris\Uri::url(
                        Jaris\Modules::getPageUri(
                            "admin/animated-blocks/slides",
                            "animated_blocks"
                        ),
                        array(
                            "view" => "edit",
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"],
                            "slide_id" => $id
                        )
                    );

                    $edit_text = t("Edit");

                    $delete_url = Jaris\Uri::url(
                        Jaris\Modules::getPageUri(
                            "admin/animated-blocks/slides",
                            "animated_blocks"
                        ),
                        array(
                            "view" => "delete",
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"],
                            "slide_id" => $id
                        )
                    );

                    $delete_text = t("Delete");

                    print "<td>
                        <a href=\"$edit_url\">$edit_text</a> <br />
                        <a href=\"$delete_url\">$delete_text</a>
                       </td>\n";

                    print "</tr>\n";
                }

                print "</tbody>\n";

                print "</table>\n";

                print "<input type=\"submit\" name=\"btnSave\" value=\"" . t("Save") . "\" /> &nbsp";
                print "<input type=\"submit\" name=\"btnCancel\" value=\"" . t("Cancel") . "\" />";
                print "</form>";
            }
            else
            {
                print t("No slides available");
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
