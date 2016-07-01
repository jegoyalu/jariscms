<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * The functions to manage contact form fields.
 */

/**
 * Adds a new custom field to a contact form.
 *
 * @param $fields An array with the values of the field.
 * @param $uri The path of the contact form page.
 *
 * @return True on success or false on failure.
 */
function contact_add_field($field, $uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = Jaris\Pages::getPath($uri) . "/contact-fields.php";

    foreach($field as $name=>$value)
    {
        if(is_array($value))
        {
            $field[$name] = serialize($value);
        }
    }

    return Jaris\Data::add($field, $path);
}

/**
 * Edits a field of a contact form page.
 *
 * @param $id The id of the field.
 * @param $field An array with the new values of the field.
 * @param $uri The contact form page path.
 *
 * @return True on success or false on failure.
 */
function contact_edit_field($id, $field, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $path = contact_generate_fields_path($uri);

    if(!$path)
    {
        return false;
    }

    return Jaris\Data::edit($id, $field, $path);
}

/**
 * Deletes a field from a contact form.
 *
 * @param $id The id of the field.
 * @param $uri The contact forms page path.
 *
 * @return True on success or false on failure.
 */
function contact_delete_field($id, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $path = contact_generate_fields_path($uri);

    if(!$path)
    {
        return false;
    }

    return Jaris\Data::delete($id, $path);
}

/**
 * Retreive the corrosponding data of a field.
 *
 * @param $id The id of the field.
 * @param $uri The contact forms page path.
 *
 * @return True on success or false on failure.
 */
function contact_get_field_data($id, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $fields = contact_get_fields($uri);

    if(!$fields)
    {
        return false;
    }

    return $fields[$id];
}

/**
 * Gets a list of all the fields available for a contact form page.
 *
 * @param $uri The contact form page path.
 *
 * @return True on success or false on failure.
 */
function contact_get_fields($uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = contact_generate_fields_path($uri);

    if(!$path)
    {
        return false;
    }

    $fields = Jaris\Data::parse($path);

    $fields = Jaris\Data::sort($fields, "position");

    return $fields;
}

/**
 * Used to append fields to a contact form page.
 *
 * @param $uri The contact form page path.
 * @param $current_fields a reference to the variable
 * that holds default data to append custom fields.
 */
function contact_append_fields($uri, &$current_fields)
{
    $fields = contact_get_fields($uri);

    if($fields)
    {
        foreach($fields as $id => $field)
        {
            //Skip file uploads since they are handled seperately
            if($field["type"] == "file")
                continue;

            $value = "";

            //Concatenate values for multiple checkbox
            if(is_array($_REQUEST[$field["variable_name"]]))
            {
                foreach($_REQUEST[$field["variable_name"]] as $option)
                {
                    $value .= $option . ", ";
                }

                $value = rtrim($value, ",");
            }
            else
            {
                $value .= $_REQUEST[$field["variable_name"]];
            }

            if($field["strip_html"])
            {
                $value = Jaris\Util::stripHTMLTags($value);
            }

            if($field["limit"] > 0)
            {
                $value = substr($value, 0, $field["limit"]);
            }

            $current_fields[$field["variable_name"]] = $value;
        }
    }
}

/**
 * Check if file uploads are of allowed extensions and file size.
 *
 * @param string $type The uri of the contact form page.
 *
 * @return boolean
 */
function contact_files_upload_pass($uri)
{
    $fields = contact_get_fields($uri);

    $pass = true;

    foreach($fields as $id => $field_data)
    {
        if($field_data["type"] == "file")
        {
            //Skip files not uploaded and not required
            if(
                trim($_FILES[$field_data["variable_name"]]["name"]) == "" &&
                !$field_data["required"]
            )
            {
                continue;
            }

            //Check file size didnt exceeded the maximum allowed
            if($field_data["size"] > 0)
            {
                if(
                    (filesize($_FILES[$field_data["variable_name"]]["tmp_name"]) / 1024)
                    >
                    (intval($field_data["size"]) + 1)
                )
                {
                    Jaris\View::addMessage(
                        t("File size exceeded by") . " " .
                            t($field_data["name"]) . ". " .
                            t("Maximum size permitted is:") . " " .
                            intval($field_data["size"]) . "K",
                        "error"
                    );

                    $pass = false;

                    continue;
                }
            }

            $file_name = $_FILES[$field_data["variable_name"]]["name"];
            $file_name_parts = explode(".", $file_name);
            $file_extension = trim($file_name_parts[count($file_name_parts) - 1]);

            $valid_extension = false;

            if(trim($field_data["extensions"]) != "")
            {
                $extensions = explode(",", $field_data["extensions"]);

                foreach($extensions as $extension)
                {
                    if(trim($extension) == $file_extension)
                    {
                        $valid_extension = true;
                        break;
                    }
                }
            }
            else
            {
                $valid_extension = true;
            }

            if(!$valid_extension)
            {
                Jaris\View::addMessage(
                    t("Incorrect file type uploaded for") . " " .
                        t($field_data["name"]) . ". " .
                        t("Supported file formats are:") . " " .
                        $field_data["extensions"],
                    "error"
                );
                $pass = false;
            }
        }
    }

    return $pass;
}

