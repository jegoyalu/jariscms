<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content types fields listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Content Type Fields") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_types_fields"));
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
        if(!isset($_REQUEST["type"]))
        {
            Jaris\Uri::go("admin/types");
        }

        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(
            t("Add Field"),
            "admin/types/fields/add",
            array("type_name" => $_REQUEST["type"])
        );

        Jaris\View::addTab(t("Manage Types"), "admin/types");
    ?>

    <form class="categories"
          action="<?php print Jaris\Uri::url("admin/types/fields"); ?>"
          method="post"
    >
        <input type="hidden" name="type" value="<?php print $_REQUEST["type"] ?>" />

    <?php
    if(isset($_REQUEST["btnSave"]))
    {
        $saved = true;

        for($i = 0; $i < count($_REQUEST["id"]); $i++)
        {
            $field_id = intval($_REQUEST["id"][$i]);

            $new_field_data = Jaris\Fields::get(
                $field_id,
                $_REQUEST["type"]
            );

            $new_field_data["position"] = $i;

            if(
                !Jaris\Fields::edit(
                    $field_id,
                    $new_field_data,
                    $_REQUEST["type"]
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

        Jaris\Uri::go("admin/types/fields", array("type" => $_REQUEST["type"]));
    }

    $fields_array = Jaris\Fields::getList($_REQUEST["type"]);

    if(!$fields_array)
    {
        print "<h3>" . t("No fields available click on Add Field to create one.") . "</h3>";
    }
    else
    {
        print "<table class=\"types-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Order") . "</td>\n";
        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        print "<tbody>\n";

        foreach($fields_array as $id => $fields)
        {
            print "<tr>\n";

            print "<td>" .
                "<a class=\"sort-handle\"></a>" .
                "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />" .
                "</td>\n";

            print "<td>" . t($fields["name"]) . "</td>\n";

            print "<td>" . t($fields["description"]) . "</td>\n";

            $edit_url = Jaris\Uri::url(
                "admin/types/fields/edit",
                array("id" => $id, "type_name" => $_REQUEST["type"])
            );

            $delete_url = Jaris\Uri::url(
                "admin/types/fields/delete",
                array("id" => $id, "type_name" => $_REQUEST["type"])
            );

            $edit_text = t("Edit");
            $delete_text = t("Delete");

            print "<td>
                <a href=\"$edit_url\">$edit_text</a>&nbsp;
                <a href=\"$delete_url\">$delete_text</a>
               </td>\n";

            print "</tr>\n";
        }

        print "</tbody>\n";

        print "</table>\n";
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
