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
        <?php print t("Contact Form Fields") ?>
    field;

    field: content
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
        Jaris\Authentication::protectedPage(["edit_content"]);

        Jaris\View::addSystemScript("jquery-ui/jquery.ui.js");
        Jaris\View::addSystemScript("jquery-ui/jquery.ui.touch-punch.min.js");

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $arguments = ["uri" => $_REQUEST["uri"]];

        Jaris\View::addTab(
            t("Fields"),
            Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
            $arguments
        );

        Jaris\View::addTab(
            t("Add Field"),
            Jaris\Modules::getPageUri("admin/pages/contact-form/fields/add", "contact"),
            $arguments,
            1
        );

        //Tabs
        if (Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        if (Jaris\Authentication::groupHasPermission("view_content_blocks", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_images", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("view_files", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("translate_languages", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if (Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }
    ?>

    <form class="contact-form-fields" method="post"
          action="<?php print Jaris\Uri::url(Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact")); ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />

    <?php
        if (isset($_REQUEST["btnSave"])) {
            $saved = true;

            for ($i = 0; $i < count($_REQUEST["id"]); $i++) {
                $new_field_data = contact_get_field_data(
                    $_REQUEST["id"][$i],
                    $_REQUEST["uri"]
                );

                $new_field_data["position"] = $i;

                if (
                    !contact_edit_field(
                        $_REQUEST["id"][$i],
                        $new_field_data,
                        $_REQUEST["uri"]
                    )
                ) {
                    $saved = false;
                    break;
                }
            }

            if ($saved) {
                Jaris\View::addMessage(t("Your changes have been saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
                ["uri" => $_REQUEST["uri"]]
            );
        }

        $fields_array = contact_get_fields($_REQUEST["uri"]);

        if (!$fields_array) {
            print "<h3>" .
                t("No fields available click on Add Field to create one.") .
                "</h3>"
            ;
        } else {
            print "<table class=\"types-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Order") . "</td>\n";
            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Description") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";

            print "</tr></thead>\n";

            print "<tbody>\n";

            foreach ($fields_array as $id => $fields) {
                print "<tr>\n";

                print "<td>" .
                    "<a class=\"sort-handle\"></a>" .
                    "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />" .
                    "<input type=\"hidden\" name=\"position[]\" value=\"{$fields['position']}\" />" .
                    "</td>\n";

                print "<td>" . t($fields["name"]) . "</td>\n";

                print "<td>" . t($fields["description"]) . "</td>\n";

                $edit_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/contact-form/fields/edit",
                        "contact"
                    ),
                    ["id" => $id, "uri" => $_REQUEST["uri"]]
                );

                $delete_url = Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/contact-form/fields/delete",
                        "contact"
                    ),
                    ["id" => $id, "uri" => $_REQUEST["uri"]]
                );

                $edit_text = t("Edit");
                $delete_text = t("Delete");

                print "<td>
                    <a href=\"$edit_url\">$edit_text</a>&nbsp;
                    <a href=\"$delete_url\">$delete_text</a>
                   </td>\n"
                ;

                print "</tr>\n";
            }

            print "</tbody>\n";

            print "</table>\n";
        }
    ?>

        <div>
            <br />
            <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
            &nbsp;
            <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
    </form>
    field;

    field: is_system
        1
    field;
row;
