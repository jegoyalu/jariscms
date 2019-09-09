<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Listing Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_blocks"));

        $block_data = Jaris\Blocks::get(
            intval($_REQUEST["id"]),
            strval($_REQUEST["position"])
        );

        $block_data["filter_types"] = unserialize($block_data["filter_types"]);
        $block_data["filter_categories"] = unserialize($block_data["filter_categories"]);

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("listing-blocks-edit")
        )
        {
            $block_data["description"] = $_REQUEST["description"];
            $block_data["title"] = $_REQUEST["title"];
            $block_data["display_rule"] = $_REQUEST["display_rule"];
            $block_data["pages"] = $_REQUEST["pages"];
            $block_data["groups"] = $_REQUEST["groups"];
            $block_data["themes"] = $_REQUEST["themes"];

            if(
                Jaris\Authentication::groupHasPermission(
                    "return_code_blocks",
                    Jaris\Authentication::currentUserGroup()
                )
                ||
                Jaris\Authentication::isAdminLogged()
            )
            {
                $block_data["return"] = $_REQUEST["return"];
            }

            $block_data["pre_content"] = $_REQUEST["pre_content"];
            $block_data["sub_content"] = $_REQUEST["sub_content"];

            $block_data["filter_types"] = serialize($_REQUEST["filter_types"]);
            $block_data["filter_authors"] = $_REQUEST["filter_authors"];

            $block_data["category_matching"] = $_REQUEST["category_matching"];

            $block_data["skip_current_page"] = $_REQUEST["skip_current_page"];
            $block_data["related_to_current_page"] = $_REQUEST["related_to_current_page"];

            if(Jaris\Modules::isInstalled("ecommerce"))
            {
                $block_data["treat_as_products"] = $_REQUEST["treat_as_products"];
                $block_data["show_prices"] = $_REQUEST["show_prices"];
                $block_data["onsale_only"] = $_REQUEST["onsale_only"];
            }

            if(Jaris\Modules::isInstalled("realty"))
            {
                $block_data["treat_as_properties"] = $_REQUEST["treat_as_properties"];
                $block_data["realty_type"] = $_REQUEST["realty_type"];
                $block_data["realty_country"] = $_REQUEST["country"];
                $block_data["realty_state_province"] = $_REQUEST["state_province"];
                $block_data["realty_city"] = $_REQUEST["city"];
                $block_data["realty_category"] = $_REQUEST["realty_category"];
                $block_data["realty_status"] = $_REQUEST["realty_status"];
                $block_data["realty_foreclosure"] = $_REQUEST["realty_foreclosure"];
                $block_data["realty_commercial"] = $_REQUEST["realty_commercial"];
            }

            $filter_categories_list = Jaris\Categories::getList();
            $filter_categories = array();

            if($filter_categories_list)
            {
                foreach($filter_categories_list as $machine_name => $data)
                {
                    if(isset($_REQUEST["filter_category_$machine_name"]))
                    {
                        $filter_categories[$machine_name] = $_REQUEST["filter_category_$machine_name"];
                    }
                }
            }

            $block_data["filter_categories"] = serialize($filter_categories);

            $block_data["filter_ordering"] = $_REQUEST["filter_ordering"];
            $block_data["display_title"] = $_REQUEST["display_title"];
            $block_data["display_summary"] = $_REQUEST["display_summary"];
            $block_data["display_more"] = $_REQUEST["display_more"];
            $block_data["maximum_words"] = intval($_REQUEST["maximum_words"]);
            $block_data["results_to_show"] = intval($_REQUEST["results_to_show"]);
            $block_data["thumbnail_show"] = $_REQUEST["thumbnail_show"];
            $block_data["thumbnail_width"] = intval($_REQUEST["thumbnail_width"]);
            $block_data["thumbnail_height"] = intval($_REQUEST["thumbnail_height"]);
            $block_data["thumbnail_bg"] = $_REQUEST["thumbnail_bg"];
            $block_data["thumbnail_keep_aspectratio"] = $_REQUEST["thumbnail_keep_aspectratio"];

            if(Jaris\Blocks::edit($_REQUEST["id"], $_REQUEST["position"], $block_data))
            {
                if($_REQUEST["position"] != $_REQUEST["new_position"])
                {
                    Jaris\Blocks::move(
                        $_REQUEST["id"],
                        $_REQUEST["position"],
                        $_REQUEST["new_position"]
                    );
                }

                Jaris\View::addMessage(t("Your changes have been saved to the block."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/blocks");
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/blocks");
        }

        Jaris\View::addTab(
            t("Edit"),
            Jaris\Modules::getPageUri("admin/blocks/listing/edit", "listing"),
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(
            t("Delete"),
            "admin/blocks/delete",
            array("id" => $_REQUEST["id"], "position" => $_REQUEST["position"])
        );

        Jaris\View::addTab(t("Blocks"), "admin/blocks");

        //Print block edit form

        $parameters["name"] = "listing-blocks-edit";
        $parameters["class"] = "listing-blocks-edit";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/blocks/listing/edit", "listing")
        );
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $_REQUEST["id"]
        );

        $fields[] = array(
            "type" => "hidden",
            "name" => "position",
            "value" => $_REQUEST["position"]
        );

        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $fields[] = array(
            "type" => "select",
            "name" => "new_position",
            "label" => t("Position:"),
            "id" => "new_position",
            "value" => $positions,
            "selected" => $_REQUEST["new_position"] ?
                $_REQUEST["new_position"]
                :
                $_REQUEST["position"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "value" => $block_data["description"],
            "required" => true
        );

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "value" => $block_data["title"]
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "pre_content",
            "id" => "pre_content",
            "label" => t("Pre-content:"),
            "value" => $_REQUEST["pre_content"] ?
                $_REQUEST["pre_content"]
                :
                $block_data["pre_content"],
            "description" => t("Content that will appear above the results.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "sub_content",
            "id" => "sub_content",
            "label" => t("Sub-content:"),
            "value" => $_REQUEST["sub_content"] ?
                $_REQUEST["sub_content"]
                :
                $block_data["sub_content"],
            "description" => t("Content that will appear below the results.")
        );

        $fieldset[] = array("fields" => $fields);

        if(Jaris\Modules::isInstalled("ecommerce"))
        {
            $fields_ecommerce[] = array(
                "type" => "radio",
                "name" => "treat_as_products",
                "label" => t("Treat listing as products?"),
                "value" => array(
                    t("Yes") => true,
                    t("No") => false
                ),
                "checked" => isset($_REQUEST["treat_as_products"]) ?
                    $_REQUEST["treat_as_products"]
                    :
                    $block_data["treat_as_products"],
                "description" => t("If all selected content types on the filter are products the listing is treated as a listing of products.")
            );

            $fields_ecommerce[] = array(
                "type" => "radio",
                "name" => "show_prices",
                "label" => t("Display prices?"),
                "value" => array(
                    t("Yes") => true,
                    t("No") => false
                ),
                "checked" => isset($_REQUEST["show_prices"]) ?
                    $_REQUEST["show_prices"]
                    :
                    $block_data["show_prices"],
                "description" => t("Display the product base price.")
            );

            $fields_ecommerce[] = array(
                "type" => "radio",
                "name" => "onsale_only",
                "label" => t("On sale only?"),
                "value" => array(
                    t("Yes") => true,
                    t("No") => false
                ),
                "checked" => isset($_REQUEST["onsale_only"]) ?
                    $_REQUEST["onsale_only"]
                    :
                    $block_data["onsale_only"],
                "description" => t("Display only the products that are on sale.")
            );

            $fieldset[] = array(
                "fields" => $fields_ecommerce,
                "name" => t("E-commerce"),
                "collapsible" => true,
                "collapsed" => empty($block_data["treat_as_products"]),
                "description" => t("Note: To treat the listed results as products, every content type selected on the filters section must be a valid product content type.")
            );
        }

        if(Jaris\Modules::isInstalled("realty"))
        {
            $fields_realty[] = array(
                "type" => "radio",
                "name" => "treat_as_properties",
                "label" => t("Treat listing as properties?"),
                "value" => array(
                    t("Yes") => true,
                    t("No") => false
                ),
                "checked" => isset($_REQUEST["treat_as_properties"]) ?
                    $_REQUEST["treat_as_properties"]
                    :
                    $block_data["treat_as_properties"],
                "description" => t("If all selected content types on the filter are properties the listing is treated as a listing of properties.")
            );

            $fields_realty[] = array(
                "type" => "radio",
                "name" => "realty_type",
                "label" => t("Type:"),
                "value" => array(
                    t("All") => "",
                    t("Sale") => "sale",
                    t("Rent") => "rent"
                ),
                "checked" => isset($_REQUEST["realty_type"]) ?
                    $_REQUEST["realty_type"]
                    :
                    $block_data["realty_type"],
                "description" => t("Type of properties.")
            );

            $fields_realty = array_merge(
                $fields_realty,
                countries_get_form_fields(
                    "listing-blocks-edit",
                    array(
                        "country" => $block_data["realty_country"],
                        "state_province" => $block_data["realty_state_province"],
                        "city" => $block_data["realty_city"]
                    ),
                    "realty",
                    true
                )
            );

            $fields_realty[] = array(
                "type" => "other",
                "html_code" => "<div></div>"
            );

            $categories[t("All")] = "";
            $categories += realty_get_categories();

            $fields_realty[] = array(
                "type" => "select",
                "name" => "realty_category",
                "label" => t("Category:"),
                "selected" => isset($_REQUEST["realty_category"]) ?
                    $_REQUEST["realty_category"]
                    :
                    $block_data["realty_category"],
                "value" => $categories,
                "inline" => true,
                "description" => "Property category."
            );

            $status[t("All")] = "";
            $status += realty_get_status();

            $fields_realty[] = array(
                "type" => "select",
                "name" => "realty_status",
                "label" => t("Status:"),
                "id" => "status",
                "selected" => isset($_REQUEST["realty_status"]) ?
                    $_REQUEST["realty_status"]
                    :
                    $block_data["realty_status"],
                "value" => $status,
                "inline" => true,
                "description" => t("Status of properties.")
            );

            $foreclosure[t("All")] = "";
            $foreclosure[t("Yes")] = 'y';
            $foreclosure[t("No")] = 'n';

            $fields_realty[] = array(
                "type" => "select",
                "selected" => isset($_REQUEST["realty_foreclosure"]) ?
                    $_REQUEST["realty_foreclosure"]
                    :
                    $block_data["realty_foreclosure"],
                "value" => $foreclosure,
                "name" => "realty_foreclosure",
                "label" => t("Foreclosure:"),
                "inline" => true,
                "description" => t("Property is foreclosure.")
            );

            $commercial[t("All")] = "";
            $commercial[t("Yes")] = 'y';
            $commercial[t("No")] = 'n';

            $fields_realty[] = array(
                "type" => "select",
                "selected" => isset($_REQUEST["realty_commercial"]) ?
                    $_REQUEST["realty_commercial"]
                    :
                    $block_data["realty_commercial"],
                "value" => $commercial,
                "name" => "realty_commercial",
                "label" => t("Commercial:"),
                "inline" => true,
                "description" => t("Property is commercial.")
            );

            $fieldset[] = array(
                "fields" => $fields_realty,
                "name" => t("Realty"),
                "collapsible" => true,
                "collapsed" => true,
                "description" => t("Note: To treat the listed results as properties, every content type selected on the filters section must be a valid property content type.")
            );
        }

        $criteria_types = array();
        $criteria_types_list = Jaris\Types::getList(
            Jaris\Authentication::currentUserGroup()
        );

        foreach($criteria_types_list as $machine_name => $type_fields)
        {
            $criteria_types[t(trim($type_fields["name"]))] = $machine_name;
        }

        $fields_criteria[] = array(
            "type" => "select",
            "name" => "filter_types[]",
            "id" => "filter_types",
            "label" => t("Content type:"),
            "multiple" => true,
            "selected" => $_REQUEST["filter_types"] ?
                $_REQUEST["filter_types"]
                :
                $block_data["filter_types"],
            "value" => $criteria_types
        );

        $fields_criteria[] = array(
            "type" => "textarea",
            "name" => "filter_authors",
            "id" => "filter_authors",
            "label" => t("Authors:"),
            "value" => $_REQUEST["filter_authors"] ?
                $_REQUEST["filter_authors"]
                :
                $block_data["filter_authors"],
            "description" => t("List of usernames separated by comma, for example: admin, joe, john")
        );

        $fields_criteria[] = array("type" => "other", "html_code" => "<br />");

        $fields_criteria[] = array(
            "type" => "checkbox",
            "name" => "skip_current_page",
            "id" => "skip_current_page",
            "label" => t("Skip current page?"),
            "checked" => $_REQUEST["skip_current_page"] ?
                $_REQUEST["skip_current_page"]
                :
                $block_data["skip_current_page"],
            "value" => true,
            "description" => t("The current page is skipped from the results being displayed.")
        );

        $fields_criteria[] = array(
            "type" => "checkbox",
            "name" => "related_to_current_page",
            "id" => "related_to_current_page",
            "label" => t("Related?"),
            "checked" => $_REQUEST["related_to_current_page"] ?
                $_REQUEST["related_to_current_page"]
                :
                $block_data["related_to_current_page"],
            "value" => true,
            "description" => t("The results shown are related to the page being displayed.")
        );

        $fields_criteria[] = array("type" => "other", "html_code" => "<br />");

        $fields_criteria[] = array(
            "type" => "other",
            "html_code" => "<h2>" . t("Categories:") . "</h2><hr />"
        );

        $category_matching[t("Match all")] = "match_all";
        $category_matching[t("Match partially")] = "match_partial";

        $fields_criteria[] = array(
            "type" => "radio",
            "name" => "category_matching",
            "id" => "category_matching",
            "label" => t("How you wish to match categories?"),
            "checked" => $_REQUEST["category_matching"] ?
                $_REQUEST["category_matching"]
                :
                $block_data["category_matching"],
            "value" => $category_matching,
            "description" => t("Select 'Match partially' to allow results which match some of the categories selected, otherwise select 'Match all' to force the results to have all selected categories.")
        );

        $fields_criteria = array_merge(
            $fields_criteria,
            listing_category_fields($block_data["filter_categories"], "")
        );

        $fieldset[] = array(
            "fields" => $fields_criteria,
            "name" => t("Filters"),
            "collapsible" => true,
            "collapsed" => true
        );

        $ordering[t("Newest to oldest")] = "date_desc";
        $ordering[t("Oldest to newest")] = "date_asc";
        $ordering[t("Title ascendent")] = "title_asc";
        $ordering[t("Title descendent")] = "title_desc";
        $ordering[t("Most viewed all time")] = "views_desc";
        $ordering[t("Most viewed today")] = "views_today_desc";
        $ordering[t("Most viewed this week")] = "views_week_desc";
        $ordering[t("Most viewed this month")] = "views_month_desc";
        $ordering[t("From current date descending")] = "current_date_desc";
        $ordering[t("From current date ascending")] = "current_date_asc";

        $fields_ordering[] = array(
            "type" => "radio",
            "name" => "filter_ordering",
            "value" => $ordering,
            "checked" => $_REQUEST["filter_ordering"] ?
                $_REQUEST["filter_ordering"]
                :
                $block_data["filter_ordering"]
        );

        $fieldset[] = array(
            "fields" => $fields_ordering,
            "name" => t("Ordering"),
            "collapsible" => true,
            "collapsed" => false
        );

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_title",
            "id" => "display_title",
            "label" => t("Display title?"),
            "checked" => $_REQUEST["display_title"] ?
                $_REQUEST["display_title"]
                :
                $block_data["display_title"],
            "value" => true
        );

        $fields_layout[] = array("type" => "other", "html_code" => "<br />");

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_summary",
            "id" => "display_summary",
            "label" => t("Display summary?"),
            "checked" => $_REQUEST["display_summary"] ?
                $_REQUEST["display_summary"]
                :
                $block_data["display_summary"],
            "value" => true
        );

        $fields_layout[] = array("type" => "other", "html_code" => "<br />");

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_more",
            "id" => "display_more",
            "label" => t("Display view more link?"),
            "checked" => $_REQUEST["display_more"] ?
                $_REQUEST["display_more"]
                :
                $block_data["display_more"],
            "value" => true
        );

        $fields_layout[] = array(
            "type" => "text",
            "name" => "maximum_words",
            "value" => $_REQUEST["maximum_words"] ?
                $_REQUEST["maximum_words"]
                :
                $block_data["maximum_words"],
            "label" => t("Maximum amount of words:"),
            "id" => "maximum_words",
            "required" => true,
            "description" => t("Amount of words displayed of the page summary.")
        );

        $fields_layout[] = array(
            "type" => "text",
            "name" => "results_to_show",
            "value" => $_REQUEST["results_to_show"] ?
                $_REQUEST["results_to_show"]
                :
                $block_data["results_to_show"],
            "label" => t("Results to show:"),
            "id" => "results_to_show",
            "required" => true,
            "description" => t("The amount of results to display.")
        );

        $fieldset[] = array(
            "fields" => $fields_layout,
            "name" => t("Layout"),
            "collapsible" => true,
            "collapsed" => false
        );

        $fields_thumbnail[] = array(
            "type" => "checkbox",
            "name" => "thumbnail_show",
            "id" => "thumbnail_show",
            "label" => t("Show thumbnail?"),
            "checked" => $_REQUEST["thumgnail_show"] ?
                $_REQUEST["thumbnail_show"]
                :
                $block_data["thumbnail_show"],
            "value" => true
        );

        $fields_thumbnail[] = array(
            "type" => "text",
            "name" => "thumbnail_width",
            "id" => "thumbnail_width",
            "label" => t("Width:"),
            "value" => $_REQUEST["thumbnail_width"] ?
                $_REQUEST["thumbnail_width"]
                :
                $block_data["thumbnail_width"],
            "required" => true,
            "description" => t("The width of the thumbnail in pixels.")
        );

        $fields_thumbnail[] = array(
            "type" => "text",
            "name" => "thumbnail_height",
            "id" => "thumbnail_height",
            "label" => t("Height:"),
            "value" => $_REQUEST["thumbnail_height"] ?
                $_REQUEST["thumbnail_height"]
                :
                $block_data["thumbnail_height"],
            "description" => t("The height of the thumbnail in pixels.")
        );

        $fields_thumbnail[] = array(
            "type" => "color",
            "name" => "thumbnail_bg",
            "id" => "thumbnail_bg",
            "label" => t("Background color:"),
            "value" => $_REQUEST["thumbnail_bg"] ?
                $_REQUEST["thumbnail_bg"]
                :
                $block_data["thumbnail_bg"],
            "description" => t("The background color of the thumbnail in case is neccesary.")
        );

        $fields_thumbnail[] = array("type" => "other", "html_code" => "<br />");

        $fields_thumbnail[] = array(
            "type" => "checkbox",
            "name" => "thumbnail_keep_aspectratio",
            "id" => "thumbnail_keep_aspectratio",
            "label" => t("Keep aspect ratio?"),
            "checked" => $_REQUEST["thumbnail_keep_aspectratio"] ?
                $_REQUEST["thumbnail_keep_aspectratio"]
                :
                $block_data["thumbnail_keep_aspectratio"],
            "value" => true
        );

        $fieldset[] = array(
            "fields" => $fields_thumbnail,
            "name" => t("Thumbnail"),
            "collapsible" => true,
            "collapsed" => false
        );

        $fieldset[] = array(
            "fields" => Jaris\Groups::generateFields($block_data["groups"]),
            "name" => t("Users Access"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the groups that can see the block. Don't select anything to display block to everyone.")
        );

        $fieldset[] = array(
            "fields" => Jaris\Blocks::generateThemesSelect($block_data["themes"]),
            "name" => t("Positions Per Theme"),
            "collapsed" => true,
            "collapsible" => true,
            "description" => t("Select the position where the block is going to be displayed per theme.")
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => $block_data["display_rule"],
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $block_data["pages"]
        );

        $fieldset[] = array(
            "fields" => $fields_pages,
            "name" => "Pages to display",
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        );

        if(
            Jaris\Authentication::groupHasPermission(
                "return_code_blocks",
                Jaris\Authentication::currentUserGroup()
            )
            ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_other[] = array(
                "type" => "textarea",
                "name" => "return",
                "label" => t("Return Code:"),
                "id" => "return",
                "value" => $block_data["return"],
                "description" => t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(Jaris\Authentication::isUserLogged()) print \"true\"; else print \"false\"; ?&gt;")
            );
        }

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>

    field;

    field: is_system
        1
    field;
row;
