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
        <?php print t("Delete Event") ?>
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

        if(isset($_REQUEST["btnYes"]))
        {
            calendar_event_delete($id, $uri);

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/calendar/events", "calendar"),
                array("uri" => $uri)
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/calendar/events", "calendar"),
                array("uri" => $uri)
            );
        }
    ?>

    <form
        class="calendar-delete-event"
        method="post"
        action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/calendar/events/delete", "calendar")) ?>"
    >
        <input type="hidden" name="id" value="<?php print $id ?>" />
        <input type="hidden" name="uri" value="<?php print $uri ?>" />
        <div><?php print t("Are you sure you want to delete the calendar event?") ?>
            <div>
                <?php
                    print $event_data["title"];
                ?>
            </div>
        </div>
        <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
        <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
    </form>
    field;

    field: is_system
        1
    field;
row;
