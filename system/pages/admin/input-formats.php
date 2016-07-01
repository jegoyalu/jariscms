<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the input formats configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Input Formats") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_input_formats"));

        if(
            Jaris\Authentication::groupHasPermission(
                "add_input_formats",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Create Input Format"),
                "admin/input-formats/add"
            );
        }

        $input_formats_array = array();
        $input_formats_array = Jaris\InputFormats::getList();

        print "<table class=\"types-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";

        if(
            Jaris\Authentication::groupHasPermission(
                "edit_input_formats",
                Jaris\Authentication::currentUserGroup()
            ) ||
            Jaris\Authentication::groupHasPermission(
                "delete_input_formats",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            print "<td>" . t("Operation") . "</td>\n";
        }

        print "</tr></thead>\n";

        foreach($input_formats_array as $machine_name => $fields)
        {
            print "<tr>\n";

            print "<td>" . t($fields["name"]) . "</td>\n";
            print "<td>" . t($fields["description"]) . "</td>\n";

            $edit_url = Jaris\Uri::url(
                "admin/input-formats/edit",
                array("input_format" => $machine_name)
            );

            $delete_url = Jaris\Uri::url(
                "admin/input-formats/delete",
                array("input_format" => $machine_name)
            );

            $edit_text = t("Edit");
            $delete_text = t("Delete");

            if(
                Jaris\Authentication::groupHasPermission(
                    "edit_input_formats",
                    Jaris\Authentication::currentUserGroup()
                ) ||
                Jaris\Authentication::groupHasPermission(
                    "delete_input_formats",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                print "<td>";
                if(
                    Jaris\Authentication::groupHasPermission(
                        "edit_input_formats",
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                }

                if(
                    Jaris\Authentication::groupHasPermission(
                        "delete_input_formats",
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    print "<a href=\"$delete_url\">$delete_text</a>";
                }
                print "</td>\n";
            }

            print "</tr>\n";
        }

        print "</table>\n";

        if(count($input_formats_array) <= 0)
        {
            Jaris\View::addMessage(t("No custom input formats available."));
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
