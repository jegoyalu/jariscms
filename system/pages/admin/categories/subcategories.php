<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the subcategories configuration page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Subcategories") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_subcategories"]);

        if (!isset($_REQUEST["category"])) {
            Jaris\Uri::go("admin/categories");
        }
    ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".subcategories-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });
        });
    </script>

    <?php
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(t("Categories"), "admin/categories");

        Jaris\View::addTab(
            t("Create Subcategory"),
            "admin/categories/subcategories/add",
            ["category" => $_REQUEST["category"]]
        );

        Jaris\View::addTab(
            t("Bulk Subcategory Add"),
            "admin/categories/subcategories/add-bulk",
            ["category" => $_REQUEST["category"]]
        );

        $print_subcategories = function (
            $category_name,
            $parent = "root",
            $position = ""
        ) use (&$print_subcategories) {
            $category_data = Jaris\Categories::get($category_name);

            if (!$category_data["sorting"]) {
                $subcategories_list = Jaris\Data::sort(
                    Jaris\Categories::getChildSubcategories($category_name, $parent),
                    "order"
                );
            } else {
                $subcategories_list = Jaris\Data::sort(
                    Jaris\Categories::getChildSubcategories($category_name, $parent),
                    "title"
                );
            }

            if ($subcategories_list) {
                foreach ($subcategories_list as $id => $fields) {
                    $select = "<select name=\"parent[]\">\n";
                    $subcategories_for_parent["root"] = ["title" => "&lt;root&gt;"];
                    $subcategories_for_parent += Jaris\Categories::getSubcategories($category_name);
                    foreach ($subcategories_for_parent as $select_id => $select_fields) {
                        $selected = "";
                        if ("" . $fields["parent"] . "" == "" . $select_id . "") {
                            $selected = "selected";
                        }

                        if ("" . $select_id . "" != "" . $id . "") {
                            $select .= "<option $selected value=\"$select_id\">" .
                                t($select_fields['title']) .
                                "</option>\n"
                            ;
                        }
                    }
                    $select .= "</select>";

                    print "<tr>\n";

                    print "<td>\n";
                    print "<a class=\"sort-handle\"></a>";
                    print "<input type=\"hidden\" name=\"subcategory_id[]\" value=\"$id\" />\n";
                    print "<input size=\"3\" class=\"form-text\" type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n";
                    print "</td>\n";

                    print "<td>$position" . t($fields['title']) . "</td>\n";

                    print "<td>" . $select . "</td>\n";

                    $url_arguments["id"] = $id;
                    $url_arguments["category"] = $category_name;

                    print "<td>";
                    print "<a href=\"" .
                        Jaris\Uri::url(
                            "admin/categories/subcategories/edit",
                            $url_arguments
                        ) .
                        "\">" .
                        t("Edit") .
                        "</a>"
                    ;
                    print "&nbsp;";
                    print "<a href=\"" .
                        Jaris\Uri::url(
                            "admin/categories/subcategories/delete",
                            $url_arguments
                        ) .
                        "\">" .
                        t("Delete") .
                        "</a>"
                    ;
                    print "</td>";

                    print "</tr>\n";

                    $print_subcategories(
                        $category_name,
                        "$id",
                        $position . "&nbsp;&nbsp;&nbsp;"
                    );
                }
            }
        };

        if (isset($_REQUEST["btnSave"])) {
            $saved = true;

            for ($i = 0; $i < count($_REQUEST["subcategory_id"]); $i++) {
                $subcategory_data = Jaris\Categories::getSubcategory(
                    $_REQUEST["category"],
                    intval($_REQUEST["subcategory_id"][$i])
                );

                $subcategory_data["order"] = $i;

                //Checks if client is trying to move a root parent subcategory
                //to its own subcategory and makes subs category root
                if (
                    $subcategory_data["parent"] == "root" &&
                    $_REQUEST["parent"][$i] != "root"
                ) {
                    $new_parent_subcategory = Jaris\Categories::getSubcategory(
                        $_REQUEST["category"],
                        intval($_REQUEST["parent"][$i])
                    );

                    if (
                        "" . $new_parent_subcategory["parent"] . "" ==
                        "" . $_REQUEST["subcategory_id"][$i] . ""
                    ) {
                        $new_parent_subcategory["parent"] = "root";

                        Jaris\Categories::editSubcategory(
                            $_REQUEST["category"],
                            $new_parent_subcategory,
                            intval($_REQUEST["parent"][$i])
                        );
                    }
                }

                $subcategory_data["parent"] = $_REQUEST["parent"][$i];


                if (
                    !Jaris\Categories::editSubcategory(
                        $_REQUEST["category"],
                        $subcategory_data,
                        intval($_REQUEST["subcategory_id"][$i])
                    )
                ) {
                    Jaris\View::addMessage($_REQUEST["category"]);
                    Jaris\View::addMessage($_REQUEST["subcategory_id"][$i]);
                    $saved = false;
                    break;
                }
            }

            if ($saved) {
                Jaris\View::addMessage(t("Your changes have been saved."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/categories/subcategories",
                ["category" => $_REQUEST["category"]]
            );
        }
    ?>

    <form action="<?php print Jaris\Uri::url("admin/categories/subcategories"); ?>"
          method="post"
    >
        <input type="hidden" name="category"
               value="<?php print $_REQUEST["category"] ?>"
        />

    <?php
        $main_category = Jaris\Categories::get($_REQUEST["category"]);

        print "<table class=\"subcategories\">";

        print "<tr>";
        print "<td class=\"name\">";
        print "<h3>" . t($main_category["name"]) . "</h3>";
        print "</td>\n";
        print "</tr>";

        print "</table>";

        $subcategories_list = Jaris\Data::sort(
            Jaris\Categories::getSubcategories($_REQUEST["category"]),
            "order"
        );

        if (count($subcategories_list) > 0) {
            print "<table class=\"subcategories-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Order") . "</td>\n";
            print "<td>" . t("Title") . "</td>\n";
            print "<td>" . t("Parent") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";

            print "</tr></thead><tbody>\n";

            $print_subcategories($_REQUEST["category"]);

            print "</tbody></table>\n";
        } else {
            print t("No subcategories available.") . "<br />\n";
        }
    ?>

        <div>
            <br />
            <input class="form-submit" type="submit"
                   name="btnSave" value="<?php print t("Save") ?>"
            />
            &nbsp;
            <input class="form-submit" type="submit"
                   name="btnCancel" value="<?php print t("Cancel") ?>"
            />
        </div>
    </form>
    field;

    field: is_system
        1
    field;
row;