<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions to manage church accounting income/expenses categories.
 */

function church_attendance_reunion_add($data, $members=array())
{
    $activity_date = strtotime(
        "{$data['day']}-{$data['month']}-{$data['year']}"
    );

    Jaris\Sql::escapeVar($data["day"], "int");
    Jaris\Sql::escapeVar($data["month"], "int");
    Jaris\Sql::escapeVar($data["year"], "int");

    Jaris\Sql::escapeVar($data["title"]);
    Jaris\Sql::escapeVar($data["description"]);
    Jaris\Sql::escapeVar($data["hour"]);
    Jaris\Sql::escapeVar($data["minute"]);
    Jaris\Sql::escapeVar($data["is_am"]);
    Jaris\Sql::escapeVar($data["calendar_event_id"], "int");
    Jaris\Sql::escapeVar($data["calendar_uri"]);
    Jaris\Sql::escapeVar($data["registered_by"]);

    $db = Jaris\Sql::open("church_attendance_reunions");

    $insert = "insert into church_attendance_reunions "
        . "("
        . "activity_date, day, month, year, title, "
        . "description, hour, minute, is_am, calendar_event_id, "
        . "calendar_uri, attendance_count, registered_by"
        . ") "
        . "values("
        . "'$activity_date', "
        . "'{$data["day"]}', "
        . "'{$data["month"]}', "
        . "'{$data["year"]}', "
        . "'{$data["title"]}', "
        . "'{$data["description"]}', "
        . "'{$data["hour"]}', "
        . "'{$data["minute"]}', "
        . "'{$data["is_am"]}', "
        . "{$data["calendar_event_id"]},"
        . "'{$data["calendar_uri"]}',"
        . count($members) . ", "
        . "'{$data["registered_by"]}'"
        . ")"
    ;

    Jaris\Sql::query($insert, $db);

    $reunion_id = Jaris\Sql::lastInsertRowId($db);

    Jaris\Sql::close($db);

    $db = Jaris\Sql::open("church_attendance_registry");

    Jaris\Sql::beginTransaction($db);

    $db_members = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::beginTransaction($db_members);

    foreach($members as $member_data)
    {
        $member_data = explode(",", $member_data);

        $insert = "insert into church_attendance_registry "
            . "("
            . "activity_id, "
            . "member_id, "
            . "group_id, "
            . "gender"
            . ") "
            . "values("
            . "$reunion_id,"
            . "{$member_data[0]},"
            . "{$member_data[1]},"
            . "'{$member_data[2]}'"
            . ")"
        ;

        Jaris\Sql::query($insert, $db);

        if($activity_date > $member_data[3])
        {
            $update_member = "update church_attendance_members set "
                . "last_visit_date = '$activity_date' "
                . "where id=".$member_data[0]
            ;

            Jaris\Sql::query($update_member, $db_members);
        }
    }

    Jaris\Sql::commitTransaction($db_members);

    Jaris\Sql::close($db_members);

    Jaris\Sql::commitTransaction($db);

    Jaris\Sql::close($db);
}

function church_attendance_reunion_edit($id, $data, $members=array())
{
    $activity_date = strtotime(
        "{$data['day']}-{$data['month']}-{$data['year']}"
    );

    Jaris\Sql::escapeVar($id, "int");

    Jaris\Sql::escapeVar($data["day"], "int");
    Jaris\Sql::escapeVar($data["month"], "int");
    Jaris\Sql::escapeVar($data["year"], "int");

    Jaris\Sql::escapeVar($data["title"]);
    Jaris\Sql::escapeVar($data["description"]);
    Jaris\Sql::escapeVar($data["hour"]);
    Jaris\Sql::escapeVar($data["minute"]);
    Jaris\Sql::escapeVar($data["is_am"]);
    Jaris\Sql::escapeVar($data["calendar_event_id"], "int");
    Jaris\Sql::escapeVar($data["calendar_uri"]);
    Jaris\Sql::escapeVar($data["registered_by"]);

    $db = Jaris\Sql::open("church_attendance_reunions");

    $update = "update church_attendance_reunions "
        . "set "
        . "activity_date='$activity_date', "
        . "day='{$data["day"]}', "
        . "month='{$data["month"]}', "
        . "year='{$data["year"]}', "
        . "title='{$data["title"]}', "
        . "description='{$data["description"]}', "
        . "hour='{$data["hour"]}', "
        . "minute='{$data["minute"]}', "
        . "is_am='{$data["is_am"]}', "
        . "calendar_event_id={$data["calendar_event_id"]},"
        . "calendar_uri='{$data["calendar_uri"]}',"
        . "attendance_count=".count($members).", "
        . "registered_by='{$data["registered_by"]}'"
        . "where id=$id"
    ;

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);

    $db = Jaris\Sql::open("church_attendance_registry");

    Jaris\Sql::query(
        "delete from church_attendance_registry where activity_id=$id",
        $db
    );

    Jaris\Sql::beginTransaction($db);

    $db_members = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::beginTransaction($db_members);

    foreach($members as $member_data)
    {
        $member_data = explode(",", $member_data);

        $insert = "insert into church_attendance_registry "
            . "("
            . "activity_id, "
            . "member_id, "
            . "group_id, "
            . "gender"
            . ") "
            . "values("
            . "$id,"
            . "{$member_data[0]},"
            . "{$member_data[1]},"
            . "'{$member_data[2]}'"
            . ")"
        ;

        Jaris\Sql::query($insert, $db);

        if($activity_date > $member_data[3])
        {
            $update_member = "update church_attendance_members set "
                . "last_visit_date = '$activity_date' "
                . "where id=".$member_data[0]
            ;

            Jaris\Sql::query($update_member, $db_members);
        }
    }

    Jaris\Sql::commitTransaction($db_members);

    Jaris\Sql::close($db_members);

    Jaris\Sql::commitTransaction($db);

    Jaris\Sql::close($db);
}

function church_attendance_reunion_delete($id)
{
    $db = Jaris\Sql::open("church_attendance_reunions");

    Jaris\Sql::escapeVar($id, "int");

    $delete = "delete from church_attendance_reunions where id=$id";

    Jaris\Sql::query($delete, $db);

    Jaris\Sql::close($db);
}

function church_attendance_reunion_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_attendance_reunions");

    $select = "select * from church_attendance_reunions where id=$id";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}
