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

function comments_get_settings($type)
{
    $settings = array();
    if(!($settings = Jaris\Settings::get($type, "comments")))
    {
        $settings["enabled"] = false;
        $settings["ordering"] = "asc";
        $settings["replies"] = "cascade";
        $settings["maximun_characters"] = 500;
    }
    else
    {
        $settings = unserialize($settings);

        $settings["enabled"] = $settings["enabled"] ?
            $settings["enabled"]
            :
            false
        ;

        $settings["ordering"] = $settings["ordering"] ?
            $settings["ordering"]
            :
            "asc"
        ;

        $settings["replies"] = $settings["replies"] ?
            $settings["replies"]
            :
            "cascade"
        ;

        $settings["maximun_characters"] = $settings["maximun_characters"] ?
            $settings["maximun_characters"]
            :
            500
        ;
    }

    return $settings;
}

function comments_get($id, $page)
{
    comments_create_db_if_needed($page, Jaris\Authentication::currentUser());

    $fields["id"] = $id;

    Jaris\Sql::escapeArray($fields);

    $db = Jaris\Sql::open("comments", comments_page_path($page));

    $select = "select * from comments where id={$fields['id']}";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function comments_add($comment, $page, $reply_to_id = null)
{
    if($reply_to_id <= 0)
    {
        $reply_to_id = "null";
    }

    comments_create_db_if_needed($page, Jaris\Authentication::currentUser());

    $fields["created_timestamp"] = time();
    $fields["comment_text"] = $comment;
    $fields["user"] = Jaris\Authentication::currentUser();
    $fields["reply_to"] = $reply_to_id;
    $fields["flags"] = 0;
    $fields["uri"] = $page;
    $fields["type"] = Jaris\Pages::getType($page);

    Jaris\Sql::escapeArray($fields);

    //Page database
    $db_page = Jaris\Sql::open("comments", comments_page_path($page));

    $insert_page = "insert into comments (created_timestamp, comment_text, user, reply_to, flags)
    values(
    '{$fields['created_timestamp']}', '{$fields['comment_text']}', '{$fields['user']}',
    {$fields['reply_to']}, {$fields['flags']}
    )";

    Jaris\Sql::query($insert_page, $db_page);

    //Retrieve id of created comment
    $select_id = "select id from comments where
    created_timestamp='{$fields['created_timestamp']}' and user='{$fields['user']}'";

    $result = Jaris\Sql::query($select_id, $db_page);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db_page);

    //User database
    $db_user = Jaris\Sql::open("comments", comments_user_path(Jaris\Authentication::currentUser()));

    $insert_user = "insert into comments (id, created_timestamp, comment_text, reply_to, flags, uri)
    values(
    {$data['id']}, '{$fields['created_timestamp']}', '{$fields['comment_text']}',
    {$fields['reply_to']}, {$fields['flags']}, '{$fields['uri']}'
    )";

    Jaris\Sql::query($insert_user, $db_user);

    Jaris\Sql::close($db_user);

    //System database
    $db_system = Jaris\Sql::open("comments");

    $insert_system = "insert into comments (id, created_timestamp, flags, uri, type, notification)
    values(
    {$data['id']}, '{$fields['created_timestamp']}',
    {$fields['flags']}, '{$fields['uri']}', '{$fields['type']}', 0
    )";

    Jaris\Sql::query($insert_system, $db_system);

    Jaris\Sql::close($db_system);

    //Subscribe current user
    comments_notifications_initial_subscribe(Jaris\Authentication::currentUser(), $page);

    return $data["id"];
}

function comments_edit($comment, $id, $page, $user)
{
    comments_create_db_if_needed($page, $user);

    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;
    $fields["edited_timestamp"] = time();
    $fields["comment_text"] = $comment;

    Jaris\Sql::escapeArray($fields);

    //update page db
    $db_page = Jaris\Sql::open("comments", comments_page_path($page));
    $update_page = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']}";
    Jaris\Sql::query($update_page, $db_page);
    Jaris\Sql::close($db_page);

    //update user db
    $db_user = Jaris\Sql::open("comments", comments_user_path($user));
    $update_user = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_user, $db_user);
    Jaris\Sql::close($db_user);

    //update system db
    $db_system = Jaris\Sql::open("comments");
    $update_system = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_system, $db_system);
    Jaris\Sql::close($db_system);
}