function contact_get_file_attachments($uri)
{
    $fields = contact_get_fields($uri);

    $attachments = array();

    foreach($fields as $id => $field_data)
    {
        if($field_data["type"] == "file")
        {
            //Skip files not uploaded and not required
            if(
                trim($_FILES[$field_data["variable_name"]]["name"]) == "" &&
                !$field_data["required"]
            )
            {
                continue;
            }

            $attachments[$_FILES[$field_data["variable_name"]]["name"]] = $_FILES[$field_data["variable_name"]]["tmp_name"];
        }
    }

    return $attachments;
}

/**
 * Generates an array with the fields of a contact form for the Jaris\Forms::generate function..
 *
 * @param $uri The machine name of the $uri.
 * @param $values Array of the values in the format $values["variable_name"] = value.
 */
function contact_generate_form_fields($uri, $values = array())
{
    if(trim($uri) == "")
    {
        return false;
    }

    $fields = contact_get_fields($uri);

    if(!$fields)
    {
        return false;
    }

    $form_fields = array();

    foreach($fields as $id => $field)
    {
        if(
            $field["type"] == "text" ||
            $field["type"] == "password" ||
            $field["type"] == "textarea"
        )
        {
            if($field["limit"] > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "limit" => $field["limit"],
                    "value" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]]
                        :
                        (
                            $values[$field["variable_name"]] ?
                                $values[$field["variable_name"]]
                                :
                                $field["default"]
                        ),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "inline" => $field["inline"],
                    "required" => $field["required"],
                    "readonly" => $field["readonly"],
                    "description" => t($field["description"])
                );
            }
            else
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]]
                        :
                        (
                            $values[$field["variable_name"]] ?
                                $values[$field["variable_name"]]
                                :
                                $field["default"]
                        ),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "inline" => $field["inline"],
                    "required" => $field["required"],
                    "readonly" => $field["readonly"],
                    "description" => t($field["description"])
                );
            }
        }
        elseif($field["type"] == "color" || $field["type"] == "date")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "value" => $_REQUEST[$field["variable_name"]] ?
                    $_REQUEST[$field["variable_name"]]
                    :
                    (
                        $values[$field["variable_name"]] ?
                            $values[$field["variable_name"]]
                            :
                            $field["default"]
                    ),
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "inline" => $field["inline"],
                "required" => $field["required"],
                "readonly" => $field["readonly"],
                "description" => t($field["description"])
            );
        }
        elseif($field["type"] == "file")
        {
            $description = "";
            if(trim($field["description"]) != "")
            {
                //To add a space after user entered description
                $description .= " ";
            }

            $description .= t("Allowed file types:") . " ";

            if(trim($field["extensions"]) != "")
            {
                $description .= $field["extensions"];
            }
            else
            {
                //If no extension was entered by the user just display all
                $description .= t("all");
            }

            $description .= " ";
            $description .= t("Maximum allowed size is:") . " ";

            if($field["size"] > 0)
            {
                $description .= intval($field["size"]) . "K";
            }
            else
            {
                $description .= ini_get("upload_max_filesize");
            }

            $form_fields[] = array(
                "type" => $field["type"],
                "valid_types" => trim($field['extensions']),
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "inline" => $field["inline"],
                "required" => $field["required"],
                "readonly" => $field["readonly"],
                "description" => t($field["description"]) . $description
            );
        }
        elseif($field["type"] == "hidden")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "value" => $_REQUEST[$field["variable_name"]] ?
                    $_REQUEST[$field["variable_name"]]
                    :
                    (
                        $values[$field["variable_name"]] ?
                            $values[$field["variable_name"]]
                            :
                            $field["default"]
                    ),
                "name" => $field["variable_name"],
                "required" => $field["required"],
                "readonly" => $field["readonly"]
            );
        }
        elseif($field["type"] == "select")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);

            for($i = 0; $i < count($select_values); $i++)
            {
                $select[t(trim($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "selected" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]]
                        :
                        (
                            $values[$field["variable_name"]] ?
                                $values[$field["variable_name"]]
                                :
                                $field["default"]
                        ),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "inline" => $field["inline"],
                    "required" => $field["required"],
                    "readonly" => $field["readonly"],
                    "description" => t($field["description"])
                );
            }
        }
        elseif($field["type"] == "radio")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);


            for($i = 0; $i < count($select_values); $i++)
            {
                $select[t(trim($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "checked" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]]
                        :
                        (
                            $values[$field["variable_name"]] ?
                                $values[$field["variable_name"]]
                                :
                                $field["default"]
                        ),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "inline" => $field["inline"],
                    "required" => $field["required"],
                    "readonly" => $field["readonly"],
                    "description" => t($field["description"])
                );
            }
        }
        elseif($field["type"] == "checkbox")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);


            for($i = 0; $i < count($select_values); $i++)
            {
                $select[t(trim($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "checked" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]]
                        :
                        (
                            $values[$field["variable_name"]] ?
                                $values[$field["variable_name"]]
                                :
                                $field["default"]
                        ),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "inline" => $field["inline"],
                    "required" => $field["required"],
                    "readonly" => $field["readonly"],
                    "description" => t($field["description"])
                );
            }
        }
        elseif($field["type"] == "other")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "html_code" => Jaris\System::evalPHP($field["default"])
            );
        }
    }

    return $form_fields;
}

/**
 * Generates the path where a contact form fields are stored.
 *
 * @param $uri The path to the contact form page.
 *
 * @return True on success or false if no fields exist.
 */
function contact_generate_fields_path($uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = Jaris\Pages::getPath($uri) . "/contact-fields.php";

    if(!file_exists($path))
    {
        return false;
    }

    return $path;
}

?>