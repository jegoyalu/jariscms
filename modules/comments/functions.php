<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * @note File that stores all hook functions.
 */

 Jaris\Signals\SignalHandler::listenWithParams(
     Jaris\Site::SIGNAL_PAGE_DATA,
     function (&$page_data) {
         global $comments_display;

         if (empty($page_data[0]["type"])) {
             return;
         }

         $comment_settings = comments_get_settings($page_data[0]["type"]);

         if ($comment_settings["enabled"]) {
             if (
                Jaris\Authentication::groupHasPermission(
                    "view_comments",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                 Jaris\View::addStyle(
                    Jaris\Modules::directory("comments")
                        . "styles/comments.css"
                );

                 Jaris\View::addSystemScript("optional/jquery.limit.js");

                 Jaris\View::addScript(
                    Jaris\Modules::directory("comments")
                        . "scripts/comments.js"
                );

                 $comments_display = true;
             }
         }
     }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_SCRIPTS,
    function (&$scripts, &$scripts_code) {
        global $comments_display;

        if ($comments_display) {
            $page_data = Jaris\Site::$page_data;

            $comment_settings = comments_get_settings($page_data[0]["type"]);

            $add_comment_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "add/comment",
                    "comments"
                )
            );

            $flag_comment_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "flag/comment",
                    "comments"
                )
            );

            $delete_comment_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "delete/comment",
                    "comments"
                )
            );

            $navigation_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "navigations/comment",
                    "comments"
                )
            );

            $page_uri = Jaris\Uri::get();
            $page_type = $page_data[0]["type"];
            $cascade = $comment_settings["replies"] == "cascade" ? "true" : "false";
            $maximum_characters = $comment_settings["maximun_characters"];

            $reply_text = t("reply");
            $cancel_text = t("cancel");
            $characters_text = t("characters left");

            $scripts_code .= <<<SCRIPT
<script>
$(document).ready(function(){
    var translations = new Array();
    translations["reply"] = "$reply_text";
    translations["cancel"] = "$cancel_text";
    translations["characters left"] = "$characters_text";

    $.commentsModule({
        add_comment_url: "$add_comment_url",
        flag_comment_url: "$flag_comment_url",
        delete_comment_url: "$delete_comment_url",
        navigation_url: "$navigation_url",
        page_uri: "$page_uri",
        page_type: "$page_type",
        cascade: $cascade,
        maximum_characters: $maximum_characters,
        translations: translations
    });
});
</script>

