<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module include file
 *
 * @note File with functions to manage church expenses
 */

function church_accounting_expense_add($data)
{
    $date = strtotime("{$data['day']}-{$data['month']}-{$data['year']}");

    $total = isset($data["total"]) && $data["total"] > 0 ?
        $data["total"] : 0.00
    ;

    if(!is_array($data["checks"]))
    {
        $data["checks"] = array();
    }

    if(is_array($data["items_data"]))
    {
        foreach($data["items_data"] as $item)
        {
            $total += floatval($item["amount"]);
        }
    }
    else
    {
        $data["items_data"] = array();
    }

    if(is_array($data["attachments"]))
    {
        $attachments = array();
        foreach($data["attachments"] as $file)
        {
            $attachments[] = church_accounting_attachments_add(
                $file, intval($data['month']), intval($data['year'])
            );
        }
        $data["attachments"] = $attachments;
    }
    else
    {
        $data["attachments"] = array();
    }

    $total = number_format($total, 2, ".", "");

    $data["checks"] = serialize($data["checks"]);
    $data["items_data"] = serialize($data["items_data"]);
    $data["attachments"] = serialize($data["attachments"]);

    Jaris\Sql::escapeArray($data);

    $data["day"] = intval($data['day']);
    $data["month"] = intval($data['month']);
    $data["year"] = intval($data['year']);
    $data["category"] = intval($data['category']);

    $db = Jaris\Sql::open("church_accounting_expenses");

    Jaris\Sql::query(
        "insert into church_accounting_expenses ("
        . "created_date, "
        . "day,"
        . "month,"
        . "year,"
        . "category,"
        . "description,"
        . "checks,"
        . "items_data,"
        . "attachments,"
        . "total,"
        . "prepared_by,"
        . "verified_by"
        . ") "
        . "values ("
        . "'$date',"
        . "{$data['day']},"
        . "{$data['month']},"
        . "{$data['year']},"
        . "{$data['category']},"
        . "'{$data['description']}',"
        . "'{$data['checks']}',"
        . "'{$data['items_data']}',"
        . "'{$data['attachments']}',"
        . "$total,"
        . "'{$data['prepared_by']}',"
        . "'{$data['verified_by']}'"
        . ")",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_expense_edit($id, $data)
{
    Jaris\Sql::escapeVar($id, "int");

    $date = strtotime("{$data['day']}-{$data['month']}-{$data['year']}");

    $total = isset($data["total"]) && $data["total"] > 0 ?
        $data["total"] : 0.00
    ;

    if(!is_array($data["checks"]))
    {
        $data["checks"] = array();
    }

    if(is_array($data["items_data"]))
    {
        foreach($data["items_data"] as $item)
        {
            $total += floatval($item["amount"]);
        }
    }
    else
    {
        $data["items_data"] = array();
    }

    if(is_array($data["attachments"]))
    {
        $attachments = array();
        foreach($data["attachments"] as $file)
        {
            if(is_array($file))
            {
                $attachments[] = church_accounting_attachments_add(
                    $file, intval($data['month']), intval($data['year'])
                );
            }
            else
            {
                $attachments[] = $file;
            }
        }
        $data["attachments"] = $attachments;
    }
    else
    {
        $data["attachments"] = array();
    }

    $total = number_format($total, 2, ".", "");

    $data["checks"] = serialize($data["checks"]);
    $data["items_data"] = serialize($data["items_data"]);
    $data["attachments"] = serialize($data["attachments"]);

    Jaris\Sql::escapeArray($data);

    $data["day"] = intval($data['day']);
    $data["month"] = intval($data['month']);
    $data["year"] = intval($data['year']);
    $data["category"] = intval($data['category']);

    $db = Jaris\Sql::open("church_accounting_expenses");

    Jaris\Sql::query(
        "update church_accounting_expenses set "
        . "created_date='$date', "
        . "day={$data['day']},"
        . "month={$data['month']},"
        . "year={$data['year']},"
        . "category={$data['category']},"
        . "description='{$data['description']}',"
        . "checks='{$data['checks']}',"
        . "items_data='{$data['items_data']}',"
        . "attachments='{$data['attachments']}',"
        . "total=$total,"
        . "prepared_by='{$data['prepared_by']}',"
        . "verified_by='{$data['verified_by']}' "
        . "where id=$id ",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_expense_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_expenses");

    $result = Jaris\Sql::query(
        "select * from church_accounting_expenses where id=$id",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    $data["checks"] = unserialize($data["checks"]);
    $data["items_data"] = unserialize($data["items_data"]);
    $data["attachments"] = unserialize($data["attachments"]);

    Jaris\Sql::close($db);

    return $data;
}

function church_accounting_expense_delete($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_expenses");

    Jaris\Sql::query(
        "delete from church_accounting_expenses where id=$id",
        $db
    );

    Jaris\Sql::close($db);
}

/**
 * Moves all expenses from a given category to the 'other' category. Used
 * when a category is delete but there was expenses classified as that category.
 * @param int $current_category Id of category to displace.
 */
function church_accounting_expense_move_to_other($current_category)
{
    Jaris\Sql::escapeVar($current_category, "int");

    $db = Jaris\Sql::open("church_accounting_expenses");

    Jaris\Sql::query(
        "update church_accounting_expenses set "
        . "category=13 "
        . "where category=$current_category ",
        $db
    );

    Jaris\Sql::close($db);
}