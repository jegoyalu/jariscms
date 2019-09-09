<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_INITIALIZATION,
    function()
    {
        $uri = $_REQUEST["uri"];

        if($uri && Jaris\Uri::get() != "admin/pages/add")
        {
            $page_data = Jaris\Pages::get($uri);
            if($page_data["type"] == "listing")
            {
                switch(Jaris\Uri::get())
                {
                    case "admin/pages/edit":
                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri(
                                "admin/pages/listing/edit",
                                "listing"
                            ),
                            array("uri" => $uri)
                        );
                    default:
                        break;
                }
            }
        }
        else if($_REQUEST["type"])
        {
            $page = Jaris\Uri::get();
            if($page == "admin/pages/add" && $_REQUEST["type"] == "listing")
            {
                Jaris\Uri::go(
                    Jaris\Modules::getPageUri(
                        "admin/pages/listing/add",
                        "listing"
                    ),
                    array("type" => "listing", "uri" => $uri)
                );
            }
        }

        if(isset($_REQUEST["id"], $_REQUEST["position"]))
        {
            if(Jaris\Uri::get() == "admin/blocks/edit")
            {
                $block_data = Jaris\Blocks::get(
                    $_REQUEST["id"],
                    $_REQUEST["position"]
                );

                if($block_data["is_listing_block"])
                {
                    Jaris\Uri::go(
                        Jaris\Modules::getPageUri(
                            "admin/blocks/listing/edit",
                            "listing"
                        ),
                        array(
                            "id" => $_REQUEST["id"],
                            "position" => $_REQUEST["position"]
                        )
                    );
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        if(
            Jaris\Authentication::groupHasPermission(
                "add_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $content = array(
                "title" => t("Add Listing Block"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/blocks/listing/add",
                        "listing"
                    )
                ),
                "description" => t("Create blocks to display a list a content by a given criteria.")
            );
        }

        if(isset($content))
        {
            foreach($sections as $section_index => $section_data)
            {
                if($section_data["class"] == "blocks")
                {
                    $sections[$section_index]["sub_sections"][] = $content;
                    break;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function(&$content, &$content_title, &$content_data)
    {
        if($content_data["type"] == "listing")
        {
            Jaris\View::addStyle(
                Jaris\Modules::directory("listing")
                    . "styles/lists.css"
            );

            $content_data["filter_types"] = unserialize(
                $content_data["filter_types"]
            );

            $content_data["filter_categories"] = unserialize(
                $content_data["filter_categories"]
            );

            $listing_content = listing_print_results(
                Jaris\Uri::get(),
                $content_data
            );

            $content .= $listing_content;

            $content_data["listing_content"] = $listing_content;
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
            if($page_data["type"] == "listing")
            {
                if(
                    $page_data["author"] == Jaris\Authentication::currentUser() ||
                    Jaris\Authentication::isAdminLogged() ||
                    Jaris\Authentication::groupHasPermission(
                        "edit_all_user_content",
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    unset($tabs_array[0][t("Edit")]);

                    $new_tabs_array = array();

                    $new_tabs_array[0][t("Edit Listing")] = array(
                        "uri" => Jaris\Modules::getPageUri(
                            "admin/pages/listing/edit",
                            "listing"
                        ),
                        "arguments" => array("uri" => Jaris\Uri::get())
                    );

                    $new_tabs_array[0] = array_merge(
                        $new_tabs_array[0],
                        $tabs_array[0]
                    );

                    $tabs_array[0] = $new_tabs_array[0];
                }
            }
        }
        else
        {
            if(Jaris\Uri::get() == "admin/blocks")
            {
                $tabs_array[0][t("Create Listing Block")] = array(
                    "uri" => Jaris\Modules::getPageUri(
                        "admin/blocks/listing/add",
                        "listing"
                    ),
                    "arguments" => array()
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_BLOCK,
    function(&$position, &$page, &$field)
    {
        if($field["is_listing_block"])
        {
            Jaris\View::addStyle(
                Jaris\Modules::directory("listing")
                    . "styles/lists.css"
            );

            $field["filter_types"] = unserialize($field["filter_types"]);
            $field["filter_categories"] = unserialize($field["filter_categories"]);

            $field["content"] = Jaris\System::evalPHP($field["pre_content"]);
            $field["content"] .= listing_block_print_results($page, $field);
            $field["content"] .= Jaris\System::evalPHP($field["sub_content"]);

            $field["is_system"] = true;
        }
    }
);