<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content files add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add File") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_files"]);

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        //Check maximum permitted file upload have not exceed
        $type_settings = Jaris\Types::get(
            Jaris\Pages::getType($_REQUEST["uri"])
        );

        $current_group = Jaris\Authentication::currentUserGroup();

        $maximum_files = $type_settings["uploads"][$current_group]["maximum_files"] != "" ?
            $type_settings["uploads"][$current_group]["maximum_files"]
            :
            "-1"
        ;
        $file_count = count(Jaris\Pages\Files::getList($_REQUEST["uri"]));

        if ($maximum_files == "0") {
            Jaris\View::addMessage(
                t("File uploads not permitted for this content type.")
            );

            Jaris\Uri::go(
                "admin/pages/files",
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif ($file_count >= $maximum_files && $maximum_files != "-1") {
            Jaris\View::addMessage(t("Maximum file uploads reached."));

            Jaris\Uri::go(
                "admin/pages/files",
                ["uri" => $_REQUEST["uri"]]
            );
        }

        $arguments = ["uri" => $_REQUEST["uri"]];

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-file")
        ) {
            $message = "";
            foreach ($_FILES["file"]["name"] as $file_index => $file_name) {
                if ($file_count >= $maximum_files && $maximum_files != "-1") {
                    break;
                }

                $file = [
                    "name" => $file_name,
                    "tmp_name" => $_FILES["file"]["tmp_name"][$file_index],
                    "type" => $_FILES["file"]["type"][$file_index]
                ];

                $message = Jaris\Pages\Files::add(
                    $file,
                    $_REQUEST["file"]["descriptions"][$file_index],
                    $_REQUEST["uri"]
                );

                if ($message == "true") {
                    $file_count++;

                    continue;
                } else {
                    Jaris\View::addMessage($message, "error");
                    break;
                }
            }

            if ($message == "true") {
                Jaris\View::addMessage(t("The file was successfully added."));
            }

            Jaris\Uri::go("admin/pages/files", $arguments);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/pages/files", $arguments);
        }

        $parameters["name"] = "add-file";
        $parameters["class"] = "add-file";
        $parameters["action"] = Jaris\Uri::url("admin/pages/files/add");
        $parameters["method"] = "post";
        $parameters["enctype"] = "multipart/form-data";

        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "file",
            "name" => "file",
            "description_field" => true,
            "multiple" => true,
            "label" => t("File:"),
            "id" => "file",
            "required" => true
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
