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
    <?php
        if (empty($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        $type_data = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));

        print t("Edit") . " " . t($type_data["name"]);
    ?>
    field;

    field: content
    <script type="text/javascript">
        row_id = 1;

        $(document).ready(function() {
            $("#add-item").click(function() {

                row = "<tr style=\"width: 100%\" id=\"table-row-" + row_id + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"subject_title[]\" /></td>";
                row += "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"subject_to[]\" /></td>";
                row += "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(" + row_id + ")\"><?php print t("remove") ?></a></td>";
                row += "</tr>";

                $("#items-table > tbody").append($(row));

                row_id++;
            });
        });

        function remove_row(id)
        {
            $("#table-row-" + id).fadeOut("slow", function() {
                $(this).remove();
            });
        }
    </script>

    <?php
        Jaris\Authentication::protectedPage(["edit_content"]);

        if (
            !Jaris\Pages::userIsOwner(
                trim($_REQUEST["actual_uri"]) != "" ?
                    $_REQUEST["actual_uri"]
                    :
                    $_REQUEST["uri"]
            )
        ) {
            Jaris\Authentication::protectedPage();
        }

        //Check if client trying to edit system page and exit
        if (Jaris\Pages::isSystem($_REQUEST["uri"]) && !isset($_REQUEST["actual_uri"])) {
            Jaris\View::addMessage(
                t("The content you was trying to edit is a system page."),
                "error"
            );

            Jaris\Uri::go("admin/pages");
        }

        //Get page data
        $page_data = [];

        if (isset($_REQUEST["actual_uri"])) {
            $page_data = Jaris\Pages::get($_REQUEST["actual_uri"]);
        } else {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);
        }

        //If page has no type defaults to 'page' type
        $current_type = trim($page_data["type"]);
        if ($current_type == "") {
            $current_type = "pages";
        }

        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-page-$current_type")
        ) {
            //Check if client is trying to submit content to a
            //system page sending variables thru GET
            if (Jaris\Pages::isSystem($_REQUEST["actual_uri"])) {
                Jaris\View::addMessage(
                    t("The content you was trying to edit is a system page."),
                    "error"
                );

                Jaris\Uri::go("admin/pages");
            }

            //Trim uri spaces
            $_REQUEST["uri"] = trim($_REQUEST["uri"]);
            $_REQUEST["actual_uri"] = trim($_REQUEST["actual_uri"]);

            $fields = Jaris\Pages::get($_REQUEST["actual_uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];

            $subjects = [];
            if (is_array($_REQUEST["subject_title"])) {
                foreach ($_REQUEST["subject_title"] as $subject_position => $subject_value) {
                    $subjects[$subject_value] = $_REQUEST["subject_to"][$subject_position];
                }
            }

            $fields["subjects"] = serialize($subjects);
            $fields["mail_recipient"] = $_REQUEST["mail_recipient"];
            $fields["mail_carbon_copy"] = $_REQUEST["mail_carbon_copy"];
            $fields["message_archive"] = $_REQUEST["message_archive"];
            $fields["mail_autoresponse"] = $_REQUEST["mail_autoresponse"];
            $fields["mail_autoresponse_subject"] = $_REQUEST["mail_autoresponse_subject"];
            $fields["mail_autoresponse_message"] = $_REQUEST["mail_autoresponse_message"];

            if (Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup())) {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            if (Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup())) {
                $fields["groups"] = $_REQUEST["groups"];

                $users = explode(",", $_REQUEST["users"]);

                if (count($users) > 0) {
                    foreach ($users as $user_position=>$username) {
                        $users[$user_position] = trim($username);
                    }
                }

                $fields["users"] = $users;
            }

            if (Jaris\Authentication::groupHasPermission("select_type_content", Jaris\Authentication::currentUserGroup())) {
                $fields["type"] = $_REQUEST["type"];
            }

            $categories = [];
            $categories_list = Jaris\Categories::getList($fields["type"]);

            if ($categories_list) {
                foreach ($categories_list as $machine_name => $values) {
                    if (isset($_REQUEST[$machine_name])) {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }

            $fields["categories"] = $categories;

            if (Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup())) {
                $fields["input_format"] = $_REQUEST["input_format"];
            }

            $fields["last_edit_by"] = Jaris\Authentication::currentUser();
            $fields["last_edit_date"] = time();

            Jaris\Fields::appendFields($fields["type"], $fields);

            if (
                !Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()) ||
                $_REQUEST["uri"] == ""
            ) {
                $_REQUEST["uri"] = Jaris\Types::generateURI(
                    $fields["type"],
                    $fields["title"],
                    $fields["author"]
                );
            }

            if (Jaris\Pages::edit($_REQUEST["actual_uri"], $fields)) {
                //Update all translations
                $new_page_data = Jaris\Pages::get($_REQUEST["actual_uri"]);
                foreach (Jaris\Language::getInstalled() as $code => $name) {
                    $translation_path = Jaris\Language::dataTranslate(Jaris\Pages::getPath($_REQUEST["actual_uri"]), $code);
                    $original_path = Jaris\Pages::getPath($_REQUEST["actual_uri"]);

                    if ($translation_path != $original_path) {
                        $translation_data = Jaris\Pages::get($_REQUEST["actual_uri"], $code);

                        $new_page_data["title"] = $translation_data["title"];
                        $new_page_data["content"] = $translation_data["content"];
                        $new_page_data["description"] = $translation_data["description"];
                        $new_page_data["keywords"] = $translation_data["keywords"];

                        Jaris\Translate::page($_REQUEST["actual_uri"], $new_page_data, $code);
                    }
                }

                //Move page to new location
                if ($_REQUEST["actual_uri"] != $_REQUEST["uri"]) {
                    Jaris\Pages::move($_REQUEST["actual_uri"], $_REQUEST["uri"]);

                    //Also move its translations on the language directory
                    if (Jaris\Translate::movePage($_REQUEST["actual_uri"], $_REQUEST["uri"])) {
                        Jaris\View::addMessage(t("Translations repositioned."));
                    } else {
                        Jaris\View::addMessage(Jaris\System::errorMessage("translations_not_moved"), "error");
                    }
                }

                Jaris\View::addMessage(t("Your changes have been successfully saved."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/pages/edit", ["uri" => $_REQUEST["uri"]]);
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go("admin/pages/edit", ["uri" => $_REQUEST["actual_uri"]]);
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

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
        if ($page_data["message_archive"]) {
            Jaris\View::addTab(
                t("Messages Archive"),
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                $arguments
            );
        }
        if (Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup())) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $parameters["name"] = "edit-page-$current_type";
        $parameters["class"] = "edit-page-$current_type";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/contact-form/edit", "contact")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList($page_data["type"]);

        if ($categories) {
            $fields_categories = Jaris\Categories::generateFields(
                $page_data["categories"],
                "",
                $page_data["type"]
            );

            $fieldset[] = [
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            ];
        }

        $fields[] = [
            "type" => "hidden",
            "name" => "actual_uri",
            "value" => $_REQUEST["actual_uri"] ?
                $_REQUEST["actual_uri"]
                :
                $_REQUEST["uri"]
        ];

        $fields[] = [
            "type" => "text",
            "name" => "title",
            "value" => $page_data["title"],
            "label" => Jaris\Types::getLabel($page_data["type"], "title_label"),
            "id" => "title",
            "required" => true,
            "description" => Jaris\Types::getLabel($page_data["type"], "title_description")
        ];

        $fields[] = [
            "type" => "textarea",
            "name" => "content",
            "value" => $page_data["content"],
            "label" => Jaris\Types::getLabel($page_data["type"], "content_label"),
            "id" => "content",
            "description" => Jaris\Types::getLabel($page_data["type"], "content_description")
        ];

        $fieldset[] = ["fields" => $fields];

        $subject_html = "<table id=\"items-table\" style=\"width: 100%\">";
        $subject_html .= "<thead>";
        $subject_html .= "<tr>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Subject") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Recipient") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"></td>";
        $subject_html .= "</tr>";
        $subject_html .= "</thead>";
        $subject_html .= "<tbody>";

        $page_data["subjects"] = unserialize($page_data["subjects"]);

        $i = 0;
        foreach ($page_data["subjects"] as $subject_title => $subject_to) {
            $subject_html .= "<tr id=\"table-row-$i\">";
            $subject_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"subject_title[]\" value=\"$subject_title\" /></td>";
            $subject_html .= "<td style=\"width: auto\"><input style=\"width: 90%\" type=\"text\" name=\"subject_to[]\" value=\"$subject_to\" /></td>";
            $subject_html .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row($i)\">" . t("remove") . "</a></td>";
            $subject_html .= "</tr>";

            $i++;
        }

        $subject_html .= "</tbody>";
        $subject_html .= "</table>";
        $subject_html .= "<a id=\"add-item\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add subject") . "</a>";
        $subject_html .= "<script>row_id=$i;</script>";

        $fields_subject[] = ["type" => "other", "html_code" => $subject_html];

        $fieldset[] = [
            "name" => t("Subjects"),
            "fields" => $fields_subject,
            "collapsible" => true,
            "description" => t("Optional list of selectable subjects by the visitor with alternate mail recipients.")
        ];

        $fields_recipient[] = [
            "type" => "text",
            "name" => "mail_recipient",
            "value" => $page_data["mail_recipient"],
            "label" => t("Mail recipient"),
            "id" => "title",
            "required" => true,
            "description" => t("The default recipient to receive the email. This value gets overriden by the recipient entered on the list of subjects."),
            "required" => true
        ];

        $fields_recipient[] = [
            "type" => "textarea",
            "name" => "mail_carbon_copy",
            "value" => $page_data["mail_carbon_copy"],
            "label" => t("Carbon copy recipients"),
            "id" => "title",
            "description" => t("A comma seperated list of emails to receive a copy. For example: email_1@domain.com, email_2@domain.com")
        ];

        $fields_recipient[] = [
            "type" => "radio",
            "name" => "message_archive",
            "label" => t("Archive Messages?"),
            "value" => [t("No") => false, t("Yes") => true],
            "checked" => isset($_REQUEST["message_archive"]) ?
                $_REQUEST["message_archive"] : $page_data["message_archive"],
            "description" => t("All sent messages are archived along any file attachments.")
        ];

        $fieldset[] = ["fields" => $fields_recipient];

        $fields_response[] = [
            "type" => "radio",
            "name" => "mail_autoresponse",
            "label" => t("Auto response"),
            "value" => [
                t("Enable") => true,
                t("Disable") => false
            ],
            "checked" => $page_data["mail_autoresponse"]
        ];

        $fields_response[] = [
            "type" => "text",
            "name" => "mail_autoresponse_subject",
            "label" => t("Subject:"),
            "value" => $page_data["mail_autoresponse_subject"],
            "description" => t("The subject for the auto response."),
        ];

        $fields_response[] = [
            "type" => "textarea",
            "name" => "mail_autoresponse_message",
            "id" => "mail_autoresponse_message",
            "label" => t("Message:"),
            "value" => $page_data["mail_autoresponse_message"],
            "description" => t("The message sent when a visitor sends the form. You can insert the values of the form fields by using the format {Field_name}.")
        ];

        $fieldset[] = [
            "fields" => $fields_response,
            "name" => t("Enable or disable the auto response"),
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("On this area you can enable sending an automatic message to the user submitting the contact form.")
        ];

        if (Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup())) {
            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["meta_title"],
                "name" => "meta_title",
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            ];

            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["description"],
                "name" => "description",
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            ];

            $fields_meta[] = [
                "type" => "textarea",
                "value" => $page_data["keywords"],
                "name" => "keywords",
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            ];

            $fieldset[] = [
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            ];
        }

        if (Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) || Jaris\Authentication::isAdminLogged()) {
            $fields_inputformats = [];

            foreach (Jaris\InputFormats::getAll() as $machine_name => $fields_formats) {
                $fields_inputformats[] = [
                    "type" => "radio",
                    "checked" => $machine_name == $page_data["input_format"] ? true : false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => [$fields_formats["title"] => $machine_name]
                ];
            }

            $fieldset[] = [
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            ];
        }

        $extra_fields = Jaris\Fields::generateFields($current_type, $page_data);

        if ($extra_fields) {
            $fieldset[] = ["fields" => $extra_fields];
        }

        if (Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup())) {
            $fields_users_access[] = [
                "type" => "other",
                "html_code" => "<h4>"
                    . t("Select the groups that can see this content. Don't select anything to display content to everyone.")
                    . "</h4>"
            ];

            $fields_users_access = array_merge(
                $fields_users_access,
                Jaris\Groups::generateFields($page_data["groups"])
            );

            $fields_users_access[] = [
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => implode(", ", $page_data["users"]),
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            ];

            $fieldset[] = [
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            ];
        }

        if (Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup())) {
            $fields_other[] = [
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => $_REQUEST["uri"],
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
            ];
        }

        if (Jaris\Authentication::groupHasPermission("select_type_content", Jaris\Authentication::currentUserGroup())) {
            $types = [];
            $types_array = Jaris\Types::getList(Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser());

            foreach ($types_array as $machine_name => $type_fields) {
                $types[t(trim($type_fields["name"]))] = $machine_name;
            }

            $fields_other[] = [
                "type" => "select",
                "selected" => $current_type,
                "name" => "type",
                "label" => t("Type:"),
                "id" => "type",
                "value" => $types
            ];
        }

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];

        $fields_other[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];

        $fieldset[] = ["fields" => $fields_other];

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
