<?php exit; ?>


row: 0

	field: title
		<?php print t("Add Contact Form Field") ?>
	field;

	field: content
		<?php
        Jaris\Authentication::protectedPage(["edit_content"]);
        
        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }
        
        if (
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-contact-form-field")
        ) {
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
            $fields["position"] = "0";
        
            if (contact_add_field($fields, $_REQUEST["uri"])) {
                Jaris\View::addMessage(
                    t("The contact form field has been successfully created.")
                );
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }
        
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/fields",
                    "contact"
                ),
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnCancel"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("admin/pages/contact-form/fields", "contact"),
                ["uri" => $_REQUEST["uri"]]
            );
        }
        
        $parameters["name"] = "add-contact-form-field";
        $parameters["class"] = "add-contact-form-field";
        $parameters["action"] = Jaris\Uri::url(
            Jaris\Modules::getPageUri("admin/pages/contact-form/fields/add", "contact")
        );
        $parameters["method"] = "post";
        
        $fields[] = [
            "type" => "hidden",
            "name" => "uri",
            "value" => $_REQUEST["uri"]
        ];
        
        $fields[] = [
            "type" => "text",
            "value" => $_REQUEST["name"],
            "name" => "name",
            "label" => t("Name:"),
            "id" => "name",
            "required" => true,
            "description" => t("A human readable name displayed when the form is generated.")
        ];
        
        $fields[] = [
            "type" => "textarea",
            "value" => $_REQUEST["description"],
            "name" => "description",
            "label" => t("Description:"),
            "id" => "description",
            "description" => t("A brief description of how the user should fill this field or it's purpose.")
        ];
        
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
        
        $fields[] = [
            "type" => "select",
            "value" => $types,
            "selected" => $_REQUEST["type"],
            "name" => "type",
            "label" => t("Type:"),
            "id" => "type",
            "description" => t("The type of the form field.")
        ];
        
        $fields[] = [
            "type" => "text",
            "value" => $_REQUEST["limit"],
            "name" => "limit",
            "label" => t("Input limit:"),
            "id" => "limit",
            "description" => t("The maximun amount of character the user can insert if this is a text or textarea field. 0 for unlimited.")
        ];
        
        $fields[] = [
            "type" => "textarea",
            "value" => $_REQUEST["default"],
            "name" => "default",
            "label" => t("Default value:"),
            "id" => "default",
            "description" => t("The default value for a text, textarea, password, hidden, other or a list like select, radio and checkbox.")
        ];
        
        $fieldset[] = ["fields" => $fields];
        
        $fields_file[] = [
            "type" => "textarea",
            "value" => $_REQUEST["extensions"],
            "name" => "extensions",
            "label" => t("File extensions:"),
            "id" => "extensions",
            "description" => t("A comma (,) seperated list of extensions allowed for upload in case of file upload. For example: txt, doc, pdf")
        ];
        
        $fields_file[] = [
            "type" => "text",
            "value" => $_REQUEST["size"],
            "name" => "size",
            "label" => t("File size:"),
            "id" => "size",
            "description" => t("The maximum permitted file size in kilobytes. For example: 100k") . " " .
                t("The maximum file upload size allowed by this server is:") . " " .
                ini_get("upload_max_filesize")
        ];
        
        $fieldset[] = [
            "fields" => $fields_file,
            "name" => t("File upload"),
            "description" => t("Options used in case the type selected is a file upload.")
        ];
        
        $fields_options[] = [
            "type" => "checkbox",
            "checked" => $_REQUEST["inline"],
            "name" => "inline",
            "label" => t("Inline:"),
            "id" => "inline",
            "description" => t("Display the field inlined so other fields are next to it.")
        ];
        
        $fields_options[] = [
            "type" => "checkbox",
            "checked" => $_REQUEST["readonly"],
            "name" => "readonly",
            "label" => t("Read only:"),
            "id" => "readonly",
            "description" => t("In case the field should be readonly.")
        ];
        
        $fields_options[] = [
            "type" => "checkbox",
            "checked" => $_REQUEST["required"],
            "name" => "required",
            "label" => t("Required:"),
            "id" => "required",
            "description" => t("In case the field should be required.")
        ];
        
        $fields_options[] = [
            "type" => "checkbox",
            "checked" => $_REQUEST["strip_html"],
            "name" => "strip_html",
            "label" => t("Strip html:"),
            "id" => "strip_html",
            "description" => t("To enable stripping of any html tags.")
        ];
        
        $fieldset[] = [
            "fields" => $fields_options,
            "name" => t("Field options"),
            "description" => t("Special options for the field.")
        ];
        
        $fields_select[] = [
            "type" => "textarea",
            "value" => $_REQUEST["values"],
            "name" => "values",
            "label" => t("Values:"),
            "id" => "valuess",
            "description" => t("A list of values seperated by comma for select, radio and checkbox.")
        ];
        
        $fields_select[] = [
            "type" => "textarea",
            "value" => $_REQUEST["captions"],
            "name" => "captions",
            "label" => t("Captions:"),
            "id" => "captions",
            "description" => t("A list of captions seperated by comma in the same order entered in values in case it is a radio, checkbox or select.")
        ];
        
        $fieldset[] = [
            "fields" => $fields_select,
            "name" => t("Multiple options"),
            "description" => t("Options used in case the type selected is a select, radio or checkbox.")
        ];
        
        $fields_buttons[] = [
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        ];
        
        $fields_buttons[] = [
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        ];
        
        $fieldset[] = ["fields" => $fields_buttons];
        
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


