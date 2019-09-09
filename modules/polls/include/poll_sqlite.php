<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions related to the management of poll in sqlite database
 */

function polls_sqlite_add($uri, $date)
{
    if (!Jaris\Sql::dbExists("polls")) {
        $db = Jaris\Sql::open("polls");
        Jaris\Sql::query("create table polls (uri text, date text)", $db);
        Jaris\Sql::close($db);
    }

    $db = Jaris\Sql::open("polls");
    Jaris\Sql::query("insert into polls (uri, date) values ('$uri', '$date')", $db);
    Jaris\Sql::close($db);
}

function polls_sqlite_delete($uri)
{
    if (Jaris\Sql::dbExists("polls")) {
        $db = Jaris\Sql::open("polls");
        Jaris\Sql::query("delete from polls where uri = '$uri'", $db);
        Jaris\Sql::close($db);
    }
}

function polls_print_polls_navigation($polls_count, $page, $amount = 30)
{
    $page_count = 0;
    $remainder_pages = 0;

    if ($polls_count <= $amount) {
        $page_count = 1;
    } else {
        $page_count = floor($polls_count / $amount);
        $remainder_pages = $polls_count % $amount;

        if ($remainder_pages > 0) {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if ($page > $page_count || $page < 0 || $page_count == 1) {
        return false;
    }

    print "<div class=\"navigation\">\n";
    if ($page != 1) {
        $previous_page = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/polls", "polls"),
            ["page" => $page - 1]
        );

        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for ($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++) {
        $text = t($start_page);

        if ($start_page > $page || $start_page < $page) {
            $url = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/polls", "polls"),
                ["page" => $start_page]
            );

            print "<a class=\"page\" href=\"$url\">$text</a>";
        } else {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if ($page < $page_count) {
        $next_page = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/polls", "polls"),
            ["page" => $page + 1]
        );

        $next_text = t("Next");
        print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
    }
    print "</div>\n";
}

function polls_get_polls($page = 0, $limit = 30)
{
    $db = null;
    $page *= $limit;
    $polls = [];

    if (Jaris\Sql::dbExists("polls")) {
        $db = Jaris\Sql::open("polls");

        $result = Jaris\Sql::query(
            "select uri from polls order by date desc limit $page, $limit",
            $db
        );
    } else {
        return $polls;
    }

    $fields = [];

    if ($fields = Jaris\Sql::fetchArray($result)) {
        $polls[] = $fields["uri"];

        while ($fields = Jaris\Sql::fetchArray($result)) {
            $polls[] = $fields["uri"];
        }

        Jaris\Sql::close($db);
        return $polls;
    } else {
        Jaris\Sql::close($db);
        return $polls;
    }
}

function polls_count_polls()
{
    if (Jaris\Sql::dbExists("polls")) {
        $db = Jaris\Sql::open("polls");

        $result = Jaris\Sql::query(
            "select count(uri) as 'polls_count' from polls",
            $db
        );

        $count = Jaris\Sql::fetchArray($result);

        Jaris\Sql::close($db);

        return $count["polls_count"];
    } else {
        return 0;
    }
}
