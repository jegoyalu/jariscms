<?php exit; ?>


row: 0

	field: description
		primary menu
	field;

	field: title
		Primary menu
	field;

	field: content
		<?php
        print Jaris\View::getLinksHTML(
    Jaris\Data::sort(
                Jaris\Menus::getChildItems("primary"),
                "order"
            ),
    "primary"
        );
        ?>
	field;

	field: order
		6
	field;

	field: display_rule
		all_except_listed
	field;

	field: pages

	field;

	field: return

	field;

	field: is_system
		1
	field;

	field: menu_name
		primary
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		15
	field;

row;


row: 1

	field: description
		secondary menu
	field;

	field: title
		Secondary menu
	field;

	field: content
		<?php
        print Jaris\View::getLinksHTML(
            Jaris\Data::sort(
                Jaris\Menus::getChildItems("secondary"),
                "order"
            ),
            "secondary"
        );
        ?>
	field;

	field: display_rule
		all_except_listed
	field;

	field: pages

	field;

	field: return

	field;

	field: order
		8
	field;

	field: is_system
		1
	field;

	field: menu_name
		secondary
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		16
	field;

row;


row: 2

	field: description
		site search
	field;

	field: title
		Search
	field;

	field: content
		<?php
        $parameters["class"] = "block-search";
        $parameters["action"] = Jaris\Uri::url("search");
        $parameters["method"] = "get";

        $fields[] = [
            "type" => "hidden",
            "name" => "search",
            "value" => 1
        ];

        $fields[] = [
            "type" => "text",
            "name" => "keywords",
            "id" => "search",
            "value" => empty($_REQUEST["keywords"]) ?
                "" : $_REQUEST["keywords"]
        ];

        $fields[] = [
            "type" => "submit",
            "value" => t("Search")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
        ?>
	field;

	field: order
		7
	field;

	field: display_rule
		all_except_listed
	field;

	field: pages

	field;

	field: return
		<?php
        if (Jaris\Uri::get() == "search") {
            print "false";
        } else {
            print "true";
        }
        ?>
	field;

	field: is_system
		1
	field;

	field: groups
		N;
	field;

	field: themes
		a:1:{s:7:"default";s:0:"";}
	field;

	field: id
		12
	field;

row;


row: 3

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

        $fields[] = [
            "type" => "text",
            "name" => "username",
            "label" => t("Username:"),
            "id" => "block-username",
            "required" => true
        ];

        $fields[] = [
            "type" => "password",
            "name" => "password",
            "label" => t("Password:"),
            "id" => "block-password",
            "required" => true
        ];

        $fields[] = [
            "type" => "submit",
            "name" => "login",
            "value" => t("Login")
        ];

        $fieldset[] = ["fields" => $fields];

        print Jaris\Forms::generate($parameters, $fieldset);
        ?>
	field;

	field: order
		4
	field;

	field: display_rule
		all_except_listed
	field;

	field: pages

	field;

	field: return
		<?php
        if (
            Jaris\Authentication::isUserLogged() ||
            Jaris\Uri::url() == "admin/user"
        ) {
            print "false";
        } else {
            print "true";
        }
        ?>
	field;

	field: is_system
		1
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		13
	field;

row;


row: 4

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
        if (Jaris\Authentication::isUserLogged()) {
            print "true";
        } else {
            print "false";
        }
            ?>
	field;

	field: order
		5
	field;

	field: is_system
		1
	field;

	field: menu_name
		navigation
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		14
	field;

row;


row: 5

	field: description
		about menu
	field;

	field: title
		about menu
	field;

	field: content
		<?php
        print Jaris\View::getLinksHTML(
                Jaris\Data::sort(
                Jaris\Menus::getChildItems("about"),
                "order"
            ),
                "about"
        );
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

	field;

	field: is_system
		1
	field;

	field: menu_name
		about
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		21
	field;

row;


row: 6

	field: description
		extend menu
	field;

	field: title
		extend menu
	field;

	field: content
		<?php
        print Jaris\View::getLinksHTML(
            Jaris\Data::sort(
                Jaris\Menus::getChildItems("extend"),
                "order"
            ),
            "extend"
        );
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

	field;

	field: is_system
		1
	field;

	field: menu_name
		extend
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		22
	field;

row;


row: 7

	field: description
		support menu
	field;

	field: title
		support menu
	field;

	field: content
		<?php
        print Jaris\View::getLinksHTML(
            Jaris\Data::sort(
                Jaris\Menus::getChildItems("support"),
                "order"
            ),
            "support"
        );
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

	field;

	field: is_system
		1
	field;

	field: menu_name
		support
	field;

	field: groups
		N;
	field;

	field: themes
		N;
	field;

	field: id
		23
	field;

row;


