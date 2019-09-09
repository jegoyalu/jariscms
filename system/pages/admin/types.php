<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the types configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Types") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_types"));
    ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".types-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });
        });
    </script>

    <?php
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(t("Create Type"), "admin/types/add");

        $types = array();
        $types_array = Jaris\Types::getList();
        $types_array = Jaris\Data::sort($types_array, "order");
    ?>

    <form class="types"
          action="<?php print Jaris\Uri::url("admin/types"); ?>"
          method="post"
    >

    <?php
        if(isset($_REQUEST["btnSave"]))
        {
            $saved = true;

            for($i = 0; $i < count($_REQUEST["type_name"]); $i++)
            {
                $new_type_data = Jaris\Types::get($_REQUEST["type_name"][$i]);
                $new_type_data["order"] = $i;

                if(!Jaris\Types::edit($_REQUEST["type_name"][$i], $new_type_data))
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

            Jaris\Uri::go("admin/types");
        }

        print "<table class=\"types-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Order") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        print "<tbody>\n";

        foreach($types_array as $machine_name => $fields)
        {
            print "<tr>\n";

            $order = !empty($fields['order']) ? $fields['order'] : "0";

            print "<td>" .
                "<a class=\"sort-handle\"></a>" .
                "<input type=\"hidden\" name=\"type_name[]\" value=\"$machine_name\" />" .
                "<input type=\"hidden\" style=\"width: 30px;\" name=\"type_order[]\" value=\"$order\" />" .
                "</td>\n";

            print "<td>" . t($fields["name"]) . "</td>\n";

            print "<td>" . t($fields["description"]) . "</td>\n";

            $edit_url = Jaris\Uri::url(
                "admin/types/edit",
                array("type" => $machine_name)
            );

            $fields_url = Jaris\Uri::url(
                "admin/types/fields",
                array("type" => $machine_name)
            );

            $delete_url = Jaris\Uri::url(
                "admin/types/delete",
                array("type" => $machine_name)
            );

            $edit_text = t("Edit");
            $fields_text = t("Fields");
            $delete_text = t("Delete");

            print "<td>
                <a href=\"$edit_url\">$edit_text</a>&nbsp;
                <a href=\"$fields_url\">$fields_text</a>&nbsp;
                <a href=\"$delete_url\">$delete_text</a>
               </td>\n";

            print "</tr>\n";
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
