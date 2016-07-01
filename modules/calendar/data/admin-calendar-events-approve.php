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
        <?php print t("Approve Event") ?>
    field;

    field: content
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

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        $uri = trim($_REQUEST["uri"]);

        $event_data = calendar_event_data($id, $uri);

        if(count($event_data) > 0)
        {
            $event_data["approved"] = 1;

            calendar_event_edit($id, $event_data, $uri);

            Jaris\View::addMessage(t("Event approved!"));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/calendar/events", "calendar"),
                array("uri"=>$uri)
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
