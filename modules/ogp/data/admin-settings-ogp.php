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
        <?php print t("Open Graph Settings"); ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $ogp_settings = Jaris\Settings::getAll("ogp");

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("ogp-settings")
        )
        {
            $new_image = "";

            if(isset($_FILES["image"]) && file_exists($_FILES["image"]["tmp_name"]))
            {
                if($_FILES["image"]["type"] == "image/png" ||
                    $_FILES["image"]["type"] == "image/jpeg" ||
                    $_FILES["image"]["type"] == "image/pjpeg" ||
                    $_FILES["image"]["type"] == "image/gif"
                )
                {
                    $new_image = Jaris\Files::addUpload(
                        $_FILES["image"],
                        "ogp"
                    );

                    chmod(
                        Jaris\Files::get($new_image, "ogp"),
                        0755
                    );
                }
                else
                {
                    Jaris\View::addMessage(Jaris\System::errorMessage("image_file_type"), "error");
                }
            }

            if($new_image != "")
            {
                $current_image = $ogp_settings["current_image"];

                if(Jaris\Settings::save("current_image", $new_image, "ogp"))
                {
                    //Remove old original ogp
                    if($current_image != $new_image)
                    {
                        Jaris\Files::delete($current_image, "ogp");
                    }

                    Jaris\View::addMessage(t("Changes successfully saved."));

                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/settings/ogp",
                            "ogp"
                        )
                    );
                }
                else
                {
                    Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
                }
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/settings");
        }

        $parameters["name"] = "ogp-settings";
        $parameters["class"] = "ogp-settings";
        $parameters["enctype"] = "multipart/form-data";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/ogp",
                "ogp"
            )
        );
        $parameters["method"] = "post";

        if(isset($ogp_settings["current_image"]))
        {
            if(file_exists(Jaris\Files::get($ogp_settings["current_image"], "ogp")))
            {
                $fields[] = array(
                    "type" => "other",
                    "html_code" => "<div style=\"margin-top: 10px;\">"
                        . "<strong>" .
                        t("Current image:") .
                        "</strong>"
                        . "<hr />"
                        . '<div style="padding: 7px; border: solid 1px #000; background-color: #d3d3d3">'
                        . "<img width=\"300px\" src=\"" .
                        Jaris\Uri::url(
                            Jaris\Files::get(
                                $ogp_settings["current_image"],
                                "ogp"
                            )
                        )
                        ."\" />"
                        . "</div>"
                        . "</div>"
                );
            }
        }

        $fields[] = array(
            "type" => "file",
            "name" => "image",
            "label" => t("Default image file:"),
            "description" => t("Please upload a 200px X 200px image that will be used as default image if current section doesn't have one. If non is uploaded, a logo.png or logo.jpg image will be scanned from the current theme and used if found.")
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