<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Multiple Background"); ?>
    field;

    field: content
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".navigation-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".navigation-list tbody tr td a.delete").click(function() {
                $(this).parent().parent().fadeOut(1000, function() {
                    $(this).remove();
                });
            });
        });
    </script>

    <style>
        .navigation-list tbody tr:hover
        {
            background-color: #d3d3d3;
        }
    </style>
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        $background = $backgrounds[intval($_REQUEST["id"])];
        $background["images"] = unserialize($background["images"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("backgrounds-multi-edit")
        )
        {
            $fields = $background;

            //Delete removed images
            foreach($fields["images"] as $image)
            {
                if(!in_array($image, $_REQUEST["images_list"]))
                {
                    Jaris\Files::delete($image, "backgrounds");
                }
            }

            $fields["images"] = $_REQUEST["images_list"];

            //Add new images
            if(is_array($_FILES["images"]["name"]))
            {
                foreach($_FILES["images"]["name"] as $file_index => $file_name)
                {
                    if($_FILES["images"]["type"][$file_index] == "image/png" ||
                        $_FILES["images"]["type"][$file_index] == "image/jpeg" ||
                        $_FILES["images"]["type"][$file_index] == "image/pjpeg" ||
                        $_FILES["images"]["type"][$file_index] == "image/gif"
                    )
                        $file = array(
                            "name" => $file_name,
                            "tmp_name" => $_FILES["images"]["tmp_name"][$file_index]
                        );

                        $fields["images"][] = Jaris\Files::addUpload(
                            $file,
                            "backgrounds"
                        );
                }
            }

            //Chmod all uploaded image files to 0755
            foreach($fields["images"] as $image_file)
            {
                chmod(Jaris\Files::get($image_file, "backgrounds"), 0755);
            }

            $fields["multi"] = true;
            $fields["description"] = $_REQUEST["description"];
            $fields["element"] = trim($_REQUEST["element"]);
            $fields["top"] = intval($_REQUEST["top"]);
            $fields["attachment"] = $_REQUEST["attachment"];
            $fields["fade_speed"] = intval($_REQUEST["fade_speed"]);
            $fields["rotation_speed"] = intval($_REQUEST["rotation_speed"]);

            $fields["images"] = is_array($fields["images"]) ?
                serialize($fields["images"])
                :
                false
            ;

            $fields["stretch"] = intval($_REQUEST["stretch"]);
            $fields["responsive_stretch"] = intval($_REQUEST["responsive_stretch"]);
            $fields["min_width"] = intval($_REQUEST["min_width"]);
            $fields["min_height"] = intval($_REQUEST["min_height"]);
            $fields["max_width"] = intval($_REQUEST["max_width"]);
            $fields["max_height"] = intval($_REQUEST["max_height"]);
            $fields["centerx"] = intval($_REQUEST["centerx"]);
            $fields["centery"] = intval($_REQUEST["centery"]);
            $fields["background_language"] = $_REQUEST["background_language"];
            $fields["display_rule"] = $_REQUEST["display_rule"];
            $fields["pages"] = $_REQUEST["pages"];

            $backgrounds[$_REQUEST["id"]] = $fields;

            if(Jaris\Settings::save("backgrounds", serialize($backgrounds), "backgrounds"))
            {
                Jaris\View::addMessage(t("Changes successfully saved."));

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/settings/backgrounds/multi/edit",
                        "backgrounds"
                    ),
                    array("id" => $_REQUEST["id"])
                );
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            //Uninitialize fields variable to not
            //conflict with form generation below
            $fields = array();
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/backgrounds",
                    "backgrounds"
                )
            );
        }

        $parameters["name"] = "backgrounds-multi-edit";
        $parameters["class"] = "backgrounds-multi-edit";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/multi/edit",
                "backgrounds"
            )
        );
        $parameters["method"] = "post";

        $fields_main[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Description:"),
            "value" => $_REQUEST["description"] ?
                $_REQUEST["description"]
                :
                $background["description"],
            "name" => "description",
            "id" => "description",
            "description" => t("Description of the background image like for example: January 2011 special product promotion."),
            "required" => true
        );

        $images = "<table class=\"navigation-list\">";
        $images .= "<thead>";
        $images .= "<tr>";
        $images .= "<td>" . t("Order") . "</td>";
        $images .= "<td>" . t("Image") . "</td>";
        $images .= "<td>" . t("Action") . "</td>";
        $images .= "</tr>";
        $images .= "</thead>";

        $images .= "<tbody>";
        if(is_array($background["images"]))
        {
            foreach($background["images"] as $image)
            {
                $images .= "<tr>";

                $images .= "<td><a class=\"sort-handle\"></a></td>";

                $images .= "<td>
                    <input type=\"hidden\" name=\"images_list[]\" value=\"$image\"  />
                    <img width=\"150px\" src=\"" . Jaris\Uri::url(Jaris\Files::get($image, "backgrounds")) . "\" />
                </td>";

                $images .= "<td><a class=\"delete\" style=\"cursor: pointer\">" .
                    t("Delete") .
                    "</a></td>"
                ;

                $images .= "</tr>";
            }
        }
        $images .= "</tbody>";

        $images .= "</table>";

        $fields_main[] = array(
            "type" => "file",
            "name" => "images",
            "multiple" => true,
            "valid_types" => "gif,jpg,jpeg,png",
            "label" => t("Background images:"),
            "id" => "images"
        );

        $fields_main[] = array(
            "type" => "other",
            "html_code" => "<div style=\"margin-top: 10px;\"><strong>" .
                t("Current images:") .
                "</strong><hr />$images</div>"
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Element:"),
            "value" => $_REQUEST["element"] ?
                $_REQUEST["element"]
                :
                $background["element"],
            "name" => "element",
            "id" => "element",
            "description" => t("The css selector of an explicit element to put the background images.")
        );

        $top = isset($_REQUEST["top"]) ?
            $_REQUEST["top"]
            :
            $background["top"]
        ;

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Top position:"),
            "value" => $top,
            "name" => "top",
            "id" => "top",
            "description" => t("The top position of the background in pixels, for example 200. Default is 0")
        );

        $fields_main[] = array(
            "type" => "text",
            "name" => "fade_speed",
            "value" => $_REQUEST["fade_speed"] ?
                $_REQUEST["fade_speed"]
                :
                $background["fade_speed"],
            "label" => t("Fade speed:"),
            "id" => "fade_speed",
            "required" => true,
            "description" => t("The speed of the fade effect in milliseconds.")
        );

        $fields_main[] = array(
            "type" => "text",
            "name" => "rotation_speed",
            "value" => $_REQUEST["rotation_speed"] ?
                $_REQUEST["rotation_speed"]
                :
                $background["rotation_speed"],
            "label" => t("Rotation speed:"),
            "id" => "rotation_speed",
            "required" => true,
            "description" => t("The time in milliseconds an image is displayed before changing to the next one.")
        );

        $fieldset[] = array("fields" => $fields_main);

        $attachment[t("Scroll")] = "scroll";
        $attachment[t("Fixed")] = "fixed";

        $attachment_fields[] = array(
            "type" => "radio",
            "name" => "attachment",
            "id" => "attachment",
            "value" => $attachment,
            "checked" => $_REQUEST["attachment"] ?
                $_REQUEST["attachment"]
                :
                $background["attachment"]
        );

        $fieldset[] = array(
            "name" => t("Attachment"),
            "fields" => $attachment_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $stretch[t("No")] = 0;
        $stretch[t("Yes")] = 1;

        $stretch_fields[] = array(
            "type" => "radio",
            "name" => "stretch",
            "id" => "stretch",
            "value" => $stretch,
            "checked" => $_REQUEST["stretch"] ?
                $_REQUEST["stretch"]
                :
                $background["stretch"]
        );

        $stretch_fields[] = array(
            "type" => "radio",
            "name" => "responsive_stretch",
            "id" => "responsive_stretch",
            "label" => t("Responsive streching?"),
            "value" => $stretch,
            "checked" => $_REQUEST["responsive_stretch"] ?
                $_REQUEST["responsive_stretch"]
                :
                $background["responsive_stretch"],
            "description" => t("When enabled the image will be stretched according to window size")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "min_width",
            "value" => $_REQUEST["min_width"] ?
                $_REQUEST["min_width"]
                :
                $background["min_width"],
            "label" => t("Minimum width:"),
            "id" => "min_width",
            "required" => true,
            "description" => t("The minimum width for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "min_height",
            "value" => $_REQUEST["min_height"] ?
                $_REQUEST["min_height"]
                :
                $background["min_height"],
            "label" => t("Minimum height:"),
            "id" => "min_height",
            "required" => true,
            "description" => t("The minimum height for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "max_width",
            "value" => $_REQUEST["max_width"] ?
                $_REQUEST["max_width"]
                :
                $background["max_width"],
            "label" => t("Maximum width:"),
            "id" => "max_width",
            "required" => true,
            "description" => t("The maximum width for resized images. Default: 0 to disable this feature.")
        );

        $stretch_fields[] = array(
            "type" => "text",
            "name" => "max_height",
            "value" => $_REQUEST["max_height"] ?
                $_REQUEST["max_height"]
                :
                $background["max_height"],
            "label" => t("Maximum height:"),
            "id" => "max_height",
            "required" => true,
            "description" => t("The maximum height for resized images. Default: 0 to disable this feature.")
        );

        $fieldset[] = array(
            "name" => t("Stretch Image"),
            "fields" => $stretch_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $center_horizontally[t("No")] = 0;
        $center_horizontally[t("Yes")] = 1;

        $center_horizontally_fields[] = array(
            "type" => "radio",
            "name" => "centerx",
            "id" => "centerx",
            "value" => $center_horizontally,
            "checked" => $_REQUEST["centerx"] ?
                $_REQUEST["centerx"]
                :
                $background["centerx"]
        );

        $fieldset[] = array(
            "name" => t("Center Image Horizontally"),
            "fields" => $center_horizontally_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $center_vertically[t("No")] = 0;
        $center_vertically[t("Yes")] = 1;

        $center_vertically_fields[] = array(
            "type" => "radio",
            "name" => "centery",
            "id" => "centery",
            "value" => $center_vertically,
            "checked" => $_REQUEST["centery"] ?
                $_REQUEST["centery"]
                :
                $background["centery"]
        );

        $fieldset[] = array(
            "name" => t("Center Image Vertically"),
            "fields" => $center_vertically_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $languages = array(
            t("All") => ""
        );

        foreach(Jaris\Language::getInstalled() as $lang_code => $lang_name)
        {
            $languages[t($lang_name)] = $lang_code;
        }

        $fields_language[] = array(
            "type" => "radio",
            "name" => "background_language",
            "value" => $languages,
            "checked" => $_REQUEST["background_language"] ?? $background["background_language"]
        );

        $fieldset[] = array(
            "fields" => $fields_language,
            "name" => t("Site language"),
            "description" => t("Select the language of the page in which backgrounds are displayed.")
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $display_rule = isset($_REQUEST["display_rule"]) ?
            $_REQUEST["display_rule"]
            :
            $background["display_rule"]
        ;

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => $display_rule,
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $_REQUEST["pages"] ?
                $_REQUEST["pages"]
                :
                $background["pages"]
        );

        $fieldset[] = array(
            "fields" => $fields_pages,
            "name" => t("Pages to display"),
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
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
    ?>
    field;

    field: is_system
        1
    field;
row;


