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
        <?php print t("Church Accounting Tithers") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("manage_tithers_church_accounting"));

        Jaris\View::addTab(
            t("Add Tither"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/tithers/add",
                "church_accounting"
            )
        );

        $page = 1;

        if(isset($_REQUEST["page"]))
        {
            $page = $_REQUEST["page"];
        }

        $pages_count = Jaris\Sql::countColumn(
            "church_accounting_tithers",
            "church_accounting_tithers",
            "id"
        );

        print "<div>";
        print "<h2>" . t("Total:") . " " . $pages_count . "</h2>";
        print "</div>";

        $tithers = Jaris\Sql::getDataList(
            "church_accounting_tithers",
            "church_accounting_tithers",
            $page - 1,
            20
        );

        Jaris\System::printNavigation(
            $pages_count,
            $page,
            "admin/church-accounting/tithers",
            "church_accounting",
            20
        );

        print "<table class=\"navigation-list navigation-list-hover\">";
        print "<thead>";
        print "<tr>";
        print "<td>" . t("First Name") . "</td>";
        print "<td>" . t("Last Names") . "</td>";
        print "<td>" . t("Contact") . "</td>";
        print "<td>" . t("Operation") . "</td>";
        print "</tr>";
        print "</thead>";

        print "<tbody>";
        foreach($tithers as $tithers_data)
        {
            print "<tr>";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers/edit",
                    "church_accounting"
                ),
                array("id"=>$tithers_data["id"])
            );

            print "<td>"
                . "<a href=\"$edit_url\">" . $tithers_data["first_name"] . "</a> "
                . "</td>"
            ;

            print "<td>"
                . $tithers_data["last_name"]
                . " "
                . $tithers_data["maiden_name"]
                . "</td>"
            ;

            print "<td>";
            if(trim($tithers_data["email"]) != ""){
                print $tithers_data["email"];
            }
            elseif(trim($tithers_data["phone"]) != ""){
                print $tithers_data["phone"];
            }
            elseif(trim($tithers_data["mobile_phone"]) != ""){
                print $tithers_data["mobile_phone"];
            }
            print "</td>";

            $add_tithe_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/income/tithes/add",
                    "church_accounting"
                ),
                array("tid"=>$tithers_data["id"])
            );

            $add_tither_offering_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/income/tither-offerings/add",
                    "church_accounting"
                ),
                array("tid"=>$tithers_data["id"])
            );

            $delete_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers/delete",
                    "church_accounting"
                ),
                array("id"=>$tithers_data["id"])
            );

            print "<td>"
                . "<a href=\"$add_tithe_url\">" . t("Add tithe") . "</a> "
                . "<a href=\"$add_tither_offering_url\">" . t("Add Offering") . "</a> "
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
            "admin/church-accounting/tithers",
            "church_accounting",
            20
        );
    ?>
    field;

    field: is_system
        1
    field;
row;
