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
    <?php
        $id = 0;
        if(!isset($_REQUEST["id"]) || intval($_REQUEST["id"]) <= 0)
        {
            Jaris\Uri::go("");
        }
        else
        {
            $id = intval($_REQUEST["id"]);
        }

        if(!isset($_REQUEST["uri"]) || trim($_REQUEST["uri"]) == "")
        {
            Jaris\Uri::go("");
        }
        elseif(!($page_data = Jaris\Pages::get($_REQUEST["uri"])))
        {
            Jaris\Uri::go("");
        }

        if($page_data["type"] != "calendar")
        {
            Jaris\Uri::go("");
        }

        $uri = trim($_REQUEST["uri"]);

        $event_data = calendar_event_data($id, $uri);

        if($event_data["approved"])
        {
            print Jaris\Util::stripHTMLTags($event_data["title"]);
        }
        else
        {
            Jaris\Uri::go($uri);
        }
    ?>
    field;

    field: content
    <?php
        $id = intval($_REQUEST["id"]);
        $uri = trim($_REQUEST["uri"]);

        $event_data = calendar_event_data($id, $uri);

        if(count($event_data) > 0)
        {
            Jaris\View::addScript("https://maps.googleapis.com/maps/api/js?sensor=false");
            Jaris\View::addScript(Jaris\Modules::directory("calendar") . "scripts/gmap3.min.js");

            Jaris\View::addStyle(Jaris\Modules::directory("calendar") . "styles/calendar.css");

            if(
                Jaris\Pages::userIsOwner($uri) ||
                $event_data["author"] == Jaris\Authentication::currentUser()
            )
            {
                Jaris\View::addTab(
                    t("Edit Event"),
                    Jaris\Modules::getPageUri("admin/calendar/events/edit", "calendar"),
                    array("uri" => $uri, "id" => $id)
                );

                Jaris\View::addTab(
                    t("Delete Event"),
                    Jaris\Modules::getPageUri("admin/calendar/events/delete", "calendar"),
                    array("uri" => $uri, "id" => $id)
                );
            }

            $user_data = Jaris\Users::get($event_data["author"]);

            $months = array_flip(Jaris\Date::getMonths());

            $minute = strlen($event_data["minute"]) == 1 ?
                "0" . $event_data["minute"] : $event_data["minute"]
            ;

            $minute_to = strlen($event_data["minute_to"]) == 1 ?
                "0" . $event_data["minute_to"] : $event_data["minute_to"]
            ;

            $description = Jaris\InputFormats::parseEmails(
                Jaris\InputFormats::parseLinks(
                    Jaris\InputFormats::parseLineBreaks(
                        Jaris\Util::stripHTMLTags($event_data["description"])
                    )
                )
            );

            $place = Jaris\InputFormats::parseEmails(
                Jaris\InputFormats::parseLinks(
                    Jaris\InputFormats::parseLineBreaks(
                        Jaris\Util::stripHTMLTags($event_data["place"])
                    )
                )
            );

            $url = trim($event_data["all_data"]["url"]) != "" ?
                (
                    "http://" . str_replace(
                        array("http://", "https://"),
                        "",
                        $event_data["all_data"]["url"]
                    )
                )
                :
                ""
            ;

            $images = array();
            foreach($event_data["attachments"] as $file)
            {
                $file_parts = explode(".", $file);

                if(
                    in_array(
                        strtolower(array_pop($file_parts)),
                        array("jpg", "jpeg", "gif", "png"))
                )
                {
                    $images[] = $file;
                }
            }

            $files = array();
            foreach($event_data["attachments"] as $file)
            {
                $file_parts = explode(".", $file);

                if(
                    in_array(
                        strtolower(array_pop($file_parts)),
                        array("pdf"))
                )
                {
                    $files[] = $file;
                }
            }

            $theme = Jaris\Site::$theme;

            $custom_template = Jaris\Themes::directory($theme)
                . "calendar-event.php"
            ;

            $default_template = Jaris\Modules::directory("calendar")
                . "templates/calendar-event.php"
            ;

            if(file_exists($custom_template))
            {
                include($custom_template);
            }
            else
            {
                include($default_template);
            }
        }
        else
        {
            Jaris\Uri::go($uri);
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
