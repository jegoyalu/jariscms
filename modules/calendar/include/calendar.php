<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions to manage calendar events.
 */

function calendar_event_add($data, $uri)
{
    calendar_event_create_db($uri);

    $directory = Jaris\Pages::getPath($uri);

    $all_data = serialize($data);
    Jaris\Sql::escapeVar($all_data);

    $attachments = serialize($data["attachments"]);
    Jaris\Sql::escapeVar($attachments);

    Jaris\Sql::escapeVar($data["approved"], "int");

    Jaris\Sql::escapeVar($data["day"], "int");
    Jaris\Sql::escapeVar($data["month"], "int");
    Jaris\Sql::escapeVar($data["year"], "int");
    Jaris\Sql::escapeVar($data["day_to"], "int");
    Jaris\Sql::escapeVar($data["month_to"], "int");
    Jaris\Sql::escapeVar($data["year_to"], "int");

    Jaris\Sql::escapeVar($data["hour"], "int");
    Jaris\Sql::escapeVar($data["minute"], "int");
    Jaris\Sql::escapeVar($data["is_am"], "int");
    Jaris\Sql::escapeVar($data["hour_to"], "int");
    Jaris\Sql::escapeVar($data["minute_to"], "int");
    Jaris\Sql::escapeVar($data["is_am_to"], "int");

    Jaris\Sql::escapeArray($data);

    $am_pm = $data["is_am"] ? "AM" : "PM";

    $minute = str_pad($data['minute'], 2, "0", STR_PAD_LEFT);

    $date = DateTime::createFromFormat(
        'd/m/Y H:i A',
        "{$data['day']}/{$data['month']}/{$data['year']} "
        . "{$data['hour']}:{$minute} $am_pm"
    )->getTimestamp();

    $am_pm_to = $data["is_am_to"] ? "AM" : "PM";

    $minute_to = str_pad($data['minute_to'], 2, "0", STR_PAD_LEFT);

    $date_to = DateTime::createFromFormat(
        'd/m/Y H:i A',
        "{$data['day_to']}/{$data['month_to']}/{$data['year_to']} "
        . "{$data['hour_to']}:{$minute_to} $am_pm_to"
    )->getTimestamp();

    $db = Jaris\Sql::open("calendar_events", $directory);

    Jaris\Sql::query(
        "insert into calendar_events "
        . "("
        . "title,"
        . "description,"
        . "place,"
        . "latitude,"
        . "longitude,"
        . "day,"
        . "month,"
        . "year,"
        . "day_to,"
        . "month_to,"
        . "year_to,"
        . "hour,"
        . "minute,"
        . "is_am,"
        . "hour_to,"
        . "minute_to,"
        . "is_am_to,"
        . "date,"
        . "date_to,"
        . "attachments,"
        . "author,"
        . "approved,"
        . "public_invites,"
        . "current_invites,"
        . "maximum_invites,"
        . "all_data"
        . ") values("
        . "'{$data['title']}',"
        . "'{$data['description']}',"
        . "'{$data['place']}',"
        . "'{$data['latitude']}',"
        . "'{$data['longitude']}',"
        . "{$data['day']},"
        . "{$data['month']},"
        . "{$data['year']},"
        . "{$data['day_to']},"
        . "{$data['month_to']},"
        . "{$data['year_to']},"
        . "{$data['hour']},"
        . "{$data['minute']},"
        . "{$data['is_am']},"
        . "{$data['hour_to']},"
        . "{$data['minute_to']},"
        . "{$data['is_am_to']},"
        . "'$date',"
        . "'$date_to',"
        . "'$attachments',"
        . "'{$data['author']}',"
        . "{$data['approved']},"
        . "0,"
        . "0,"
        . "0,"
        . "'$all_data'"
        . ")",
        $db
    );

    // Get the row id of inserted data.
    $event_id = Jaris\Sql::lastInsertRowId($db);

    Jaris\Sql::close($db);

    // Insert to global events db
    Jaris\Sql::escapeVar($uri);

    $db = Jaris\Sql::open("calendar_events");

    Jaris\Sql::query(
        "insert into calendar_events "
        . "("
        . "event_id,"
        . "uri,"
        . "title,"
        . "description,"
        . "place,"
        . "hour,"
        . "minute,"
        . "is_am,"
        . "hour_to,"
        . "minute_to,"
        . "is_am_to,"
        . "date,"
        . "date_to,"
        . "approved"
        . ") values("
        . "$event_id,"
        . "'$uri',"
        . "'{$data['title']}',"
        . "'{$data['description']}',"
        . "'{$data['place']}',"
        . "{$data['hour']},"
        . "{$data['minute']},"
        . "{$data['is_am']},"
        . "{$data['hour_to']},"
        . "{$data['minute_to']},"
        . "{$data['is_am_to']},"
        . "'$date',"
        . "'$date_to',"
        . "{$data['approved']}"
        . ")",
        $db
    );

    Jaris\Sql::close($db);
}

