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
        <?php print t("Talents") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("manage_groups_church_attendance")
        );

        if(isset($_REQUEST["action"]))
        {
            if($_REQUEST["action"] == "add")
            {
                church_attendance_talent_add($_REQUEST["label"]);
            }
            elseif($_REQUEST["action"] == "edit")
            {
                foreach($_REQUEST["id"] as $index=>$id)
                {
                    church_attendance_talent_edit(
                        $id,
                        $_REQUEST["label"][$index]
                    );
                }

                Jaris\View::addMessage(t("Talents successfully updated."));
            }
        }

        $elements = church_attendance_talent_list();

        // Add element
        $parameters["class"] = "church-attendance-add-talent";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Uri::get()
        );
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "action",
            "value" => "add"
        );

        $fields[] = array(
            "type" => "text",
            "label" => t("Label:"),
            "name" => "label"
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnAdd",
            "value" => t("Add")
        );

        $fieldset[] = array(
            "name" => t("Add Talent"),
            "fields" => $fields,
            "collapsible" => true,
            "collapsed" => count($elements) > 0
        );

        print Jaris\Forms::generate($parameters, $fieldset);

        // List elements
        if(is_array($elements) && count($elements) > 0)
        {
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
            foreach($elements as $id=>$label)
            {
                print "<tr>";

                print "<td>";
                print '<input type="hidden" name="id[]" value="'.$id.'" />';
                print '<input type="text" name="label[]" value="'.$label.'" style="min-width: 290px" />';
                print "</td>";

                $delete_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-attendance/talents/delete",
                        "church_attendance"
                    ),
                    array("id"=>$id)
                );

                print "<td>";
                print '<a href="'.$delete_url.'">'.t("Delete").'</a>';
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
