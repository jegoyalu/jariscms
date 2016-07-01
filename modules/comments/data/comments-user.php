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
        <?php print t("My Comments") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_comments"));

        comments_clean_user_comments(Jaris\Authentication::currentUser());

        Jaris\View::addStyle(Jaris\Modules::directory("comments") . "styles/user.css");

        Jaris\View::addTab(
            t("Notifications"),
            Jaris\Modules::getPageUri("comments/notifications", "comments")
        );

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        if(
            Jaris\Sql::dbExists(
                "comments",
                comments_user_path(Jaris\Authentication::currentUser())
            )
        )
        {
            $comments_count = Jaris\Sql::countColumn(
                "comments",
                "comments",
                "id",
                "",
                comments_user_path(Jaris\Authentication::currentUser())
            );

            print "<h2>"
                . t("Total Comments:") . " "
                . $comments_count
                . "</h2>"
            ;

            $comments = Jaris\Sql::getDataList(
                "comments",
                "comments",
                $page - 1,
                10,
                "order by created_timestamp desc",
                "*",
                comments_user_path(Jaris\Authentication::currentUser())
            );

            Jaris\System::printNavigation(
                $comments_count,
                $page,
                "comments/user",
                "comments",
                10
            );

            foreach($comments as $data)
            {
                $page_data = Jaris\Pages::get($data["uri"]);
                $comment_data = comments_get($data["id"], $data["uri"]);

                print "<div class=\"comments-list\">\n";

                print "<div class=\"title\"><a title=\"{$page_data["title"]}\" href=\"" .
                    Jaris\Uri::url($data["uri"]) . "\">" .
                    $page_data["title"] .
                    "</a></div>\n"
                ;

                print "<div class=\"text\">\n";
                print $comment_data["comment_text"];

                $replies = Jaris\Sql::getDataList(
                    "comments",
                    "comments",
                    0,
                    5,
                    "where reply_to={$data['id']} order by created_timestamp desc",
                    "*",
                    comments_page_path($data["uri"])
                );

                foreach($replies as $reply_data)
                {
                    print "<h4>" . t("Recent replies to this comment") . "</h4>";
                    print "<div class=\"text\">\n";
                    print $reply_data["comment_text"];
                    print "</div>";
                }

                print "</div>";

                print "</div>\n";
            }

            if($comments_count <= 0)
            {
                Jaris\View::addMessage(t("No comments posted by you yet."));
            }

            Jaris\System::printNavigation(
                $comments_count,
                $page,
                "comments/user",
                "comments",
                10
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
