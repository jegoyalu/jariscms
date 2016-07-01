<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * The functions to manage content types.
 */
class Types
{

/**
 * Receives parameters: $machine_name, $fields
 * @var string
 */
const SIGNAL_ADD_TYPE = "hook_add_type";

/**
 * Receives parameters: $machine_name, $fields
 * @var string
 */
const SIGNAL_EDIT_TYPE = "hook_edit_type";

/**
 * Adds a new content type.
 *
 * @param string $name The machine readable name of the type.
 * @param array $fields An array with the needed fields to write to the type.
 *
 * @return string "true" string on success error message on fail.
 * @original add_type
 */
static function add($name, $fields)
{
    $type_data_path = self::getPath($name);

    //Create page type directory in case is not present
    $path = str_replace("$name.php", "", $type_data_path);
    if(!file_exists($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    //Check if type already exist.
    if(file_exists($type_data_path))
    {
        return System::errorMessage("type_exist");
    }

    //Call add_type hook before creating the category
    Modules::hook("hook_add_type", $name, $fields);

    if($fields["image"])
    {
        $page_type_images_data = Pages::get("admin/types/image");

        if(!$page_type_images_data)
        {
            $uri = "";
            Pages::add(
                "admin/types/image",
                array(
                    "title" => "<?php print t('Type Images') ?>",
                    "content" => "<?php protected_page(); ?>",
                    "is_system" => 1
                ),
                $uri
            );
        }

        $image_name = "";

        Pages\Images::add($fields["image"], "", "admin/types/image", $image_name);

        $fields["image"] = $image_name;
    }

    $fields["categories"] = serialize($fields["categories"]);
    $fields["uploads"] = serialize($fields["uploads"]);
    $fields["posts"] = serialize($fields["posts"]);

    if(!Data::add($fields, $type_data_path))
    {
        return System::errorMessage("write_error_data");
    }

    return "true";
}

/**
 * Deletes an existing content type.
 *
 * @param string $name Machine name of the type.
 *
 * @return string "true" string on success error message on fail.
 * @original delete_type
 */
static function delete($name)
{
    $type_data_path = self::getPath($name);

    $type_data = self::get($name);

    //Check that user is not deleting the systema type pages
    if($name == "pages")
    {
        return System::errorMessage("delete_system_type");
    }

    if(!unlink($type_data_path))
    {
        return System::errorMessage("write_error_data");
    }

    if(isset($type_data["image"]) && trim($type_data["image"]) != "")
    {
        Pages\Images::delete($type_data["image"], "admin/types/image");
    }

    return "true";
}

/**
 * Edits or changes the data of an existing content type.
 *
 * @param string $name The machine name of the type.
 * @param array $fields Array with all the new values of the type.
 *
 * @return bool true on success false on fail.
 * @original edit_type
 */
static function edit($name, $fields)
{
    $type_data_path = self::getPath($name);

    //Call add_type hook before creating the category
    Modules::hook("hook_edit_type", $name, $fields);

    $fields["categories"] = serialize($fields["categories"]);
    $fields["uploads"] = serialize($fields["uploads"]);
    $fields["posts"] = serialize($fields["posts"]);

    if(is_array($fields["image"]))
    {
        $page_type_images_data = Pages::get("admin/types/image");

        if(!$page_type_images_data)
        {
            $uri = "";
            Pages::add(
                "admin/types/image",
                array(
                    "title" => "<?php print t('Type Images') ?>",
                    "content" => "<?php protected_page(); ?>",
                    "is_system" => 1
                ),
                $uri
            );
        }

        $image_name = "";

        Pages\Images::add($fields["image"], "", "admin/types/image", $image_name);

        $fields["image"] = $image_name;
    }

    return Data::edit(0, $fields, $type_data_path);
}

/**
 * Get an array with data of a specific content type.
 *
 * @param string $name Machine name of the type.
 *
 * @return array An array with all the fields of the type.
 * @original get_type_data
 */
static function get($name)
{
    $type_data_path = self::getPath($name);

    $type = Data::parse($type_data_path);

    $type[0]["categories"] = isset($type[0]["categories"]) ?
        unserialize($type[0]["categories"])
        :
        array()
    ;

    if(!isset($type[0]["uploads"]))
    {
        $type[0]["uploads"] = array();

        foreach(Groups::getList() as $name => $machine_name)
        {
            $type[0]["uploads"][$machine_name] = array(
                "maximum_images" => 0,
                "maximum_files" => 0
            );
        }
    }
    else
    {
        $type[0]["uploads"] = unserialize($type[0]["uploads"]);
    }

    if(!isset($type[0]["posts"]))
    {
        $type[0]["posts"] = array();

        foreach(Groups::getList() as $name => $machine_name)
        {
            $type[0]["posts"][$machine_name] = 0;
        }
    }
    else
    {
        $type[0]["posts"] = unserialize($type[0]["posts"]);
    }


    return $type[0];
}

/**
 * Get the image url of a given content type.
 *
 * @param string $name Machine name of the type.
 * @param int $width Amount in pixels.
 * @param int $height Amount in pixels.
 * @param bool $ar Flag that indicates if aspect ratio should be kept.
 * @param string $bg The background color in html format, eg: FFFFFF
 *
 * @return string Url of image or empty string if nothing found.
 * @original type_get_image_url
 */
static function getImageUrl(
    $name, $width=null, $height=null, $ar=null, $bg=null
)
{
    static $type_image=array();

    if(isset($type_image[$name]))
    {
        return Uri::url(
            $type_image[$name],
            array(
                "w" => $width,
                "h" => $height,
                "ar" => $ar,
                "bg" => $bg,
            )
        );
    }

    $type_data = self::get($name);

    if(isset($type_data["image"]) && trim($type_data["image"]) != "")
    {
        $type_image[$name] = "image/admin/types/image/{$type_data['image']}";
    }
    else
    {
        $images = Pages\Images::getList("admin/types/image");

        $image_found = false;
        foreach($images as $image)
        {
            if($image["name"] == "no-pic.png")
            {
                $image_found = true;
                break;
            }
        }

        if(!$image_found)
        {
            if(empty(Pages::get("admin/types/image")))
            {
                $uri = "";
                Pages::add(
                    "admin/types/image",
                    array(
                        "title" => "<?php print t('Type Images') ?>",
                        "content" => "<?php protected_page(); ?>",
                        "is_system" => 1
                    ),
                    $uri
                );
            }

            $image_name = "";

            FileSystem::copy(
                "styles/images/no-pic.png",
                Site::dataDir() . "no-pic.png"
            );

            $image = array(
                "type" => "image/png",
                "name" => "no-pic.png",
                "tmp_name" => Site::dataDir() . "no-pic.png"
            );

            Pages\Images::add($image, "", "admin/types/image", $image_name);
        }

        $type_image[$name] = "image/admin/types/image/no-pic.png";
    }

    return Uri::url(
        $type_image[$name],
        array(
            "w" => $width,
            "h" => $height,
            "ar" => $ar,
            "bg" => $bg,
        )
    );
}

/**
 * Gets the list of available content types.
 *
 * @param string $user_group Optional machine name of a group
 * to only retrieves types where it have permissions.
 * @param string $username Optional username to only retrieve
 * types which max posts hasnt been reached.
 *
 * @return array All types in the format
 * types["machine name"] = array(
 *  "name"=>"string",
 *  "description"=>"string"
 * )
 * or null if no type found.
 * @original get_types_list
 */
static function getList($user_group = "", $username = "")
{
    $dir = opendir(Site::dataDir() . "types");

    $types = null;

    while(($file = readdir($dir)) !== false)
    {
        if(
            $file != "." &&
            $file != ".." &&
            !is_dir(Site::dataDir() . "types/$file")
        )
        {
            $machine_name = str_replace(".php", "", $file);

            if($user_group)
            {
                if(Authentication::hasTypeAccess($machine_name, $user_group, $username))
                {
                    $types[$machine_name] = self::get($machine_name);
                }
            }
            else
            {
                $types[$machine_name] = self::get($machine_name);
            }
        }
    }

    closedir($dir);

    return $types;
}

/**
 * To check if a user reached the maximum amount of posts
 * for a given type.
 *
 * @param string $type
 * @param string $username
 *
 * @return bool True if user reached max post allowed false otherwise
 * @original user_reached_max_posts
 */
static function userReachedMaxPosts($type, $username)
{
    if(Sql::dbExists("search_engine") && !Authentication::isAdminLogged())
    {
        $type_data = self::get($type);
        $user_data = Users::get($username);

        if($type_data["posts"][$user_data["group"]] > 0)
        {
            $db = Sql::open("search_engine");

            Sql::turbo($db);

            $result = Sql::query(
                "select count(uri) as total_posts from uris " .
                "where author='$username' and type='$type'",
                $db
            );

            $data = Sql::fetchArray($result);

            Sql::close($db);

            if($data["total_posts"] >= $type_data["posts"][$user_data["group"]])
            {
                return true;
            }
        }
    }

    return false;
}

/**
 * Generates array of checkbox form fields for each categories.
 *
 * @param array $selected The array of selected categories.
 *
 * @return array A series of fields that can
 * be used when generating a form.
 * @original generate_types_categories_fields_list
 */
static function generateCategoriesFields($selected = null)
{
    $fields = array();

    $categories_list = Categories::getList();

    foreach($categories_list as $machine_name => $category_data)
    {
        $checked = false;
        if($selected)
        {
            foreach($selected as $value)
            {
                if($value == $machine_name)
                {
                    $checked = true;
                    break;
                }
            }
        }

        $fields[] = array(
            "type" => "checkbox",
            "checked" => $checked,
            "label" => t($category_data["name"]),
            "name" => "categories[]",
            "id" => "types",
            "description" => t($category_data["description"]),
            "value" => $machine_name
        );

        $fields[] = array(
            "type" => "other",
            "html_code" => "<br />"
        );
    }

    return $fields;
}

/**
 * Generates array of checkbox form fields for each content type.
 *
 * @param array $selected The array of selected types.
 *
 * @return array A series of fields that can
 * be used when generating a form.
 * @original generate_types_fields_list
 */
static function generateFields($selected = null)
{
    $fields = array();

    $types_list = self::getList();

    foreach($types_list as $machine_name => $type_data)
    {
        $checked = false;
        if($selected)
        {
            foreach($selected as $value)
            {
                if($value == $machine_name)
                {
                    $checked = true;
                    break;
                }
            }
        }

        $fields[] = array(
            "type" => "checkbox",
            "checked" => $checked,
            "label" => t($type_data["name"]),
            "name" => "types[]",
            "id" => "types",
            "description" => t($type_data["description"]),
            "value" => $machine_name
        );

        $fields[] = array("type" => "other", "html_code" => "<br />");
    }

    return $fields;
}

/**
 * Generates an uri for a given content type
 *
 * @param string $type Machine name of the type.
 * @param string $title The title of the content.
 * @param string $user The username of the user that is creating the content.
 *
 * @return string Valid uri for system content creation.
 * @original generate_uri_for_type
 */
static function generateURI($type, $title, $user)
{
    $type_data = self::get($type);

    $type = Uri::fromText($type);
    $user = Uri::fromText($user);
    $title = Uri::fromText($title);

    if(!$type_data["uri_scheme"])
    {
        return $user . "/" . $type . "/" . $title;
    }

    $uri_scheme = $type_data["uri_scheme"];

    $uri_scheme = str_replace(
        array("{user}", "{type}", "{title}"),
        array($user, $type, $title),
        $uri_scheme
    );

    return $uri_scheme;
}

/**
 * Get a type default input format
 *
 * @param string $name The machine name of the type.
 *
 * @return string The type default input format or full_html
 * if no input format assigned.
 * @original get_type_default_input_format
 */
static function getDefaultInputFormat($name)
{
    $type = self::get($name);

    if(!isset($type["input_format"]) || !$type["input_format"])
    {
        return "full_html";
    }

    return $type["input_format"];
}

/**
 * static function to retrieve the title or content labels and descriptions.
 *
 * @param string $type The machine name of the type.
 * @param string $label One of the following values:
 * title_label, title_description, content_label, content_description
 *
 * @return string The corresponding label or description
 * value already translated.
 * @original get_type_label
 */
static function getLabel($type, $label)
{
    $type_data = self::get($type);

    if(isset($type_data[$label]) && trim($type_data[$label]) != "")
    {
        return t($type_data[$label]);
    }

    switch($label)
    {
        case "title_label":
            return t("Title:");
        case "title_description":
            return t("Displayed on the web browser title bar and inside the website.");
        case "content_label":
            return t("Content:");
        case "content_description":
            return "";
    }

    return "";
}

/**
 * Generates the data path where content type information resides.
 *
 * @param string $name The machine name of the content type.
 *
 * @return string $name The path of the type file.
 * @original generate_type_path
 */
static function getPath($name)
{
    $type_path = Site::dataDir() . "types/$name.php";

    return $type_path;
}

}