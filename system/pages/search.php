<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that contains the search page.
 */
exit;
?>

row: 0
    field: title
    <?php
        if(Jaris\Settings::get("search_display_category_titles", "main"))
        {
            $categories_title = "";

            $categories = Jaris\Categories::getList();

            if($categories)
            {
                foreach($categories as $machine_name => $category_data)
                {
                    if(
                        isset($_REQUEST[$machine_name]) &&
                        $_REQUEST[$machine_name][0] != "-1"
                    )
                    {
                        $categories_title .= t($category_data["name"]);
                        $categories_title .= " > ";

                        if(
                            count($_REQUEST[$machine_name]) ==
                            count(Jaris\Categories::getSubcategories($machine_name))
                        )
                        {
                            $categories_title .= t("All");
                        }
                        elseif(count($_REQUEST[$machine_name]) > 1)
                        {
                            $subcategory_titles = "";
                            foreach($_REQUEST[$machine_name] as $subcategory_id)
                            {
                                $subcategory_data = Jaris\Categories::getSubcategory(
                                    $machine_name, $subcategory_id
                                );

                                $subcategory_titles .= t($subcategory_data["title"]) . " | ";
                            }

                            $categories_title .= trim($subcategory_titles, "| ");
                        }
                        else
                        {
                            $subcategory_data = Jaris\Categories::getSubcategory(
                                $machine_name, $_REQUEST[$machine_name][0]
                            );

                            $categories_title .= t($subcategory_data["title"]);
                        }

                        $categories_title .= ", ";
                    }
                }
            }

            if($categories_title != "")
            {
                print trim($categories_title, ", ");
            }
            else
            {
                print t("Search");
            }
        }
        else
        {
            print t("Search");
        }
    ?>
    field;

    field: content
    <?php
        //Variables that hold settings to display search preview images.
        $search_settings = Jaris\Settings::getAll("main");
        $search_types = !empty($search_settings["search_images_types"]) ?
            unserialize($search_settings["search_images_types"])
            :
            null
        ;
        $type_display_image = array();

        if(is_array($search_types))
        {
            foreach($search_types as $type)
            {
                $type_display_image[$type] = 1;
            }
        }

        // To protect agains sql injections be sure $page is a int
        if(empty($_REQUEST["page"]))
        {
            $_REQUEST["page"] = 1;
        }
        else
        {
            $_REQUEST["page"] = intval($_REQUEST["page"]);
        }

        //Delete search results from session variable if user clicks reset
        if(isset($_REQUEST["btnReset"]))
        {
            unset($_REQUEST["keywords"]);
            Jaris\Search::reset();
        }

        if(empty($_REQUEST["keywords"]))
        {
            $_REQUEST["keywords"] = "";
        }

        if(empty($_REQUEST["type"]))
        {
            $_REQUEST["type"] = "";
        }

        $parameters["action"] = Jaris\Uri::url("search");
        $parameters["name"] = "search";
        $parameters["id"] = "search-engine";
        $parameters["method"] = "get";

        $fields_head[] = array(
            "type" => "other",
            "html_code" => "<fieldset style=\"margin-bottom: 5px;\" class=\"collapsible collapsed\">"
            . "<legend><a class=\"expand\" href=\"javascript:void(0)\">" .
            t("Search Options") .
            "</a></legend>"
        );

        $fieldset[] = array(
            "fields" => $fields_head,
            "collapsible" => false,
            "collapsed" => false
        );

        $fields[] = array("type" => "hidden", "name" => "search", "value" => 1);

        $categories = Jaris\Categories::getList($_REQUEST["type"]);

        $selected_categories = array();

        if($categories)
        {
            foreach($categories as $machine_name => $values)
            {
                if(isset($_REQUEST[$machine_name]))
                {
                    $selected_categories[$machine_name] = $_REQUEST[$machine_name];
                }
            }
        }

        if(count($selected_categories) > 0)
        {
            $fields[] = array(
                "type" => "other",
                "html_code" => "<fieldset style=\"margin-bottom: 5px;\" class=\"collapsible collapsed\">"
            );

            $fields[] = array(
                "type" => "other",
                "html_code" => "<legend><a class=\"expand\" href=\"javascript:void(0)\">" .
                    t("Sorting") .
                    "</a></legend>"
            );

            $ordering_options[t("Title ascending")] = "title_asc";
            $ordering_options[t("Title descending")] = "title_desc";
            $ordering_options[t("Newest first")] = "newest";
            $ordering_options[t("Oldest first")] = "oldest";

            $fields[] = array(
                "type" => "select",
                "code" => "onchange=\"this.form.submit()\"",
                "name" => "order",
                "label" => t("Sort by:"),
                "id" => "order",
                "value" => $ordering_options,
                "selected" => $_REQUEST["order"]
            );

            $fields[] = array(
                "type" => "other",
                "html_code" => "</fieldset>\n"
            );
        }

        if(count($categories) > 0)
        {
            $fields_categories = Jaris\Categories::generateFields(
                $selected_categories, null, $_REQUEST["type"]
            );

            foreach($fields_categories as $field_index=>$field_category)
            {
                $fields_categories[$field_index]["inline"] = true;
            }

            $fieldset[] = array(
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true,
                "collapsed" => false
            );
        }

        $types[t("-All-")] = "";
        $types_array = Jaris\Types::getList();
        foreach($types_array as $machine_name => $type_fields)
        {
            $types[t(trim($type_fields["name"]))] = $machine_name;
        }

        $fields_type[] = array(
            "type" => "select",
            "code" => "onchange=\"this.form.submit()\"",
            "selected" => $_REQUEST["type"],
            "name" => "type",
            "id" => "type",
            "value" => $types
        );

        $fieldset[] = array(
            "fields" => $fields_type,
            "name" => t("Content type"),
            "collapsible" => true,
            "collapsed" => false,
            "description" => t("The type of content you are searching.")
        );


        $fields[] = array(
            "type" => "other",
            "html_code" => "</fieldset>\n"
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "<div style=\"clear: both\"></div>"
        );

        $fields[] = array(
            "type" => "text",
            "code" => "style=\"margin: 5px 0 5px 0; width: 400px; float: left\"",
            "name" => "keywords",
            "label" => t("Search text:"),
            "id" => "search",
            "value" => $_REQUEST["keywords"]
        );

        $fields[] = array(
            "type" => "submit",
            "code" => "style=\"float: left; margin: 4px 0 0 5px;\"",
            "value" => t("Search")
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "<div style=\"clear: both\"></div>"
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);

        //The amount of results to display per page
        $results_per_page = isset($_REQUEST["results_count"]) &&
            intval($_REQUEST["results_count"]) <= 50 ?
            $_REQUEST["results_count"] : 10
        ;

        $results = array();
        if(isset($_REQUEST["search"]))
        {
            if(trim($_REQUEST["keywords"]) != "")
            {
                Jaris\Search::start(
                    $_REQUEST["keywords"],
                    null,
                    $selected_categories,
                    1,
                    $results_per_page
                );
            }
            else if(count($categories) > 0)
            {
                Jaris\Search::start(
                    "",
                    null,
                    $selected_categories,
                    1,
                    $results_per_page
                );
            }

            $results = Jaris\Search::getResults(1, $results_per_page);
        }
        elseif(
            (isset($_REQUEST["page"]) && trim($_REQUEST["keywords"]) != "") ||
            (isset($_REQUEST["page"]) && count($selected_categories) > 0)
        )
        {
            $results = Jaris\Search::getResults($_REQUEST["page"], $results_per_page);

            //In case a search engine indexed a search page we research to be
            //able to show data since all search results are stored on
            //session variable
            if(count($results) <= 0)
            {
                if(trim($_REQUEST["keywords"]) != "")
                {
                    Jaris\Search::start(
                        $_REQUEST["keywords"],
                        null,
                        $selected_categories,
                        $_REQUEST["page"],
                        $results_per_page
                    );
                }
                else if(count($categories) > 0)
                {
                    Jaris\Search::start(
                        "",
                        null,
                        $selected_categories,
                        $_REQUEST["page"],
                        $results_per_page
                    );
                }

                $results = Jaris\Search::getResults(
                    $_REQUEST["page"],
                    $results_per_page
                );
            }
        }

        print "<h2 class=\"search-results-title\">" . t("Results") . "</h2>\n";

        //Print header template if available or default
        if($header_template = Jaris\View::searchTemplate(Jaris\Uri::get(), $_REQUEST["type"], "header"))
        {
            ob_start();
            include($header_template);
            $html = ob_get_contents();
            ob_end_clean();

            print $html;
        }
        else
        {
            print "<div class=\"search-results\">\n";
        }

        foreach($results as $fields)
        {
            $url = Jaris\Uri::url($fields["uri"]);

            //Display content preview image
            $image = "";
            if(
                $search_settings["search_display_images"] &&
                isset($type_display_image[$fields["type"]])
            )
            {
                $images = Jaris\Pages\Images::getList($fields["uri"]);

                $type_image = Jaris\Types::getImageUrl(
                    $fields["type"],
                    $search_settings["search_images_width"] ? $search_settings["search_images_width"] : 60,
                    $search_settings["search_images_height"] ? $search_settings["search_images_height"] : null,
                    $search_settings["search_images_aspect_ratio"] ? $search_settings["search_images_aspect_ratio"] : null,
                    $search_settings["search_images_background_color"] ? $search_settings["search_images_background_color"] : null
                );

                $image = "";
                if(count($images) > 0)
                {
                    foreach($images as $id => $image_fields)
                    {
                        $image = "<a title=\"{$image_fields['description']}\" " .
                            "style=\"float: left; padding-right: 4px; " .
                            "padding-bottom: 4px;\" href=\"$url\">" .
                            "<img alt=\"{$image_fields['description']}\" " .
                            "src=\"" . Jaris\Uri::url(
                                "image/" . $fields["uri"] . "/{$image_fields['name']}",
                                array(
                                    "w" => $search_settings["search_images_width"] ? $search_settings["search_images_width"] : 60,
                                    "h" => $search_settings["search_images_height"] ? $search_settings["search_images_height"] : "",
                                    "ar" => $search_settings["search_images_aspect_ratio"] ? $search_settings["search_images_aspect_ratio"] : "",
                                    "bg" => $search_settings["search_images_background_color"] ? $search_settings["search_images_background_color"] : ""
                                )) .
                            "\" />" .
                            "</a>"
                        ;

                        break;
                    }
                }
                elseif($type_image != "")
                {
                    $image = "<a title=\"{$image_fields['description']}\" "
                        . "style=\"float: left; padding-right: 4px; "
                        . "padding-bottom: 4px;\" href=\"$url\">"
                        . "<img alt=\"{$image_fields['description']}\" "
                        . "src=\"" . $type_image . "\" />"
                        . "</a>"
                    ;
                }
            }

            $title = Jaris\Search::highlightResults($fields["title"]);

            $content = Jaris\Search::highlightResults(
                $fields["content"],
                $fields["input_format"],
                "content"
            );

            //Print result template if available or default
            if($result_template = Jaris\View::searchTemplate(Jaris\Uri::get(), $_REQUEST["type"]))
            {
                ob_start();
                include($result_template);
                $html = ob_get_contents();
                ob_end_clean();

                print $html;
            }
            else
            {
                print "<div class=\"title\">\n";
                print "<li><a href=\"$url\">$title</a></li>\n";
                print "</div>\n";

                print "<div class=\"text\">\n";
                print "$image ";

                foreach(Jaris\Search::getTypeFields($fields["type"]) as $label => $fields_name)
                {
                    if($fields_name == "content")
                    {
                        if(is_numeric($label))
                        {
                            print "<div>" . $content . "</div>";
                        }
                        else
                        {
                            print "<span class=\"label\">" .
                                $label .
                                "</span> "
                            ;

                            print "<span class=\"value\">" .
                                $content .
                                "</span> "
                            ;
                        }
                    }
                    else if(is_numeric($label))
                    {
                        print "<span class=\"value\">" .
                            $fields[$fields_name] .
                            "</span> "
                        ;
                    }
                    else
                    {
                        print "<span class=\"label\">" .
                            $label .
                            "</span> "
                        ;

                        print "<span class=\"value\">" .
                            $fields[$fields_name] .
                            "</span> "
                        ;
                    }
                }

                print "<div style=\"clear: both\"></div>";
                print "</div>\n";
            }
        }

        //Print footer template if available or default
        if(
            $footer_template =
            Jaris\View::searchTemplate(
                Jaris\Uri::get(),
                $_REQUEST["type"],
                "footer"
            )
        )
        {
            ob_start();
            include($footer_template);
            $html = ob_get_contents();
            ob_end_clean();

            print $html;
        }
        else
        {
            print "</div>\n";
        }

        //Print page navigation menu
        print "<div class=\"search-results\">\n";

        if(isset($_REQUEST["search"]) && $results)
        {
            Jaris\Search::printNavigation(1, $results_per_page);
        }
        elseif(isset($_REQUEST["page"]) && $results)
        {
            Jaris\Search::printNavigation($_REQUEST["page"], $results_per_page);
        }

        print "</div>\n";

        //If nothing was found
        if(isset($_REQUEST["search"]) && !$results)
        {
            if(
                isset($search_settings["search_results_not_found"])
                &&
                trim($search_settings["search_results_not_found"]) != ""
            )
            {
                print Jaris\System::evalPHP($search_settings["search_results_not_found"]);
            }
            else
            {
                print t("Nothing was found.");
            }
        }
        ?>
    field;

    field: is_system
        1
    field;
row;
