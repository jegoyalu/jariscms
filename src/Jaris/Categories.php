<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Utilities to manage the categories.
 */
class Categories
{

/**
 * Receives parameters: $machine_name, $data
 * @var string
 */
const SIGNAL_CREATE_CATEGORY = "hook_create_category";

/**
 * Receives parameters: $machine_name, $path
 * @var string
 */
const SIGNAL_DELETE_CATEGORY = "hook_delete_category";

/**
 * Receives parameters: $machine_name, $new_data, $path
 * @var string
 */
const SIGNAL_EDIT_CATEGORY = "hook_edit_category";

/**
 * Receives parameters: $machine_name, $data
 * @var string
 */
const SIGNAL_GET_CATEGORY_DATA = "hook_get_category_data";

/**
 * Receives parameters: $category, $data
 * @var string
 */
const SIGNAL_CREATE_SUBCATEGORY = "hook_create_subcategory";

/**
 * Receives parameters: $category, $id, $path
 * @var string
 */
const SIGNAL_DELETE_SUBCATEGORY = "hook_delete_subcategory";

/**
 * Receives parameters: $category, $new_data, $id
 * @var string
 */
const SIGNAL_EDIT_SUBCATEGORY = "hook_edit_subcategory";

/**
 * Receives parameters: $category, $data, $id
 * @var string
 */
const SIGNAL_GET_SUBCATEGORY_DATA = "hook_get_subcategory_data";

/**
 * Creates a category.
 *
 * @param string $machine_name The machine name of the category to create
 * @param array $data Data to store to the category in the format:
 * data = array("name"=>"value", "description"=>value)
 *
 * @return string "true" on success or error message on fail.
 */
static function add(string $machine_name, array $data): string
{
    $path = self::getPath($machine_name);

    //First we make category directory
    if(!file_exists($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    $category_data_path = $path . "/data.php";

    //Check if type already exist.
    if(file_exists($category_data_path))
    {
        return System::errorMessage("category_exist");
    }

    //Call create_category hook before creating the category
    Modules::hook("hook_create_category", $machine_name, $data);

    //Add category data
    if(Data::add($data, $category_data_path))
    {
        self::addBlock($machine_name, $data);

        return "true";
    }
    else
    {
        return System::errorMessage("write_error_data");
    }
}

/**
 * Deletes a category data directory.
 *
 * @param string $machine_name The machine name of the category.
 *
 * @return bool True on success or false on fail.
 */
static function delete(string $machine_name): bool
{
    $path = self::getPath($machine_name);

    //Call delete_category hook before deleting the category
    Modules::hook("hook_delete_category", $machine_name, $path);

    //Clears the category directory and deletes it
    if(!FileSystem::recursiveRemoveDir($path))
    {
        return false;
    }

    Blocks::deleteByField("category_name", $machine_name);

    return true;
}

/**
 * Modifies the data of a category.
 *
 * @param string $machine_name The machine name of the category.
 * @param array $new_data New data in the format:
 * $data = array("name"=>value, "description"=>value)
 *
 * @return bool True on success or false on fail.
 */
static function edit(string $machine_name, array $new_data): bool
{
    $path = self::getPath($machine_name);

    //Call edit_category hook before editing the category
    Modules::hook("hook_edit_category", $machine_name, $new_data, $path);

    return Data::edit(0, $new_data, $path . "/data.php");
}

/**
 * Gets all the data of a category.
 *
 * @param string $machine_name The machine name of the category.
 *
 * @return array All the data fields of the category in the format
 * $data["field_name"] = "value";
 */
static function get(string $machine_name): array
{
    static $data;

    if(!is_array($data[$machine_name]))
    {
        $path = self::getPath($machine_name);

        $data[$machine_name] = Data::get(0, $path . "/data.php");
    }

    //Call get_category_data hook before returning the data
    Modules::hook(
        "hook_get_category_data",
        $machine_name,
        $data[$machine_name]
    );

    return $data[$machine_name];
}

/**
 * Creates a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param array $data Data to store to the sub category
 * in the format: data = array("name"=>"value",
 * "description"=>value)
 *
 * @return bool True on success or false on fail.
 */
static function addSubcategory(string $category, array $data): bool
{
    $path = self::getPath($category);

    //Call create_subcategory hook before creating the category
    Modules::hook("hook_create_subcategory", $category, $data);

    $added = Data::add($data, $path . "/sub_categories.php");

    if($added)
    {
        self::populateContentCount($category);
    }

    return $added;
}

/**
 * Deletes a subcategory and all its subcategories.
 *
 * @param string $category The machine name of the main category.
 * @param int $id The id of the subcategory to delete.
 * @param ?array &$sub_categories
 *
 * @return bool True on success or false on fail.
 */
static function deleteSubcategory(
    string $category, int $id, ?array &$sub_categories=array()
): bool
{
    $path = self::getPath($category);

    //Call delete_subcategory hook before deleting the category
    Modules::hook("hook_delete_subcategory", $category, $id, $path);

    if(!$sub_categories)
        $sub_categories = self::getChildSubcategories($category, $id);

    if($sub_categories)
    {
        foreach($sub_categories as $sub_category_id=>$sub_category_data)
        {
            $sub_sub_categories = is_array($sub_category_data["sub_items"]) ?
                $sub_category_data["sub_items"] :
                array()
            ;

            if(
                self::deleteSubcategory(
                    $category, $sub_category_id, $sub_sub_categories
                )
            )
            {
                continue;
            }
            else
            {
                return false;
            }
        }
    }

    if(Data::delete($id, $path . "/sub_categories.php"))
    {
        if(Sql::dbExists("categories_content"))
        {
            Sql::escapeVar($id);
            Sql::escapeVar($category);

            $db = Sql::open("categories_content");

            Sql::query(
                "delete from categories_content where "
                . "category='$category' and "
                . "id='$id'",
                $db
            );

            Sql::close($db);
        }

        return true;
    }

    return false;
}

/**
 * Modifies the data of a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param array $new_data New data in the format:
 * $data = array("name"=>value, "description"=>value)
 * @param int $id The id of the subcategory to edit.
 *
 * @return bool True on success or false on fail.
 */
static function editSubcategory(
    string $category, array $new_data, int $id
): bool
{
    $path = self::getPath($category);

    //Call edit_subcategory hook before editing the page
    Modules::hook("hook_edit_subcategory", $category, $new_data, $id);

    return Data::edit($id, $new_data, $path . "/sub_categories.php");
}

/**
 * Gets all the data of a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param int $id The id of the subcategory.
 *
 * @return array All the data fields of the subcategory in the
 * format $data["field_name"] = "value";
 */
static function getSubcategory(string $category, int $id): array
{
    if($id === null)
        return array();

    $sub_categories = self::getSubcategories($category);

    $data = $sub_categories[$id];

    //Call get_category_data hook before returning the data
    Modules::hook("hook_get_subcategory_data", $category, $data, $id);

    return $data;
}

/**
 * Recursive static function that returns the subcategories of a subcategory.
 *
 * @param string $category the machine name of the main category.
 * @param int|string $parent_id the id of the parent item.
 *
 * @return array The parent subcategory with its subcategories and also
 * the subcategories of the subcategories in another array. For example:
 * $parent_subcategory = array(..., subcategory_values, ..., "sub_items"=>array())
 */
static function getChildSubcategories(
    string $category, $parent_id = "root"
): array
{
    $subcategories = self::getSubcategories($category);

    $subcategory_childrens = array();
    if($subcategories)
    {
        foreach($subcategories as $id => $fields)
        {
            if("" . $fields["parent"] . "" == "" . $parent_id . "")
            {
                //get the sub items of this item
                $sub_items = array(
                    "sub_items" => Data::sort(
                        self::getChildSubcategories(
                            $category, $id
                        ),
                        "order"
                    )
                );

                if(count($sub_items["sub_items"]) > 0)
                {
                    $fields += $sub_items;
                }

                $subcategory_childrens[$id] = $fields;
            }
        }
    }

    return $subcategory_childrens;
}

/**
 * Gets the list of available subcategories.
 *
 * @param string $category The machine name main category.
 *
 * @return array All subcategories in the format
 * categories["id"] = array(
 *  "title"=>"string",
 *  "description"=>"string"
 *  "parent"=>"string"
 *  "order"=>int
 * )
 * or empty array if no subcategory is found
 */
static function getSubcategories(string $category): array
{
    static $categories;

    if(!isset($categories[$category]))
    {
        $categories = array();

        $path = self::getPath($category);

        $category_data = self::get($category);

        if(!isset($category_data["sorting"]) || !$category_data["sorting"])
        {
            $categories[$category] = Data::sort(
                Data::parse($path . "/sub_categories.php"), "order"
            );
        }
        else
        {
            $categories[$category] = Data::sort(
                Data::parse($path . "/sub_categories.php"), "title"
            );
        }
    }

    if(isset($categories[$category]))
    {
        return $categories[$category];
    }

    return array();
}

/**
 * Gets the list of available categories.
 *
 * @param string $type Optional value to only get the categories
 * available for a content type.
 *
 * @return array All categories in the format
 * categories["machine name"] = array(
 *  "name"=>"string",
 *  "description"=>"string",
 *  "multiple" = bool,
 *  "sorting" = bool,
 *  "order" = int
 * )
 * or empty array if no category is found.
 */
static function getList(string $type = ""): array
{
    $dir = opendir(Site::dataDir() . "categories");

    $categories = array();

    while(($file = readdir($dir)) !== false)
    {
        if($file != "." && $file != ".." && $file != "readme.txt")
        {
            $machine_name = $file;

            $categories[$machine_name] = self::get($machine_name);
        }
    }

    closedir($dir);

    if($categories)
    {
        ksort($categories);
    }

    if($type && $categories)
    {
        $type_data = Types::get($type);

        //Only if user selected specific categories
        if(is_array($type_data["categories"]))
        {
            foreach($categories as $category_name => $category_data)
            {
                $is_available = false;
                foreach($type_data["categories"] as $available_categories)
                {
                    if($category_name == $available_categories)
                    {
                        $is_available = true;
                        break;
                    }
                }

                if(!$is_available)
                {
                    unset($categories[$category_name]);
                }
            }
        }
        else
        {
            $categories = array();
        }
    }

    if($categories)
    {
        $categories = Data::sort($categories, "order");

        return $categories;
    }

    return array();
}

/**
 * Recursively organize subcategories and illustrate its
 * childs as parents with white spaces.
 *
 * @param string $category_name The machine name of the main category.
 * @param string $parent The parent of the subcategory, root for main categories.
 * @param string $position
 * @param bool $with_content Only get categories that have content.
 *
 * @return array All subcategories.
 */
static function getSubcategoriesInParentOrder(
    string $category_name,
    string $parent = "root",
    string $position = "",
    bool $with_content = false
): array
{
    static $content_count = array();

    if($with_content && !isset($content_count[$category_name]))
    {
        $category = $category_name;
        Sql::escapeVar($category);

        $count = Sql::getDataList(
            "categories_content",
            "categories_content",
            0, 1000,
            "where category='$category'"
        );

        $content_count[$category_name] = array();

        foreach($count as $count_data)
        {
            $content_count[$category_name][$count_data["id"]] =
                $count_data["amount"]
            ;
        }
    }

    $category_data = self::get($category_name);

    if(!$category_data["sorting"])
    {
        $subcategories_list = Data::sort(
            self::getChildSubcategories($category_name, $parent),
            "order"
        );
    }
    else
    {
        $subcategories_list = Data::sort(
            self::getChildSubcategories($category_name, $parent),
            "title"
        );
    }

    $subcategories = array();

    if($subcategories_list)
    {
        foreach($subcategories_list as $id => $fields)
        {
            if(
                $with_content
                &&
                $parent != "root"
                &&
                $content_count[$category_name][$id] <= 0)
            {
                continue;
            }

            $childs = self::getSubcategoriesInParentOrder(
                $category_name, $id, $position . "- ", $with_content
            );

            if($with_content)
            {
                if(
                    count($childs) > 0
                    ||
                    $content_count[$category_name][$id] > 0
                )
                {
                    $subcategories[$id] = $fields;
                    $subcategories[$id]["title"] = $position
                        . t($fields["title"])
                        . " "
                        . "(".$content_count[$category_name][$id].")"
                    ;
                    $subcategories += $childs;
                }
            }
            else{
                $subcategories[$id] = $fields;
                $subcategories[$id]["title"] = $position . t($fields["title"]);
                $subcategories += $childs;
            }
        }
    }

    return $subcategories;
}

/**
 * Generates the neccesary array of all available categories for the form fields.
 *
 * @param array $selected The array of selected categories on the control.
 * @param string $main_category Machine name of category to generate the
 * form field for a specific and single category.
 * @param string $type The type to generate the available categories for it.
 * @param string $prefix A prefix for the generated field names.
 *
 * @return array Data that represent a series of fields that can
 * be used when generating a form on a fieldset.
 */
static function generateFields(
    array $selected = [],
    string $main_category = "",
    string $type = "",
    string $prefix = ""
): array
{
    $fields = array();
    $categories_list = array();

    if(!$main_category && $type)
    {
        $categories_list = self::getList($type);
    }
    elseif($main_category)
    {
        $categories_list[$main_category] = self::get($main_category);
    }

    foreach($categories_list as $machine_name => $values)
    {
        $subcategories = self::getSubcategoriesInParentOrder($machine_name);

        $select_values = array();
        if(!$values["multiple"])
        {
            $select_values[t("-None Selected-")] = "-1";
        }

        foreach($subcategories as $id => $sub_values)
        {
            //In case person created categories with the same name
            if(isset($select_values[t($sub_values["title"])]))
            {
                $title = t($sub_values["title"]) . " ";
                while(isset($select_values[$title]))
                {
                    $title .= " ";
                }

                $select_values[$title] = $id;
            }
            else
            {
                $select_values[t($sub_values["title"])] = $id;
            }
        }

        $multiple = false;
        if($values["multiple"])
        {
            $multiple = true;
        }

        if(count($select_values) > 1)
        {
            if(count($selected) > 0)
            {
                $fields[] = array(
                    "type" => "select",
                    "multiple" => $multiple,
                    "selected" => $selected[$prefix . $machine_name],
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "inline" => true
                );
            }
            else
            {
                $fields[] = array(
                    "type" => "select",
                    "multiple" => $multiple,
                    "name" => "$prefix{$machine_name}[]",
                    "label" => t($values["name"]),
                    "id" => $prefix . $machine_name,
                    "value" => $select_values,
                    "inline" => true
                );
            }
        }
    }

    return $fields;
}

/**
 * Creates a category menu block.
 *
 * @param string $machine_name The machine name of the category.
 * @param array $data The category data.
 */
static function addBlock(string $machine_name, array $data): void
{
    $category_block = array();

    $category_block["description"] = $machine_name . " " . "categories";
    $category_block["title"] = $data["name"] . " " . "categories";
    $category_block["content"] = "<?php\n"
        . "print Jaris\\Categories::generateMenu(\"$machine_name\");\n"
        . "?>"
    ;
    $category_block["order"] = "0";
    $category_block["display_rule"] = "all_except_listed";
    $category_block["pages"] = "";
    $category_block["return"] = "";
    $category_block["is_system"] = "1";
    $category_block["category_name"] = $machine_name;

    Blocks::add($category_block, "none");
}

/**
 * Creates a category menu block.
 *
 * @param string $machine_name The machine name of the category.
 * @param string $parent_id The id of the subcategory to generate the menu.
 *
 * @return string UL html of the category menu.
 */
static function generateMenu(
    string $machine_name, string $parent_id="root"
): string
{
    $position = 1;
    $subcategories_array = self::getChildSubcategories(
        $machine_name, $parent_id
    );
    $count_subcategories = count($subcategories_array);

    $category_data = self::get($machine_name);

    $links = "";

    if($count_subcategories > 0)
    {
        $links .= "<ul class=\"menu $machine_name\">";

        foreach($subcategories_array as $subcategory_id=>$subcategory)
        {
            $list_class = "";

            $subcategory["url"] = "category/$machine_name/" .
                    Uri::fromText($subcategory["title"])
            ;

            if($position == 1)
            {
                $list_class = " class=\"first\"";
            }
            elseif($position == $count_subcategories)
            {
                $list_class = " class=\"last\"";
            }
            else
            {
                $list_class = "";
            }

            // Translate the title and description using the strings.php
            // file if available.
            $subcategory['title'] = t($subcategory['title']);
            $subcategory['description'] = t($subcategory['description']);

            $active = Uri::get() == $subcategory["url"] ?
                "class=\"active\""
                :
                ""
            ;

            $links .= "<li{$list_class}><span><a $active "
                . "title=\"{$subcategory['description']}\" href=\""
                . Uri::url($subcategory['url']) . "\">"
                . $subcategory['title'] . "</a></span>"
            ;

            $links .= "</li>\n";

            if($category_data["display_subcategories"])
            {
                $links .= self::generateMenu($machine_name, $subcategory_id);
            }

            $position++;
        }

        $links .= "</ul>";
    }

    return $links;
}

/**
 * Prepares the corresponding $_REQUEST variables to display the search
 * results of a category using an alias.
 *
 * @param string &$page The uri alias of the category.
 */
static function showResults(string &$page): void
{
    $sections = explode("/", $page);

    $category_name = $sections[1];

    $path = self::getPath($category_name);

    if(file_exists($path))
    {
        $subcategories = self::getSubcategories($category_name);

        if(isset($sections[2]))
        {
            if($subcategories)
            {
                foreach($subcategories as $id => $data)
                {
                    $category_uri = Uri::fromText($data["title"]);

                    if($category_uri == $sections[2])
                    {
                        $_REQUEST[$category_name][] = $id;

                        $page = "search";

                        if(!isset($_REQUEST["page"]))
                        {
                            $_REQUEST["search"] = 1;
                        }

                        break;
                    }
                }
            }
        }
        else if(isset($sections[1]))
        {
            if($subcategories)
            {
                foreach($subcategories as $id => $data)
                {
                    $_REQUEST[$category_name][] = $id;
                }
            }

            $page = "search";

            if(!isset($_REQUEST["page"]))
            {
                $_REQUEST["search"] = 1;
            }
        }
    }
}

/**
 * Create database that will hold each category content count.
 */
static function createContentCountDb(): void
{
    static $exists = false;

    if(!$exists && !Sql::dbExists("categories_content"))
    {
        $db = Sql::open("categories_content");

        Sql::query("PRAGMA journal_mode=WAL", $db);

        Sql::query(
            "create table categories_content ("
            . "category text,"
            . "id text,"
            . "parent text,"
            . "amount integer"
            . ")",
            $db
        );

        Sql::query(
            "create index categories_content_index on categories_content ("
            . "category desc,"
            . "id desc,"
            . "parent desc"
            . ")",
            $db
        );

        Sql::close($db);
    }

    $exists = true;
}

/**
 * Populates the categories content count database for the given category
 * or all existing categories if no category is given
 * @param  string $category Machine name of category.
 */
static function populateContentCount(string $category=""): void
{
    self::createContentCountDb();

    $db = Sql::open("categories_content");

    if($category == "")
    {
        $categories = self::getList();

        foreach($categories as $category => $cat_data)
        {
            $subcategories = self::getSubcategories($category);

            Sql::escapeVar($category);

            foreach($subcategories as $sub_id => $sub_data)
            {
                Sql::escapeVar($sub_id);

                $result = Sql::query(
                    "select * from categories_content where "
                    . "category = '$category' and "
                    . "id = '$sub_id'",
                    $db
                );

                if(!($data = Sql::fetchArray($result)))
                {
                    Sql::escapeVar($sub_data["parent"]);

                    Sql::query(
                        "insert into categories_content ("
                        . "category, id, parent, amount"
                        . ") values("
                        . "'$category', '$sub_id', '{$sub_data['parent']}', 0"
                        . ")",
                        $db
                    );
                }
            }
        }
    }
    else
    {
        $subcategories = self::getSubcategories($category);

        Sql::escapeVar($category);

        foreach($subcategories as $sub_id => $sub_data)
        {
            Sql::escapeVar($sub_id);

            $result = Sql::query(
                "select * from categories_content where "
                . "category = '$category' and "
                . "id = '$sub_id'",
                $db
            );

            if(!($data = Sql::fetchArray($result)))
            {
                Sql::escapeVar($sub_data["parent"]);

                Sql::query(
                    "insert into categories_content ("
                    . "category, id, parent, amount"
                    . ") values("
                    . "'$category', '$sub_id', '{$sub_data['parent']}', 0"
                    . ")",
                    $db
                );
            }
        }
    }

    Sql::close($db);
}

/**
 * Increment the content count for the given categories.
 * @param array $categories Array of categories as found on the page data structure.
 * @return void
 */
static function incrementContent(array $categories): void
{
    static $exists = false;

    if($exists || Sql::dbExists("categories_content"))
    {
        $exists = true;

        $db = Sql::open("categories_content");

        Sql::beginTransaction($db);

        foreach($categories as $machine_name => $sub_categories)
        {
            Sql::escapeVar($machine_name);

            foreach($sub_categories as $sub_id)
            {
                Sql::query(
                    "update categories_content "
                    . "set amount = amount+1 "
                    . "where "
                    . "category='$machine_name' and "
                    . "id = $sub_id",
                    $db
                );
            }
        }

        Sql::commitTransaction($db);

        Sql::close($db);
    }
}

/**
 * Decrement the content count for the given categories.
 * @param array $categories Array of categories as found on the page data structure.
 * @return void
 */
static function decrementContent(array $categories): void
{
    static $exists = false;

    if($exists || Sql::dbExists("categories_content"))
    {
        $exists = true;

        $db = Sql::open("categories_content");

        Sql::beginTransaction($db);

        foreach($categories as $machine_name => $sub_categories)
        {
            Sql::escapeVar($machine_name);

            foreach($sub_categories as $sub_id)
            {
                Sql::escapeVar($sub_id);

                Sql::query(
                    "update categories_content "
                    . "set amount = amount-1 "
                    . "where "
                    . "category='$machine_name' and "
                    . "id = '$sub_id'",
                    $db
                );
            }
        }

        Sql::commitTransaction($db);

        Sql::close($db);
    }
}

/**
 * Generates the system path to the category data directory.
 *
 * @param string $machine_name the machine name of the category.
 *
 * @return string Path to category data directory.
 */
static function getPath(string $machine_name): string
{
    $path = Site::dataDir() . "categories/$machine_name";

    return $path;
}

}
