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
        Add Popup
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        if(
            isset($_REQUEST["btnSave"])
            &&
            !Jaris\Forms::requiredFieldEmpty("popup-add")
        )
        {
            $data = array(
                "description" => $_REQUEST["description"],
                "message" => $_REQUEST["message"],
                "condition" => $_REQUEST["condition"],
                "delay" => intval($_REQUEST["delay"]),
                "onmouseleave" => $_REQUEST["onmouseleave"],
                "display_once" => $_REQUEST["display_once"],
                "groups" => serialize($_REQUEST["groups"]),
                "display_rule" => $_REQUEST["display_rule"],
                "pages" => $_REQUEST["pages"]
            );

            if(
                Jaris\Data::add(
                    $data,
                    Jaris\Site::dataDir() . "settings/popup.php"
                )
            )
            {
                Jaris\View::addMessage(t("Your changes have been saved."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data")
                );
            }

            Jaris\Uri::go("admin/settings/popup");
        }

        $parameters["name"] = "popup-add";
        $parameters["class"] = "popup-add";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri(
                "admin/settings/popup/add",
                "popup"
            )
        );
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "description",
            "label" => t("Description:"),
            "value" => $_REQUEST["description"],
            "required" => true,
            "description" => t("A description of the popup for your reference.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "message",
            "label" => t("Message:"),
            "value" => $_REQUEST["message"],
            "required" => true,
            "description" => t("The message that will appear on the popup. Can be a mix of html and php code.")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "condition",
            "label" => t("Condition:"),
            "value" => $_REQUEST["condition"],
            "description" => t("A condition expressed as php code that should return true if the popup message should be displayed or false otherwise. Eg: &lt;?php if(true){return true;} else{return false;} ?&gt;")
        );

        $fieldset[] = array("fields" => $fields);

        $fields_options[] = array(
            "type" => "text",
            "name" => "delay",
            "label" => t("Delay:"),
            "value" => intval($_REQUEST["delay"]),
            "inline" => true,
            "description" => t("A delay in seconds to display the popup.")
        );

        $fields_options[] = array(
            "type" => "radio",
            "name" => "onmouseleave",
            "label" => t("On Mouse Leave:"),
            "value" => array(
                t("Enable") => true,
                t("Disable") => false
            ),
            "inline" => true,
            "checked" => $_REQUEST["onmouseleave"],
            "description" => t("Only display the popup when the mouse leaves the window.")
        );

        $fields_options[] = array(
            "type" => "radio",
            "name" => "display_once",
            "label" => t("Display Once:"),
            "value" => array(
                t("Enable") => true,
                t("Disable") => false
            ),
            "inline" => true,
            "checked" => $_REQUEST["display_once"],
            "description" => t("Only display the popup once.")
        );

        $fieldset[] = array(
            "fields" => $fields_options,
            "name" => t("Popup Options")
        );

        $fields_users_access[] = array(
            "type" => "other",
            "html_code" => "<p>"
                . t("Select the groups that can see this popup. Don't select anything to display the popup to everyone.")
                . "</p>"
        );

        $fields_users_access = array_merge(
            $fields_users_access,
            Jaris\Groups::generateFields(
                $_REQUEST["groups"],
                "groups",
                array(),
                true
            )
        );

        $fieldset[] = array(
            "fields" => $fields_users_access,
            "name" => t("Users Access"),
            "collapsed" => false,
            "collapsible" => true
        );

        $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
        $display_rules[t("Just display on the listed pages.")] = "just_listed";

        $fields_pages[] = array(
            "type" => "radio",
            "checked" => isset($_REQUEST["display_rule"]) ?
                $_REQUEST["display_rule"] : "just_listed",
            "name" => "display_rule",
            "id" => "display_rule",
            "value" => $display_rules
        );

        $fields_pages[] = array(
            "type" => "uriarea",
            "name" => "pages",
            "label" => t("Pages:"),
            "id" => "pages",
            "value" => $_REQUEST["pages"]
        );

        $fieldset[] = array(
            "fields" => $fields_pages,
            "name" => t("Pages to display"),
            "description" => t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields_submit[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields_submit);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
