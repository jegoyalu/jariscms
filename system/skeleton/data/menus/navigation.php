<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the navigation menu available
 * for registered users by using an associanted block menu.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Home
    field;

    field: url

    field;

    field: description

    field;

    field: order
        0
    field;

    field: parent
        root
    field;

    field: target
        _self
    field;
row;

row: 1
    field: title
        Control Center
    field;

    field: url
        admin/start
    field;

    field: description

    field;

    field: order
        1
    field;

    field: parent
        root
    field;

    field: target
        _self
    field;
row;

row: 2
    field: title
        My Account
    field;

    field: url
        admin/user
    field;

    field: description

    field;

    field: order
        2
    field;

    field: parent
        root
    field;

    field: target
        _self
    field;
row;

row: 3
    field: title
        Logout
    field;

    field: url
        admin/logout
    field;

    field: description

    field;

    field: order
        3
    field;

    field: parent
        root
    field;

    field: target
        _self
    field;
row;
