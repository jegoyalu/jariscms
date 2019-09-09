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
        $type_data = Jaris\Types::get($_REQUEST["type"]);

        print t("Add") . " " . t($type_data["name"]);
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
        Jaris\Authentication::protectedPage(array("add_content"));

        if(!Jaris\Authentication::hasTypeAccess($_REQUEST["type"], Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser()))
        {
            Jaris\Authentication::protectedPage();
        }

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-page-{$_REQUEST['type']}") &&
            Jaris\Authentication::hasTypeAccess($_REQUEST["type"], Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser())
        )
        {
            //Trim uri spaces
            $_REQUEST["uri"] = trim($_REQUEST["uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];

            $subjects = array();
            if(is_array($_REQUEST["subject_title"]))
            {
                foreach($_REQUEST["subject_title"] as $subject_position => $subject_value)
                {
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

            if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
            {
                $fields["meta_title"] = $_REQUEST["meta_title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["keywords"] = $_REQUEST["keywords"];
            }

            if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
            {
                $fields["groups"] = $_REQUEST["groups"];

                $users = explode(",", $_REQUEST["users"]);

                if(count($users) > 0)
                {
                    foreach($users as $user_position=>$username)
                    {
                        $users[$user_position] = trim($username);
                    }
                }

                $fields["users"] = $users;
            }
            else
            {
                $fields["groups"] = array();
                $fields["user"] = array();
            }

            $categories = array();
            $categories_list = Jaris\Categories::getList($_REQUEST["type"]);

            if($categories_list)
            {
                foreach($categories_list as $machine_name => $values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }

            $fields["categories"] = $categories;

            if(
                Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
                Jaris\Authentication::isAdminLogged()
            )
            {
                $fields["input_format"] = $_REQUEST["input_format"];
            }
            else
            {
                $fields["input_format"] = Jaris\Types::getDefaultInputFormat($_REQUEST["type"]);
            }

            $fields["created_date"] = time();
            $fields["author"] = Jaris\Authentication::currentUser();
            $fields["type"] = $_REQUEST["type"];

            Jaris\Fields::appendFields($fields["type"], $fields);

            //Stores the uri of the page to display the edit page after saving.
            $uri = "";

            if(
                !Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()) ||
                $_REQUEST["uri"] == ""
            )
            {
                $_REQUEST["uri"] = Jaris\Types::generateURI(
                    $fields["type"],
                    $fields["title"],
                    $fields["author"]
                );
            }

            if(Jaris\Pages::add($_REQUEST["uri"], $fields, $uri))
            {
                Jaris\View::addMessage(t("The page was successfully created."));

                $fields = array();

                //Add default fields
                $fields["name"] = "Name";
                $fields["variable_name"] = str_replace("-", "_", Jaris\Uri::fromText($fields["name"]));
                $fields["type"] = "text";
                $fields["required"] = true;
                $fields["strip_html"] = true;
                $fields["position"] = "0";

                contact_add_field($fields, $uri);

                $fields["name"] = "E-mail";
                $fields["variable_name"] = str_replace("-", "_", Jaris\Uri::fromText($fields["name"]));

                contact_add_field($fields, $uri);

                $fields["name"] = "Message";
                $fields["type"] = "textarea";
                $fields["variable_name"] = str_replace("-", "_", Jaris\Uri::fromText($fields["name"]));

                contact_add_field($fields, $uri);
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            if(Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup()))
            {
                Jaris\Uri::go("admin/pages/edit", array("uri" => $uri));
            }
            else
            {
                Jaris\Uri::go($uri);
            }
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            if(
                Jaris\Authentication::groupHasPermission(
                    "view_content",
                    Jaris\Authentication::currentUserGroup()
                )
            )
            {
                Jaris\Uri::go("admin/pages");
            }
            else
            {
                Jaris\Uri::go("admin/pages/types");
            }
        }
        elseif(!Jaris\Authentication::hasTypeAccess($_REQUEST["type"], Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser()))
        {
            Jaris\View::addMessage(
                t("You do not have permissions to add content of that type."),
                "error"
            );
        }

        $parameters["name"] = "add-page-{$_REQUEST['type']}";
        $parameters["class"] = "add-page-{$_REQUEST['type']}";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/contact-form/add", "contact")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList($_REQUEST["type"]);

        if($categories)
        {
            $fields_categories = Jaris\Categories::generateFields(
                [], "", $_REQUEST["type"]
            );

            $fieldset[] = array(
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            );
        }

        $fields[] = array(
            "type" => "text",
            "name" => "title",
            "value" => $_REQUEST["title"],
            "label" => Jaris\Types::getLabel($_REQUEST["type"], "title_label"),
            "id" => "title",
            "required" => true,
            "description" => Jaris\Types::getLabel($_REQUEST["type"], "title_description")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => $_REQUEST["content"],
            "label" => Jaris\Types::getLabel($_REQUEST["type"], "content_label"),
            "id" => "content",
            "description" => Jaris\Types::getLabel($_REQUEST["type"], "content_description")
        );

        $fieldset[] = array("fields" => $fields);

        $subject_html = "<table id=\"items-table\" style=\"width: 100%\">";
        $subject_html .= "<thead>";
        $subject_html .= "<tr>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Subject") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"><b>" . t("Recipient") . "</b></td>";
        $subject_html .= "<td style=\"width: auto\"></td>";
        $subject_html .= "</tr>";
        $subject_html .= "</thead>";
        $subject_html .= "<tbody>";
        $subject_html .= "</tr>";
        $subject_html .= "</tbody>";
        $subject_html .= "</table>";
        $subject_html .= "<a id=\"add-item\" style=\"cursor: pointer; display: block; margin-top: 8px\">" . t("Add subject") . "</a>";

        $fields_subject[] = array("type" => "other", "html_code" => $subject_html);

        $fieldset[] = array(
            "name" => t("Subject"),
            "fields" => $fields_subject,
            "collapsible" => true,
            "description" => t("Optional list of selectable subjects by the visitor with alternate mail recipients.")
        );

        $fields_recipient[] = array(
            "type" => "text",
            "name" => "mail_recipient",
            "value" => $_REQUEST["mail_recipient"],
            "label" => t("Mail recipient"),
            "id" => "title",
            "description" => t("The default recipient to receive the email. This value gets overriden by the recipient entered on the list of subjects."),
            "required" => true
        );

        $fields_recipient[] = array(
            "type" => "textarea",
            "name" => "mail_carbon_copy",
            "value" => $_REQUEST["mail_carbon_copy"],
            "label" => t("Carbon copy recipients"),
            "id" => "title",
            "description" => t("A comma seperated list of emails to receive a copy. For example: email_1@domain.com, email_2@domain.com")
        );

        $fields_recipient[] = array(
            "type" => "radio",
            "name" => "message_archive",
            "label" => t("Archive Messages?"),
            "value" => array(t("No") => false, t("Yes") => true),
            "checked" => isset($_REQUEST["message_archive"]) ?
                $_REQUEST["message_archive"] : false,
            "description" => t("All sent messages are archived along any file attachments.")
        );

        $fieldset[] = array("fields" => $fields_recipient);

        $fields_response[] = array(
            "type" => "radio",
            "name" => "mail_autoresponse",
            "label" => t("Auto response"),
            "value" => array(
                t("Enable") => true,
                t("Disable") => false
            ),
            "checked" => isset($_REQUEST["mail_autoresponse"]) ?
                $_REQUEST["mail_autoresponse"]
                :
                false
        );

        $fields_response[] = array(
            "type" => "text",
            "name" => "mail_autoresponse_subject",
            "label" => t("Subject:"),
            "value" => $_REQUEST["mail_autoresponse_subject"],
            "description" => t("The subject for the auto response.")
        );

        $fields_response[] = array(
            "type" => "textarea",
            "name" => "mail_autoresponse_message",
            "id" => "mail_autoresponse_message",
            "label" => t("Message:"),
            "value" => $_REQUEST["mail_autoresponse_message"],
            "description" => t("The message sent when a visitor sends the form. You can insert the values of the form fields by using the format {Field_name}.")
        );

        $fieldset[] = array(
            "fields" => $fields_response,
            "name" => t("Enable or disable the auto response"),
            "collapsible" => true,
            "collapsed" => true,
            "description" => t("On this area you can enable sending an automatic message to the user submitting the contact form.")
        );

        if(Jaris\Authentication::groupHasPermission("add_edit_meta_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "meta_title",
                "value" => $_REQUEST["meta_title"],
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "description",
                "value" => $_REQUEST["description"],
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "name" => "keywords",
                "value" => $_REQUEST["keywords"],
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            );

            $fieldset[] = array(
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            );
        }

        if(
            Jaris\Authentication::groupHasPermission("input_format_content", Jaris\Authentication::currentUserGroup()) ||
            Jaris\Authentication::isAdminLogged()
        )
        {
            $fields_inputformats = array();

            foreach(Jaris\InputFormats::getAll() as $machine_name => $fields_formats)
            {
                $fields_inputformats[] = array(
                    "type" => "radio",
                    "checked" => $machine_name == Jaris\Types::getDefaultInputFormat($_REQUEST["type"]) ?
                        true
                        :
                        false,
                    "name" => "input_format",
                    "description" => $fields_formats["description"],
                    "value" => array($fields_formats["title"] => $machine_name)
                );
            }

            $fieldset[] = array(
                "fields" => $fields_inputformats,
                "name" => t("Input Format")
            );
        }

        //If page has no type defaults to 'pages' type
        $current_type = trim($_REQUEST["type"]);
        if($current_type == "")
        {
            $current_type = "pages";
        }

        $extra_fields = Jaris\Fields::generateFields($current_type);

        if($extra_fields)
        {
            $fieldset[] = array("fields" => $extra_fields);
        }

        if(Jaris\Authentication::groupHasPermission("select_content_groups", Jaris\Authentication::currentUserGroup()))
        {
            $fields_users_access[] = array(
                "type" => "other",
                "html_code" => "<h4>"
                    . t("Select the groups that can see this content. Don't select anything to display content to everyone.")
                    . "</h4>"
            );

            $fields_users_access = array_merge(
                $fields_users_access,
                Jaris\Groups::generateFields()
            );

            $fields_users_access[] = array(
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => $_REQUEST["users"],
                "description" => t("A comma seperated list of users that can see this content. Leave empty to display content to everyone.")
            );

            $fieldset[] = array(
                "fields" => $fields_users_access,
                "name" => t("Users Access"),
                "collapsed" => true,
                "collapsible" => true
            );
        }

        if(Jaris\Authentication::groupHasPermission("manual_uri_content", Jaris\Authentication::currentUserGroup()))
        {
            $fields_other[] = array(
                "type" => "text",
                "name" => "uri",
                "label" => t("Uri:"),
                "id" => "uri",
                "value" => $_REQUEST["uri"],
                "description" => t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate.")
            );
        }

        if(Jaris\Authentication::groupHasPermission("select_type_content", Jaris\Authentication::currentUserGroup()))
        {
            $types = array();
            $types_array = Jaris\Types::getList(Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser());
            foreach($types_array as $machine_name => $type_fields)
            {
                $types[t(trim($type_fields["name"]))] = $machine_name;
            }

            $fields_other[] = array(
                "type" => "select",
                "selected" => $current_type,
                "name" => "type",
                "label" => t("Type:"),
                "id" => "type",
                "value" => $types
            );
        }
        else
        {
            $fields_other[] = array(
                "type" => "hidden",
                "name" => "type",
                "value" => $current_type
            );
        }

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_other[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_other);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
