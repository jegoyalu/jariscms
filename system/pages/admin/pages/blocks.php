<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content blocks page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Page Blocks") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_content_blocks"));

        if(!isset($_REQUEST["uri"]))
        {
            Jaris\Uri::go("");
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
        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.js");
        Jaris\View::addScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

        $base_url = Jaris\Site::$base_url;

        $page_uri = $_REQUEST["uri"];
        $arguments["uri"] = $page_uri;

        //Tabs
        if(
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }

        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);

        if(
            Jaris\Authentication::groupHasPermission(
                "view_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Blocks"),
                "admin/pages/blocks",
                $arguments
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "view_images",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "view_files",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "translate_languages",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Translate"),
                "admin/pages/translate",
                $arguments
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Delete"),
                "admin/pages/delete",
                $arguments
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "add_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Create Block"),
                "admin/pages/blocks/add",
                $arguments,
                1
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "add_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Create Post Block"),
                "admin/pages/blocks/add/post",
                $arguments,
                1
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "edit_post_settings_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Post Settings"),
                "admin/pages/blocks/post/settings",
                $arguments,
                1
            );
        }
    ?>

    <form class="blocks"
          action="<?php print Jaris\Uri::url("admin/pages/blocks"); ?>" method="post"
    >
        <input type="hidden" name="uri"
               value="<?php print $_REQUEST["uri"] ?>"
        />

    <?php
        if(
            isset($_REQUEST["btnSave"]) &&
            Jaris\Authentication::groupHasPermission(
                "edit_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $saved = true;

            for($i = 0; $i < count($_REQUEST["id"]); $i++)
            {
                $new_block_data = Jaris\Blocks::get(
                    $_REQUEST["id"][$i],
                    $_REQUEST["previous_position"][$i],
                    $page_uri
                );

                $new_block_data["order"] = $i;

                if(
                    !Jaris\Blocks::edit(
                        $_REQUEST["id"][$i],
                        $_REQUEST["previous_position"][$i],
                        $new_block_data,
                        $page_uri
                    )
                )
                {
                    $saved = false;
                    break;
                }

                if(
                    $_REQUEST["previous_position"][$i] !=
                    $_REQUEST["position"][$i]
                )
                {
                    Jaris\Blocks::move(
                        $_REQUEST["id"][$i],
                        $_REQUEST["previous_position"][$i],
                        $_REQUEST["position"][$i],
                        $page_uri
                    );
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

            Jaris\Uri::go("admin/pages/blocks", $arguments);
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

        foreach($block_positions as $block_caption => $block_name)
        {
            print "<tbody>";
            print "<tr><td colspan=\"4\">";
            print "<h3>" . t($block_caption) . "</h3>";
            print "</td></tr>";
            print "</tbody>\n";

            $blocks_list = Jaris\Data::sort(
                Jaris\Blocks::getList($block_name, $page_uri),
                "order"
            );

            if(count($blocks_list) > 0)
            {
                print "<tbody><tr class=\"head\">\n";

                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Description") . "</td>\n";
                print "<td>" . t("Position") . "</td>\n";
                if(
                    Jaris\Authentication::groupHasPermission(
                        "edit_content_blocks",
                        Jaris\Authentication::currentUserGroup()
                    ) ||
                    Jaris\Authentication::groupHasPermission(
                        "delete_content_blocks",
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    print "<td>" . t("Operation") . "</td>\n";
                }

                print "</tr></tbody>\n";

                print "<tbody class=\"$block_name blocks\">\n";

                foreach($blocks_list as $id => $fields)
                {
                    print "<tr>\n";

                    print "<td>\n";
                    print "<a class=\"sort-handle\"></a>\n";
                    print "<input type=\"hidden\" name=\"previous_position[]\" value=\"$block_name\" />\n";
                    print "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n";
                    print "<input type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n";
                    print "</td>\n";

                    print "<td>" . t($fields["description"]) . "</td>\n";

                    print "<td>\n";
                    print "<select name=\"position[]\">\n";
                    foreach($block_positions as $caption => $position)
                    {
                        $selected = $block_name == $position ? " selected" : "";
                        print "<option $selected value=\"$position\">" .
                            $caption .
                            "</option>\n"
                        ;
                    }
                    print "</select>";
                    print "</td>";

                    $url_arguments["uri"] = $page_uri;
                    $url_arguments["id"] = $id;
                    $url_arguments["position"] = $block_name;

                    if(
                        Jaris\Authentication::groupHasPermission(
                            "edit_content_blocks",
                            Jaris\Authentication::currentUserGroup()
                        ) ||
                        Jaris\Authentication::groupHasPermission(
                            "delete_content_blocks",
                            Jaris\Authentication::currentUserGroup()
                        )
                    )
                    {
                        print "<td>";
                        if(
                            Jaris\Authentication::groupHasPermission(
                                "edit_content_blocks",
                                Jaris\Authentication::currentUserGroup()
                            )
                        )
                        {
                            print "<a href=\"" .
                                Jaris\Uri::url(
                                    "admin/pages/blocks/edit",
                                    $url_arguments
                                ) . "\">" .
                                t("Edit") .
                                "</a>&nbsp;"
                            ;
                        }

                        if(
                            Jaris\Authentication::groupHasPermission(
                                "delete_content_blocks",
                                Jaris\Authentication::currentUserGroup()
                            )
                        )
                        {
                            print "<a href=\"" .
                                Jaris\Uri::url(
                                    "admin/pages/blocks/delete",
                                    $url_arguments
                                ) . "\">" .
                                t("Delete") .
                                "</a>"
                            ;
                        }
                        print "</td>";
                    }

                    print "</tr>\n";
                }

                print "</tbody>\n";
            }
            else
            {
                print "<tbody>";
                print "<tr><td colspan=\"4\">";
                print t("No block available.");
                print "</td></tr>";
                print "</tbody>\n";
            }
        }

        print "</table>\n";
    ?>

    <?php if(Jaris\Authentication::groupHasPermission("edit_content_blocks", Jaris\Authentication::currentUserGroup())){ ?>
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
    <?php } ?>

    </form>
    field;

    field: is_system
        1
    field;
row;
