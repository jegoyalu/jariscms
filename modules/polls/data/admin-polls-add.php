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
        <?php print t("Add Poll") ?>
    field;

    field: content
    <script type="text/javascript">
        row_id = 1;

        $(document).ready(function() {
            $("#add-item").click(function() {

                row = "<tr id=\"table-row-" + row_id + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" /></td>";
                row += "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(" + row_id + ")\"><?php print t("remove") ?></a></td>";
                row += "</tr>";

                $("#items-table > tbody").append($(row).hide().fadeIn("slow"));

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

        if(!Jaris\Authentication::hasTypeAccess("poll", Jaris\Authentication::currentUserGroup(), Jaris\Authentication::currentUser()))
        {
            Jaris\Authentication::protectedPage();
        }

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("add-poll"))
        {
            //Trim uri spaces
            $_REQUEST["uri"] = trim($_REQUEST["uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];
            $fields["duration"] = $_REQUEST["duration"];
            $fields["option_name"] = serialize($_REQUEST["option_name"]);

            $option_values = array();
            foreach($_REQUEST["option_name"] as $option)
            {
                $option_values[] = 0;
            }

            $fields["option_value"] = serialize($option_values);

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
            $categories_list = Jaris\Categories::getList("poll");

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
                $fields["input_format"] = Jaris\Types::getDefaultInputFormat("poll");
            }

            $fields["created_date"] = time();
            $fields["author"] = Jaris\Authentication::currentUser();
            $fields["type"] = "poll";

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
                polls_sqlite_add($uri, $fields["created_date"]);

                add_recent_poll($uri, $fields["title"]);

                Jaris\View::addMessage(t("The poll was successfully added."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/polls/edit", "polls"),
                array("uri" => $uri)
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("");
        }

        $parameters["name"] = "add-poll";
        $parameters["class"] = "add-poll";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/polls/add", "polls")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList("poll");

        if($categories)
        {
            $fields_categories = Jaris\Categories::generateFields(
                [], "", "poll"
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
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "content",
            "value" => $_REQUEST["content"],
            "label" => t("Content:"),
            "id" => "content"
        );

        $fields[] = array(
            "type" => "text",
            "name" => "duration",
            "value" => $_REQUEST["duration"] ? $_REQUEST["duration"] : "7",
            "label" => t("Duration:"),
            "id" => "duration",
            "description" => t("The amount of days the poll is going to be active. Leave blank for unlimited time.")
        );

        $fieldset[] = array("fields" => $fields);

        $items = "<table id=\"items-table\" style=\"width: 100%\">";
        $items .= "<thead>";
        $items .= "<tr>";
        $items .= "<td style=\"width: auto\"><b>" . t("Name") . "</b></td>";
        $items .= "<td style=\"width: auto\"></td>";
        $items .= "</tr>";
        $items .= "</thead>";
        $items .= "<tbody>";
        $items .= "<tr id=\"table-row-0\">";
        $items .= "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" /></td>";
        $items .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(0)\">" . t("remove") . "</a></td>";
        $items .= "</tr>";
        $items .= "</tbody>";
        $items .= "</table>";
        $items .= "<a id=\"add-item\" style=\"cursor: pointer\">" . t("Add another option") . "</a>";

        $fields_items[] = array("type" => "other", "html_code" => $items);

        $fieldset[] = array(
            "name" => t("Options"),
            "fields" => $fields_items,
            "collapsible" => true
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
                    "checked" => $machine_name == "full_html" ? true : false,
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

        $extra_fields = Jaris\Fields::generateFields("poll");

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