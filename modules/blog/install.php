<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for blog module.
 */

function blog_install()
{
    $string = t("Blog Post");
    $string = t("Message automatically posted on your personal blog");

    //Add blog type
    $blog_fields["name"] = "Blog Post";
    $blog_fields["description"] = "Message automatically posted on your personal blog";

    Jaris\Types::add("blog", $blog_fields);

    //Create blog data base
    if (!Jaris\Sql::dbExists("blog")) {
        $db = Jaris\Sql::open("blog");

        Jaris\Sql::query(
            "create table blogs ("
            . "id integer primary key, "
            . "created_timestamp text, "
            . "edited_timestamp text, "
            . "title text, "
            . "description text, "
            . "tags text, "
            . "views integer, "
            . "user text, "
            . "category text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index blogs_index on blogs ("
            . "created_timestamp desc, "
            . "title desc, "
            . "description desc, "
            . "tags desc, "
            . "views desc, "
            . "user desc, "
            . "category desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    //Create blog subscriptions data base
    if (!Jaris\Sql::dbExists("blog_subscriptions")) {
        $db = Jaris\Sql::open("blog_subscriptions");

        Jaris\Sql::query(
            "create table subscriptions ("
            . "id integer primary key, "
            . "user text, "
            . "subscriber text, "
            . "created_timestamp text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index subscriptions_index on subscriptions ("
            . "user desc, "
            . "subscriber desc, "
            . "created_timestamp desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    //Add user blog archive navigation
    $text = t("Blog Archive");

    //Add recent user posts
    $text = t("Recent Posts by This User");

    //Add new created blogs
    $text = t("New Blogs");

    //Add most viewed blogs block
    $text = t("Most Viewed Blogs");
    $text = t("Views:");
    $text = t("Created by:");

    //Add navigate by categories blogs block
    $text = t("Categories");

    Jaris\View::addMessage(
        t("Remember to set the blog configurations to work properly.")
        . " <a href=\""
        . Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/settings/blog", "blog")
        )
        . "\">"
        . t("Configure Now")
        . "</a>"
    );
}
