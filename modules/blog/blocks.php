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
    field: module_identifier
        blog_user_archive
    field;

    field: position
        left
    field;

    field: description
        User Blog Archive
    field;

    field: title
        Blog Archive
    field;

    field: content
    <?php
        $current_year = date("Y", time());
        $current_month = date("n", time());
        $username = "";

        //Get blogs username
        if(Jaris\Pages::getType(Jaris\Uri::get()) == "blog")
        {
            $page_data = Jaris\Pages::get(Jaris\Uri::get());
            $username = $page_data["author"];
        }
        else
        {
            $uri_parts = explode("/", Jaris\Uri::get());
            $username = $uri_parts[2];
        }

        $user_data = Jaris\Users::get($username);
        $database_path = Jaris\Users::getPath($username, $user_data["group"]);
        $database_path = str_replace("data.php", "", $database_path);

        if(Jaris\Sql::dbExists("blog", $database_path))
        {
            $months[1] = t("January");
            $months[2] = t("February");
            $months[3] = t("March");
            $months[4] = t("April");
            $months[5] = t("May");
            $months[6] = t("June");
            $months[7] = t("July");
            $months[8] = t("August");
            $months[9] = t("September");
            $months[10] = t("October");
            $months[11] = t("November");
            $months[12] = t("December");

            $months_found = 0;
            $total_post_checked = 0;
            $total_post_count = Jaris\Sql::countColumn(
                "blog",
                "post",
                "id",
                "",
                $database_path
            );

            print "<ul class=\"blog-archive-links\">";

            while($months_found <= 12 && $total_post_checked < $total_post_count)
            {
                $post_count = Jaris\Sql::countColumn(
                    "blog",
                    "post",
                    "id",
                    "where month='$current_month' and year='$current_year'",
                    $database_path
                );

                if($post_count > 0)
                {
                    $months_found++;
                    $total_post_checked += $post_count;

                    $url = Jaris\Uri::url(
                        "blog/user/$username",
                        array("m"=>$current_month, "y"=>$current_year)
                    );

                    print "<li>"
                        . "<a href=\"$url\">"
                        . "<span class=\"month\">"
                        . $months[$current_month]
                        . "</span> "
                        . "<span class=\"year\">"
                        . $current_year
                        . "</span>"
                        . "<span class=\"blog-archive-count\">"
                        . "(<span class=\"number\">$post_count</span>)"
                        . "</span>"
                        . "</a>"
                        . "</li>"
                        . "\n"
                    ;
                }

                if($current_month == 1)
                {
                    $current_year--;
                    $current_month = 12;
                }
                else
                {
                    $current_month--;
                }
            }

            print "</ul>";
        }
    ?>
    field;

    field: order
        -1
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        if(
            Jaris\Pages::getType(Jaris\Uri::get()) == "blog" ||
            strpos(Jaris\Uri::get(), "blog/user/") !== false
        )
        {
            print "true";
        }
        else
        {
            print "false";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;

row: 1
    field: module_identifier
        blog_recent_user_posts
    field;

    field: position
        left
    field;

    field: description
        5 Recent User Posts
    field;

    field: title
        Recent Posts by This User
    field;

    field: content
    <?php
        $page_data = Jaris\Pages::get(Jaris\Uri::get());
        $username = $page_data["author"];
        $user_data = Jaris\Users::get($username);
        $database_path = Jaris\Users::getPath($username, $user_data["group"]);
        $database_path = str_replace("data.php", "", $database_path);

        $db = Jaris\Sql::open("blog", $database_path);

        $select = "select * from post "
            . "order by created_timestamp desc "
            . "limit 0, 5"
        ;

        $result = Jaris\Sql::query($select, $db);

        print "<div class=\"blog-recent-post\">\n";
        print "<ul>\n";
        while($data = Jaris\Sql::fetchArray($result))
        {
            $post_data = Jaris\Pages::get($data["uri"]);

            print "<li>"
                . "<a href=\"" . Jaris\Uri::url($data["uri"]) . "\">"
                . $post_data["title"]
                . "</a>"
                . "</li>\n"
            ;
        }
        print "</ul>\n";
        print "</div>\n";

        Jaris\Sql::close($db);
    ?>
    field;

    field: order
        -2
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        if(Jaris\Pages::getType(Jaris\Uri::get()) == "blog")
        {
            print "true";
        }
        else
        {
            print "false";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;

row: 2
    field: module_identifier
        blog_new_blogs
    field;

    field: position
        none
    field;

    field: description
        10 newly created blogs
    field;

    field: title
        New Blogs
    field;

    field: content
    <?php
        Jaris\View::addStyle(Jaris\Modules::directory("blog") . "styles/list.css");

        $db = Jaris\Sql::open("blog");

        $select = "select title, description, user, created_timestamp, views "
            . "from blogs "
            . "order by created_timestamp desc "
            . "limit 0, 10"
        ;

        $result = Jaris\Sql::query($select, $db);

        while($data = Jaris\Sql::fetchArray($result))
        {
            $user_data = Jaris\Users::get($data["user"]);

            if($user_data["picture"])
            {
                $picture = Jaris\Uri::url("image/user/" . $data["user"]);
            }
            else
            {
                $picture = Jaris\Uri::url(
                    Jaris\Modules::directory("blog") . "images/no-picture.png"
                );
            }

            $user_url = Jaris\Uri::url("blog/user/" . $data["user"]);

            $title = trim($data["title"]) != "" ?
                $data["title"]
                :
                $data["user"] . " " . t("blog")
            ;

            print "<div class=\"blog-list blog-recent-blogs\">\n";

            print "<div class=\"title\">"
                . "<a href=\"" . $user_url . "\">"
                . $title
                . "</a>"
                . "</div>\n"
            ;

            print "<div class=\"thumbnail\">"
                . "<a title=\"$title\" href=\"" . $user_url . "\">"
                . "<img alt=\"$title\" src=\"$picture\" /></a>"
                . "</div>\n"
            ;

            print "<div class=\"views\">"
                . "<span class=\"label\">"
                . t("Views:")
                . "</span> "
                . $data["views"]
                . "</div>\n"
            ;

            print "<div class=\"user\">"
                . "<span class=\"label\">"
                . t("Created by:")
                . "</span>"
                . "<a href=\"$user_url\">"
                . $data["user"]
                . "</a>"
                . "</div>\n"
            ;

            if($data["description"])
            {
                print "<div class=\"description\">"
                    . $data["description"]
                    . "</div>\n"
                ;
            }

            print "<div style=\"clear: both\"></div>\n";
            print "</div>\n";
        }

        Jaris\Sql::close($db);
    ?>
    field;

    field: order
        0
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return

    field;

    field: is_system
        1
    field;
row;

row: 3
    field: module_identifier
        blog_most_viewed_blogs
    field;

    field: position
        none
    field;

    field: description
        Top 10 Most Viewed Blogs
    field;

    field: title
        Most Viewed Blogs
    field;

    field: content
    <?php
        Jaris\View::addStyle(Jaris\Modules::directory("blog") . "styles/list.css");

        $db = Jaris\Sql::open("blog");

        $select = "select title, user, views from blogs "
            . "order by views desc, created_timestamp desc "
            . "limit 0, 10"
        ;

        $result = Jaris\Sql::query($select, $db);

        while($data = Jaris\Sql::fetchArray($result))
        {
            $user_data = Jaris\Users::get($data["user"]);

            if($user_data["picture"])
            {
                $picture = Jaris\Uri::url("image/user/" . $data["user"]);
            }
            else
            {
                $picture = Jaris\Uri::url(
                    Jaris\Modules::directory("blog") . "images/no-picture.png"
                );
            }

            $user_url = Jaris\Uri::url("blog/user/" . $data["user"]);

            $title = trim($data["title"]) != "" ?
                $data["title"]
                :
                $data["user"] . " " . t("blog")
            ;

            print "<div class=\"blog-list blog-most-viewed-blogs\">\n";

            print "<div class=\"title\">"
                . "<a href=\"" . Jaris\Uri::url($user_url) . "\">" . $title . "</a>"
                . "</div>\n"
            ;

            print "<div class=\"thumbnail\">"
                . "<a title=\"$title\" href=\"" . $user_url . "\">"
                . "<img alt=\"$title\" src=\"$picture\" />"
                . "</a>"
                . "</div>\n"
            ;

            print "<div class=\"details\">\n";
            print "<div class=\"views\">"
                . "<span class=\"label\">"
                . t("Views:")
                . "</span> "
                . $data["views"]
                . "</div>\n"
            ;

            print "<div class=\"user\">"
                . "<span class=\"label\">" . t("Created by:") . "</span> "
                . "<a href=\"$user_url\">" . $data["user"] . "</a>"
                . "</div>\n"
            ;
            print "</div>\n";

            print "<div style=\"clear: both\"></div>\n";

            print "</div>\n";
        }

        Jaris\Sql::close($db);
    ?>
    field;

    field: order
        0
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return

    field;

    field: is_system
        1
    field;
row;

row: 4
    field: module_identifier
        blog_categories_blogs
    field;

    field: position
        left
    field;

    field: description
        Navigate blogs by categories
    field;

    field: title
        Categories
    field;

    field: content
    <?php
        $settings = blog_get_main_settings();

        if($settings["main_category"] != "")
        {
            $subcategories = Jaris\Categories::getSubcategories(
                $settings["main_category"]
            );

            print "<ul class=\"blog-categories\">";

            print "<li><a href=\""
                . Jaris\Uri::url(
                    Jaris\Modules::getPageUri("blog/browser", "blog")
                )
                . "\">"
                . t("All")
                . "</a>"
                . "</li>"
            ;

            foreach($subcategories as $id=>$data)
            {
                print "<li>"
                    . "<a href=\""
                    . Jaris\Uri::url(
                        Jaris\Modules::getPageUri("blog/browser", "blog"),
                        array("c"=>$id)
                    )
                    . "\">"
                    . t($data["title"])
                    . "</a>"
                    . "</li>"
                ;
            }

            print "<li>"
                . "<a href=\""
                . Jaris\Uri::url(
                    Jaris\Modules::getPageUri("blog/browser", "blog"),
                    array("c"=>-1)
                )
                . "\">"
                . t("Other")
                . "</a>"
                . "</li>"
            ;

            print "</ul>";
        }
    ?>
    field;

    field: order
        0
    field;

    field: display_rule
        just_listed
    field;

    field: pages
        blog/browser
    field;

    field: return

    field;

    field: is_system
        1
    field;
row;