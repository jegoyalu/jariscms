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
        <?php print t("Edit Poll") ?>
    field;

    field: content
    <script type="text/javascript">
        row_id = 1;

        $(document).ready(function() {
            $("#add-item").click(function() {

                row = "<tr id=\"table-row-" + row_id + "\">";
                row += "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" /></td>";
                row += "<td style=\"width: auto\"><input type=\"hidden\" name=\"option_value[]\" value=\"0\" /></td>";
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
        Jaris\Authentication::protectedPage(array("edit_content"));

        if(
            !Jaris\Pages::userIsOwner(
                trim($_REQUEST["actual_uri"]) != "" ?
                    $_REQUEST["actual_uri"]
                    :
                    $_REQUEST["uri"]
            )
        )
        {
            Jaris\Authentication::protectedPage();
        }

        if(isset($_REQUEST["btnSave"]) && !Jaris\Forms::requiredFieldEmpty("edit-poll"))
        {
            //Check if client is trying to submit content to a
            //system page sending variables thru GET
            if(Jaris\Pages::isSystem($_REQUEST["actual_uri"]))
            {
                Jaris\View::addMessage(
                    t("The content you was trying to edit is a system page."),
                    "error"
                );

                Jaris\Uri::go("");
            }

            //Trim uri spaces
            $_REQUEST["uri"] = trim($_REQUEST["uri"]);
            $_REQUEST["actual_uri"] = trim($_REQUEST["actual_uri"]);

            $fields = Jaris\Pages::get($_REQUEST["actual_uri"]);

            $fields["title"] = $_REQUEST["title"];
            $fields["content"] = $_REQUEST["content"];
            $fields["duration"] = $_REQUEST["duration"];
            $fields["option_name"] = serialize($_REQUEST["option_name"]);
            $fields["option_value"] = serialize($_REQUEST["option_value"]);

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
            $fields["type"] = "poll";
            $fields["last_edit_by"] = Jaris\Authentication::currentUser();
            $fields["last_edit_date"] = time();

            Jaris\Fields::appendFields($fields["type"], $fields);

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

            if(Jaris\Pages::edit($_REQUEST["actual_uri"], $fields))
            {
                edit_recent_poll(
                    $_REQUEST["uri"],
                    $fields["title"],
                    $_REQUEST["actual_uri"]
                );

                //Update all translations
                $new_page_data = Jaris\Pages::get($_REQUEST["actual_uri"]);
                foreach(Jaris\Language::getInstalled() as $code => $name)
                {
                    $translation_path = Jaris\Language::dataTranslate(
                        Jaris\Pages::getPath($_REQUEST["actual_uri"]),
                        $code
                    );

                    $original_path = Jaris\Pages::getPath($_REQUEST["actual_uri"]);

                    if($translation_path != $original_path)
                    {
                        $translation_data = Jaris\Pages::get(
                            $_REQUEST["actual_uri"],
                            $code
                        );

                        $new_page_data["title"] = $translation_data["title"];
                        $new_page_data["content"] = $translation_data["content"];

                        Jaris\Translate::page(
                            $_REQUEST["actual_uri"],
                            $new_page_data,
                            $code
                        );
                    }
                }

                //Move page to new location
                if($_REQUEST["actual_uri"] != $_REQUEST["uri"])
                {
                    Jaris\Pages::move($_REQUEST["actual_uri"], $_REQUEST["uri"]);

                    //Also move its translations on the language directory
                    if(Jaris\Translate::movePage($_REQUEST["actual_uri"], $_REQUEST["uri"]))
                    {
                        Jaris\View::addMessage(t("Translations repositioned."));
                    }
                    else
                    {
                        Jaris\View::addMessage(
                            Jaris\System::errorMessage("translations_not_moved"),
                            "error"
                        );
                    }
                }

                Jaris\View::addMessage(t("Your changes have been successfully saved."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/polls/edit", "polls"),
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/polls/edit", "polls"),
                array("uri" => $_REQUEST["actual_uri"])
            );
        }

        $arguments["uri"] = $_REQUEST["uri"];

        //Tabs
        if(Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        if(Jaris\Authentication::groupHasPermission("view_content_blocks", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("view_images", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("view_files", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("translate_languages", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $page_data = Jaris\Pages::get($_REQUEST["uri"]);
        $page_data["option_name"] = unserialize($page_data["option_name"]);
        $page_data["option_value"] = unserialize($page_data["option_value"]);

        $parameters["name"] = "edit-poll";
        $parameters["class"] = "edit-poll";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/polls/edit", "polls")
        );
        $parameters["method"] = "post";

        $categories = Jaris\Categories::getList("poll");

        if($categories)
        {
            $fields_categories = Jaris\Categories::generateFields(
                $page_data["categories"],
                null,
                "poll"
            );

            $fieldset[] = array(
                "fields" => $fields_categories,
                "name" => t("Categories"),
                "collapsible" => true
            );
        }

        $fields[] = array(
            "type" => "hidden",
            "name" => "actual_uri",
            "value" => $_REQUEST["actual_uri"] ? $_REQUEST["actual_uri"] : $_REQUEST["uri"]
        );

        $fields[] = array(
            "type" => "text",
            "value" => $page_data["title"],
            "name" => "title",
            "label" => t("Title:"),
            "id" => "title",
            "required" => true
        );

        $fields[] = array(
            "type" => "textarea",
            "value" => $page_data["content"],
            "name" => "content",
            "label" => t("Content:"),
            "id" => "content"
        );

        $fields[] = array(
            "type" => "text",
            "name" => "duration",
            "value" => $page_data["duration"],
            "label" => t("Duration:"),
            "id" => "duration",
            "description" => t("The amount of days the poll is going to be active. Leave blank for unlimited time.")
        );

        $fieldset[] = array("fields" => $fields);

        $items = "<table id=\"items-table\" style=\"width: 100%\">";
        $items .= "<thead>";
        $items .= "<tr>";
        $items .= "<td style=\"width: auto\"><b>" . t("Name") . "</b></td>";
        $items .= "<td style=\"width: auto\"><b>" . t("Value") . "</b></td>";
        $items .= "<td style=\"width: auto\"></td>";
        $items .= "</tr>";
        $items .= "</thead>";
        $items .= "<tbody>";

        $i = 0;
        for($i; $i < count($page_data["option_name"]); $i++)
        {
            $items .= "<tr id=\"table-row-$i\">";
            $items .= "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" value=\"{$page_data['option_name'][$i]}\" /></td>";
            $items .= "<td style=\"width: auto; text-align: center\">{$page_data['option_value'][$i]}<input type=\"hidden\" name=\"option_value[]\" value=\"{$page_data['option_value'][$i]}\" /></td>";
            $items .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row($i)\">" . t("remove") . "</a></td>";
            $items .= "</tr>";
        }

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
                "value" => $page_data["meta_title"],
                "name" => "meta_title",
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "value" => $page_data["description"],
                "name" => "description",
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "value" => $page_data["keywords"],
                "name" => "keywords",
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
                    "checked" => $machine_name == $page_data["input_format"] ?
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

        $extra_fields = Jaris\Fields::generateFields("poll", $page_data);

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
                Jaris\Groups::generateFields($page_data["groups"])
            );

            $fields_users_access[] = array(
                "type" => "userarea",
                "name" => "users",
                "label" => t("Users:"),
                "id" => "users",
                "value" => implode(", ", $page_data["users"]),
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