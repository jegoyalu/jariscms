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
        <?php print t("Create Listing") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_content"));

        if(!Jaris\Authentication::hasTypeAccess("listing", Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser()))
        {
            Jaris\Authentication::protectedPage();
        }

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("add-listing"))
        {
            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];

            if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
            {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            $fields["filter_types"] = serialize($_REQUEST["filter_types"]);
            $fields["filter_authors"] = $_REQUEST["filter_authors"];
            $fields["category_matching"] = $_REQUEST["category_matching"];

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

            $fields["filter_categories"] = serialize($filter_categories);

            $fields["filter_ordering"] = $_REQUEST["filter_ordering"];
            $fields["layout"] = $_REQUEST["layout"];
            $fields["display_title"] = $_REQUEST["display_title"];
            $fields["display_summary"] = $_REQUEST["display_summary"];
            $fields["display_more"] = $_REQUEST["display_more"];
            $fields["maximum_words"] = intval($_REQUEST["maximum_words"]);
            $fields["display_navigation"] = $_REQUEST["display_navigation"];
            $fields["results_per_page"] = intval($_REQUEST["results_per_page"]);
            $fields["results_per_row"] = intval($_REQUEST["results_per_row"]);
            $fields["thumbnail_show"] = $_REQUEST["thumbnail_show"];
            $fields["thumbnail_width"] = intval($_REQUEST["thumbnail_width"]);
            $fields["thumbnail_height"] = intval($_REQUEST["thumbnail_height"]);
            $fields["thumbnail_bg"] = $_REQUEST["thumbnail_bg"];
            $fields["thumbnail_keep_aspectratio"] = $_REQUEST["thumbnail_keep_aspectratio"];

            if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
            {
                $fields["groups"] = $_REQUEST["groups"];

                $users = explode(",", $_REQUEST["users"]);

                if(count($users) > 0)
                {
                    foreach($users as $user_position=>$username)
                    {
                        $users[$user_position] = trim($username);
                    }
                }

                $fields["users"] = $users;
            }
            else
            {
                $fields["groups"] = array();
                $fields["user"] = array();
            }

            $categories = array();

            $categories_list = Jaris\Categories::getList("listing");

            if($categories_list)
            {
                foreach($categories_list as $machine_name => $values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }

            $fields["categories"] = $categories;

            if(Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) || Jaris\Authentication::isAdminLogged())
            {
                $fields["input_format"] = $_REQUEST["input_format"];
            }
            else
            {
                $fields["input_format"] = Jaris\Types::getDefaultInputFormat("listing");
            }

            $fields["created_date"] = time();
            $fields["author"] = Jaris\Authentication::currentUser();
            $fields["type"] = "listing";

            Jaris\Fields::appendFields($fields["type"], $fields);

            //Stores the uri of the page to display the edit page after saving.
            $uri = "";

            if(
                !Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()) ||
                $_REQUEST["uri"] == ""
            )
            {
                $_REQUEST["uri"] = Jaris\Types::generateURI(
                    $fields["type"],
                    $fields["title"],
                    $fields["author"]
                );
            }

            if(Jaris\Pages::add($_REQUEST["uri"], $fields, $uri))
            {
                Jaris\View::addMessage(t("The listing was successfully created."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/listing/edit", "listing"),
                array("uri" => $uri)
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        $parameters["name"] = "add-listing";
        $parameters["class"] = "add-listing";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/listing/add", "listing")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList("listing");

        if($categories)
        {
            $fields_categories = Jaris\Categories::generateFields(
                null,
                null,
                "listing"
            );

            $fieldset[] = array(
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            );
        }

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "value" => $_REQUEST["title"],
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => $_REQUEST["content"],
            "label" => t("Content:"),
            "id" => "content"
        );

        $fieldset[] = array("fields" => $fields);

        $criteria_types = array();
        $criteria_types_list = Jaris\Types::getList(Jaris\Authentication::currentUserGroup());
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
            "selected" => $_REQUEST["filter_types"],
            "value" => $criteria_types
        );

        $fields_criteria[] = array(
            "type" => "textarea",
            "name" => "filter_authors",
            "id" => "filter_authors",
            "label" => t("Authors:"),
            "value" => $_REQUEST["filter_authors"],
            "description" => t("List of usernames separated by comma, for example: admin, joe, john")
        );

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
            "checked" => $_REQUEST["category_matching"],
            "value" => $category_matching,
            "description" => t("Select 'Match partially' to allow results which match some of the categories selected, otherwise select 'Match all' to force the results to have all selected categories.")
        );

        $fields_criteria = array_merge(
            $fields_criteria,
            listing_category_fields(null, null)
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

        $fields_ordering[] = array(
            "type" => "radio",
            "name" => "filter_ordering",
            "value" => $ordering,
            "checked" => $_REQUEST["filter_ordering"] ?
                $_REQUEST["filter_ordering"]
                :
                "date_desc"
        );

        $fieldset[] = array(
            "fields" => $fields_ordering,
            "name" => t("Ordering"),
            "collapsible" => true,
            "collapsed" => false
        );

        $teaser_checked = $_REQUEST["layout"] == "teaser" || !isset($_REQUEST["layout"]) ? "checked" : "";
        $grid_checked = $_REQUEST["layout"] == "grid" ? "checked" : "";
        $list_checked = $_REQUEST["layout"] == "list" ? "checked" : "";

        $layout = "<table>";
        $layout .= "<thead>";
        $layout .= "<tr>";
        $layout .= "<td></td>";
        $layout .= "<td>" . t("Teaser") . "</td>";
        $layout .= "<td></td>";
        $layout .= "<td>" . t("Grid") . "</td>";
        $layout .= "<td></td>";
        $layout .= "<td>" . t("List") . "</td>";
        $layout .= "</tr>";
        $layout .= "</thead>";
        $layout .= "<tr>";
        $layout .= "<td><input $teaser_checked type=\"radio\" name=\"layout\" value=\"teaser\" /></td>";
        $layout .= "<td><img src=\"" . Jaris\Uri::url(Jaris\Modules::directory("listing") . "images/listing-teaser.png") . "\" /></td>";
        $layout .= "<td><input $grid_checked type=\"radio\" name=\"layout\" value=\"grid\" /></td>";
        $layout .= "<td><img src=\"" . Jaris\Uri::url(Jaris\Modules::directory("listing") . "images/listing-grid.png") . "\" /></td>";
        $layout .= "<td><input $list_checked type=\"radio\" name=\"layout\" value=\"list\" /></td>";
        $layout .= "<td><img src=\"" . Jaris\Uri::url(Jaris\Modules::directory("listing") . "images/listing-list.png") . "\" /></td>";
        $layout .= "</tr>";
        $layout .= "</table><hr />";

        $fields_layout[] = array("type" => "other", "html_code" => $layout);

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_title",
            "id" => "display_title",
            "label" => t("Display title?"),
            "checked" => $_REQUEST["display_title"],
            "value" => true
        );

        $fields_layout[] = array("type" => "other", "html_code" => "<br />");

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_summary",
            "id" => "display_summary",
            "label" => t("Display summary?"),
            "checked" => $_REQUEST["display_summary"],
            "value" => true
        );

        $fields_layout[] = array("type" => "other", "html_code" => "<br />");

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_more",
            "id" => "display_more",
            "label" => t("Display view more link?"),
            "checked" => $_REQUEST["display_more"],
            "value" => true
        );

        $fields_layout[] = array(
            "type" => "text",
            "name" => "maximum_words",
            "value" => $_REQUEST["maximum_words"] ?
                $_REQUEST["maximum_words"]
                :
                20,
            "label" => t("Maximum amount of words:"),
            "id" => "maximum_words",
            "required" => true,
            "description" => t("Amount of words displayed of the page summary.")
        );

        $fields_layout[] = array("type" => "other", "html_code" => "<br />");

        $fields_layout[] = array(
            "type" => "checkbox",
            "name" => "display_navigation",
            "id" => "display_navigation",
            "label" => t("Display navigation?"),
            "checked" => $_REQUEST["display_navigation"],
            "value" => true
        );

        $fields_layout[] = array(
            "type" => "text",
            "name" => "results_per_page",
            "value" => $_REQUEST["results_per_page"] ?
                $_REQUEST["results_per_page"]
                :
                12,
            "label" => t("Results per page:"),
            "id" => "results_per_page",
            "required" => true,
            "description" => t("The amount of results to display in case the navigation is enabled.")
        );

        $fields_layout[] = array(
            "type" => "text",
            "name" => "results_per_row",
            "value" => $_REQUEST["results_per_row"] ?
                $_REQUEST["results_per_row"]
                :
                3,
            "label" => t("Results per row:"),
            "id" => "results_per_row",
            "required" => true,
            "description" => t("The amount of columns per row in case grid was select as layout.")
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
            "checked" => $_REQUEST["thumgnail_show"],
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
                "200",
            "required" => true,
            "description" => t("The width of the thumbnail in pixels.")
        );

        $fields_thumbnail[] = array(
            "type" => "text",
            "name" => "thumbnail_height",
            "id" => "thumbnail_height",
            "label" => t("Height:"),
            "value" => $_REQUEST["thumbnail_height"],
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
                "FFFFFF",
            "description" => t("The background color of the thumbnail in case is neccesary.")
        );

        $fields_thumbnail[] = array("type" => "other", "html_code" => "<br />");

        $fields_thumbnail[] = array(
            "type" => "checkbox",
            "name" => "thumbnail_keep_aspectratio",
            "id" => "thumbnail_keep_aspectratio",
            "label" => t("Keep aspect ratio?"),
            "checked" => $_REQUEST["thumbnail_keep_aspectratio"],
            "value" => true
        );

        $fieldset[] = array(
            "fields" => $fields_thumbnail,
            "name" => t("Thumbnail"),
            "collapsible" => true,
            "collapsed" => false
        );

        if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "meta_title",
                "value" => $_REQUEST["meta_title"],
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "description",
                "value" => $_REQUEST["description"],
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "keywords",
                "value" => $_REQUEST["keywords"],
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            );

            $fieldset[] = array(
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            );
        }

        if(
            Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {
                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == "full_html" ? true : false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => array($fields_formats["title"] => $machine_name)
                );
            }

            $fieldset[] = array(
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            );
        }

        $extra_fields = Jaris\Fields::generateFields("listing");

        if($extra_fields)
        {
            $fieldset[] = array("fields" => $extra_fields);
        }

        if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
        {
            $fields_users_access[] = array(
                "type" => "other",
                "html_code" => "<h4>"
                    . t("Select the groups that can see this content. Don't select anything to display content to everyone.")
                    . "</h4>"
            );

            $fields_users_access = array_merge(
                $fields_users_access,
                Jaris\Groups::generateFields()
            );

            $fields_users_access[] = array(
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => $_REQUEST["users"],
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            );

            $fieldset[] = array(
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            );
        }

        if(Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_other[] = array(
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => $_REQUEST["uri"],
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
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