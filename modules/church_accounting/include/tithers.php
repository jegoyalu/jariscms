<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module include file
 *
 * @note File with functions to manage church tithers
 */

function church_accounting_tither_add($data)
{
    Jaris\Sql::escapeArray($data);

    $db = Jaris\Sql::open("church_accounting_tithers");

    Jaris\Sql::query(
        "insert into church_accounting_tithers ("
        . "first_name, "
        . "last_name,"
        . "maiden_name,"
        . "postal_address,"
        . "email,"
        . "phone,"
        . "mobile_phone"
        . ") "
        . "values ("
        . "'{$data['first_name']}',"
        . "'{$data['last_name']}',"
        . "'{$data['maiden_name']}',"
        . "'{$data['postal_address']}',"
        . "'{$data['email']}',"
        . "'{$data['phone']}',"
        . "'{$data['mobile_phone']}'"
        . ")",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_tither_edit($id, $data)
{
    Jaris\Sql::escapeVar($id, "int");

    Jaris\Sql::escapeArray($data);

    $db = Jaris\Sql::open("church_accounting_tithers");

    Jaris\Sql::query(
        "update church_accounting_tithers set "
        . "first_name='{$data['first_name']}',"
        . "last_name='{$data['last_name']}',"
        . "maiden_name='{$data['maiden_name']}',"
        . "postal_address='{$data['postal_address']}', "
        . "email='{$data['email']}',"
        . "phone='{$data['phone']}',"
        . "mobile_phone='{$data['mobile_phone']}' "
        . "where id=$id ",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_tither_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_tithers");

    $result = Jaris\Sql::query(
        "select * from church_accounting_tithers where id=$id",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function church_accounting_tither_delete($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_tithers");

    Jaris\Sql::query(
        "delete from church_accounting_tithers where id=$id",
        $db
    );

    Jaris\Sql::close($db);
}
