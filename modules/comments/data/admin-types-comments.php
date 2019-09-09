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
        <?php print t("Comment Settings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_types", "edit_types"]);

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $comment_settings = comments_get_settings($_REQUEST["type"]);

        if (isset($_REQUEST["btnSave"])) {
            $data["enabled"] = $_REQUEST["enabled"];
            $data["ordering"] = $_REQUEST["ordering"];
            $data["replies"] = $_REQUEST["replies"];
            $data["maximun_characters"] = $_REQUEST["maximun_characters"];

            //Check if write is possible and continue to write settings
            if (Jaris\Settings::save($_REQUEST["type"], serialize($data), "comments")) {
                Jaris\View::addMessage(t("Your comment settings have been successfully saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/types/edit", ["type" => $_REQUEST["type"]]);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/types/edit", ["type" => $_REQUEST["type"]]);
        }

        Jaris\View::addTab(
            t("Edit Type"),
            "admin/types/edit",
            ["type" => $_REQUEST["type"]]
        );

        $parameters["name"] = "edit-comments-settings";
        $parameters["class"] = "edit-comments-settings";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/types/comments", "comments")
        );
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "type",
            "value" => $_REQUEST["type"]
        ];

        $enabled[t("Enable")] = true;
        $enabled[t("Disable")] = false;

        $fields[] = [
            "type" => "radio",
            "name" => "enabled",
            "id" => "enabled",
            "value" => $enabled,
            "checked" => $comment_settings["enabled"]
        ];

        $ordering[t("Ascending")] = "asc";
        $ordering[t("Descending")] = "desc";

        $fields[] = [
            "type" => "radio",
            "label" => "Ordering",
            "name" => "ordering",
            "id" => "ordering",
            "value" => $ordering,
            "checked" => $comment_settings["ordering"] ?
                $comment_settings["ordering"]
                :
                "asc"
        ];

        $replies[t("Cascade")] = "cascade";
        $replies[t("Linear")] = "linear";

        $fields[] = [
            "type" => "radio",
            "label" => "Replies Display Mode",
            "name" => "replies",
            "id" => "replies",
            "value" => $replies,
            "checked" => $comment_settings["replies"] ?
                $comment_settings["replies"]
                :
                "cascade",
            "description" => t("Cascade is slower but easier to associate with original comment while linear is faster but harder to read.")
        ];

        $fields[] = [
            "type" => "text",
            "name" => "maximun_characters",
            "label" => t("Maximun characters:"),
            "id" => "maximun_characters",
            "value" => $comment_settings["maximun_characters"],
            "description" => t("The maximun characters allowed per user post.")
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
