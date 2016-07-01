<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

function listing_category_fields($selected = null, $main_category = null, $type = null)
{
    $fields = array();

    $categories_list = array();

    if(!$main_category)
    {
        $categories_list = Jaris\Categories::getList($type);
    }
    else
    {
        $categories_list[$main_category] = Jaris\Categories::get($main_category);
    }

    if($categories_list)
    {
        foreach($categories_list as $machine_name => $values)
        {
            $subcategories = Jaris\Categories::getSubcategoriesInParentOrder($machine_name);

            $select_values = null;
            /* if(!$values["multiple"])
              {
              $select_values[t("-None Selected-")] = "-1";
              } */

            foreach($subcategories as $id => $sub_values)
            {
                //In case person created categories with the same name
                if(isset($select_values[t($sub_values["title"])]))
                {
                    $title = t($sub_values["title"]) . " ";
                    while(isset($select_values[$title]))
                    {
                        $title .= " ";
                    }

                    $select_values[$title] = $id;
                }
                else
                {
                    $select_values[t($sub_values["title"])] = $id;
                }
            }

            /* $multiple = false;
              if($values["multiple"])
              {
              $multiple = true;
              } */

            $multiple = true;

            if(count($select_values) >= 1)
            {
                if(count($selected) > 0)
                {
                    $fields[] = array(
                        "type" => "select",
                        "inline" => true,
                        "multiple" => $multiple,
                        "selected" => $selected[$machine_name],
                        "name" => "filter_category_{$machine_name}[]",
                        "label" => t($values["name"]),
                        "id" => "filter_category_" . $machine_name,
                        "value" => $select_values
                    );
                }
                else
                {
                    $fields[] = array(
                        "type" => "select",
                        "inline" => true,
                        "multiple" => $multiple,
                        "name" => "filter_category_{$machine_name}[]",
                        "label" => t($values["name"]),
                        "id" => "filter_category_" . $machine_name,
                        "value" => $select_values
                    );
                }
            }
        }
    }

    return $fields;
}

