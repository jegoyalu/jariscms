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
        <?php print t("Flagged Comments List") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["manage_comments_flags"]);

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }


        $flags_count = Jaris\Sql::countColumn(
            "comments",
            "comments",
            "id",
            "where flags > 0"
        );

        print "<h2>" . t("Total flags:") . " " . $flags_count . "</h2>";

        $flags = comments_get_flagged_list($page - 1);

        Jaris\System::printNavigation(
            $flags_count,
            $page,
            "admin/comments/flags",
            "comments"
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Page Title") . "</td>";
        print "<td>" . t("Comment") . "</td>";
        print "<td>" . t("User") . "</td>";
        print "<td>" . t("Added on") . "</td>";
        print "<td>" . t("Flags") . "</td>";
        print "<td>" . t("Actions") . "</td>";
        print "</tr>";
        print "</thead>";

        foreach ($flags as $data) {
            $comment_data = comments_get($data["id"], $data["uri"]);
            $page_data = Jaris\Pages::get($data["uri"]);

            print "<tr>";

            print "<td><a href=\"" . Jaris\Uri::url($data["uri"]) . "\">" . Jaris\System::evalPHP($page_data["title"]) . "</a></td>";

            print "<td>" . $comment_data["comment_text"] . "</td>";

            print "<td>" . $comment_data["user"] . "</td>";

            print "<td>" . date("m/d/Y", $comment_data["created_timestamp"]) . "</td>";

            print "<td>" . $data["flags"] . "</td>";

            $delete_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/comments/flags/delete",
                    "comments"
                ),
                [
                    "id" => $comment_data["id"],
                    "user" => $comment_data["user"],
                    "page" => $data["uri"]
                ]
            );

            $remove_flags_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/comments/flags/remove",
                    "comments"
                ),
                [
                    "id" => $comment_data["id"],
                    "user" => $comment_data["user"],
                    "page" => $data["uri"]
                ]
            );

            print "<td>" .
                "<a href=\"$delete_url\">" . t("Delete") . "</a><br />" .
                "<a href=\"$remove_flags_url\">" . t("Unflag") . "</a>" .
                "</td>"
            ;

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $flags_count,
            $page,
            "admin/comments/flags",
            "comments"
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
