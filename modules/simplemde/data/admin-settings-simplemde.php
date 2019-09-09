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
        <?php print t("Simple Markdown Editor Settings") ?>
    field;

    field: content
    <style>
        .groups td
        {
            width: auto;
            padding: 5px;
            border-bottom: solid 1px #000;
        }

        .groups thead td
        {
            width: auto;
            font-weight:  bold;
            border-bottom: 0;
        }
    </style>

    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $classes = unserialize(Jaris\Settings::get("teaxtarea_id", "simplemde"));
        $forms_to_display = unserialize(Jaris\Settings::get("forms", "simplemde"));
        $groups = unserialize(Jaris\Settings::get("groups", "simplemde"));
        $disable_editor = unserialize(Jaris\Settings::get("disable_editor", "simplemde"));

        if(isset($_REQUEST["btnSave"], $_REQUEST["group"]))
        {
            $classes[$_REQUEST["group"]] = $_REQUEST["teaxtarea_id"];
            $forms_to_display[$_REQUEST["group"]] = $_REQUEST["forms"];
            $groups[$_REQUEST["group"]] = $_REQUEST["groups"];
            $disable_editor[$_REQUEST["group"]] = $_REQUEST["disable_editor"];

            if(
                Jaris\Settings::save(
                    "teaxtarea_id",
                    serialize($classes),
                    "simplemde"
                )
            )
            {
                Jaris\Settings::save(
                    "forms",
                    serialize($forms_to_display),
                    "simplemde"
                );

                Jaris\Settings::save(
                    "groups",
                    serialize($groups),
                    "simplemde"
                );

                Jaris\Settings::save(
                    "disable_editor",
                    serialize($disable_editor),
                    "simplemde"
                );

                Jaris\View::addMessage(t("Your changes have been saved."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data")
                );
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/simplemde",
                    "simplemde"
                )
            );
        }

        print "<table class=\"groups\">\n";
        print "<thead>\n";
        print "<tr>\n";

        print "<td>\n";
        print t("Groups");
        print "</td>\n";

        print "<td>\n";
        print t("Description");
        print "</td>\n";

        print "<td>\n";
        print "</td>\n";

        print "</tr>\n";
        print "</thead>\n";

        $groups_list = Jaris\Groups::getList();
        $groups_list[] = "guest";

        foreach($groups_list as $group)
        {
            $group_data = Jaris\Groups::get($group);

            print "<tr>\n";

            print "<td>\n";
            print $group_data["name"];
            print "</td>\n";

            print "<td>\n";
            print $group_data["description"];
            print "</td>\n";

            $edit_url = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/settings/simplemde",
                    "simplemde"
                ),
                array("group" => $group)
            );

            print "<td>\n";
            print "<a href=\"$edit_url\">" . t("edit") . "</a>";
            print "</td>\n";

            print "</tr>\n";
        }

        print "</table>";

        print "<br />";

        if(isset($_REQUEST["group"]))
        {
            $parameters["name"] = "simplemde-settings";
            $parameters["class"] = "simplemde-settings";
            $parameters["action"] = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/settings/simplemde",
                    "simplemde"
                )
            );
            $parameters["method"] = "post";

            $fields_enable_simplemde[] = array(
                "type" => "other",
                "html_code" => "<br />"
            );

            $fields_enable_simplemde[] = array(
                "type" => "checkbox",
                "checked" => $groups[$_REQUEST["group"]],
                "name" => "groups",
                "label" => t("Enable simplemde?"),
                "id" => "groups"
            );

            $fieldset[] = array("fields" => $fields_enable_simplemde);

            $fields_pages[] = array(
                "type" => "textarea",
                "name" => "teaxtarea_id",
                "label" => t("Textarea Id:"),
                "id" => "teaxtarea_id",
                "value" => $classes[$_REQUEST["group"]] ?
                    $classes[$_REQUEST["group"]] : "content,pre_content,sub_content",
                "description" => t("List of textarea id's seperated by comma (,).")
            );

            $fields_pages[] = array(
                "type" => "textarea",
                "name" => "forms",
                "label" => t("Form names:"),
                "id" => "forms",
                "value" => $forms_to_display[$_REQUEST["group"]] ?
                    $forms_to_display[$_REQUEST["group"]]
                    :
                    "add-page-pages,edit-page-pages,translate-page,"
                    . "add-page-block,block-page-edit,add-block,block-edit,"
                    . "translate-block, add-page-block-page, duplicate-page-product,"
                    . "edit-page-blog,add-page-blog,"
                    . "add-exam,edit-exam,"
                    . "add-page-product, edit-page-product,"
                    . "add-listing,edit-listing,"
                    . "add-gallery,edit-gallery,"
                    . "add-page-contact-form,edit-page-contact-form,"
                    . "add-page-calendar,edit-page-calendar,"
                    . "add-page-faq,edit-page-faq,"
                    . "add-page-book,edit-page-book,"
                    . "add-page-book-page,edit-page-book-page,"
                    . "add-page-layaway_product,edit-page-layaway_product,"
                    . "realty-add-listing,realty-edit-listing,"
                    . "animated-blocks-add,animated-blocks-edit,"
                    . "listing-blocks-add,listing-blocks-edit"
            );

            $fieldset[] = array(
                "fields" => $fields_pages,
                "name" => "Forms to display",
                "description" => t("List of form names seperated by comma (,).")
            );

            $fields_disable_editor[] = array(
                "type" => "other",
                "html_code" => "<br />"
            );

            $fields_disable_editor[] = array(
                "type" => "checkbox",
                "checked" => $disable_editor[$_REQUEST["group"]],
                "name" => "disable_editor",
                "label" => t("Show disable editor button?"),
                "id" => "disable_editor"
            );

            $fieldset[] = array("fields" => $fields_disable_editor);

            $fields[] = array(
                "type" => "hidden",
                "name" => "group",
                "value" => $_REQUEST["group"]
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnSave",
                "value" => t("Save")
            );

            $fields[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields);

            $group_data = Jaris\Groups::get($_REQUEST["group"]);

            print "<b>" . t("Selected group:") . "</b> " . $group_data["name"];
            print Jaris\Forms::generate($parameters, $fieldset);
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