function comments_delete($id, $page, $user)
{
    comments_create_db_if_needed($page, $user);

    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;

    Jaris\Sql::escapeArray($fields);

    //Delete from page db
    $db_page = Jaris\Sql::open("comments", comments_page_path($page));
    $delete_page = "delete from comments where id={$fields['id']}";
    Jaris\Sql::query($delete_page, $db_page);
    Jaris\Sql::close($db_page);

    //Delete from user db
    $db_user = Jaris\Sql::open("comments", comments_user_path($user));
    $delete_user = "delete from comments where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($delete_user, $db_user);
    Jaris\Sql::close($db_user);

    //Delete from system db
    $db_system = Jaris\Sql::open("comments");
    $delete_system = "delete from comments where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($delete_system, $db_system);
    Jaris\Sql::close($db_system);
}

function comments_flag($id, $page, $user)
{
    comments_create_db_if_needed($page, $user);

    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;

    Jaris\Sql::escapeArray($fields);

    //update page db
    $db_page = Jaris\Sql::open("comments", comments_page_path($page));
    $update_page = "update comments set
    flags = flags+1
    where id={$fields['id']}";

    Jaris\Sql::query($update_page, $db_page);
    Jaris\Sql::close($db_page);

    //update user db
    $db_user = Jaris\Sql::open("comments", comments_user_path($user));
    $update_user = "update comments set
    flags = flags+1
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_user, $db_user);
    Jaris\Sql::close($db_user);

    //update system db
    $db_system = Jaris\Sql::open("comments");
    $update_system = "update comments set
    flags = flags+1
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_system, $db_system);
    Jaris\Sql::close($db_system);
}

function comments_flag_remove($id, $page, $user)
{
    comments_create_db_if_needed($page, $user);

    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;

    Jaris\Sql::escapeArray($fields);

    //update page db
    $db_page = Jaris\Sql::open("comments", comments_page_path($page));
    $update_page = "update comments set
    flags = 0
    where id={$fields['id']}";

    Jaris\Sql::query($update_page, $db_page);
    Jaris\Sql::close($db_page);

    //update user db
    $db_user = Jaris\Sql::open("comments", comments_user_path($user));
    $update_user = "update comments set
    flags = 0
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_user, $db_user);
    Jaris\Sql::close($db_user);

    //update system db
    $db_system = Jaris\Sql::open("comments");
    $update_system = "update comments set
    flags = 0
    where id={$fields['id']} and uri='{$fields['uri']}'";
    Jaris\Sql::query($update_system, $db_system);
    Jaris\Sql::close($db_system);
}

function comments_is_from_current_user($id, $page)
{
    comments_create_db_if_needed($page, Jaris\Authentication::currentUser());

    $fields["id"] = $id;
    $fields["uri"] = $page;

    Jaris\Sql::escapeArray($fields);

    //select comment from page db
    $db = Jaris\Sql::open("comments", comments_page_path($page));

    $select = "select user from comments where id={$fields['id']}";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    if($data["user"] == Jaris\Authentication::currentUser())
    {
        Jaris\Sql::close($db);
        return true;
    }

    Jaris\Sql::close($db);
    return false;
}

/**
 * Creates the user and page database if they do not exist
 *
 * @param $page the uri of the page to create its database
 * @param $user the username of the user to creates its database
 */
