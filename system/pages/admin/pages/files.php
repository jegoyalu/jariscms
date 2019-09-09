<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content files management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Files") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_files"]);

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }
    ?>
    <script>
        $(document).ready(function() {
            $("#files-select-action").change(function(){
                if($(this).val() == "all"){
                    $("input[name='files[]']").attr("checked", true);
                }
                else if($(this).val() == "none"){
                    $("input[name='files[]']").attr("checked", false);
                }
                $(this).val("");
            });

            $("#files-select-submit").click(function(){
                $("input[name='files[]']").each(function(index, element){
                    if($(element).is(":checked")){
                        var image_hidden = $(
                            '<input type="hidden" name="files[]" value="'
                                + $(element).val()
                                + '" />'
                        );

                        $("#files-select").append(image_hidden);
                    }
                });
            });
        });
    </script>
    <?php
        //Check maximum permitted file upload have not exceed
        $type_settings = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));

        $current_group = Jaris\Authentication::currentUserGroup();

        $maximum_files = $type_settings["uploads"][$current_group]["maximum_files"] != "" ?
            $type_settings["uploads"][$current_group]["maximum_files"]
            :
            "-1"
        ;

        $file_count = count(Jaris\Pages\Files::getList($_REQUEST["uri"]));

        if ($maximum_files == "0") {
            Jaris\View::addMessage(t("File uploads not permitted for this content type."));
        } elseif ($file_count >= $maximum_files && $maximum_files != "-1") {
            Jaris\View::addMessage(t("Maximum file uploads reached."));
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
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        if ($maximum_files == "-1" || $file_count < $maximum_files) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "add_files",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\View::addTab(
                    t("Add File"),
                    "admin/pages/files/add",
                    $arguments,
                    1
                );
            }
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            Jaris\Authentication::groupHasPermission(
                "edit_files",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            $file_count = count($_REQUEST["id"]);

            $saved = true;

            for ($i = 0; $i < $file_count; $i++) {
                $file_id = intval($_REQUEST["id"][$i]);

                $file_data = Jaris\Pages\Files::get(
                    $file_id,
                    $arguments["uri"]
                );

                $file_data["description"] = $_REQUEST["description"][$i];

                if (
                    !Jaris\Pages\Files::edit(
                        $file_id,
                        $file_data,
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

            Jaris\Uri::go("admin/pages/files", $arguments);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/pages/files", $arguments);
        }


        if ($files = Jaris\Pages\Files::getList($arguments["uri"])) {
            print "<form id=\"files-select\" method=\"post\" action=\""
                . Jaris\Uri::url("admin/pages/files/delete-selected")
                . "\">\n"
            ;
            print "<div style=\"float: right;\">";
            print t("Select:") . " <select id=\"files-select-action\">\n";
            print "<option value=\"\">" . t("-Action-") . "</option>\n";
            print "<option value=\"all\">" . t("All") . "</option>\n";
            print "<option value=\"none\">" . t("None") . "</option>\n";
            print "</select>\n";

            print "<input type=\"hidden\" name=\"uri\" value=\"{$_REQUEST["uri"]}\">";
            print " <input id=\"files-select-submit\" type=\"submit\" value=\""
                . t("Delete Selected")
                . "\">\n"
            ;
            print "</div>";

            print "<div style=\"clear: both\"></div>";
            print "</form>";

            print "<hr />";

            print "<form class=\"files\" method=\"post\" action=\""
                . Jaris\Uri::url("admin/pages/files", $arguments)
                . "\" >"
            ;
            print "<input type=\"hidden\" name=\"uri\" "
                . "value=\"{$arguments['uri']}\" />\n"
            ;

            print "<table class=\"files-list\">\n";
            print "<thead>\n";
            print "<tr>\n";
            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Description") . "</td>\n";

            if (
                Jaris\Authentication::groupHasPermission(
                    "delete_files",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                print "<td>" . t("Operation") . "</td>\n";
            }
            print "<td></td>\n";
            print "</tr>";
            print "</thead>\n";

            foreach ($files as $id => $fields) {
                print "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n";

                print "<tr>";

                print "<td>"
                    . "<a title=\"" . t("Click to download") . "\" "
                    . "href=\"" . Jaris\Uri::url("file/" . $arguments["uri"] . "/"
                    . $fields["name"]) . "\">"
                    . "{$fields['name']}"
                    . "</a>"
                    . "</td>"
                ;

                print "<td>"
                    . "<input type=\"text\" name=\"description[]\" "
                    . "value=\"{$fields['description']}\" />"
                    . "</td>"
                ;

                if (
                    Jaris\Authentication::groupHasPermission(
                        "delete_files",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    print "<td>"
                        . "<a href=\"" . Jaris\Uri::url(
                            "admin/pages/files/delete",
                            ["uri" => $_REQUEST["uri"], "id" => $id]
                        )
                        . "\">"
                        . t("Delete")
                        . "</a>"
                        . "</td>"
                    ;
                }

                print "<td>"
                    . "<input type=\"checkbox\" name=\"files[]\" value=\"$id\" />"
                    . "</td>"
                ;

                print "</tr>";
            }

            print "</table>";

            if (
                Jaris\Authentication::groupHasPermission(
                    "edit_files",
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
                    "add_files",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\View::addMessage(
                    t("No file available click Add File to create one.")
                );
            } else {
                Jaris\View::addMessage(t("No file available."));
            }
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
