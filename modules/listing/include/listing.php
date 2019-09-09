<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function listing_category_fields($selected = null, $main_category = "", $type = "")
{
    $fields = [];

    $categories_list = [];

    if (!$main_category) {
        $categories_list = Jaris\Categories::getList($type);
    } else {
        $categories_list[$main_category] = Jaris\Categories::get(
            $main_category
        );
    }

    if ($categories_list) {
        foreach ($categories_list as $machine_name => $values) {
            $subcategories = Jaris\Categories::getSubcategoriesInParentOrder(
                $machine_name
            );

            $select_values = [];
            /* if(!$values["multiple"])
              {
              $select_values[t("-None Selected-")] = "-1";
              } */

            foreach ($subcategories as $id => $sub_values) {
                //In case person created categories with the same name
                if (isset($select_values[t($sub_values["title"])])) {
                    $title = t($sub_values["title"]) . " ";
                    while (isset($select_values[$title])) {
                        $title .= " ";
                    }

                    $select_values[$title] = $id;
                } else {
                    $select_values[t($sub_values["title"])] = $id;
                }
            }

            /* $multiple = false;
              if($values["multiple"])
              {
              $multiple = true;
              } */

            $multiple = true;

            if (count($select_values) >= 1) {
                if (is_array($selected) && count($selected) > 0) {
                    $fields[] = [
                        "type" => "select",
                        "inline" => true,
                        "multiple" => $multiple,
                        "selected" => $selected[$machine_name],
                        "name" => "filter_category_{$machine_name}[]",
                        "label" => t($values["name"]),
                        "id" => "filter_category_" . $machine_name,
                        "value" => $select_values
                    ];
                } else {
                    $fields[] = [
                        "type" => "select",
                        "inline" => true,
                        "multiple" => $multiple,
                        "name" => "filter_category_{$machine_name}[]",
                        "label" => t($values["name"]),
                        "id" => "filter_category_" . $machine_name,
                        "value" => $select_values
                    ];
                }
            }
        }
    }

    return $fields;
}

function listing_category_filter_fields(
    $selected = null,
    $main_category = "",
    $type = "",
    $prefix = "",
    $show_count = false,
    $field_type="select"
) {
    $fields = [];

    $categories_list = [];
    if (!$main_category) {
        $categories_list = Jaris\Categories::getList($type);
    } else {
        $categories_list[$main_category] = Jaris\Categories::get($main_category);
    }

    foreach ($categories_list as $machine_name => $values) {
        $subcategories = Jaris\Categories::getSubcategoriesInParentOrder(
            $machine_name,
            "root",
            "",
            true
        );

        $select_values = [];
        if (/*!$values["multiple"] &&*/ $field_type == "select") {
            $select_values[t("-None Selected-")] = "-1";
        }

        foreach ($subcategories as $id => $sub_values) {
            //In case person created categories with the same name
            if (isset($select_values[t($sub_values["title"])])) {
                $title = $sub_values["title"] . " ";
                while (isset($select_values[$title])) {
                    $title .= " ";
                }

                $select_values[$title] = $id;
            } else {
                $select_values[$sub_values["title"]] = $id;
            }
        }

        $multiple = false;
        /*if($values["multiple"])
        {
            $multiple = true;
        }*/

        if ($field_type == "select" && count($select_values) > 1) {
            if (count($selected) > 0) {
                $fields[] = [
                    "type" => "select",
                    "multiple" => $multiple,
                    "selected" => $selected[$prefix . $machine_name],
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "code" => "onchange=\"this.form.submit();\"",
                    "value" => $select_values
                ];
            } else {
                $fields[] = [
                    "type" => "select",
                    "multiple" => $multiple,
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "code" => "onchange=\"this.form.submit();\"",
                    "value" => $select_values
                ];
            }
        }
        if ($field_type == "radio" && count($select_values) > 1) {
            if (count($selected) > 0) {
                $fields[] = [
                    "type" => "radio",
                    "checked" => $selected[$prefix . $machine_name][0],
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "code" => "onchange=\"this.form.submit();\"",
                    "horizontal_list" => true
                ];
            } else {
                $fields[] = [
                    "type" => "radio",
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "code" => "onchange=\"this.form.submit();\"",
                    "horizontal_list" => true
                ];
            }
        } elseif (count($select_values) > 1) {
            if (count($selected) > 0) {
                $fields[] = [
                    "type" => "checkbox",
                    "checked" => $selected[$prefix . $machine_name],
                    "name" => "$prefix{$machine_name}",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "horizontal_list" => true
                ];
            } else {
                $fields[] = [
                    "type" => "checkbox",
                    "name" => "$prefix{$machine_name}",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "horizontal_list" => true
                ];
            }
        }
    }

    return $fields;
}

