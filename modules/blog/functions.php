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
    Jaris\Pages::SIGNAL_CREATE_PAGE,
    function(&$page, &$data, &$path)
    {
        if($data["type"] == "blog")
        {
            blog_add_post($page, $data);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function(&$page, &$new_data, &$page_path)
    {
        $username = $new_data["author"];
        $user_data = Jaris\Users::get($username);

        //Check user has blog permissions
        if(!Jaris\Authentication::hasTypeAccess("blog", $user_data["group"]))
        {
            return;
        }

        //In case users blog database doesnt exists yet
        blog_create_if_not_exists($username);

        //Ensure that if user changed content type from blog to
        //another to delete the post from the blog listing
        if($new_data["type"] != "blog")
        {
            blog_delete_post($page, $username);
        }

        //If some one took an existing content and changed the
        //content type from another one to blog and it to the users post list
        else
        {
            $db_path = str_replace(
                "data.php",
                "",
                Jaris\Users::getPath($username, $user_data["group"])
            );

            $db = Jaris\Sql::open("blog", $db_path);

            $select = "select * from post where uri = '$page'";

            $result = Jaris\Sql::query($select, $db);

            $in_db = Jaris\Sql::fetchArray($result);

            //Ensure database gets unlocked in order to use it for writing
            unset($result);

            Jaris\Sql::close($db);

            if(!is_array($in_db))
            {
                blog_add_post($page, $new_data);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_MOVE_PAGE,
    function(&$actual_uri, &$new_uri)
    {
        $page_data = Jaris\Pages::get($actual_uri);

        if($page_data["type"] == "blog")
        {
            blog_edit_post($actual_uri, $new_uri, $page_data["author"]);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function(&$page, &$page_path)
    {
        $page_data = Jaris\Pages::get($page);

        if($page_data["type"] == "blog")
        {
            blog_delete_post($page, $page_data["author"]);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Users::SIGNAL_DELETE_USER,
    function(&$username, &$group)
    {
        blog_delete_from_db($username);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_PAGE_DATA,
    function(&$page_data)
    {
        $segments = explode("/", Jaris\Uri::get());

        if(count($segments) == 3)
        {
            if($segments[0] == "blog" && $segments[1] == "user")
            {
                if($user_data = Jaris\Users::get($segments[2]))
                {
                    if(
                        Jaris\Authentication::hasTypeAccess(
                            "blog", $user_data["group"]
                        )
                    )
                    {
                        $_REQUEST["user"] = $segments[2];

                        blog_create_if_not_exists($_REQUEST["user"]);

                        blog_count_view($_REQUEST["user"]);

                        $page_data[0] = Jaris\Pages::get(
                            Jaris\Modules::getPageUri(
                                "blog/user",
                                "blog"
                            )
                        );

                        $page_data[0]["title"] = Jaris\System::evalPHP(
                            $page_data[0]["title"]
                        );
                    }
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Users::SIGNAL_PRINT_USER_PAGE,
    function(&$content, &$tabs)
    {
        if(
            Jaris\Authentication::hasTypeAccess(
                "blog", Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $tabs[t("Blog")] = array(
                "uri" => Jaris\Modules::getPageUri("users/blog", "blog")
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(Jaris\Uri::get() == "admin/settings")
        {
            $tabs_array[0][t("Blog")] = array(
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/blog", "blog"
                ),
                "arguments" => null
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_CONTENT_TEMPLATE,
    function(&$page, &$type, &$template_path)
    {
        $theme = Jaris\Site::$theme;
        $default_template = Jaris\Themes::directory($theme) . "content.php";

        $page_data = Jaris\Pages::get(Jaris\Uri::get());

        if($template_path == $default_template)
        {
            if($page_data["type"] == "blog")
            {
                $template_path = Jaris\Modules::directory("blog") 
                    . "templates/content-blog.php"
                ;
            }
        }
    }
);