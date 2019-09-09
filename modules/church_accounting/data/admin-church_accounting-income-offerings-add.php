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
        <?php print t("Add Offerings") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_income_church_accounting"]);
    ?>
    <script type="text/javascript">
        row_id = 1;

        $(document).ready(function() {
            $("#add-item").click(function() {

                row = "<tr style=\"width: 100%\" id=\"table-row-" + row_id + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[number][]\" placeholder=\"<?php print t("number") ?>\" /></td>";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[amount][]\" placeholder=\"<?php print t("amount") ?>\" /></td>";
                row += "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(" + row_id + ")\"><?php print t("remove") ?></a></td>";
                row += "</tr>";

                $("#items-table > tbody").append($(row));

                row_id++;
            });
        });

        function remove_row(id)
        {
            $("#table-row-" + id).fadeOut("slow", function() {
                $(this).remove();
            });
        }
    </script>
    <?php

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-income-offerings")
        ) {
            $data = [
                "day" => $_REQUEST["day"],
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "category" => $_REQUEST["category"],
                "description" => $_REQUEST["description"],
                "cash" => $_REQUEST["cash"],
                "total" => $_REQUEST["total"],
                "prepared_by" => $_REQUEST["prepared_by"],
                "verified_by" => $_REQUEST["verified_by"]
            ];

            $ckecks = [];
            if (isset($_REQUEST["checks"]["number"])) {
                foreach ($_REQUEST["checks"]["number"] as $index=>$value) {
                    $checks[] = [
                        "number"=>$value,
                        "amount"=>$_REQUEST["checks"]["amount"][$index]
                    ];
                }
            }
            $data["checks"] = $checks;

            $attachments = [];
            if (is_array($_FILES["attachments"]["name"])) {
                foreach ($_FILES["attachments"]["name"] as $file_index => $file_name) {
                    $attachments[] = [
                        "name" => $file_name,
                        "tmp_name" => $_FILES["attachments"]["tmp_name"][$file_index]
                    ];
                }
            }
            $data["attachments"] = $attachments;

            church_accounting_income_add($data);

            Jaris\View::addMessage(t("Offerings successfully added."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/income",
                    "church_accounting"
                )
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/income",
                    "church_accounting"
                )
            );
        }

        $parameters["name"] = "add-income-offerings";
        $parameters["class"] = "add-income-offerings";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields_date[] = [
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "value" => Jaris\Date::getDays(),
            "selected" => isset($_REQUEST["day"]) ?
                $_REQUEST["day"] : date("j", time()),
            "inline" => true
        ];

        $fields_date[] = [
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "value" => Jaris\Date::getMonths(),
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"] : date("n", time()),
            "inline" => true
        ];

        $fields_date[] = [
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "value" => Jaris\Date::getYears(),
            "selected" => isset($_REQUEST["year"]) ?
                $_REQUEST["year"] : date("Y", time()),
            "inline" => true
        ];

        $fieldset[] = [
            "name" => t("Date"),
            "fields" => $fields_date,
            "description" => t("Date when the offerings where received.")
        ];

        $categories = church_accounting_category_list();
        $categories_list = [];
        foreach ($categories as $category_id=>$category_name) {
            if ($category_id == 1) {
                continue;
            }

            $categories_list[t($category_name)] = $category_id;
        }

        if (count($categories_list) > 0) {
            $fields[] = [
                "type" => "select",
                "name" => "category",
                "value" => $categories_list,
                "selected" => $_REQUEST["category"],
                "label" => t("Category:"),
                "description" => t("The category that best apply for this entry.")
            ];
        }

        $fields[] = [
            "type" => "textarea",
            "name" => "description",
            "value" => $_REQUEST["description"],
            "label" => t("Description:"),
            "description" => t("A brief or detailed description about the income.")
        ];

        $fieldset[] = ["fields" => $fields];

        $cash = [
            "0.01",
            "0.05",
            "0.10",
            "0.25",
            "0.50",
            "1.00",
            "5.00",
            "10.00",
            "20.00",
            "50.00",
            "100.00"
        ];

        $fields_cash = [];
        foreach ($cash as $index=>$amount) {
            $fields_cash[] = [
                "type" => "hidden",
                "name" => "cash[$index][amount]",
                "value" => $amount
            ];

            $fields_cash[] = [
                "type" => "number",
                "name" => "cash[$index][quantity]",
                "value" => $_REQUEST["cash"][$index]["quantity"],
                "label" => "\$$amount",
                "inline" => true
            ];
        }

        $fieldset[] = [
            "name" => t("Cash"),
            "fields" => $fields_cash,
            "collapsible" => true,
            "description" => t("Enter the quantity received for each amount that applies.")
        ];

        $subject_html = "<table id=\"items-table\" style=\"width: 100%\">";
        $subject_html .= "<thead>";
        $subject_html .= "<tr>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Number") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Amount") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"></td>";
        $subject_html .= "</tr>";
        $subject_html .= "</thead>";
        $subject_html .= "<tbody>";

        $subject_html .= "</tbody>";
        $subject_html .= "</table>";
        $subject_html .= "<a id=\"add-item\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add check") . "</a>";

        $fields_checks[] = ["type" => "other", "html_code" => $subject_html];

        $fieldset[] = [
            "name" => t("Checks"),
            "fields" => $fields_checks,
            "collapsible" => true,
            "description" => t("Any checks received as offering.")
        ];

        $fields_attachments[] = [
            "type" => "file",
            "name" => "attachments",
            "multiple" => true,
            "label" => t("Files:")
        ];

        $fieldset[] = [
            "name" => t("Attachments"),
            "fields" => $fields_attachments,
            "collapsible" => true,
            "description" => t("You can attach any file type like photos of checks, etc...")
        ];

        $fields_other[] = [
            "type" => "text",
            "name" => "total",
            "value" => $_REQUEST["total"],
            "label" => t("Additional amount"),
            "description" => t("Any other additional amount.")
        ];

        $fields_other[] = [
            "type" => "user",
            "name" => "prepared_by",
            "value" => isset($_REQUEST["prepared_by"]) ?
                $_REQUEST["prepared_by"] : Jaris\Authentication::currentUser(),
            "label" => t("Prepared by:"),
            "description" => t("The treasurer or person who entered this data.")
        ];

        $fields_other[] = [
            "type" => "user",
            "name" => "verified_by",
            "value" => isset($_REQUEST["verified_by"]) ?
                $_REQUEST["verified_by"] : "",
            "label" => t("Verified by:"),
            "description" => t("The sub-treasurer or person who verified this data.")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_other];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
