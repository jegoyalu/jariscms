<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the upload sqlite backup page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Upload Theme") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(
            !isset($_REQUEST["btnUpload"]) &&
            !isset($_REQUEST["btnCancel"])
        )
        {
            Jaris\View::addMessage(
                t("By uploading a theme you will override any existing system one with your version."),
                t("Warning")
            );
        }

        if(
            isset($_REQUEST["btnUpload"]) &&
            !Jaris\Forms::requiredFieldEmpty("theme-upload")
        )
        {
            if(class_exists("ZipArchive"))
            {
                $zip = new ZipArchive;

                $zip->open($_FILES["theme_file"]["tmp_name"]);

                // Remove all previous files.
                $theme_name = $zip->getNameIndex(0);

                if(is_dir(Jaris\Themes::getUploadPath() . $theme_name))
                {
                    Jaris\FileSystem::recursiveRemoveDir(
                        Jaris\Themes::getUploadPath() . $theme_name
                    );
                }

                $zip->extractTo(
                    Jaris\Themes::getUploadPath()
                );

                $zip->close();

                Jaris\View::addMessage(t("Theme uploaded."));

                t("Uploaded theme '{theme}'.");

                Jaris\Logger::info(
                    "Uploaded theme '{theme}'.",
                    array(
                        "theme" => $theme_name
                    )
                );
            }
            else
            {
                Jaris\View::addMessage(
                    t("ZipArchive extension not enabled. Could not extract zip file."),
                    "error"
                );
            }

            Jaris\Uri::go("admin/themes");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/themes");
        }

        $parameters["name"] = "theme-upload";
        $parameters["class"] = "theme-upload";
        $parameters["action"] = Jaris\Uri::url("admin/themes/upload");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "file",
            "label" => t("Theme file:"),
            "name" => "theme_file",
            "id" => "theme_file",
            "valid_types" => "zip",
            "description" => t("A valid theme zip file.")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnUpload",
            "value" => t("Upload")
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
