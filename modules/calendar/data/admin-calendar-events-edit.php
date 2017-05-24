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
        <?php print t("Edit Event"); ?>
    field;

    field: content
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".navigation-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".navigation-list tbody tr td a.delete").click(function() {
                $(this).parent().parent().fadeOut(1000, function() {
                    $(this).remove();
                });
            });
        });
    </script>

    <style>
        .navigation-list tbody tr:hover
        {
            background-color: #d3d3d3;
        }
    </style>
    <?php
        $page_data = null;

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

        $is_page_owner = Jaris\Pages::userIsOwner($uri, $page_data);

        if(
            $event_data["author"] != Jaris\Authentication::currentUser())
        {
            if(!$is_page_owner)
            {
                Jaris\Authentication::protectedPage();
            }
        }

        Jaris\View::addTab(
            t("View Event"),
            Jaris\Modules::getPageUri("calendar/event", "calendar"),
            array("uri" => $uri, "id" => $id)
        );

        Jaris\View::addTab(
            t("Delete Event"),
            Jaris\Modules::getPageUri("admin/calendar/events/delete", "calendar"),
            array("uri" => $uri, "id" => $id)
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("calendar-edit-event")
        )
        {
            $data = calendar_event_data($id, $uri);

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

            //Delete removed attachments
            if(is_array($data["attachments"]))
            {
                foreach($data["attachments"] as $file)
                {
                    if(!in_array($file, $_REQUEST["files_list"]))
                    {
                        Jaris\Files::delete(
                            $file,
                            "calendar/" .  str_replace("/", "-", $uri)
                        );
                    }
                }
            }

            $data["attachments"] = $_REQUEST["files_list"];

            //Add new attachments
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

            calendar_event_edit($id, $data, $uri);

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

        $parameters["name"] = "calendar-edit-event";
        $parameters["class"] = "calendar-edit-event";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/calendar/events/edit", "calendar")
        );
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        );

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "value" => isset($_REQUEST["title"]) ?
                $_REQUEST["title"]
                :
                $event_data["title"],
            "required" => true,
            "description" => t("A brief description of the event.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "description",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"]
                :
                $event_data["description"],
            "label" => t("Description:"),
            "required" => true,
            "description" => t("A detailed description of the event.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "place",
            "value" => isset($_REQUEST["place"]) ?
                $_REQUEST["place"]
                :
                $event_data["place"],
            "label" => t("Place:"),
            "description" => t("The physical address or description of the area where the event is goint to take place.")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "url",
            "value" => isset($_REQUEST["url"]) ?
                $_REQUEST["url"]
                :
                $event_data["url"],
            "label" => t("Url:"),
            "description" => t("A general purpose url or registration page for this event.")
        );

        $fields[] = array(
            "type" => "gmap-location",
            "name" => "location",
            "lat_name" => "latitude",
            "lng_name" => "longitude",
            "lat" => isset($_REQUEST["latitude"]) ?
                $_REQUEST["latitude"]
                :
                $event_data["latitude"],
            "lng" => isset($_REQUEST["longitude"]) ?
                $_REQUEST["longitude"]
                :
                $event_data["longitude"],
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
                $event_data["day"],
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
                $event_data["month"],
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
                $event_data["year"],
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
                $event_data["day_to"],
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
                $event_data["month_to"],
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
                $event_data["year_to"],
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
            "selected" => isset($_REQUEST["hour"]) ?
                $_REQUEST["hour"]
                :
                $event_data["hour"],
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
            "selected" => isset($_REQUEST["minute"]) ?
                $_REQUEST["minute"]
                :
                $event_data["minute"],
            "value" => $minutes,
            "inline" => true,
            "required" => true,
            "label" => t("Minute:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "is_am",
            "selected" => isset($_REQUEST["is_am"]) ?
                $_REQUEST["is_am"]
                :
                $event_data["is_am"],
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
            "selected" => isset($_REQUEST["hour_to"]) ?
                $_REQUEST["hour_to"]
                :
                $event_data["hour_to"],
            "value" => $hours,
            "inline" => true,
            "required" => true,
            "label" => t("Hour:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "minute_to",
            "selected" => isset($_REQUEST["minute_to"]) ?
                $_REQUEST["minute_to"]
                :
                $event_data["minute_to"],
            "value" => $minutes,
            "inline" => true,
            "required" => true,
            "label" => t("Minute:")
        );

        $fields_time[] = array(
            "type" => "select",
            "name" => "is_am_to",
            "selected" => isset($_REQUEST["is_am_to"]) ?
                $_REQUEST["is_am_to"]
                :
                $event_data["is_am_to"],
            "value" => array("AM"=>1, "PM"=>0),
            "inline" => true,
            "required" => true,
            "label" => t("AM/PM:")
        );

        $fieldset[] = array("name"=>t("Time"), "fields" => $fields_time);

        $files = "<table class=\"navigation-list\">";
        $files .= "<thead>";
        $files .= "<tr>";
        $files .= "<td>" . t("Order") . "</td>";
        $files .= "<td>" . t("File") . "</td>";
        $files .= "<td>" . t("Action") . "</td>";
        $files .= "</tr>";
        $files .= "</thead>";

        $files .= "<tbody>";
        if(is_array($event_data["attachments"]))
        {
            foreach($event_data["attachments"] as $file)
            {
                $files .= "<tr>";

                $files .= "<td><a class=\"sort-handle\"></a></td>";

                $files .= "<td>
                    <input type=\"hidden\" name=\"files_list[]\" value=\"$file\"  />
                    $file
                </td>";

                $files .= "<td><a class=\"delete\" style=\"cursor: pointer\">" .
                    t("Delete") .
                    "</a></td>"
                ;

                $files .= "</tr>";
            }
        }
        $files .= "</tbody>";

        $files .= "</table>";

        $fields_submit[] = array(
            "type" => "file",
            "name" => "attachments",
            "multiple" => true,
            "valid_types" => "jpg, jpeg, png, gif, pdf",
            "label" => t("Attachments:"),
            "description" => t("Here you can upload promotional material like flyers or downloadable pdf.")
        );

        $fields_submit[] = array(
            "type" => "other",
            "html_code" => "<div style=\"margin-top: 10px;\"><strong>" .
                t("Current attachments:") .
                "</strong><hr />$files</div>"
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
