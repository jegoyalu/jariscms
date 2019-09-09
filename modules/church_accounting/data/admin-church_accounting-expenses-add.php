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
        <?php print t("Add Expense") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["add_expenses_church_accounting"]);
    ?>
    <script type="text/javascript">
        row_id = 1;

        $(document).ready(function() {
            $("#add-item").click(function() {

                row = "<tr style=\"width: 100%\" id=\"table-row-" + row_id + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[number][]\" placeholder=\"<?php print t("number") ?>\" /></td>";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[paid_to][]\" placeholder=\"<?php print t("paid to") ?>\" /></td>";
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

        row_id_item = 1;

        $(document).ready(function() {
            $("#add-item-data").click(function() {

                row = "<tr style=\"width: 100%\" id=\"table-row-item-" + row_id_item + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"items_data[description][]\" placeholder=\"<?php print t("description") ?>\" /></td>";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"items_data[amount][]\" placeholder=\"<?php print t("amount") ?>\" /></td>";
                row += "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row_item(" + row_id_item + ")\"><?php print t("remove") ?></a></td>";
                row += "</tr>";

                $("#items-data-table > tbody").append($(row));

                row_id_item++;
            });
        });

        function remove_row_item(id)
        {
            $("#table-row-item-" + id).fadeOut("slow", function() {
                $(this).remove();
            });
        }
    </script>
    <?php

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-expense")
        ) {
            $data = [
                "day" => $_REQUEST["day"],
                "month" => $_REQUEST["month"],
                "year" => $_REQUEST["year"],
                "category" => $_REQUEST["category"],
                "description" => $_REQUEST["description"],
                "prepared_by" => $_REQUEST["prepared_by"],
                "verified_by" => $_REQUEST["verified_by"]
            ];

            $ckecks = [];
            if (isset($_REQUEST["checks"]["number"])) {
                foreach ($_REQUEST["checks"]["number"] as $index=>$value) {
                    $checks[] = [
                        "number"=>$value,
                        "paid_to"=>$_REQUEST["checks"]["paid_to"][$index],
                        "amount"=>$_REQUEST["checks"]["amount"][$index]
                    ];
                }
            }
            $data["checks"] = $checks;

            $items = [];
            if (isset($_REQUEST["items_data"]["description"])) {
                foreach ($_REQUEST["items_data"]["description"] as $index=>$value) {
                    $items[] = [
                        "description"=>$value,
                        "amount"=>$_REQUEST["items_data"]["amount"][$index]
                    ];
                }
            }
            $data["items_data"] = $items;

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

            church_accounting_expense_add($data);

            Jaris\View::addMessage(t("Expense successfully added."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses",
                    "church_accounting"
                )
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses",
                    "church_accounting"
                )
            );
        }

        $parameters["name"] = "add-expense";
        $parameters["class"] = "add-expense";
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

        $categories = church_accounting_category_list(
            ChurchAccountingCategory::EXPENSE
        );

        $categories_list = [];
        foreach ($categories as $category_id=>$category_name) {
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
            "description" => t("A brief or detailed description about the expenses.")
        ];

        $fieldset[] = ["fields" => $fields];

        $items_html = "<table id=\"items-data-table\" style=\"width: 100%\">";
        $items_html .= "<thead>";
        $items_html .= "<tr>";
        $items_html .= "<td style=\"width: auto\"><b>" . t("Description") . "</b></td>";
        $items_html .= "<td style=\"width: auto\"><b>" . t("Amount") . "</b></td>";
        $items_html .= "<td style=\"width: auto\"></td>";
        $items_html .= "</tr>";
        $items_html .= "</thead>";
        $items_html .= "<tbody>";
        $items_html .= "</tbody>";
        $items_html .= "</table>";
        $items_html .= "<a id=\"add-item-data\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add expense") . "</a>";

        $fields_items[] = ["type" => "other", "html_code" => $items_html];

        $fieldset[] = [
            "name" => t("Expenses"),
            "fields" => $fields_items,
            "collapsible" => true,
            "description" => t("A list of expenses.")
        ];

        $subject_html = "<table id=\"items-table\" style=\"width: 100%\">";
        $subject_html .= "<thead>";
        $subject_html .= "<tr>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Number") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Paid to") . "</b></td>";
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
            "description" => t("Checks used to pay the expenses.")
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
