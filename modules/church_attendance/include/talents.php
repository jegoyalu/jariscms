<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions to manage church accounting income/expenses categories.
 */

function church_attendance_talent_add($label)
{
    Jaris\Sql::escapeVar($label);

    $db = Jaris\Sql::open("church_attendance_talents");

    $insert = "insert into church_attendance_talents "
        . "(label) "
        . "values("
        . "'$label'"
        . ")"
    ;

    Jaris\Sql::query($insert, $db);

    Jaris\Sql::close($db);
}

function church_attendance_talent_edit($id, $label)
{
    $db = Jaris\Sql::open("church_attendance_talents");

    Jaris\Sql::escapeVar($id, "int");
    Jaris\Sql::escapeVar($label);

    $update = "update church_attendance_talents "
        . "set "
        . "label = '$label' "
        . "where id=$id"
    ;

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);
}

function church_attendance_talent_delete($id)
{
    $db = Jaris\Sql::open("church_attendance_talents");

    Jaris\Sql::escapeVar($id, "int");

    $delete = "delete from church_attendance_talents where id=$id";

    Jaris\Sql::query($delete, $db);

    Jaris\Sql::close($db);
}

function church_attendance_talent_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_attendance_talents");

    $select = "select * from church_attendance_talents where id=$id";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function church_attendance_talent_list()
{
    static $list = [];

    if (empty($list)) {
        $list = [];

        $db = Jaris\Sql::open("church_attendance_talents");

        $select = "select * from church_attendance_talents "
            . "order by label asc"
        ;

        $result = Jaris\Sql::query($select, $db);

        while ($data = Jaris\Sql::fetchArray($result)) {
            $list[$data["id"]] = $data["label"];
        }

        Jaris\Sql::close($db);
    }

    return $list;
}

function church_attendance_talent_label($id)
{
    $list = church_attendance_talent_list();

    return $list[$id];
}
