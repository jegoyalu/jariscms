<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the types listing for add content.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Content") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("add_content"));

        $types = array();
        $types_array = Jaris\Types::getList();
        $types_array = Jaris\Data::sort($types_array, "order");

        print "<table class=\"types-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Description") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";

        print "</tr></thead>\n";

        $has_a_type_permission = false;

        foreach($types_array as $machine_name => $fields)
        {
            if(
                Jaris\Authentication::hasTypeAccess(
                    $machine_name,
                    Jaris\Authentication::currentUserGroup(),
                    Jaris\Authentication::currentUser()
                )
            )
            {
                print "<tr>\n";

                print "<td>" . t($fields["name"]) . "</td>\n";
                print "<td>" . t($fields["description"]) . "</td>\n";

                $add_url = "";
                if(isset($_REQUEST["uri"]) && trim($_REQUEST["uri"]) != "")
                {
                    $add_url = Jaris\Uri::url(
                        "admin/pages/add",
                        array(
                            "type" => $machine_name,
                            "uri" => trim($_REQUEST["uri"])
                        )
                    );
                }
                else
                {
                    $add_url = Jaris\Uri::url(
                        "admin/pages/add",
                        array("type" => $machine_name)
                    );
                }

                $add_text = t("Add");

                print "<td>
                    <a href=\"$add_url\">$add_text</a>
                   </td>\n";

                print "</tr>\n";

                $has_a_type_permission = true;
            }
        }

        print "</table>\n";

        if(!$has_a_type_permission)
        {
            Jaris\View::addMessage(
                t("You do not have permissions to add content of any type. Ask the administrator for access.")
            );
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
