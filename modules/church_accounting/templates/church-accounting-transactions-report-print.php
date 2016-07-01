<html>
<head>
<title><?php print t("Transactions Report") ?></title>

<style>
    .report-table
    {
        width: 100%;
    }

    .report-table td
    {
        padding: 7px;
        border-right: solid 1px #d3d3d3;
        width: auto !important;
        vertical-align: top;
    }

    .report-table td:last-child
    {
        text-align: right;
        border-right: none;
    }

    .report-table td.credit
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

    .report-table td h3
    {
        border-top: solid 1px #000;
        border-left: solid 1px #000;
        border-right: solid 1px #000;
        text-align: center;
        margin: 10px 0 0 0;
        padding: 5px;
        color: #000;
    }

    .report-table td p
    {
        margin-top: 0;
    }

    .report-table tr:nth-child(even)
    {
        background-color: #efefef;
    }

    .report-table td table
    {
        border: solid 1px #000;
        background-color: #fff;
        margin-bottom: 7px;
    }

    p.total strong{
        font-size: 20px;
    }

    p.total{
        text-align: center;
        font-size: 20px;
        margin: 0;
    }

</style>

</style>
</head>

<body>

<?php

if(
    ($day > 0 && $month > 0 && $year > 0)
)
{
    print "<h1>" . t("Transactions Report") . "</h1>";

    print "<hr />";

    $months = array_flip(Jaris\Date::getMonths());

    $categories_income = church_accounting_category_list();

    $categories_expenses = church_accounting_category_list(
        ChurchAccountingCategory::EXPENSE
    );

    $db_income = Jaris\Sql::open("church_accounting_income");
    $db_expenses = Jaris\Sql::open("church_accounting_expenses");

    $total_income = 0.00;
    $total_expenses = 0.00;

    $view_tither = Jaris\Authentication::userHasPermissions(
        array("manage_tithers_church_accounting"),
        Jaris\Authentication::currentUser()
    );

    print "<h2 style=\"text-align: center;\">"
        . $day . " / " . $months[$month] . " / " . $year
        . "</h2>"
    ;

    print "<hr />";

    print "<table class=\"report-table\">";
    print "<thead>";
    print "<tr>";
    print "<td>" . t("Type") . "</td>";
    print "<td>" . t("Description") . "</td>";
    print "<td>" . t("Debit") . "</td>";
    print "<td>" . t("Credit") . "</td>";
    print "</tr>";
    print "</thead>";

    print "<tbody>";

    $result_income = Jaris\Sql::query(
        "select * "
        . "from church_accounting_income "
        . "where $where",
        $db_income
    );

    $result_expenses = Jaris\Sql::query(
        "select * "
        . "from church_accounting_expenses "
        . "where $where",
        $db_expenses
    );

    $types_total = array();

    while($data = Jaris\Sql::fetchArray($result_income))
    {
        print "<tr>";

        $total_income += $data["total"];
        $types_total[t($categories_income[$data["category"]])] += $data["total"];

        print "<td>"
            . t($categories_income[$data["category"]])
            . "</td>"
        ;

        print "<td>";

        if($view_tither)
        {
            $tither_data = church_accounting_tither_get($data["tither"]);
            print "<strong>"
                . $tither_data["first_name"] . " "
                . $tither_data["last_name"] . " "
                . $tither_data["maiden_name"]
                . "</strong>";
        }

        print "<p>" . str_replace("\n", "<br />", $data["description"]) . "</p>";
        print $prepared_by;
        print "</td>";

        print "<td></td>";

        print "<td>$" . number_format($data["total"], 2, ".", ",") . "</td>";

        print "</tr>";
    }

    while($data = Jaris\Sql::fetchArray($result_expenses))
    {
        print "<tr>";

        $total_expenses += $data["total"];
        $types_total[t($categories_expenses[$data["category"]])] += $data["total"];

        print "<td>"
            . $data["day"] . " / "
            . $months[$data["month"]] . " / "
            . $data["year"]
            . "</td>"
        ;

        print "<td>"
            . t($categories_expenses[$data["category"]])
            . "</td>"
        ;

        print "<td>";
        print "<p>"
            . str_replace("\n", "<br />", $data["description"])
            . "</p>"
        ;

        $data["checks"] = unserialize($data["checks"]);

        if(count($data["checks"]) > 0)
        {
            print "<h3>" . t("Checks") . "</h3>";
            print "<table class=\"report-table\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Number") . "</td>";
            print "<td>" . t("Paid to") . "</td>";
            print "<td>" . t("Amount") . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";
            foreach($data["checks"] as $check)
            {
                print "<tr>";
                print "<td>" . $check["number"] . "</td>";
                print "<td>" . $check["paid_to"] . "</td>";
                print '<td>$'
                    . number_format($check["amount"], 2, ".", ",")
                    . "</td>"
                ;
                print "</tr>";
            }
            print "</tbody>";
            print "</table>";
        }

        $data["items_data"] = unserialize($data["items_data"]);

        if(count($data["items_data"]) > 0)
        {
           print "<h3>" . t("Items") . "</h3>";
            print "<table class=\"report-table\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Description") . "</td>";
            print "<td>" . t("Amount") . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";
            foreach($data["items_data"] as $item)
            {
                print "<tr>";
                print "<td>" . $item["description"] . "</td>";
                print '<td>$'
                    . number_format($item["amount"], 2, ".", ",")
                    . "</td>"
                ;
                print "</tr>";
            }
            print "</tbody>";
            print "</table>";
        }

        print $prepared_by;

        print "</td>";

        print "<td class=\"credit\">$"
            . number_format($data["total"], 2, ".", ",")
            . "</td>"
        ;

        print "<td></td>";

        print "</tr>";
    }

    print "<tr>";
    print "<td></td>";
    print "<td style=\"text-align: right\">"
        . "<strong>".t("Total:")."</strong>"
        . "</td>"
    ;
    print '<td class="credit">$'
        . number_format($total_expenses, 2, ".", ",")
        . "</td>"
    ;
    print '<td>$' . number_format($total_income, 2, ".", ",") . "</td>";
    print "</tr>";

    foreach($types_total as $type_name => $type_total)
    {
        print "<tr>";
        print "<td></td>";
        print "<td style=\"text-align: right\">"
            . "<strong>".$type_name."</strong>"
            . "</td>"
        ;
        print '<td colspan="2">';
        print "<p class=\"total\">"
        . "\$" . number_format($type_total, 2, ".", ",")
        . "</p>"
        ;
        print '</td>';
        print "</tr>";
    }

    print "<tr>";
    print "<td></td>";
    print "<td style=\"text-align: right\">"
        . "<strong>".t("Net Total:")."</strong>"
        . "</td>"
    ;
    print '<td colspan="2">';
    print "<p class=\"total\">"
        . "\$" . number_format($total_income - $total_expenses, 2, ".", ",")
        . "</p>"
    ;
    print '</td>';
    print "</tr>";

    print "</tbody>";

    print "</table>";

    print "<br />";

    print '<table style="width: 100%;">';
    print "<tr>";
    print '<td style="padding-right: 50px;">' . t("Prepared by:") . "</td>";
    print '<td style="text-align: right; padding-left: 50px;">' . t("Verified by:") . "</td>";
    print "</tr>";

    print "<tr>";
    print '<td style="padding-top: 20px; border-bottom: solid 1px #000; padding-right: 50px;"></td>';
    print '<td style="text-align: right; padding-top: 20px; border-bottom: solid 1px #000; padding-left: 50px;"></td>';
    print "</tr>";
    print "</table>";

    Jaris\Sql::close($db_income);
    Jaris\Sql::close($db_expenses);
}
elseif(
    ($month > 0 && $year > 0)
)
{
    print "<h1>" . t("Transactions Report") . "</h1>";

    print "<hr />";

    $months = array_flip(Jaris\Date::getMonths());

    $categories_income = church_accounting_category_list();

    $categories_expenses = church_accounting_category_list(
        ChurchAccountingCategory::EXPENSE
    );

    $db_income = Jaris\Sql::open("church_accounting_income");
    $db_expenses = Jaris\Sql::open("church_accounting_expenses");

    $total_income = 0.00;
    $total_expenses = 0.00;

    print "<h2 style=\"text-align: center;\">"
        . $months[$month] . " / " . $year
        . "</h2>"
    ;

    print "<hr />";

    print "<table class=\"report-table\">";
    print "<thead>";
    print "<tr>";
    print "<td>" . t("Date") . "</td>";
    print "<td>" . t("Type") . "</td>";
    print "<td>" . t("Description") . "</td>";
    print "<td>" . t("Debit") . "</td>";
    print "<td>" . t("Credit") . "</td>";
    print "</tr>";
    print "</thead>";

    print "<tbody>";

    for($day=1; $day<=31; $day++)
    {
        $result_income = Jaris\Sql::query(
            "select * "
            . "from church_accounting_income "
            . "where day=$day and $where",
            $db_income
        );

        $result_expenses = Jaris\Sql::query(
            "select * "
            . "from church_accounting_expenses "
            . "where day=$day and $where",
            $db_expenses
        );

        $day_total = 0;

        while($data = Jaris\Sql::fetchArray($result_income))
        {
            $prepared_by = "<div>";
            if(trim($data["prepared_by"]) != "")
            {
                $user_data = Jaris\Users::get($data["prepared_by"]);

                $prepared_by .= "<b>" . t("Prepared by:") . "</b> ";

                if($user_data)
                {
                    $prepared_by .= $user_data["name"];
                }
                else
                {
                    $prepared_by .= $data["prepared_by"];
                }

                $prepared_by .= " ";
            }

            if(trim($data["verified_by"]) != "")
            {
                $user_data = Jaris\Users::get($data["verified_by"]);

                $prepared_by .= "<b>" . t("Verified by:") . "</b> ";

                if($user_data)
                {
                    $prepared_by .= $user_data["name"];
                }
                else
                {
                    $prepared_by .= $data["verified_by"];
                }
            }
            $prepared_by .= "</div>";

            print "<tr>";

            $day_total += $data["total"];
            $total_income += $data["total"];

            print "<td>"
                . $data["day"] . " / "
                . $months[$data["month"]] . " / "
                . $data["year"]
                . "</td>"
            ;

            print "<td>"
                . t($categories_income[$data["category"]])
                . "</td>"
            ;

            print "<td>";
            print "<p>"
                . str_replace("\n", "<br />", $data["description"])
                . "</p>"
            ;
            print $prepared_by;
            print "</td>";

            print "<td></td>";

            print "<td>$" . number_format($data["total"], 2, ".", ",") . "</td>";

            print "</tr>";
        }

        if($day_total > 0)
        {
            print "<tr>";
            print "<td></td>";
            print "<td></td>";
            print "<td></td>";
            print "<td style=\"text-align: right\">"
                . "<strong>".t("Subtotal:")."</strong>"
                . "</td>"
            ;
            print "<td></td>";
            print '<td>$' . number_format($day_total, 2, ".", ",") . "</td>";
            print "</tr>";
        }

        $day_total = 0;

        while($data = Jaris\Sql::fetchArray($result_expenses))
        {
            $prepared_by = "<div>";
            if(trim($data["prepared_by"]) != "")
            {
                $user_data = Jaris\Users::get($data["prepared_by"]);

                $prepared_by .= "<b>" . t("Prepared by:") . "</b> ";

                if($user_data)
                {
                    $prepared_by .= $user_data["name"];
                }
                else
                {
                    $prepared_by .= $data["prepared_by"];
                }

                $prepared_by .= " ";
            }

            if(trim($data["verified_by"]) != "")
            {
                $user_data = Jaris\Users::get($data["verified_by"]);

                $prepared_by .= "<b>" . t("Verified by:") . "</b> ";

                if($user_data)
                {
                    $prepared_by .= $user_data["name"];
                }
                else
                {
                    $prepared_by .= $data["verified_by"];
                }
            }
            $prepared_by .= "</div>";

            print "<tr>";

            $day_total += $data["total"];
            $total_expenses += $data["total"];

            print "<td>"
                . $data["day"] . " / "
                . $months[$data["month"]] . " / "
                . $data["year"]
                . "</td>"
            ;

            print "<td>"
                . t($categories_expenses[$data["category"]])
                . "</td>"
            ;

            print "<td>";
            print "<p>"
                . str_replace("\n", "<br />", $data["description"])
                . "</p>"
            ;

            $data["checks"] = unserialize($data["checks"]);

            if(count($data["checks"]) > 0)
            {
                print "<h3>" . t("Checks") . "</h3>";
                print "<table class=\"report-table\">";
                print "<thead>";
                print "<tr>";
                print "<td>" . t("Number") . "</td>";
                print "<td>" . t("Paid to") . "</td>";
                print "<td>" . t("Amount") . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";
                foreach($data["checks"] as $check)
                {
                    print "<tr>";
                    print "<td>" . $check["number"] . "</td>";
                    print "<td>" . $check["paid_to"] . "</td>";
                    print '<td>$'
                        . number_format($check["amount"], 2, ".", ",")
                        . "</td>"
                    ;
                    print "</tr>";
                }
                print "</tbody>";
                print "</table>";
            }

            $data["items_data"] = unserialize($data["items_data"]);

            if(count($data["items_data"]) > 0)
            {
                print "<h3>" . t("Items") . "</h3>";
                print "<table class=\"report-table\">";
                print "<thead>";
                print "<tr>";
                print "<td>" . t("Description") . "</td>";
                print "<td>" . t("Amount") . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";
                foreach($data["items_data"] as $item)
                {
                    print "<tr>";
                    print "<td>" . $item["description"] . "</td>";
                    print '<td>$'
                        . number_format($item["amount"], 2, ".", ",")
                        . "</td>"
                    ;
                    print "</tr>";
                }
                print "</tbody>";
                print "</table>";
            }

            print $prepared_by;

            print "</td>";

            print "<td class=\"credit\">$"
                . number_format($data["total"], 2, ".", ",")
                . "</td>"
            ;

            print "<td></td>";

            print "</tr>";
        }

        if($day_total > 0)
        {
            print "<tr>";
            print "<td></td>";
            print "<td></td>";
            print "<td style=\"text-align: right\">"
                . "<strong>".t("Subtotal:")."</strong>"
                . "</td>"
            ;
            print '<td>$' . number_format($day_total, 2, ".", ",") . "</td>";
            print "<td></td>";
            print "</tr>";
        }
    }

    print "<tr>";
    print "<td></td>";
    print "<td></td>";
    print "<td style=\"text-align: right\">"
        . "<strong>".t("Total:")."</strong>"
        . "</td>"
    ;
    print '<td class="credit">$'
        . number_format($total_expenses, 2, ".", ",")
        . "</td>"
    ;
    print '<td>$' . number_format($total_income, 2, ".", ",") . "</td>";
    print "</tr>";

    print "<tr>";
    print "<td></td>";
    print "<td></td>";
    print "<td style=\"text-align: right\">"
        . "<strong>".t("Net Total:")."</strong>"
        . "</td>"
    ;
    print '<td colspan="2">';
    print "<p class=\"total\">"
        . "\$" . number_format($total_income - $total_expenses, 2, ".", ",")
        . "</p>"
    ;
    print '</td>';
    print "</tr>";

    print "</tbody>";

    print "</table>";

    Jaris\Sql::close($db_income);
    Jaris\Sql::close($db_expenses);
}

?>

</body>

</html>
