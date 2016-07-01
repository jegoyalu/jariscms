<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the left blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: description
        Login
    field;

    field: title
        Login
    field;

    field: content
    <?php
        $parameters["class"] = "block-login";
        $parameters["action"] = Jaris\Uri::url("admin/user");
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "username",
            "label" => t("Username:"),
            "id" => "block-username",
            "required" => true
        );

        $fields[] = array(
            "type" => "password",
            "name" => "password",
            "label" => t("Password:"),
            "id" => "block-password",
            "required" => true
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "login",
            "value" => t("Login")
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: order
        0
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        if(Jaris\Authentication::isUserLogged() || Jaris\Uri::get() == "admin/user")
        {
            print "false";
        }
        else
        {
            print "true";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;

row: 1
    field: description
        Navigation Menu
    field;

    field: title
        Navigation
    field;

    field: content
    <?php
        print Jaris\View::getLinksHTML(
            Jaris\Data::sort(
                Jaris\Menus::getChildItems("navigation"),
                "order"
            ),
            "navigation"
        );
    ?>
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return
    <?php
        if(Jaris\Authentication::isUserLogged())
        {
            print "true";
        }
        else
        {
            print "false";
        }
    ?>
    field;

    field: order
        1
    field;

    field: is_system
        1
    field;

    field: menu_name
        navigation
    field;
row;
