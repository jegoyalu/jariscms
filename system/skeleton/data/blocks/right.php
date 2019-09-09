<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the right blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: description
        site search
    field;

    field: title
        Search
    field;

    field: content
    <?php
        $parameters["class"] = "block-search";
        $parameters["action"] = Jaris\Uri::url("search");
        $parameters["method"] = "get";

        $fields[] = [
            "type" => "hidden",
            "name" => "search",
            "value" => 1
        ];

        $fields[] = [
            "type" => "text",
            "name" => "keywords",
            "id" => "search",
            "value" => empty($_REQUEST["keywords"]) ?
                "" : $_REQUEST["keywords"]
        ];

        $fields[] = [
            "type" => "submit",
            "value" => t("Search")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: order
        0
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        if (Jaris\Uri::get() == "search") {
            print "false";
        } else {
            print "true";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
