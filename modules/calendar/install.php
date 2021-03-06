<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for faqs module.
 */

function calendar_install()
{
    $string = t("Calendar");
    $string = t("Section to display a list of activities or events.");
    $string = t("A brief description of the calendar page.");
    $string = t("A full description of the calendar page.");

    //Create new calendar type
    $new_type["name"] = "Calendar";
    $new_type["description"] = "Section to display a list of activities or events.";
    $new_type["title_description"] = "A brief description of the calendar page.";
    $new_type["content_description"] = "A full description of the calendar page.";
    $new_type["uri_scheme"] = "{type}/{title}";

    Jaris\Types::add("calendar", $new_type);

    //Create calendar events database
    if (!Jaris\Sql::dbExists("calendar_events")) {
        $db = Jaris\Sql::open("calendar_events");

        Jaris\Sql::query(
            "create table calendar_events ("
            . "event_id integer,"
            . "uri text,"
            . "date text,"
            . "date_to text,"
            . "title text,"
            . "description text,"
            . "place text,"
            . "hour integer,"
            . "minute integer,"
            . "is_am integer,"
            . "hour_to integer,"
            . "minute_to integer,"
            . "is_am_to integer,"
            . "approved integer,"
            . "repeat_event integer,"
            . "repeat_type integer,"
            . "repeat_amount integer,"
            . "repeat_count integer,"
            . "repeat_end_date integer,"
            . "repeat_generated integer,"
            . "repeat_generated_id integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index calendar_events_index on calendar_events ("
            . "event_id desc, "
            . "uri desc, "
            . "date desc, "
            . "approved desc, "
            . "repeat_event desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    //Create calendar events database
    if (!Jaris\Sql::dbExists("calendar_repeat_month")) {
        $db = Jaris\Sql::open("calendar_repeat_month");

        Jaris\Sql::query(
            "create table calendar_repeat_month ("
            . "month integer,"
            . "year integer,"
            . "marker_timestamp integer,"
            . "repeats_added integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index calendar_repeat_month_index on calendar_repeat_month ("
            . "month desc,"
            . "year desc,"
            . "marker_timestamp desc,"
            . "repeats_added desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}
