<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * The functions to manage content types extra fields.
 */
class Fields
{

/**
 * Adds a new custom field to a content type.
 *
 * @param array $fields An array with the values of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
static function add(array $fields, string $type): bool
{
    if($type == "")
    {
        return false;
    }

    $path = Site::dataDir() . "types/fields/$type.php";

    //Create directory of fields in case does'nt exist
    if(!file_exists(Site::dataDir() . "types/fields"))
    {
        FileSystem::makeDir(Site::dataDir() . "types/fields", 0755, true);
    }

    $fields["groups"] = serialize($fields["groups"]);

    return Data::add($fields, $path);
}

/**
 * Edits a custom field of a content type.
 *
 * @param int $id The id of the field.
 * @param array $fields An array with the new values of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
static function edit(int $id, array $fields, string $type): bool
{
    if($id == "" && $type == "")
    {
        return false;
    }

    $path = self::getPath($type);

    if(!$path)
    {
        return false;
    }

    $fields["groups"] = serialize($fields["groups"]);

    return Data::edit($id, $fields, $path);
}

/**
 * Deletes a custom field from a content type.
 *
 * @param int $id The id of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
static function delete(int $id, string $type): bool
{
    if($id == "" && $type == "")
    {
        return false;
    }

    $path = self::getPath($type);

    if(!$path)
    {
        return false;
    }

    return Data::delete($id, $path);
}

/**
 * Retreive the corrosponding data of a field.
 *
 * @param int $id The id of the field.
 * @param string $type The machine name of the type.
 *
 * @return array Array with a field fields or empty array if not found.
 */
static function get(int $id, string $type): array
{
    if($id == "" && $type == "")
    {
        return array();
    }

    $fields = self::getList($type);

    if(!$fields || !isset($fields[$id]))
    {
        return array();
    }

    return $fields[$id];
}

/**
 * Gets a list of all the custom fields available for a content type.
 *
 * @param string $type The machine name of the type.
 *
 * @return array Array on success or empty array on failure.
 */
static function getList(string $type): array
{
    if($type == "")
    {
        return array();
    }

    $path = self::getPath($type);

    if(!$path)
    {
        return array();
    }

    $fields = Data::parse($path);

    $fields = Data::sort($fields, "position");

    foreach($fields as $id=>$field_data)
    {
        if(isset($fields[$id]["groups"]))
        {
            $fields[$id]["groups"] = unserialize($fields[$id]["groups"]);
        }
        else
        {
            $fields[$id]["groups"] = array();
        }
    }

    return $fields;
}

/**
 * Used to append extra custom fields when submiting content on a content type.
 *
 * @param string $type The machine name of the type.
 * @param ?array $current_fields A reference to the variable
 * that holds default data to append custom fields.
 */
static function appendFields(string $type, ?array &$current_fields): void
{
    $fields = self::getList($type);

    if($fields)
    {
        foreach($fields as $id => $field)
        {
            // Skip fields not assigned to current user group.
            if(
                count($field["groups"]) > 0 &&
                !in_array(Authentication::currentUserGroup(), $field["groups"])
            )
            {
                continue;
            }

            //Skip file uploads since they are handled seperately
            if($field["type"] == "file" || $field["type"] == "image")
            {
                continue;
            }
            elseif($field["type"] == "gmap-location")
            {
                $lat = Util::stripHTMLTags($_REQUEST[$field["lat_name"]]);
                $lng = Util::stripHTMLTags($_REQUEST[$field["lng_name"]]);

                $current_fields[$field["lat_name"]] = $lat;
                $current_fields[$field["lng_name"]] = $lng;
            }
            else
            {
                $value = $_REQUEST[$field["variable_name"]];

                if($field["strip_html"])
                {
                    $value = Util::stripHTMLTags($value);
                }

                if($field["limit"] > 0)
                {
                    $value = substr($value, 0, $field["limit"]);
                }

                $current_fields[$field["variable_name"]] = $value;
            }
        }
    }
}

/**
 * Check if file uploads for a given content type are of allowed extensions.
 *
 * @param string $type The machine name of the type.
 *
 * @return bool
 */
static function validUploads(string $type): bool
{
    $fields = self::getList($type);

    $pass = true;

    if(!$fields)
    {
        return $pass;
    }

    foreach($fields as $id => $field_data)
    {
        // Skip fields not assigned to current user group.
        if(
            count($field_data["groups"]) > 0 &&
            !in_array(Authentication::currentUserGroup(), $field_data["groups"])
        )
        {
            continue;
        }

        if($field_data["type"] == "file")
        {
            $files = array();

            if($field_data["file_multiple"])
            {
                $files = $_FILES[$field_data["variable_name"]];
            }
            else
            {
                $files = array(
                    "name" => array($_FILES[$field_data["variable_name"]]["name"]),
                    "type" => array($_FILES[$field_data["variable_name"]]["type"]),
                    "tmp_name" => array($_FILES[$field_data["variable_name"]]["tmp_name"])
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

            $files = $cleaned_files;

            // Check current files
            if(isset($_REQUEST[$field_data["variable_name"]."_current"]))
            {
                if(is_array($_REQUEST[$field_data["variable_name"]."_current"]))
                {
                    $count_files += count(
                        $_REQUEST[$field_data["variable_name"]."_current"]
                    );
                }
            }

            if($field_data["file_multiple"] && $field_data["file_max"] > 0)
            {
                if($count_files > $field_data["file_max"])
                {
                    View::addMessage(
                        sprintf(
                            t("The amount of uploaded files for %s exceeded the maximum allowed of %d."),
                            t($field_data["name"]),
                            $field_data["file_max"]
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
                if($field_data["size"] > 0)
                {
                    if((filesize($files["tmp_name"][$file_index]) / 1024) > (intval($field_data["size"]) + 1))
                    {
                        View::addMessage(
                            t("File size exceeded by") . " " .
                            t($field_data["name"]) . ". " .
                            t("Maximum size permitted is:") . " " .
                            intval($field_data["size"]) . "K",
                            "error"
                        );

                        $pass = false;

                        break;
                    }
                }

                $file_name = $files["name"][$file_index];
                $file_name_parts = explode(".", $file_name);
                $file_extension = trim($file_name_parts[count($file_name_parts) - 1]);

                $extensions = explode(",", $field_data["extensions"]);

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
                        $field_data["extensions"],
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

            if($field_data["image_multiple"])
            {
                $files = $_FILES[$field_data["variable_name"]];
            }
            else
            {
                $files = array(
                    "name" => array($_FILES[$field_data["variable_name"]]["name"]),
                    "type" => array($_FILES[$field_data["variable_name"]]["type"]),
                    "tmp_name" => array($_FILES[$field_data["variable_name"]]["tmp_name"])
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
            if(isset($_REQUEST[$field_data["variable_name"]."_current"]))
            {
                if(is_array($_REQUEST[$field_data["variable_name"]."_current"]))
                {
                    $count_files += count(
                        $_REQUEST[$field_data["variable_name"]."_current"]
                    );
                }
            }

            if($field_data["image_multiple"] && $field_data["image_max"] > 0)
            {
                if($count_files > $field_data["image_max"])
                {
                    View::addMessage(
                        sprintf(
                            t("The amount of uploaded images for %s exceeded the maximum allowed of %d."),
                            t($field_data["name"]),
                            $field_data["image_max"]
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

    return $pass;
}

/**
 * Save files and images uploaded to a page with custom fields for files and
 * images.
 *
 * @param string $type Content type of the page to work on,
 * @param string $page Uri of the page where file uploads are going to be saved.
 */
static function saveUploads(string $type, string $page): void
{
    $fields = self::getList($type);

    if(!$fields)
    {
        return;
    }

    $page_data = Pages::get($page);

    $files_list = Pages\Files::getList($page);
    $images_list = Pages\Images::getList($page);

    foreach($fields as $id => $field_data)
    {
        // Skip fields not assigned to current user group.
        if(
            count($field_data["groups"]) > 0 &&
            !in_array(Authentication::currentUserGroup(), $field_data["groups"])
        )
        {
            continue;
        }

        if($field_data["type"] == "file")
        {
            $files = array();

            if($field_data["file_multiple"])
            {
                $files = $_FILES[$field_data["variable_name"]];
            }
            else
            {
                $files = array(
                    "name" => array($_FILES[$field_data["variable_name"]]["name"]),
                    "type" => array($_FILES[$field_data["variable_name"]]["type"]),
                    "tmp_name" => array($_FILES[$field_data["variable_name"]]["tmp_name"])
                );
            }

            $current_files_count = 0;
            $current_files_list = array();
            $count_files = count($files["name"]);
            $added_files = array();

            // Check current files
            if(isset($_REQUEST[$field_data["variable_name"]."_current"]))
            {
                if(is_array($_REQUEST[$field_data["variable_name"]."_current"]))
                {
                    $current_files_list = $_REQUEST[$field_data["variable_name"]."_current"];

                    $current_files_count += count(
                        $_REQUEST[$field_data["variable_name"]."_current"]
                    );

                    $count_files += $current_files_count;

                    if($field_data["file_description"])
                    {
                        foreach(
                            $_REQUEST[$field_data["variable_name"]."_current"]
                            as
                            $file_index=>$file_name
                        )
                        {
                            $file_data = Pages\Files::getByName(
                                $file_name, $page
                            );

                            $file_data["description"] = $_REQUEST[$field_data["variable_name"]."_desc"][$file_index];

                            Pages\Files::editByName(
                                $file_name,
                                $file_data,
                                $page
                            );
                        }
                    }
                }
            }

            $current_files = array_map(
                "trim",
                explode(
                    ",",
                    $page_data[$field_data["variable_name"]]
                )
            );

            $current_files_list = array_map(
                "trim",
                $current_files_list
            );

            //Delete previous files
            if(trim($page_data[$field_data["variable_name"]]) != "")
            {
                if($files_list)
                {
                    foreach($files_list as $file_id => $file_data)
                    {
                        if(
                            in_array($file_data["name"], $current_files) &&
                            !in_array($file_data["name"], $current_files_list)
                        )
                        {
                            Pages\Files::delete($file_id, $page);
                            unset($files_list[$id]);
                        }
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


                $file_name = "";

                $file_add = array(
                    "name" => $files["name"][$file_index],
                    "type" => $files["type"][$file_index],
                    "tmp_name" => $files["tmp_name"][$file_index]
                );

                $description = "";
                if($field_data["file_description"])
                {
                    $description .= $_REQUEST[$field_data["variable_name"]]
                        ['descriptions']
                        [$file_index]
                    ;
                }

                Pages\Files::add($file_add, $description, $page, $file_name);

                $added_files[] = $file_name;
            }

            if($field_data["file_multiple"])
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
                    foreach($files_list as $file_id => $file_data)
                    {
                        if(
                            in_array($file_data["name"], $current_files)
                        )
                        {
                            Pages\Files::delete($file_id, $page);
                            unset($files_list[$id]);
                        }
                    }
                }
                elseif(count($added_files) <= 0)
                {
                    $added_files = array_merge($added_files, $current_files_list);
                }
            }

            $page_data[$field_data["variable_name"]] = implode(",", $added_files);
        }
        elseif($field_data["type"] == "image")
        {
            $images = array();

            if($field_data["image_multiple"])
            {
                $images = $_FILES[$field_data["variable_name"]];
            }
            else
            {
                $images = array(
                    "name" => array($_FILES[$field_data["variable_name"]]["name"]),
                    "type" => array($_FILES[$field_data["variable_name"]]["type"]),
                    "tmp_name" => array($_FILES[$field_data["variable_name"]]["tmp_name"])
                );
            }

            $current_images_count = 0;
            $current_images_list = array();
            $count_images = count($images["name"]);
            $added_images = array();

            // Check current files
            if(isset($_REQUEST[$field_data["variable_name"]."_current"]))
            {
                if(is_array($_REQUEST[$field_data["variable_name"]."_current"]))
                {
                    $current_images_list = $_REQUEST[$field_data["variable_name"]."_current"];

                    $current_images_count += count(
                        $_REQUEST[$field_data["variable_name"]."_current"]
                    );

                    $count_images += $current_images_count;

                    if($field_data["image_description"])
                    {
                        foreach(
                            $_REQUEST[$field_data["variable_name"]."_current"]
                            as
                            $file_index=>$file_name
                        )
                        {
                            $file_data = Pages\Images::getByName(
                                $file_name, $page
                            );

                            $file_data["description"] = $_REQUEST[$field_data["variable_name"]."_desc"][$file_index];

                            Pages\Images::editByName(
                                $file_name,
                                $file_data,
                                $page
                            );
                        }
                    }
                }
            }

            $current_images = array_map(
                "trim",
                explode(
                    ",",
                    $page_data[$field_data["variable_name"]]
                )
            );

            $current_images_list = array_map(
                "trim",
                $current_images_list
            );

            //Delete previous image
            if(
                trim($page_data[$field_data["variable_name"]]) != ""
            )
            {
                foreach($images_list as $image_id => $image_data)
                {
                    if(
                        in_array($image_data["name"], $current_images) &&
                        !in_array($image_data["name"], $current_images_list)
                    )
                    {
                        Pages\Images::delete($image_id, $page);
                        unset($images_list[$id]);
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
                $file_name = "";

                $file_add = array(
                    "name" => $images["name"][$file_index],
                    "type" => $images["type"][$file_index],
                    "tmp_name" => $images["tmp_name"][$file_index]
                );

                $description = "";
                if($field_data["image_description"])
                {
                    $description .= $_REQUEST[$field_data["variable_name"]]
                        ['descriptions']
                        [$file_index]
                    ;
                }

                Pages\Images::add(
                    $file_add,
                    $description,
                    $page,
                    $file_name
                );

                $added_images[] = $file_name;
            }

            if($field_data["image_multiple"])
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
                    foreach($images_list as $image_id => $image_data)
                    {
                        if(
                            in_array($image_data["name"], $current_images)
                        )
                        {
                            Pages\Images::delete($image_id, $page);
                            unset($images_list[$id]);
                        }
                    }
                }
                elseif(count($added_images) <= 0)
                {
                    $added_images = array_merge($added_images, $current_images_list);
                }
            }

            $page_data[$field_data["variable_name"]] = implode(",", $added_images);
        }
    }

    Pages::edit($page, $page_data);
}

/**
 * Generates an array with the custom fields of a type for the
 * generate_form function.
 *
 * @param string $type The machine name of the type.
 * @param array $values Array of the values in the format
 * $values["variable_name"] = value.
 *
 * @return array Array of custom fields for the given type or empty array.
 */
static function generateFields(string $type, array $values = []): array
{
    if($type == "")
    {
        return array();
    }

    $fields = self::getList($type);

    if(!$fields)
    {
        return array();
    }

    $form_fields = array();

    foreach($fields as $id => $field)
    {
        // Skip fields not assigned to current user group.
        if(
            count($field["groups"]) > 0 &&
            !in_array(Authentication::currentUserGroup(), $field["groups"])
        )
        {
            continue;
        }

        if(
            $field["type"] == "text" ||
            $field["type"] == "password" ||
            $field["type"] == "textarea" ||
            $field["type"] == "uri" ||
            $field["type"] == "uriarea"
        )
        {
            if($field["limit"] > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "limit" => $field["limit"],
                    "value" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                            $values[$field["variable_name"]] : $field["default"]),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
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
                        $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                            $values[$field["variable_name"]] : $field["default"]),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
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
                    $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                        $values[$field["variable_name"]] : $field["default"]),
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "required" => $field["required"],
                "readonly" => $field["readonly"],
                "description" => t($field["description"])
            );
        }
        elseif($field["type"] == "gmap-location")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "zoom" => $field["map_zoom"],
                "lat_name" => $field["lat_name"],
                "lng_name" => $field["lng_name"],
                "lat" => $_REQUEST[$field["lat_name"]] ?
                    $_REQUEST[$field["lat_name"]]
                    :
                    (
                        $values[$field["lat_name"]] ?
                            $values[$field["lat_name"]]
                            :
                            $field["lat_value"]
                    ),
                "lng" => $_REQUEST[$field["lng_name"]] ?
                    $_REQUEST[$field["lng_name"]]
                    :
                    (
                        $values[$field["lng_name"]] ?
                            $values[$field["lng_name"]]
                            :
                            $field["lng_value"]
                    ),
                //"required" => $field["required"],
                //"readonly" => $field["readonly"],
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

            $description .= ". ";
            $description .= t("Maximum allowed size is:") . " ";

            if($field["size"] > 0)
            {
                $description .= intval($field["size"]) . "K";
            }
            else
            {
                $description .= ini_get("upload_max_filesize");
            }

            if($field["file_max"] > 0)
            {
                $description .= ". "
                    . t("Maximum amount of files allowed:")
                    . " " . $field["file_max"]
                ;
            }

            $form_fields[] = array(
                "type" => $field["type"],
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "required" => $field["required"],
                "readonly" => $field["readonly"],
                "multiple" => $field["file_multiple"],
                "max_files" => $field["file_max"],
                "max_size" => $field["size"] > 0 ? ((int) $field["size"]) : 0,
                "valid_types" => $field['extensions'],
                "current_files_selector" => "#current-files-{$field["variable_name"]} tr",
                "description" => t($field["description"]) . $description,
                "description_field" => $field["file_description"]
            );

            if($values[$field["variable_name"]] != "")
            {
                $files = explode(",", $values[$field["variable_name"]]);

                $files_html = "<table id=\"current-files-{$field["variable_name"]}\" class=\"file-uploaded-list\">";
                $files_html .= "<tbody>";

                foreach($files as $file)
                {
                    $onclick = "$(this).parent().parent().remove();";

                    $files_html .= "<tr>";

                    $files_html .= "<td class=\"file\">"
                        . "<a target=\"_blank\" href=\""
                        . Uri::url("file/{$_REQUEST["uri"]}/$file")
                        . "\">"
                        . $file
                        . "</a>"
                        . "</td>"
                    ;

                    if($field["file_description"])
                    {
                        $file_data = Pages\Files::getByName(
                            $file, $_REQUEST["uri"]
                        );

                        $files_html .= "<td class=\"file\">"
                            . '<input type="text" placeholder="'.t('description').'" name="'.$field["variable_name"].'_desc[]" value="'.$file_data["description"].'" />'
                            . "</td>"
                        ;
                    }

                    $files_html .= "<td class=\"delete\">"
                        . '<input type="hidden" name="'.$field["variable_name"].'_current[]" value="'.$file.'" />'
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
            $description .= ini_get("upload_max_filesize");

            if($field["image_max"] > 0)
            {
                $description .= ". "
                    . t("Maximum amount of images allowed:")
                    . " " . $field["image_max"]
                ;
            }

            $form_fields[] = array(
                "type" => "file",
                "name" => $field["variable_name"],
                "label" => t($field["name"]) . ":",
                "id" => $field["variable_name"],
                "required" => $field["required"],
                "readonly" => $field["readonly"],
                "multiple" => $field["image_multiple"],
                "max_files" => $field["image_max"],
                "valid_types" => "jpg, jpeg, png, gif",
                "current_files_selector" => "#current-files-{$field["variable_name"]} tr",
                "description" => t($field["description"]) . $description,
                "description_field" => $field["image_description"]
            );

            if($values[$field["variable_name"]] != "")
            {
                $images = explode(",", $values[$field["variable_name"]]);

                $files_html = "<table id=\"current-files-{$field["variable_name"]}\" class=\"file-uploaded-list\">";
                $files_html .= "<tbody>";

                foreach($images as $image)
                {
                    $onclick = "$(this).parent().parent().remove();";

                    $files_html .= "<tr>";

                    $files_html .= "<td class=\"file\">"
                        . "<a target=\"_blank\" href=\""
                        . Uri::url("image/{$_REQUEST["uri"]}/$image")
                        . "\">"
                        . "<img src=\""
                        . Uri::url("image/{$_REQUEST["uri"]}/$image", array("w" => 250))
                        . "\" />"
                        . "</a>"
                        . "</td>"
                    ;

                    if($field["image_description"])
                    {
                        $file_data = Pages\Images::getByName(
                            $image, $_REQUEST["uri"]
                        );

                        $files_html .= "<td class=\"file\">"
                            . '<input type="text" placeholder="'.t('description').'" name="'.$field["variable_name"].'_desc[]" value="'.$file_data["description"].'" />'
                            . "</td>"
                        ;
                    }

                    $files_html .= "<td class=\"delete\">"
                        . '<input type="hidden" name="'.$field["variable_name"].'_current[]" value="'.$image.'" />'
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
        elseif($field["type"] == "hidden")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "value" => $_REQUEST[$field["variable_name"]] ?
                    $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                        $values[$field["variable_name"]] : $field["default"]),
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
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "selected" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                            $values[$field["variable_name"]] : $field["default"]),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
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
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "checked" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                            $values[$field["variable_name"]] : $field["default"]),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
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
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array(
                    "type" => $field["type"],
                    "value" => $select,
                    "checked" => $_REQUEST[$field["variable_name"]] ?
                        $_REQUEST[$field["variable_name"]] : ($values[$field["variable_name"]] ?
                            $values[$field["variable_name"]] : $field["default"]),
                    "name" => $field["variable_name"],
                    "label" => t($field["name"]) . ":",
                    "id" => $field["variable_name"],
                    "required" => false,
                    "description" => t($field["description"])
                );
            }
        }
        elseif($field["type"] == "other")
        {
            $form_fields[] = array(
                "type" => $field["type"],
                "html_code" => System::evalPHP($field["default"])
            );
        }
    }

    return $form_fields;
}

/**
 * Generates the path where a content type fields are stored.
 *
 * @param string $type The machine name of the content type.
 *
 * @return string Path on success or empty string if no fields exist.
 */
static function getPath(string $type): string
{
    if($type == "")
    {
        return "";
    }

    $path = Site::dataDir() . "types/fields/$type.php";

    if(!file_exists($path))
    {
        return "";
    }

    return $path;
}

}