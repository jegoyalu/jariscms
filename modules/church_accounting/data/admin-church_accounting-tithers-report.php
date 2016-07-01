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
        <?php print t("Tither Report") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_income_church_accounting"));

        $tither_data = church_accounting_tither_get($_REQUEST["id"]);

        if(!is_array($tither_data))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }

        $month =  0;
        if(trim($_REQUEST["month"]) != "")
        {
            $month = intval($_REQUEST["month"]);
            $options[] = "month=$month";
        }

        $year = 0;
        if(trim($_REQUEST["year"]) != "")
        {
            $year = intval($_REQUEST["year"]);
            $options[] = "year=$year";
        }

        $where = "";
        if(count($options) > 0)
        {
            $where = " and " . implode(" and ", $options);
        }

        $print_label = $year > 0 && $month <= 0 ?
            t("Print Certification")
            :
            t("Print")
        ;

        Jaris\View::addTab(
            $print_label,
            Jaris\Modules::getPageUri(
                "admin/church-accounting/tithers/report/print",
                "church_accounting"
            ),
            array(
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "id" => $_REQUEST["id"]
            )
        );

        print "<form class=\"filter-results\" method=\"get\" action=\""
            . Jaris\Uri::url(Jaris\Uri::get())
            . "\" style=\"display: block; width: 100%;\">\n"
        ;
        print '<input type="hidden" name="id" value="'.$_REQUEST["id"].'" />';

        print "<div style=\"float: left\">";
        print t("Month:") . " <select name=\"month\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach(Jaris\Date::getMonths() as $month_name=>$month_value)
        {
            $selected = "";

            if($_REQUEST["month"] == $month_value)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$month_value\">$month_name</option>\n";
        }
        print "</select>\n";

        print t("Year:") . " <select onchange=\"javascript: this.form.submit()\" name=\"year\">\n";
        print "<option value=\"\">" . t("All") . "</option>\n";
        foreach(Jaris\Date::getYears() as $year_index=>$year_value)
        {
            $selected = "";

            if($_REQUEST["year"] == $year_value)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$year_value\">$year_value</option>\n";
        }
        print "</select>\n";

        print '<input type="submit" value="'.t("View").'" />';
        print "</div>";
        print "</form>\n";

        print "<div style=\"clear: both\"></div>";

        $theme = Jaris\Site::$theme;

        ob_start();
        if(file_exists(Jaris\Themes::directory($theme) . "church-accounting-tither-report.php"))
            include(Jaris\Themes::directory($theme) . "church-accounting-tither-report.php");
        else
            include(
                Jaris\Modules::directory("church_accounting")
                . "templates/church-accounting-tither-report.php"
            );
        $output = ob_get_contents();
        ob_end_clean();

        print $output;
    ?>
    field;

    field: is_system
        1
    field;
row;
