<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the blocks configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Blocks") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_blocks"]);
    ?>
    <script>
        $(document).ready(function() {
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $(".blocks-list tbody.header").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".blocks-list tbody.left").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".blocks-list tbody.right").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".blocks-list tbody.center").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".blocks-list tbody.footer").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $(".blocks-list tbody.none").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });
        });
    </script>

    <?php
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        Jaris\View::addTab(t("Create Block"), "admin/blocks/add");
    ?>

    <form class="blocks" action="<?php print Jaris\Uri::url("admin/blocks"); ?>"
          method="post"
    >

        <?php
            if (isset($_REQUEST["btnSave"])) {
                $saved = true;

                for ($i = 0; $i < count($_REQUEST["id"]); $i++) {
                    $new_block_data = Jaris\Blocks::get(
                        intval($_REQUEST["id"][$i]),
                        $_REQUEST["previous_position"][$i]
                    );

                    $new_block_data["order"] = $i;

                    if (
                        !Jaris\Blocks::edit(
                            intval($_REQUEST["id"][$i]),
                            $_REQUEST["previous_position"][$i],
                            $new_block_data
                        )
                    ) {
                        $saved = false;
                        break;
                    }

                    if (
                        $_REQUEST["previous_position"][$i] !=
                        $_REQUEST["position"][$i]
                    ) {
                        Jaris\Blocks::move(
                            intval($_REQUEST["id"][$i]),
                            $_REQUEST["previous_position"][$i],
                            $_REQUEST["position"][$i]
                        );
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

                Jaris\Uri::go("admin/blocks");
            }

            $block_positions[t("Header")] = "header";
            $block_positions[t("Left")] = "left";
            $block_positions[t("Right")] = "right";
            $block_positions[t("Center")] = "center";
            $block_positions[t("Footer")] = "footer";
            $block_positions[t("None")] = "none";

            print "<table class=\"blocks-list\">\n";

            print "<thead><tr>\n";

            print "<td></td>\n";
            print "<td></td>\n";
            print "<td></td>\n";
            print "<td></td>\n";

            print "</tr></thead>\n";

            foreach ($block_positions as $block_caption => $block_name) {
                print "<tbody><tr><td colspan=\"4\"><h3>" .
                    t($block_caption) .
                    "</h3></td></tr></tbody>\n"
                ;

                $blocks_list = Jaris\Data::sort(
                    Jaris\Blocks::getList($block_name),
                    "order"
                );

                if (count($blocks_list) > 0) {
                    print "<tbody><tr class=\"head\">\n";

                    print "<td>" . t("Order") . "</td>\n";
                    print "<td>" . t("Description") . "</td>\n";
                    print "<td>" . t("Position") . "</td>\n";
                    print "<td>" . t("Operation") . "</td>\n";

                    print "</tr></tbody>\n";

                    print "<tbody class=\"$block_name blocks\">\n";

                    foreach ($blocks_list as $id => $fields) {
                        print "<tr>\n";

                        print "<td>\n";
                        print "<a class=\"sort-handle\"></a>\n";
                        print "<input type=\"hidden\" name=\"previous_position[]\" value=\"$block_name\" />\n";
                        print "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n";
                        print "<input size=\"3\" class=\"form-text\" type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n";
                        print "</td>\n";

                        print "<td>" . t($fields["description"]) . "</td>\n";

                        print "<td>\n";
                        print "<select name=\"position[]\">\n";

                        foreach ($block_positions as $caption => $position) {
                            $selected = $block_name == $position ?
                                " selected" :
                                ""
                            ;

                            print "<option $selected value=\"$position\">" .
                                $caption .
                                "</option>\n"
                            ;
                        }
                        print "</select>";
                        print "</td>";

                        $url_arguments["id"] = $id;
                        $url_arguments["position"] = $block_name;

                        print "<td>";

                        print "<a href=\"" .
                            Jaris\Uri::url("admin/blocks/edit", $url_arguments) .
                            "\">" .
                            t("Edit") .
                            "</a>"
                        ;

                        if (!$fields["is_system"]) {
                            print "&nbsp;";
                            print "<a href=\"" .
                                Jaris\Uri::url("admin/blocks/delete", $url_arguments) .
                                "\">" .
                                t("Delete") .
                                "</a>"
                            ;
                        }

                        print "</td>";

                        print "</tr>\n";
                    }
                    print "</tbody>\n";
                } else {
                    print "<tbody>";
                    print "<tr><td colspan=\"4\">";
                    print t("No block available.");
                    print "</td></tr>";
                    print "</tbody>\n";
                }
            }

            print "</table>\n";
        ?>

        <br />

        <div>
            <input class="form-submit" type="submit" name="btnSave"
                   value="<?php print t("Save") ?>"
            />
            &nbsp;
            <input class="form-submit" type="submit" name="btnCancel"
                   value="<?php print t("Cancel") ?>"
            />
        </div>
    </form>
    field;

    field: is_system
        1
    field;
row;
