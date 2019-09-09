<?php exit; ?>


row: 0

	field: title
		<?php print t("RSS Selector") ?>
	field;

	field: content
		<?php
		if(isset($_REQUEST["btnView"]))
		{
		    if($_REQUEST["type"] != "")
		    {
		        Jaris\Uri::go(
		            Jaris\Modules::getPageUri("rss", "rss"),
		            array("type" => $_REQUEST["type"])
		        );
		    }
		
		    Jaris\Uri::go(Jaris\Modules::getPageUri("rss", "rss"));
		}
		
		$parameters["name"] = "rss-selector";
		$parameters["class"] = "rss-selector";
		$parameters["action"] = Jaris\Uri::url(
		    Jaris\Modules::getPageUri("rss/selector", "rss")
		);
		$parameters["method"] = "post";
		
		$types = Jaris\Types::getList();
		$types_list = array();
		$types_list[t("All")] = "";
		
		foreach($types as $type_name => $type_data)
		{
		    $types_list[t($type_data["name"])] = $type_name;
		}
		
		$fields[] = array(
		    "type" => "select",
		    "name" => "type",
		    "label" => t("Type of content:"),
		    "id" => "type",
		    "value" => $types_list,
		    "selected" => ""
		);
		
		$fields[] = array(
		    "type" => "submit",
		    "name" => "btnView",
		    "value" => t("View")
		);
		
		$fieldset[] = array("fields" => $fields);
		
		print "<p>" .
		    t("You can use the rss selecter tool to generate rss by content type.") .
		    "</p>"
		;
		
		print Jaris\Forms::generate($parameters, $fieldset);
		    ?>
	field;

	field: is_system
		1
	field;

	field: users
		N;
	field;

	field: groups
		N;
	field;

	field: categories
		N;
	field;

row;


