<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        print t("Duplicate Content");
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["duplicate_content"]);

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        if (Jaris\Pages::isSystem($_REQUEST["uri"])) {
            Jaris\View::addMessage(
                t("The content you was trying to duplicate is a system page."),
                "error"
            );

            Jaris\Uri::go("admin/pages");
        }

        $page_data = Jaris\Pages::get($_REQUEST["uri"]);
        $type = $page_data["type"];

        if (empty($page_data)) {
            Jaris\Uri::go("");
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("duplicate-page-$type")
        ) {
            $page_data["title"] = $_REQUEST["title"];
            $page_data["content"] = $_REQUEST["content"];

            $page_uri = $_REQUEST["uri"] = Jaris\Types::generateURI(
                $page_data["type"],
                $page_data["title"],
                Jaris\Authentication::currentUser()
            );

            $uri = $page_uri;

            if (Jaris\Pages::add($page_uri, $page_data, $uri)) {
                // Src files, images and blocks
                $files_src = Jaris\Pages\Files::getPath($_REQUEST["uri"]);
                $images_src = Jaris\Pages\Images::getPath($_REQUEST["uri"]);

                $files_path_src = str_replace("files.php", "files", $files_src);
                $images_path_src = str_replace("images.php", "images", $images_src);
                $blocks_path_src = Jaris\Pages::getPath($_REQUEST["uri"]) . "/blocks";

                // Dest files, images and blocks
                $files_dest = Jaris\Pages\Files::getPath($uri);
                $images_dest = Jaris\Pages\Images::getPath($uri);

                $files_path_dest = str_replace("files.php", "files", $files_dest);
                $images_path_dest = str_replace("images.php", "images", $images_dest);
                $blocks_path_dest = Jaris\Pages::getPath($uri) . "/blocks";

                if (file_exists($files_src)) {
                    copy($files_src, $files_dest);
                }

                if (file_exists($images_src)) {
                    copy($images_src, $images_dest);
                }

                if (is_dir($files_path_src)) {
                    Jaris\FileSystem::recursiveCopyDir(
                        $files_path_src,
                        $files_path_dest
                    );
                }

                if (is_dir($images_path_src)) {
                    Jaris\FileSystem::recursiveCopyDir(
                        $images_path_src,
                        $images_path_dest
                    );
                }

                if (is_dir($blocks_path_src)) {
                    Jaris\FileSystem::recursiveCopyDir(
                        $blocks_path_src,
                        $blocks_path_dest
                    );
                }

                Jaris\View::addMessage(
                    t("The page was successfully duplicated.")
                );

                Jaris\Uri::go("admin/pages/edit", ["uri" => $uri]);
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go($_REQUEST["uri"]);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        $parameters["name"] = "duplicate-page-$type";
        $parameters["class"] = "duplicate-page-$type";
        $parameters["action"] = Jaris\Uri::url("admin/pages/duplicate");
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "value" => $page_data["title"],
            "label" => Jaris\Types::getLabel($page_data["type"], "title_label"),
            "id" => "title",
            "required" => true,
            "description" => Jaris\Types::getLabel(
                $page_data["type"],
                "title_description"
            )
        ];

        $fields[] = [
            "type" => "textarea",
            "name" => "content",
            "value" => $page_data["content"],
            "label" => Jaris\Types::getLabel($page_data["type"], "content_label"),
            "id" => "content",
            "description" => Jaris\Types::getLabel(
                $page_data["type"],
                "content_description"
            )
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
