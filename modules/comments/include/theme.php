<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * @file Has all the theming functions needed to display a comment.
 */

/**
 * Prepares the content that is going to be displayed
 *
 *
 * @param $content Array that contains all the page data content.
 *
 * @return String with the content preformatted.
 */
function comments_theme($comment_data, $page, $type, $include_replies=false)
{
    $theme = Jaris\Site::$theme;
    $theme_path = Jaris\Site::$theme_path;

    $id = $comment_data["id"];
    $user = $comment_data["user"];
    $content = $comment_data["comment_text"];
    $created_timestamp = $comment_data["created_timestamp"];
    $edited_timestamp = $comment_data["edited_timestamp"];
    $reply_to = $comment_data["reply_to"];
    $flags = $comment_data["flags"];
    $replies = "";

    $user_data = Jaris\Users::get($user);

    if ($reply_to) {
        $reply_to_data = comments_get($reply_to, $page);
        $reply_to_user = $reply_to_data["user"];
        $reply_user_data = Jaris\Users::get($reply_to_data["user"]);
    }

    $flag_url = "";
    if (Jaris\Authentication::groupHasPermission("flag_comments", Jaris\Authentication::currentUserGroup())) {
        $flag_url = "<a id=\"comment-flag-$id-$user\" class=\"comment-flag-link\">" . t("flag") . "</a>";
    }

    $reply_url = "";
    if (Jaris\Authentication::groupHasPermission("add_comments", Jaris\Authentication::currentUserGroup())) {
        $reply_url = "<a id=\"comment-reply-$id-$user\" class=\"comment-reply-link\">" . t("reply") . "</a>";
    }

    $delete_url = "";
    if (Jaris\Authentication::groupHasPermission("delete_comments", Jaris\Authentication::currentUserGroup())) {
        if (Jaris\Authentication::isAdminLogged() || Jaris\Authentication::currentUser() == $user) {
            $delete_url = "<a id=\"comment-delete-$id-$user\" class=\"comment-delete-link\">" . t("delete") . "</a>";
        }
    }

    if ($include_replies) {
        $replies_list = comments_get_replies($comment_data["id"], $page);

        foreach ($replies_list as $reply_comment) {
            $replies .= comments_theme(
                $reply_comment,
                $page,
                $type,
                $include_replies
            );
        }
    }

    $comment = "";

    ob_start();
    include(comments_template_path($page, $type));
    $comment .= ob_get_contents();
    ob_end_clean();

    return $comment;
}

/**
 * Search for the best comments template match
 *
 * @param $page The page uri that is going to be displayed.
 * @param $type The type machine name used.
 *
 * @return string The page file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/comments-uri.php
 *      themes/theme/comments.php
 */
function comments_template_path($page, $type) : string
{
    $theme = Jaris\Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_page = Jaris\Themes::directory($theme) . "comments-" . $page . ".php";
    $content_type = Jaris\Themes::directory($theme) . "comments-" . $type . ".php";
    $default_page = Jaris\Themes::directory($theme) . "comments.php";

    $template_path = "";

    if (file_exists($current_page)) {
        $template_path = $current_page;
    } elseif (file_exists($content_type)) {
        $template_path = $content_type;
    } elseif (file_exists($default_page)) {
        $template_path = $default_page;
    } else {
        $template_path = Jaris\Modules::directory("comments") . "templates/comments.php";
    }

    //Call content_template hook before returning the template to use
    Jaris\Modules::hook("hook_comments_template", $page, $type, $template_path);

    return $template_path;
}

function comments_print(
    $page,
    $type,
    $page_number = 1,
    $ordering="asc",
    $replies="cascade"
) {
    $comments_content = "<div id=\"comments\">";

    $ordering = $ordering != "desc" ? "asc": "desc";

    $include_replies = $replies == "linear" ? true : false;

    $comments = comments_get_list(
        $page,
        $page_number - 1,
        10,
        $ordering,
        $include_replies
    );

    foreach ($comments as $comment_data) {
        $comments_content .= comments_theme(
            $comment_data,
            $page,
            $type,
            $replies == "linear" ? false : true
        );
    }

    $count_where = $replies == "linear" ? "" : "where reply_to is null";

    $comments_count = Jaris\Sql::countColumn(
        "comments",
        "comments",
        "id",
        $count_where,
        comments_page_path($page)
    );

    ob_start();
    comments_print_navigation($comments_count, $page_number, $page, 10);
    $comments_content .= ob_get_contents();
    ob_end_clean();

    $comments_content .= "</div>";

    return $comments_content;
}

function comments_print_post()
{
    if (Jaris\Authentication::groupHasPermission("add_comments", Jaris\Authentication::currentUserGroup())) {
        $parameters["name"] = "add-comment";
        $parameters["class"] = "add-comment";

        $fields[] = [
            "type" => "textarea",
            "code" => "style=\"height: 60px\"",
            "name" => "comment",
            "label" => t("Post a comment:"),
            "id" => "comment",
            "description" => t("<span id=\"add-comment-left\"></span>&nbsp;characters left"),
            "required" => true
        ];

        $fields[] = [
            "type" => "other",
            "html_code" => '<input id="add-comment-submit" value="' . t("Post") . '" type="button" />'
        ];

        $fields[] = [
            "type" => "other",
            "html_code" => ' <input id="add-comment-reset" value="' . t("Reset") . '" type="button" />'
        ];

        $fieldset[] = ["fields" => $fields];

        return Jaris\Forms::generate($parameters, $fieldset);
    } elseif (!Jaris\Authentication::isUserLogged()) {
        return "<div class=\"comment-login\">
            <a href=\"" . Jaris\Uri::url("admin/user", ["return" => Jaris\Uri::get()]) . "\">" .
            t("Login") . "</a> " . t("or") . " " .
            "<a href=\"" . Jaris\Uri::url("register", ["return" => Jaris\Uri::get()]) . "\">" .
            t("Register") . "</a> " . t("to post a comment.") .
            "</div>"
        ;
    }

    return "";
}

/**
 * Prints a generaic navigation bar for any kind of results
 *
 * @param $total_count The total amount of results
 * @param $page The actual page number displaying results
 * @param $uri The uri used on navigation bar links
 * @param $module Optional module name to generate uri
 * @param $amount Optional amount of results to display per page, Default: 30
 */
function comments_print_navigation($total_count, $page, $uri, $amount = 30)
{
    $page_count = 0;
    $remainder_pages = 0;

    if ($total_count <= $amount) {
        $page_count = 1;
    } else {
        $page_count = floor($total_count / $amount);
        $remainder_pages = $total_count % $amount;

        if ($remainder_pages > 0) {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if ($page > $page_count || $page < 0 || $page_count == 1) {
        return false;
    }

    print "<div class=\"search-results\">\n";
    print "<div class=\"navigation\" id=\"comments-navigation\">\n";
    if ($page != 1) {
        $previous_page = Jaris\Uri::url($uri, ["page" => $page - 1]);
        $previous_text = t("Previous");
        print "<a class=\"previous\" data-page=\"".($page - 1)."\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for ($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++) {
        $text = t($start_page);

        if ($start_page > $page || $start_page < $page) {
            $url = Jaris\Uri::url($uri, ["page" => $start_page]);
            print "<a class=\"page\" data-page=\"".$start_page."\">$text</a>";
        } else {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if ($page < $page_count) {
        $next_page = Jaris\Uri::url($uri, ["page" => $page + 1]);
        $next_text = t("Next");
        print "<a class=\"next\" data-page=\"".($page + 1)."\">$next_text</a>";
    }
    print "</div>\n";
    print "</div>\n";
}
