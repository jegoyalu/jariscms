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
        <?php print t("Upload Module") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("install_modules"));

        if(
            !isset($_REQUEST["btnUpload"]) &&
            !isset($_REQUEST["btnCancel"])
        )
        {
            Jaris\View::addMessage(
                t("By uploading a module you will override any existing system one with your version."),
                t("Warning")
            );
        }

        if(
            isset($_REQUEST["btnUpload"]) &&
            !Jaris\Forms::requiredFieldEmpty("module-upload")
        )
        {
            if(class_exists("ZipArchive"))
            {
                $zip = new ZipArchive;

                $zip->open($_FILES["module_file"]["tmp_name"]);

                // Remove all previous files.
                $module_name = $zip->getNameIndex(0);

                if(is_dir(Jaris\Modules::getUploadPath() . $module_name))
                {
                    Jaris\FileSystem::recursiveRemoveDir(
                        Jaris\Modules::getUploadPath() . $module_name
                    );
                }

                $zip->extractTo(
                    Jaris\Modules::getUploadPath()
                );

                $zip->close();

                Jaris\View::addMessage(t("Module uploaded."));

                t("Uploaded module '{module_name}'.");

                Jaris\Logger::info(
                    "Uploaded module '{module_name}'.",
                    array(
                        "module_name" => $module_name
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

            Jaris\Uri::go("admin/modules");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/modules");
        }

        $parameters["name"] = "module-upload";
        $parameters["class"] = "module-upload";
        $parameters["action"] = Jaris\Uri::url("admin/modules/upload");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = array(
            "type" => "file",
            "label" => t("Module file:"),
            "name" => "module_file",
            "id" => "module_file",
            "valid_types" => "zip",
            "description" => t("A valid module zip file.")
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
