<html>
<head>
<title><?php print t("Income and Expenses Report") ?></title>

<style>
    .report-table
    {
        width: 100%;
    }

    .report-table td
    {
        width: 50% !important;
        padding: 7px;
    }

    .report-table td:first-child
    {
        border-right: solid 1px #d3d3d3;
    }

    .report-table td:last-child
    {
        text-align: right;
    }

    .report-table thead td
    {
        font-weight: bold;
        background-color: #000;
        color: #fff;
    }

    .report-table td
    {
        border-bottom: solid 1px #d3d3d3;
    }

    p.total, p.total strong{
        text-align: right;
        font-size: 20px;
    }

    p.total-net, p.total-net strong{
        text-align: center;
        font-size: 20px;
    }

</style>
</head>

<body>

<?php

print "<h1>" . t("Income and Expenses Report") . "</h1>";

$months = array_flip(Jaris\Date::getMonths());

if($_REQUEST["month"])
{
    print "<strong>";
    print t("Month:");
    print "</strong> ";

    print $months[$_REQUEST["month"]];
}

if($_REQUEST["year"])
{
    print " <strong>";
    print t("Year:");
    print "</strong> ";

    print $_REQUEST["year"];
}

if(!isset($_REQUEST["month"]) && !isset($_REQUEST["year"]))
{
    print "<strong>";
    print t("All time report");
    print "</strong> ";
}

if(true)
{
    print "<hr />";

    $categories = church_accounting_category_list();

    $db = Jaris\Sql::open("church_accounting_income");

    $income_total = 0.00;

    print "<h2>" . t("Income") . "</h2>";

    print "<table class=\"report-table\">";
    print "<thead>";
    print "<tr>";
    print "<td>" . t("Category") . "</td>";
    print "<td>" . t("Amount") . "</td>";
    print "</tr>";
    print "</thead>";

    print "<tbody>";
    $results_data = array();
    foreach($categories as $cat_id=>$cat_name)
    {
        $result = Jaris\Sql::query(
            "select sum(total) as grand_total "
            . "from church_accounting_income "
            . "where category=$cat_id $where",
            $db
        );

        $data = Jaris\Sql::fetchArray($result);

        $results_data[$cat_name] = $data["grand_total"];

        $income_total += $data["grand_total"];
    }

    arsort($results_data);

    foreach($results_data as $cat_name=>$grand_total)
    {
        print "<tr>";

        print "<td>" . t($cat_name) . "</td>";

        print "<td>$" . number_format($grand_total, 2, ".", ",") . "</td>";

        print "</tr>";
    }
    print "</tbody>";

    print "</table>";

    print "<p class=\"total\">"
        . "<strong>" . t("Total:") . "</strong> "
        . "\$" . number_format($income_total, 2, ".", ",")
        . "</p>"
    ;

    Jaris\Sql::close($db);

    print "<hr />";

    $categories = church_accounting_category_list(ChurchAccountingCategory::EXPENSE);

    $db = Jaris\Sql::open("church_accounting_expenses");

    $expenses_total = 0.00;

    print "<h2>" . t("Expenses") . "</h2>";

    print "<table class=\"report-table\">";
    print "<thead>";
    print "<tr>";
    print "<td>" . t("Category") . "</td>";
    print "<td>" . t("Amount") . "</td>";
    print "</tr>";
    print "</thead>";

    print "<tbody>";
    $results_data = array();
    foreach($categories as $cat_id=>$cat_name)
    {
        $result = Jaris\Sql::query(
            "select sum(total) as grand_total "
            . "from church_accounting_expenses "
            . "where category=$cat_id $where",
            $db
        );

        $data = Jaris\Sql::fetchArray($result);

        $results_data[$cat_name] = $data["grand_total"];

        $expenses_total += $data["grand_total"];
    }

    arsort($results_data);

    foreach($results_data as $cat_name=>$grand_total)
    {
        print "<tr>";

        print "<td>" . t($cat_name) . "</td>";

        print "<td>$" . number_format($grand_total, 2, ".", ",") . "</td>";

        print "</tr>";
    }
    print "</tbody>";

    print "</table>";

    print "<p class=\"total\">"
        . "<strong>" . t("Total:") . "</strong> "
        . "\$" . number_format($expenses_total, 2, ".", ",")
        . "</p>"
    ;

    print "<hr />";

    print "<p class=\"total-net\">"
        . "<strong>" . t("Income:") . "</strong> "
        . "\$" . number_format($income_total, 2, ".", ",")
        . "<strong> - </strong> "
        . "<strong>" . t("Expenses:") . "</strong> "
        . "\$" . number_format($expenses_total, 2, ".", ",")
        . "<strong> = </strong> "
        . "\$" . number_format($income_total - $expenses_total, 2, ".", ",")
        . "</p>"
    ;

    Jaris\Sql::close($db);
}

?>

</body>

</html>