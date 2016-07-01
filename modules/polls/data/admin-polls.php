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
        <?php print t("Polls") ?>
    field;

    field: content
    <style>
        .navigation
        {
            margin: 15px 0 15px 0;
        }

        .navigation a
        {
            margin: 0 10px 0 0;
        }

        .navigation .current-page
        {
            border: solid 1px #d3d3d3;
            background-color: #666666;
            color: #fff;
            font-weight: bold;
        }

        #polls-list
        {
            width: 100%;
        }

        #polls-list td
        {
            width: auto;
            border-bottom:  dashed 1px #d3d3d3;
            padding: 3px;
        }

        #polls-list thead td
        {
            font-weight: bold;
            border-bottom:  solid 1px #d3d3d3;
        }
    </style>

    <?php
        Jaris\Authentication::protectedPage();

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $polls_count = polls_count_polls();

        print "<b>" . t("Total polls:") . "</b> " . $polls_count . "<br />";

        $polls = polls_get_polls($page - 1);

        polls_print_polls_navigation($polls_count, $page);

        print "<table id=\"polls-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Title") . "</td>";
        print "<td>" . t("Actions") . "</td>";
        print "</tr>";
        print "</thead>";

        foreach($polls as $uri)
        {
            $polls_data = Jaris\Pages::get($uri);

            print "<tr>";

            print "<td>" . date("n/j/Y", $polls_data["created_date"]) . "</td>";

            print "<td>" .
                $polls_data["title"] .
                "</td>";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri("admin/polls/edit", "polls"),
                array("uri" => $uri)
            );

            $delete_url = Jaris\Uri::url(
                "admin/pages/delete",
                array("uri" => $uri)
            );

            print "<td>" .
                "<a href=\"$edit_url\">" . t("Edit") . "</a> " .
                "<a href=\"$delete_url\">" . t("Delete") . "</a>" .
                "</td>";

            print "</tr>";
        }

        print "</table>";

        polls_print_polls_navigation($polls_count, $page);
    ?>
    field;

    field: is_system
        1
    field;
row;
