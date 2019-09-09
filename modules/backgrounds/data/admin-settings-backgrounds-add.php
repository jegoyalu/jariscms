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
        <?php print t("Add Background"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("backgrounds-add")
        )
        {
            if($_FILES["image"]["type"] == "image/png" ||
                $_FILES["image"]["type"] == "image/jpeg" ||
                $_FILES["image"]["type"] == "image/pjpeg" ||
                $_FILES["image"]["type"] == "image/gif"
            )
            {
                $fields["description"] = $_REQUEST["description"];
                $fields["image"] = Jaris\Files::addUpload(
                    $_FILES["image"],
                    "backgrounds"
                );

                $fields["element"] = trim($_REQUEST["element"]);
                $fields["top"] = intval($_REQUEST["top"]);
                $fields["position"] = $_REQUEST["position"];
                $fields["mode"] = $_REQUEST["mode"];
                $fields["attachment"] = $_REQUEST["attachment"];
                $fields["background_size"] = $_REQUEST["background_size"];
                $fields["background_color"] = $_REQUEST["background_color"];
                $fields["responsive"] = $_REQUEST["responsive"];
                $fields["max_width"] = $_REQUEST["max_width"];
                $fields["background_language"] = $_REQUEST["background_language"];
                $fields["display_rule"] = $_REQUEST["display_rule"];
                $fields["pages"] = $_REQUEST["pages"];

                if($fields["image"])
                {
                    chmod(
                        Jaris\Files::get($fields["image"], "backgrounds"),
                        0755
                    );

                    $backgrounds_settings = Jaris\Settings::getAll("backgrounds");

                    $backgrounds = unserialize(
                        $backgrounds_settings["backgrounds"]
                    );

                    if(!is_array($backgrounds))
                    {
                        $backgrounds = array();
                    }

                    $backgrounds[] = $fields;

                    if(
                        Jaris\Settings::save(
                            "backgrounds",
                            serialize($backgrounds),
                            "backgrounds"
                        )
                    )
                    {
                        Jaris\View::addMessage(t("Background successfully added."));

                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/settings/backgrounds",
                                "backgrounds"
                            )
                        );
                    }
                    else
                    {
                        Jaris\View::addMessage(
                            Jaris\System::errorMessage("write_error_data"),
                            "error"
                        );
                    }
                }
                else
                {
                    Jaris\View::addMessage(
                        t("The image could not be moved to files/backgrounds directory."),
                        "error"
                    );
                }
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("image_file_type"), "error");
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

        $parameters["name"] = "backgrounds-add";
        $parameters["class"] = "backgrounds-add";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/backgrounds/add",
                "backgrounds"
            )
        );
        $parameters["method"] = "post";

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Description:"),
            "value" => $_REQUEST["description"],
            "name" => "description",
            "id" => "description",
            "description" => t("Description of the background image like for example: January 2011 special product promotion."),
            "required" => true
        );

        $fields_main[] = array(
            "type" => "file",
            "name" => "image",
            "label" => t("Background image file:"),
            "id" => "image",
            "required" => true
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Element:"),
            "value" => $_REQUEST["element"],
            "name" => "element",
            "id" => "element",
            "description" => t("The css selector of an explicit element to put the background images.")
        );

        $fields_main[] = array(
            "type" => "text",
            "label" => t("Top position:"),
            "value" => $_REQUEST["top"],
            "name" => "top",
            "id" => "top",
            "description" => t("The top position of the background in pixels, for example 200. Default is 0")
        );

        $fields_main[] = array(
            "type" => "color",
            "label" => t("Background color:"),
            "value" => $_REQUEST["background_color"] ?
                $_REQUEST["background_color"]
                :
                "FFFFFF",
            "name" => "background_color",
            "id" => "background_color",
            "description" => t("The overall background color of the body.")
        );

        $fieldset[] = array("fields" => $fields_main);

        $position[t("Left")] = "left";
        $position[t("Center")] = "center";
        $position[t("Right")] = "right";

        $position_fields[] = array(
            "type" => "radio",
            "name" => "position",
            "id" => "position",
            "value" => $position,
            "checked" => $_REQUEST["position"] ?
                $_REQUEST["position"]
                :
                "center"
        );

        $fieldset[] = array(
            "name" => t("Position"),
            "fields" => $position_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $mode[t("Repeat")] = "repeat";
        $mode[t("No Repeat")] = "no-repeat";

        $mode_fields[] = array(
            "type" => "radio",
            "name" => "mode",
            "id" => "mode",
            "value" => $mode,
            "checked" => $_REQUEST["mode"] ?
                $_REQUEST["mode"]
                :
                "no-repeat"
        );

        $fieldset[] = array(
            "name" => t("Mode"),
            "fields" => $mode_fields,
            "collapsible" => true,
            "collapsed" => true
        );

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
                "fixed"
        );

        $fieldset[] = array(
            "name" => t("Attachment"),
            "fields" => $attachment_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $size_fields[] = array(
            "type" => "radio",
            "name" => "background_size",
            "id" => "background_size",
            "value" => array(
                "cover" => "cover",
                "auto" => "auto",
                "contain" => "contain"
            ),
            "checked" => $_REQUEST["background_size"] ?
                $_REQUEST["background_size"]
                :
                "cover"
        );

        $fieldset[] = array(
            "name" => t("Size"),
            "fields" => $size_fields,
            "collapsible" => true,
            "collapsed" => true
        );

        $responsive[t("Enable")] = true;
        $responsive[t("Disable")] = false;

        $fields_responsive[] = array(
            "type" => "radio",
            "checked" => $_REQUEST["responsive"] ?
                $_REQUEST["responsive"]
                :
                false,
            "name" => "responsive",
            "id" => "responsive",
            "value" => $responsive
        );

        $fields_responsive[] = array(
            "type" => "text",
            "name" => "max_width",
            "value" => $_REQUEST["max_width"] ? $_REQUEST["max_width"] : '0',
            "label" => t("Maximum width:"),
            "id" => "max_width",
            "description" => t("The maximum width for resized images. Default: 0 to disable this feature.")
        );

        $fieldset[] = array(
            "fields" => $fields_responsive,
            "name" => t("Responsive"),
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
            "checked" => $_REQUEST["background_language"] ?? ""
        );

        $fieldset[] = array(
            "fields" => $fields_language,
            "name" => t("Site language"),
            "description" => t("Select the language of the page in which backgrounds are displayed.")
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => "all_except_listed",
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $_REQUEST["pages"]
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
