<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content images management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Images") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_images"]);

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
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

            $(".images-list tbody").sortable({
                cursor: 'crosshair',
                helper: fixHelper,
                handle: "a.sort-handle"
            });

            $("#images-select-action").change(function(){
                if($(this).val() == "all"){
                    $("input[name='images[]']").attr("checked", true);
                }
                else if($(this).val() == "none"){
                    $("input[name='images[]']").attr("checked", false);
                }
                $(this).val("");
            });

            $("#images-select-submit").click(function(){
                $("input[name='images[]']").each(function(index, element){
                    if($(element).is(":checked")){
                        var image_hidden = $(
                            '<input type="hidden" name="images[]" value="'
                                + $(element).val()
                                + '" />'
                        );

                        $("#images-select").append(image_hidden);
                    }
                });
            });
        });
    </script>
    <?php
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        //Check maximum permitted image upload have not exceed
        $type_settings = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));

        $current_group = Jaris\Authentication::currentUserGroup();

        $maximum_images = $type_settings["uploads"][$current_group]["maximum_images"] != "" ?
            $type_settings["uploads"][$current_group]["maximum_images"]
            :
            "-1"
        ;

        $image_count = count(Jaris\Pages\Images::getList($_REQUEST["uri"], false));

        if ($maximum_images == "0") {
            Jaris\View::addMessage(t("Image uploads not permitted for this content type."));
        } elseif ($image_count >= $maximum_images && $maximum_images != "-1") {
            Jaris\View::addMessage(t("Maximum image uploads reached."));
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        //Tabs
        if (
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }

        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);

        if (
            Jaris\Authentication::groupHasPermission(
                "view_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "view_images",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "view_files",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "translate_languages",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(
                t("Translate"),
                "admin/pages/translate",
                $arguments
            );
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(
                t("Delete"),
                "admin/pages/delete",
                $arguments
            );
        }

        if ($maximum_images == "-1" || $image_count < $maximum_images) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "add_images",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\View::addTab(
                    t("Add Image"),
                    "admin/pages/images/add",
                    $arguments,
                    1
                );
            }
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            Jaris\Authentication::groupHasPermission(
                "edit_images",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            $image_count = count($_REQUEST["id"]);

            $saved = true;

            for ($i = 0; $i < $image_count; $i++) {
                $image_id = intval($_REQUEST["id"][$i]);

                $image_data = Jaris\Pages\Images::get(
                    $image_id,
                    $arguments["uri"]
                );

                $image_data["description"] = $_REQUEST["description"][$i];
                $image_data["order"] = $i;

                if ($_REQUEST["disabled"][$i]) {
                    $image_data["disabled"] = true;
                } elseif (isset($image_data["disabled"])) {
                    unset($image_data["disabled"]);
                }

                if (
                    !Jaris\Pages\Images::edit(
                        $image_id,
                        $image_data,
                        $arguments["uri"]
                    )
                ) {
                    $saved = false;
                    break;
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

            Jaris\Uri::go("admin/pages/images", $arguments);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/pages/images", $arguments);
        }


        if ($images = Jaris\Pages\Images::getList($arguments["uri"], false)) {
            print "<form id=\"images-select\" method=\"post\" "
                . "action=\"" . Jaris\Uri::url("admin/pages/images/delete-selected")
                . "\">\n"
            ;
            print "<div style=\"float: right;\">";
            print t("Select:") . " <select id=\"images-select-action\">\n";
            print "<option value=\"\">" . t("-Action-") . "</option>\n";
            print "<option value=\"all\">" . t("All") . "</option>\n";
            print "<option value=\"none\">" . t("None") . "</option>\n";
            print "</select>\n";

            print "<input type=\"hidden\" name=\"uri\" "
                . "value=\"{$_REQUEST["uri"]}\">"
            ;
            print " <input id=\"images-select-submit\" type=\"submit\" "
                . "value=\"" . t("Delete Selected") . "\">\n"
            ;
            print "</div>";

            print "<div style=\"clear: both\"></div>";
            print "</form>";

            print "<hr />";

            print "<form class=\"images\" method=\"post\" "
                . "action=\"" . Jaris\Uri::url("admin/pages/images", $arguments)
                . "\" >"
            ;
            print "<input type=\"hidden\" name=\"uri\" "
                . "value=\"{$arguments['uri']}\" />\n"
            ;

            print "<table class=\"images-list\">\n";
            print "<thead>\n";
            print "<tr>\n";

            print "<td>" . t("Order") . "</td>\n";
            print "<td>" . t("Thumbnail") . "</td>\n";
            print "<td>" . t("Description") . "</td>\n";
            print "<td>" . t("Disabled") . "</td>\n";

            if (
                Jaris\Authentication::groupHasPermission(
                    "delete_images",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                print "<td>" . t("Operation") . "</td>\n";
            }

            print "<td></td>\n";

            print "</tr>";
            print "</thead>\n";

            print "<tbody>\n";

            foreach ($images as $id => $fields) {
                if ($fields['order'] == "") {
                    $fields['order'] = 0;
                }

                print "<tr>";
                $image_size = ["w" => "100"];

                print "<td>";
                print "<a class=\"sort-handle\"></a>";
                print "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />";
                print "<input type=\"hidden\" name=\"order[]\" "
                    . "value=\"{$fields['order']}\" />"
                ;
                print "</td>";

                print "<td>";
                print "<a title=\"" . t("Click to enlarge") . "\" href=\"" .
                    Jaris\Uri::url(
                        "image/" . $arguments["uri"] .
                        "/" . $fields["name"]
                    ) . "\">" .
                    "<img src=\"" .
                    Jaris\Uri::url(
                        "image/" . $arguments["uri"] . "/" . $fields["name"],
                        $image_size
                    ) . "\" /></a>"
                ;
                print "</td>";

                print "<td>";
                print "<input type=\"text\" name=\"description[]\" "
                    . "value=\"{$fields['description']}\" />"
                ;
                print "</td>";

                $disabled_checked = "";
                if (isset($fields["disabled"])) {
                    $disabled_checked .= "checked=\"checked\"";
                }

                print "<td>"
                    . "<input type=\"checkbox\" name=\"disabled[]\" $disabled_checked />"
                    . "</td>"
                ;

                if (
                    Jaris\Authentication::groupHasPermission(
                        "delete_images",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    print "<td>";
                    print "<a href=\"" .
                        Jaris\Uri::url(
                            "admin/pages/images/delete",
                            ["uri" => $_REQUEST["uri"], "id" => $id]
                        ) . "\">" . t("Delete") .
                        "</a>"
                    ;
                    print "</td>";
                }

                print "<td>"
                    . "<input type=\"checkbox\" name=\"images[]\" value=\"$id\" />"
                    . "</td>"
                ;

                print "</tr>";
            }

            print "</tbody>\n";

            print "</table>";

            if (
                Jaris\Authentication::groupHasPermission(
                    "edit_images",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                print "<input type=\"submit\" name=\"btnSave\" "
                    . "value=\"" . t("Save") . "\" /> &nbsp"
                ;

                print "<input type=\"submit\" name=\"btnCancel\" "
                    . "value=\"" . t("Cancel") . "\" />"
                ;
            }

            print "</form>";
        } else {
            if (
                Jaris\Authentication::groupHasPermission(
                    "add_images",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\View::addMessage(
                    t("No images available click Add Image to create one.")
                );
            } else {
                Jaris\View::addMessage(t("No images available."));
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
