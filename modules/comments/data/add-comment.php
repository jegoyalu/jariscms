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
        Add comment
    field;

    field: content
    <?php
        if (isset($_REQUEST["s"]) && Jaris\Authentication::currentUser() == "Guest") {
            session_destroy();
            session_id($_REQUEST["s"]);
            session_start();
        }

        Jaris\Authentication::protectedPage(["add_comments"]);

        if (
            isset($_REQUEST["comment"]) &&
            isset($_REQUEST["page"]) &&
            isset($_REQUEST["type"])) {
            $type_settings = comments_get_settings($_REQUEST["type"]);

            if ($type_settings["enabled"]) {
                $comment = substr(
                    Jaris\Util::stripHTMLTags($_REQUEST["comment"]),
                    0,
                    $type_settings["maximun_characters"]
                );

                $page_data = Jaris\Pages::get($_REQUEST["page"]);

                if (
                    trim($comment) != "" &&
                    $page_data &&
                    $page_data["type"] == $_REQUEST["type"]
                ) {
                    $user_data = Jaris\Users::get($page_data["author"]);

                    $id = comments_add(
                        $comment,
                        $_REQUEST["page"],
                        $_REQUEST["rid"]
                    );

                    //Send poster a new comment notification
                    if (
                        comments_get_notifications_type($page_data["author"]) != "none" &&
                        Jaris\Authentication::currentUser() != $page_data["author"]
                    ) {
                        $comment_user_data = Jaris\Users::get(
                            Jaris\Authentication::currentUser()
                        );

                        $comment_name = trim($comment_user_data["name"]) != "" ?
                            $comment_user_data["name"]
                            :
                            Jaris\Authentication::currentUser()
                        ;

                        $to[$user_data["name"]] = $user_data["email"];
                        $subject = t("You have a new comment on") . " " . $page_data["title"];

                        $html_message = t("A user posted the following comment on your post:") . "<br /><br />";
                        $html_message .= "<b>" . $comment_name  . "</b>:<br />";
                        $html_message .= "<i>" . $comment . "</i><br /><br />";

                        $html_message .= t("To reply or view your original post click on the following link:") . "<br />";
                        $html_message .= "<a target=\"_blank\" href=\"" .
                            Jaris\Uri::url(
                                "admin/user",
                                ["return" => $_REQUEST["page"]]
                            ) . "\">" . Jaris\Uri::url($_REQUEST["page"]) . "</a>"
                        ;

                        Jaris\Mail::send($to, $subject, $html_message);
                    }

                    $data = comments_get($id, $_REQUEST["page"]);

                    print comments_theme(
                        $data,
                        $_REQUEST["page"],
                        $_REQUEST["type"],
                        $type_settings["replies"] == "cascade" ? true : false
                    );
                }
            }
        }
    ?>
    field;

    field: rendering_mode
        api
    field;

    field: is_system
        1
    field;
row;
