<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_INITIALIZATION,
    function()
    {
        $uri = $_REQUEST["uri"];

        if($uri && Jaris\Uri::get() != "admin/pages/add")
        {
            $page_data = Jaris\Pages::get($uri);
            if($page_data["type"] == "poll")
            {
                switch(Jaris\Uri::get())
                {
                    case "admin/pages/edit":
                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/polls/edit", 
                                "polls"
                            ),
                            array("uri" => $uri)
                        );
                    default:
                        break;
                }
            }
        }
        elseif($_REQUEST["type"])
        {
            $page = Jaris\Uri::get();
            if($page == "admin/pages/add" && $_REQUEST["type"] == "poll")
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri("admin/polls/add", "polls"),
                    array("type" => "poll", "uri" => $uri)
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function(&$page, &$page_path)
    {
        if(Jaris\Pages::getType($page) == "poll")
        {
            polls_sqlite_delete($page);
            delete_recent_poll($page);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        if(Jaris\Authentication::isAdminLogged())
        {
            $content[] = array(
                "title" => t("Add Poll"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/polls/add", 
                        "polls"
                    )
                ),
                "description" => t("Create a poll where users can vote.")
            );

            $content[] = array(
                "title" => t("View All Polls"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/polls", 
                        "polls"
                    )
                ),
                "description" => t("View created polls on the system.")
            );

            $new_section[] = array(
                "class" => "polls",
                "title" => t("Polls"),
                "sub_sections" => $content
            );

            $original_sections = $sections;

            $sections = array_merge($new_section, $original_sections);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(!Jaris\Pages::isSystem())
        {
            $page_data = Jaris\Pages::get(Jaris\Uri::get());
            
            if($page_data["type"] == "poll")
            {
                $tabs_array = array();

                if(
                    $page_data["author"] 
                    == 
                    Jaris\Authentication::currentUser() ||
                    Jaris\Authentication::isAdminLogged() ||
                    Jaris\Authentication::groupHasPermission(
                        "edit_all_user_content", 
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    $tabs_array[0][t("Edit Poll")] = array(
                        "uri" => Jaris\Modules::getPageUri(
                            "admin/polls/edit", 
                            "polls"
                        ),
                        "arguments" => array("uri" => Jaris\Uri::get())
                    );
                }
            }
        }
        else
        {
            $uri = Jaris\Uri::get();
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_CONTENT_TEMPLATE,
    function(&$page, &$type, &$template_path)
    {
        $theme = Jaris\Site::$theme;

        $default_template = Jaris\Themes::directory($theme) . "content.php";

        if($type == "poll" && $template_path == $default_template)
        {
            $template_path = Jaris\Modules::directory("polls") 
                . "templates/content-poll.php"
            ;
        }
    }
);