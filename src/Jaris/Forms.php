<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Contains forms functions.
 */
class Forms
{

/**
 * Receives parameters: $parameters, $fieldsets
 * @var string
 */
    const SIGNAL_GENERATE_FORM = "hook_generate_form";

    /**
     * Receives parameters: $form_name, $required
     * @var string
     */
    const SIGNAL_IS_REQUIRED_FIELD_EMPTY =  "hook_is_required_field_empty";

    /**
     * To check if an email address is genuine.
     *
     * @param string $email The email to check.
     * @param bool $checkmx
     *
     * @return bool True on success false on failure.
     */
    public static function validEmail(string $email, bool $checkmx = true): bool
    {
        if (Site::$development_mode) {
            return true;
        }

        $valid = preg_match(
        '/^[_A-z0-9-]+((\.|\+)[_A-z0-9-]+)*@[A-z0-9-]+(\.[A-z0-9-]+)*(\.[A-z]{2,4})$/',
        $email
    );

        if ($valid && $checkmx) {
            //If the function is available we also check the dns record for mx entries
            if (function_exists("checkdnsrr")) {
                list($name, $domain) = explode('@', $email);

                if (!checkdnsrr($domain, 'MX')) {
                    return false;
                }
            }

            return true;
        } elseif ($valid && !$checkmx) {
            return true;
        }

        return false;
    }

    /**
     * Verifies if a username is valid and contains only letters,
     * numbers, dots and dashes.
     *
     * @param string $username The username to check.
     *
     * @return bool True if valid false otherwise.
     */
    public static function validUsername(string $username): bool
    {
        $result = preg_replace("/\w+/", "", $username);

        if ($result != "") {
            return false;
        }

        return true;
    }

    /**
     * Verifies if a input string is valid number and contains only numbers and dots.
     *
     * @param string $input The string to check.
     * @param string $number_type The type of number could be float or integer.
     *
     * @return bool True if valid false otherwise.
     */
    public static function validNumber(string $input, string $number_type = "float"): bool
    {
        $result = "";

        if ($number_type == "integer") {
            $result = preg_replace("/[\d]+/", "", $input);
        } else {
            $result = preg_replace("/[\d\.]+/", "", $input);
        }

        if ($result != "") {
            return false;
        }

        return true;
    }

    /**
     * Enable file uploading to upload.php for the current session
     */
    public static function enableUpload(): void
    {
        $_SESSION["can_upload_file"] = true;
    }

    /**
     * Disable file uploading to upload.php for the current session.
     */
    public static function disableUpload(): void
    {
        unset($_SESSION["can_upload_file"]);
    }

    /**
     * Check if file uploading into upload.php is possible for the current session.
     * @return bool
     */
    public static function canUpload(): bool
    {
        if (isset($_SESSION["can_upload_file"])) {
            return true;
        }

        return false;
    }

    /**
     * Add files uploaded with jquery.fileupload to $_FILES for normal processing.
     *
     * @param string $field_name
     * @param bool $multiple_uploads
     */
    public static function processUploads(
    string $field_name,
    bool $multiple_uploads = false
): void {
        if (!is_array($_REQUEST[$field_name]["names"])) {
            return;
        }

        if ($multiple_uploads) {
            $_FILES[$field_name]["name"] = [];
            $_FILES[$field_name]["tmp_name"] = [];
            $_FILES[$field_name]["type"] = [];

            foreach ($_REQUEST[$field_name]["names"] as $index => $value) {
                $_FILES[$field_name]["name"][] = $_REQUEST[$field_name]["names"][$index];
                $_FILES[$field_name]["tmp_name"][] = self::getUploadPath(
                $_REQUEST[$field_name]["names"][$index]
            );
                $_FILES[$field_name]["type"][] = $_REQUEST[$field_name]["types"][$index];
            }
        } else {
            $first_file = true;
            foreach ($_REQUEST[$field_name]["names"] as $index => $value) {
                //Save first file uploaded only.
                if ($first_file) {
                    $_FILES[$field_name]["name"] = $_REQUEST[$field_name]["names"][$index];
                    $_FILES[$field_name]["tmp_name"] = self::getUploadPath(
                    $_REQUEST[$field_name]["names"][$index]
                );
                    $_FILES[$field_name]["type"] = $_REQUEST[$field_name]["types"][$index];

                    $first_file = false;

                    continue;
                }

                //In case some one uploaded more than 1 file for a field not marked as
                //multiple the rest of the files are deleted.
                unlink(self::getUploadPath($_REQUEST[$field_name]["names"][$index]));
            }
        }
    }

    /**
     * Get file upload path from current session and then remove it from session.
     *
     * @param string $file_name The name of the file
     *
     * @return string Path to file
     */
    public static function getUploadPath(string $file_name): string
    {
        $file_path = $_SESSION["uploaded_files"][$file_name];

        unset($_SESSION["uploaded_files"][$file_name]);

        return $file_path;
    }