function listing_print_results($uri, $content_data)
{
    $page = 1;

    if (isset($_REQUEST["page"])) {
        $page = intval($_REQUEST["page"]);
    }

    $types = "";

    if (
        is_array($content_data["filter_types"]) &&
        count($content_data["filter_types"]) > 0
    ) {
        $types = "and (";
        foreach ($content_data["filter_types"] as $type) {
            $types .= "type='$type' or ";
        }

        $types = rtrim($types, " or");

        $types .= ")";
    } else {
        $content_data["filter_types"] = [];
    }

    // Handle ecommerce settings
    $ecommerce_installed = Jaris\Modules::isInstalled("ecommerce");
    $ecommerce_product_types = false;

    if (
        !empty($content_data["treat_as_products"])
        &&
        $ecommerce_installed
    ) {
        $ecommerce_product_types = true;
        $product_types = ecommerce_get_product_types();

        foreach ($content_data["filter_types"] as $type) {
            if (!isset($product_types[$type])) {
                $ecommerce_product_types = false;
                break;
            }
        }
    }

    $ecommerce_products = $ecommerce_installed
        &&
        $ecommerce_product_types
    ;

    // Handle realty settings
    $realty_installed = Jaris\Modules::isInstalled("realty");
    $realty_product_types = false;

    if (
        !empty($content_data["treat_as_properties"])
        &&
        $realty_installed
    ) {
        $realty_product_types = true;

        foreach ($content_data["filter_types"] as $type) {
            if ($type != "property") {
                $realty_product_types = false;
                break;
            }
        }
    }

    $realty_properties = $realty_installed
        &&
        $realty_product_types
    ;

    $realty_type = "";
    $realty_country = "";
    $realty_state_province = "";
    $realty_city = "";
    $realty_category = "";
    $realty_status = "";
    $realty_foreclosure = "";
    $realty_commercial = "";

    if ($realty_properties) {
        $realty_type = !empty($content_data["realty_type"]) ?
            " and sub_category ='".$content_data["realty_type"]."'"
            :
            ""
        ;

        $realty_country = !empty($content_data["realty_country"]) ?
            " and country='".$content_data["realty_country"]."'"
            :
            ""
        ;

        $realty_state_province = !empty($content_data["realty_state_province"]) ?
            " and state_province='".$content_data["realty_state_province"]."'"
            :
            ""
        ;

        $realty_city = !empty($content_data["realty_city"]) ?
            " and city='".$content_data["realty_city"]."'"
            :
            ""
        ;

        if (
            isset($content_data["realty_category"])
            &&
            trim($content_data["realty_category"]) != ""
        ) {
            $realty_category = " and category='".$content_data["realty_category"]."'";
        } elseif (
            $_REQUEST["sub_category"] == "rent"
        ) {
            $category_list = realty_get_categories("rent");

            $realty_category .= "and category in (";
            foreach ($category_list as $category_name) {
                $realty_category .= "'"
                    . str_replace("'", "''", $category_name)
                    . "',"
                ;
            }
            $realty_category = rtrim($realty_category, ",");
            $realty_category .= ") ";
        }

        $realty_status = !empty($content_data["realty_status"]) ?
            " and status='".$content_data["realty_status"]."'"
            :
            ""
        ;

        if ($content_data["realty_foreclosure"] == "y") {
            $realty_foreclosure = " and is_foreclosure = '1'";
        } elseif ($content_data["realty_foreclosure"] == "n") {
            $realty_foreclosure = " and is_foreclosure = '0'";
        }

        if ($content_data["realty_commercial"] == "y") {
            $realty_commercial = " and is_commercial = '1'";
        } elseif ($content_data["realty_commercial"] == "n") {
            $realty_commercial = " and is_commercial = '0'";
        }
    }

    // Handle reviews settings
    $reviews_installed = Jaris\Modules::isInstalled("reviews");
    $reviews_enabled_types = false;

    if (
        !empty($content_data["show_reviews"])
        &&
        $reviews_installed
    ) {
        $reviews_enabled_types = true;

        foreach ($content_data["filter_types"] as $type) {
            $review_settings = reviews_get_settings($type);

            if (empty($review_settings["enabled"])) {
                $reviews_enabled_types = false;
                break;
            }
        }
    }

    $reviews_enabled = $reviews_installed
        &&
        $reviews_enabled_types
    ;

    $reviews_max_score = intval($content_data["reviews_score"]);

    $authors = "";
    $authors_list = explode(",", $content_data["filter_authors"]);

    if (
        is_array($authors_list) &&
        count($authors_list) > 0 &&
        trim($authors_list[0]) != ""
    ) {
        $authors = "and (";

        foreach ($authors_list as $author) {
            $authors .= "author='" . trim($author) . "' or ";
        }

        $authors = rtrim($authors, " or");

        $authors .= ")";
    }

    $has_categories = "";
    $where_categories = "";

    if (count($content_data["filter_types"]) == 1) {
        $categories = Jaris\Categories::getList(
            $content_data["filter_types"][0]
        );

        $categories_filter = [];

        foreach ($categories as $cat_name => $cat_fields) {
            if (isset($_REQUEST[$cat_name])) {
                $categories_filter[$cat_name] = $_REQUEST[$cat_name];
            }
        }

        if (count($categories_filter) > 0) {
            $content_data["filter_categories"] = $categories_filter;
        }
    }

    if (
        is_array($content_data["filter_categories"]) &&
        count($content_data["filter_categories"]) > 0
    ) {
        $categories = serialize($content_data["filter_categories"]);

        if ($content_data["category_matching"] != "match_partial") {
            $has_categories = ", "
                . "hascategories(categories, '$categories') as has_category"
            ;
        } else {
            $has_categories = ", "
                . "hassomecategories(categories, '$categories') as has_category"
            ;
        }

        $where_categories = "and has_category > 0";
    }

    $ordering = "";
    $where_date = "";
    switch ($content_data["filter_ordering"]) {
        case "date_desc":
            $ordering = "order by created_date desc";
            break;
        case "date_asc":
            $ordering = "order by created_date asc";
            break;
        case "title_asc":
            $ordering = "order by title asc";
            break;
        case "title_desc":
            $ordering = "order by title desc";
            break;
        case "views_desc":
            $ordering = "order by views desc";
            break;
        case "views_today_desc":
            $ordering = "order by views_day_count desc";
            break;
        case "views_week_desc":
            $ordering = "order by views_week_count desc";
            break;
        case "views_month_desc":
            $ordering = "order by views_month_count desc";
            break;
        case "current_date_asc":
            $ordering = "order by created_date asc";
            $where_date .= "and created_date >= '".time()."'";
            break;
        case "current_date_desc":
            $ordering = "order by created_date desc";
            $where_date .= "and created_date >= '".time()."'";
            break;
        default:
            $ordering = "order by created_date desc";
            break;
    }

    if (
        !empty($content_data["display_sorting_selector"])
        &&
        isset($_REQUEST["s"])
        &&
        trim($_REQUEST["s"]) != ""
    ) {
        switch ($_REQUEST["s"]) {
            case "rd":
                $ordering = "order by created_date desc";
                break;
            case "ra":
                $ordering = "order by created_date asc";
                break;
            case "na":
                $ordering = "order by title asc";
                break;
            case "nd":
                $ordering = "order by title desc";
                break;
        }

        if ($ecommerce_products) {
            switch ($_REQUEST["s"]) {
                case "pd":
                    $ordering = "order by price desc";
                    break;
                case "pa":
                    $ordering = "order by price asc";
                    break;
            }
        }

        if ($reviews_enabled) {
            switch ($_REQUEST["s"]) {
                case "sd":
                    $ordering = "order by reviews_score desc";
                    break;
                case "sa":
                    $ordering = "order by reviews_score asc";
                    break;
            }
        }
    }

    $user = Jaris\Authentication::currentUser();
    $group = Jaris\Authentication::currentUserGroup();

    // Get results count
    $results_count = 0;
    $db = Jaris\Sql::open("search_engine");

    $query = "";

    $on_sale = "";

    if ($ecommerce_products) {
        $on_sale = !empty($content_data["onsale_only"]) ?
            " and on_sale != ''"
            :
            ""
        ;

        Jaris\Sql::attach("ecommerce_inventory", $db);

        if ($reviews_enabled) {
            Jaris\Sql::attach("reviews", $db);

            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "count(a.uri) as uri_count $has_categories from uris a "
                . "inner join ecommerce_inventory b on "
                . "a.uri = b.uri "
                . "left join reviews c on "
                . "b.uri = c.uri "
                . "where b.variation=0 and b.in_stock=1 and "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories $on_sale "
            ;
        } else {
            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "count(a.uri) as uri_count $has_categories from uris a "
                . "inner join ecommerce_inventory b on "
                . "a.uri = b.uri "
                . "where b.variation=0 and b.in_stock=1 and "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories $on_sale"
            ;
        }

        $query = str_replace("type=", "a.type=", $query);
    } elseif ($realty_properties) {
        Jaris\Sql::attach("realty_properties", $db);

        if ($reviews_enabled) {
            Jaris\Sql::attach("reviews", $db);

            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "count(a.uri) as uri_count $has_categories from uris a "
                . "inner join realty_properties b on "
                . "a.uri = b.uri "
                . "left join reviews c on "
                . "b.uri = c.uri "
                . "where "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories "
                . "$realty_type $realty_country $realty_state_province $realty_city "
                . "$realty_category $realty_status $realty_foreclosure "
                . "$realty_commercial"
            ;
        } else {
            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "count(a.uri) as uri_count $has_categories from uris a "
                . "inner join realty_properties b on "
                . "a.uri = b.uri "
                . "where "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories "
                . "$realty_type $realty_country $realty_state_province $realty_city "
                . "$realty_category $realty_status $realty_foreclosure "
                . "$realty_commercial"
            ;
        }

        $query = str_replace("type=", "a.type=", $query);
    } elseif ($reviews_enabled) {
        Jaris\Sql::attach("reviews", $db);

        $query .= "select haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions, "
            . "count(a.uri) as uri_count $has_categories from uris a "
            . "left join reviews b on "
            . "a.uri = b.uri "
            . "where "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories"
        ;

        $query = str_replace("type=", "a.type=", $query);
    } else {
        $query .= "select haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions, "
            . "count(uri) as uri_count $has_categories from uris "
            . "where has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories"
        ;
    }

    Jaris\Sql::turbo($db);

    $result = Jaris\Sql::query($query, $db);

    while ($data_count = Jaris\Sql::fetchArray($result)) {
        $results_count = $data_count["uri_count"];
        break;
    }

    Jaris\Sql::close($db);

    if ($reviews_enabled && $results_count > 0) {
        Jaris\View::addScript(
            Jaris\Modules::directory("reviews")
                . "scripts/raty/js/jquery.raty.min.js"
        );

        $images_path = Jaris\Uri::url(
            Jaris\Modules::directory("reviews") . "scripts/raty/img/"
        );

        $hints_list = [];
        for ($i=1; $i<=$reviews_max_score; $i++) {
            $hints_list[] = $i;
        }

        $hints = reviews_print_hints(implode(",", $hints_list));

        Jaris\View::addScriptCode(
            '$(document).ready(function(){'
            . '$(".listing-review-score").each(function(index){'
            . 'var score = parseInt($(this).text());'
            . '$(this).text("");'
            . '$(this).raty({'
            . '    number: '.$reviews_max_score.','
            . '    path: "'.$images_path.'",'
            . '    score: score,'
            . '    showHalf: true,'
            . '    starHalf: "star-half.png",'
            . '    starOff: "star-off.png",'
            . '    starOn: "star-on.png",'
            . '    hints: '.$hints.','
            . '    noRatedMsg: "'.t("not rated yet").'",'
            . '    readOnly: true'
            . '});'
            . '});'
            . '});'
        );
    }

    // Get results
    $db = Jaris\Sql::open("search_engine");

    $query = "";

    $limit = !empty($content_data["display_count_selector"])
        &&
        isset($_REQUEST["a"])
        &&
        (
            intval($_REQUEST["a"]) >= 25
            &&
            intval($_REQUEST["a"]) <= 100
        )
        ?
            intval($_REQUEST["a"])
            :
            intval($content_data["results_per_page"])
    ;

    if ($ecommerce_products) {
        Jaris\Sql::attach("ecommerce_inventory", $db);

        if ($reviews_enabled) {
            Jaris\Sql::attach("reviews", $db);

            $query .= "select a.uri, haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "cast(c.score as float) "
                . "/ "
                . "cast($reviews_max_score * c.reviews_count as float) "
                . "as reviews_score "
                . "$has_categories from uris a "
                . "inner join ecommerce_inventory b on "
                . "a.uri = b.uri "
                . "left join reviews c on "
                . "b.uri = c.uri "
                . "where b.variation=0 and b.in_stock=1 and "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories $on_sale $ordering "
                . "limit ".(($page-1)*$limit).", ".$limit
            ;
        } else {
            $query .= "select a.uri, haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions "
                . "$has_categories from uris a "
                . "inner join ecommerce_inventory b on "
                . "a.uri = b.uri "
                . "where b.variation=0 and b.in_stock=1 and "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories $on_sale $ordering "
                . "limit ".(($page-1)*$limit).", ".$limit
            ;
        }

        $query = str_replace("type=", "a.type=", $query);
    } elseif ($realty_properties) {
        Jaris\Sql::attach("realty_properties", $db);

        if ($reviews_enabled) {
            Jaris\Sql::attach("reviews", $db);

            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "cast(c.score as float) "
                . "/ "
                . "cast($reviews_max_score * c.reviews_count as float) "
                . "as reviews_score "
                . "$has_categories from uris a "
                . "inner join realty_properties b on "
                . "a.uri = b.uri "
                . "left join reviews c on "
                . "b.uri = c.uri "
                . "where "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories "
                . "$realty_type $realty_country $realty_state_province $realty_city "
                . "$realty_category $realty_status $realty_foreclosure "
                . "$realty_commercial $ordering "
                . "limit ".(($page-1)*$limit).", ".$limit
            ;
        } else {
            $query .= "select haspermission(groups, '$group') as has_permissions, "
                . "hasuserpermission(users, '$user') as has_user_permissions, "
                . "$has_categories from uris a "
                . "inner join realty_properties b on "
                . "a.uri = b.uri "
                . "where "
                . "has_permissions > 0 and has_user_permissions > 0 and "
                . "approved='a' $types $authors $where_date $where_categories "
                . "$realty_type $realty_country $realty_state_province $realty_city "
                . "$realty_category $realty_status $realty_foreclosure "
                . "$realty_commercial $ordering "
                . "limit ".(($page-1)*$limit).", ".$limit
            ;
        }

        $query = str_replace("type=", "a.type=", $query);
    } elseif ($reviews_enabled) {
        Jaris\Sql::attach("reviews", $db);

        $query .= "select a.uri, haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions, "
            . "cast(b.score as float) "
            . "/ "
            . "cast($reviews_max_score * b.reviews_count as float) "
            . "as reviews_score "
            . "$has_categories from uris a "
            . "left join reviews b on "
            . "a.uri = b.uri "
            . "where "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories $ordering "
            . "limit ".(($page-1)*$limit).", ".$limit
        ;

        $query = str_replace("type=", "a.type=", $query);
    } else {
        $query .= "select uri, haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions "
            . "$has_categories from uris "
            . "where "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories $ordering "
            . "limit ".(($page-1)*$limit).", ".$limit
        ;
    }

    Jaris\Sql::turbo($db);

    $result = Jaris\Sql::query($query, $db);

    $results = [];
    if ($fields = Jaris\Sql::fetchArray($result)) {
        $results[] = $fields;

        while ($fields = Jaris\Sql::fetchArray($result)) {
            $results[] = $fields;
        }
    }

    Jaris\Sql::close($db);

    // Generate output
    $output = "";

    if (
        !empty($content_data["display_count_selector"])
        ||
        !empty($content_data["display_sorting_selector"])
    ) {
        $parameters["class"] = "filter-listing-results";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        if (count($content_data["filter_types"]) == 1) {
            $categories = Jaris\Categories::getList(
                $content_data["filter_types"][0]
            );

            foreach ($categories as $cat_name => $cat_fields) {
                if (isset($_REQUEST[$cat_name])) {
                    foreach ($_REQUEST[$cat_name] as $cat_value) {
                        $fields[] = [
                            "type" => "hidden",
                            "name" => $cat_name."[]",
                            "value" => $cat_value
                        ];
                    }
                }
            }
        }

        $fields[] = [
            "type" => "other",
            "html_code" =>
                '<style>'
                . '.content-list-options{margin-bottom: 20px} '
                . '.content-list-options select{min-width: 150px;}'
                . '</style>'
                . '<div '
                . 'class="content-list-options" '
                . 'style="display: flex; justify-content: flex-end" '
                . '>'
        ];

        if (!empty($content_data["display_sorting_selector"])) {
            $sorting_list = [
                t("Default") => "",
                t("Name Ascending") => "na",
                t("Name Descending") => "nd",
                t("Newest First") => "rd",
                t("Newest Last") => "ra",
            ];

            if ($ecommerce_products) {
                $sorting_list[t("Price Lowest")] = 'pa';
                $sorting_list[t("Price Highest")] = 'pd';
            }

            if ($reviews_enabled) {
                $sorting_list[t("Lowest Rating")] = 'sa';
                $sorting_list[t("Highest Rating")] = 'sd';
            }

            $fields[] = [
                "type" => "select",
                "name" => "s",
                "label" => t("Sort by:"),
                "value" => $sorting_list,
                "selected" => isset($_REQUEST["s"]) ?
                    $_REQUEST["s"]
                    :
                    "",
                "code" => 'onchange="javascript: this.form.submit()"',
                "inline" => true
            ];
        }

        if (!empty($content_data["display_count_selector"])) {
            $amount_list = [
                t("Default") => "",
                "25" => 25,
                "50" => 50,
                "75" => 75,
                "100" => 100
            ];

            $fields[] = [
                "type" => "select",
                "name" => "a",
                "label" => t("Results per page:"),
                "value" => $amount_list,
                "selected" => isset($_REQUEST["a"]) ?
                    $_REQUEST["a"]
                    :
                    "",
                "code" => 'onchange="javascript: this.form.submit()"',
                "inline" => true
            ];
        }

        $fields[] = [
            "type" => "other",
            "html_code" => '</div>'
        ];

        $fieldset[] = [
            "fields" => $fields
        ];

        $output .= Jaris\Forms::generate($parameters, $fieldset);
    }

    if ($content_data["layout"] == "list") {
        ob_start();
        include(listing_result_template($uri, "all", "list-header"));
        $output .= ob_get_contents();
        ob_end_clean();
    }

    $column = 1;
    $current_product = 1;
    $products_count = count($results);

    if ($content_data["layout"] == "grid") {
        $output .= "<table class=\"listing-grid-table\">";
        $output .= "<tbody>";
    }

    foreach ($results as $fields) {
        $page_data = Jaris\Pages::get(
            $fields["uri"],
            Jaris\Language::getCurrent()
        );

        $title = !$content_data["display_title"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                . $page_data["title"]
                . "</a>"
        ;

        $summary = !$content_data["display_summary"] ?
            false
            :
            Jaris\Util::contentPreview(
                (
                    $page_data["input_format"] == "php_code" ?
                        Jaris\System::evalPHP($page_data["content"])
                        :
                        $page_data["content"]
                ),
                $content_data["maximum_words"],
                true
            )
        ;

        $price = "";

        if ($ecommerce_products && !empty($content_data["show_prices"])) {
            $price = ecommerce_get_product_price($page_data, $group);

            $price_plain = $price;

            if ($price) {
                $price = '$' . number_format($price, 2, ".", ",");
            }

            if (!empty($page_data["on_sale"])) {
                $price = '<div class="on-sale">'
                    . '<span>' . t("on sale") . '</span> '
                    . $price
                    . '</div>'
                ;
            }
        }

        $reviews_score = "";

        if ($reviews_enabled) {
            $reviews_score = '<span class="listing-review-score">'
                . $fields["reviews_score"] * $reviews_max_score
                . '</span>'
            ;
        }

        $image_list = Jaris\Data::sort(
            Jaris\Pages\Images::getList(
                $fields["uri"]
            ),
            "order"
        );
        $image_name = null;
        $image_description = null;
        $image = null;
        $image_url = null;

        if ($image_list) {
            foreach ($image_list as $id => $image_fields) {
                $image_name = $image_fields["name"];
                $image_description = $image_fields["description"];
                break;
            }
        }

        if ($image_name) {
            $image = !$content_data["thumbnail_show"] ?
                false
                :
                "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\"><img alt=\"" .
                htmlspecialchars($image_description) .
                "\" title=\"" . htmlspecialchars($page_data["title"]) .
                "\" src=\"" .
                Jaris\Uri::url(
                    "image/" . $fields["uri"] . "/$image_name",
                    [
                        "w" => $content_data["thumbnail_width"],
                        "h" => $content_data["thumbnail_height"],
                        "ar" => $content_data["thumbnail_keep_aspectratio"],
                        "bg" => $content_data["thumbnail_bg"]
                    ]
                ) . "\" /></a>"
            ;

            $image_url = Jaris\Uri::url(
                "image/" . $fields["uri"] . "/$image_name",
                [
                    "w" => $content_data["thumbnail_width"],
                    "h" => $content_data["thumbnail_height"],
                    "ar" => $content_data["thumbnail_keep_aspectratio"],
                    "bg" => $content_data["thumbnail_bg"]
                ]
            );
        } else {
            $type_image = Jaris\Types::getImageUrl(
                $page_data["type"],
                $content_data["thumbnail_width"],
                $content_data["thumbnail_height"],
                $content_data["thumbnail_keep_aspectratio"],
                $content_data["thumbnail_bg"]
            );

            if ($type_image != "") {
                $type_data = Jaris\Types::get($page_data["type"]);
                $image_name = $type_data['image'];

                $image = !$content_data["thumbnail_show"] ?
                    false
                    :
                    "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                    . "<img "
                    . "title=\"" . htmlspecialchars($page_data["title"]) . "\" "
                    . "src=\"" . $type_image . "\" "
                    . "/>"
                    . "</a>"
                ;
            }
        }

        $url = Jaris\Uri::url($fields["uri"]);

        $view_more = !$content_data["display_more"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                . t("View More")
                . "</a>"
        ;

        $layout = $content_data["layout"];
        if ($content_data["layout"] == "list") {
            $layout = "list-content";
        }

        if ($content_data["layout"] == "grid") {
            if ($column == 1) {
                $output .= "<tr>";
            }

            $output .= "<td>";
        }

        ob_start();
        include(listing_result_template($uri, $page_data["type"], $layout));
        $output .= ob_get_contents();
        ob_end_clean();

        if ($content_data["layout"] == "grid") {
            $output .= "</td>";

            if ($column == $content_data["results_per_row"]) {
                $column = 1;
                $output .= "</tr>";
            } else {
                if ($current_product != $products_count) {
                    $column++;
                }
            }
        }

        $current_product++;
    }

    if ($content_data["layout"] == "grid") {
        if (
            ($column != $content_data["results_per_row"] && $column != 1) ||
            (($products_count % $content_data["results_per_row"]) > 0)
        ) {
            for ($column; $column != $content_data["results_per_row"]; $column++) {
                $output .= "<td></td>";
            }

            $output .= "</tr>";
        }

        $output .= "</tbody>";
        $output .= "</table>";
    }

    if ($content_data["layout"] == "list") {
        ob_start();

        include(listing_result_template($uri, "all", "list-footer"));

        $output .= ob_get_contents();

        ob_end_clean();
    }

    if ($content_data["display_navigation"]) {
        ob_start();

        $arguments = [];

        if (isset($_REQUEST["s"])) {
            $arguments["s"] = $_REQUEST["s"];
        }

        if (isset($_REQUEST["a"])) {
            $arguments["a"] = $_REQUEST["a"];
        }

        if (count($content_data["filter_types"]) == 1) {
            $categories = Jaris\Categories::getList(
                $content_data["filter_types"][0]
            );

            foreach ($categories as $cat_name => $cat_fields) {
                if (isset($_REQUEST[$cat_name])) {
                    $arguments[$cat_name] = $_REQUEST[$cat_name];
                }
            }
        }

        Jaris\System::printNavigation(
            $results_count,
            $page,
            $uri,
            "",
            $limit,
            $arguments
        );

        $output .= ob_get_contents();

        ob_end_clean();
    }

    return $output;
}

function listing_block_print_results($uri, $content_data)
{
    $types = "";
    if (
        is_array($content_data["filter_types"])
        &&
        count($content_data["filter_types"]) > 0
    ) {
        $types = "and (";
        foreach ($content_data["filter_types"] as $type) {
            $types .= "type='$type' or ";
        }

        $types = rtrim($types, " or");

        $types .= ")";
    } else {
        $content_data["filter_types"] = [];
    }

    // Handle ecommerce settings
    $ecommerce_installed = Jaris\Modules::isInstalled("ecommerce");
    $ecommerce_product_types = false;

    if (
        !empty($content_data["treat_as_products"])
        &&
        $ecommerce_installed
    ) {
        $ecommerce_product_types = true;
        $product_types = ecommerce_get_product_types();

        foreach ($content_data["filter_types"] as $type) {
            if (!isset($product_types[$type])) {
                $ecommerce_product_types = false;
                break;
            }
        }
    }

    $ecommerce_products = $ecommerce_installed
        &&
        $ecommerce_product_types
    ;

    $on_sale = "";

    if ($ecommerce_products) {
        $on_sale = !empty($content_data["onsale_only"]) ?
            " and on_sale != ''"
            :
            ""
        ;
    }

    // Handle realty settings
    $realty_installed = Jaris\Modules::isInstalled("realty");
    $realty_product_types = false;

    if (
        !empty($content_data["treat_as_properties"])
        &&
        $realty_installed
    ) {
        $realty_product_types = true;

        foreach ($content_data["filter_types"] as $type) {
            if ($type != "property") {
                $realty_product_types = false;
                break;
            }
        }
    }

    $realty_properties = $realty_installed
        &&
        $realty_product_types
    ;

    $realty_type = "";
    $realty_country = "";
    $realty_state_province = "";
    $realty_city = "";
    $realty_category = "";
    $realty_status = "";
    $realty_foreclosure = "";
    $realty_commercial = "";

    if ($realty_properties) {
        $realty_type = !empty($content_data["realty_type"]) ?
            " and sub_category ='".$content_data["realty_type"]."'"
            :
            ""
        ;

        $realty_country = !empty($content_data["realty_country"]) ?
            " and country='".$content_data["realty_country"]."'"
            :
            ""
        ;

        $realty_state_province = !empty($content_data["realty_state_province"]) ?
            " and state_province='".$content_data["realty_state_province"]."'"
            :
            ""
        ;

        $realty_city = !empty($content_data["realty_city"]) ?
            " and city='".$content_data["realty_city"]."'"
            :
            ""
        ;

        if (
            isset($content_data["realty_category"])
            &&
            trim($content_data["realty_category"]) != ""
        ) {
            $realty_category = " and category='".$content_data["realty_category"]."'";
        } elseif (
            $_REQUEST["sub_category"] == "rent"
        ) {
            $category_list = realty_get_categories("rent");

            $realty_category .= "and category in (";
            foreach ($category_list as $category_name) {
                $realty_category .= "'"
                    . str_replace("'", "''", $category_name)
                    . "',"
                ;
            }
            $realty_category = rtrim($realty_category, ",");
            $realty_category .= ") ";
        }

        $realty_status = !empty($content_data["realty_status"]) ?
            " and status='".$content_data["realty_status"]."'"
            :
            ""
        ;

        if ($content_data["realty_foreclosure"] == "y") {
            $realty_foreclosure = " and is_foreclosure = '1'";
        } elseif ($content_data["realty_foreclosure"] == "n") {
            $realty_foreclosure = " and is_foreclosure = '0'";
        }

        if ($content_data["realty_commercial"] == "y") {
            $realty_commercial = " and is_commercial = '1'";
        } elseif ($content_data["realty_commercial"] == "n") {
            $realty_commercial = " and is_commercial = '0'";
        }
    }

    $authors = "";
    $authors_list = explode(",", $content_data["filter_authors"]);
    if (
        is_array($authors_list) &&
        count($authors_list) > 0 &&
        trim($authors_list[0]) != ""
    ) {
        $authors = "and (";

        foreach ($authors_list as $author) {
            $authors .= "author='" . trim($author) . "' or ";
        }

        $authors = rtrim($authors, " or");

        $authors .= ")";
    }

    $has_categories = "";
    $where_categories = "";

    if (
        is_array($content_data["filter_categories"]) &&
        count($content_data["filter_categories"]) > 0
    ) {
        $categories = serialize($content_data["filter_categories"]);

        if ($content_data["category_matching"] != "match_partial") {
            $has_categories = ", "
                . "hascategories(categories, '$categories') as has_category"
            ;
        } else {
            $has_categories = ", "
                . "hassomecategories(categories, '$categories') as has_category"
            ;
        }

        $where_categories = "and has_category > 0";
    }

    $ordering = "";
    $where_date = "";
    switch ($content_data["filter_ordering"]) {
        case "date_desc":
            $ordering = "order by created_date desc";
            break;
        case "date_asc":
            $ordering = "order by created_date asc";
            break;
        case "title_asc":
            $ordering = "order by title asc";
            break;
        case "title_desc":
            $ordering = "order by title desc";
            break;
        case "views_desc":
            $ordering = "order by views desc";
            break;
        case "views_today_desc":
            $ordering = "order by views_day_count desc";
            break;
        case "views_week_desc":
            $ordering = "order by views_week_count desc";
            break;
        case "views_month_desc":
            $ordering = "order by views_month_count desc";
            break;
        case "current_date_asc":
            $ordering = "order by created_date asc";
            $where_date .= "and created_date >= '".time()."'";
            break;
        case "current_date_desc":
            $ordering = "order by created_date desc";
            $where_date .= "and created_date >= '".time()."'";
            break;
        default:
            $ordering = "order by created_date desc";
            break;
    }

    $skip_current = "";
    if (
        !empty($content_data["skip_current_page"])
        ||
        (
            !array_key_exists("skip_current_page", $content_data)
            &&
            !empty($content_data["related_to_current_page"])
        )
    ) {
        $skip_current = "uri <> '" . Jaris\Uri::get() . "' and ";
    }

    $related_select = "";
    $related_where = "";
    if (!empty($content_data["related_to_current_page"])) {
        $displayed_page_data = Jaris\Pages::get(
            Jaris\Uri::get(),
            Jaris\Language::getCurrent()
        );

        $displayed_title = str_replace(
            "'",
            "''",
            $displayed_page_data["title"]
        );

        $displayed_content = str_replace(
            "'",
            "''",
            $displayed_page_data["content"]
        );

        $related_select = ", "
            . "leftsearch(title, '$displayed_title') as title_relevancy, "
            . "leftsearch(content, '$displayed_content') as content_relevancy "
        ;
        $related_where = "(title_relevancy > 0 or content_relevancy > 0) and ";

        // Change default ordering
        $ordering = "order by title_relevancy desc, content_relevancy desc";
    }

    $user = Jaris\Authentication::currentUser();
    $group = Jaris\Authentication::currentUserGroup();

    // Get results
    $db = Jaris\Sql::open("search_engine");

    $query = "";

    $limit = intval($content_data["results_to_show"]);

    if ($ecommerce_products) {
        Jaris\Sql::attach("ecommerce_inventory", $db);

        $query .= "select a.uri, haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions "
            . "$has_categories $related_select from uris a "
            . "inner join ecommerce_inventory b on "
            . "a.uri = b.uri "
            . "where "
            . str_replace("uri <>", "a.uri <>", $skip_current) . " "
            . "$related_where "
            . "b.variation=0 and b.in_stock=1 and "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories $on_sale $ordering "
            . "limit 0, ".$limit
        ;

        $query = str_replace("type=", "a.type=", $query);
    } elseif ($realty_properties) {
        Jaris\Sql::attach("realty_properties", $db);

        $query .= "select a.uri, haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions "
            . "$has_categories $related_select from uris a "
            . "inner join realty_properties b on "
            . "a.uri = b.uri "
            . "where "
            . str_replace("uri <>", "a.uri <>", $skip_current) . " "
            . "$related_where "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories "
            . "$realty_type $realty_country $realty_state_province $realty_city "
            . "$realty_category $realty_status $realty_foreclosure "
            . "$realty_commercial $ordering "
            . "limit 0, ".$limit
        ;

        $query = str_replace("type=", "a.type=", $query);
    } else {
        $query .= "select uri, haspermission(groups, '$group') as has_permissions, "
            . "hasuserpermission(users, '$user') as has_user_permissions "
            . "$has_categories $related_select from uris "
            . "where "
            . "$skip_current $related_where "
            . "has_permissions > 0 and has_user_permissions > 0 and "
            . "approved='a' $types $authors $where_date $where_categories $ordering "
            . "limit 0, ".$limit
        ;
    }

    Jaris\Sql::turbo($db);

    $result = Jaris\Sql::query($query, $db);

    $results = [];
    if ($fields = Jaris\Sql::fetchArray($result)) {
        $results[] = $fields;

        while ($fields = Jaris\Sql::fetchArray($result)) {
            $results[] = $fields;
        }
    }

    Jaris\Sql::close($db);

    $output = '<div id="listing-block-container-'.$content_data["id"].'" class="listing-block-container">';

    foreach ($results as $fields) {
        $page_data = Jaris\Pages::get(
            $fields["uri"],
            Jaris\Language::getCurrent()
        );

        $title = !$content_data["display_title"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                . $page_data["title"]
                . "</a>"
        ;

        $summary = !$content_data["display_summary"] ?
            false
            :
            Jaris\Util::contentPreview(
                (
                    $page_data["input_format"] == "php_code" ?
                        Jaris\System::evalPHP($page_data["content"])
                        :
                        $page_data["content"]
                ),
                $content_data["maximum_words"],
                true
            )
        ;

        $price = "";

        if ($ecommerce_products && !empty($content_data["show_prices"])) {
            $price = ecommerce_get_product_price($page_data, $group);

            $price_plain = $price;

            if ($price) {
                $price = '$' . number_format($price, 2, ".", ",");
            }

            if (!empty($page_data["on_sale"])) {
                $price = '<div class="on-sale">'
                    . '<span>' . t("on sale") . '</span> '
                    . $price
                    . '</div>'
                ;
            }
        }

        $image_list = Jaris\Pages\Images::getList($fields["uri"]);
        $image_name = null;
        $image_description = null;
        $image = null;

        if ($image_list) {
            foreach ($image_list as $id => $image_fields) {
                $image_name = $image_fields["name"];
                $image_description = $image_fields["description"];
                break;
            }
        }

        if ($image_name) {
            $image = !$content_data["thumbnail_show"] ?
                false
                :
                "<a href=\"" . Jaris\Uri::url($fields["uri"]) .
                "\"><img alt=\"" .
                htmlspecialchars($image_description) .
                "\" title=\"" . htmlspecialchars($page_data["title"]) .
                "\" src=\"" .
                Jaris\Uri::url(
                    "image/" . $fields["uri"] . "/$image_name",
                    [
                        "w" => $content_data["thumbnail_width"],
                        "h" => $content_data["thumbnail_height"],
                        "ar" => $content_data["thumbnail_keep_aspectratio"],
                        "bg" => $content_data["thumbnail_bg"]
                    ]
                ) . "\" /></a>"
            ;
        } else {
            $type_image = Jaris\Types::getImageUrl(
                $page_data["type"],
                $content_data["thumbnail_width"],
                $content_data["thumbnail_height"],
                $content_data["thumbnail_keep_aspectratio"],
                $content_data["thumbnail_bg"]
            );

            if ($type_image != "") {
                $type_data = Jaris\Types::get($page_data["type"]);
                $image_name = $type_data['image'];

                $image = !$content_data["thumbnail_show"] ?
                    false
                    :
                    "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                    . "<img "
                    . "title=\"" . htmlspecialchars($page_data["title"]) . "\" "
                    . "src=\"" . $type_image . "\" "
                    . "/>"
                    . "</a>"
                ;
            }
        }

        $view_more = !$content_data["display_more"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">"
                . t("View More")
                . "</a>"
        ;

        ob_start();

        include(listing_result_template($uri, $page_data["type"], "block"));

        $output .= ob_get_contents();

        ob_end_clean();
    }

    $output .= '</div>';

    return $output;
}

function listing_result_template($page, $results_type = "all", $template_type = "teaser")
{
    $theme = Jaris\Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_template = Jaris\Themes::directory($theme)
        . "listing-$template_type.php"
    ;
    $current_page = Jaris\Themes::directory($theme)
        . "listing-$template_type-" . $page . ".php"
    ;
    $current_results_type = Jaris\Themes::directory($theme)
        . "listing-$template_type-" . $results_type . ".php"
    ;
    $current_page_result_type = Jaris\Themes::directory($theme)
        . "listing-$template_type-" . $page . "-" . $results_type . ".php"
    ;

    $template_path = "";

    if (file_exists($current_page_result_type)) {
        $template_path = $current_page_result_type;
    } elseif (file_exists($current_page)) {
        $template_path = $current_page;
    } elseif (file_exists($current_results_type)) {
        $template_path = $current_results_type;
    } elseif (file_exists($current_template)) {
        $template_path = $current_template;
    } else {
        $template_path = Jaris\Modules::directory("listing")
            . "templates/listing-$template_type.php"
        ;
    }

    return $template_path;
}
