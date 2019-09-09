<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Functions to manage church accounting income/expenses categories.
 */

class ChurchAccountingCategory
{
    const EXPENSE=1;
    const INCOME=2;
}

function church_accounting_category_add($label, $type=ChurchAccountingCategory::INCOME)
{
    Jaris\Sql::escapeVar($label);
    Jaris\Sql::escapeVar($type, "int");

    $db = Jaris\Sql::open("church_accounting_categories");

    $insert = "insert into church_accounting_categories "
        . "(label, type) "
        . "values("
        . "'$label',"
        . "$type"
        . ")"
    ;

    Jaris\Sql::query($insert, $db);

    Jaris\Sql::close($db);
}

function church_accounting_category_edit($id, $label, $type=ChurchAccountingCategory::INCOME)
{
    $db = Jaris\Sql::open("church_accounting_categories");

    Jaris\Sql::escapeVar($id, "int");
    Jaris\Sql::escapeVar($label);
    Jaris\Sql::escapeVar($type, "int");

    $update = "update church_accounting_categories "
        . "set "
        . "label = '$label', "
        . "type = $type "
        . "where id=$id"
    ;

    Jaris\Sql::query($update, $db);

    Jaris\Sql::close($db);
}

function church_accounting_category_delete($id)
{
    $db = Jaris\Sql::open("church_accounting_categories");

    Jaris\Sql::escapeVar($id, "int");

    $delete = "delete from church_accounting_categories where id=$id";

    Jaris\Sql::query($delete, $db);

    Jaris\Sql::close($db);
}

function church_accounting_category_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_categories");

    $select = "select * from church_accounting_categories where id=$id";

    $result = Jaris\Sql::query($select, $db);

    $data = Jaris\Sql::fetchArray($result);

    Jaris\Sql::close($db);

    return $data;
}

function church_accounting_category_list($type=ChurchAccountingCategory::INCOME)
{
    Jaris\Sql::escapeVar($type, "int");

    static $list = [];

    if (!isset($list[$type])) {
        $list[$type] = [];

        $db = Jaris\Sql::open("church_accounting_categories");

        $select = "select * from church_accounting_categories "
            . "where type=$type order by label asc"
        ;

        $result = Jaris\Sql::query($select, $db);

        while ($data = Jaris\Sql::fetchArray($result)) {
            $list[$type][$data["id"]] = $data["label"];
        }

        Jaris\Sql::close($db);
    }

    return $list[$type];
}

function church_accounting_category_label($id, $type=ChurchAccountingCategory::INCOME)
{
    $list = church_accounting_category_list($type);

    return $list[$id];
}
