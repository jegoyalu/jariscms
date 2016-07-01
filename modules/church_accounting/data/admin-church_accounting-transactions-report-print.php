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
        <?php print t("Church Accounting Transactions") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_income_church_accounting"));

        $day = "";
        if(trim($_REQUEST["day"]) != "")
        {
            $day = intval($_REQUEST["day"]);
            $options[] = "day=$day";
        }

        $month = date("n");
        if(trim($_REQUEST["month"]) != "")
        {
            $month = intval($_REQUEST["month"]);
            $options[] = "month=$month";
        }
        else
        {
            $options[] = "month=$month";
        }

        $year = date("Y");
        if(trim($_REQUEST["year"]) != "")
        {
            $year = intval($_REQUEST["year"]);
            $options[] = "year=$year";
        }
        else
        {
            $options[] = "year=$year";
        }

        $where = "";
        if(count($options) > 0)
        {
            $where = implode(" and ", $options);
        }


        $theme = Jaris\Site::$theme;

        ob_start();
        if(file_exists(Jaris\Themes::directory($theme) . "church-accounting-transactions-report-print.php"))
            include(Jaris\Themes::directory($theme) . "church-accounting-transactions-report-print.php");
        else
            include(
                Jaris\Modules::directory("church_accounting")
                . "templates/church-accounting-transactions-report-print.php"
            );
        $output = ob_get_contents();
        ob_end_clean();

        print $output;
    ?>
    field;

    field: rendering_mode
        plain_html
    field;

    field: is_system
        1
    field;
row;