function calendar_event_edit($id, $data, $uri)
{
    $directory = Jaris\Pages::getPath($uri);

    Jaris\Sql::escapeVar($id, "int");

    $all_data = serialize($data);
    Jaris\Sql::escapeVar($all_data);

    $attachments = serialize($data["attachments"]);
    Jaris\Sql::escapeVar($attachments);

    Jaris\Sql::escapeVar($data["approved"], "int");

    Jaris\Sql::escapeVar($data["day"], "int");
    Jaris\Sql::escapeVar($data["month"], "int");
    Jaris\Sql::escapeVar($data["year"], "int");
    Jaris\Sql::escapeVar($data["day_to"], "int");
    Jaris\Sql::escapeVar($data["month_to"], "int");
    Jaris\Sql::escapeVar($data["year_to"], "int");

    Jaris\Sql::escapeVar($data["hour"], "int");
    Jaris\Sql::escapeVar($data["minute"], "int");
    Jaris\Sql::escapeVar($data["is_am"], "int");
    Jaris\Sql::escapeVar($data["hour_to"], "int");
    Jaris\Sql::escapeVar($data["minute_to"], "int");
    Jaris\Sql::escapeVar($data["is_am_to"], "int");

    Jaris\Sql::escapeArray($data);

    $am_pm = $data["is_am"] ? "AM" : "PM";

    $minute = str_pad($data['minute'], 2, "0", STR_PAD_LEFT);

    $date = DateTime::createFromFormat(
        'd/m/Y H:i A',
        "{$data['day']}/{$data['month']}/{$data['year']} "
        . "{$data['hour']}:{$minute} $am_pm"
    )->getTimestamp();

    $am_pm_to = $data["is_am_to"] ? "AM" : "PM";

    $minute_to = str_pad($data['minute_to'], 2, "0", STR_PAD_LEFT);

    $date_to = DateTime::createFromFormat(
        'd/m/Y H:i A',
        "{$data['day_to']}/{$data['month_to']}/{$data['year_to']} "
        . "{$data['hour_to']}:{$minute_to} $am_pm_to"
    )->getTimestamp();

    $db = Jaris\Sql::open("calendar_events", $directory);

    Jaris\Sql::query(
        "update calendar_events set "
        . "title='{$data['title']}',"
        . "description='{$data['description']}',"
        . "place='{$data['place']}',"
        . "latitude='{$data['latitude']}',"
        . "longitude='{$data['longitude']}',"
        . "day={$data['day']},"
        . "month={$data['month']},"
        . "year={$data['year']},"
        . "day_to={$data['day_to']},"
        . "month_to={$data['month_to']},"
        . "year_to={$data['year_to']},"
        . "hour={$data['hour']},"
        . "minute={$data['minute']},"
        . "is_am={$data['is_am']},"
        . "hour_to={$data['hour_to']},"
        . "minute_to={$data['minute_to']},"
        . "is_am_to={$data['is_am_to']},"
        . "date='$date',"
        . "date_to='$date_to',"
        . "attachments='$attachments',"
        . "author='{$data['author']}',"
        . "approved={$data['approved']},"
        . "public_invites=0,"
        . "current_invites=0,"
        . "maximum_invites=0,"
        . "all_data='$all_data' "
        . "where id=$id",
        $db
    );

    Jaris\Sql::close($db);

    // Edit global events database
    Jaris\Sql::escapeVar($uri);

    $db = Jaris\Sql::open("calendar_events");

    Jaris\Sql::query(
        "update calendar_events set "
        . "title='{$data['title']}',"
        . "description='{$data['description']}',"
        . "place='{$data['place']}',"
        . "hour={$data['hour']},"
        . "minute={$data['minute']},"
        . "is_am={$data['is_am']},"
        . "hour_to={$data['hour_to']},"
        . "minute_to={$data['minute_to']},"
        . "is_am_to={$data['is_am_to']},"
        . "date='$date',"
        . "date_to='$date_to',"
        . "approved={$data['approved']} "
        . "where event_id=$id and uri='$uri'",
        $db
    );

    Jaris\Sql::close($db);
}