SCRIPT;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        if (empty($content_data["type"])) {
            return;
        }

        $comment_settings = comments_get_settings($content_data["type"]);

        if ($comment_settings["enabled"]) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "view_comments",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                $comments_content = comments_print_post();

                $comments_content .= "<h3 class=\"comments-head\">"
                    . t("Comments")
                    . "</h3>"
                ;

                if (Jaris\Authentication::isUserLogged()) {
                    $comments_content .= "<div class=\"comments-header-buttons\">";

                    if (Jaris\Authentication::isAdminLogged()) {
                        $comments_content .= "<a class=\"button subscribe-all\" href=\"" .
                            Jaris\Uri::url(
                                Jaris\Modules::getPageUri("comments/subscribe-all", "comments"),
                                ["uri" => Jaris\Uri::get()]
                            ) . "\" title=\"" .
                            t("subscribe all users that have participated on the comments to receive notifications") .
                            "\">" . t("Subscribe All") . "</a>"
                        ;
                    }

                    if (
                        comments_notifications_is_subscribed(
                            Jaris\Authentication::currentUser(),
                            Jaris\Uri::get()
                        )
                    ) {
                        $comments_content .= "<a class=\"button subscribe\" href=\"" .
                            Jaris\Uri::url(
                                Jaris\Modules::getPageUri("comments/unsubscribe", "comments"),
                                ["uri" => Jaris\Uri::get()]
                            ) . "\" title=\"" .
                            t("do not receive e-mail notifications of new comments") .
                            "\">" . t("Unsubscribe") . "</a>"
                        ;
                    } else {
                        $comments_content .= "<a class=\"button unsubscribe\" href=\"" .
                            Jaris\Uri::url(
                                Jaris\Modules::getPageUri("comments/subscribe", "comments"),
                                ["uri" => Jaris\Uri::get()]
                            ) . "\" title=\"" .
                            t("receive e-mail notifications of new comments") .
                            "\">" . t("Subscribe") . "</a>"
                        ;
                    }

                    $comments_content .= "<div style=\"clear: both;\"></div>";
                    $comments_content .= "</div>";
                }

                $comments_content .= "<div id=\"comments\"></div>";

                $content .= $comments_content;

                $content_data["comments_content"] = $comments_content;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Groups::SIGNAL_SET_GROUP_PERMISSION,
    function (&$permissions, $group) {
        if ($group != "guest") {
            $comments["view_comments"] = t("View");
            $comments["add_comments"] = t("Add");
            $comments["edit_comments"] = t("Edit");
            $comments["delete_comments"] = t("Delete");
            $comments["flag_comments"] = t("Flag");
            $comments["manage_comments_flags"] = t("Manage Flags");

            $permissions[t("Comments")] = $comments;
        } else {
            $comments["view_comments"] = t("View");
            $comments["flag_comments"] = t("Flag");

            $permissions[t("Comments")] = $comments;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function (&$sections) {
        $group = Jaris\Authentication::currentUserGroup();

        $title = t("Content");

        foreach ($sections as $index => $sub_section) {
            if ($sub_section["title"] == $title) {
                if (
                    Jaris\Authentication::groupHasPermission(
                        "manage_comments_flags",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    $sub_section["sub_sections"][] = [
                        "title" => t("Manage Comment Flags"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/comments/flags",
                                "comments"
                            )
                        ),
                        "description" => t("To see which comments has been flagged and delete them.")
                    ];

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function (&$page, &$page_path) {
        $fields["uri"] = $page;

        Jaris\Sql::escapeArray($fields);

        //Delete from system db
        $db_system = Jaris\Sql::open("comments");
        $delete_system = "delete from comments where uri='{$fields['uri']}'";
        Jaris\Sql::query($delete_system, $db_system);
        Jaris\Sql::close($db_system);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Users::SIGNAL_PRINT_USER_PAGE,
    function (&$content, &$tabs) {
        if (
            Jaris\Authentication::groupHasPermission(
                "add_comments",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            $tabs[t("Comments")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "comments/user",
                    "comments"
                )
            ];
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/types/edit") {
            $tabs_array[0][t("Comments")] = [
                "uri" => Jaris\Modules::getPageUri("admin/types/comments", "comments"),
                "arguments" => ["type" => $_REQUEST["type"]]
            ];
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_CRONJOB,
    function () {
        $db = Jaris\Sql::open("comments");

        // Get the count of comments in order to open/close database while sending notifications
        // instead of letting the db file open
        $select = "select count(id) as comments_count from comments where notification = 0";
        $results = Jaris\Sql::query($select, $db);
        $data = Jaris\Sql::fetchArray($results);
        $comments_count = $data["comments_count"];
        unset($results);
        Jaris\Sql::close($db);

        for ($comment = 0; $comment < $comments_count; $comment++) {
            $db = Jaris\Sql::open("comments");

            $result = Jaris\Sql::query(
                "select * from comments where notification = 0 order by id asc limit 0,1",
                $db
            );

            $data = Jaris\Sql::fetchArray($result);

            unset($result);

            Jaris\Sql::close($db);

            $page_data = Jaris\Pages::get($data["uri"]);

            // In case this comments is a reply, retrieve the username it replied to
            $db_page_comments = Jaris\Sql::open(
                "comments",
                comments_page_path($data["uri"])
            );

            $result_page_comments = Jaris\Sql::query(
                "select * from comments where id={$data["id"]}",
                $db_page_comments
            );

            $reply_to = "";
            $data_comment = Jaris\Sql::fetchArray($result_page_comments);
            unset($result_page_comments);

            if ($data_comment["reply_to"] > 0) {
                $result_page_comments = Jaris\Sql::query(
                    "select * from comments where id={$data_comment["reply_to"]}",
                    $db_page_comments
                );

                $data_comment_reply = Jaris\Sql::fetchArray(
                    $result_page_comments
                );

                unset($result_page_comments);

                $reply_to = $data_comment_reply["user"];
            }

            Jaris\Sql::close($db_page_comments);

            $comment_user_data = Jaris\Users::get($data_comment["user"]);

            // Send e-mail notification to all subscribers
            $db_subscribers = Jaris\Sql::open(
                "comments_subscribers",
                comments_page_path($data["uri"])
            );

            $result_subscribers = Jaris\Sql::query(
                "select count(user) as users_count from comments_subscribers where subscribed=1",
                $db_subscribers
            );

            $data_subscribers = Jaris\Sql::fetchArray($result_subscribers);

            $subscribers_count = $data_subscribers["users_count"];

            Jaris\Sql::close($db_subscribers);

            for ($subscriber = 0; $subscriber < $subscribers_count; $subscriber++) {
                $db_subscribers = Jaris\Sql::open(
                    "comments_subscribers",
                    comments_page_path($data["uri"])
                );

                $result_subscribers = Jaris\Sql::query(
                    "select * from comments_subscribers where subscribed=1 limit $subscriber,1",
                    $db_subscribers
                );

                $data_subscribers = Jaris\Sql::fetchArray($result_subscribers);

                Jaris\Sql::close($db_subscribers);

                $user_data = Jaris\Users::get($data_subscribers["user"]);

                $notification_type = isset($user_data["comments_notification"]) ?
                    $user_data["comments_notification"] :
                    "all"
                ;

                $notify = true;

                if ($notification_type == "replies") {
                    if ($reply_to != $data_subscribers["user"]) {
                        $notify = false;
                    }
                } elseif ($notification_type == "none") {
                    $notify = false;
                }

                // Notifications to content author are sent from add/comment
                if ($page_data["author"] == $data_subscribers["user"]) {
                    $notify = false;
                }

                if ($data_comment["user"] == $data_subscribers["user"]) {
                    $notify = false;
                }

                if ($notify) {
                    $to = [];
                    $to[$user_data["name"]] = $user_data["email"];

                    $subject = t("A new comment has been posted on:")
                        . " "
                        . $page_data["title"]
                    ;

                    if ($notification_type == "replies") {
                        $subject = t("A new reply has been posted on:")
                            . " "
                            . $page_data["title"]
                        ;
                    }

                    $html_message = t("A user posted the following comment:") . "<br /><br />";

                    if ($notification_type == "replies") {
                        $html_message = t("A user replied with the following comment:")
                            . "<br /><br />"
                        ;
                    }

                    $html_message .= "<b>"
                        . $comment_user_data["name"]
                        . "</b>: <br />"
                    ;

                    $html_message .= "<i>"
                        . Jaris\Util::stripHTMLTags(
                            $data_comment["comment_text"]
                        ) . "</i><br /><br />"
                    ;

                    $html_message .= t("To reply or view the post click on the following link:")
                        . "<br />"
                    ;

                    $html_message .= "<a target=\"_blank\" href=\"" .
                        Jaris\Uri::url(
                            "admin/user",
                            ["return" => $data["uri"]]
                        ) . "\">" . Jaris\Uri::url($data["uri"]) . "</a>"
                    ;

                    Jaris\Mail::send($to, $subject, $html_message);
                }
            }

            $db = Jaris\Sql::open("comments");

            Jaris\Sql::query(
                "update comments set notification = 1 where id={$data["id"]} and uri='{$data["uri"]}'",
                $db
            );

            Jaris\Sql::close($db);
        }
    }
);