function comments_create_db_if_needed($page, $user)
{
    //Create page comments data base
    if(!Jaris\Sql::dbExists("comments", comments_page_path($page)))
    {
        $db = Jaris\Sql::open("comments", comments_page_path($page));

        Jaris\Sql::query(
            "create table comments ("
            . "id integer primary key, "
            . "created_timestamp text, "
            . "edited_timestamp text, "
            . "comment_text text, "
            . "reply_to integer, "
            . "user text, "
            . "flags integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index comments_index on comments ("
            . "created_timestamp desc, "
            . "reply_to desc, "
            . "user desc, "
            . "flags desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    //Create user comments data base
    if(!Jaris\Sql::dbExists("comments", comments_user_path($user)))
    {
        $db = Jaris\Sql::open("comments", comments_user_path($user));

        Jaris\Sql::query(
            "create table comments ("
            . "id integer, "
            . "created_timestamp text, "
            . "edited_timestamp text, "
            . "comment_text text, "
            . "reply_to integer, "
            . "uri text, "
            . "flags integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index comments_index on comments ("
            . "created_timestamp desc, "
            . "reply_to desc, "
            . "uri desc, "
            . "flags desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}

function comments_user_path($user)
{
    if($user == "Guest")
    {
        return null;
    }

    $user_exist = Jaris\Users::exists($user);

    $group = $user_exist["group"];

    $user_data_path = Jaris\Users::getPath($user, $group);
    $user_data_path = str_replace("data.php", "", $user_data_path);

    return $user_data_path;
}

function comments_page_path($page)
{
    return Jaris\Pages::getPath($page) . "/";
}

/**
 * To retrieve a list of comments of page from sqlite database to generate comments list
 *
 * @param string $page_uri the uri of the page to retrieve its comments.
 * @param int $page the current page count of pages list the admin is viewing
 * @param int $limit the amount of comments per page to display
 * @param string $ordering The order in which comments should be displayed (asc | desc).
 * @param bool $replies Flags that indicate if replies should also be retreived.
 *
 * @return array with each page uri not longer than $limit
 */
function comments_get_list(
    $page_uri, $page = 0, $limit = 30, $ordering="asc", $replies=false
)
{
    $db = null;
    $page *= $limit;
    $comments = array();

    $replies_where = "";
    if(!$replies)
    {
        $replies_where .= "where reply_to is null";
    }

    if(Jaris\Sql::dbExists("comments", comments_page_path($page_uri)))
    {
        $db = Jaris\Sql::open("comments", comments_page_path($page_uri));

        $result = Jaris\Sql::query(
            "select * from comments "
            . "$replies_where "
            . "order by created_timestamp $ordering "
            . "limit $page, $limit",
            $db
        );
    }
    else
    {
        return $comments;
    }

    $fields = array();
    if($fields = Jaris\Sql::fetchArray($result))
    {
        $comments[] = $fields;

        while($fields = Jaris\Sql::fetchArray($result))
        {
            $comments[] = $fields;
        }

        Jaris\Sql::close($db);
        return $comments;
    }
    else
    {
        Jaris\Sql::close($db);
        return $comments;
    }
}

/**
 * To retrieve a list of replies for a specific comment.
 *
 * @param int $comment_id
 * @param string $page_uri the uri of the page to retrieve its comments.
 *
 * @return array with each page uri not longer than $limit
 */
function comments_get_replies($comment_id, $page_uri)
{
    $db = null;
    $comments = array();

    if(Jaris\Sql::dbExists("comments", comments_page_path($page_uri)))
    {
        $db = Jaris\Sql::open("comments", comments_page_path($page_uri));

        $result = Jaris\Sql::query(
            "select * from comments "
            . "where reply_to = $comment_id "
            . "order by created_timestamp asc ",
            $db
        );
    }
    else
    {
        return $comments;
    }

    $fields = array();
    if($fields = Jaris\Sql::fetchArray($result))
    {
        $comments[] = $fields;

        while($fields = Jaris\Sql::fetchArray($result))
        {
            $comments[] = $fields;
        }

        Jaris\Sql::close($db);
        return $comments;
    }
    else
    {
        Jaris\Sql::close($db);
        return $comments;
    }
}

function comments_clean_user_comments($user)
{
    if(!Jaris\Sql::dbExists("comments", comments_user_path($user)))
        return;

    //Make a copy because of db locks while reading and writing at same time
    $comments_original = comments_user_path($user) . "comments";
    $comments_copy = comments_user_path($user) . "comments-copy";

    //Create a copy of original users database to query comments
    copy($comments_original, $comments_copy);

    $db_copy = Jaris\Sql::open("comments-copy", comments_user_path($user));

    //Open original user comments db to delete non existen comments
    $db = Jaris\Sql::open("comments", comments_user_path($user));

    $select = "select id, uri from comments;";

    $result = Jaris\Sql::query($select, $db_copy);

    while($comment = Jaris\Sql::fetchArray($result))
    {
        //Delete comment if original page that hold comment was deleted
        if(!Jaris\Sql::dbExists("comments", comments_page_path($comment["uri"])))
        {
            $delete = "delete from comments where id={$comment['id']} and uri='{$comment['uri']}'";

            Jaris\Sql::query($delete, $db);
        }

        //Delete if comment doesnt exist on the page comments database
        else
        {
            $db_page = Jaris\Sql::open("comments", comments_page_path($comment["uri"]));

            $select_page = "select id from comments where id={$comment['id']}";

            $result_page = Jaris\Sql::query($select_page, $db_page);

            if(!($comment_page = Jaris\Sql::fetchArray($result_page)))
            {
                $delete = "delete from comments where id={$comment['id']} and uri='{$comment['uri']}'";

                Jaris\Sql::query($delete, $db);
            }

            Jaris\Sql::close($db_page);
        }
    }

    Jaris\Sql::close($db);
    Jaris\Sql::close($db_copy);

    unlink($comments_copy);
}

/**
 * To retrieve a list of flagged comments
 *
 * @param $page the current page count of pages list the admin is viewing
 * @param $limit the amount of comments per page to display
 *
 * @return array with each page uri not longer than $limit
 */
function comments_get_flagged_list($page = 0, $limit = 30)
{
    $db = null;
    $page *= $limit;
    $comments = array();

    if(Jaris\Sql::dbExists("comments"))
    {
        $db = Jaris\Sql::open("comments");

        $result = Jaris\Sql::query(
            "select * from comments where flags > 0 order by flags desc limit $page, $limit",
            $db
        );
    }
    else
    {
        return $comments;
    }

    $fields = array();
    if($fields = Jaris\Sql::fetchArray($result))
    {
        $comments[] = $fields;

        while($fields = Jaris\Sql::fetchArray($result))
        {
            $comments[] = $fields;
        }

        Jaris\Sql::close($db);
        return $comments;
    }
    else
    {
        Jaris\Sql::close($db);
        return $comments;
    }
}

/**
 * Sets which comments notifications a user will receive
 * @param string $type Valid values: all, replies, none
 * @param string $user
 */
function comments_set_notifications_type($type, $user)
{
    $user_data = Jaris\Users::get($user);

    $user_data["comments_notification"] = $type;

    return Jaris\Users::edit($user, $user_data["group"], $user_data);
}

/**
 * Get which comments notifications a user will receive
 * @param string $user
 * @return string Valid values: all, replies, none
 */
function comments_get_notifications_type($user)
{
    $user_data = Jaris\Users::get($user);

    if(isset($user_data["comments_notification"]))
        return $user_data["comments_notification"];
    else
        return "all";
}

function comments_notifications_initial_subscribe($user, $page)
{
    //Create page comments_subscribers data base
    if(!Jaris\Sql::dbExists("comments_subscribers", comments_page_path($page)))
    {
        $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

        Jaris\Sql::query("create table comments_subscribers (user text, subscribed integer)", $db);

        Jaris\Sql::query("create index comments_subscribers_index on comments_subscribers (user desc, subscribed desc)", $db);

        Jaris\Sql::close($db);
    }

    $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

    $result = Jaris\Sql::query("select * from comments_subscribers where user='$user'", $db);

    if(!($data = Jaris\Sql::fetchArray($result)))
    {
        Jaris\Sql::query("insert into comments_subscribers (user, subscribed) values('$user', 1)", $db);
    }

    Jaris\Sql::close($db);
}

function comments_notifications_subscribe($user, $page)
{
    //Create page comments_subscribers data base
    if(!Jaris\Sql::dbExists("comments_subscribers", comments_page_path($page)))
    {
        $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

        Jaris\Sql::query("create table comments_subscribers (user text, subscribed integer)", $db);

        Jaris\Sql::query("create index comments_subscribers_index on comments_subscribers (user desc, subscribed desc)", $db);

        Jaris\Sql::close($db);
    }

    $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

    $result = Jaris\Sql::query("select * from comments_subscribers where user='$user'", $db);

    if(!($data = Jaris\Sql::fetchArray($result)))
    {
        Jaris\Sql::query("insert into comments_subscribers (user, subscribed) values('$user', 1)", $db);
    }
    else
    {
        Jaris\Sql::query("update comments_subscribers set subscribed=1 where user='$user'", $db);
    }

    Jaris\Sql::close($db);
}

function comments_notifications_unsubscribe($user, $page)
{
    if(Jaris\Sql::dbExists("comments_subscribers", comments_page_path($page)))
    {
        $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

        Jaris\Sql::query("update comments_subscribers set subscribed=0 where user='$user'", $db);

        Jaris\Sql::close($db);
    }
}

function comments_notifications_is_subscribed($user, $page)
{
    $is_subscribed = false;

    //Create page comments data base
    if(Jaris\Sql::dbExists("comments_subscribers", comments_page_path($page)))
    {
        $db = Jaris\Sql::open("comments_subscribers", comments_page_path($page));

        $result = Jaris\Sql::query("select * from comments_subscribers where user='$user'", $db);

        if($data = Jaris\Sql::fetchArray($result))
        {
            if($data["subscribed"] > 0)
                $is_subscribed = true;
        }

        Jaris\Sql::close($db);
    }

    return $is_subscribed;
}

?>
