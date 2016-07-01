<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 General License Protecting Programmers
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Utilities to handle blocks.
 */
class Blocks
{

/**
 * Receives parameters: $fields, $position, $page
 * @var string
 */
const SIGNAL_ADD_BLOCK = "hook_add_block";

/**
 * Receives parameters: $id, $position, $new_data, $page
 * @var string
 */
const SIGNAL_EDIT_BLOCK = "hook_edit_block";

/**
 * Receives parameters: $id, $position, $data, $page
 * @var string
 */
const SIGNAL_DELETE_BLOCK = "hook_delete_block";

/**
 * Adds a new block to a block file.
 *
 * @param array $fields An array with the needed fields to write to the block.
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty
 * for global blocks.
 *
 * @return bool True on success false on fail.
 * @original add_block
 */
static function add($fields, $position, $page = "")
{
    $block_data_path = self::getPath($position, $page);

    //Create page block directory in case is not present
    $path = str_replace("$position.php", "", $block_data_path);
    if(!file_exists($path))
    {
        FileSystem::makeDir($path, 0755, true);
    }

    $fields["groups"] = serialize($fields["groups"]);
    $fields["themes"] = serialize($fields["themes"]);
    $fields["id"] = self::getNewId();

    //Call add_block hook before creating the block
    Modules::hook("hook_add_block", $fields, $position, $page);

    return Data::add($fields, $block_data_path);
}

/**
 * Deletes an existing block from a file.
 *
 * @param int $id Unique identifier of the block on position level.
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty
 * for global blocks.
 *
 * @return bool true on success false on fail.
 * @original delete_block
 */
static function delete($id, $position, $page = "")
{
    $data = self::get($id, $position);

    $block_data_path = self::getPath($position, $page);

    if(Data::delete($id, $block_data_path))
    {
        // Remove translations
        if(isset($data["id"]))
        {
            $languages = Language::getInstalled();

            foreach($languages as $code => $name)
            {
                $translations = Language::dataTranslate(
                    "blocks/{$data['id']}.php",
                    $code,
                    true
                );

                if(file_exists($translations))
                {
                    unlink($translations);
                }
            }
        }

        //Call delete_block hook before editing the block
        Modules::hook("hook_delete_block", $id, $position, $data, $page);

        return true;
    }

    return false;
}

/**
 * Deletes an existing block by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 * @param string $page The page uri where the block belongs.
 *
 * @return bool True on success false on fail.
 * @original delete_block_by_field
 */
static function deleteByField($field_name, $value, $page = "")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";

