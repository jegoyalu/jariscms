<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function church_attendance_upgrade()
{
    if(Jaris\Sql::dbExists("church_attendance_members"))
    {
        $data = Jaris\Sql::getDataList(
            "church_attendance_members",
            "church_attendance_members",
            0,
            1
        );

        if(!array_key_exists("civil_status", $data[0]))
        {
            $db = Jaris\Sql::open("church_attendance_members");

            Jaris\Sql::query(
                "alter table church_attendance_members add column civil_status text",
                $db
            );

            Jaris\Sql::query(
                "alter table church_attendance_members add column courses text",
                $db
            );

            Jaris\Sql::query(
                "alter table church_attendance_members add column time_following_christ text",
                $db
            );

            Jaris\Sql::query(
                "alter table church_attendance_members add column time_following_christ_unit text",
                $db
            );

            Jaris\Sql::query(
                "alter table church_attendance_members add column work_place text",
                $db
            );

            Jaris\Sql::query(
                "alter table church_attendance_members add column work_phone text",
                $db
            );

            Jaris\Sql::close($db);
        }
    }

    // Courses list
    if(!Jaris\Sql::dbExists("church_attendance_courses"))
    {
        $db = Jaris\Sql::open("church_attendance_courses");

        Jaris\Sql::query(
            "create table church_attendance_courses ("
            . "id integer primary key, "
            . "label text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_courses_index "
            . "on church_attendance_courses ("
            . "id desc"
            . ")",
            $db
        );

        // Strings to assist poedit or other translation tools.
        $strings = array(
            t("Discipleschip 101"),
            t("Discipleschip 102"),
            t("Institute"),
            t("Other")
        );

        //Default income categories
        $groups = array(
            "Discipleschip 101",
            "Discipleschip 102",
            "Institute",
            "Other"
        );

        Jaris\Sql::beginTransaction($db);

        foreach($groups as $group)
        {
            $insert = "insert into church_attendance_courses "
                . "(label) "
                . "values("
                . "'$group'"
                . ")"
            ;

            Jaris\Sql::query($insert, $db);
        }

        Jaris\Sql::commitTransaction($db);

        Jaris\Sql::close($db);
    }

    // Courses count
    if(!Jaris\Sql::dbExists("church_attendance_courses_count"))
    {
        $db = Jaris\Sql::open("church_attendance_courses_count");

        Jaris\Sql::query(
            "create table church_attendance_courses_count ("
            . "member_id integer, "
            . "course_id integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_courses_count_index "
            . "on church_attendance_courses_count ("
            . "member_id desc,"
            . "course_id desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}
