<?php exit; ?>


row: 0

	field: title
		<?php print t("Edit Contact Form Field") ?>
	field;

	field: content
		<?php
		Jaris\Authentication::protectedPage(array("edit_content"));
		
		if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
		{
		    Jaris\Authentication::protectedPage();
		}
		
		if(
		    isset($_REQUEST["btnSave"]) &&
		    !Jaris\Forms::requiredFieldEmpty("edit-contact-form-field")
		)
		{
		    $fields = contact_get_field_data($_REQUEST["id"], $_REQUEST["uri"]);
		
		    $fields["variable_name"] = str_replace("-", "_", Jaris\Uri::fromText($_REQUEST["name"]));
		    $fields["name"] = $_REQUEST["name"];
		    $fields["description"] = $_REQUEST["description"];
		    $fields["type"] = $_REQUEST["type"];
		    $fields["inline"] = $_REQUEST["inline"];
		    $fields["readonly"] = $_REQUEST["readonly"];
		    $fields["required"] = $_REQUEST["required"];
		    $fields["default"] = $_REQUEST["default"];
		    $fields["extensions"] = $_REQUEST["extensions"];
		    $fields["size"] = intval($_REQUEST["size"]);
		    $fields["values"] = $_REQUEST["values"];
		    $fields["captions"] = $_REQUEST["captions"];
		    $fields["limit"] = $_REQUEST["limit"];
		    $fields["strip_html"] = $_REQUEST["strip_html"];
		
		    if(contact_edit_field($_REQUEST["id"], $fields, $_REQUEST["uri"]))
		    {
		        Jaris\View::addMessage(
		            t("The contact form field has been successfully modified.")
		        );
		    }
		    else
		    {
		        Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
		    }
		
		    Jaris\Uri::go(
		        Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
		        array("uri" => $_REQUEST["uri"])
		    );
		}
		elseif(isset($_REQUEST["btnCancel"]))
		{
		    Jaris\Uri::go(
		        Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
		        array("uri" => $_REQUEST["uri"])
		    );
		}
		
		$field_data = contact_get_field_data($_REQUEST["id"], $_REQUEST["uri"]);
		
		$parameters["name"] = "edit-contact-form-field";
		$parameters["class"] = "edit-contact-form-field";
		$parameters["action"] = Jaris\Uri::url(
		    Jaris\Modules::getPageUri("admin/pages/contact-form/fields/edit", "contact")
		);
		$parameters["method"] = "post";
		
		$fields[] = array(
		    "type" => "hidden",
		    "name" => "id",
		    "value" => $_REQUEST["id"]
		);
		
		$fields[] = array(
		    "type" => "hidden",
		    "name" => "uri",
		    "value" => $_REQUEST["uri"]
		);
		
		$fields[] = array(
		    "type" => "text",
		    "value" => $_REQUEST["name"] ?
		        $_REQUEST["name"]
		        :
		        $field_data["name"],
		    "name" => "name",
		    "label" => t("Name:"),
		    "id" => "name",
		    "required" => true,
		    "description" => t("A human readable name displayed when the form is generated.")
		);
		
		$fields[] = array(
		    "type" => "textarea",
		    "value" => $_REQUEST["description"] ?
		        $_REQUEST["description"]
		        :
		        $field_data["description"],
		    "name" => "description",
		    "label" => t("Description:"),
		    "id" => "description",
		    "description" => t("A brief description of how the user should fill this field or it's purpose.")
		);
		
		$types[t("Check box")] = "checkbox";
		$types[t("Color selector")] = "color";
		$types[t("Date picker")] = "date";
		$types[t("File upload")] = "file";
		$types[t("Hidden")] = "hidden";
		$types[t("Other")] = "other";
		$types[t("Password")] = "password";
		$types[t("Radio box")] = "radio";
		$types[t("Select")] = "select";
		$types[t("Text")] = "text";
		$types[t("Text area")] = "textarea";
		
		$fields[] = array(
		    "type" => "select",
		    "value" => $types,
		    "selected" => $_REQUEST["type"] ?
		        $_REQUEST["type"]
		        :
		        $field_data["type"],
		    "name" => "type",
		    "label" => t("Type:"),
		    "id" => "type",
		    "description" => t("The type of the form field.")
		);
		
		$fields[] = array(
		    "type" => "text",
		    "value" => $_REQUEST["limit"] ?
		        $_REQUEST["limit"]
		        :
		        $field_data["limit"],
		    "name" => "limit",
		    "label" => t("Input limit:"),
		    "id" => "limit",
		    "description" => t("The maximun amount of character the user can insert if this is a text or textarea field. 0 for unlimited.")
		);
		
		$fields[] = array(
		    "type" => "textarea",
		    "value" => $_REQUEST["default"] ?
		        $_REQUEST["default"]
		        :
		        $field_data["default"],
		    "name" => "default",
		    "label" => t("Default value:"),
		    "id" => "default",
		    "description" => t("The default value for a text, textarea, password, hidden, other or a list like select, radio and checkbox.")
		);
		
		$fieldset[] = array("fields" => $fields);
		
		$fields_file[] = array(
		    "type" => "textarea",
		    "value" => $_REQUEST["extensions"] ?
		        $_REQUEST["extensions"]
		        :
		        $field_data["extensions"],
		    "name" => "extensions",
		    "label" => t("File extensions:"),
		    "id" => "extensions",
		    "description" => t("A comma (,) seperated list of extensions allowed for upload in case of file upload. For example: txt, doc, pdf")
		);
		
		$fields_file[] = array(
		    "type" => "text",
		    "value" => $_REQUEST["size"] ? $_REQUEST["size"] : $field_data["size"],
		    "name" => "size",
		    "label" => t("File size:"),
		    "id" => "size",
		    "description" => t("The maximum permitted file size in kilobytes. For example: 100k") . " " .
		        t("The maximum file upload size allowed by this server is:") . " " .
		        ini_get("upload_max_filesize")
		);
		
		$fieldset[] = array(
		    "fields" => $fields_file,
		    "name" => t("File upload"),
		    "description" => t("Options used in case the type selected is a file upload.")
		);
		
		$fields_options[] = array(
		    "type" => "checkbox",
		    "checked" => $_REQUEST["inline"] ?
		        $_REQUEST["inline"]
		        :
		        $field_data["inline"],
		    "name" => "inline",
		    "label" => t("Inline:"),
		    "id" => "inline",
		    "description" => t("Display the field inlined so other fields are next to it.")
		);
		
		$fields_options[] = array(
		    "type" => "checkbox",
		    "checked" => $_REQUEST["readonly"] ?
		        $_REQUEST["readonly"]
		        :
		        $field_data["readonly"],
		    "name" => "readonly",
		    "label" => t("Read only:"),
		    "id" => "readonly",
		    "description" => t("In case the field should be readonly.")
		);
		
		$fields_options[] = array(
		    "type" => "checkbox",
		    "checked" => $_REQUEST["required"] ?
		        $_REQUEST["required"]
		        :
		        $field_data["required"],
		    "name" => "required",
		    "label" => t("Required:"),
		    "id" => "required",
		    "description" => t("In case the field should be required.")
		);
		
		$fields_options[] = array(
		    "type" => "checkbox",
		    "checked" => $_REQUEST["strip_html"] ?
		        $_REQUEST["strip_html"]
		        :
		        $field_data["strip_html"],
		    "name" => "strip_html",
		    "label" => t("Strip html:"),
		    "id" => "strip_html",
		    "description" => t("To enable stripping of any html tags.")
		);
		
		$fieldset[] = array(
		    "fields" => $fields_options,
		    "name" => t("Field options"),
		    "description" => t("Special options for the field.")
		);
		
		$fields_select[] = array(
		    "type" => "textarea",
		    "value" => $_REQUEST["values"] ?
		        $_REQUEST["values"]
		        :
		        $field_data["values"],
		    "name" => "values",
		    "label" => t("Values:"),
		    "id" => "valuess",
		    "description" => t("A list of values seperated by comma for select, radio and checkbox.")
		);
		
		$fields_select[] = array(
		    "type" => "textarea",
		    "value" => $_REQUEST["captions"] ?
		        $_REQUEST["captions"]
		        :
		        $field_data["captions"],
		    "name" => "captions",
		    "label" => t("Captions:"),
		    "id" => "captions",
		    "description" => t("A list of captions seperated by comma in the same order entered in values in case it is a radio, checkbox or select.")
		);
		
		$fieldset[] = array(
		    "fields" => $fields_select,
		    "name" => t("Multiple options"),
		    "description" => t("Options used in case the type selected is a select, radio or checkbox.")
		);
		
		$fields_buttons[] = array(
		    "type" => "submit",
		    "name" => "btnSave",
		    "value" => t("Save")
		);
		
		$fields_buttons[] = array(
		    "type" => "submit",
		    "name" => "btnCancel",
		    "value" => t("Cancel")
		);
		
		$fieldset[] = array("fields" => $fields_buttons);
		
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