function calendar_event_data($id, $uri)
{
    $directory = Jaris\Pages::getPath($uri);

    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("calendar_events", $directory);

    $result = Jaris\Sql::query(
        "select * from calendar_events where id=$id",
        $db
    );

    $event = [];

    if ($data = Jaris\Sql::fetchArray($result)) {
        $event = $data;

        $event["title"] = htmlspecialchars_decode($event["title"]);
        $event["desrcription"] = htmlspecialchars_decode($event["desrcription"]);
        $event["place"] = htmlspecialchars_decode($event["place"]);
        $event["attachments"] = unserialize($event["attachments"]);
        $event["all_data"] = unserialize($event["all_data"]);
    }

    Jaris\Sql::close($db);

    return $event;
}

function calendar_event_get_events($month, $year, $uri, $user=null)
{
    $directory = Jaris\Pages::getPath($uri);

    Jaris\Sql::escapeVar($month, "int");
    Jaris\Sql::escapeVar($year, "int");

    $author = "";
    if ($user) {
        Jaris\Sql::escapeVar($user);
        $author .= "and author='$user'";
    }

    $results = Jaris\Sql::getDataList(
        "calendar_events",
        "calendar_events",
        0,
        32,
        "where month=$month and year=$year and approved=1 $author "
        . "order by date asc",
        "*",
        $directory
    );

    $events = [];

    foreach ($results as $event) {
        if (!isset($events[$event["day"]])) {
            $events[$event["day"]] = [];
        }

        $events[$event["day"]][] = $event;
    }

    return $events;
}

function calendar_event_delete($id, $uri)
{
    $directory = Jaris\Pages::getPath($uri);

    $event_data = calendar_event_data($id, $uri);

    foreach ($event_data["attachments"] as $file) {
        $file_path = Jaris\Files::getDir()
            . "calendar/" .  str_replace("/", "-", $uri) . "/"
            . $file
        ;

        unlink($file_path);
    }

    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("calendar_events", $directory);

    Jaris\Sql::query(
        "delete from calendar_events where id=$id",
        $db
    );

    Jaris\Sql::close($db);

    // Delete from global events db
    Jaris\Sql::escapeVar($uri);

    $db = Jaris\Sql::open("calendar_events");

    Jaris\Sql::query(
        "delete from calendar_events where event_id=$id and uri='$uri'",
        $db
    );

    Jaris\Sql::close($db);
}

function calendar_event_can_add($uri, $page_data=null, $user_group=null)
{
    $uri = trim($uri);

    if (!is_array($page_data)) {
        $page_data = Jaris\Pages::get($uri);
    }

    if (!$user_group) {
        $user_group = Jaris\Authentication::currentUserGroup();
    }

    if (!is_array($page_data["groups_add_event"])) {
        $page_data["groups_add_event"] = unserialize(
            $page_data["groups_add_event"]
        );
    }

    $can_add_events = false;

    if (Jaris\Pages::userIsOwner($uri, $page_data)) {
        $can_add_events = true;
    } elseif (
        is_array($page_data["groups_add_event"]) &&
        count($page_data["groups_add_event"]) > 0
    ) {
        if (in_array($user_group, $page_data["groups_add_event"])) {
            $can_add_events = true;
        }
    }

    return $can_add_events;
}

