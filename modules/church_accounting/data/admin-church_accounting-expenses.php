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
        <?php print t("Church Accounting Expenses") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_expenses_church_accounting"]);
    ?>

    <script>
    $(document).ready(function(){
        $("a.category-more").click(function(){
            $(this).next().slideToggle();
        });
    });
    </script>

    <style>
    a.category-more
    {
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
    }

    table.ca-expenses td > table
    {
        display: none;
    }
    </style>

    <?php
        Jaris\View::addTab(
        t("Add Expense"),
        Jaris\Modules::getPageUri(
                "admin/church-accounting/expenses/add",
                "church_accounting"
            )
        );

        Jaris\View::addTab(
            t("Income/Expenses Report"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/income/report",
                "church_accounting"
            )
        );

        Jaris\View::addTab(
            t("Transactions Report"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/transactions/report",
                "church_accounting"
            )
        );


        $options = [];

        $categories = church_accounting_category_list(
            ChurchAccountingCategory::EXPENSE
        );

        if (trim($_REQUEST["cat"]) != "") {
            $category = intval($_REQUEST["cat"]);
            $options[] = "category=$category";
        }

        if (trim($_REQUEST["month"]) != "") {
            $month = intval($_REQUEST["month"]);
            $options[] = "month=$month";
        }

        if (trim($_REQUEST["year"]) != "") {
            $year = intval($_REQUEST["year"]);
            $options[] = "year=$year";
        }

        $sorting_array = [
            t("Date Descending") => "date_desc",
            t("Date Ascending") => "date_asc"
        ];

        $sorting = "";
        if (trim($_REQUEST["sorting"]) != "") {
            switch (trim($_REQUEST["sorting"])) {
                case "date_asc":
                    $sorting = 'order by created_date asc';
                    break;
                default:
                    $sorting = 'order by created_date desc';
            }
        } else {
            $sorting = 'order by created_date desc';
        }

        $where = "";
        if (count($options) > 0) {
            $where = "where "
                . implode(" and ", $options)
            ;
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        print "<form class=\"filter-results\" method=\"get\" action=\""
            . Jaris\Uri::url(Jaris\Uri::get())
            . "\" style=\"display: block; width: 100%;\">\n"
        ;
        print t("Category:") . " <select onchange=\"javascript: this.form.submit()\" name=\"cat\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach ($categories as $category_id=>$category_name) {
            $selected = "";

            $category_name = t($category_name);

            if ($_REQUEST["cat"] == $category_id) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$category_id\">$category_name</option>\n";
        }
        print "</select>\n";

        print t("Month:") . " <select onchange=\"javascript: this.form.submit()\" name=\"month\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach (Jaris\Date::getMonths() as $month_name=>$month_value) {
            $selected = "";

            if ($_REQUEST["month"] == $month_value) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$month_value\">$month_name</option>\n";
        }
        print "</select>\n";

        print t("Year:") . " <select onchange=\"javascript: this.form.submit()\" name=\"year\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach (Jaris\Date::getYears() as $year) {
            $selected = "";

            if ($_REQUEST["year"] == $year) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$year\">$year</option>\n";
        }
        print "</select>\n";
        print "</div>";

        print "<div style=\"float: right; margin-left: 10px;\">";
        print t("Sort by:") . " <select onchange=\"javascript: this.form.submit()\" name=\"sorting\">\n";
        foreach ($sorting_array as $label => $value) {
            $selected = "";

            if ($_REQUEST["sorting"] == $value) {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$value\">$label</option>\n";
        }
        print "</select>\n";
        print "</div>";
        print "</form>\n";

        print "<div style=\"clear: both\"></div>";

        print "<hr />";

        $pages_count = Jaris\Sql::countColumn(
            "church_accounting_expenses",
            "church_accounting_expenses",
            "id",
            $where
        );

        print "<div>";
        print "<h2>" . t("Total:") . " " . $pages_count . "</h2>";
        print "</div>";

        $expenses = Jaris\Sql::getDataList(
            "church_accounting_expenses",
            "church_accounting_expenses",
            $page - 1,
            20,
            "$where $sorting"
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-accounting/expenses",
            "church_accounting",
            20,
            [
                "cat" => $_REQUEST["cat"],
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "sorting" => $_REQUEST["sorting"]
            ]
        );

        $months = array_flip(Jaris\Date::getMonths());

        print "<table class=\"navigation-list navigation-list-hover ca-expenses\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Category") . "</td>";
        print "<td>" . t("Total") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach ($expenses as $expense_data) {
            print "<tr>";

            print "<td>"
                . date("j-", $expense_data["created_date"])
                . $months[$expense_data["month"]]
                . date("-Y", $expense_data["created_date"])
                . "</td>"
            ;

            $items = "";
            if ($expense_data["category"] == "9") {
                $expense_data["items_data"] = unserialize(
                    $expense_data["items_data"]
                );

                if (count($expense_data["items_data"]) > 0) {
                    $items .= ' <a class="category-more">&raquo;</a>';
                    $items .= "<table style=\"margin-top: 7px;\">";
                    $items .= "<tbody>";
                    foreach ($expense_data["items_data"] as $item) {
                        $items .= "<tr>";
                        $items .= "<td style=\"font-size: 13px; text-align: left; padding-left: 0; padding-right: 0;\">"
                            . $item["description"]
                            . "</td>";
                        $items .= "</tr>";
                    }
                    $items .= "</tbody>";
                    $items .= "</table>";
                }
            }

            print "<td>"
                . t(church_accounting_category_label(
                    $expense_data["category"],
                    ChurchAccountingCategory::EXPENSE
                ))
                . $items
                . "</td>"
            ;

            print "<td>$"
                . number_format($expense_data["total"], 2, ".", ",")
                . "</td>"
            ;


            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses/edit",
                    "church_accounting"
                ),
                ["id"=>$expense_data["id"]]
            );

            $delete_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses/delete",
                    "church_accounting"
                ),
                ["id"=>$expense_data["id"]]
            );

            print "<td>"
                . "<a href=\"$edit_url\">" . t("Edit") . "</a> "
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
            "admin/church-accounting/expenses",
            "church_accounting",
            20,
            [
                "cat" => $_REQUEST["cat"],
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "sorting" => $_REQUEST["sorting"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
