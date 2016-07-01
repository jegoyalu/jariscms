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
        <?php print t("Church Accounting Income/Expenses Report") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_income_church_accounting"));

        Jaris\View::addTab(
            t("Print"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/income/report/print",
                "church_accounting"
            ),
            array(
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"]
            )
        );

        if(trim($_REQUEST["month"]) != "")
        {
            $month = intval($_REQUEST["month"]);
            $options[] = "month=$month";
        }

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

        print "<form class=\"filter-results\" method=\"get\" action=\""
            . Jaris\Uri::url(Jaris\Uri::get())
            . "\" style=\"display: block; width: 100%;\">\n"
        ;
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
        foreach(Jaris\Date::getYears() as $year)
        {
            $selected = "";

            if($_REQUEST["year"] == $year)
            {
                $selected = "selected=\"selected\"";
            }

            print "<option $selected value=\"$year\">$year</option>\n";
        }
        print "</select>\n";

        print '<input type="submit" value="'.t("View").'" />';
        print "</div>";
        print "</form>\n";

        print "<div style=\"clear: both\"></div>";

        $theme = Jaris\Site::$theme;

        ob_start();
        if(file_exists(Jaris\Themes::directory($theme) . "church-accounting-report.php"))
            include(Jaris\Themes::directory($theme) . "church-accounting-report.php");
        else
            include(
                Jaris\Modules::directory("church_accounting")
                . "templates/church-accounting-report.php"
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
