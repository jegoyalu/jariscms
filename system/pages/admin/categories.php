<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the categories configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Categories") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_categories"));
    ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".categories-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });
        });
    </script>

    <?php
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.js");
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(t("Create Category"), "admin/categories/add");

        $categories_array = Jaris\Categories::getList();
    ?>

    <form class="categories"
          action="<?php print Jaris\Uri::url("admin/categories"); ?>"
          method="post"
    >

    <?php
        if(isset($_REQUEST["btnSave"]))
        {
            $saved = true;

            for($i = 0; $i < count($_REQUEST["category_name"]); $i++)
            {
                $new_category_data = Jaris\Categories::get(
                    $_REQUEST["category_name"][$i]
                );

                $new_category_data["order"] = $i;

                if(
                    !Jaris\Categories::edit(
                        $_REQUEST["category_name"][$i],
                        $new_category_data
                    )
                )
                {
                    $saved = false;
                    break;
                }
            }

            if($saved)
            {
                Jaris\View::addMessage(t("Your changes have been saved."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/categories");
        }

        print "<table class=\"categories-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Order") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        print "<tbody>\n";

        if($categories_array)
        {
            foreach($categories_array as $machine_name => $fields)
            {
                print "<tr>\n";

                print "<td>";
                print "<a class=\"sort-handle\"></a>";
                print "<input type=\"hidden\" name=\"category_name[]\" value=\"$machine_name\" />";
                print "<input type=\"hidden\" style=\"width: 30px;\" name=\"category_order[]\" value=\"{$fields['order']}\" />";
                print "</td>\n";

                print "<td>" . t($fields["name"]) . "</td>\n";
                print "<td>" . t($fields["description"]) . "</td>\n";

                $edit_url = Jaris\Uri::url(
                    "admin/categories/edit",
                    array("category" => $machine_name)
                );

                $delete_url = Jaris\Uri::url(
                    "admin/categories/delete",
                    array("category" => $machine_name)
                );

                $subcategories_url = Jaris\Uri::url(
                    "admin/categories/subcategories",
                    array("category" => $machine_name)
                );

                $edit_text = t("Edit");
                $delete_text = t("Delete");
                $subcategories_text = t("Subcategories");

                print "<td>";
                print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                print "<a href=\"$delete_url\">$delete_text</a>&nbsp;";
                print "<a href=\"$subcategories_url\">$subcategories_text</a>";
                print "</td>\n";

                print "</tr>\n";
            }
        }

        print "</tbody>\n";

        print "</table>\n";
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
