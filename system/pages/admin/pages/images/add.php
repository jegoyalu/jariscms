<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content images add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Image") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_images"));

        if(!isset($_REQUEST["uri"]))
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        //Check maximum permitted file upload have not exceed
        $type_settings = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));

        $current_group = Jaris\Authentication::currentUserGroup();

        $maximum_images = $type_settings["uploads"][$current_group]["maximum_images"] != "" ?
            $type_settings["uploads"][$current_group]["maximum_images"]
            :
            "-1"
        ;

        $image_count = count(Jaris\Pages\Images::getList($_REQUEST["uri"]));

        if($maximum_images == "0")
        {
            Jaris\View::addMessage(
                t("Image uploads not permitted for this content type.")
            );

            Jaris\Uri::go(
                "admin/pages/files",
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif($image_count >= $maximum_images && $maximum_images != "-1")
        {
            Jaris\View::addMessage(t("Maximum image uploads reached."));

            Jaris\Uri::go(
                "admin/pages/files",
                array("uri" => $_REQUEST["uri"])
            );
        }

        $arguments = array("uri" => $_REQUEST["uri"]);

        //Image compression configurations
        $image_compression = Jaris\Settings::get("image_compression", "main");
        $has_width_edit_permission = Jaris\Authentication::groupHasPermission(
            "edit_upload_width", Jaris\Authentication::currentUserGroup()
        );
        $max_width = Jaris\Settings::get("image_compression_maxwidth", "main");
        $image_quality = Jaris\Settings::get("image_compression_quality", "main");

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-image")
        )
        {
            $message = "";
            foreach($_FILES["image"]["name"] as $file_index => $file_name)
            {
                if($image_count >= $maximum_images && $maximum_images != "-1")
                    break;

                $file = array(
                    "name" => $file_name,
                    "tmp_name" => $_FILES["image"]["tmp_name"][$file_index],
                    "type" => $_FILES["image"]["type"][$file_index]
                );

                //Resize and compress image
                if($image_compression)
                {
                    //Get width override if user changed it
                    //from default and has permissions
                    if($has_width_edit_permission)
                    {
                        $max_width = $_REQUEST["max_width_override"];
                    }


                    $image_info = getimagesize($file["tmp_name"]);

                    if($image_info[0] > $max_width)
                    {
                        $image = Jaris\Images::get(
                            $file["tmp_name"],
                            intval($max_width)
                        );

                        switch($image_info["mime"])
                        {
                            case "image/jpeg":
                                imagejpeg(
                                    $image["binary_data"],
                                    $file["tmp_name"],
                                    $image_quality ?
                                        intval($image_quality) : 100
                                );
                                break;
                            case "image/png":
                                imagepng(
                                    $image["binary_data"],
                                    $file["tmp_name"]
                                );
                                break;
                            case "image/gif":
                                imagegif(
                                    $image["binary_data"],
                                    $file["tmp_name"]
                                );
                                break;
                        }
                    }
                }

                //Store image
                $message = Jaris\Pages\Images::add(
                    $file,
                    $_REQUEST["image"]["descriptions"][$file_index],
                    $_REQUEST["uri"]
                );

                if($message == "true")
                {
                    $image_count++;

                    continue;
                }
                else
                {
                    Jaris\View::addMessage($message, "error");
                    break;
                }
            }

            if($message == "true")
            {
                Jaris\View::addMessage(t("The image was successfully added."));
            }

            Jaris\Uri::go("admin/pages/images", $arguments);
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/pages/images", $arguments);
        }

        $parameters["name"] = "add-image";
        $parameters["class"] = "add-image";
        $parameters["action"] = Jaris\Uri::url("admin/pages/images/add");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        );

        $image_fields[] = array(
            "type" => "file",
            "name" => "image",
            "description_field" => true,
            "valid_types" => "gif,jpg,jpeg,png",
            "multiple" => true,
            "label" => t("Image file:"),
            "id" => "image",
            "required" => true
        );

        $fieldset[] = array("fields" => $image_fields);

        if($image_compression && $has_width_edit_permission)
        {
            $image_compression_fields[] = array(
                "type" => "text",
                "name" => "max_width_override",
                "value" => $max_width,
                "label" => t("Maximun width:"),
                "id" => "description",
                "description" => t("The width the image should be resized to.") .
                " (" . t("default:") . " $max_width" . ")"
            );

            $fieldset[] = array(
                "name" => "Image compression enabled",
                "fields" => $image_compression_fields
            );
        }

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
