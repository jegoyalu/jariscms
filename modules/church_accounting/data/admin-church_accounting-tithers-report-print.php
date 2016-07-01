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

        $year =  0;
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

        $theme = Jaris\Site::$theme;

        ob_start();
        if(file_exists(Jaris\Themes::directory($theme) . "church-accounting-tither-report-print.php"))
            include(Jaris\Themes::directory($theme) . "church-accounting-tither-report-print.php");
        else
            include(
                Jaris\Modules::directory("church_accounting")
                . "templates/church-accounting-tither-report-print.php"
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