    /**
     * Delete all files uploaded by current user.
     * Useful to keep upload dir clean by running it each time the user logs in.
     */
    public static function deleteUploads(): void
    {
        $upload_dir = str_replace(
        "data.php",
        "uploads/",
        Users::getPath(
            Authentication::currentUser(),
            Authentication::currentUserGroup()
        )
    );

        $current_user = strtolower(Authentication::currentUser());

        if (is_dir($upload_dir)) {
            foreach (FileSystem::getFiles($upload_dir) as $file) {
                if ($current_user != "guest") {
                    unlink($file);
                } else {
                    $max_time = 60 * 60 * 60;
                    $current_time = time();
                    $file_time = filectime($file);

                    if (($current_time - $file_time) >= $max_time) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * To check if all required fields on a generated form where filled up.
     * Prints messages of all required empty fields.
     *
     * @param string $form_name Form to check.
     *
     * @return bool true if a required field is empty or false if ok.
     */
    public static function requiredFieldEmpty(string $form_name): bool
    {
        Session::start();

        $required = false;
        if (is_array($_SESSION["required_fields"][$form_name])) {
            foreach ($_SESSION["required_fields"][$form_name] as $fields) {
                if (
                $fields["type"] == "text"
                ||
                $fields["type"] == "textarea"
                ||
                $fields["type"] == "password"
                ||
                $fields["type"] == "autocomplete"
                ||
                $fields["type"] == "uri"
                ||
                $fields["type"] == "uriarea"
                ||
                $fields["number"]
            ) {
                    if (
                    !isset($_REQUEST[$fields["name"]])
                    ||
                    $_REQUEST[$fields["name"]] == ""
                ) {
                        $required = true;
                    }
                } elseif (
                $fields["type"] == "checkbox"
                ||
                $fields["type"] == "radio"
                ||
                $fields["type"] == "select"
            ) {
                    if (
                    !isset($_REQUEST[$fields["name"]])
                    ||
                    $_REQUEST[$fields["name"]] == ""
                ) {
                        $required = true;
                    }
                } elseif ($fields["type"] == "file") {
                    if (
                    (
                        !isset($_FILES[$fields["name"]])
                        ||
                        $_FILES[$fields["name"]]["tmp_name"] == ""
                    )
                    &&
                    (
                        empty($_REQUEST[$fields["name"]])
                        ||
                        empty($_REQUEST[$fields["name"]]["names"])
                    )
                ) {
                        $required = true;
                    }
                }
            }
        } else {
            Uri::go("access-denied");
        }

        self::disableUpload();

        if (is_array($_SESSION["file_upload_fields"][$form_name])) {
            foreach (
            $_SESSION["file_upload_fields"][$form_name]
            as
            $field_name => $multiple
        ) {
                self::processUploads($field_name, $multiple);
            }
        }

        if ($required) {
            View::addMessage(
            t("You need to provide all the required fields, the ones marked with asterik."),
            "error"
        );
        }

        unset($_SESSION["required_fields"][$form_name]);

        unset($_SESSION["file_upload_fields"][$form_name]);

        $not_validated = false;
        if (is_array($_SESSION["validation_fields"][$form_name])) {
            foreach ($_SESSION["validation_fields"][$form_name] as $fields) {
                if ($_REQUEST[$fields["name"]] != $fields["value"]) {
                    if ($fields["type"] == "validate_sum") {
                        View::addMessage(
                        t("A problem occurred when sending the form."),
                        "error"
                    );
                    }

                    $not_validated = true;
                }
            }
        }

        unset($_SESSION["validation_fields"][$form_name]);

        if ($not_validated) {
            return true;
        }

        //Call create_page hook before creating the page
        Modules::hook("hook_is_required_field_empty", $form_name, $required);

        Session::destroyIfEmpty();

        return $required;
    }

    /**
     * static function to create the code of html form.
     *
     * @param array $parameters An array in the format array["parameter_name"] = "value"
     *        for example: parameters["method"] = "post"
     * @param array $fieldsets The needed data to create the form in the format:
     *        $fieldset[] = array(
     *        "name"=>"value", //Optional value if used a <fieldset> with <legend> is generated
     *        "collapsible"=>true or false //Optional value to specify if fieldset should have collapsible class
     *        "collapsed"=>true or false
     *        "description"=>string
     *        "fields" => array(
     *          "type"=>"text, hidden, file, password, submit, reset, select, textarea, radio, checkbox, other",
     *          "id"=>"value",
     *          "name"=>"value",
     *          "class"=>"value" //Optional appended to current class
     *          "label"=>"value", //Optional
     *          "value"=>"value" or for selects, checkbox and radio array("label", "value"), //Optional
     *          "size"=>"value", //Optional
     *          "description"=>"value" //Optional
     *          "readonly"=>true or false //Optional for password or text
     *          "multiple"=>true or false value used on a select
     *          "code"=>"example (width="100%")" //Optional parameters passed to field tags
     *        )
     *        )
     *
     * @return string The html code for a form.
     */
    public static function generate(array $parameters, array $fieldsets): string
    {
        Session::start();

        $form_no_name_warning = false;

        //Keeps the fields user entered values if browser page back/forward
        header("Cache-control: private");

        //Call generate_form hook before running function
        Modules::hook("hook_generate_form", $parameters, $fieldsets);

        if (isset($parameters["name"])) {
            $_SESSION["required_fields"][$parameters["name"]] = [];
        }

        //Check if a field of file type exists
        foreach ($fieldsets as $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                if ($field["type"] == "file") {
                    $parameters["enctype"] = "multipart/form-data";
                    break;
                }
            }
        }

        // Store scripts code that give dynamic functionality to controls to
        // place them on the bottom of form since they conflict with collapse
        // functionality.
        $scripts = "";

        if (!isset($parameters["id"])) {
            $parameters["id"] = md5(strval(time()));
        }

        $form = "<form ";
        foreach ($parameters as $name => $value) {
            $form .= "$name=\"$value\" ";
        }
        $form .= ">\n";

        foreach ($fieldsets as $fieldset) {
            if (isset($fieldset["name"])) {
                $fieldset["collapsible"] = isset($fieldset["collapsible"]) ?
                $fieldset["collapsible"]
                :
                false
            ;

                $fieldset["collapsed"] = isset($fieldset["collapsed"]) ?
                $fieldset["collapsed"]
                :
                false
            ;

                $collapsible = "";
                $legend = "<legend>{$fieldset['name']}</legend>\n";

                if ($fieldset["collapsible"] && $fieldset["collapsed"]) {
                    $collapsible = "class=\"collapsible collapsed\"";

                    $legend = "<legend>"
                    . "<a class=\"expand\" href=\"javascript:void(0)\">"
                    . $fieldset['name']
                    . "</a>"
                    . "</legend>"
                ;
                } else {
                    $collapsible = "class=\"collapsible\"";

                    $legend = "<legend>"
                    . "<a class=\"collapse\" href=\"javascript:void(0)\">"
                    . $fieldset['name']
                    . "</a>"
                    . "</legend>"
                ;
                }

                $form .= "<fieldset $collapsible>\n";
                $form .= $legend;
            }

            foreach ($fieldset["fields"] as $field) {
                $field["name"] = isset($field["name"]) ?
                $field["name"]
                :
                ""
            ;

                $field["value"] = isset($field["value"]) ?
                $field["value"]
                :
                ""
            ;

                $field["id"] = isset($field["id"]) ?
                $field["id"]
                :
                str_replace(
                    ["[", "]"],
                    "",
                    $field["name"]
                )
            ;

                if (isset($parameters["name"])) {
                    $field["id"] = $parameters["name"] . "-" . $field["id"];
                }

                $field["code"] = isset($field["code"]) ?
                $field["code"]
                :
                ""
            ;

                $field["class"] = isset($field["class"]) ?
                $field["class"]
                :
                ""
            ;

                $field["readonly"] = isset($field["readonly"]) ?
                $field["readonly"]
                :
                false
            ;

                $field["required"] = isset($field["required"]) ?
                $field["required"]
                :
                false
            ;

                $field["autofocus"] = isset($field["autofocus"]) ?
                $field["autofocus"]
                :
                false
            ;

                $readonly = "";
                if ($field["readonly"]) {
                    $readonly .= "readonly=\"readonly\"";
                }

                $placeholder = "";
                if (isset($field["placeholder"])) {
                    $placeholder .= "placeholder=\"{$field["placeholder"]}\"";
                }

                $required_attr = "";
                if ($field["required"]) {
                    $required_attr .= "required=\"required\"";
                }

                $autofocus_attr = "";
                if ($field["autofocus"]) {
                    $autofocus_attr .= "autofocus=\"autofocus\"";
                }

                $size_attr = "";
                if (isset($field["size"])) {
                    $size_attr .= "size=\"{$field['size']}\"";
                }

                //Convert special characters to html
                if (is_string($field["value"])) {
                    if (
                    $field["type"] == "textarea" ||
                    $field["type"] == "uriarea" ||
                    $field["type"] == "userarea"
                ) {
                        $field["value"] = htmlspecialchars($field["value"]);
                    } else {
                        $field["value"] = str_replace(
                        '"',
                        "&quot;",
                        $field["value"]
                    );
                    }
                }

                if (
                $field["type"] != "hidden" && $field["type"] != "other" &&
                $field["type"] != "submit" && $field["type"] != "reset"
            ) {
                    $field_inline = isset($field["inline"]) && $field["inline"] == true ?
                    "field_{$field["type"]}_inline field_inline"
                    :
                    ""
                ;

                    $form .= "<div class=\"field field_{$field["type"]} $field_inline\">\n";
                }

                //print label
                if (isset($field["label"]) && $field["type"] != "validate_sum") {
                    // Dont display label for single checkboxe since this
                    // should be added to a fields set
                    if (
                    $field["type"] != "checkbox"
                    ||
                    (
                        $field["type"] == "checkbox"
                        &&
                        is_array($field["value"])
                    )
                ) {
                        $required = "";

                        if ($field["required"]) {
                            //Register field as required on session variable required_fields
                            if (isset($parameters["name"])) {
                                $_SESSION["required_fields"][$parameters["name"]][] = [
                                "type" => $field["type"],
                                "name" => str_replace("[]", "", $field["name"])
                            ];
                            } elseif (Site::$development_mode && !$form_no_name_warning) {
                                View::addMessage(
                                t("Form with required fields doesn't have a proper name."),
                                "error"
                            );

                                $form_no_name_warning = true;
                            }

                            $required = "<span class=\"required\"> *</span>";
                        }

                        $form .= "<div class=\"caption\">"
                        . "<label for=\"{$field['id']}\">"
                        . "<span>{$field['label']}</span>"
                        . $required
                        . "</label>"
                        . "</div>\n"
                    ;
                    }
                }

                if (isset($field['class']) && $field["class"] != "") {
                    $field['class'] = "-" . $field['class'];
                }

                //print field
                if ($field["type"] == "hidden") {
                    $form .= "<input "
                    . "type=\"{$field['type']}\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "/>"
                ;
                } elseif ($field["type"] == "text" || $field["type"] == "password") {
                    $form .= "<input "
                    . "{$field['code']} "
                    . "$placeholder "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"{$field['type']}\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$size_attr $required_attr $autofocus_attr"
                    . "/>"
                ;

                    if ($field["type"] == "password" && isset($field["reveal"])) {
                        $form .= ' <span class="form-checkpassword">'
                        . '<input type="checkbox" id="'.$field['id'].'-check" />'
                        . '<label for="'.$field['id'].'-check"> '
                        . t("Show Password")
                        . '</label>'
                        . '</span>'
                    ;

                        $scripts .= '<script>'
                        . '$("#'.$field['id'].'-check").click(function(){'
                        . 'if($(this).is(":checked")){'
                        . '$("#'.$field['id'].'").attr("type", "text");'
                        . '} else{'
                        . '$("#'.$field['id'].'").attr("type", "password");'
                        . '}'
                        . '});'
                        . '</script>'
                    ;
                    }

                    if (
                    isset($field["limit"]) &&
                    ($field["type"] == "text" || $field["type"] == "password")
                ) {
                        View::addSystemScript("optional/jquery.limit.js");

                        $field["description"] .= " <span "
                        . "class=\"form-chars-left\" "
                        . "id=\"{$field["id"]}-limit\""
                        . ">"
                        . $field['limit']
                        . "</span>"
                        . "&nbsp;"
                        . "<span class=\"form-chars-left-label\">"
                        . t("characters left")
                        . "</span>"
                    ;

                        $scripts .= "<script>"
                        . "$(\"#{$field["id"]}\").limit("
                        . "'{$field['limit']}', "
                        . "'#{$field["id"]}-limit'"
                        . ");"
                        . "</script>"
                    ;
                    }
                } elseif ($field["type"] == "number") {
                    $form .= "<input "
                    . "{$field['code']} "
                    . "$placeholder "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"{$field['type']}\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$size_attr "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . "/>"
                ;
                } elseif ($field["type"] == "file") {
                    self::enableUpload();

                    $multiple = null;
                    $single_upload = "true";
                    if (isset($field["multiple"]) && $field["multiple"]) {
                        $multiple = "multiple";
                        $single_upload = "false";
                        $_SESSION["file_upload_fields"]
                        [$parameters["name"]][$field['name']] = true
                    ;
                    } else {
                        $_SESSION["file_upload_fields"]
                        [$parameters["name"]][$field['name']] = false
                    ;
                    }

                    $description_field = "false";
                    if (
                    isset($field["description_field"]) &&
                    $field["description_field"]
                ) {
                        $description_field = "true";
                    }

                    $max_upload = (int)(ini_get('upload_max_filesize'));
                    $max_post = (int)(ini_get('post_max_size'));
                    $memory_limit = (int)(ini_get('memory_limit'));

                    $upload_max = intval(
                    min($max_upload, $max_post, $memory_limit) * 1024 * 1024
                );

                    if (
                    !isset($field["max_size"]) ||
                    strval($field["max_size"]) == "0" ||
                    strval($field["max_size"]) == ""
                ) {
                        $field["max_size"] = $upload_max;
                    } else {
                        $field["max_size"] *= 1024;
                    }

                    $field['valid_types'] = isset($field['valid_types']) ?
                    $field['valid_types']
                    :
                    ""
                ;

                    $field['max_files'] = isset($field['max_files']) ?
                    $field['max_files']
                    :
                    0
                ;

                    $field['current_files_selector'] =
                    isset($field['current_files_selector']) ?
                        $field['current_files_selector']
                        :
                        ""
                ;

                    $url = "data-url=\"" . Uri::url("upload.php") . "\"";

                    if (isset($field["upload_url"])) {
                        $url = "data-url=\"" . Uri::url($field["upload_url"]) . "\"";
                    }

                    $form .= "<input "
                    . "{$field['code']} "
                    . "$readonly "
                    . "$multiple "
                    . "$url "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"{$field['type']}\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$size_attr "
                    . "/>"
                ;

                    View::addSystemScript("jquery-ui/jquery.ui.js");
                    View::addSystemScript("fileupload/jquery.iframe-transport.js");
                    View::addSystemScript("fileupload/jquery.fileupload.js");
                    View::addSystemScript("fileupload/jquery.fileupload.wrapper.js");

                    $scripts .= '<script>'
                    . '$(document).ready(function(){'
                    . '$("#' . $field['id'] . '").fileuploadwrapper({'
                    . 'showDescriptionField: ' . $description_field . ', '
                    . 'acceptFileTypes: "' . $field['valid_types'] . '", '
                    . 'singleUpload: ' . $single_upload . ', '
                    . 'incorrectFileTypeMessage: "'
                    . t("Incorrect file type selected. The type should be:")
                    . '", '
                    . 'maxFiles: '.intval($field['max_files']).', '
                    . 'maxFilesMessage: "'
                    . (isset($field['max_files_message']) ?
                        $field['max_files_message']
                        : t("Maximum amount of files allowed reached."))
                    . '", '
                    . 'currentFiles: "'
                    . $field['current_files_selector']
                    . '", '
                    . 'maxSize: '.$field["max_size"].', '
                    . 'descriptionPlaceholder: "' . t("description") . '"'
                    . '});'
                    . '});'
                    . '</script>'
                ;
                } elseif ($field["type"] == "color") {
                    View::addSystemScript("jscolor/jscolor.js");

                    $form .= "<input "
                    . "{$field['code']} "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"text\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . "/>"
                ;

                    $scripts .= "<script type=\"text/javascript\">"
                    . "var color_picker = new jscolor("
                    . "document.getElementById('{$field['id']}'), {}"
                    . ");"
                    . "</script>"
                ;
                } elseif ($field["type"] == "gmap-location") {
                    $google_map_key = Settings::get("google_maps", "keys");
                    $map_key_param = !empty($google_map_key) ?
                    "&key=$google_map_key"
                    :
                    ""
                ;

                    View::addScript(
                    "https://maps.googleapis.com/maps/api/js?sensor=false$map_key_param"
                );

                    View::addSystemScript(
                    "jquery-geolocation-edit/jquery.geolocation.edit.min.js"
                );

                    View::addSystemScript(
                    "optional/jquery.clearmap.js"
                );

                    $form .= '<div '
                    . 'id="'.$field['id'].'" '
                    . 'class="form-gmap-location" '
                    . $field['code']
                    . '>'
                    . '</div>'
                    . '<input '
                    . 'type="hidden" '
                    . 'name="'.$field['name'].'" '
                    . 'value="1"'
                    . '>'
                    . '<input '
                    . 'type="hidden" '
                    . 'name="'.$field['lat_name'].'" '
                    . 'id="'.$field['id'].'-lat" '
                    . 'value="'.$field['lat'].'"'
                    . '>'
                    . '<input '
                    . 'type="hidden" '
                    . 'name="'.$field['lng_name'].'" '
                    . 'id="'.$field['id'].'-lng" '
                    . 'value="'.$field['lng'].'"'
                    . '>'
                    . '<input '
                    . 'type="text" '
                    . 'id="'.$field['id'].'-addr" '
                    . 'class="form-gmap-addr" '
                    . 'placeholder="'.t("Enter address to locate").'"'
                    . '>'
                    . '<a '
                    . 'class="form-gmap-locate" '
                    . 'onclick="$(\'#'.$field['id'].'\').geolocate(\'callGeocoding\');"'
                    . '>'
                    . t("Locate")
                    . '</a>'
                    . '<a '
                    . 'class="form-gmap-locate" '
                    . 'onclick="$.clearMap(\''.$field['id'].'\');"'
                    . '>'
                    . t("Clear")
                    . '</a>'
                ;

                    $map_zoom = isset($field["zoom"]) ? intval($field["zoom"]) : 5;

                    $scripts .= "<script>"
                    . "$(document).ready(function(){"
                    . "$('#{$field['id']}').geolocate({"
                    . "lat: '#{$field['id']}-lat',"
                    . "lng: '#{$field['id']}-lng',"
                    . "address: ['#{$field['id']}-addr'],"
                    . "mapOptions: { zoom: $map_zoom },"
                    . "});"
                    . "$('#{$field['id']}-addr').keydown(function(e){"
                    . "if(e.keyCode === 13){"
                    . "$('#{$field['id']}').geolocate('callGeocoding');"
                    . "e.preventDefault();"
                    . "}"
                    . "});"
                    . "});"
                    . "</script>"
                ;
                } elseif ($field["type"] == "autocomplete") {
                    View::addSystemScript("autocomplete/jquery.autocomplete.js");
                    View::addSystemStyle("autocomplete/jquery.autocomplete.css");

                    $form .= "<input "
                    . "{$field['code']} "
                    . "$placeholder "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-text{$field['class']}\" "
                    . "type=\"text\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . "/>"
                ;

                    $scripts .= "<script>"
                    . "$(document).ready(function(){"
                    . "$('#{$field['id']}').autocomplete({"
                    . "serviceUrl:'" . Uri::url($field["service_url"]) . "',"
                    . "minChars:1,"
                    . "maxHeight:400,"
                    . "zIndex: 9999"
                ;

                    if (isset($field["has_labels"])) {
                        $scripts .= ","
                        . "onSelect: function(value, data) {"
                        . "$('input[name=\"{$field['name']}\"]').val(data);"
                        . "}"
                    ;
                    }

                    $scripts .= "});"
                    . "});"
                    . "</script>"
                ;
                } elseif ($field["type"] == "uri") {
                    View::addSystemScript("autocomplete/jquery.autocomplete.js");
                    View::addSystemStyle("autocomplete/jquery.autocomplete.css");

                    $form .= "<input "
                    . "{$field['code']} "
                    . "$placeholder "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-text{$field['class']}\" "
                    . "type=\"text\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . "/>"
                ;

                    $scripts .= "<script>"
                    . "$(document).ready(function(){"
                    . "$('#{$field['id']}').autocomplete({"
                    . "serviceUrl:'" . Uri::url("uris.php") . "',"
                    . "minChars:1,"
                    . "maxHeight:400,"
                    . "zIndex: 9999"
                    . "});"
                    . "});"
                    . "</script>"
                ;
                } elseif ($field["type"] == "uriarea") {
                    View::addSystemScript("autocomplete/jquery.autocomplete.js");
                    View::addSystemStyle("autocomplete/jquery.autocomplete.css");

                    $form .= "<textarea "
                    . "$placeholder "
                    . "$readonly "
                    . "{$field['code']} "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-textarea{$field['class']}\" "
                    . "name=\"{$field['name']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . ">\n"
                    . $field["value"]
                    . "</textarea>\n"
                ;

                    $scripts .= "<script>"
                    . "$(document).ready(function(){"
                    . "$('#{$field['id']}').autocomplete({"
                    . "serviceUrl:'" . Uri::url("uris.php") . "',"
                    . "minChars:1,"
                    . "delimiter: /(,|;)\s*/,"
                    . "maxHeight:400,"
                    . "zIndex: 9999"
                    . "});"
                    . "});"
                    . "</script>"
                ;
                } elseif ($field["type"] == "user") {
                    $form .= "<input "
                    . "{$field['code']} "
                    . "$placeholder "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-text{$field['class']}\" "
                    . "type=\"text\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$required_attr "
                    . "$autofocus_attr "
                    . "/>"
                ;

                    if (
                    Authentication::groupHasPermission(
                        "autocomplete_users",
                        Authentication::currentUserGroup()
                    )
                ) {
                        View::addSystemScript("autocomplete/jquery.autocomplete.js");
                        View::addSystemStyle("autocomplete/jquery.autocomplete.css");

                        $scripts .= "<script>"
                        . "$(document).ready(function(){"
                        . "$('#{$field['id']}').autocomplete({"
                        . "serviceUrl: '" . Uri::url("uris.php?type=users") . "',"
                        . "minChars: 1,"
                        . "maxHeight: 400,"
                        . "zIndex: 9999"
                        . "});"
                        . "});"
                        . "</script>"
                    ;
                    }
                } elseif ($field["type"] == "userarea") {
                    $form .= "<textarea "
                    . "$placeholder "
                    . "$readonly "
                    . "{$field['code']} "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-textarea{$field['class']}\" "
                    . "name=\"{$field['name']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . ">\n"
                    . $field["value"]
                    . "</textarea>\n"
                ;

                    if (
                    Authentication::groupHasPermission(
                        "autocomplete_users",
                        Authentication::currentUserGroup()
                    )
                ) {
                        View::addSystemScript("autocomplete/jquery.autocomplete.js");
                        View::addSystemStyle("autocomplete/jquery.autocomplete.css");

                        $scripts .= "<script>"
                        . "$(document).ready(function(){"
                        . "$('#{$field['id']}').autocomplete({"
                        . "serviceUrl: '" . Uri::url("uris.php?type=users") . "',"
                        . "minChars: 1,"
                        . "delimiter: /(,|;)\s*/,"
                        . "maxHeight: 400,"
                        . "zIndex: 9999"
                        . "});"
                        . "});"
                        . "</script>"
                    ;
                    }
                } elseif ($field["type"] == "date") {
                    View::addSystemScript("jdpicker/jquery.jdpicker.js");
                    View::addSystemStyle("jdpicker/jdpicker.css");

                    $form .= "<input "
                    . "$placeholder "
                    . "{$field['code']} "
                    . "$readonly "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"text\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "$size_attr $required_attr $autofocus_attr"
                    . "/>"
                ;

                    $date_format = "FF dd YYYY";

                    if ($field["format"]) {
                        $date_format = $field["format"];
                    }

                    $scripts .= "<script type=\"text/javascript\">\n"
                    . "\$(document).ready(function(){\n"
                    . "$('#{$field['id']}').jdPicker({"
                    . "month_names: ["
                    . "\"" . t("January") . "\", "
                    . "\"" . t("February") . "\", "
                    . "\"" . t("March") . "\", "
                    . "\"" . t("April") . "\", "
                    . "\"" . t("May") . "\", "
                    . "\"" . t("June") . "\", "
                    . "\"" . t("July") . "\", "
                    . "\"" . t("August") . "\", "
                    . "\"" . t("September") . "\", "
                    . "\"" . t("October") . "\", "
                    . "\"" . t("November") . "\", "
                    . "\"" . t("December") . "\""
                    . "],\n"
                    . "short_month_names: ["
                    . "\"" . t("Jan") . "\", "
                    . "\"" . t("Feb") . "\", "
                    . "\"" . t("Mar") . "\", "
                    . "\"" . t("Apr") . "\", "
                    . "\"" . t("May") . "\", "
                    . "\"" . t("Jun") . "\", "
                    . "\"" . t("Jul") . "\", "
                    . "\"" . t("Aug") . "\", "
                    . "\"" . t("Sep") . "\", "
                    . "\"" . t("Oct") . "\", "
                    . "\"" . t("Nov") . "\", "
                    . "\"" . t("Dec") . "\""
                    . "],\n"
                    . "short_day_names: ["
                    . "\"" . t("SU") . "\", "
                    . "\"" . t("MO") . "\", "
                    . "\"" . t("TU") . "\", "
                    . "\"" . t("WE") . "\", "
                    . "\"" . t("TH") . "\", "
                    . "\"" . t("FR") . "\", "
                    . "\"" . t("SA") . "\""
                    . "],\n"
                    . "error_out_of_range: "
                    . "\"" . t("Selected date is out of range") . "\",\n"
                    . "date_format: \"$date_format\"\n"
                    . "});"
                    . "});\n"
                    . "</script>\n"
                ;
                } elseif ($field["type"] == "radio") {
                    $field["checked"] = isset($field["checked"]) ?
                    $field["checked"]
                    :
                    false
                ;

                    $radio_index = 1;
                    foreach ($field["value"] as $label => $value) {
                        if (
                        isset($field["horizontal_list"]) &&
                        $field["horizontal_list"]
                    ) {
                            $form .= "<div>";
                        }

                        $checked = "";
                        if ($field["checked"] == $value) {
                            $checked = "checked=\"checked\"";
                        }

                        $value = htmlspecialchars($value);

                        $form .= "<input "
                        . "$checked "
                        . "{$field['code']} "
                        . "id=\"{$field['id']}-$radio_index-$value\" "
                        . "class=\"form-{$field['type']}{$field['class']}\" "
                        . "type=\"{$field['type']}\" "
                        . "name=\"{$field['name']}\" "
                        . "value=\"$value\" "
                        . "/> "
                        . "<label for=\"{$field['id']}-$radio_index-$value\">"
                        . "<span>$label</span>"
                        . "</label>\n"
                    ;

                        if (
                        isset($field["horizontal_list"]) &&
                        $field["horizontal_list"]
                    ) {
                            $form .= "</div>\n";
                        }

                        $radio_index++;
                    }
                } elseif ($field["type"] == "checkbox") {
                    $field["checked"] = isset($field["checked"]) ?
                    $field["checked"]
                    :
                    false
                ;

                    if (is_array($field["value"])) {
                        $check_index = 0;
                        foreach ($field["value"] as $label => $value) {
                            if ($field["horizontal_list"]) {
                                $form .= '<div>';
                            } else {
                                $form .= '<div class="form-checkbox-entry">';
                            }

                            $checked = "";
                            if (is_array($field["checked"])) {
                                if (in_array($value, $field["checked"])) {
                                    $checked = "checked=\"checked\"";
                                }
                            }

                            $value = htmlspecialchars($value);

                            $form .= "<input "
                            . "$checked "
                            . "{$field['code']} "
                            . "id=\"{$field['id']}-$check_index-{$field['value']}\" "
                            . "class=\"form-{$field['type']}{$field['class']}\" "
                            . "type=\"{$field['type']}\" "
                            . "name=\"{$field['name']}[]\" "
                            . "value=\"$value\" "
                            . "/> "
                            . "<label for=\"{$field['id']}-$check_index-{$field['value']}\">"
                            . "<span>$label</span>"
                            . "</label>\n"
                        ;

                            $form .= "</div>\n";

                            $check_index++;
                        }
                    } else {
                        $checked = "";
                        if ($field["checked"] == true) {
                            $checked = "checked=\"checked\"";
                        }

                        $value = "";
                        if (trim($field["value"]) != "") {
                            $value = "value=\"{$field['value']}\"";
                        }

                        $form .= "<label for=\"{$field['id']}-{$field["value"]}\">"
                        . "<span>{$field['label']}</span>"
                        . "</label> "
                        . "<input "
                        . "$checked "
                        . "{$field['code']} "
                        . "$value "
                        . "id=\"{$field['id']}-{$field["value"]}\" "
                        . "class=\"form-{$field['type']}{$field['class']}\" "
                        . "type=\"{$field['type']}\" "
                        . "name=\"{$field['name']}\" "
                        . "/> \n"
                    ;
                    }
                } elseif ($field["type"] == "select") {
                    $lang = explode("-", Language::getCurrent())[0];

                    View::addSystemStyle("select2/select2.min.css");
                    View::addSystemScript("select2/select2.min.js");

                    if (
                    $lang != "en"
                    &&
                    file_exists(
                        System::JS_PATH . "select2/i18n/$lang.js"
                    )
                ) {
                        View::addSystemScript("select2/i18n/$lang.js");
                    }

                    $field["multiple"] = isset($field["multiple"]) ?
                    $field["multiple"]
                    :
                    false
                ;

                    $field["selected"] = isset($field["selected"]) ?
                    $field["selected"]
                    :
                    ""
                ;

                    $multiple = "";
                    if ($field["multiple"]) {
                        $multiple = "multiple=\"multiple\"";
                    }

                    $form .= "<select "
                    . "{$field['code']} "
                    . "$multiple "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "name=\"{$field['name']}\" "
                    . "$required_attr "
                    . "$autofocus_attr"
                    . ">\n"
                ;

                    foreach ($field["value"] as $label => $value) {
                        //For compatibility with jaris realty
                        if ($label === "optgroup") {
                            foreach ($value as $options) {
                                $form .= "<optgroup label=\"{$options['label']}\">";

                                foreach (
                                $options["values"]
                                as
                                $option_label => $option_value
                            ) {
                                    $selected = "";
                                    if ($field["selected"] == $option_value) {
                                        $selected = "selected=\"selected\"";
                                    }

                                    $form .= "<option "
                                    . "$selected "
                                    . "value=\"$option_value\""
                                    . ">"
                                    . $option_label
                                    . "</option>\n"
                                ;
                                }
                                $form .= "</optgroup>";
                            }
                        }//Compatibility up to here
                        else {
                            $selected = "";
                            if ($field["multiple"] || is_array($field["selected"])) {
                                if (is_array($field["selected"])) {
                                    foreach ($field["selected"] as $selected_value) {
                                        if ("" . $selected_value . "" == "" . $value . "") {
                                            $selected = "selected=\"selected\"";
                                        }
                                    }
                                } elseif ("" . $field["selected"] . "" == "" . $value . "") {
                                    $selected = "selected=\"selected\"";
                                }
                            } elseif ("" . $field["selected"] . "" == "" . $value . "") {
                                $selected = "selected=\"selected\"";
                            }

                            $value = htmlspecialchars($value);

                            $form .= "<option "
                            . "$selected "
                            . "value=\"$value\""
                            . ">"
                            . $label
                            . "</option>\n"
                        ;
                        }
                    }
                    $form .= "</select>\n";

                    $select_placeholder = "";
                    if ($field["multiple"]) {
                        if ($field["placeholder"]) {
                            $select_placeholder .= ", "
                            . "placeholder: '".$field["placeholder"]."'"
                        ;
                        } else {
                            $select_placeholder .= ", "
                            . "placeholder: '".t("select values")."'"
                        ;
                        }
                    }

                    $tags = isset($field["tags"]) && $field["tags"] ?
                    "true"
                    :
                    "false"
                ;

                    $scripts .= "<script type=\"text/javascript\">\n"
                    . "\$(document).ready(function(){\n"
                    . "$('#{$field['id']}').select2("
                    . "{"
                    . "language: '$lang', "
                    . "tags: $tags"
                    . $select_placeholder
                    . "}"
                    . ");\n"
                    . "});\n"
                    . "</script>\n"
                ;
                } elseif ($field["type"] == "textarea") {
                    $form .= "<textarea "
                    . "$placeholder "
                    . "$readonly "
                    . "{$field['code']} "
                    . "id=\"{$field['id']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "name=\"{$field['name']}\" "
                    . "$autofocus_attr"
                    . ">\n"
                    . $field["value"]
                    . "</textarea>\n"
                ;

                    if (isset($field["limit"])) {
                        View::addSystemScript("optional/jquery.limit.js");

                        $field["description"] .= " <span "
                        . "class=\"form-chars-left\" "
                        . "id=\"{$field["id"]}-limit\""
                        . ">"
                        . $field['limit']
                        . "</span>"
                        . "&nbsp;"
                        . "<span class=\"form-chars-left-label\">"
                        . t("characters left")
                        . "</span>"
                    ;

                        $scripts .= "<script>"
                        . "$(\"#{$field["id"]}\").limit("
                        . "'{$field['limit']}', "
                        . "'#{$field["id"]}-limit'"
                        . ");"
                        . "</script>"
                    ;
                    }
                } elseif ($field["type"] == "other") {
                    $form .= $field["html_code"];
                } elseif ($field["type"] == "validate_sum") {
                    $num1 = rand(1, 10);
                    $num2 = rand(1, 20);
                    $result = $num1 + $num2;

                    $_SESSION["validation_fields"]
                    [$parameters["name"]]
                    [$field["name"]] = [
                        "type" => $field["type"],
                        "name" => $field["name"],
                        "value" => $result
                ];

                    View::addSystemScript("jquery-base64/jquery.base64.min.js");

                    $values = [];
                    $last_value = rand(5, 100);
                    for ($i=0; $i<=$last_value; $i++) {
                        $values[] = "/* $.base64.decode('"
                        . base64_encode(
                            "$('#{$field['id']}').val('".\Jaris\Users::generatePassword(2)."');"
                        )
                        . "') */"
                    ;
                    }

                    $values[rand(0, $last_value)] = " $.base64.decode('"
                    . base64_encode(
                        "$('#{$field['id']}').val('$result');"
                    )
                    . "') "
                ;

                    $values_string = "";
                    foreach ($values as $value) {
                        $values_string .= $value;
                    }

                    $add_field = "eval( $.base64.decode('"
                    . base64_encode(
                        "$('#{$parameters['id']}').append("
                        . "'<input type=\"hidden\" name=\"{$field['name']}\" id=\"{$field['id']}\" />'"
                        . ");"
                    )
                    . "') );"
                ;

                    $scripts .= "<script>"
                    . "$(document).ready(function(){"
                    . $add_field
                    . "eval("
                    . $values_string
                    . ");"
                    . "});"
                    . "</script>"
                ;
                } elseif ($field["type"] == "submit" || $field["type"] == "reset") {
                    $novalidate = "";
                    if (isset($field["novalidate"]) && $field["novalidate"]) {
                        $novalidate .= 'formnovalidate';
                    } elseif ($field["value"] == t("Cancel") || Site::$development_mode) {
                        $novalidate .= "formnovalidate";
                    }

                    $form .= "<input "
                    . "$novalidate "
                    . "{$field['code']} "
                    . "id=\"{$field['name']}\" "
                    . "class=\"form-{$field['type']}{$field['class']}\" "
                    . "type=\"{$field['type']}\" "
                    . "name=\"{$field['name']}\" "
                    . "value=\"{$field['value']}\" "
                    . "/> "
                ;
                }

                //Print description of field
                if (isset($field["description"])) {
                    $form .= "<div class=\"description\">\n"
                    . "<span>{$field['description']}</span>\n"
                    . "</div>\n"
                ;
                }

                if (
                $field["type"] != "hidden" && $field["type"] != "other"
                &&
                $field["type"] != "submit" && $field["type"] != "reset"
            ) {
                    $form .= "</div>\n";
                }
            }

            if (isset($fieldset["name"])) {
                if (isset($fieldset["description"])) {
                    $form .= "<p class=\"fieldset-description\">"
                    . $fieldset['description']
                    . "</p>\n"
                ;
                }

                $form .= "</fieldset>\n";
            }
        }

        $form .= "</form>\n";

        $form .= $scripts;

        return $form;
    }

    /**
     * Helper static function to generate the starting html of a collapsible fieldset.
     *
     * @param string $title
     * @param bool $collapsible
     * @param bool $collapsed
     *
     * @return string
     */
    public static function beginFieldset(
    string $title,
    bool $collapsible=true,
    bool $collapsed=false
): string {
        $html = "";

        $collapsible_class = "";
        $legend = "<legend>$title</legend>\n";

        if ($collapsible && $collapsed) {
            $collapsible_class = "class=\"collapsible collapsed\"";
            $legend = "<legend>"
            . "<a class=\"expand\" href=\"javascript:void(0)\">$title</a>"
            . "</legend>"
        ;
        } else {
            $collapsible_class = "class=\"collapsible\"";
            $legend = "<legend>"
            . "<a class=\"collapse\" href=\"javascript:void(0)\">$title</a>"
            . "</legend>"
        ;
        }

        $html .= "<fieldset $collapsible_class>\n";
        $html .= $legend;

        return $html;
    }

    /**
     * Helper static function to generate the closing html of a collapsible fieldset.
     *
     * @param string $description
     *
     * @return string
     */
    public static function endFieldset(string $description=""): string
    {
        $html = "";

        if (strlen($description) > 0) {
            $html .= "<p class=\"fieldset-description\">$description</p>\n";
        }

        $html .= "</fieldset>\n";

        return $html;
    }

    /**
     * Adds a new field to a form fieldset array after a given field name.
     *
     * @param array $field The new field to add.
     * @param string $field_name The name of the field used as reference to insert
     * new one.
     * @param array $fieldset Reference to the fieldset array where new field is
     * going to be inserted.
     */
    public static function addFieldAfter(
    array $field,
    string $field_name,
    array &$fieldset
): void {
        self::addField($field, $field_name, $fieldset);
    }

    /**
     * Adds a new field to a form fieldset array before a given field name.
     *
     * @param array $field The new field to add.
     * @param string $field_name The name of the field used as reference to insert
     * new one.
     * @param array $fieldset Reference to the fieldset array where new field is
     * going to be inserted.
     */
    public static function addFieldBefore(
    array $field,
    string $field_name,
    array &$fieldset
): void {
        self::addField($field, $field_name, $fieldset, true);
    }

    /**
     * Adds a new field to a form fieldset array after or before a given field name.
     *
     * @param array $field The new field to add.
     * @param string $field_name The name of the field used as reference to insert
     * new one.
     * @param array $fieldset Reference to the fieldset array where new field is
     * going to be inserted.
     * @param bool $before Flag which indicates if new field should be inserted
     * before the given field name or after it.
     */
    public static function addField(
    array $field,
    string $field_name,
    array &$fieldset,
    bool $before=false
): void {
        $fields = [$field];

        self::addFields($fields, $field_name, $fieldset, $before);
    }

    /**
     * Adds a new set of fields to a form fieldset array after or before a given field name.
     *
     * @param array $fields The new array of fields to add.
     * @param string $field_name The name of the field used as reference to insert
     * new one.
     * @param array $fieldset Reference to the fieldsets array where new field is
     * going to be inserted.
     * @param bool $before Flag which indicates if new field should be inserted
     * before the given field name or after it.
     */
    public static function addFields(
    array $fields,
    string $field_name,
    array &$fieldset,
    bool $before=false
): void {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            $new_fields = [];
            $found_field_name = false;

            foreach ($fieldset_data["fields"] as $field_data) {
                if (isset($field_data["name"])) {
                    if ($field_data["name"] == $field_name) {
                        $found_field_name = true;

                        if ($before) {
                            foreach ($fields as $field) {
                                $new_fields[] = $field;
                            }
                        } else {
                            $new_fields[] = $field_data;

                            foreach ($fields as $field) {
                                $new_fields[] = $field;
                            }

                            continue;
                        }
                    }
                }

                $new_fields[] = $field_data;
            }

            if ($found_field_name) {
                $fieldset[$fieldset_index]["fields"] = $new_fields;

                return;
            }
        }
    }

    /**
     * Move a field to another position in a fieldset.
     *
     * @param string $field_name The name of the field to move.
     * @param string $sibling_name The name of sibling field to move near to.
     * @param array $fieldset Reference to the fieldsets array.
     * @param bool $before False to move before sibling.
     *
     * @return bool true if moved otherwise false.
     */
    public static function moveField(
    string $field_name,
    string $sibling_name,
    array &$fieldset,
    bool $before=false
): bool {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            $field = [];
            $found_field_name = false;

            foreach ($fieldset_data["fields"] as $field_data) {
                if (isset($field_data["name"])) {
                    if ($field_data["name"] == $field_name) {
                        $found_field_name = true;

                        $field = $field_data;

                        break;
                    }
                }
            }

            if ($found_field_name) {
                self::deleteField($field_name, $fieldset);

                self::addField($field, $sibling_name, $fieldset, $before);

                return true;
            }
        }

        return false;
    }

    /**
     * Move an unamed other field to another position in a fieldset.
     *
     * @param string $content_match String inside the field html to match.
     * @param string $sibling_name The name of sibling field to move near to.
     * @param array $fieldset Reference to the fieldsets array.
     * @param bool $before False to move before sibling.
     *
     * @return bool true if moved otherwise false.
     */
    public static function moveOtherField(
    string $content_match,
    string $sibling_name,
    array &$fieldset,
    bool $before=false
): bool {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            $field = [];
            $found_field_name = false;

            foreach ($fieldset_data["fields"] as $field_data) {
                if ($field_data["type"] == "other") {
                    if (strstr($field_data["html_code"], $content_match) !== false) {
                        $found_field_name = true;

                        $field = $field_data;

                        break;
                    }
                }
            }

            if ($found_field_name) {
                self::deleteOtherField($content_match, $fieldset);

                self::addField($field, $sibling_name, $fieldset, $before);

                return true;
            }
        }

        return false;
    }

