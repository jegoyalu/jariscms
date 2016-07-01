<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the errors listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Error Log") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addTab(t("Clear Errors Log"), "admin/settings/errors-clear");

        $ordering = "order by error_date desc";

        if(!isset($_REQUEST["ordering"]))
        {
            $_REQUEST["ordering"] = "";
        }

        switch($_REQUEST["ordering"])
        {
            case "level_desc":
                $ordering = "order by error_type desc";
                break;

            case "level_asc":
                $ordering = "order by error_type asc";
                break;

            case "date_asc":
                $ordering = "order by error_date asc";
                break;

            default:
                $ordering = "order by error_date desc";
        }

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $order_by = array(
            t("Date Descending") => "date_desc",
            t("Date Ascending") => "date_asc",
            t("Error Level Descending") => "level_desc",
            t("Error Level Ascending") => "level_asc"
        );

        print "<form method=\"get\" action=\"" . Jaris\Uri::url("admin/settings/errors") . "\">\n";
        print "<input type=\"hidden\" name=\"page\" value=\"$page\" />\n";
        print t("Order by:") . " <select onchange=\"javascript: this.form.submit()\" name=\"ordering\">\n";
        foreach($order_by as $order_title=>$order_value)
        {
            if($order_value == $_REQUEST["ordering"])
            {
                print "<option selected value=\"$order_value\">$order_title</option>\n";
            }
            else
            {
                print "<option value=\"$order_value\">$order_title</option>\n";
            }
        }
        print "</select>\n";
        print "</form>\n";

        $pages_count = Jaris\Sql::countColumn(
            "errors_log",
            "errors_log",
            "error_date"
        );

        print "<h2>" . t("Total errors:") . " " . $pages_count . "</h2>";

        $errors = Jaris\Sql::getDataList(
            "errors_log",
            "errors_log",
            $page - 1,
            100,
            $ordering
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/settings/errors",
            "",
            100,
            array(
                "ordering" => $_REQUEST["ordering"]
            )
        );

        $errortype = array(
            E_ERROR => t('Error'),
            E_WARNING => t('Warning'),
            E_PARSE => t('Parsing Error'),
            E_NOTICE => t('Notice'),
            E_CORE_ERROR => t('Core Error'),
            E_CORE_WARNING => t('Core Warning'),
            E_COMPILE_ERROR => t('Compile Error'),
            E_COMPILE_WARNING => t('Compile Warning'),
            E_USER_ERROR => t('User Error'),
            E_USER_WARNING => t('User Warning'),
            E_USER_NOTICE => t('User Notice'),
            E_STRICT => t('Runtime Notice'),
            E_RECOVERABLE_ERROR => t('Catchable Fatal Error')
        );

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Type") . "</td>";
        print "<td>" . t("Message") . "</td>";
        print "<td>" . t("File") . "</td>";
        print "<td>" . t("Line") . "</td>";
        print "<td>" . t("Page") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        foreach($errors as $error)
        {
            print "<tr>";

            print "<td>" . date("r", $error["error_date"]) . "</td>";

            print "<td>" . $errortype[$error["error_type"]] . "</td>";

            if(
                "" . stripos($error["error_message"], "eval()'d code") .  "" != ""
            )
            {
                $matches = array();

                preg_match("/line ([0-9]+) and/", $error["error_message"], $matches);

                $error_line_url = Jaris\Uri::url(
                    "admin/settings/error-line",
                    array(
                        "page" => $error["error_page"],
                        "line" => $matches[1]
                    )
                );

                print "<td><a href=\"".$error_line_url."#l{$matches[1]}\">"
                    . $error["error_message"]
                    . "</a></td>";
            }
            else
            {
                print "<td>" . $error["error_message"] . "</td>";
            }

            print "<td>" . $error["error_file"] . "</td>";

            if(
                "" . stripos($error["error_file"], "eval()'d code") .  "" != ""
            )
            {
                $error_line_url = Jaris\Uri::url(
                    "admin/settings/error-line",
                    array(
                        "page" => $error["error_page"],
                        "line" => $error["error_line"]
                    )
                );

                print "<td><a href=\"".$error_line_url."#l{$error["error_line"]}\">"
                    . $error["error_line"]
                    . "</a></td>";
            }
            else
            {
                $error_line_url = Jaris\Uri::url(
                    "admin/settings/error-line",
                    array(
                        "include" => $error["error_file"],
                        "line" => $error["error_line"]
                    )
                );

                print "<td><a href=\"".$error_line_url."#l{$error["error_line"]}\">"
                    . $error["error_line"]
                    . "</a></td>";
            }

            print "<td><a href=\"".Jaris\Uri::url($error["error_page"])."\">"
                . $error["error_page"]
                . "</a></td>";

            print "</tr>";
        }

        print "</tbody>";

        print "</table>";

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/settings/errors",
            "",
            100,
            array(
                "ordering" => $_REQUEST["ordering"]
            )
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
