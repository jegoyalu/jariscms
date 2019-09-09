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
        <?php print t("Contact Form Message Archive") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_content"]);

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        $page_data = Jaris\Pages::get($_REQUEST["uri"]);

        //Tabs
        if (Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        if (Jaris\Authentication::groupHasPermission("view_content_blocks", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_images", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_files", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("translate_languages", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if ($page_data["message_archive"]) {
            Jaris\View::addTab(
                t("Messages Archive"),
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                $arguments
            );
        }
        if (Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = intval($_REQUEST["page"]);
        }

        $uri_query = $_REQUEST["uri"];
        Jaris\Sql::escapeVar($uri_query);

        $uri = "uri='".$uri_query."' ";

        $month = "";
        if (!empty($_REQUEST["month"])) {
            $month .= "month=" . intval($_REQUEST["month"]);
        }

        $year = "";
        if (!empty($_REQUEST["year"])) {
            $year .= "year=" . intval($_REQUEST["year"]);
        }

        $sorting = "order by created_date desc";
        if (!empty($_REQUEST["sort"])) {
            switch ($_REQUEST["sort"]) {
                case "da":
                    $sorting = "order by created_date asc";
                    break;
                default:
                    $sorting = "order by created_date desc";
            }
        }

        $where = "where $uri";

        if ($month) {
            $where .= "and $month ";
        }

        if ($year) {
            $where .= "and $year ";
        }

        if ($sorting) {
            $where .= "$sorting ";
        }

        $pages_count = Jaris\Sql::countColumn(
            "contact_archive",
            "contact_archive",
            "id",
            $where
        );

        print "<div>";
        print "<h2>"
            . t("Total:") . " " . $pages_count
            . "</h2>"
        ;
        print "</div>";

        $list = Jaris\Sql::getDataList(
            "contact_archive",
            "contact_archive",
            $page - 1,
            20,
            $where
        );

        $parameters["class"] = "filter-by-contact-archive";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "get";

        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "value" => array_merge(
                [t("All") => ""],
                Jaris\Date::getMonths()
            ),
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "value" => [t("All") => ""] + Jaris\Date::getYears(),
            "selected" => isset($_REQUEST["year"]) ?
                $_REQUEST["year"]
                :
                "",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fields[] = [
            "type" => "select",
            "name" => "sort",
            "label" => t("Sort by:"),
            "value" => [
                t("Date Descending") => "da",
                t("Date Ascending") => "dd"
            ],
            "selected" => isset($_REQUEST["sort"]) ?
                $_REQUEST["sort"]
                :
                "da",
            "code" => 'onchange="javascript: this.form.submit()"',
            "inline" => true
        ];

        $fieldset[] = [
            "name" => t("Filter Results"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => !isset($_REQUEST["month"])
                && !isset($_REQUEST["year"])
                && !isset($_REQUEST["sort"])
        ];

        print Jaris\Forms::generate($parameters, $fieldset);

        if (count($list) > 0) {
            Jaris\System::printNavigation(
                $pages_count,
                $page,
                "admin/pages/contact-form/archive",
                "contact",
                20,
                [
                    "uri" => $_REQUEST["uri"],
                    "month" => $_REQUEST["month"],
                    "year" => $_REQUEST["year"],
                    "sort" => $_REQUEST["sort"]
                ]
            );

            $months_list = Jaris\Date::getMonths();
            $months_list = array_flip($months_list);

            print "<table class=\"navigation-list navigation-list-hover\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Date") . "</td>";
            print "<td>" . t("From") . "</td>";
            print "<td>" . t("Operation") . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";
            foreach ($list as $list_entry) {
                print "<tr>";

                $edit_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/contact-form/archive/edit",
                        "contact"
                    ),
                    [
                        "id" => $list_entry["id"],
                        "uri" => $_REQUEST["uri"]
                    ]
                );

                print "<td>"
                    . "<a href=\"$edit_url\">"
                    . $list_entry["day"]
                    . "/"
                    . $months_list[$list_entry["month"]]
                    . "/"
                    . $list_entry["year"]
                    . "</a> "
                    . "</td>"
                ;

                $from = unserialize($list_entry["from_info"]);
                $from_name = "";
                $from_email = "";

                foreach ($from as $index=>$value) {
                    $from_name = $index;
                    $from_email = $value;
                }

                print "<td>"
                    . "<a href=\"$edit_url\">"
                    . (
                        !empty($from_name) ?
                            $from_name . " " . "&lt;" . $from_email . "&gt;"
                            :
                            t("N/A")
                    )
                    . "</a> "
                    . "</td>"
                ;

                $delete_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/contact-form/archive/delete",
                        "contact"
                    ),
                    [
                        "id" => $list_entry["id"],
                        "uri" => $_REQUEST["uri"]
                    ]
                );

                print "<td>"
                    . "<a href=\"$delete_url\">" . t("Delete") . "</a>"
                    . "</td>"
                ;

                print "</tr>";
            }
            print "</tbody>";

            print "</table>";


            Jaris\System::printNavigation(
                $pages_count,
                $page,
                "admin/pages/contact-form/archive",
                "contact",
                20,
                [
                    "uri" => $_REQUEST["uri"],
                    "month" => $_REQUEST["month"],
                    "year" => $_REQUEST["year"],
                    "sort" => $_REQUEST["sort"]
                ]
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
