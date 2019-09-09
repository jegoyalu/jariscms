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

function church_accounting_install()
{
    // Create income database
    if (!Jaris\Sql::dbExists("church_accounting_income")) {
        //Income database
        $db = Jaris\Sql::open("church_accounting_income");

        Jaris\Sql::query(
            "create table church_accounting_income ("
            . "id integer primary key, "
            . "created_date text, "
            . "day integer, "
            . "month integer, "
            . "year integer, "
            . "category integer, "
            . "description text, "
            . "cash text, "
            . "checks text, "
            . "attachments text, "
            . "is_tithe integer, "
            . "tither integer, "
            . "total double, "
            . "prepared_by text, "
            . "verified_by text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_accounting_income_index "
            . "on church_accounting_income ("
            . "id desc, "
            . "created_date desc, "
            . "day desc, "
            . "month desc, "
            . "year desc, "
            . "category desc, "
            . "is_tithe desc, "
            . "total desc, "
            . "prepared_by desc, "
            . "verified_by desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    // Create expenses database
    if (!Jaris\Sql::dbExists("church_accounting_expenses")) {
        $db = Jaris\Sql::open("church_accounting_expenses");

        Jaris\Sql::query(
            "create table church_accounting_expenses ("
            . "id integer primary key, "
            . "created_date text, "
            . "day integer, "
            . "month integer, "
            . "year integer, "
            . "category integer, "
            . "description text, "
            . "checks text, "
            . "items_data text, "
            . "attachments text, "
            . "total double, "
            . "prepared_by text, "
            . "verified_by text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_accounting_expenses_index "
            . "on church_accounting_expenses ("
            . "id desc, "
            . "created_date desc, "
            . "day desc, "
            . "month desc, "
            . "year desc, "
            . "category desc, "
            . "total desc, "
            . "prepared_by desc, "
            . "verified_by desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }

    // Income/Expenses category database
    if (!Jaris\Sql::dbExists("church_accounting_categories")) {
        $db = Jaris\Sql::open("church_accounting_categories");

        Jaris\Sql::query(
            "create table church_accounting_categories ("
            . "id integer primary key, "
            . "label text, "
            . "type integer"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_accounting_categories_index "
            . "on church_accounting_categories ("
            . "id desc,"
            . "type desc"
            . ")",
            $db
        );

        // Strings to assist poedit or other translation tools.
        $strings = [
            // Income
            t("Tithe"),
            t("Offering"),
            t("Bank Interest"),
            t("Other"),
            // Expenses
            t("Activities"),
            t("Rent"),
            t("Radio Broadcasting"),
            t("Materials"),
            t("Equipment"),
            t("Offering"),
            t("Travel"),
            t("Donation"),
            t("Other")
        ];

        //Default income categories
        $income = [
            "Tithe",
            "Offerings",
            "Bank Interest",
            "Other"
        ];

        //Default expense categories
        $expenses = [
            "Activities",
            "Rent",
            "Radio Broadcasting",
            "Materials",
            "Equipment",
            "Offering",
            "Travel",
            "Donation",
            "Other"
        ];

        Jaris\Sql::beginTransaction($db);

        foreach ($income as $element) {
            $insert = "insert into church_accounting_categories "
                . "(label, type) "
                . "values("
                . "'$element',"
                . "2"
                . ")"
            ;

            Jaris\Sql::query($insert, $db);
        }

        foreach ($expenses as $element) {
            $insert = "insert into church_accounting_categories "
                . "(label, type) "
                . "values("
                . "'$element',"
                . "1"
                . ")"
            ;

            Jaris\Sql::query($insert, $db);
        }

        Jaris\Sql::commitTransaction($db);

        Jaris\Sql::close($db);
    }

    // Tithers database
    if (!Jaris\Sql::dbExists("church_accounting_tithers")) {
        $db = Jaris\Sql::open("church_accounting_tithers");

        Jaris\Sql::query(
            "create table church_accounting_tithers ("
            . "id integer primary key, "
            . "first_name text, "
            . "last_name text, "
            . "maiden_name text, "
            . "postal_address text, "
            . "email text,"
            . "phone text, "
            . "mobile_phone text"
            . ")",
            $db
        );

        Jaris\Sql::query(
            "create index church_accounting_tithers_index "
            . "on church_accounting_tithers ("
            . "id desc,"
            . "first_name desc, "
            . "last_name desc, "
            . "maiden_name desc, "
            . "email desc, "
            . "phone desc, "
            . "mobile_phone desc"
            . ")",
            $db
        );

        Jaris\Sql::close($db);
    }
}
