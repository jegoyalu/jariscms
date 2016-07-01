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
    Jaris\Site::SIGNAL_PAGE_DATA,
    function(&$page_data)
    {
        /*
         * If a person is trying to view a specific revision and has proper permissions
         * replace the current page content with the specified revision.
         */
        $uri = Jaris\Uri::get();

        if(isset($_REQUEST["rev"]))
        {
            $_REQUEST["rev"] = intval($_REQUEST["rev"]);

            $revisions_file = Jaris\Pages::getPath($uri) 
                . "/revisions/" 
                . $_REQUEST["rev"] . ".php"
            ;

            if(file_exists($revisions_file))
            {
                if(
                    Jaris\Authentication::groupHasPermission(
                        "view_revisions", 
                        Jaris\Authentication::currentUserGroup()
                    ) &&
                    !trim($page_data[0]["is_system"])
                )
                {
                    if(Jaris\Pages::userIsOwner($uri))
                    {
                        $revision_data = Jaris\Data::get(0, $revisions_file);
                        $revision_data["users"] = unserialize($revision_data[0]["users"]);
                        $revision_data["groups"] = unserialize($revision_data[0]["groups"]);
                        $revision_data["categories"] = unserialize($revision_data[0]["categories"]);

                        $revision_data["title"] = revision_diff_html(
                            $page_data[0]["title"], $revision_data["title"]
                        );

                        $revision_data["content"] = revision_diff_html(
                            $page_data[0]["content"], $revision_data["content"]
                        );

                        $page_data[0] = $revision_data;

                        Jaris\View::addStyle(Jaris\Modules::directory("revision") . "styles/html.css");

                        Jaris\View::addMessage(
                            t("You are viewing revision of:") . " " .
                            t(date("F", $_REQUEST["rev"])) . " " .
                            date("d, Y (h:i:s a)", $_REQUEST["rev"])
                        );
                    }
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_CREATE_PAGE,
    function(&$page, &$data, &$path)
    {
        $revisions_path = $path . "/revisions/";

        mkdir($revisions_path);

        $revision_data = $data;

        foreach($revision_data as $field_name=>$field_value)
        {
            if(is_array($field_value))
            {
                $revision_data[$field_name] = serialize($field_value);
            }
        }

        $revision_file = $revisions_path . "/" . time() . ".php";

        Jaris\Data::add($revision_data, $revision_file);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function(&$page, &$new_data, &$page_path)
    {
        $revisions_path = $page_path . "/revisions/";

        if(!file_exists($revisions_path))
            mkdir($revisions_path);

        // Check if something changed
        $current_data = Jaris\Pages::get($page);

        $has_changed = false;

        foreach($current_data as $field => $value)
        {
            if(
                $field != "views" &&
                $field != "last_edit_by" &&
                $field != "last_edit_date" &&
                $value != $new_data[$field]
            )
            {
                $has_changed = true;
                break;
            }
        }

        // Create revision
        if($has_changed)
        {
            $revision_data = $new_data;

            foreach($revision_data as $field_name=>$field_value)
            {
                if(is_array($field_value))
                {
                    $revision_data[$field_name] = serialize($field_value);
                }
            }

            $revision_file = $revisions_path . "/" . time() . ".php";

            Jaris\Data::add($revision_data, $revision_file);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Groups::SIGNAL_SET_GROUP_PERMISSION,
    function(&$permissions, $group)
    {
        if($group != "guest")
        {
            $revisions = array();

            $revisions["view_revisions"] = t("View");
            $revisions["delete_revisions"] = t("Delete");
            $revisions["revert_revisions"] = t("Revert");

            $permissions[t("Revisions")] = $revisions;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        $page_data = Jaris\Site::$page_data;

        $uri = Jaris\Uri::get();

        if(
            Jaris\Authentication::groupHasPermission(
                "view_revisions", 
                Jaris\Authentication::currentUserGroup()
            ) &&
            !trim($page_data[0]["is_system"])
        )
        {
            if(Jaris\Pages::userIsOwner($uri))
            {
                $tabs_array[0][t("Revisions")] = array(
                    "uri" => Jaris\Modules::getPageUri(
                        "revisions", 
                        "revision"
                    ),
                    "arguments" => array("uri" => $uri)
                );

                if(isset($_REQUEST["rev"]))
                {
                    $_REQUEST["rev"] = intval($_REQUEST["rev"]);

                    $revisions_file = Jaris\Pages::getPath($uri)
                        . "/revisions/" . $_REQUEST["rev"] . ".php"
                    ;

                    if(file_exists($revisions_file))
                    {
                        if(
                            Jaris\Authentication::groupHasPermission(
                                "revert_revisions", 
                                Jaris\Authentication::currentUserGroup()
                            )
                        )
                        {
                            $tabs_array[1][t("Revert to this revision")] = array(
                                "uri" => Jaris\Modules::getPageUri("revision/revert", "revision"),
                                "arguments" => array("uri" => $uri, "rev" => $_REQUEST["rev"])
                            );
                        }
                    }
                }
            }
        }
    }
);