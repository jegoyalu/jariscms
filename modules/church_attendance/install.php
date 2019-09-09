<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for module.
 */

function church_attendance_install()
{
    // Create income database
    if(!Jaris\Sql::dbExists("church_attendance_reunions"))
    {
        //Income database
        $db = Jaris\Sql::open("church_attendance_reunions");

        Jaris\Sql::query(
            "create table church_attendance_reunions ("
            . "id integer primary key, "
            . "activity_date text, "
            . "day integer, "
            . "month integer, "
            . "year integer, "
            . "title text, "
            . "description text, "
            . "hour text, "
            . "minute text, "
            . "is_am text, "
            . "calendar_event_id integer,"
            . "calendar_uri text,"
            . "attendance_count integer, "
            . "registered_by text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_reunions_index "
            . "on church_attendance_reunions ("
            . "id desc, "
            . "activity_date desc, "
            . "day desc, "
            . "month desc, "
            . "year desc, "
            . "calendar_event_id desc,"
            . "calendar_uri desc,"
            . "attendance_count desc, "
            . "registered_by desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    // Income/Expenses category database
    if(!Jaris\Sql::dbExists("church_attendance_groups"))
    {
        $db = Jaris\Sql::open("church_attendance_groups");

        Jaris\Sql::query(
            "create table church_attendance_groups ("
            . "id integer primary key, "
            . "label text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_groups_index "
            . "on church_attendance_groups ("
            . "id desc"
            . ")",
            $db
        );

        // Strings to assist poedit or other translation tools.
        $strings = array(
            // Income
            t("Gentlemen"),
            t("Ladies"),
            t("Boys"),
            t("Girls"),
            t("Other")
        );

        //Default income categories
        $groups = array(
            "Gentlemen",
            "Ladies",
            "Boys",
            "Girls",
            "Other"
        );

        Jaris\Sql::beginTransaction($db);

        foreach($groups as $group)
        {
            $insert = "insert into church_attendance_groups "
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

    // Talents list
    if(!Jaris\Sql::dbExists("church_attendance_talents"))
    {
        $db = Jaris\Sql::open("church_attendance_talents");

        Jaris\Sql::query(
            "create table church_attendance_talents ("
            . "id integer primary key, "
            . "label text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_talents_index "
            . "on church_attendance_talents ("
            . "id desc"
            . ")",
            $db
        );

        // Strings to assist poedit or other translation tools.
        $strings = array(
            t("Graphic Design"),
            t("Adults Teacher"),
            t("Poetry Compositor"),
            t("Drama"),
            t("Pantomimes"),
            t("Preach"),
            t("Declaim"),
            t("Music Composer"),
            t("Sing"),
            t("Visit the sick"),
            t("Dance"),
            t("Sound"),
            t("Music"),
            t("Kids Teacher"),
            t("Pray for the sick"),
            t("Computers"),
            t("Counseling"),
            t("Hand out treaties")
        );

        //Default income categories
        $groups = array(
            "Graphic Design",
            "Adults Teacher",
            "Poetry Compositor",
            "Drama",
            "Pantomimes",
            "Preach",
            "Declaim",
            "Music Composer",
            "Sing",
            "Visit the sick",
            "Dance",
            "Sound",
            "Music",
            "Kids Teacher",
            "Pray for the sick",
            "Computers",
            "Counseling",
            "Hand out treaties"
        );

        Jaris\Sql::beginTransaction($db);

        foreach($groups as $group)
        {
            $insert = "insert into church_attendance_talents "
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

    // Talents count
    if(!Jaris\Sql::dbExists("church_attendance_talents_count"))
    {
        $db = Jaris\Sql::open("church_attendance_talents_count");

        Jaris\Sql::query(
            "create table church_attendance_talents_count ("
            . "member_id integer, "
            . "talent_id integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_talents_count_index "
            . "on church_attendance_talents_count ("
            . "member_id desc,"
            . "talent_id desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    // Members database
    if(!Jaris\Sql::dbExists("church_attendance_members"))
    {
        $db = Jaris\Sql::open("church_attendance_members");

        Jaris\Sql::query(
            "create table church_attendance_members ("
            . "id integer primary key, "
            . "first_name text, "
            . "last_name text, "
            . "maiden_name text, "
            . "gender text, "
            . "civil_status text, "
            . "is_member integer, "
            . "postal_address text, "
            . "residential_address text, "
            . "year_accepted_christ integer,"
            . "time_following_christ text,"
            . "time_following_christ_unit text,"
            . "baptized integer,"
            . "taken_discipleship integer,"
            . "courses text,"
            . "family_at_home integer,"
            . "talents text,"
            . "email text,"
            . "phone text, "
            . "mobile_phone text,"
            . "work_place text,"
            . "work_phone text,"
            . "birth_date text,"
            . "birth_day integer,"
            . "birth_month integer,"
            . "birth_year integer,"
            . "group_id integer,"
            . "last_visit_date text,"
            . "notes text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_members_index "
            . "on church_attendance_members ("
            . "id desc,"
            . "first_name desc, "
            . "last_name desc, "
            . "maiden_name desc, "
            . "gender desc, "
            . "civil_status desc, "
            . "birth_date desc,"
            . "birth_day desc,"
            . "birth_month desc,"
            . "birth_year desc,"
            . "is_member desc, "
            . "taken_discipleship desc, "
            . "year_accepted_christ desc,"
            . "time_following_christ desc,"
            . "time_following_christ_unit desc,"
            . "baptized desc,"
            . "family_at_home desc, "
            . "email desc, "
            . "phone desc, "
            . "mobile_phone desc, "
            . "group_id desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    // Assistance registry database
    if(!Jaris\Sql::dbExists("church_attendance_registry"))
    {
        $db = Jaris\Sql::open("church_attendance_registry");

        Jaris\Sql::query(
            "create table church_attendance_registry ("
            . "id integer primary key, "
            . "activity_id integer, "
            . "member_id integer, "
            . "group_id integer, "
            . "gender text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_attendance_registry_index "
            . "on church_attendance_registry ("
            . "id desc,"
            . "activity_id desc, "
            . "member_id desc, "
            . "group_id desc, "
            . "gender desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}
