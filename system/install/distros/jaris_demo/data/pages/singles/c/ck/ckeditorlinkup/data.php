<?php exit; ?>


row: 0

	field: title
		File Uploader
	field;

	field: content
		<?php
        Jaris\Authentication::protectedPage(["add_files"]);
        
        $groups = Jaris\Settings::get("groups", "ckeditor") ?
            unserialize(Jaris\Settings::get("groups", "ckeditor")) : false
        ;
        
        //Check if current user is on one of the groups that can use the editor
        if ($groups) {
            $user_is_in_group = false;
            foreach ($groups as $machine_name => $value) {
                if (Jaris\Authentication::currentUserGroup() == $machine_name && $value) {
                    $user_is_in_group = true;
                    break;
                }
            }
        
            if (!Jaris\Authentication::isAdminLogged() && !$user_is_in_group) {
                exit;
            }
        }
        
        if (empty($_REQUEST["uri"]) || trim($_REQUEST["uri"]) == "") {
            exit;
        }
        
        $uri = $_REQUEST["uri"];
        
        if (!Jaris\Pages::userIsOwner($uri)) {
            Jaris\Authentication::protectedPage();
        }
            ?>
		
		    <!DOCTYPE html>
		    <html lang="en">
		    <head>
		<meta charset="UTF-8">
		<title><?php print t("File Upload") ?></title>
		    </head>
		    <body>
		    <?php
            // Required: anonymous function reference number as explained above.
            $funcNum = $_GET['CKEditorFuncNum'] ;
            // Optional: instance name (might be used to load a specific configuration file or anything else).
            $CKEditor = $_GET['CKEditor'] ;
            // Optional: might be used to provide localized messages.
            $langCode = $_GET['langCode'] ;
            // Optional: compare it with the value of `ckCsrfToken` sent in a cookie to protect your server side uploader against CSRF.
            // Available since CKEditor 4.5.6.
            $token = $_POST['ckCsrfToken'] ;
            // Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
            $url = '';
            // Usually you will only assign something here if the file could not be uploaded.
            $message = "";
            // If error occurs set to true
            $error = false;
        
            //Check maximum permitted file upload have not exceed
            $type_settings = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));
            $maximum_files = $type_settings["uploads"][Jaris\Authentication::currentUserGroup()]["maximum_files"] != "" ?
        $type_settings["uploads"][Jaris\Authentication::currentUserGroup()]["maximum_files"]
        :
        "-1"
            ;
            $file_count = count(Jaris\Pages\Files::getList($_REQUEST["uri"]));
        
            if ($maximum_files == "0") {
                $message = t("File uploads not permitted for this content type.");
                $error = true;
            } elseif ($file_count >= $maximum_files && $maximum_files != "-1") {
                $message = t("Maximum file uploads reached.");
                $error = true;
            }
        
            if (!$error) {
                // Name given to uploaded file
                $file_name = "";
        
                //Store File
                $message = Jaris\Pages\Files::add(
            $_FILES["upload"],
            "",
            $uri,
            $file_name
        );
        
                if ($message == "true") {
                    $message = "";
        
                    $url = Jaris\Uri::url("file/$uri/$file_name");
                }
            }
        
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
            ?>
		    </body>
		    </html>
	field;

	field: rendering_mode
		plain_html
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


