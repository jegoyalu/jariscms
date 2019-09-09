<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the center blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: module_identifier
        browse_listing_by_categories
    field;

    field: position
        left
    field;

    field: description
        filter listing result by categories
    field;

    field: title
        Filter Results
    field;

    field: content
    <?php
        $page_data = Jaris\Site::$page_data[0];

        $page_data["filter_types"] = unserialize(
            $page_data["filter_types"]
        );

        $content_type = $page_data["filter_types"][0];

        if (trim($content_type) != "") {
            $categories = Jaris\Categories::getList($content_type);

            $selected_categories = [];

            foreach ($categories as $cat_name => $cat_fields) {
                if (isset($_REQUEST[$cat_name])) {
                    $selected_categories[$cat_name] = $_REQUEST[$cat_name];
                }
            }

            $fields = listing_category_filter_fields(
                $selected_categories,
                "",
                $content_type,
                "",
                false,
                $page_data["filter_selector_type"]
            );

            $parameters["name"] = "listing-filter-by";
            $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
            $parameters["method"] = "get";

            $fieldset[] = ["fields" => $fields];

            $fields_other[] = [
                "type" => "submit",
                "name" => "filter",
                "value" => t("Filter")
            ];

            $fieldset[] = ["fields" => $fields_other];

            print Jaris\Forms::generate($parameters, $fieldset);
        }
    ?>
    field;

    field: order
        -4
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        $page_data = Jaris\Site::$page_data[0];

        if ($page_data["type"] == "listing") {
            $page_data["filter_types"] = unserialize(
                $page_data["filter_types"]
            );

            if (
                $page_data["show_categories"]
                &&
                is_array($page_data["filter_types"])
                &&
                count($page_data["filter_types"]) == 1
            ) {
                return true;
            }
        }

        return false;
    ?>
    field;

    field: is_system
        1
    field;
row;