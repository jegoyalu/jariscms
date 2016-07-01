<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menus configuration page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Menus") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_menus"));
    ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };
    <?php
        $menu_list = Jaris\Menus::getList();

        foreach($menu_list as $menu_name)
        {
            print "$(\".menu-list tbody.$menu_name\").sortable({
                    cursor: 'crosshair',
                    helper: fixHelper,
                    handle: \"a.sort-handle\"
                });";
        }
    ?>
        });
    </script>

    <?php
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.js");
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(t("Create Menu"), "admin/menus/add");
        Jaris\View::addTab(t("Configuration"), "admin/menus/configuration");

        function print_menu_items($menu_name, $parent = "root", $position = "")
        {
            $menu_items_list = Jaris\Data::sort(
                Jaris\Menus::getChildItems($menu_name, $parent),
                "order"
            );

            foreach($menu_items_list as $id => $fields)
            {
                $select = "<select name=\"parent[]\">\n";
                $menu_items_for_parent["root"] = array("title" => "&lt;root&gt;");
                $menu_items_for_parent += Jaris\Menus::getItemsList($menu_name);
                foreach($menu_items_for_parent as $select_id => $select_fields)
                {
                    $selected = "";
                    if("" . $fields["parent"] . "" == "" . $select_id . "")
                    {
                        $selected = "selected";
                    }

                    if("" . $select_id . "" != "" . $id . "")
                    {
                        $select .= "<option $selected value=\"$select_id\">" .
                            t($select_fields['title']) .
                            "</option>\n"
                        ;
                    }
                }
                $select .= "</select>";

                print "<tr>\n";

                print "<td>\n";
                print "<a class=\"sort-handle\"></a>\n";
                print "<input type=\"hidden\" name=\"menu[]\" value=\"$menu_name\" />\n";
                print "<input type=\"hidden\" name=\"item_id[]\" value=\"$id\" />\n";
                print "<input size=\"3\" class=\"form-text\" type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n";
                print "</td>\n";

                print "<td>";
                print "$position<a href=\"" . Jaris\Uri::url($fields["url"]) . "\">" .
                    t($fields['title']) .
                    "</a>"
                ;
                print "</td>\n";

                print "<td>" . $select . "</td>\n";

                $url_arguments["id"] = $id;
                $url_arguments["menu"] = $menu_name;

                print "<td>";
                print "<a href=\"" .
                    Jaris\Uri::url(
                        "admin/menus/edit-item",
                        $url_arguments
                    ) . "\">" .
                    t("Edit") .
                    "</a>"
                ;

                print "&nbsp;";

                print "<a href=\"" .
                    Jaris\Uri::url(
                        "admin/menus/delete-item",
                        $url_arguments
                    ) . "\">" .
                    t("Delete") .
                    "</a>"
                ;
                print "</td>";

                print "</tr>\n";

                print_menu_items(
                    $menu_name,
                    "$id",
                    $position . "&nbsp;&nbsp;&nbsp;"
                );
            }
        }
    ?>

    <form class="menu"
          action="<?php print Jaris\Uri::url("admin/menus"); ?>" method="post"
    >

    <?php
        if(isset($_REQUEST["btnSave"]))
        {
            $saved = true;

            for($i = 0; $i < count($_REQUEST["menu"]); $i++)
            {
                $item_data = Jaris\Menus::getItem(
                    $_REQUEST["item_id"][$i],
                    $_REQUEST["menu"][$i]
                );

                $item_data["order"] = $i;

                //Checks if client is trying to move a root parent menu to
                //its own submenu and makes subs menu root menu
                if(
                    $item_data["parent"] == "root" &&
                    $_REQUEST["parent"][$i] != "root"
                )
                {
                    $new_parent_item = Jaris\Menus::getItem(
                        $_REQUEST["parent"][$i],
                        $_REQUEST["menu"][$i]
                    );

                    if(
                        "" . $new_parent_item["parent"] . "" ==
                        "" . $_REQUEST["item_id"][$i] . ""
                    )
                    {
                        $new_parent_item["parent"] = "root";

                        Jaris\Menus::editItem(
                            $_REQUEST["parent"][$i],
                            $_REQUEST["menu"][$i],
                            $new_parent_item
                        );
                    }
                }

                $item_data["parent"] = $_REQUEST["parent"][$i];


                if(
                    !Jaris\Menus::editItem(
                        $_REQUEST["item_id"][$i],
                        $_REQUEST["menu"][$i],
                        $item_data
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

            Jaris\Uri::go("admin/menus");
        }

        $menu_list = Jaris\Menus::getList();

        print "<table class=\"menu-list\">";

        print "<thead><tr>\n";

        print "<td></td>\n";
        print "<td></td>\n";
        print "<td></td>\n";
        print "<td></td>\n";

        print "</tr></thead>\n";

        foreach($menu_list as $menu_name)
        {
            print "<tbody>\n";
            print "<tr>\n";
            print "<td class=\"name\" colspan=\"1\">\n";

            print "<h3>" . t($menu_name) . "</h3>\n";

            print "</td>\n";

            $add_url = Jaris\Uri::url(
                "admin/menus/add-item",
                array("menu" => $menu_name)
            );

            $rename_menu = Jaris\Uri::url(
                "admin/menus/rename",
                array("current_name" => $menu_name)
            );

            $delete_menu = Jaris\Uri::url(
                "admin/menus/delete",
                array("menu" => $menu_name)
            );

            print "<td class=\"options\" colspan=\"3\">";
            print "<a href=\"$add_url\">" . t("Add Item") . "</a> &nbsp;";
            print "<a href=\"$rename_menu\">" . t("Rename") . "</a> &nbsp;";
            print "<a href=\"$delete_menu\">" . t("Delete") . "</a>";
            print "</td>";

            print "</td>\n";
            print "</tr>";
            print "</tbody>\n";

            $menu_items_list = Jaris\Data::sort(
                Jaris\Menus::getChildItems($menu_name),
                "order"
            );

            if(count($menu_items_list) > 0)
            {
                print "<tbody>\n";
                print "<tr class=\"head\">\n";

                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Title") . "</td>\n";
                print "<td>" . t("Parent") . "</td>\n";
                print "<td>" . t("Operation") . "</td>\n";

                print "</tr>\n";
                print "</tbody>\n";

                print "<tbody class=\"$menu_name items\">\n";
                print_menu_items($menu_name);
                print "</tbody>\n";
            }
            else
            {
                print "<tbody>";
                print "<tr><td colspan=\"4\">";
                print t("No menu items available.");
                print "<br />";
                print "</td></tr>";
                print "</tbody>\n";
            }
        }

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
