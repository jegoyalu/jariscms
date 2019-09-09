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

    p.total strong{
        font-size: 20px;
    }

    p.total{
        text-align: right;
        font-size: 20px;
    }

</style>

<?php

if (
    ($month > 0 && $year > 0) ||
    ($month <= 0 && $year > 0) ||
    ($month <= 0 && $year <= 0)
) {
    print "<hr />";

    print "<strong>";
    print t("Tither:");
    print "</strong> ";

    print $tither_data["first_name"] . " "
        . $tither_data["last_name"] . " "
        . $tither_data["maiden_name"]
    ;

    print "<hr />";

    $db = Jaris\Sql::open("church_accounting_income");

    $total = 0.00;

    $months = array_flip(Jaris\Date::getMonths());

    if ($month > 0) {
        print "<h2>" . $months[$month] . " / " . $year . "</h2>";

        print "<table class=\"report-table\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Amount") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        $result = Jaris\Sql::query(
            "select * "
            . "from church_accounting_income "
            . "where tither={$tither_data['id']} $where",
            $db
        );

        while ($data = Jaris\Sql::fetchArray($result)) {
            print "<tr>";

            $total += $data["total"];

            print "<td>"
                . $data["day"] . " / "
                . $months[$data["month"]] . " / "
                . $data["year"]
                . "</td>"
            ;

            print "<td>$" . number_format($data["total"], 2, ".", ",") . "</td>";

            print "</tr>";
        }

        print "</tbody>";

        print "</table>";

        print "<p class=\"total\">"
            . "<strong>" . t("Total:") . "</strong> "
            . "\$" . number_format($total, 2, ".", ",")
            . "</p>"
        ;
    } elseif ($month <= 0 && $year > 0) {
        print "<h2>" . t("Year:") . " " . $year . "</h2>";

        print "<table class=\"report-table\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Month") . "</td>";
        print "<td>" . t("Amount") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        foreach ($months as $month_number=>$month_label) {
            $result = Jaris\Sql::query(
                "select sum(total) as grand_total "
                . "from church_accounting_income "
                . "where tither={$tither_data['id']} and "
                . "month=$month_number $where",
                $db
            );

            $data = Jaris\Sql::fetchArray($result);

            if (!is_array($data)) {
                $data = ["grand_total"=>0];
            }

            print "<tr>";

            $total += $data["grand_total"];

            print "<td>" . $month_label . "</td>"
            ;

            print "<td>$" . number_format($data["grand_total"], 2, ".", ",") . "</td>";

            print "</tr>";
        }

        print "</tbody>";

        print "</table>";

        print "<p class=\"total\">"
            . "<strong>" . t("Total:") . "</strong> "
            . "\$" . number_format($total, 2, ".", ",")
            . "</p>"
        ;
    } else {
        print "<h2>" . t("All time report") . "</h2>";

        print "<table class=\"report-table\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Year") . "</td>";
        print "<td>" . t("Amount") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        foreach (Jaris\Date::getYears() as $year_value) {
            $result = Jaris\Sql::query(
                "select sum(total) as grand_total "
                . "from church_accounting_income "
                . "where tither={$tither_data['id']} and "
                . "year=$year_value",
                $db
            );

            $data = Jaris\Sql::fetchArray($result);

            if (!is_array($data) || $data["grand_total"] == null) {
                continue;
            }

            print "<tr>";

            $total += $data["grand_total"];

            print "<td>" . $year_value . "</td>"
            ;

            print "<td>$" . number_format($data["grand_total"], 2, ".", ",") . "</td>";

            print "</tr>";
        }

        print "</tbody>";

        print "</table>";

        print "<p class=\"total\">"
            . "<strong>" . t("Total:") . "</strong> "
            . "\$" . number_format($total, 2, ".", ",")
            . "</p>"
        ;
    }

    Jaris\Sql::close($db);
}
