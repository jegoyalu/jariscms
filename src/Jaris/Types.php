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
     */
    public static function add(string $name, array $fields): string
    {
        $type_data_path = self::getPath($name);

        //Create page type directory in case is not present
        $path = str_replace("$name.php", "", $type_data_path);
        if (!file_exists($path)) {
            FileSystem::makeDir($path, 0755, true);
        }

        //Check if type already exist.
        if (file_exists($type_data_path)) {
            return System::errorMessage("type_exist");
        }

        //Call add_type hook before creating the category
        Modules::hook("hook_add_type", $name, $fields);

        if ($fields["image"]) {
            $page_type_images_data = Pages::get("admin/types/image");

            if (!$page_type_images_data) {
                $uri = "";
                Pages::add(
                "admin/types/image",
                [
                    "title" => "<?php print t('Type Images') ?>",
                    "content" => "<?php protected_page(); ?>",
                    "is_system" => 1
                ],
                $uri
            );
            }

            $image_name = "";

            Pages\Images::add($fields["image"], "", "admin/types/image", $image_name);

            $fields["image"] = $image_name;
        }

        $fields["categories"] = is_array($fields["categories"]) ?
        serialize($fields["categories"]) : serialize([])
    ;
        $fields["uploads"] = is_array($fields["uploads"]) ?
        serialize($fields["uploads"]) : serialize([])
    ;
        $fields["posts"] = is_array($fields["posts"]) ?
        serialize($fields["posts"]) : serialize([])
    ;
        $fields["requires_approval"] = is_array($fields["requires_approval"]) ?
        serialize($fields["requires_approval"]) : serialize([])
    ;

        if (!Data::add($fields, $type_data_path)) {
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
     */
    public static function delete(string $name): string
    {
        $type_data_path = self::getPath($name);

        $type_data = self::get($name);

        //Check that user is not deleting the systema type pages
        if ($name == "pages") {
            return System::errorMessage("delete_system_type");
        }

        if (!unlink($type_data_path)) {
            return System::errorMessage("write_error_data");
        }

        if (isset($type_data["image"]) && trim($type_data["image"]) != "") {
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
     */
    public static function edit(string $name, array $fields): bool
    {
        $type_data_path = self::getPath($name);

        //Call add_type hook before creating the category
        Modules::hook("hook_edit_type", $name, $fields);

        $fields["categories"] = is_array($fields["categories"]) ?
        serialize($fields["categories"]) : serialize([])
    ;
        $fields["uploads"] = is_array($fields["uploads"]) ?
        serialize($fields["uploads"]) : serialize([])
    ;
        $fields["posts"] = is_array($fields["posts"]) ?
        serialize($fields["posts"]) : serialize([])
    ;
        $fields["requires_approval"] = is_array($fields["requires_approval"]) ?
        serialize($fields["requires_approval"]) : serialize([])
    ;

        if (is_array($fields["image"])) {
            $page_type_images_data = Pages::get("admin/types/image");

            if (!$page_type_images_data) {
                $uri = "";
                Pages::add(
                "admin/types/image",
                [
                    "title" => "<?php print t('Type Images') ?>",
                    "content" => "<?php protected_page(); ?>",
                    "is_system" => 1
                ],
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
     */
    public static function get(string $name): array
    {
        $type_data_path = self::getPath($name);

        $type = Data::parse($type_data_path);

        $categories = [];
        if (
        isset($type[0]["categories"])
        &&
        is_array($categories=unserialize($type[0]["categories"]))
    ) {
            $type[0]["categories"] = $categories;
        } else {
            $type[0]["categories"] = [];
        }

        if (!isset($type[0]["uploads"])) {
            $type[0]["uploads"] = [];

            foreach (Groups::getList() as $name => $machine_name) {
                $type[0]["uploads"][$machine_name] = [
                "maximum_images" => 0,
                "maximum_files" => 0
            ];
            }
        } else {
            $type[0]["uploads"] = unserialize($type[0]["uploads"]);
        }

        if (!isset($type[0]["posts"])) {
            $type[0]["posts"] = [];

            foreach (Groups::getList() as $name => $machine_name) {
                $type[0]["posts"][$machine_name] = 0;
            }
        } else {
            $type[0]["posts"] = unserialize($type[0]["posts"]);
        }

        if (!isset($type[0]["requires_approval"])) {
            $type[0]["requires_approval"] = [];
        } else {
            $type[0]["requires_approval"] = unserialize(
            $type[0]["requires_approval"]
        );
        }


        return $type[0];
    }

    /**
     * Get the image url of a given content type.
     *
     * @param string $name Machine name of the type.
     * @param ?int $width Amount in pixels.
     * @param ?int $height Amount in pixels.
     * @param ?bool $ar Flag that indicates if aspect ratio should be kept.
     * @param ?string $bg The background color in html format, eg: FFFFFF
     *
     * @return string Url of image or empty string if nothing found.
     */
    public static function getImageUrl(
    string $name,
    ?int $width=null,
    ?int $height=null,
    ?bool $ar=null,
    ?string $bg=null
): string {
        static $type_image=[];

        if (isset($type_image[$name])) {
            return Uri::url(
            $type_image[$name],
            [
                "w" => $width,
                "h" => $height,
                "ar" => $ar,
                "bg" => $bg,
            ]
        );
        }

        $type_data = self::get($name);

        if (isset($type_data["image"]) && trim($type_data["image"]) != "") {
            $type_image[$name] = "image/admin/types/image/{$type_data['image']}";
        } else {
            $images = Pages\Images::getList("admin/types/image");

            $image_found = false;
            foreach ($images as $image) {
                if ($image["name"] == "no-pic.png") {
                    $image_found = true;
                    break;
                }
            }

            if (!$image_found) {
                if (empty(Pages::get("admin/types/image"))) {
                    $uri = "";
                    Pages::add(
                    "admin/types/image",
                    [
                        "title" => "<?php print t('Type Images') ?>",
                        "content" => "<?php protected_page(); ?>",
                        "is_system" => 1
                    ],
                    $uri
                );
                }

                $image_name = "";

                FileSystem::copy(
                System::CSS_PATH . "images/no-pic.png",
                Site::dataDir() . "no-pic.png"
            );

                $image = [
                "type" => "image/png",
                "name" => "no-pic.png",
                "tmp_name" => Site::dataDir() . "no-pic.png"
            ];

                Pages\Images::add($image, "", "admin/types/image", $image_name);
            }

            $type_image[$name] = "image/admin/types/image/no-pic.png";
        }

        return Uri::url(
        $type_image[$name],
        [
            "w" => $width,
            "h" => $height,
            "ar" => $ar,
            "bg" => $bg,
        ]
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
     * or empty array if no type found.
     */
    public static function getList(string $user_group = "", string $username = ""): array
    {
        $dir = opendir(Site::dataDir() . "types");

        $types = [];

        while (($file = readdir($dir)) !== false) {
            if (
            $file != "." &&
            $file != ".." &&
            !is_dir(Site::dataDir() . "types/$file")
        ) {
                $machine_name = str_replace(".php", "", $file);

                if ($user_group) {
                    if (Authentication::hasTypeAccess($machine_name, $user_group, $username)) {
                        $types[$machine_name] = self::get($machine_name);
                    }
                } else {
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
     */
    public static function userReachedMaxPosts(string $type, string $username): bool
    {
        if (Sql::dbExists("search_engine") && !Authentication::isAdminLogged()) {
            $type_data = self::get($type);
            $user_data = Users::get($username);

            if ($type_data["posts"][$user_data["group"]] > 0) {
                $db = Sql::open("search_engine");

                Sql::turbo($db);

                $result = Sql::query(
                "select count(uri) as total_posts from uris " .
                "where author='$username' and type='$type'",
                $db
            );

                $data = Sql::fetchArray($result);

                Sql::close($db);

                if ($data["total_posts"] >= $type_data["posts"][$user_data["group"]]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a user requires approval when publishing content of certain type.
     *
     * @param string $type The machine name of the content type.
     * @param string $group_name The machine name of a user group.
     *
     * @return bool
     */
    public static function groupRequiresApproval(string $type, string $group_name): bool
    {
        if ($group_name == "administrator") {
            return false;
        }

        $type_data = self::get($type);

        if ($type_data["requires_approval"][$group_name]) {
            return true;
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
     */
    public static function generateCategoriesFields(array $selected = []): array
    {
        $fields = [];

        $categories_list = Categories::getList();

        foreach ($categories_list as $machine_name => $category_data) {
            $checked = false;
            if ($selected) {
                foreach ($selected as $value) {
                    if ($value == $machine_name) {
                        $checked = true;
                        break;
                    }
                }
            }

            $fields[] = [
            "type" => "checkbox",
            "checked" => $checked,
            "label" => t($category_data["name"]),
            "name" => "categories[]",
            "id" => "types",
            "description" => t($category_data["description"]),
            "value" => $machine_name
        ];

            $fields[] = [
            "type" => "other",
            "html_code" => "<br />"
        ];
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
     */
    public static function generateFields(array $selected = []): array
    {
        $fields = [];

        $types_list = self::getList();

        foreach ($types_list as $machine_name => $type_data) {
            $checked = false;
            if ($selected) {
                foreach ($selected as $value) {
                    if ($value == $machine_name) {
                        $checked = true;
                        break;
                    }
                }
            }

            $fields[] = [
            "type" => "checkbox",
            "checked" => $checked,
            "label" => t($type_data["name"]),
            "name" => "types[]",
            "id" => "types",
            "description" => t($type_data["description"]),
            "value" => $machine_name
        ];

            $fields[] = ["type" => "other", "html_code" => "<br />"];
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
     */
    public static function generateURI(string $type, string $title, string $user): string
    {
        $type_data = self::get($type);

        $type = Uri::fromText($type);
        $user = Uri::fromText($user);
        $title = Uri::fromText($title);

        if (!$type_data["uri_scheme"]) {
            return $user . "/" . $type . "/" . $title;
        }

        $uri_scheme = $type_data["uri_scheme"];

        $uri_scheme = str_replace(
        ["{user}", "{type}", "{title}"],
        [$user, $type, $title],
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
     */
    public static function getDefaultInputFormat(string $name): string
    {
        $type = self::get($name);

        if (!isset($type["input_format"]) || !$type["input_format"]) {
            return "full_html";
        }

        return $type["input_format"];
    }

    /**
     * Retrieve the title or content labels and descriptions.
     *
     * @param string $type The machine name of the type.
     * @param string $label One of the following values:
     * title_label, title_description, content_label, content_description
     *
     * @return string The corresponding label or description
     * value already translated.
     */
    public static function getLabel(string $type, string $label): string
    {
        $type_data = self::get($type);

        if (isset($type_data[$label]) && trim($type_data[$label]) != "") {
            return t($type_data[$label]);
        }

        switch ($label) {
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
     */
    public static function getPath(string $name): string
    {
        $type_path = Site::dataDir() . "types/$name.php";

        return $type_path;
    }
}
