<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions related to the management of poll blocks.
 */

function add_recent_poll($poll_page, $poll_title)
{
    if (trim($poll_page) == "") {
        return false;
    }

    $block = Jaris\Blocks::getByField("poll_block", "1");

    $block["content"] = '
    <?php
        $page_data = Jaris\Pages::get("' . $poll_page . '");
        $page_data["option_name"] = unserialize($page_data["option_name"]);
        $page_data["option_value"] = unserialize($page_data["option_value"]);

        $poll_data = array();
        foreach($page_data["option_name"] as $id=>$name)
        {
            $poll_data[t($name)] = $id;
        }

        $parameters["class"] = "block-poll";
        $parameters["action"] = Jaris\Uri::url(Jaris\Modules::getPageUri("admin/polls/vote", "polls"));
        $parameters["method"] = "get";

        $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>"' . $poll_page . '");
        $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>Jaris\Uri::get());
        $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
        $fields[] = array("type"=>"submit", "value"=>t("Vote"));

        $fieldset[] = array("fields"=>$fields);

        print t("' . $poll_title . '") . "<br />";

        if(!isset($_COOKIE["poll"]["' . $poll_page . '"]) && !poll_expired("' . $poll_page . '"))
        {
            print Jaris\Forms::generate($parameters, $fieldset);
        }
        else
        {
            $total_votes = 0;
            foreach($page_data["option_value"] as $value)
            {
                $total_votes += $value;
            }

            $option_percent = array();
            foreach($page_data["option_value"] as $value)
            {
                if($value <= 0)
                {
                    $option_percent[] = 0;
                }
                else
                {
                    $option_percent[] = floor(($value / $total_votes) * 100);
                }
            }

            print "<div style=\"padding: 4px\">";
            for($i=0; $i<count($page_data["option_name"]); $i++)
            {
                print "<br />";
                print "<b>" . t($page_data["option_name"][$i]) . ":</b>";
                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
            }

            print "<br /><a href=\"" . Jaris\Uri::url("' . $poll_page . '") . "\">" . t("More Details") . "</a>";
            print "</div>";
        }
    ?>
    ';
    $block["is_system"] = true;
    $block["poll_block"] = "1";
    $block["poll_page"] = $poll_page;

    Jaris\Blocks::editByField("poll_block", "1", $block);
}

function edit_recent_poll($poll_page, $poll_title, $current_page)
{
    if (trim($poll_page) == "") {
        return false;
    }

    $block = Jaris\Blocks::getByField("poll_block", "1");

    $block["content"] = '
    <?php
        $page_data = Jaris\Pages::get("' . $poll_page . '");
        $page_data["option_name"] = unserialize($page_data["option_name"]);
        $page_data["option_value"] = unserialize($page_data["option_value"]);

        $poll_data = array();
        foreach($page_data["option_name"] as $id=>$name)
        {
            $poll_data[t($name)] = $id;
        }

        $parameters["class"] = "block-poll";
        $parameters["action"] = Jaris\Uri::url(Jaris\Modules::getPageUri("admin/polls/vote", "polls"));
        $parameters["method"] = "get";

        $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>"' . $poll_page . '");
        $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>Jaris\Uri::get());
        $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
        $fields[] = array("type"=>"submit", "value"=>t("Vote"));

        $fieldset[] = array("fields"=>$fields);

        print t("' . $poll_title . '") . "<br />";

        if(!isset($_COOKIE["poll"]["' . $poll_page . '"]) && !poll_expired("' . $poll_page . '"))
        {
            print Jaris\Forms::generate($parameters, $fieldset);
        }
        else
        {
            $total_votes = 0;
            foreach($page_data["option_value"] as $value)
            {
                $total_votes += $value;
            }

            $option_percent = array();
            foreach($page_data["option_value"] as $value)
            {
                if($value <= 0)
                {
                    $option_percent[] = 0;
                }
                else
                {
                    $option_percent[] = floor(($value / $total_votes) * 100);
                }
            }

            print "<div style=\"padding: 4px\">";
            for($i=0; $i<count($page_data["option_name"]); $i++)
            {
                print "<br />";
                print "<b>" . t($page_data["option_name"][$i]) . ":</b>";
                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
            }

            print "<br /><a href=\"" . Jaris\Uri::url("' . $poll_page . '") . "\">" . t("More Details") . "</a>";
            print "</div>";
        }
    ?>
    ';
    $block["is_system"] = true;
    $block["poll_block"] = "1";
    $block["poll_page"] = $poll_page;

    Jaris\Blocks::editByField("poll_page", $current_page, $block);
}

function delete_recent_poll($poll_page)
{
    if (trim($poll_page) == "") {
        return false;
    }

    $block = Jaris\Blocks::getByField("poll_block", "1");

    $block["content"] = '';
    $block["is_system"] = true;
    $block["poll_block"] = "1";
    $block["poll_page"] = "";

    Jaris\Blocks::editByField("poll_page", $poll_page, $block);
}

function poll_expired($poll_uri)
{
    $poll_data = Jaris\Pages::get($poll_uri);

    $time_diffrence = time() - $poll_data["created_date"];


    $days = floor($time_diffrence / 60 / 60 / 24);

    if ($poll_data["duration"] <= 0) {
        return false;
    } else {
        return $days >= $poll_data["duration"];
    }
}