    foreach($block_positions as $position)
    {
        $blocks = self::getList($position, $page);

        if(is_array($blocks))
        {
            foreach($blocks as $id => $fields)
            {
                if($fields[$field_name] == $value)
                {
                    if(!self::delete($id, $position, $page))
                    {
                        return false;
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Edits or changes the data of an existing block from a file.
 *
 * @param int $id Unique identifier of the block.
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param array $new_data An array of the fields that will substitue
 * the old values.
 * @param string $page The page where the block reside, leave empty
 * for global blocks.
 *
 * @return bool True on success false on fail.
 * @original edit_block
 */
static function edit($id, $position, $new_data, $page = "")
{
    $block_data_path = self::getPath($position, $page);

    $new_data["groups"] = serialize($new_data["groups"]);
    $new_data["themes"] = serialize($new_data["themes"]);

    if(!isset($new_data["id"]))
    {
        $new_data["id"] = self::getNewId();
    }

    //Call edit_block hook before editing the block
    Modules::hook("hook_edit_block", $id, $position, $new_data, $page);

    return Data::edit($id, $new_data, $block_data_path);
}

/**
 * Edits an existing block by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 * @param array $new_data The new fields to write in block.
 * @param string $page The page where the block resides, leave empty
 * for global blocks.
 *
 * @return bool True on success or false on fail.
 * @original edit_block_by_field
 */
static function editByField($field_name, $value, $new_data, $page = "")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";

    foreach($block_positions as $position)
    {
        $blocks = self::getList($position, $page);

        foreach($blocks as $id => $fields)
        {
            if($fields[$field_name] == $value)
            {
                if(!self::edit($id, $position, $new_data, $page))
                {
                    return false;
                }
            }
        }
    }

    return true;
}

/**
 * Get an array with data of a specific block.
 *
 * @param int $id Unique identifier of the block.
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty
 * for global blocks.
 *
 * @return array An array with all the fields of the block.
 * @original get_block_data
 */
static function get($id, $position, $page = "")
{
    $block_data_path = self::getPath($position, $page);

    $blocks = Data::parse($block_data_path);

    $blocks[$id]["groups"] = isset($blocks[$id]["groups"]) ?
        unserialize($blocks[$id]["groups"])
        :
        null
    ;

    $blocks[$id]["themes"] = isset($blocks[$id]["themes"]) ?
        unserialize($blocks[$id]["themes"])
        :
        null
    ;

    return $blocks[$id];
}

/**
 * Translate a block content if possible.
 *
 * @param array $data Current block data which is replaced by translation.
 * @param string $language_code The code of the language to translate to.
 *
 * @return bool  True if the translation of the block for
 * the given language exists.
 * @original get_block_data_translation
 */
static function getTranslated(&$data, $language_code)
{
    if(isset($data["id"]))
    {
        $file = Language::dataTranslate(
            "blocks/{$data['id']}.php",
            $language_code,
            true
        );

        if(file_exists($file))
        {
            $translation = Data::get(0, $file);

            $data = array_merge($data, $translation);

            return true;
        }
    }

    return false;
}

/**
 * Gets an existing block data by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 * @param string $page If a page block.
 *
 * @return array All fields of the found block or empty array.
 * @original get_block_data_by_field
 */
static function getByField($field_name, $value, $page = "")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";

    foreach($block_positions as $position)
    {
        $blocks = self::getList($position, $page);

        foreach($blocks as $id => $fields)
        {
            if($fields[$field_name] == $value)
            {
                return self::get($id, $position, $page);
            }
        }
    }

    return array();
}

/**
 * Gets the full list of blocks from a file.
 *
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center
 * @param string $page The page where the block reside, leave empty
 * for global blocks.
 *
 * @return array List of blocks available.
 * @original get_block_list
 */
static function getList($position, $page = "")
{
    $block_data_path = self::getPath($position, $page);

    $blocks = Data::parse($block_data_path);

    if(!$blocks)
    {
        return array();
    }
    else
    {
        return $blocks;
    }
}

/**
 * Moves a blocks from one position to another.
 *
 * @param int $id Unique identifier of the block.
 * @param string $current_position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param string $new_position The new position of where to move the block.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return bool True on success false on fail.
 * @original move_block
 */
static function move($id, $current_position, $new_position, $page = "")
{
    $block_data_path = self::getPath($current_position, $page);

    $current_block_data = Data::get($id, $block_data_path);

    $new_block_data_path = self::getPath($new_position, $page);

    Data::add($current_block_data, $new_block_data_path);

    return Data::delete($id, $block_data_path);
}

/**
 * Checks if the current user group has access to the block.
 *
 * @param array $block Data array of the block to check.
 *
 * @return bool True if has access or false of not.
 * @original user_block_access
 */
static function userHasAccess($block)
{
    $current_group = Authentication::currentUserGroup();

    //If administrator not selected any group return true or admin logged.
    if(empty($block["groups"]) || Authentication::isAdminLogged())
    {
        return true;
    }

    foreach($block["groups"] as $machine_name)
    {
        if($machine_name == $current_group)
        {
            return true;
        }
    }

    return false;
}

/**
 * Checks if the current page has access to the block.
 *
 * @param array $block Data array of the block to check.
 * @param string $page The uri of the page to check.
 *
 * @return bool True if has access or false if not.
 * @original page_block_access
 */
static function pageHasAccess($block, $page)
{
    $pages = explode(",", $block["pages"]);

    if($block["display_rule"] == "all_except_listed")
    {
        foreach($pages as $page_check)
        {
            $page_check = trim($page_check);

            //Check if no pages listed and display in all pages.
            if($page_check == "")
            {
                return true;
            }

            $page_check = str_replace(
                    array("/", "/*"), array("\\/", "/.*"), $page_check
            );

            $page_check = "/^$page_check\$/";

            if(preg_match($page_check, $page))
            {
                return false;
            }
        }
    }
    else if($block["display_rule"] == "just_listed")
    {
        foreach($pages as $page_check)
        {
            $page_check = trim($page_check);

            $page_check = str_replace(
                    array("/", "*"), array("\\/", ".*"), $page_check
            );

            $page_check = "/^$page_check\$/";

            if(preg_match($page_check, $page))
            {
                return true;
            }
        }

        return false;
    }

    return true;
}

/**
 * Set the specific settings of a page blocks post settings.
 *
 * @param array $settings The settings to save.
 * @param string $page The uri of the page to set the specific post settings.
 *
 * @return bool True on success false if fail.
 * @original set_block_post_settings
 */
static function setPostSettings($settings, $page)
{
    $settings_path = Pages::getPath($page) . "/blocks/post_settings.php";

    $settings_data[0] = $settings;

    //Create blocks directory if not exists
    if(!file_exists(Pages::getPath($page) . "/blocks"))
    {
        FileSystem::makeDir(Pages::getPath($page) . "/blocks");
    }

    return Data::write($settings_data, $settings_path);
}

/**
 * Gets the specific settings of a page blocks post settings.
 *
 * @param string $page The uri of the page to get the specific post settings.
 *
 * @return array All the post settings.
 * @original get_block_post_settings
 */
static function getPostSettings($page)
{
    $settings_path = Pages::getPath($page) . "/blocks/post_settings.php";

    $settings = array();

    if(file_exists($settings_path))
    {
        $settings = Data::parse($settings_path);
    }
    else
    {
        $fields = array();

        $fields["display_title"] = false;
        $fields["display_image"] = false;
        $fields["thumbnail_width"] = "125";
        $fields["thumbnail_height"] = "";
        $fields["thumbnail_background_color"] = "FFFFFF";
        $fields["keep_aspect_ratio"] = false;
        $fields["maximum_words"] = 20;
        $fields["display_view_more"] = true;

        $settings[0] = $fields;
    }

    return $settings[0];
}

/**
 * Generates the content for a block that display a summary of full page content.
 *
 * @param string $uri The uri of the block to display a summary.
 * @param string $page_uri The uri of the page where the content block resides.
 *
 * @return array Block post data that can be added to actual block data array.
 * @original generate_block_post_content
 */
static function generatePostContent($uri, $page_uri = null)
{
    $settings = self::getPostSettings($page_uri);

    $page_data = Pages::get($uri, Language::getCurrent());
    $content = $page_data["content"];
    $image = "";
    $image_path = "";
    $post_title = "";
    $post_title_plain = "";
    $view_more = "";
    $view_url = "";

    $content = InputFormats::filter($page_data["content"], $page_data["input_format"]);
    $content = Util::contentPreview($content, $settings["maximum_words"], true);

    if($settings["display_image"])
    {
        $images = Data::sort(Pages\Images::getList($uri), "order");

        foreach($images as $id => $fields)
        {
            $image_options["w"] = $settings["thumbnail_width"];

            if($settings["thumbnail_height"])
            {
                $image_options["h"] = $settings["thumbnail_height"];
            }

            if($settings["keep_aspect_ratio"])
            {
                $image_options["ar"] = "1";
            }

            if($settings["thumbnail_background_color"])
            {
                $image_options["bg"] = $settings["thumbnail_background_color"];
            }

            $image = "<a title=\"{$fields['description']}\" href=\"" .
                    Uri::url("$uri") . "\">" .
                    "<img alt=\"{$fields['description']}\" src=\"" .
                    Uri::url("image/$uri/{$fields["name"]}", $image_options) .
                    "\" />" . "</a>"
            ;

            $image_path = Uri::url("image/$uri/$id");

            break;
        }
    }

    if($settings["display_title"])
    {
        $post_title = "<a title=\"{$page_data['title']}\" href=\"" .
                Uri::url("$uri") . "\">" . $page_data["title"] . "</a>"
        ;

        $post_title_plain = $page_data["title"];
    }

    if($settings["display_view_more"])
    {
        $view_more = "<a title=\"{$page_data['title']}\" href=\"" .
                Uri::url("$uri") . "\">" . t("view more") . "</a>"
        ;

        $view_url = Uri::url("$uri");
    }

    $fields = array();

    $fields["content"] = $content;
    $fields["image"] = $image;
    $fields["image_path"] = $image_path;
    $fields["post_title"] = $post_title;
    $fields["post_title_plain"] = $post_title_plain;
    $fields["view_more"] = $view_more;
    $fields["view_url"] = $view_url;

    return $fields;
}

/**
 * Move blocks to correct positions depending on current theme.
 *
 * @param array $header
 * @param array $left
 * @param array $right
 * @param array $center
 * @param array $footer
 * @original move_blocks_by_theme
 */
static function moveByTheme(&$header, &$left, &$right, &$center, &$footer)
{
    $all_blocks = array();
    $all_blocks["header"] = $header;
    $all_blocks["left"] = $left;
    $all_blocks["right"] = $right;
    $all_blocks["center"] = $center;
    $all_blocks["footer"] = $footer;

    foreach($all_blocks as $position => $blocks)
    {
        foreach($blocks as $block_id => $block_data)
        {
            if(isset($block_data["themes"]))
            {
                $themes_conf = unserialize($block_data["themes"]);

                if(is_array($themes_conf))
                {
                    if(isset($themes_conf[Site::$theme]))
                    {
                        if(
                            $themes_conf[Site::$theme] != "" &&
                            $themes_conf[Site::$theme] != $position
                        )
                        {
                            $block_data["original_position"] = $position;
                            $block_data["original_id"] = $block_id;

                            switch($themes_conf[Site::$theme])
                            {
                                case "header":
                                    $header[] = $block_data;
                                    break;
                                case "left":
                                    $left[] = $block_data;
                                    break;
                                case "right":
                                    $right[] = $block_data;
                                    break;
                                case "center":
                                    $center[] = $block_data;
                                    break;
                                case "footer":
                                    $footer[] = $block_data;
                                    break;
                                case "none":
                                    if($position == "header")
                                        unset($header[$block_id]);
                                    elseif($position == "left")
                                        unset($left[$block_id]);
                                    elseif($position == "right")
                                        unset($right[$block_id]);
                                    elseif($position == "center")
                                        unset($center[$block_id]);
                                    elseif($position == "footer")
                                        unset($footer[$block_id]);
                                    break;
                            }

                            if($themes_conf[Site::$theme] != "none")
                            {
                                if($position == "header")
                                    unset($header[$block_id]);
                                elseif($position == "left")
                                    unset($left[$block_id]);
                                elseif($position == "right")
                                    unset($right[$block_id]);
                                elseif($position == "center")
                                    unset($center[$block_id]);
                                elseif($position == "footer")
                                    unset($footer[$block_id]);
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Generates an array of select fields for each theme so the user can select
 * on which position to display a block per theme.
 *
 * @param array $selected
 *
 * @return array
 * @original generate_themes_block_fields_list
 */
static function generateThemesSelect($selected = null)
{
    $fields = array();

    $themes_list = Themes::getEnabled();

    $index = 0;

    foreach($themes_list as $theme_path)
    {
        $theme_info = Themes::get($theme_path);

        $positions = array();
        $positions[t("Default")] = "";
        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";

        $index++;

        if(is_array($selected))
        {
            if(isset($selected[$theme_path]))
            {
                $fields[] = array(
                    "type" => "select",
                    "selected" => $selected[$theme_path],
                    "label" => t($theme_info["name"]),
                    "name" => "themes[$theme_path]",
                    "id" => "themes-$index",
                    "value" => $positions
                );

                continue;
            }
        }

        $fields[] = array(
            "type" => "select",
            "selected" => isset($_REQUEST["themes"][$theme_path]) ?
                $_REQUEST["themes"][$theme_path] :
                "",
            "label" => t($theme_info["name"]),
            "name" => "themes[$theme_path]",
            "id" => "themes-$index",
            "value" => $positions
        );
    }

    return $fields;
}

/**
 * Generates a unique numeric identifier.
 *
 * @return int A new block id.
 * @original blocks_get_new_id
 */
static function getNewId()
{
    $block_data_path = self::getPath("last_id");

    if(!file_exists($block_data_path))
    {
        Data::add(
            array("id"=>1),
            $block_data_path
        );

        return 1;
    }

    $data = Data::get(0, $block_data_path);
    $data["id"]++;

    Data::edit(0, $data, $block_data_path);

    return $data["id"];
}

/**
 * Generates the data path where the block resides.
 *
 * @param string $position The position of the block, valid values:
 * header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return string The path of the blocks file example data/blocks/left.php
 * @original generate_block_path
 */
static function getPath($position, $page = "")
{
    $block_path = "";

    if($page)
    {
        //Page block
        $block_path = Pages::getPath($page) . "/";
    }
    else
    {
        //Global block
        $block_path = Site::dataDir() . "";
    }

    $block_path .= "blocks/" . $position . ".php";

    return $block_path;
}

}