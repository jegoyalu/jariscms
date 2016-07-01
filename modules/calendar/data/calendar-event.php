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
            Jaris\View::addScript("http://maps.googleapis.com/maps/api/js?sensor=false");
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

            print '<div class="calendar-event">';

            print '<div class="publisher">'
                . '<strong>'.t("Published by:").'</strong> '
                . $user_data["name"]
                . '</div>'
            ;

            $months = array_flip(Jaris\Date::getMonths());

            print '<div class="date">'
                . '<strong>'.t("Date:").'</strong> '
                . $event_data["day"] . "/"
                . $months[$event_data["month"]] . "/"
                . $event_data["year"]
                . "&nbsp;&nbsp;-&nbsp;&nbsp;"
                . $event_data["day_to"] . "/"
                . $months[$event_data["month_to"]] . "/"
                . $event_data["year_to"]
                . '</div>'
            ;

            $minute = strlen($event_data["minute"]) == 1 ?
                "0" . $event_data["minute"] : $event_data["minute"]
            ;

            $minute_to = strlen($event_data["minute_to"]) == 1 ?
                "0" . $event_data["minute_to"] : $event_data["minute_to"]
            ;

            print '<div class="hours">'
                . '<strong>'.t("Hours:").'</strong> '
                . $event_data["hour"] . ":"
                . $minute . " "
                . ($event_data["is_am"] ? "AM" : "PM")
                . "&nbsp;&nbsp;-&nbsp;&nbsp;"
                . $event_data["hour_to"] . ":"
                . $minute_to . " "
                . ($event_data["is_am_to"] ? "AM" : "PM")
                . '</div>'
            ;

            $description = Jaris\InputFormats::parseEmails(
                Jaris\InputFormats::parseLinks(
                    Jaris\InputFormats::parseLineBreaks(
                        Jaris\Util::stripHTMLTags($event_data["description"])
                    )
                )
            );

            print '<div class="description">'
                . '<strong>'.t("Description:").'</strong> '
                . $description
                . '</div>'
            ;

            $place = Jaris\InputFormats::parseEmails(
                Jaris\InputFormats::parseLinks(
                    Jaris\InputFormats::parseLineBreaks(
                        Jaris\Util::stripHTMLTags($event_data["place"])
                    )
                )
            );

            if(trim($place) != "")
            {
                print '<div class="place">'
                    . '<strong>'.t("Place:").'</strong> '
                    . $place
                    . '</div>'
                ;
            }

            if(!empty($event_data["latitude"]) && !empty($event_data["longitude"]))
            {
                print '<div class="map">';
                print '<strong>'.t("Map:").'</strong> ';

                print '<div id="map" style="width: 100%; height: 300px"></div>';
                print '<script type="text/javascript">'
                . '$("#map").gmap3({'
                    . 'marker:{'
                    . 'latLng:['.$event_data["latitude"].','.$event_data["longitude"].']'
                    . '},'
                    . 'map:{'
                    . 'options:{'
                    . 'zoom: 10'
                    . '}'
                    . '}'
                    . '});'
                    . '</script>'
                ;
                print '</div>';
            }

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

            if(count($images) > 0)
            {
                print '<div class="images">'
                    . '<strong>'.t("Images:").'</strong> '
                ;

                foreach($images as $image)
                {
                    $image_path = Jaris\Files::getDir()
                        . "calendar/" .  str_replace("/", "-", $uri) . "/"
                        . $image
                    ;

                    $image_url = Jaris\Uri::url($image_path);

                    print '<img style="max-width: 800px; width: 97%;" src="'.$image_url.'" />';
                }

                print '</div>';
            }

            if(count($files) > 0)
            {
                print '<div class="documents">'
                    . '<strong>'.t("Documents:").'</strong> '
                ;

                foreach($files as $file)
                {
                    $file_path = Jaris\Files::getDir()
                        . "calendar/" .  str_replace("/", "-", $uri) . "/"
                        . $file
                    ;

                    $file_url = Jaris\Uri::url($file_path);

                    print '<a target="_blank" href="'.$file_url.'">'.$file.'</a>';
                }

                print '</div>';
            }

            print '</div>'; // .calendar-event
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
