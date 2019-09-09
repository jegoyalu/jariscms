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
        <?php print t("Edit Expense") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_expenses_church_accounting"));
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

        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".navigation-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".navigation-list tbody tr td a.delete").click(function() {
                $(this).parent().parent().fadeOut(1000, function() {
                    $(this).remove();
                });
            });
        });
    </script>
    <style>
        .navigation-list tbody tr:hover
        {
            background-color: #d3d3d3;
        }
    </style>
    <?php
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        $offerings_data = church_accounting_expense_get($_REQUEST["id"]);

        if(!is_array($offerings_data))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses",
                    "church_accounting"
                )
            );
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-expense")
        )
        {
            $data = $offerings_data;

            $data["day"] = $_REQUEST["day"];
            $data["month"] = $_REQUEST["month"];
            $data["year"] = $_REQUEST["year"];
            $data["category"] = $_REQUEST["category"];
            $data["description"] = $_REQUEST["description"];
            $data["total"] = $_REQUEST["total"];
            $data["prepared_by"] = $_REQUEST["prepared_by"];
            $data["verified_by"] = $_REQUEST["verified_by"];

            $ckecks = array();
            if(isset($_REQUEST["checks"]["number"]))
            {
                foreach($_REQUEST["checks"]["number"] as $index=>$value)
                {
                    $checks[] = array(
                        "number"=>$value,
                        "paid_to"=>$_REQUEST["checks"]["paid_to"][$index],
                        "amount"=>$_REQUEST["checks"]["amount"][$index]
                    );
                }
            }
            $data["checks"] = $checks;

            $items = array();
            if(isset($_REQUEST["items_data"]["description"]))
            {
                foreach($_REQUEST["items_data"]["description"] as $index=>$value)
                {
                    $items[] = array(
                        "description"=>$value,
                        "amount"=>$_REQUEST["items_data"]["amount"][$index]
                    );
                }
            }
            $data["items_data"] = $items;

            $attachments = $data["attachments"];

            //Delete removed files
            foreach($attachments as $file)
            {
                if(is_array($_REQUEST["file_list"]))
                {
                    if(!in_array($file, $_REQUEST["file_list"]))
                    {
                        church_accounting_attachments_delete($file);
                    }
                }
                else
                {
                    church_accounting_attachments_delete($file);
                }
            }

            if(is_array($_REQUEST["file_list"]))
                $attachments = $_REQUEST["file_list"];
            else
                $attachments = array();

            if(is_array($_FILES["attachments"]["name"]))
            {
                foreach($_FILES["attachments"]["name"] as $file_index => $file_name)
                {
                    $attachments[] = array(
                        "name" => $file_name,
                        "tmp_name" => $_FILES["attachments"]["tmp_name"][$file_index]
                    );
                }
            }
            $data["attachments"] = $attachments;

            church_accounting_expense_edit($_REQUEST["id"], $data);

            Jaris\View::addMessage(t("Expense successfully edited."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses",
                    "church_accounting"
                )
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/expenses",
                    "church_accounting"
                )
            );
        }

        $parameters["name"] = "edit-expense";
        $parameters["class"] = "edit-expense";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields_date[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $offerings_data["id"]
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "day",
            "label" => t("Day:"),
            "value" => Jaris\Date::getDays(),
            "selected" => isset($_REQUEST["day"]) ?
                $_REQUEST["day"] : $offerings_data["day"],
            "inline" => true
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "month",
            "label" => t("Month:"),
            "value" => Jaris\Date::getMonths(),
            "selected" => isset($_REQUEST["month"]) ?
                $_REQUEST["month"] : $offerings_data["month"],
            "inline" => true
        );

        $fields_date[] = array(
            "type" => "select",
            "name" => "year",
            "label" => t("Year:"),
            "value" => Jaris\Date::getYears(),
            "selected" => isset($_REQUEST["year"]) ?
                $_REQUEST["year"] : $offerings_data["year"],
            "inline" => true
        );

        $fieldset[] = array(
            "name" => t("Date"),
            "fields" => $fields_date,
            "description" => t("Date when the offerings where received.")
        );

        $categories = church_accounting_category_list(
            ChurchAccountingCategory::EXPENSE
        );

        $categories_list = array();
        foreach($categories as $category_id=>$category_name)
        {
            $categories_list[t($category_name)] = $category_id;
        }

        if(count($categories_list) > 0)
        {
            $fields[] = array(
                "type" => "select",
                "name" => "category",
                "value" => $categories_list,
                "selected" => isset($_REQUEST["category"]) ?
                    $_REQUEST["category"] : $offerings_data["category"],
                "label" => t("Category:"),
                "description" => t("The category that best apply for this entry.")
            );
        }

        $fields[] = array(
            "type" => "textarea",
            "name" => "description",
            "value" => isset($_REQUEST["description"]) ?
                $_REQUEST["description"] : $offerings_data["description"],
            "label" => t("Description:"),
            "description" => t("A brief or detailed description about the expenses.")
        );

        $fieldset[] = array("fields" => $fields);

        $items_html = "<table id=\"items-data-table\" style=\"width: 100%\">";
        $items_html .= "<thead>";
        $items_html .= "<tr>";
        $items_html .= "<td style=\"width: auto\"><b>" . t("Description") . "</b></td>";
        $items_html .= "<td style=\"width: auto\"><b>" . t("Amount") . "</b></td>";
        $items_html .= "<td style=\"width: auto\"></td>";
        $items_html .= "</tr>";
        $items_html .= "</thead>";
        $items_html .= "<tbody>";

        $i = 1;
        foreach($offerings_data["items_data"] as $item_data)
        {
            $description = $item_data["description"];
            $amount = $item_data["amount"];

            $items_html .= "<tr id=\"table-row-item-$i\">";
            $items_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"items_data[description][]\" placeholder=\"".t("description")."\" value=\"$description\" /></td>";
            $items_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"items_data[amount][]\" placeholder=\"".t("amount")."\" value=\"$amount\" /></td>";
            $items_html .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row_item($i)\">" . t("remove") . "</a></td>";
            $items_html .= "</tr>";

            $i++;
        }

        $items_html .= "</tbody>";
        $items_html .= "</table>";
        $items_html .= "<a id=\"add-item-data\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add expense") . "</a>";
        $items_html .= '<script type="text/javascript">row_id_item = '.$i.';</script>';

        $fields_items[] = array("type" => "other", "html_code" => $items_html);

        $fieldset[] = array(
            "name" => t("Expenses"),
            "fields" => $fields_items,
            "collapsible" => true,
            "description" => t("A list of expenses.")
        );

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

        $i = 1;
        foreach($offerings_data["checks"] as $check_data)
        {
            $number = $check_data["number"];
            $paid_to = $check_data["paid_to"];
            $amount = $check_data["amount"];

            $subject_html .= "<tr id=\"table-row-$i\">";
            $subject_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[number][]\" placeholder=\"".t("number")."\" value=\"$number\" /></td>";
            $subject_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[paid_to][]\" placeholder=\"".t("paid to")."\" value=\"$paid_to\" /></td>";
            $subject_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"checks[amount][]\" placeholder=\"".t("amount")."\" value=\"$amount\" /></td>";
            $subject_html .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row($i)\">" . t("remove") . "</a></td>";
            $subject_html .= "</tr>";

            $i++;
        }

        $subject_html .= "</tbody>";
        $subject_html .= "</table>";
        $subject_html .= "<a id=\"add-item\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add check") . "</a>";
        $subject_html .= '<script type="text/javascript">row_id = '.$i.';</script>';

        $fields_checks[] = array("type" => "other", "html_code" => $subject_html);

        $fieldset[] = array(
            "name" => t("Checks"),
            "fields" => $fields_checks,
            "collapsible" => true,
            "description" => t("Checks used to pay the expenses.")
        );

        $files = "<table class=\"navigation-list\">";
        $files .= "<thead>";
        $files .= "<tr>";
        $files .= "<td>" . t("Order") . "</td>";
        $files .= "<td>" . t("File") . "</td>";
        $files .= "<td>" . t("Action") . "</td>";
        $files .= "</tr>";
        $files .= "</thead>";

        $files .= "<tbody>";
        if(is_array($offerings_data["attachments"]))
        {
            foreach($offerings_data["attachments"] as $file)
            {
                $files .= "<tr>";

                $files .= "<td><a class=\"sort-handle\"></a></td>";

                $file_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/attachment",
                        "church_accounting"
                    ),
                    array("f" => $file)
                );

                $file_elements = explode("/", $file);

                $files .= "<td>
                    <input type=\"hidden\" name=\"file_list[]\" value=\"$file\"  />
                    <a href=\"" . $file_url . "\" />{$file_elements[2]}</a>
                </td>";

                $files .= "<td><a class=\"delete\" style=\"cursor: pointer\">" .
                    t("Delete") .
                    "</a></td>"
                ;

                $files .= "</tr>";
            }
        }
        $files .= "</tbody>";

        $files .= "</table>";

        $fields_attachments[] = array(
            "type" => "file",
            "name" => "attachments",
            "multiple" => true,
            "label" => t("Files:")
        );

        $fields_attachments[] = array(
            "type" => "other",
            "html_code" => "<div style=\"margin-top: 10px;\">"
                . "<strong>" . t("Current files:") . "</strong>"
                . "<hr />"
                . "$files"
                . "</div>"
        );

        $fieldset[] = array(
            "name" => t("Attachments"),
            "fields" => $fields_attachments,
            "collapsible" => true,
            "description" => t("You can attach any file type like photos of checks, etc...")
        );

        $fields_other[] = array(
            "type" => "user",
            "name" => "prepared_by",
            "value" => isset($_REQUEST["prepared_by"]) ?
                $_REQUEST["prepared_by"] : $offerings_data["prepared_by"],
            "label" => t("Prepared by:"),
            "description" => t("The treasurer or person who entered this data.")
        );

        $fields_other[] = array(
            "type" => "user",
            "name" => "verified_by",
            "value" => isset($_REQUEST["verified_by"]) ?
                $_REQUEST["verified_by"] : $offerings_data["verified_by"],
            "label" => t("Verified by:"),
            "description" => t("The sub-treasurer or person who verified this data.")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
