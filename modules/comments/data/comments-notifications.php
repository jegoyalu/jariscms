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
        <?php print t("Comments E-mail Notifications") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_comments"]);

        if (isset($_REQUEST["btnSave"])) {
            if (comments_set_notifications_type($_REQUEST["type"], Jaris\Authentication::currentUser())) {
                Jaris\View::addMessage(t("Comments notification settings successfully saved."));
            } else {
                Jaris\View::addMessage(
                    t("Could not save comments notification settings. Try again later."),
                    "error"
                );
            }

            Jaris\Uri::go(Jaris\Modules::getPageUri("comments/user", "comments"));
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(Jaris\Modules::getPageUri("comments/user", "comments"));
        }

        $parameters["name"] = "comments-notifications";
        $parameters["class"] = "comments-notifications";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("comments/notifications", "comments")
        );
        $parameters["method"] = "post";

        $current_notification_type = comments_get_notifications_type(
            Jaris\Authentication::currentUser()
        );

        $notification_types = [
            "all" => [
                "title" => t("All"),
                "description" => t("Receive all new comments from a thread you have participated.") . "<br /><br />"
            ],
            "replies" => [
                "title" => t("Replies"),
                "description" => t("Just receives replies to comments you have posted or new comments from content you have created.") . "<br /><br />"
            ],
            "none" => [
                "title" => t("None"),
                "description" => t("Don't receive notifications.")
            ]
        ];

        $fields = [];

        foreach ($notification_types as $machine_name => $fields_data) {
            $fields[] = [
                "type" => "radio",
                "checked" => $machine_name == $current_notification_type ?
                    true
                    :
                    false,
                "name" => "type",
                "description" => $fields_data["description"],
                "value" => [$fields_data["title"] => $machine_name]
            ];
        }

        $fieldset[] = [
            "fields" => $fields,
            "name" => t("Type of notifications to receive")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_other];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
