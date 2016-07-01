<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module include file
 *
 * @note File with functions to manage church income
 */

function church_accounting_income_add($data)
{
    $date = strtotime("{$data['day']}-{$data['month']}-{$data['year']}");

    $total = isset($data["total"]) && $data["total"] > 0 ?
        $data["total"] : 0.00
    ;

    if(is_array($data["cash"]))
    {
        foreach($data["cash"] as $item)
        {
            $total += floatval($item["amount"]) * intval($item["quantity"]);
        }
    }
    else
    {
        $data["cash"] = array();
    }

    if(is_array($data["checks"]))
    {
        foreach($data["checks"] as $item)
        {
            $total += floatval($item["amount"]);
        }
    }
    else
    {
        $data["checks"] = array();
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

    $data["cash"] = serialize($data["cash"]);
    $data["checks"] = serialize($data["checks"]);
    $data["attachments"] = serialize($data["attachments"]);

    Jaris\Sql::escapeArray($data);

    $data["day"] = intval($data['day']);
    $data["month"] = intval($data['month']);
    $data["year"] = intval($data['year']);
    $data["category"] = intval($data['category']);
    $data["is_tithe"] = intval($data['is_tithe']);
    $data["tither"] = intval($data['tither']);

    $db = Jaris\Sql::open("church_accounting_income");

    Jaris\Sql::query(
        "insert into church_accounting_income ("
        . "created_date, "
        . "day,"
        . "month,"
        . "year,"
        . "category,"
        . "description,"
        . "cash,"
        . "checks,"
        . "attachments,"
        . "is_tithe,"
        . "tither,"
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
        . "'{$data['cash']}',"
        . "'{$data['checks']}',"
        . "'{$data['attachments']}',"
        . "{$data['is_tithe']},"
        . "'{$data['tither']}',"
        . "$total,"
        . "'{$data['prepared_by']}',"
        . "'{$data['verified_by']}'"
        . ")",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_income_edit($id, $data)
{
    Jaris\Sql::escapeVar($id, "int");

    $date = strtotime("{$data['day']}-{$data['month']}-{$data['year']}");

    $total = isset($data["total"]) && $data["total"] > 0 ?
        $data["total"] : 0.00
    ;

    if(is_array($data["cash"]))
    {
        foreach($data["cash"] as $item)
        {
            $total += floatval($item["amount"]) * intval($item["quantity"]);
        }
    }
    else
    {
        $data["cash"] = array();
    }

    if(is_array($data["checks"]))
    {
        foreach($data["checks"] as $item)
        {
            $total += floatval($item["amount"]);
        }
    }
    else
    {
        $data["checks"] = array();
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

    $data["cash"] = serialize($data["cash"]);
    $data["checks"] = serialize($data["checks"]);
    $data["attachments"] = serialize($data["attachments"]);

    Jaris\Sql::escapeArray($data);

    $data["day"] = intval($data['day']);
    $data["month"] = intval($data['month']);
    $data["year"] = intval($data['year']);
    $data["category"] = intval($data['category']);
    $data["is_tithe"] = intval($data['is_tithe']);
    $data["tither"] = intval($data['tither']);

    $db = Jaris\Sql::open("church_accounting_income");

    Jaris\Sql::query(
        "update church_accounting_income set "
        . "created_date='$date', "
        . "day={$data['day']},"
        . "month={$data['month']},"
        . "year={$data['year']},"
        . "category={$data['category']},"
        . "description='{$data['description']}',"
        . "cash='{$data['cash']}',"
        . "checks='{$data['checks']}',"
        . "attachments='{$data['attachments']}',"
        . "is_tithe={$data['is_tithe']},"
        . "tither={$data['tither']},"
        . "total=$total, "
        . "prepared_by='{$data['prepared_by']}',"
        . "verified_by='{$data['verified_by']}' "
        . "where id=$id ",
        $db
    );

    Jaris\Sql::close($db);
}

function church_accounting_income_get($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_income");

    $result = Jaris\Sql::query(
        "select * from church_accounting_income where id=$id",
        $db
    );

    $data = Jaris\Sql::fetchArray($result);

    $data["cash"] = unserialize($data["cash"]);
    $data["checks"] = unserialize($data["checks"]);
    $data["attachments"] = unserialize($data["attachments"]);

    Jaris\Sql::close($db);

    return $data;
}

function church_accounting_income_get_residue($data)
{
    $total = 0.00;

    if(is_array($data["cash"]))
    {
        foreach($data["cash"] as $item)
        {
            $total += floatval($item["amount"]) * intval($item["quantity"]);
        }
    }

    if(is_array($data["checks"]))
    {
        foreach($data["checks"] as $item)
        {
            $total += floatval($item["amount"]);
        }
    }

    return $total < $data["total"] ?
        number_format($data["total"] - $total, 2, ".", "")
        :
        ""
    ;
}

function church_accounting_income_delete($id)
{
    Jaris\Sql::escapeVar($id, "int");

    $db = Jaris\Sql::open("church_accounting_income");

    Jaris\Sql::query(
        "delete from church_accounting_income where id=$id",
        $db
    );

    Jaris\Sql::close($db);
}

/**
 * Moves all income from a given category to the 'other' category. Used
 * when a category is delete but there was income classified as that category.
 * @param int $current_category Id of category to displace.
 */
function church_accounting_income_move_to_other($current_category)
{
    Jaris\Sql::escapeVar($current_category, "int");

    $db = Jaris\Sql::open("church_accounting_income");

    Jaris\Sql::query(
        "update church_accounting_income set "
        . "category=4 "
        . "where category=$current_category ",
        $db
    );

    Jaris\Sql::close($db);
}