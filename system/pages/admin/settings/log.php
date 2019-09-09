<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the system log listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("System Log") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        Jaris\View::addTab(t("System Log"), "admin/settings/log");
        Jaris\View::addTab(t("Errors Log"), "admin/settings/errors");

        Jaris\View::addTab(
            t("Clear Log"),
            "admin/settings/log-clear",
            [],
            1
        );

        $ordering = "order by date desc";

        if (!isset($_REQUEST["ordering"])) {
            $_REQUEST["ordering"] = "";
        }

        switch ($_REQUEST["ordering"]) {
            case "date_asc":
                $ordering = "order by date asc";
                break;

            default:
                $ordering = "order by date desc";
        }

        $page = 1;

        if (isset($_REQUEST["page"])) {
            $page = $_REQUEST["page"];
        }

        $order_by = [
            t("Date Descending") => "date_desc",
            t("Date Ascending") => "date_asc"
        ];

        print "<form method=\"get\" action=\"" . Jaris\Uri::url("admin/settings/log") . "\">\n";
        print "<input type=\"hidden\" name=\"page\" value=\"$page\" />\n";
        print t("Order by:") . " <select onchange=\"javascript: this.form.submit()\" name=\"ordering\">\n";
        foreach ($order_by as $order_title=>$order_value) {
            if ($order_value == $_REQUEST["ordering"]) {
                print "<option selected value=\"$order_value\">$order_title</option>\n";
            } else {
                print "<option value=\"$order_value\">$order_title</option>\n";
            }
        }
        print "</select>\n";
        print "</form>\n";

        $pages_count = Jaris\Sql::countColumn(
            "log",
            "log",
            "date"
        );

        print "<h2>" . t("Total messages:") . " " . $pages_count . "</h2>";

        $messages = Jaris\Sql::getDataList(
            "log",
            "log",
            $page - 1,
            100,
            $ordering
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/settings/log",
            "",
            100,
            [
                "ordering" => $_REQUEST["ordering"]
            ]
        );

        $level = [
            Jaris\Logger::EMERGENCY => t('Emergency'),
            Jaris\Logger::ALERT => t('Alert'),
            Jaris\Logger::CRITICAL => t('Critical'),
            Jaris\Logger::ERROR => t('Error'),
            Jaris\Logger::WARNING => t('Warning'),
            Jaris\Logger::NOTICE => t('Notice'),
            Jaris\Logger::INFO => t('Info'),
            Jaris\Logger::DEBUG => t('Debug')
        ];

        print "<table class=\"navigation-list\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("Date") . "</td>";
        print "<td>" . t("Type") . "</td>";
        print "<td>" . t("Message") . "</td>";
        print "<td>" . t("Author") . "</td>";
        print "<td>" . t("Uri") . "</td>";
        print "<td>" . t("Module") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";

        foreach ($messages as $message) {
            $message["context"] = unserialize($message["context"]);

            print "<tr class=\"{$message["level"]}\">";

            print "<td>" . date("r", $message["date"]) . "</td>";

            print "<td>"
                . "<span class=\"{$message["level"]}\">"
                . $level[$message["level"]]
                . "</span>"
                . "</td>"
            ;

            $message["message"] = t($message["message"]);

            if (count($message["context"]) > 0) {
                foreach ($message["context"] as $ctx_key=>$ctx_val) {
                    $message["message"] = str_replace(
                        "{".$ctx_key."}",
                        $ctx_val,
                        $message["message"]
                    );
                }
            }

            print "<td>" . $message["message"] . "</td>";

            print "<td>" . $message["author"] . "</td>";

            $link = "<a href=\"".Jaris\Uri::url($message["uri"])."\">"
                . $message["uri"]
                . "</a>"
            ;

            print "<td>" . $link . "</td>";

            print "<td>" . $message["module"] . "</td>";

            print "</tr>";
        }

        print "</tbody>";

        print "</table>";

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/settings/log",
            "",
            100,
            [
                "ordering" => $_REQUEST["ordering"]
            ]
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
