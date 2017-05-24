<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function church_attendance_member_add($data)
{
    $birth_date = strtotime(
        "{$data['birth_day']}-{$data['birth_month']}-{$data['birth_year']}"
    );

    $talents = array();

    if(is_array($data["talents"]))
    {
        $talents = $data["talents"];
        $data["talents"] = serialize($data["talents"]);
    }

    Jaris\Sql::escapeArray($data);

    $db = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::query(
        "insert into church_attendance_members ("
        . "first_name,"
        . "last_name,"
        . "maiden_name,"
        . "gender,"
        . "birth_date,"
        . "birth_day,"
        . "birth_month,"
        . "birth_year,"
        . "taken_discipleship,"
        . "year_accepted_christ,"
        . "baptized,"
        . "family_at_home,"
        . "talents,"
        . "is_member,"
        . "postal_address,"
        . "residential_address,"
        . "email,"
        . "phone,"
        . "mobile_phone,"
        . "group_id,"
        . "last_visit_date,"
        . "notes"
        . ") "
        . "values ("
        . "'{$data['first_name']}',"
        . "'{$data['last_name']}',"
        . "'{$data['maiden_name']}',"
        . "'{$data['gender']}',"
        . "'$birth_date',"
        . "'{$data['birth_day']}',"
        . "'{$data['birth_month']}',"
        . "'{$data['birth_year']}',"
        . "{$data['taken_discipleship']},"
        . "{$data['year_accepted_christ']},"
        . "{$data['baptized']},"
        . "{$data['family_at_home']},"
        . "'{$data['talents']}',"
        . "{$data['is_member']},"
        . "'{$data['postal_address']}',"
        . "'{$data['residential_address']}',"
        . "'{$data['email']}',"
        . "'{$data['phone']}',"
        . "'{$data['mobile_phone']}',"
        . "{$data['group_id']},"
        . "'{$data['last_visit_date']}',"
        . "'{$data['notes']}'"
        . ")",
        $db
    );

    $member_id = Jaris\Sql::lastInsertRowId($db);

    Jaris\Sql::close($db);

    $db_talents_count = Jaris\Sql::open("church_attendance_talents_count");

    Jaris\Sql::beginTransaction($db_talents_count);

    foreach($talents as $talent_id)
    {
        Jaris\Sql::query(
            "insert into church_attendance_talents_count "
            . "("
            . "member_id,"
            . "talent_id"
            . ") "
            . "values ("
            . "$member_id,"
            . "$talent_id"
            . ")",
            $db_talents_count
        );
    }

    Jaris\Sql::commitTransaction($db_talents_count);

    Jaris\Sql::close($db_talents_count);
}

function church_attendance_member_edit($id, $data)
{
    $birth_date = strtotime(
        "{$data['birth_day']}-{$data['birth_month']}-{$data['birth_year']}"
    );

    $talents = array();

    if(is_array($data["talents"]))
    {
        $talents = $data["talents"];
        $data["talents"] = serialize($data["talents"]);
    }

    Jaris\Sql::escapeVar($id, "int");

    Jaris\Sql::escapeArray($data);

    $db = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::query(
        "update church_attendance_members set "
        . "first_name='{$data['first_name']}',"
        . "last_name='{$data['last_name']}',"
        . "maiden_name='{$data['maiden_name']}',"
        . "gender='{$data['gender']}',"
        . "birth_date='$birth_date',"
        . "birth_day={$data['birth_day']},"
        . "birth_month={$data['birth_month']},"
        . "birth_year={$data['birth_year']},"
        . "taken_discipleship={$data['taken_discipleship']},"
        . "year_accepted_christ={$data['year_accepted_christ']},"
        . "baptized={$data['baptized']},"
        . "family_at_home={$data['family_at_home']},"
        . "talents='{$data['talents']}',"
        . "is_member={$data['is_member']},"
        . "postal_address='{$data['postal_address']}', "
        . "residential_address='{$data['residential_address']}', "
        . "email='{$data['email']}',"
        . "phone='{$data['phone']}',"
        . "mobile_phone='{$data['mobile_phone']}',"
        . "group_id={$data['group_id']},"
        . "notes='{$data['notes']}' "
        . "where id=$id ",
        $db
    );

    Jaris\Sql::close($db);

    $db_registry = Jaris\Sql::open("church_attendance_registry");

    Jaris\Sql::query(
        "update church_attendance_registry set "
        . "group_id={$data['group_id']},"
        . "gender='{$data['gender']}' "
        . "where member_id=$id",
        $db_registry
    );

    Jaris\Sql::close($db_registry);

    $db_talents_count = Jaris\Sql::open("church_attendance_talents_count");

    Jaris\Sql::query(
        "delete from church_attendance_talents_count "
        . "where member_id=$id",
        $db_talents_count
    );

    Jaris\Sql::beginTransaction($db_talents_count);

    foreach($talents as $talent_id)
    {
        Jaris\Sql::query(
            "insert into church_attendance_talents_count "
            . "("
            . "member_id,"
            . "talent_id"
            . ") "
            . "values ("
            . "$id,"
            . "$talent_id"
            . ")",
            $db_talents_count
        );
    }

    Jaris\Sql::commitTransaction($db_talents_count);

    Jaris\Sql::close($db_talents_count);
}

function church_attendance_member_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_attendance_members");

    $result = Jaris\Sql::query(
        "select * from church_attendance_members where id=$id",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    $data["talents"] = unserialize($data["talents"]);

    return $data;
}

function church_attendance_member_delete($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::query(
        "delete from church_attendance_members where id=$id",
        $db
    );

    Jaris\Sql::close($db);

    $db_talents_count = Jaris\Sql::open("church_attendance_talents_count");

    Jaris\Sql::query(
        "delete from church_attendance_talents_count "
        . "where member_id=$id",
        $db_talents_count
    );

    Jaris\Sql::close($db_talents_count);
}

function church_attendance_member_move_to_other($current_group)
{
    Jaris\Sql::escapeVar($current_group, "int");

    $db = Jaris\Sql::open("church_attendance_members");

    Jaris\Sql::query(
        "update church_attendance_members set "
        . "group_id=5 "
        . "where group_id=$current_group ",
        $db
    );

    Jaris\Sql::close($db);
}
