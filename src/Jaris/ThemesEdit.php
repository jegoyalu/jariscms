<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to edit a theme custom settings.
 */
class ThemesEdit
{

/**
 * Gets a theme custom settings.
 *
 * @param string $theme
 * @param bool $expanded
 *
 * @return array
 */
static function settings(string $theme, bool $expanded=false): array
{
    $theme_path = Themes::directory($theme);

    $defaults = array();

    if(file_exists($theme_path . "settings.php"))
    {
        include $theme_path . "settings.php";
    }

    $settings = array();
    $config_file = Files::get("config.php", "themes/".$theme);

    $files_path = self::getFilesPath($theme);
    $file_fields = self::getFileFields($theme);

    if($config_file != "")
    {
        $settings = Data::get(0, $config_file);

        foreach($settings as $field_name=>&$field)
        {
            if(empty($field) && isset($defaults[$field_name]))
            {
                if(!$expanded)
                {
                    $field = $defaults[$field_name];
                }
                else
                {
                    if(in_array($field_name, $file_fields))
                    {
                        $field = Uri::url(
                            self::expand($defaults[$field_name], $theme)
                        );
                    }
                    else
                    {
                        $field = self::expand($defaults[$field_name], $theme);
                    }
                }
            }
            elseif(!empty($field) && $expanded)
            {
                if(in_array($field_name, $file_fields))
                {
                    if(strstr($field, "{"))
                    {
                        $field = Uri::url(
                            self::expand($defaults[$field_name], $theme)
                        );
                    }
                    else
                    {
                        $field = Uri::url($files_path . $field);
                    }
                }
            }
        }
    }
    elseif($expanded)
    {
        foreach($defaults as $field_name=>&$field)
        {
            $field = self::expand($field, $theme);
        }
    }

    $settings += $defaults;

    return $settings;
}

/**
 * Saves a given set of settings for a specific theme.
 *
 * @param array $settings
 * @param string $theme
 *
 * @return bool True on success false otherwise.
 */
static function saveSettings(array $settings, string $theme):bool
{
    $theme_path = Themes::directory($theme);

    $defaults = array();

    if(file_exists($theme_path . "settings.php"))
    {
        include $theme_path . "settings.php";
    }

    $config_file = self::getFilesPath($theme) . "config.php";

    $settings += $defaults;

    if(Data::edit(0, $settings, $config_file))
    {
        return true;
    }

    return false;
}

/**
 * Expands a value place holders.
 *
 * @param string $value
 * @param string $theme
 *
 * @return string
 */
private static function expand(string $value, string $theme): string
{
    $theme_path = Themes::directory($theme);

    return str_replace(
        array(
            "{theme_path}"
        ),
        array(
            rtrim($theme_path, "/")
        ),
        $value
    );
}

/**
 * Gets a list of fieldsets that are read from a theme settings.php file
 * to generate the Theme Settings Form.
 *
 * @param string $theme
 *
 * @return array List of fieldsets.
 */
private static function getFieldsSet(string $theme): array
{
    $fieldsets = array();

    $theme_path = Themes::directory($theme);

    if(file_exists($theme_path . "settings.php"))
    {
        include $theme_path . "settings.php";
    }

    $logo_fields = array(
        array(
            "type" => "image",
            "name" => "logo",
            "label" => t("Logo:"),
            "description" => t("Custom logo for your site.")
        )
    );

    $fieldset = array(
        array("fields" => $logo_fields)
    );

    $css_js_fields = array(
        array(
            "type" => "textarea",
            "name" => "css",
            "label" => t("CSS:"),
            "description" => t("Additional styling to apply into the theme.")
        ),
        array(
            "type" => "textarea",
            "name" => "js",
            "label" => t("JavaScript:"),
            "description" => t("Additional JavaScript code to execute by the theme.")
        )
    );

    $fieldset[] = array(
        "name" => "CSS and JavaScript",
        "collapsible" => true,
        "collapsed" => true,
        "fields" => $css_js_fields
    );

    return $fieldsets = array_merge($fieldset, $fieldsets);
}
/**
 * Get a list of fields that are a file or image.
 *
 * @param string $theme
 *
 * @return array List of fields that are a file or image.
 */
static function getFileFields(string $theme): array
{
    $fieldsets = self::getFieldsSet($theme);

    $file_fields = array();

    foreach($fieldsets as &$fieldset)
    {
        foreach($fieldset["fields"] as $position => $field)
        {
            if($field["type"] == "file")
            {
                $file_fields[] = $field["name"];
            }
            elseif($field["type"] == "image")
            {
                $file_fields[] = $field["name"];
            }
        }
    }

    return $file_fields;
}

/**
 * Generates the theme settings form and also adds boiler plate code for
 * images and files upload.
 *
 * @param string $theme
 *
 * @return string The form code.
 */
static function generateForm(string $theme): string
{
    if(
        isset($_REQUEST["btnSave"])
        &&
        !Forms::requiredFieldEmpty("theme-settings")
        &&
        self::validUploads($theme)
    )
    {
        self::processRequest($theme);
        View::addMessage(t("Theme settings saved."));
    }
    elseif(isset($_REQUEST["btnCancel"]))
    {
        Forms::requiredFieldEmpty("theme-settings");
        Uri::go("admin/themes");
    }
    elseif(isset($_REQUEST["btnReset"]))
    {
        Forms::requiredFieldEmpty("theme-settings");
        FileSystem::recursiveRemoveDir(Files::getDir("themes/$theme"));
        View::addMessage(t("Theme settings reset."));
        Uri::go("admin/themes/view", array("path" => $_REQUEST["path"]));
    }

    $fieldsets = self::getFieldsSet($theme);

    $files_path = self::getFilesPath($theme);

    $values = self::settings($theme);

    foreach($fieldsets as &$fieldset)
    {
        $form_fields = array();

        foreach($fieldset["fields"] as $position => $field)
        {
            if($field["type"] == "file")
            {
                $description = "";
                if(trim($field["description"]) != "")
                {
                    //To add a space after user entered description
                    $description .= " ";
                }

                $description .= t("Allowed file types:") . " ";

                if(trim($field["valid_types"]) != "")
                {
                    $description .= $field["valid_types"];
                }
                else
                {
                    $description .= t("all");
                }

                $description .= ". ";
                $description .= t("Maximum allowed size is:") . " ";

                if($field["max_size"] > 0)
                {
                    $description .= intval($field["max_size"]) . "K";
                }
                else
                {
                    $description .= ini_get("upload_max_filesize");
                }

                if($field["max_files"] > 0)
                {
                    $description .= ". "
                        . t("Maximum amount of files allowed:")
                        . " " . $field["max_files"]
                    ;
                }

                $field["description"] .= $description;
                $field["current_files_selector"] =
                    "#current-files-{$field["name"]} tr"
                ;

                $form_fields[] = $field;

                if(!empty($values[$field["name"]]))
                {
                    $files = explode(",", $values[$field["name"]]);

                    $files_html = "<table "
                        . "id=\"current-files-{$field["name"]}\" "
                        . "class=\"file-uploaded-list\""
                        . ">"
                    ;

                    $files_html .= "<tbody>";

                    foreach($files as $file)
                    {
                        $onclick = "$(this).parent().parent().remove();";

                        $files_html .= "<tr>";

                        $file_expand = self::expand($file, $theme);

                        if($file_expand != $file)
                        {
                            $files_html .= "<td class=\"file\">"
                                . "<a target=\"_blank\" href=\""
                                . Uri::url($file_expand)
                                . "\">"
                                . $file
                                . "</a>"
                                . "</td>"
                            ;
                        }
                        else
                        {
                            $files_html .= "<td class=\"file\">"
                                . "<a target=\"_blank\" href=\""
                                . Uri::url($files_path . $file)
                                . "\">"
                                . $file
                                . "</a>"
                                . "</td>"
                            ;
                        }

                        /*if($field["description_field"])
                        {
                            $file_data = Pages\Files::getByName(
                                $file, $_REQUEST["uri"]
                            );

                            $files_html .= "<td class=\"file\">"
                                . '<input '
                                . 'type="text" '
                                . 'placeholder="'.t('description').'" '
                                . 'name="'.$field["name"].'_desc[]" '
                                . 'value="'.$file_data["description"].'" '
                                . '/>'
                                . "</td>"
                            ;
                        }*/

                        $files_html .= "<td class=\"delete\">"
                            . '<input '
                            . 'type="hidden" '
                            . 'name="'.$field["name"].'_current[]" '
                            . 'value="'.$file.'" '
                            . '/>'
                            . "<a onclick=\"$onclick\">"
                            . "x"
                            . "</a>"
                            . "</td>"
                        ;

                        $files_html .= "</tr>";
                    }

                    $files_html .= "</tbody>";
                    $files_html .= "</table>";

                    $label = t("Current file:");

                    if(count($files) > 1)
                    {
                        $label = t("Current files:");
                    }

                    $form_fields[] = array(
                        "type" => "other",
                        "html_code" => "<div class=\"current-file\">"
                            . "<strong>"
                            . $label
                            . "</strong> "
                            . $files_html
                            . "</div>"
                    );
                }
            }
            elseif($field["type"] == "image")
            {
                $description = "";
                if(trim($field["description"]) != "")
                {
                    //To add a space after user entered description
                    $description .= " ";
                }

                $description .= t("Allowed image types: jpeg, png, gif");

                $description .= ". ";

                $description .= t("Maximum allowed size is:") . " ";

                if($field["max_size"] > 0)
                {
                    $description .= intval($field["max_size"]) . "K";
                }
                else
                {
                    $description .= ini_get("upload_max_filesize");
                }

                if($field["max_files"] > 0)
                {
                    $description .= ". "
                        . t("Maximum amount of images allowed:")
                        . " " . $field["max_files"]
                    ;
                }

                $field["type"] = "file";
                $field["description"] .= $description;
                $field["valid_types"] = "jpg, jpeg, jpe, png, gif";
                $field["current_files_selector"] =
                    "#current-files-{$field["name"]} tr"
                ;

                $form_fields[] = $field;

                if(!empty($values[$field["name"]]))
                {
                    $images = explode(",", $values[$field["name"]]);

                    $files_html = "<table "
                        . "id=\"current-files-{$field["name"]}\" "
                        . "class=\"file-uploaded-list\""
                        . ">"
                    ;

                    $files_html .= "<tbody>";

                    foreach($images as $image)
                    {
                        $onclick = "$(this).parent().parent().remove();";

                        $files_html .= "<tr>";

                        $image_expand = self::expand($image, $theme);

                        if($image_expand != $image)
                        {
                            $files_html .= "<td class=\"file\">"
                                . "<a target=\"_blank\" href=\""
                                . Uri::url($image_expand)
                                . "\">"
                                . "<img width=\"250px\" src=\""
                                . Uri::url($image_expand)
                                . "\" />"
                                . "</a>"
                                . "</td>"
                            ;
                        }
                        else
                        {
                            $files_html .= "<td class=\"file\">"
                                . "<a target=\"_blank\" href=\""
                                . Uri::url($files_path . $image)
                                . "\">"
                                . "<img width=\"250px\" src=\""
                                . Uri::url($files_path . $image)
                                . "\" />"
                                . "</a>"
                                . "</td>"
                            ;
                        }

                        /*if($field["description_field"])
                        {
                            $file_data = Pages\Images::getByName(
                                $image, $_REQUEST["uri"]
                            );

                            $files_html .= "<td class=\"file\">"
                                . '<input type="text" '
                                . 'placeholder="'.t('description').'" '
                                . 'name="'.$field["name"].'_desc[]" '
                                . 'value="'.$file_data["description"].'" '
                                . '/>'
                                . "</td>"
                            ;
                        }*/

                        $files_html .= "<td class=\"delete\">"
                            . '<input type="hidden" '
                            . 'name="'.$field["name"].'_current[]" '
                            . 'value="'.$image.'" '
                            . '/>'
                            . "<a onclick=\"$onclick\">"
                            . "x"
                            . "</a>"
                            . "</td>"
                        ;

                        $files_html .= "</tr>";
                    }

                    $files_html .= "</tbody>";
                    $files_html .= "</table>";

                    $label = t("Current images:");

                    if(count($images) > 1)
                    {
                        $label = t("Current images:");
                    }

                    $form_fields[] = array(
                        "type" => "other",
                        "html_code" => "<div class=\"current-image\">"
                            . "<strong>"
                            . $label
                            . "</strong> "
                            . $files_html
                            . "</div>"
                    );
                }
            }
            else
            {
                if(isset($_REQUEST[$field["name"]]))
                {
                    $values[$field["name"]] = $_REQUEST[$field["name"]];
                }
                // unserialize array
                elseif(preg_match("/^a\:[0-9]+\:/", $values[$field["name"]]))
                {
                    $values[$field["name"]] = unserialize(
                        $values[$field["name"]]
                    );
                }

                if($field["type"] == "select")
                {
                    $field["selected"] = $values[$field["name"]];
                }
                elseif($field["type"] == "checkbox" || $field["type"] == "radio")
                {
                    $field["checked"] = $values[$field["name"]];
                }
                elseif($field["type"] == "gmap-location")
                {
                    $lat = Util::stripHTMLTags($_REQUEST[$field["lat_name"]]);
                    $lng = Util::stripHTMLTags($_REQUEST[$field["lng_name"]]);

                    if(isset($_REQUEST[$field["lat_name"]]))
                    {
                        $values[$field["lat_name"]] = $_REQUEST[$field["lat_name"]];
                    }

                    if(isset($_REQUEST[$field["lng_name"]]))
                    {
                        $values[$field["lng_name"]] = $_REQUEST[$field["lng_name"]];
                    }

                    $field["lat"] = $values[$field["lat_name"]];
                    $field["lng"] = $values[$field["lng_name"]];
                }
                else
                {
                    $field["value"] = $values[$field["name"]];
                }

                $form_fields[] = $field;
            }
        }

        $fieldset["fields"] = $form_fields;
    }

    $parameters["name"] = "theme-settings";
    $parameters["class"] = "theme-settings";
    $parameters["action"] = Uri::url(Uri::get());
    $parameters["method"] = "post";

    if(isset($_REQUEST["path"]))
    {
        $fields_submit[] = array(
            "type" => "hidden",
            "name" => "path",
            "value" => $_REQUEST["path"]
        );
    }

    $fields_submit[] = array(
        "type" => "submit",
        "name" => "btnSave",
        "value" => t("Save")
    );

    $fields_submit[] = array(
        "type" => "submit",
        "name" => "btnReset",
        "value" => t("Reset")
    );

    $fields_submit[] = array(
        "type" => "submit",
        "name" => "btnCancel",
        "value" => t("Cancel")
    );

    $fieldsets[] = array("fields" => $fields_submit);

    return Forms::generate($parameters, $fieldsets);
}

/**
 * Check if file uploads for a given content type are of allowed extensions.
 *
 * @param string $theme
 *
 * @return bool
 */
private static function validUploads(string $theme): bool
{
    $pass = true;

    $fieldsets = self::getFieldsSet($theme);

    foreach($fieldsets as $fieldset)
    {
        foreach($fieldset["fields"] as $id => $field_data)
        {
            if($field_data["type"] == "file")
            {
                $files = array();

                if($field_data["multiple"])
                {
                    $files = $_FILES[$field_data["name"]];
                }
                else
                {
                    $files = array(
                        "name" => array($_FILES[$field_data["name"]]["name"]),
                        "type" => array($_FILES[$field_data["name"]]["type"]),
                        "tmp_name" => array($_FILES[$field_data["name"]]["tmp_name"])
                    );
                }

                $count_files = is_array($files["name"]) ?
                    count($files["name"])
                    :
                    0
                ;

                $cleaned_files = array(
                    "name" => array(),
                    "type" => array(),
                    "tmp_name" => array()
                );

                for($file_position=0; $file_position<$count_files; $file_position++)
                {
                    if(
                        $files["tmp_name"][$file_position] == ""
                        ||
                        !file_exists($files["tmp_name"][$file_position])
                    )
                    {
                        $count_files--;
                    }
                    else
                    {
                        $cleaned_files["name"][] = $files["name"][$file_position];
                        $cleaned_files["type"][] = $files["type"][$file_position];
                        $cleaned_files["tmp_name"][] = $files["tmp_name"][$file_position];
                    }
                }

                $files = $cleaned_files;

                // Check current files
                if(isset($_REQUEST[$field_data["name"]."_current"]))
                {
                    if(is_array($_REQUEST[$field_data["name"]."_current"]))
                    {
                        $count_files += count(
                            $_REQUEST[$field_data["name"]."_current"]
                        );
                    }
                }

                if($field_data["multiple"] && $field_data["max_files"] > 0)
                {
                    if($count_files > $field_data["max_files"])
                    {
                        View::addMessage(
                            sprintf(
                                t("The amount of uploaded files for %s exceeded the maximum allowed of %d."),
                                t($field_data["name"]),
                                $field_data["max_files"]
                            ),
                            "error"
                        );

                        $pass = false;

                        continue;
                    }
                }

                for($file_index=0; $file_index<$count_files; $file_index++)
                {
                    //Skip files not uploaded and not required
                    if(
                        trim($files["name"][$file_index]) == "" &&
                        !$field_data["required"]
                    )
                        continue;

                    //Check file size didnt exceeded the maximum allowed
                    if($field_data["max_size"] > 0)
                    {
                        if((filesize($files["tmp_name"][$file_index]) / 1024) > (intval($field_data["max_size"]) + 1))
                        {
                            View::addMessage(
                                t("File size exceeded by") . " " .
                                t($field_data["name"]) . ". " .
                                t("Maximum size permitted is:") . " " .
                                intval($field_data["max_size"]) . "K",
                                "error"
                            );

                            $pass = false;

                            break;
                        }
                    }

                    $file_name = $files["name"][$file_index];
                    $file_name_parts = explode(".", $file_name);
                    $file_extension = trim($file_name_parts[count($file_name_parts) - 1]);

                    $extensions = explode(",", $field_data["valid_types"]);

                    $valid_extension = false;

                    foreach($extensions as $extension)
                    {
                        if(trim($extension) == $file_extension)
                        {
                            $valid_extension = true;
                            break;
                        }
                    }

                    if(!$valid_extension)
                    {
                        View::addMessage(
                            t("Incorrect file type uploaded for") . " " .
                            t($field_data["name"]) . ". " .
                            t("Supported file formats are:") . " " .
                            $field_data["valid_types"],
                            "error"
                        );

                        $pass = false;
                        break;
                    }
                }
            }
            elseif($field_data["type"] == "image")
            {
                $files = array();

                if($field_data["multiple"])
                {
                    $files = $_FILES[$field_data["name"]];
                }
                else
                {
                    $files = array(
                        "name" => array($_FILES[$field_data["name"]]["name"]),
                        "type" => array($_FILES[$field_data["name"]]["type"]),
                        "tmp_name" => array($_FILES[$field_data["name"]]["tmp_name"])
                    );
                }

                $count_files = count($files["name"]);

                $cleaned_files = array(
                    "name" => array(),
                    "type" => array(),
                    "tmp_name" => array()
                );

                for($file_position=0; $file_position<$count_files; $file_position++)
                {
                    if(
                        $files["tmp_name"][$file_position] == "" ||
                        !file_exists($files["tmp_name"][$file_position])
                    )
                    {
                        $count_files--;
                    }
                    else
                    {
                        $cleaned_files["name"][] = $files["name"][$file_position];
                        $cleaned_files["type"][] = $files["type"][$file_position];
                        $cleaned_files["tmp_name"][] = $files["tmp_name"][$file_position];
                    }
                }

                // Check current files
                if(isset($_REQUEST[$field_data["name"]."_current"]))
                {
                    if(is_array($_REQUEST[$field_data["name"]."_current"]))
                    {
                        $count_files += count(
                            $_REQUEST[$field_data["name"]."_current"]
                        );
                    }
                }

                if($field_data["multiple"] && $field_data["max_files"] > 0)
                {
                    if($count_files > $field_data["max_files"])
                    {
                        View::addMessage(
                            sprintf(
                                t("The amount of uploaded images for %s exceeded the maximum allowed of %d."),
                                t($field_data["name"]),
                                $field_data["max_files"]
                            ),
                            "error"
                        );

                        $pass = false;

                        continue;
                    }
                }

                for($file_index=0; $file_index<$count_files; $file_index++)
                {
                    //Skip images not uploaded and not required
                    if(
                        trim($files["name"][$file_index]) == "" &&
                        !$field_data["required"]
                    )
                        continue;

                    $image_info = getimagesize(
                        $files["tmp_name"][$file_index]
                    );

                    switch($image_info["mime"])
                    {
                        case "image/jpeg":
                            break;
                        case "image/png":
                            break;
                        case "image/gif":
                            break;
                        default:
                            View::addMessage(
                                t("Incorrect image type uploaded for") . " " .
                                t($field_data["name"]) . ". " .
                                t("Supported image formats are: jpeg, png and gif"),
                                "error"
                            );

                            $pass = false;
                    }

                    if(!$pass)
                        break;

                    //Resize image if needed
                    if($field_data["width"] > 0)
                    {
                        if($image_info[0] > $field_data["width"])
                        {
                            $image = Images::get(
                                $files["tmp_name"][$file_index],
                                $field_data["width"]
                            );

                            $image_quality = Settings::get(
                                "image_compression_quality",
                                "main"
                            );

                            switch($image_info["mime"])
                            {
                                case "image/jpeg":
                                    imagejpeg(
                                        $image["binary_data"],
                                        $files["tmp_name"][$file_index],
                                        $image_quality ?
                                            intval($image_quality) : 100
                                    );
                                    break;
                                case "image/png":
                                    imagepng(
                                        $image["binary_data"],
                                        $files["tmp_name"][$file_index]
                                    );
                                    break;
                                case "image/gif":
                                    imagegif(
                                        $image["binary_data"],
                                        $files["tmp_name"][$file_index]
                                    );
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    return $pass;
}

/**
 * Save files and images uploaded for the theme as the
 * values on the form fields.
 *
 * @param string $theme The theme settings form to process.
 *
 * @return bool True on success otherwise false.
 */
private static function processRequest(string $theme): bool
{
    $fieldsets = self::getFieldsSet($theme);

    if(!$fieldsets)
    {
        return false;
    }

    $settings = self::settings($theme);

    foreach($fieldsets as $fieldset_position => $fieldset)
    {
        foreach($fieldset["fields"] as $id => $field_data)
        {
            if($field_data["type"] == "file")
            {
                $files = array();

                if($field_data["multiple"])
                {
                    $files = $_FILES[$field_data["name"]];
                }
                else
                {
                    $files = array(
                        "name" => array($_FILES[$field_data["name"]]["name"]),
                        "type" => array($_FILES[$field_data["name"]]["type"]),
                        "tmp_name" => array($_FILES[$field_data["name"]]["tmp_name"])
                    );
                }

                $current_files_count = 0;
                $current_files_list = array();
                $count_files = is_array($files["name"]) ?
                    count($files["name"])
                    :
                    0
                ;
                $added_files = array();

                // Check current files
                if(isset($_REQUEST[$field_data["name"]."_current"]))
                {
                    if(is_array($_REQUEST[$field_data["name"]."_current"]))
                    {
                        $current_files_list = $_REQUEST[$field_data["name"]."_current"];

                        $current_files_count += count(
                            $_REQUEST[$field_data["name"]."_current"]
                        );

                        $count_files += $current_files_count;
                    }
                }

                $current_files = array_map(
                    "trim",
                    explode(
                        ",",
                        $settings[$field_data["name"]]
                    )
                );

                $current_files_list = array_map(
                    "trim",
                    $current_files_list
                );

                $files_list = array_map(
                    "trim",
                    explode(
                        ",",
                        $settings[$field_data["name"]]
                    )
                );

                //Delete previous files
                if(count($files_list) > 0)
                {
                    foreach($files_list as $file_id => $file_name)
                    {
                        if(
                            !in_array($file_name, $current_files_list)
                        )
                        {
                            Files::delete($file_name, "themes/$theme");
                            unset($files_list[$id]);
                        }
                    }
                }

                // Add new files
                for($file_index=0; $file_index<$count_files; $file_index++)
                {
                    //Skip files not uploaded and not required
                    if(
                        trim($files["name"][$file_index]) == "" &&
                        !$field_data["required"]
                    )
                        continue;

                    $file_add = array(
                        "name" => $files["name"][$file_index],
                        "type" => $files["type"][$file_index],
                        "tmp_name" => $files["tmp_name"][$file_index]
                    );

                    $file_name = Files::addUpload($file_add, "themes/$theme");

                    if($file_name)
                        $added_files[] = $file_name;
                }

                if($field_data["multiple"])
                {
                    $added_files = array_merge($added_files, $current_files_list);
                }
                else
                {
                    //Delete previous file if not multiple
                    if(
                        count($added_files) > 0 &&
                        count($current_files) > 0 &&
                        $files_list
                    )
                    {
                        foreach($files_list as $file_id => $file_name)
                        {
                            if(
                                in_array($file_name, $current_files)
                            )
                            {
                                Files::delete($file_name, "themes/$theme");
                            }
                        }
                    }
                    elseif(count($added_files) <= 0)
                    {
                        $added_files = array_merge($added_files, $current_files_list);
                    }
                }

                $settings[$field_data["name"]] = implode(
                    ",",
                    $added_files
                );
            }
            elseif($field_data["type"] == "image")
            {
                $images = array();

                if($field_data["multiple"])
                {
                    $images = $_FILES[$field_data["name"]];
                }
                else
                {
                    $images = array(
                        "name" => array($_FILES[$field_data["name"]]["name"]),
                        "type" => array($_FILES[$field_data["name"]]["type"]),
                        "tmp_name" => array($_FILES[$field_data["name"]]["tmp_name"])
                    );
                }

                $current_images_count = 0;
                $current_images_list = array();
                $count_images = count($images["name"]);
                $added_images = array();

                // Check current files
                if(isset($_REQUEST[$field_data["name"]."_current"]))
                {
                    if(is_array($_REQUEST[$field_data["name"]."_current"]))
                    {
                        $current_images_list = $_REQUEST[$field_data["name"]."_current"];

                        $current_images_count += count(
                            $_REQUEST[$field_data["name"]."_current"]
                        );

                        $count_images += $current_images_count;
                    }
                }

                $current_images = array_map(
                    "trim",
                    explode(
                        ",",
                        $settings[$field_data["name"]]
                    )
                );

                $current_images_list = array_map(
                    "trim",
                    $current_images_list
                );

                $images_list = array_map(
                    "trim",
                    explode(
                        ",",
                        $settings[$field_data["name"]]
                    )
                );

                //Delete previous image
                if(count($images_list) > 0)
                {
                    foreach($images_list as $image_id => $image_name)
                    {
                        if(
                            !in_array($image_name, $current_images_list)
                        )
                        {
                            Files::delete($image_name, "themes/$theme");
                            unset($images_list[$image_id]);
                        }
                    }
                }

                // Add new images
                for($file_index=0; $file_index<$count_images; $file_index++)
                {
                    //Skip images not uploaded and not required
                    if(
                        trim($images["name"][$file_index]) == "" &&
                        !$field_data["required"]
                    )
                        continue;

                    //Store image
                    $file_add = array(
                        "name" => $images["name"][$file_index],
                        "type" => $images["type"][$file_index],
                        "tmp_name" => $images["tmp_name"][$file_index]
                    );

                    $file_name = Files::addUpload($file_add, "themes/$theme");

                    if($file_name)
                        $added_images[] = $file_name;
                }

                if($field_data["multiple"])
                {
                    $added_images = array_merge($added_images, $current_images_list);
                }
                else
                {
                    //Delete previous image if not multiple
                    if(
                        count($added_images) > 0 &&
                        count($current_images) > 0 &&
                        is_array($images_list)
                    )
                    {
                        foreach($images_list as $image_id => $image_name)
                        {
                            if(
                                in_array($image_name, $current_images)
                            )
                            {
                                Files::delete($image_name, "themes/$theme");
                            }
                        }
                    }
                    elseif(count($added_images) <= 0)
                    {
                        $added_images = array_merge($added_images, $current_images_list);
                    }
                }

                $settings[$field_data["name"]] = implode(",", $added_images);
            }
            else
            {
                if(is_array($_REQUEST[$field_data["name"]]))
                {
                    $_REQUEST[$field_data["name"]] = serialize($_REQUEST[$field_data["name"]]);
                }

                if($field["type"] == "gmap-location")
                {
                    $settings[$field_data["lat_name"]] =
                        $_REQUEST[$field_data["lat_name"]]
                    ;

                    $settings[$field_data["lng_name"]] =
                        $_REQUEST[$field_data["lng_name"]]
                    ;
                }
                else
                {
                    $settings[$field_data["name"]] =
                        $_REQUEST[$field_data["name"]]
                    ;
                }
            }
        }
    }

    return self::saveSettings($settings, $theme);
}

/**
 * Get the relative path to the directory that holds a theme files and
 * configuration.
 *
 * @param string $theme
 *
 * @return string
 */
private static function getFilesPath(string $theme): string
{
    $files_path = Files::getDir("themes/$theme");

    return $files_path;
}

}