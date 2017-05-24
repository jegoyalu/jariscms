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
        <?php print t("Add Event"); ?>
    field;

    field: content
    <?php
        $page_data = null;

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

        if(!calendar_event_can_add($_REQUEST["uri"], $page_data))
        {
            Jaris\Authentication::protectedPage();
        }

        $uri = trim($_REQUEST["uri"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("calendar-add-event")
        )
        {
            $data["title"] = trim($_REQUEST["title"]);
            $data["description"] = $_REQUEST["description"];
            $data["place"] = $_REQUEST["place"];
            $data["url"] = Jaris\Util::stripHTMLTags($_REQUEST["url"]);
            $data["longitude"] = Jaris\Util::stripHTMLTags($_REQUEST["longitude"]);
            $data["latitude"] = Jaris\Util::stripHTMLTags($_REQUEST["latitude"]);
            $data["day"] = intval($_REQUEST["day"]);
            $data["month"] = intval($_REQUEST["month"]);
            $data["year"] = intval($_REQUEST["year"]);
            $data["day_to"] = intval($_REQUEST["day_to"]);
            $data["month_to"] = intval($_REQUEST["month_to"]);
            $data["year_to"] = intval($_REQUEST["year_to"]);
            $data["hour"] = intval($_REQUEST["hour"]);
            $data["minute"] = intval($_REQUEST["minute"]);
            $data["is_am"] = intval($_REQUEST["is_am"]) >= 1 ? 1 : 0;
            $data["hour_to"] = intval($_REQUEST["hour_to"]);
            $data["minute_to"] = intval($_REQUEST["minute_to"]);
            $data["is_am_to"] = intval($_REQUEST["is_am_to"]) >= 1 ? 1 : 0;
            $data["attachments"] = array();
            $data["author"] = Jaris\Authentication::currentUser();

            if(
                $page_data["add_event_approval"] &&
                !Jaris\Pages::userIsOwner($uri, $page_data)
            )
            {
                $data["approved"] = 0;

                $user_data = Jaris\Users::get($page_data["author"]);

                $to = array($user_data["name"] => $user_data["email"]);

                $html_message = t("A new calendar event has been created and is pending for your approval.");
                $html_message .= " ";
                $html_message .= t("For more details or approve this event visit the calendar page:") . "<br />";

                $html_message .= "<a target=\"_blank\" href=\"" .
                    Jaris\Uri::url("admin/user", array("return" => $_REQUEST["uri"])) .
                    "\">" . Jaris\Uri::url("admin/user", array("return" => $_REQUEST["uri"])) .
                    "</a>"
                ;

                Jaris\Mail::send($to, t("New calendar event pending for approval"), $html_message);
            }
            else
            {
                $data["approved"] = 1;
            }

            if(is_array($_FILES["attachments"]["name"]))
            {
                foreach($_FILES["attachments"]["name"] as $file_index => $file_name)
                {
                    $file = array(
                        "name" => $file_name,
                        "tmp_name" => $_FILES["attachments"]["tmp_name"][$file_index]
                    );

                    $data["attachments"][] = Jaris\Files::addUpload(
                        $file,
                        "calendar/" .  str_replace("/", "-", $uri)
                    );
                }
            }

            if(!is_array($data["attachments"]))
            {
                $data["attachments"] = array();
            }

            //Chmod all uploaded files to 0755
            foreach($data["attachments"] as $file)
            {
                chmod(
                    Jaris\Files::get(
                        $file,
                        "calendar/" .  str_replace("/", "-", $uri)
                    ),
                    0755
                );
            }

            calendar_event_add($data, $uri);

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/calendar/events", "calendar"),
                array("uri" => $uri)
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/calendar/events", "calendar"),
                array("uri" => $uri)
            );
        }

        $parameters["name"] = "calendar-add-event";
        $parameters["class"] = "calendar-add-event";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/calendar/events/add", "calendar")
        );
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "value" => $_REQUEST["title"],
            "required" => true,
            "description" => t("A brief description of the event.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "description",
            "value" => $_REQUEST["description"],
            "label" => t("Description:"),
            "required" => true,
            "description" => t("A detailed description of the event.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "place",
            "value" => $_REQUEST["place"],
            "label" => t("Place:"),
            "description" => t("The physical address or description of the area where the event is goint to take place.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "url",
            "value" => $_REQUEST["url"],
            "label" => t("Url:"),
            "description" => t("A general purpose url or registration page for this event.")
        );

        $fields[] = array(
            "type" => "gmap-location",
            "name" => "location",
            "lat_name" => "latitude",
            "lng_name" => "longitude",
            "lat" => $_REQUEST["latitude"],
            "lng" => $_REQUEST["longitude"],
            "label" => t("Map:"),
            "description" => t("Select or search the location of the event on the map. Don't select anything to not display the map."),
            "required" => true
        );

        $fieldset[] = array("fields" => $fields);

        $fields_date[] = array(
            "type" => "other",
            "html_code" => '<h3>'.t("From").'</h3>'
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "day",
            "selected" => isset($_REQUEST["day"]) ?
                $_REQUEST["day"]
                :
                date("j", time()),
            "value" => Jaris\Date::getDays(),
            "inline" => true,
            "required" => true,
            "label" => t("Day:")
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "month",
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"]
                :
                date("n", time()),
            "value" => Jaris\Date::getMonths(),
            "inline" => true,
            "required" => true,
            "label" => t("Month:")
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "year",
            "selected" => isset($_REQUEST["year"]) ?
                $_REQUEST["year"]
                :
                date("Y", time()),
            "value" => Jaris\Date::getYears(10),
            "inline" => true,
            "required" => true,
            "label" => t("Year:")
        );

        $fields_date[] = array(
            "type" => "other",
            "html_code" => '<h3>'.t("To").'</h3>'
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "day_to",
            "selected" => isset($_REQUEST["day_to"]) ?
                $_REQUEST["day_to"]
                :
                date("j", time()),
            "value" => Jaris\Date::getDays(),
            "inline" => true,
            "required" => true,
            "label" => t("Day:")
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "month_to",
            "selected" => isset($_REQUEST["month_to"]) ?
                $_REQUEST["month_to"]
                :
                date("n", time()),
            "value" => Jaris\Date::getMonths(),
            "inline" => true,
            "required" => true,
            "label" => t("Month:")
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "year_to",
            "selected" => isset($_REQUEST["year_to"]) ?
                $_REQUEST["year_to"]
                :
                date("Y", time()),
            "value" => Jaris\Date::getYears(10),
            "inline" => true,
            "required" => true,
            "label" => t("Year:")
        );

        $fieldset[] = array("name"=>t("Date"), "fields" => $fields_date);

        $fields_time[] = array(
            "type" => "other",
            "html_code" => '<h3>'.t("From").'</h3>'
        );

        $hours = array();
        for($i=1; $i<=12; $i++)
        {
            $hours[$i] = $i;
        }
        $fields_time[] = array(
            "type" => "select",
            "name" => "hour",
            "selected" => $_REQUEST["hour"],
            "value" => $hours,
            "inline" => true,
            "required" => true,
            "label" => t("Hour:")
        );

        $minutes = array();
        for($i=0; $i<=60; $i++)
        {
            if(strlen($i) < 2)
            {
                $minutes["0$i"] = $i;
            }
            else
            {
                $minutes[$i] = $i;
            }
        }
        $fields_time[] = array(
            "type" => "select",
            "name" => "minute",
            "selected" => $_REQUEST["minute"],
            "value" => $minutes,
            "inline" => true,
            "required" => true,
            "label" => t("Minute:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "is_am",
            "selected" => $_REQUEST["is_am"],
            "value" => array("AM"=>1, "PM"=>0),
            "inline" => true,
            "required" => true,
            "label" => t("AM/PM:")
        );

        $fields_time[] = array(
            "type" => "other",
            "html_code" => '<h3>'.t("To").'</h3>'
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "hour_to",
            "selected" => $_REQUEST["hour_to"],
            "value" => $hours,
            "inline" => true,
            "required" => true,
            "label" => t("Hour:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "minute_to",
            "selected" => $_REQUEST["minute_to"],
            "value" => $minutes,
            "inline" => true,
            "required" => true,
            "label" => t("Minute:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "is_am_to",
            "selected" => $_REQUEST["is_am_to"],
            "value" => array("AM"=>1, "PM"=>0),
            "inline" => true,
            "required" => true,
            "label" => t("AM/PM:")
        );

        $fieldset[] = array("name"=>t("Time"), "fields" => $fields_time);

        $fields_submit[] = array(
            "type" => "file",
            "name" => "attachments",
            "multiple" => true,
            "valid_types" => "jpg, jpeg, png, gif, pdf",
            "label" => t("Attachments:"),
            "description" => t("Here you can upload promotional material like flyers or downloadable pdf.")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_submit);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