function listing_print_results($uri, $content_data)
{
    $page = 1;

    if(isset($_REQUEST["page"]))
    {
        $page = intval($_REQUEST["page"]);
    }

    $types = "";

    if(
        is_array($content_data["filter_types"]) &&
        count($content_data["filter_types"]) > 0
    )
    {
        $types = "and (";
        foreach($content_data["filter_types"] as $type)
        {
            $types .= "type='$type' or ";
        }

        $types = rtrim($types, " or");

        $types .= ")";
    }

    $authors = "";
    $authors_list = explode(",", $content_data["filter_authors"]);

    if(
        is_array($authors_list) &&
        count($authors_list) > 0 &&
        trim($authors_list[0]) != ""
    )
    {
        $authors = "and (";

        foreach($authors_list as $author)
        {
            $authors .= "author='" . trim($author) . "' or ";
        }

        $authors = rtrim($authors, " or");

        $authors .= ")";
    }

    $has_categories = "";
    $where_categories = "";

    if(
        is_array($content_data["filter_categories"]) &&
        count($content_data["filter_categories"]) > 0
    )
    {
        $categories = serialize($content_data["filter_categories"]);

        if($content_data["category_matching"] != "match_partial")
        {
            $has_categories = ", hascategories(categories, '$categories') as has_category";
        }
        else
        {
            $has_categories = ", hassomecategories(categories, '$categories') as has_category";
        }

        $where_categories = "and has_category > 0";
    }

    $ordering = "";
    switch($content_data["filter_ordering"])
    {
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
        default:
            $ordering = "order by created_date desc";
            break;
    }

    $user = Jaris\Authentication::currentUser();
    $group = Jaris\Authentication::currentUserGroup();

    $results_count = 0;
    $db = Jaris\Sql::open("search_engine");
    Jaris\Sql::turbo($db);

    $result = Jaris\Sql::query(
        "select haspermission(groups, '$group') as has_permissions, hasuserpermission(users, '$user') as has_user_permissions, count(uri) as uri_count $has_categories from uris where has_permissions > 0 and has_user_permissions > 0 $types $authors $where_categories",
        $db
    );

    while($data_count = Jaris\Sql::fetchArray($result))
    {
        $results_count = $data_count["uri_count"];
        break;
    }

    Jaris\Sql::close($db);

    $results = Jaris\Sql::getDataList(
        "search_engine",
        "uris",
        $page - 1,
        $content_data["results_per_page"],
        "where has_permissions > 0 and has_user_permissions > 0 $types $authors $where_categories $ordering", "haspermission(groups, '$group') as has_permissions, hasuserpermission(users, '$user') as has_user_permissions, uri $has_categories"
    );

    $output = "";

    if($content_data["layout"] == "list")
    {
        ob_start();
        include(listing_result_template($uri, "all", "list-header"));
        $output .= ob_get_contents();
        ob_end_clean();
    }

    $column = 1;
    $current_product = 1;
    $products_count = count($results);

    if($content_data["layout"] == "grid")
    {
        $output .= "<table class=\"listing-grid-table\">";
        $output .= "<tbody>";
    }

    foreach($results as $fields)
    {
        $page_data = Jaris\Pages::get($fields["uri"], Jaris\Language::getCurrent());

        $title = !$content_data["display_title"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">" . $page_data["title"] . "</a>"
        ;

        $summary = !$content_data["display_summary"] ?
            false
            :
            Jaris\Util::contentPreview(
                $page_data["content"],
                $content_data["maximum_words"],
                true
            )
        ;

        $image_list = Jaris\Data::sort(Jaris\Pages\Images::getList($fields["uri"]), "order");
        $image_name = null;
        $image_description = null;
        $image = null;
        $image_url = null;

        if($image_list)
        {
            foreach($image_list as $id => $image_fields)
            {
                $image_name = $image_fields["name"];
                $image_description = $image_fields["description"];
                break;
            }
        }

        if($image_name)
        {
            $image = !$content_data["thumbnail_show"] ?
                false
                :
                "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\"><img alt=\"" .
                htmlspecialchars($image_description) .
                "\" title=\"" . htmlspecialchars($page_data["title"]) .
                "\" src=\"" .
                Jaris\Uri::url(
                    "image/" . $fields["uri"] . "/$image_name",
                    array(
                        "w" => $content_data["thumbnail_width"],
                        "h" => $content_data["thumbnail_height"],
                        "ar" => $content_data["thumbnail_keep_aspectratio"],
                        "bg" => $content_data["thumbnail_bg"]
                    )
                ) . "\" /></a>"
            ;

            $image_url = Jaris\Uri::url(
                "image/" . $fields["uri"] . "/$image_name",
                array(
                    "w" => $content_data["thumbnail_width"],
                    "h" => $content_data["thumbnail_height"],
                    "ar" => $content_data["thumbnail_keep_aspectratio"],
                    "bg" => $content_data["thumbnail_bg"]
                )
            );
        }
        else
        {
            $type_image = Jaris\Types::getImageUrl(
                $page_data["type"],
                $content_data["thumbnail_width"],
                $content_data["thumbnail_height"],
                $content_data["thumbnail_keep_aspectratio"],
                $content_data["thumbnail_bg"]
            );

            if($type_image != "")
            {
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
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">" . t("View More") . "</a>"
        ;

        $layout = $content_data["layout"];
        if($content_data["layout"] == "list")
        {
            $layout = "list-content";
        }

        if($content_data["layout"] == "grid")
        {
            if($column == 1)
            {
                $output .= "<tr>";
            }

            $output .= "<td>";
        }

        ob_start();
        include(listing_result_template($uri, $page_data["type"], $layout));
        $output .= ob_get_contents();
        ob_end_clean();

        if($content_data["layout"] == "grid")
        {
            $output .= "</td>";

            if($column == $content_data["results_per_row"])
            {
                $column = 1;
                $output .= "</tr>";
            }
            else
            {
                if($current_product != $products_count)
                    $column++;
            }
        }

        $current_product++;
    }

    if($content_data["layout"] == "grid")
    {
        if(
            ($column != $content_data["results_per_row"] && $column != 1) ||
            (($products_count % $content_data["results_per_row"]) > 0)
        )
        {
            for($column; $column != $content_data["results_per_row"]; $column++)
            {
                $output .= "<td></td>";
            }

            $output .= "</tr>";
        }

        $output .= "</tbody>";
        $output .= "</table>";
    }

    if($content_data["layout"] == "list")
    {
        ob_start();

        include(listing_result_template($uri, "all", "list-footer"));

        $output .= ob_get_contents();

        ob_end_clean();
    }

    if($content_data["display_navigation"])
    {
        ob_start();

        Jaris\System::printNavigation(
            $results_count,
            $page,
            $uri,
            "",
            $content_data["results_per_page"]
        );

        $output .= ob_get_contents();

        ob_end_clean();
    }

    return $output;
}

function listing_block_print_results($uri, $content_data)
{
    $types = "";
    if(
        is_array($content_data["filter_types"])
        &&
        count($content_data["filter_types"]) > 0
    )
    {
        $types = "and (";
        foreach($content_data["filter_types"] as $type)
        {
            $types .= "type='$type' or ";
        }

        $types = rtrim($types, " or");

        $types .= ")";
    }

    $authors = "";
    $authors_list = explode(",", $content_data["filter_authors"]);
    if(
        is_array($authors_list) &&
        count($authors_list) > 0 &&
        trim($authors_list[0]) != ""
    )
    {
        $authors = "and (";

        foreach($authors_list as $author)
        {
            $authors .= "author='" . trim($author) . "' or ";
        }

        $authors = rtrim($authors, " or");

        $authors .= ")";
    }

    $has_categories = "";
    $where_categories = "";

    if(
        is_array($content_data["filter_categories"]) &&
        count($content_data["filter_categories"]) > 0
    )
    {
        $categories = serialize($content_data["filter_categories"]);

        if($content_data["category_matching"] != "match_partial")
        {
            $has_categories = ", hascategories(categories, '$categories') as has_category";
        }
        else
        {
            $has_categories = ", hassomecategories(categories, '$categories') as has_category";
        }

        $where_categories = "and has_category > 0";
    }

    $ordering = "";
    switch($content_data["filter_ordering"])
    {
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
        default:
            $ordering = "order by created_date desc";
            break;
    }

    $user = Jaris\Authentication::currentUser();
    $group = Jaris\Authentication::currentUserGroup();

    $results = array();

    if(!$content_data["related_to_current_page"])
    {
        $results = Jaris\Sql::getDataList(
            "search_engine",
            "uris",
            0,
            $content_data["results_to_show"],
            "where has_permissions > 0 and has_user_permissions > 0 $types $authors $where_categories $ordering",
            "haspermission(groups, '$group') as has_permissions, hasuserpermission(users, '$user') as has_user_permissions, uri $has_categories"
        );
    }
    else
    {
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

        $results = Jaris\Sql::getDataList(
            "search_engine",
            "uris",
            0,
            $content_data["results_to_show"],
            "where uri <> '" . Jaris\Uri::get() . "' and (title_relevancy > 0 or content_relevancy > 0) and has_permissions > 0 and has_user_permissions > 0 $types $authors $where_categories order by title_relevancy desc, content_relevancy desc", "leftsearch(title, '$displayed_title') as title_relevancy, leftsearch(content, '$displayed_content') as content_relevancy, haspermission(groups, '$group') as has_permissions, hasuserpermission(users, '$user') as has_user_permissions, uri $has_categories"
        );
    }

    $output = "";

    foreach($results as $fields)
    {
        $page_data = Jaris\Pages::get($fields["uri"], Jaris\Language::getCurrent());

        $title = !$content_data["display_title"] ?
            false
            :
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">" . $page_data["title"] . "</a>"
        ;

        $summary = !$content_data["display_summary"] ?
            false
            :
            Jaris\Util::contentPreview(
                $page_data["content"],
                $content_data["maximum_words"],
                true
            )
        ;

        $image_list = Jaris\Pages\Images::getList($fields["uri"]);
        $image_name = null;
        $image_description = null;
        $image = null;

        if($image_list)
        {
            foreach($image_list as $id => $image_fields)
            {
                $image_name = $image_fields["name"];
                $image_description = $image_fields["description"];
                break;
            }
        }

        if($image_name)
        {
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
                    array(
                        "w" => $content_data["thumbnail_width"],
                        "h" => $content_data["thumbnail_height"],
                        "ar" => $content_data["thumbnail_keep_aspectratio"],
                        "bg" => $content_data["thumbnail_bg"]
                    )
                ) . "\" /></a>"
            ;
        }
        else
        {
            $type_image = Jaris\Types::getImageUrl(
                $page_data["type"],
                $content_data["thumbnail_width"],
                $content_data["thumbnail_height"],
                $content_data["thumbnail_keep_aspectratio"],
                $content_data["thumbnail_bg"]
            );

            if($type_image != "")
            {
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
            "<a href=\"" . Jaris\Uri::url($fields["uri"]) . "\">" . t("View More") . "</a>"
        ;

        ob_start();

        include(listing_result_template($uri, $page_data["type"], "block"));

        $output .= ob_get_contents();

        ob_end_clean();
    }

    return $output;
}

function listing_result_template($page, $results_type = "all", $template_type = "teaser")
{
    $theme = Jaris\Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_template = Jaris\Themes::directory($theme) . "listing-$template_type.php";
    $current_page = Jaris\Themes::directory($theme) . "listing-$template_type-" . $page . ".php";
    $current_results_type = Jaris\Themes::directory($theme) . "listing-$template_type-" . $results_type . ".php";
    $current_page_result_type = Jaris\Themes::directory($theme) . "listing-$template_type-" . $page . "-" . $results_type . ".php";

    $template_path = "";

    if(file_exists($current_page_result_type))
    {
        $template_path = $current_page_result_type;
    }
    elseif(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($current_results_type))
    {
        $template_path = $current_results_type;
    }
    elseif(file_exists($current_template))
    {
        $template_path = $current_template;
    }
    else
    {
        $template_path = Jaris\Modules::directory("listing") . "templates/listing-$template_type.php";
    }

    return $template_path;
}

?>
