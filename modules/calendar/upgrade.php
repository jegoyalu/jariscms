<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function calendar_upgrade()
{
    // Remove obsolete featured products type.
    if(isset(Jaris\Types::getList()["featured-product"]))
    {
        Jaris\Types::delete("featured-product");
    }

    if(Jaris\Sql::dbExists("ecommerce_coupons"))
    {
        $data = Jaris\Sql::getDataList(
            "ecommerce_coupons",
            "ecommerce_coupons",
            0,
            1
        );

        if(!isset($data[0]["max_usage_type"]))
        {
            $db = Jaris\Sql::open("ecommerce_coupons");

            Jaris\Sql::query(
                "alter table ecommerce_coupons add column max_usage_type text",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(
                t("Added max_usage_type column to ecommerce_coupons")
            );
        }

        if(!isset($data[0]["product_types"]))
        {
            $db = Jaris\Sql::open("ecommerce_coupons");

            Jaris\Sql::query(
                "alter table ecommerce_coupons add column product_types text",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(
                t("Added product_types column to ecommerce_coupons")
            );
        }

        if(!isset($data[0]["type_categories"]))
        {
            $db = Jaris\Sql::open("ecommerce_coupons");

            Jaris\Sql::query(
                "alter table ecommerce_coupons add column type_categories text",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(
                t("Added type_categories column to ecommerce_coupons")
            );
        }
    }

    if(Jaris\Sql::dbExists("ecommerce_inventory"))
    {
        $data = Jaris\Sql::getDataList(
            "ecommerce_inventory",
            "ecommerce_inventory",
            0,
            1
        );

        if(!isset($data[0]["upc_code"]))
        {
            $db = Jaris\Sql::open("ecommerce_inventory");

            Jaris\Sql::query(
                "alter table ecommerce_inventory add column upc_code text",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(
                t("Added upc_code column to ecommerce_inventory")
            );
        }

        if(!isset($data[0]["variation"]))
        {
            $db = Jaris\Sql::open("ecommerce_inventory");

            Jaris\Sql::query(
                "alter table ecommerce_inventory add column variation integer",
                $db
            );

            Jaris\Sql::query(
                "alter table ecommerce_inventory add column in_stock integer",
                $db
            );

            Jaris\Sql::close($db);

            Jaris\View::addMessage(
                t("Added variation and in_stock columns to ecommerce_inventory")
            );
        }
    }
}
