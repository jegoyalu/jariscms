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
        <?php print t("Calendar Events") ?>
    field;

    field: content
    <?php
        // Get calendar uri
        $uri = "";

        $page_data = array();

        if(isset($_REQUEST["uri"]) && trim($_REQUEST["uri"]) != "")
        {
            $uri = trim($_REQUEST["uri"]);

            $page_data = Jaris\Pages::get($uri);

            if(!$page_data)
            {
                Jaris\Uri::go("");
            }

            if($page_data["type"] != "calendar")
            {
                Jaris\Uri::go("");
            }

            if(!calendar_event_can_add($uri, $page_data))
            {
                Jaris\Authentication::protectedPage();
            }
        }
        else
        {
            Jaris\Uri::go("");
        }

        // Get events list page number
        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        // Filter options
        $options = array();

        // Month
        if(trim($_REQUEST["month"]) != "")
        {
            $month = intval($_REQUEST["month"]);
            $options[] = "month=$month";
        }

        // Year
        if(trim($_REQUEST["year"]) != "")
        {
            $year = intval($_REQUEST["year"]);
            $options[] = "year=$year";
        }

        // Status
        $status = array(
            0 => t("Approved"),
            1 => t("Pending")
        );

        if(trim($_REQUEST["status"]) != "")
        {
            if($_REQUEST["status"] == "1")
            {
                $options[] = "approved=1";
            }
            else
            {
                $options[] = "approved=0";
            }
        }

        // Sorting
        $sorting_array = array(
            t("Date Descending") => "date_desc",
            t("Date Ascending") => "date_asc",
            t("Approved Descending") => "status_desc",
            t("Approved Ascending") => "status_asc"
        );

        $sorting = "";
        if(trim($_REQUEST["sorting"]) != "")
        {
            switch(trim($_REQUEST["sorting"]))
            {
                case "status_asc":
                    $sorting = 'order by approved asc';
                    break;
                case "status_desc":
                    $sorting = 'order by approved desc';
                    break;
                case "date_asc":
                    $sorting = 'order by date asc';
                    break;
                default:
                    $sorting = 'order by date desc';
            }
        }
        else
        {
            $sorting = 'order by date desc';
        }

        // Author
        if(!Jaris\Pages::userIsOwner($uri, $page_data))
        {
            $options[] = "author='".Jaris\Authentication::currentUser()."'";
        }

        // Assemble where
        $where = "";
        if(count($options) > 0)
        {
            $where = "where "
                . implode(" and ", $options)
            ;
        }

        Jaris\View::addTab(
            t("Add Event"),
            Jaris\Modules::getPageUri("admin/calendar/events/add", "calendar"),
            array("uri"=>$uri)
        );

        Jaris\View::addTab(
            t("View Calendar"),
            $uri
        );

        print "<form class=\"filter-results\" method=\"get\" action=\""
            . Jaris\Uri::url(Jaris\Uri::get())
            . "\" style=\"display: block; width: 100%;\">\n"
        ;
        print '<input type="hidden" name="uri" value="'.$uri.'" />';
        print "<div style=\"float: left\">";
        print t("Filter by:") . " <select onchange=\"javascript: this.form.submit()\" name=\"status\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach($status as $id=>$name)
        {
            $selected = "";

            if($_REQUEST["status"] == $id && trim($_REQUEST["status"]) != "")
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$id\">$name</option>\n";
        }
        print "</select>\n";

        print t("Month:") . " <select onchange=\"javascript: this.form.submit()\" name=\"month\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach(Jaris\Date::getMonths() as $month_name=>$month_value)
        {
            $selected = "";

            if($_REQUEST["month"] == $month_value)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$month_value\">$month_name</option>\n";
        }
        print "</select>\n";

        print t("Year:") . " <select onchange=\"javascript: this.form.submit()\" name=\"year\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach(Jaris\Date::getYears() as $year)
        {
            $selected = "";

            if($_REQUEST["year"] == $year)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$year\">$year</option>\n";
        }
        print "</select>\n";
        print "</div>";

        print "<div style=\"float: right; margin-left: 10px;\">";
        print t("Sort by:") . " <select onchange=\"javascript: this.form.submit()\" name=\"sorting\">\n";
        foreach($sorting_array as $label => $value)
        {
            $selected = "";

            if($_REQUEST["sorting"] == $value)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$value\">$label</option>\n";
        }
        print "</select>\n";
        print "</div>";
        print "</form>\n";

        print "<div style=\"clear: both\"></div>";

        print "<hr />";

        $directory = Jaris\Pages::getPath($uri);

        $count = 0;
        $events = array();

        if(Jaris\Sql::dbExists("calendar_events", $directory))
        {
            $count += Jaris\Sql::countColumn(
                "calendar_events",
                "calendar_events",
                "id",
                $where,
                $directory
            );

            $events = Jaris\Sql::getDataList(
                "calendar_events",
                "calendar_events",
                $page - 1,
                30,
                "$where $sorting",
                "*",
                $directory
            );
        }

        print "<h2>" . t("Total Events:") . " " . $count . "</h2>";

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Time") . "</td>";
        print "<td>" . t("Status") . "</td>";
        print "<td></td>";
        print "</tr>";
        print "</thead>";

        foreach($events as $event)
        {
            print "<tr>";

            $edit = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/calendar/events/edit", "calendar"),
                array("uri" => $uri, "id" => $event["id"])
            );

            print "<td>"
                . "<a href=\"$edit\">"
                . $event["title"]
                . "</a>"
                . "</td>"
            ;

            print "<td>" . date("d/m/Y", $event["date"]) . "</td>";

            $minute = strlen($event["minute"]) == 1 ?
                "0".$event["minute"]
                :
                $event["minute"]
            ;

            $minute_to = strlen($event["minute_to"]) == 1 ?
                "0".$event["minute_to"]
                :
                $event["minute_to"]
            ;

            print "<td>"
                . $event["hour"] . ":"
                . $minute . ":"
                . ($event["is_am"] ? "AM" : "PM")
                . " - "
                . $event["hour_to"] . ":"
                . $minute_to . ":"
                . ($event["is_am_to"] ? "AM" : "PM")
                . "</td>"
            ;

            print "<td>";
            if($event["approved"] == 1)
            {
                print t("Approved");
            }
            else
            {
                $approve .= " (<a href=\"" .
                    Jaris\Uri::url(
                        Jaris\Modules::getPageUri(
                            "admin/calendar/events/approve", "calendar"
                        ),
                        array("uri" => $uri, "id" => $event["id"])
                    ) . "\">" . t("approve") . "</a>)"
                ;

                print t("Pending") . $approve;
            }

            print "</td>";

            $delete = "<a href=\"" .
                Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/calendar/events/delete", "calendar"
                    ),
                    array("uri" => $uri, "id" => $event["id"])
                ) . "\">" . t("delete") . "</a>"
            ;

            print "<td>"
                . $delete
                . "</td>";

            print "</tr>";
        }

        print "</table>";

        Jaris\System::printNavigation(
            $count,
            $page,
            "admin/calendar/events",
            "calendar",
            30,
            array(
                "uri"=>$uri,
                "status"=>$_REQUEST["status"],
                "month"=>$_REQUEST["month"],
                "year"=>$_REQUEST["year"],
                "sorting"=>$_REQUEST["sorting"]
            )
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
