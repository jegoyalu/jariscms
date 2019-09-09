<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module include file
 *
 * @note File with general functions
 */

function blog_get_from_db($username)
{
    $db = Jaris\Sql::open("blog");

    Jaris\Sql::turbo($db);

    $select = "select * from blogs where user = '$username'";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function blog_edit_from_db($username, $fields)
{
    $db = Jaris\Sql::open("blog");

    $time = time();

    Jaris\Sql::escapeArray($fields);

    $update = "update blogs set
    title = '{$fields['title']}',
    description = '{$fields['description']}',
    tags = '{$fields['tags']}',
    edited_timestamp = '$time',
    category = '{$fields['category']}'
    where user='$username'";

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);
}

function blog_delete_from_db($username)
{
    $db = Jaris\Sql::open("blog");

    $delete = "delete from blogs where user='$username'";

    Jaris\Sql::query($delete, $db);

    Jaris\Sql::close($db);
}

function blog_count_view($username)
{
    $db = Jaris\Sql::open("blog");

    $update = "update blogs set
    views = views + 1
    where user = '$username'
    ";

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);
}

function blog_add_post($page, $data)
{
    blog_create_if_not_exists($data["author"]);

    $username = $data["author"];
    $user_data = Jaris\Users::get($username);

    $db_path = str_replace(
        "data.php",
        "",
        Jaris\Users::getPath($username, $user_data["group"])
    );

    $db = Jaris\Sql::open("blog", $db_path);

    $fields["uri"] = $page;
    $fields["created_timestamp"] = $data["created_date"];
    $fields["month"] = date("n", time());
    $fields["year"] = date("Y", time());

    Jaris\Sql::escapeArray($fields);

    $insert = "insert into post (uri, created_timestamp, month, year) values(
    '{$fields['uri']}',
    '{$fields['created_timestamp']}',
    '{$fields['month']}',
    '{$fields['year']}'
    )";

    Jaris\Sql::query($insert, $db);

    Jaris\Sql::close($db);
}

function blog_edit_post($actual_uri, $new_uri, $username)
{
    $user_data = Jaris\Users::get($username);

    $db_path = str_replace(
        "data.php",
        "",
        Jaris\Users::getPath($username, $user_data["group"])
    );

    $db = Jaris\Sql::open("blog", $db_path);

    $fields["uri"] = $actual_uri;
    $fields["new_uri"] = $new_uri;
    $fields["edited_timestamp"] = time();

    Jaris\Sql::escapeArray($fields);

    $update = "update post set
    uri = '{$fields["new_uri"]}',
    edited_timestamp = '{$fields['edited_timestamp']}'
    where uri = '{$fields["uri"]}'";

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);
}

function blog_delete_post($page, $username)
{
    if (Jaris\Users::exists($username)) {
        $user_data = Jaris\Users::get($username);

        $db_path = str_replace(
            "data.php",
            "",
            Jaris\Users::getPath($username, $user_data["group"])
        );

        $db = Jaris\Sql::open("blog", $db_path);

        $uri = str_replace("'", "''", $page);

        $delete = "delete from post where uri='$uri'";

        Jaris\Sql::query($delete, $db);

        Jaris\Sql::close($db);
    } else {
        $db = Jaris\Sql::open("blog");

        $delete = "delete from blogs where user='$username'";

        Jaris\Sql::query($delete, $db);

        Jaris\Sql::close($db);
    }
}

function blog_create_if_not_exists($user)
{
    if ($user_data = Jaris\Users::get($user)) {
        if (!$user_data["blog"]) {
            $db = Jaris\Sql::open("blog");

            $select = "select user from blogs where user='" . str_replace("'", "''", $user) . "'";

            $result = Jaris\Sql::query($select, $db);

            if (!($data = Jaris\Sql::fetchArray($result))) {
                $fields["created_timestamp"] = time();
                $fields["user"] = $user;
                $fields["views"] = "0";

                Jaris\Sql::escapeArray($fields);

                $insert = "insert into blogs
                (
                    created_timestamp,
                    user,
                    views
                )

                values(
                    '{$fields['created_timestamp']}',
                    '{$fields['user']}',
                    {$fields['views']}
                )
                ";

                Jaris\Sql::query($insert, $db);
            }

            Jaris\Sql::close($db);

            $user_data["blog"] = true;
            Jaris\Users::edit($user, $user_data["group"], $user_data, $user_data);

            //Create personal post database
            $db_path = str_replace(
                "data.php",
                "",
                Jaris\Users::getPath($user, $user_data["group"])
            );

            if (!Jaris\Sql::dbExists("blog", $db_path)) {
                $db_post = Jaris\Sql::open("blog", $db_path);

                $create = "create table post (id integer primary key, created_timestamp text, edited_timestamp text, month text, year text, uri text)";

                Jaris\Sql::query($create, $db_post);

                $create_index = "create index post_index on post (created_timestamp desc, edited_timestamp desc, month desc, year desc, uri desc)";

                Jaris\Sql::query($create_index, $db_post);

                Jaris\Sql::close($db_post);
            }
        }
    }
}