function calendar_event_create_db($uri)
{
    $directory = Jaris\Pages::getPath($uri);

    if (!Jaris\Sql::dbExists("calendar_events", $directory)) {
        $db = Jaris\Sql::open("calendar_events", $directory);

        Jaris\Sql::query(
            "create table calendar_events "
            . "("
            . "id integer primary key,"
            . "title text,"
            . "description text,"
            . "day integer,"
            . "month integer,"
            . "year integer,"
            . "day_to integer,"
            . "month_to integer,"
            . "year_to integer,"
            . "hour integer,"
            . "minute integer,"
            . "is_am integer,"
            . "hour_to integer,"
            . "minute_to integer,"
            . "is_am_to integer,"
            . "date text,"
            . "date_to text,"
            . "place text,"
            . "longitude text,"
            . "latitude text,"
            . "attachments text,"
            . "author text,"
            . "approved integer,"
            . "allow_invties integer,"
            . "public_invites integer,"
            . "current_invites integer,"
            . "maximum_invites integer,"
            . "all_data text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index calendar_events_index on calendar_events "
            . "("
            . "id desc,"
            . "day desc,"
            . "month desc,"
            . "year desc,"
            . "date desc,"
            . "date_to desc,"
            . "author desc,"
            . "approved desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}

function calendar_generate($month, $year, $uri, $user=null)
{
    $calendar = "";

    // Display calendar navigation
    $months = array_flip(Jaris\Date::getMonths());

    $previous_month = $month;
    $next_month = $month;
    $previous_year = $year;
    $next_year = $year;

    if ($month == 1) {
        $previous_month = 12;
        $previous_year --;
    } else {
        $previous_month--;
    }

    if ($month == 12) {
        $next_month = 1;
        $next_year++;
    } else {
        $next_month++;
    }

    $previous = Jaris\Uri::url(
        Jaris\Uri::get(),
        [
            "month"=>$previous_month,
            "year"=>$previous_year
        ]
    );

    $next = Jaris\Uri::url(
        Jaris\Uri::get(),
        [
            "month"=>$next_month,
            "year"=>$next_year
        ]
    );

    $calendar .= "<style>"
        . ".calendar-consecutive{display: none;}"
        . "@media all and (max-width: 850px){"
        . ".calendar-consecutive{display: block;}"
        . ".calendar-nav{display: none;}"
        . ".calendar{display: none;}"
        . "}"
        . "</style>"
    ;

    $calendar .= '<table class="calendar-nav">'
        . '<tr>'
        . '<td><a href="'.$previous.'">&laquo;</a></td>'
        . '<td>'.$months[$month].' '.$year.'</td>'
        . '<td><a href="'.$next.'">&raquo;</a></td>'
        . '</tr>'
        . '</table>'
    ;

    // List of events by day
    $events = calendar_event_get_events($month, $year, $uri, $user);

    // draw table
    $calendar .= '<table class="calendar">';

    // table headings
    $headings = [
        t('Sunday'), t('Monday'), t('Tuesday'), t('Wednesday'),
        t('Thursday'), t('Friday'), t('Saturday')
    ];

    $calendar .= '<thead>';
    $calendar .= '<tr>'
        . '<td>'
        . implode('</td><td>', $headings)
        . '</td>'
        . '</tr>';
    $calendar .= '</thead>';

    // days and weeks vars now ...
    $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    $days_in_this_week = 1;
    $day_counter = 0;

    $today_day = date("j", time());
    $today_month = date("n", time());
    $today_year = date("Y", time());

    $calendar .= '<tbody>';

    // row for week one
    $calendar .= '<tr>';

    // print "blank" days until the first of the current week
    for ($x = 0; $x < $running_day; $x++) {
        $calendar.= '<td class="blank"> </td>';
        $days_in_this_week++;
    }

    // keep going with days....
    for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {
        $today = "";

        if (
            $year == $today_year &&
            $month == $today_month &&
            $list_day == $today_day
        ) {
            $today .= ' class="today"';
        }

        $calendar .= '<td'.$today.'>';
        $calendar .= '<div class="expand">';

        // add in the day number
        $calendar .= '<div class="day">'.$list_day.'</div>';

        // add the events list for the day if any
        if (isset($events[$list_day])) {
            $calendar .= "<ul>";
            foreach ($events[$list_day] as $event) {
                $event_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("calendar/event", "calendar"),
                    ["uri"=>$uri, "id"=>$event["id"]]
                );

                $calendar .= "<li><a href=\"$event_url\">{$event['title']}</a></li>";
            }
            $calendar .= "</ul>";
        }

        $calendar .= '</div>';
        $calendar .= '</td>';
        if ($running_day == 6) {
            $calendar .= '</tr>';
            if (($day_counter+1) != $days_in_month) {
                $calendar .= '<tr>';
            }
            $running_day = -1;
            $days_in_this_week = 0;
        }

        $days_in_this_week++;
        $running_day++;
        $day_counter++;
    }

    // finish the rest of the days in the week
    if ($days_in_this_week < 8) {
        for ($x = 1; $x <= (8 - $days_in_this_week); $x++) {
            $calendar .= '<td class="blank"> </td>';
        }
    }

    // final row
    $calendar .= '</tr>';

    $calendar .= '</tbody>';

    // end the table
    $calendar .= '</table>';

    $calendar .= calendar_generate_consecutive($uri);

    // all done, return result
    return $calendar;
}

function calendar_generate_consecutive($uri)
{
    $page = 1;

    if (!empty($_REQUEST["page"])) {
        $page = intval($_REQUEST["page"]);
    }

    $page_data = [];
    $db = null;
    $directory = "";

    $output = '<div class="calendar-consecutive">';

    if ($uri) {
        $page_data = Jaris\Pages::get($uri);
        $directory = Jaris\Pages::getPath($uri);

        if (Jaris\Sql::dbExists("calendar_events", $directory)) {
            $db = Jaris\Sql::open("calendar_events", $directory);
        }
    } else {
        $db = Jaris\Sql::open("calendar_events");
    }

    $count = 0;

    if (!is_null($db)) {
        $current_date = strtotime(
            date("j")
            . "-"
            . date("n")
            . "-"
            . date("Y")
        );

        $count = Jaris\Sql::countColumn(
            "calendar_events",
            "calendar_events",
            "date",
            "where date >= '$current_date'",
            $directory
        );

        $list = Jaris\Sql::getDataList(
            "calendar_events",
            "calendar_events",
            $page-1,
            50,
            "where date >= '$current_date' order by date asc",
            "*",
            $directory
        );

        foreach ($list as $event_data) {
            ob_start();

            $event_uri = !is_null($uri) ? $uri : $event_data["uri"];
            $event_id = isset($event_data["id"]) ?
                $event_data["id"] : $event_data["event_id"]
            ;

            $minute = strlen($event_data["minute"]) == 1 ?
                "0" . $event_data["minute"] : $event_data["minute"]
            ;

            $minute_to = strlen($event_data["minute_to"]) == 1 ?
                "0" . $event_data["minute_to"] : $event_data["minute_to"]
            ;

            $event_data["day"] = date("j", $event_data["date"]);

            $day = strlen($event_data["day"]) == 1 ?
                "0" . $event_data["day"] : $event_data["day"]
            ;
            $month = array_flip(
                Jaris\Date::getMonths()
                )[date("n", intval($event_data["date"]))]
            ;
            $year = date("Y", intval($event_data["date"]));
            $title = '<a href="'.Jaris\Uri::url(
                Jaris\Modules::getPageUri("calendar/event", "calendar"),
                ["uri" => $event_uri, "id"=>$event_id]
                ).'">'
                . Jaris\Util::stripHTMLTags($event_data["title"])
                . "</a>"
            ;
            $place = Jaris\Util::stripHTMLTags($event_data["place"]);
            $hours = $event_data["hour"] . ":"
                . $minute . " "
                . ($event_data["is_am"] ? "AM" : "PM")
                . "&nbsp;&nbsp;-&nbsp;&nbsp;"
                . $event_data["hour_to"] . ":"
                . $minute_to . " "
                . ($event_data["is_am_to"] ? "AM" : "PM")
            ;

            include(calendar_block_result_template($uri, "consecutive"));

            $output .= ob_get_contents();

            ob_end_clean();
        }
    }

    $output .= '</div>';

    //Generate navigation
    ob_start();
    Jaris\System::printNavigation(
        $count,
        $page,
        Jaris\Uri::get(),
        "",
        50
    );
    $output .= ob_get_contents();
    ob_end_clean();

    return $output;
}

function calendar_block_print_results($block_data, $uri=null)
{
    $page_data = [];
    $db = null;
    $directory = "";

    $output = "";

    if ($uri) {
        $page_data = Jaris\Pages::get($uri);
        $directory = Jaris\Pages::getPath($uri);

        if (Jaris\Sql::dbExists("calendar_events", $directory)) {
            $db = Jaris\Sql::open("calendar_events", $directory);
        }
    } else {
        $db = Jaris\Sql::open("calendar_events");
    }

    if (!is_null($db)) {
        $current_date = strtotime(
            date("j")
            . "-"
            . date("n")
            . "-"
            . date("Y")
        );

        $list = Jaris\Sql::getDataList(
            "calendar_events",
            "calendar_events",
            0,
            intval($block_data["results_to_show"]),
            "where date >= '$current_date' order by date asc",
            "*",
            $directory
        );

        foreach ($list as $event_data) {
            ob_start();

            $event_uri = !is_null($uri) ? $uri : $event_data["uri"];
            $event_id = isset($event_data["id"]) ?
                $event_data["id"] : $event_data["event_id"]
            ;

            $minute = strlen($event_data["minute"]) == 1 ?
                "0" . $event_data["minute"] : $event_data["minute"]
            ;

            $minute_to = strlen($event_data["minute_to"]) == 1 ?
                "0" . $event_data["minute_to"] : $event_data["minute_to"]
            ;

            $event_data["day"] = date("j", $event_data["date"]);

            $day = strlen($event_data["day"]) == 1 ?
                "0" . $event_data["day"] : $event_data["day"]
            ;
            $month = array_flip(
                Jaris\Date::getMonths()
                )[date("n", intval($event_data["date"]))]
            ;
            $year = date("Y", $event_data["date"]);
            $title = '<a href="'.Jaris\Uri::url(
                Jaris\Modules::getPageUri("calendar/event", "calendar"),
                ["uri" => $event_uri, "id"=>$event_id]
                ).'">'
                . Jaris\Util::stripHTMLTags($event_data["title"])
                . "</a>"
            ;
            $place = Jaris\Util::stripHTMLTags($event_data["place"]);
            $hours = $event_data["hour"] . ":"
                . $minute . " "
                . ($event_data["is_am"] ? "AM" : "PM")
                . "&nbsp;&nbsp;-&nbsp;&nbsp;"
                . $event_data["hour_to"] . ":"
                . $minute_to . " "
                . ($event_data["is_am_to"] ? "AM" : "PM")
            ;

            include(calendar_block_result_template($uri));

            $output .= ob_get_contents();

            ob_end_clean();
        }
    }

    return $output;
}

function calendar_block_result_template($page=null, $template="block")
{
    $theme = Jaris\Site::$theme;
    $page = str_replace("/", "-", $page);

    $custom_template = Jaris\Themes::directory($theme) . "calendar-$template.php";
    $custom_template_uri = Jaris\Themes::directory($theme) . "calendar-$template-" . $page . ".php";

    $template_path = "";

    if (!is_null($page) && file_exists($custom_template_uri)) {
        $template_path .= $custom_template_uri;
    } elseif (file_exists($custom_template)) {
        $template_path .= $custom_template;
    } else {
        $template_path = Jaris\Modules::directory("calendar") . "templates/calendar-$template.php";
    }

    return $template_path;
}
