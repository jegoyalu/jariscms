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
        <?php print t("Member Groups") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_groups_church_attendance"]
        );

        if (isset($_REQUEST["action"])) {
            if ($_REQUEST["action"] == "add") {
                church_attendance_group_add($_REQUEST["label"]);
            } elseif ($_REQUEST["action"] == "edit") {
                foreach ($_REQUEST["id"] as $index=>$id) {
                    if ($id == "5") {
                        continue;
                    }

                    church_attendance_group_edit(
                        $id,
                        $_REQUEST["label"][$index]
                    );
                }

                Jaris\View::addMessage(t("Groups successfully updated."));
            }
        }

        $elements = church_attendance_group_list();

        // Add element
        $parameters["class"] = "church-attendance-add-group";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Uri::get()
        );
        $parameters["method"] = "post";

        $fields[] = [
            "type" => "hidden",
            "name" => "action",
            "value" => "add"
        ];

        $fields[] = [
            "type" => "text",
            "label" => t("Label:"),
            "name" => "label"
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "btnAdd",
            "value" => t("Add")
        ];

        $fieldset[] = [
            "name" => t("Add Group"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => count($elements) > 0
        ];

        print Jaris\Forms::generate($parameters, $fieldset);

        // List elements
        if (is_array($elements) && count($elements) > 0) {
            print '<form action="'.Jaris\Uri::url(Jaris\Uri::get()).'" method="POST">';
            print '<input type="hidden" name="action" value="edit" />';
            print "<table class=\"navigation-list\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Label") . "</td>";
            print "<td>" . t("Operation") . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";
            foreach ($elements as $id=>$label) {
                $readonly = "";

                if ($id == 5) {
                    $readonly .= "readonly=\"readonly\"";
                }

                print "<tr>";

                print "<td>";
                print '<input type="hidden" name="id[]" value="'.$id.'" />';
                print '<input '.$readonly.' type="text" name="label[]" value="'.$label.'" style="min-width: 290px" />';
                print "</td>";

                $delete_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-attendance/groups/delete",
                        "church_attendance"
                    ),
                    ["id"=>$id]
                );

                print "<td>";
                if ($id > 5) {
                    print '<a href="'.$delete_url.'">'.t("Delete").'</a>';
                }
                print "</td>";

                print "</tr>";
            }
            print "</tbody>";

            print "</table>";
            print '<hr /><input type="submit" value="'.t("Save").'" />';
            print '</form>';
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