function blog_subscribed($blog, $user)
{
    $fields["user_blog"] = $blog;
    $fields["subscriber"] = $user;

    Jaris\Sql::escapeArray($fields);

    $db = Jaris\Sql::open("blog_subscriptions");

    $select = "select id from subscriptions where user='{$fields['user_blog']}' and subscriber='{$fields['subscriber']}'";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function blog_subscribe($blog, $user)
{
    if (!blog_subscribed($blog, $user)) {
        $fields["user_blog"] = $blog;
        $fields["subscriber"] = $user;

        Jaris\Sql::escapeArray($fields);

        $db = Jaris\Sql::open("blog_subscriptions");

        $time = time();

        $insert = "insert into subscriptions "
            . "("
            . "user, "
            . "subscriber, "
            . "created_timestamp"
            . ") "
            . "values("
            . "'{$fields['user_blog']}',"
            . "'{$fields['subscriber']}',"
            . "'$time'"
            . ")"
        ;

        Jaris\Sql::query($insert, $db);

        Jaris\Sql::close($db);

        return true;
    }

    return false;
}

function blog_unsubscribe($blog, $user)
{
    $fields["user_blog"] = $blog;
    $fields["subscriber"] = $user;

    Jaris\Sql::escapeArray($fields);

    $db = Jaris\Sql::open("blog_subscriptions");

    $delete = "delete from subscriptions where user='{$fields['user_blog']}' and subscriber='{$fields['subscriber']}'";

    Jaris\Sql::query($delete, $db);

    Jaris\Sql::close($db);
}

function blog_get_main_settings()
{
    $settings = Jaris\Settings::getAll("blogs");

    $settings["main_category"] = $settings["main_category"] ?
        $settings["main_category"]
        :
        "";

    return $settings;
}

/**
 * Prepares the content that is going to be displayed
 *
 * @param $content Array that contains all the page data content.
 *
 * @return String with the content preformatted.
 */
function blog_theme($post_data)
{
    $images = Jaris\Pages\Images::getList($post_data["uri"]);

    $thumbnail = false;

    foreach ($images as $image_id => $image_data) {
        $thumbnail = Jaris\Uri::url(
            "image/" . $post_data["uri"] . "/$image_id",
            ["w" => 100, "h" => 60]
        );

        break;
    }

    $page_data = Jaris\Pages::get($post_data["uri"]);
    $page_data_translation = Jaris\Pages::get($post_data["uri"], Jaris\Language::getCurrent());
    $page_data["title"] = $page_data_translation["title"];
    $page_data["content"] = $page_data_translation["content"];

    if (!$thumbnail) {
        $type_image = Jaris\Types::getImageUrl(
            $page_data["type"],
            100,
            60
        );

        if ($type_image != "") {
            $thumbnail = $type_image;
        }
    }

    $url = Jaris\Uri::url($post_data["uri"]);
    $title = $page_data["title"];
    $views = $page_data["views"];

    $description = Jaris\Util::contentPreview($page_data["content"], 50, true);

    $blog_post = "";

    ob_start();
    include(blog_template_path($post_data["uri"]));
    $blog_post .= ob_get_contents();
    ob_end_clean();

    return $blog_post;
}

/**
 * Search for the best blog template match
 *
 * @param string $page The page uri that is going to be displayed.
 *
 * @return string The template file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/blog-post-uri.php
 *      themes/theme/blog-post.php
 */
function blog_template_path($page)
{
    $theme = Jaris\Site::$theme;

    $page = str_replace("/", "-", $page);

    $current_page = Jaris\Themes::directory($theme) . "blog-post-" . $page . ".php";
    $default_page = Jaris\Themes::directory($theme) . "blog-post.php";

    $template_path = "";

    if (file_exists($current_page)) {
        $template_path = $current_page;
    } elseif (file_exists($default_page)) {
        $template_path = $default_page;
    } else {
        $template_path = Jaris\Modules::directory("blog") . "templates/blog-post.php";
    }

    return $template_path;
}