    /**
     * Gets a field reference to be able to modify it.
     *
     * @param string $field_name The name of the field to retreive.
     * @param array $fieldset Reference to the fieldsets array.
     *
     * @return array Field or empty array if not found.
     */
    public static function &getField(string $field_name, array &$fieldset): array
    {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            foreach ($fieldset_data["fields"] as $field_index=>$field_data) {
                if (isset($field_data["name"])) {
                    if ($field_data["name"] == $field_name) {
                        return $fieldset[$fieldset_index]
                        ["fields"]
                        [$field_index]
                    ;
                    }
                }
            }
        }

        return [];
    }

    /**
     * Removes a field from a given fieldset.
     *
     * @param string $field_name The name of the field to remove.
     * @param array $fieldset Reference to the fieldsets array.
     *
     * @return bool true if removed otherwise false.
     */
    public static function deleteField(string $field_name, array &$fieldset): bool
    {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            $new_fields = [];
            $found_field_name = false;

            foreach ($fieldset_data["fields"] as $field_data) {
                if (isset($field_data["name"])) {
                    if ($field_data["name"] == $field_name) {
                        $found_field_name = true;

                        continue;
                    }
                }

                $new_fields[] = $field_data;
            }

            if ($found_field_name) {
                $fieldset[$fieldset_index]["fields"] = $new_fields;

                return true;
            }
        }

        return false;
    }

    /**
     * Removes an unnamed other field from a given fieldset.
     *
     * @param string $content_match The html content of the field to match.
     * @param array $fieldset Reference to the fieldsets array.
     *
     * @return bool true if removed otherwise false.
     */
    public static function deleteOtherField(string $content_match, array &$fieldset): bool
    {
        foreach ($fieldset as $fieldset_index=>$fieldset_data) {
            $new_fields = [];
            $found_field_name = false;

            foreach ($fieldset_data["fields"] as $field_data) {
                if ($field_data["type"] == "other") {
                    if (strstr($field_data["html_code"], $content_match)) {
                        $found_field_name = true;

                        continue;
                    }
                }

                $new_fields[] = $field_data;
            }

            if ($found_field_name) {
                $fieldset[$fieldset_index]["fields"] = $new_fields;

                return true;
            }
        }

        return false;
    }

    /**
     * Add a new fieldset with fields to an array of fieldsets.
     *
     * @param array $fieldsets
     * @param string|int $position Can be the name of an existing
     * fieldset or numeric position.
     * @param array $fieldset Existing fieldset array where adding adding the
     * additional fieldsets.
     * @param bool $before Indicates if the fieldset should be added
     * before of after the indicated position
     */
    public static function addFieldsets(
    array $fieldsets,
    $position,
    array &$fieldset,
    bool $before=false
): void {
        if (is_string($position)) {
            foreach ($fieldset as $index=>$fieldset_data) {
                if ($fieldset_data["name"] == t($position)) {
                    $position = $index;
                    break;
                }
            }
        }

        $new_fieldsets = [];

        if ($before) {
            $new_fieldsets = array_merge($fieldsets, [$fieldset[$position]]);
        } else {
            $new_fieldsets = array_merge([$fieldset[$position]], $fieldsets);
        }

        array_splice($fieldset, $position, 1, $new_fieldsets);
    }

    /**
     * Add a new fieldset with fields to an array of fieldsets.
     *
     * @param string|int $position Can be the name of an existing
     * fieldset or numeric position.
     * @param array $fieldset Existing fieldset array where adding adding the
     * additional fieldsets.
     */
    public static function &getFieldset(
    $position,
    array &$fieldset
): array {
        if (is_string($position)) {
            foreach ($fieldset as $index=>$fieldset_data) {
                if ($fieldset_data["name"] == t($position)) {
                    $position = $index;
                    break;
                }
            }
        }

        return $fieldset[$position];
    }

    /**
     * Add a new fieldset with fields to an array of fieldsets.
     *
     * @param string|int $position Can be the name of an existing
     * fieldset or numeric position.
     * @param array $fieldset Existing fieldset array where adding adding the
     * additional fieldsets.
     */
    public static function deleteFieldset(
    $position,
    array &$fieldset
): void {
        if (is_string($position)) {
            foreach ($fieldset as $index=>$fieldset_data) {
                if ($fieldset_data["name"] == t($position)) {
                    $position = $index;
                    break;
                }
            }
        }

        unset($fieldset[$position]);
    }

    /**
     * Moves an existing fieldset with fields to another position.
     *
     * @param string|int $position Can be the name of an existing
     * fieldset or numeric position.
     * @param string|int $sibling_position Can be the name of an existing
     * fieldset or numeric position.
     * @param array $fieldsets
     * @param bool $before Indicates if the fieldset should be added
     * before of after the indicated position
     */
    public static function moveFieldset(
    $position,
    $sibling_position,
    array &$fieldset,
    bool $before=false
): void {
        if (is_string($position)) {
            foreach ($fieldset as $index=>$fieldset_data) {
                if ($fieldset_data["name"] == t($position)) {
                    $position = $index;
                    break;
                }
            }
        }

        if (is_string($sibling_position)) {
            foreach ($fieldset as $index=>$fieldset_data) {
                if ($fieldset_data["name"] == t($sibling_position)) {
                    $sibling_position = $index;
                    break;
                }
            }
        }

        $new_fieldset_positions = [];

        if ($before) {
            $new_fieldset_positions[] = $fieldset[$position];
            $new_fieldset_positions[] = $fieldset[$sibling_position];
        } else {
            $new_fieldset_positions[] = $fieldset[$sibling_position];
            $new_fieldset_positions[] = $fieldset[$position];
        }

        unset($fieldset[$position]);

        array_splice($fieldset, $sibling_position, 1, $new_fieldset_positions);
    }
}
