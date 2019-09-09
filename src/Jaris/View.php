<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Has all the theming functions needed to render a page.
 */
class View
{

/**
 * Receives parameters: $meta_tags
 * @var string
 */
const SIGNAL_GET_META_TAGS = "hook_get_meta_tags";

/**
 * Receives parameters: $content, $content_title, $content_data
 * @var string
 */
const SIGNAL_THEME_CONTENT = "hook_theme_content";

/**
 * Receives parameters: $position, $page, $field
 * @var string
 */
const SIGNAL_THEME_BLOCK = "hook_theme_block";

/**
 * Receives parameters: $styles, $styles_code
 * @var string
 */
const SIGNAL_THEME_STYLES = "hook_theme_styles";

/**
 * Receives parameters: $scripts, $scripts_code
 * @var string
 */
const SIGNAL_THEME_SCRIPTS = "hook_theme_scripts";

/**
 * Receives parameters: $tabs_array
 * @var string
 */
const SIGNAL_THEME_TABS = "hook_theme_tabs";

/**
 * Receives parameters: $page
 * @var string
 */
const SIGNAL_THEME_DISPLAY = "hook_theme_display";

/**
 * Receives parameters: $page, $html
 * @var string
 */
const SIGNAL_THEME_RENDER = "hook_theme_render";

/**
 * Receives parameters: $position, $page, $id, $template_path
 * @var string
 */
const SIGNAL_BLOCK_TEMPLATE = "hook_block_template";

/**
 * Receives parameters: $position, $page, $template_path
 * @var string
 */
const SIGNAL_CONTENT_BLOCK_TEMPLATE = "hook_content_block_template";

/**
 * Receives parameters: $page, $template_path
 * @var string
 */
const SIGNAL_PAGE_TEMPLATE = "hook_page_template";

/**
 * Receives parameters: $page, $type, $template_path
 * @var string
 */
const SIGNAL_CONTENT_TEMPLATE = "hook_content_template";

/**
 * Receives parameters: $group, $username, $template_path
 * @var string
 */
const SIGNAL_USER_PROFILE_TEMPLATE = "hook_user_profile_template";

/**
 * Receives parameters: $page, $results_type, $template_type, $template_path
 * @var string
 */
const SIGNAL_SEARCH_TEMPLATE = "hook_search_template";

/**
 * Array that holds the page tabs in the format array["tab_name"] = "uri".
 * @var array
 */
public static $tabs_list = array();

/**
 * Additional styles added to the final page.
 * @var array
 */
public static $additional_styles = array();

/**
 * Additional scripts added to the final page.
 * @var array
 */
public static $additional_scripts = array();

/**
 * Additional styles code added to the final page.
 * @var string
 */
public static $additional_styles_code = "";

/**
 * Additional scripts code added to the final page.
 * @var string
 */
public static $additional_scripts_code = "";

/**
 * Alternative store for the title displayed on pages.
 * @var string
 */
private static $content_title;

/**
 * An array that stores the custom settings for a theme.
 * @var array
 */
private static $theme_settings = array();

/**
 * Load user defined custom theme settings.
 */
static function loadThemeSettings(): void
{
    $theme = Site::$theme;

    $theme_dir = Themes::directory($theme);

    if(file_exists($theme_dir . "settings.php"))
    {
        self::$theme_settings = ThemesEdit::settings($theme, true);

        if(trim(self::$theme_settings["css"]) != "")
        {
            self::addStyleCode(self::$theme_settings["css"]);
        }

        if(trim(self::$theme_settings["js"]) != "")
        {
            self::addScriptCode(self::$theme_settings["js"]);
        }
    }
}

/**
 * Links a css style file to a generated page.
 *
 * @param string $path A path to css file.
 * @param array $arguments Arguments if the css file is dynamic.
 */
static function addStyle(string $path, array $arguments = []): void
{
    $current_url = Uri::url($path, $arguments);

    $aldready_in_array = false;

    //check is file is not already added
    foreach(self::$additional_styles as $url)
    {
        if($url == $current_url)
        {
            $aldready_in_array = true;
            break;
        }
    }

    if(!$aldready_in_array)
    {
        self::$additional_styles[] = $current_url;
    }
}

/**
 * Links a javascript file to a generated page.
 *
 * @param string $path A path to a javascript file.
 * @param array $arguments Arguments if the javascript file is dynamic.
 */
static function addScript(string $path, array $arguments = []): void
{
    $current_url = Uri::url($path, $arguments);

    $aldready_in_array = false;

    //check is file is not already added
    foreach(self::$additional_scripts as $url)
    {
        if($url == $current_url)
        {
            $aldready_in_array = true;
            break;
        }
    }

    if(!$aldready_in_array)
    {
        self::$additional_scripts[] = $current_url;
    }
}

/**
 * Links a core system css style file to a generated page.
 *
 * @param string $path A path relative to system css files.
 * @param array $arguments Arguments if the css file is dynamic.
 */
static function addSystemStyle(string $path, array $arguments = []): void
{
    self::addStyle(System::CSS_PATH . $path, $arguments);
}

/**
 * Links a core system javascript file to a generated page.
 *
 * @param string $path A path relative to a system javascript file.
 * @param array $arguments Arguments if the javascript file is dynamic.
 */
static function addSystemScript(string $path, array $arguments = []): void
{
    self::addScript(System::JS_PATH . $path, $arguments);
}

/**
 * Add crude css code to the generated page. The <style> tags are
 * added automatically so make sure to only pass css code.
 *
 * @param string $code Css code to add into the page.
 */
static function addStyleCode(string $code): void
{
    self::$additional_styles_code .= $code . "\n\n";
}

/**
 * Add crude javascript code to the generated page. The <script> tags are
 * added automatically so make sure to only pass js code.
 *
 * @param string $code JavaScript code to add into the page.
 */
static function addScriptCode(string $code): void
{
    self::$additional_scripts_code .= $code . "\n\n";
}

/**
 * Queues a tab to the array of tabs that is going to be displayed on the page
 * and can be accessed on the page template using the $tabs variable
 *
 * @param string $name The text used for user render.
 * @param string $uri The url of the tab when the user clicks it.
 * @param array $arguments The arguments to pass to the url.
 * @param int $row poisition where rows will appear.
 */
static function addTab(
    string $name, string $uri, array $arguments=[], int $row=0
): void
{
    self::$tabs_list[$row][$name] = array(
        "uri" => $uri,
        "arguments" => $arguments
    );
}

/**
 * Queues a message to the array of messages that is going to be displayed on
 * the page that can be accessed on the page template using the $messages var.
 *
 * @param string $message The text to display to on the page.
 * @param string $type Type of message can be: normal or error.
 */
static function addMessage(string $message, string $type = "normal"): void
{
    Session::start();

    $_SESSION["messages"][] = array("text" => $message, "type" => $type);
}

/**
 * Gets the current page meta tags or the default system ones
 * stored on the main settings file.
 *
 * @param ?array $page_data If not null the meta tags are generated for the
 * given page data instead of current page.
 *
 * @return string Meta tags html code for insertion on an html page.
 */
static function getMetaTagsHTML(?array &$page_data=[]): string
{
    if(!$page_data)
    {
        $page_data = Pages::get(Uri::get(), Language::getCurrent());
    }

    $description = isset($page_data["description"]) ?
        str_replace(
            array('"', "\n"),
            array("'", " "),
            Util::stripHTMLTags($page_data["description"])
        )
        :
        ""
    ;

    $keywords = isset($page_data["keywords"]) ?
        str_replace(
            array('"', "\n"),
            array("'", " "),
            Util::stripHTMLTags($page_data["keywords"])
        )
        :
        ""
    ;

    $meta_tags = false;

    $meta_tags = "<meta name=\"generator\" content=\"" .
        t("JarisCMS - Copyright JegoYalu.com. All rights reserved.") .
        "\" />\n"
    ;

    //Make sure we are using the latest rendering mode for IE
    $meta_tags .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";

    //Get description
    if($description)
    {
        $meta_tags .= "<meta name=\"description\" content=\"$description\" />\n";
    }

    //Get keywords
    if($keywords)
    {
        $meta_tags .= "<meta name=\"keywords\" content=\"$keywords\" />\n";
    }

    //Call get_meta_tags modules hook before returning data
    Modules::hook("hook_get_meta_tags", $meta_tags);

    return $meta_tags;
}

/**
 * Prepares the content that is going to be displayed
 *
 * @param array $content_list All the page data content.
 * @param string $page The uri of the page that is going to be displayed.
 *
 * @return string Html content preformatted.
 */
static function getContentHTML(array $content_list, string $page): string
{
    $theme = Site::$theme;
    $theme_path = Site::$theme_path;
    $theme_settings = self::$theme_settings;

    $formatted_page = "";

    foreach($content_list as $field)
    {

        $header_data = Data::sort(Blocks::getList("header", $page), "order");
        $footer_data = Data::sort(Blocks::getList("footer", $page), "order");
        $left_data = Data::sort(Blocks::getList("left", $page), "order");
        $right_data = Data::sort(Blocks::getList("right", $page), "order");
        $center_data = Data::sort(Blocks::getList("center", $page), "order");

        if(!isset($field["type"]))
        {
            $field["type"] = "";
        }

        $header = self::getContentBlocksHTML($header_data, "header", $page, $field["type"]);
        $footer = self::getContentBlocksHTML($footer_data, "footer", $page, $field["type"]);
        $left = self::getContentBlocksHTML($left_data, "left", $page, $field["type"]);
        $right = self::getContentBlocksHTML($right_data, "right", $page, $field["type"]);
        $center = self::getContentBlocksHTML($center_data, "center", $page, $field["type"]);

        $images = Pages\Images::getList($page);
        $files = Pages\Files::getList($page);
        $title = Util::stripHTMLTags($field["is_system"] ?
            System::evalPHP($field["title"]) : $field["title"])
        ;
        self::$content_title = $title;
        $content_data = $field;

        if(!Settings::get("classic_views_count", "main"))
        {
            $content_data["views"] = $field["is_system"] ?
                0 : Pages::getViews($page)
            ;
        }
        else
        {
            $content_data["views"] = $field["is_system"] ?
                0 : Pages::countView($page)
            ;
        }

        $views = $content_data["views"];

        $content = "";
        if($field["is_system"])
        {
            $content = System::evalPHP($field['content']);
        }
        else
        {
            $content = InputFormats::filter(
                $field['content'],
                $field["input_format"]
            );
        }

        $content_data["filtered_content"] = $content;

        Modules::hook(
            "hook_theme_content",
            $content,
            self::$content_title,
            $content_data
        );

        $content_title = self::$content_title;

        ob_start();
        include(self::contentTemplate($page, trim($field["type"])));

        $formatted_page .= ob_get_contents();
        ob_end_clean();
    }

    return $formatted_page;
}

/**
 * Prepares the blocks that are going to be displayed.
 *
 * @param array $arrData An array of blocks generated by data_parser function.
 * @param string $position The position of the block: left, right, center, header or footer.
 * @param string $page The uri of the page that is going to be displayed.
 *
 * @return string String with all the data preformatted based on the corresponding
 * block template.
 */
static function getBlocksHTML(
    array $arrData,
    string $position,
    string $page
): string
{
    $theme = Site::$theme;
    $theme_settings = self::$theme_settings;

    $language_code = Language::getCurrent();

    $block = "";

    if($arrData)
    {
        foreach($arrData as $id => $field)
        {
            if(Site::$development_mode && isset($field["module_identifier"]))
            {
                $field = Modules::getBlockData($field);
            }

            Blocks::getTranslated($field, $language_code);

            if(trim($field["content"]) != "")
            {
                //Unserialize groups string to array
                $field["groups"] = isset($field["groups"]) ?
                    unserialize($field["groups"])
                    :
                    array();
                ;

                if($field["return"])
                {
                    // Execute the code on the block return field to know if
                    // the block should be displayed or not
                    $eval_return = false;
                    $return = System::evalPHP($field["return"], $eval_return);

                    if($return == "false" || $return == "true")
                    {
                        // Skip the block on "false" string
                        if($return == "false")
                        {
                            continue;
                        }
                    }
                    elseif($eval_return == false)
                    {
                        continue;
                    }
                }

                if(Blocks::userHasAccess($field))
                {
                    if(Blocks::pageHasAccess($field, $page))
                    {
                        Modules::hook("hook_theme_block", $position, $page, $field);

                        $content = "";

                        if(
                            Authentication::groupHasPermission(
                                "view_blocks", Authentication::currentUserGroup()
                            ) &&
                            Authentication::groupHasPermission(
                                "edit_blocks", Authentication::currentUserGroup()
                            )
                        )
                        {
                            self::addSystemScript("admin/blocks.js");

                            $url = Uri::url(
                                "admin/blocks/edit",
                                array(
                                    "id" => isset($field["original_id"]) && $field["original_id"] ?
                                        $field["original_id"] : $id,
                                    "position" => isset($field["original_position"]) && $field["original_position"] ?
                                        $field["original_position"] : $position
                                )
                            );

                            $content = "<a class=\"instant-block-edit\" href=\"$url\">"
                                . t("edit")
                                . "</a>"
                            ;

                            $content .= "<div style=\"clear: both\"></div>";
                        }

                        if($field["is_system"])
                        {
                            $content .= System::evalPHP($field['content']);
                        }
                        else
                        {
                            $content .= InputFormats::filter(
                                $field["content"],
                                $field["input_format"] ?? "full_html"
                            );
                        }

                        //Dont show block if content is empty
                        if(trim($content) == "")
                        {
                            continue;
                        }

                        $row_id = $id;

                        //Set the id to the unique ID of the block.
                        if(isset($field["id"]))
                        {
                            $id = $field["id"];
                        }

                        ob_start();
                        $title = t($field["title"]);
                        include(self::blockTemplate($position, $page, $row_id));
                        $block .= ob_get_contents();
                        ob_end_clean();
                    }
                }
            }
        }
    }

    return $block;
}

/**
 * Prepares the content blocks that are going to be displayed.
 *
 * @param array $arrData An array of blocks generated by data_parser function.
 * @param string $position The position of the block: left, right, center, header or footer.
 * @param string $page The page uri where the block is going to be displayed.
 * @param string $page_type The page type to retrieve appropiate template.
 *
 * @return string String with all the data preformatted based on the
 * corresponding block template.
 */
static function getContentBlocksHTML(
    array $arrData, string $position, string $page, string $page_type
): string
{
    $theme = Site::$theme;

    $block = "";

    if($arrData)
    {
        foreach($arrData as $id => $field)
        {
            //Unserialize groups string to array
            $field["groups"] = unserialize($field["groups"]);

            if($field["return"])
            {
                //Execute the code on the block return field to know if the
                //block should be displayed or not
                $return = System::evalPHP($field["return"]);

                //Skip the block on "false" string
                if($return == "false")
                {
                    continue;
                }
            }

            if(Blocks::userHasAccess($field))
            {
                if(Blocks::pageHasAccess($field, $page))
                {
                    $post = false;
                    $content = "";
                    $image = "";
                    $image_path = "";
                    $post_title = "";
                    $post_title_plain = "";
                    $view_more = "";
                    $view_url = "";

                    if(
                        Authentication::groupHasPermission(
                            "view_content_blocks",
                            Authentication::currentUserGroup()
                        ) &&
                        Authentication::groupHasPermission(
                            "edit_content_blocks",
                            Authentication::currentUserGroup()
                        )
                    )
                    {
                        self::addSystemScript("admin/blocks.js");

                        $url = Uri::url("admin/pages/blocks/edit", array("uri" => $page, "id" => $id, "position" => $position));
                        $content = "<a class=\"instant-content-block-edit\" href=\"$url\">" . t("edit") . "</a>";
                        $content .= "<div style=\"clear: both\"></div>";
                    }

                    if($field["post_block"] && $field["uri"])
                    {
                        $post_fields = Blocks::generatePostContent(
                            $field["uri"], $page
                        );

                        $post = true;
                        $content .= $post_fields["content"];
                        $image = $post_fields["image"];
                        $image_path = $post_fields["image_path"];
                        $post_title = $post_fields["post_title"];
                        $post_title_plain = $post_fields["post_title_plain"];
                        $view_more = $post_fields["view_more"];
                        $view_url = $post_fields["view_url"];
                    }
                    else if($field["is_system"])
                    {
                        $content .= System::evalPHP($field['content']);
                    }
                    else
                    {
                        $content .= InputFormats::filter(
                            $field['content'], $field["input_format"]
                        );
                    }

                    //Dont show block if content is empty
                    if(trim($content) == "" && !$field["post_block"])
                    {
                        continue;
                    }

                    ob_start();
                    $title = t($field["title"]);
                    include(self::contentBlockTemplate(
                        $position, $page, $page_type, $id)
                    );
                    $block .= ob_get_contents();
                    ob_end_clean();
                }
            }
        }
    }

    return $block;
}

/**
 * Prepares the primary links that are going to be displayed on the page.
 *
 * @param array $arrLinks An array of links generated by data_parser function.
 * @param string $menu_name The machine name of a menu used for css class.
 *
 * @return string All the links preformatted.
 */
static function getLinksHTML(array $arrLinks, string $menu_name): string
{
    $position = 1;
    $count_links = count($arrLinks);

    $links = "";

    if($count_links > 0)
    {
        $links .= "<ul class=\"menu $menu_name\">";

        foreach($arrLinks as $link)
        {
            //Skip disabled menus
            if(isset($link["disabled"]) && trim($link["disabled"]) !== "")
                continue;

            $list_class = "";

            $has_childs = "";
            if(isset($link["expanded"]) && $link["expanded"])
                $has_childs = " haschilds";

            if($position == 1)
            {
                $list_class = " class=\"first l{$position}{$has_childs}\"";
            }
            elseif($position == $count_links)
            {
                $list_class = " class=\"last l{$position}{$has_childs}\"";
            }
            else
            {
                $list_class = " class=\"l{$position}{$has_childs}\"";
            }

            //Translate the title and description using the strings.php file if available.
            $link['title'] = t($link['title']);
            $link['description'] = isset($link['description']) ?
                t($link['description'])
                :
                ""
            ;

            $active = Uri::get() == $link["url"] ? "class=\"active\"" : "";

            if(isset($link["target"]))
            {
                $target = "target=\"{$link['target']}\"";
            }

            $links .= "<li{$list_class}>"
                . "<span $active>"
                . "<a $active $target title=\"{$link['description']}\" "
                . "href=\"" . Uri::url($link['url']) . "\">"
                . $link['title']
                . "</a></span>"
            ;

            if(
                isset($link["sub_items"]) &&
                (
                    (isset($link["expanded"]) && $link["expanded"]) ||
                    $link['url'] == Uri::get()
                )
            )
            {
                $links .= self::getLinksHTML(
                    $link["sub_items"], $menu_name . "-sub-menu"
                );
            }

            $links .= "</li>\n";

            $position++;
        }

        $links .= "</ul>";
    }

    return $links;
}

/**
 * Generate the html code to insert system styles on pages.
 *
 * @param array $styles An array of style files.
 *
 * @return string Html code for the head section of document.
 */
static function getStylesHTML(array $styles): string
{
    $theme = Site::$theme;

    $styles_code = "";
    $theme_dir = rtrim(Themes::directory($theme), "/");
    $theme_info = Themes::get($theme);
    $style_dir = $theme_dir . "/css";
    $style_files = array();

    $exclude_list = array(".", "..");

    $theme_files = array_diff(scandir($theme_dir), $exclude_list);

    if(is_dir($style_dir))
    {
        $style_files = array_diff(scandir($style_dir), $exclude_list);
        sort($style_files);
    }

    $files = array_merge($theme_files, $style_files);

    foreach($files as $file)
    {
        $file_path = "";

        if(is_file("$theme_dir/$file"))
        {
            $file_path = "$theme_dir/$file";
        }
        elseif(is_file("$style_dir/$file"))
        {
            $file_path = "$style_dir/$file";
        }

        $file_array = explode(".", $file_path);
        $extension = $file_array[count($file_array) - 1];

        if($extension == "css")
        {
            $styles[] = Uri::url(
                "$file_path",
                array("v" => $theme_info["version"])
            );
        }
    }

    if(System::getUserBrowser() == "ie")
    {
        if(file_exists($theme_dir . "/" . "ie"))
        {
            if(file_exists("$theme_dir/ie/all.css"))
            {
                $styles[] = "$theme_dir/ie/all.css";
            }

            // Load specific css file for current ie version if available
            preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
            $version = floor($matches[1]);

            if(file_exists("$theme_dir/ie/$version.css"))
            {
                $styles[] = "$theme_dir/ie/$version.css";
            }
        }
    }

    if(count($styles) > 0)
    {
        foreach($styles as $file)
        {
            $styles_code .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
        }
    }

    Modules::hook("hook_theme_styles", $styles, $styles_code);

    if(self::$additional_styles_code)
    {
        $styles_code .= "<style>\n"
            . rtrim(self::$additional_styles_code, "\n") . "\n"
            . "</style>\n"
        ;
    }

    return $styles_code;
}

/**
 * Generate the html code to insert system java scripts on pages.
 *
 * @param array $scripts An array of scripts files.
 *
 * @return string Html code for the head section of document.
 */
static function getScriptsHTML(array $scripts): string
{
    $theme = Site::$theme;
    $page_data = Site::$page_data;;

    $scripts_code = "";
    $theme_dir = rtrim(Themes::directory($theme), "/");
    $theme_info = Themes::get($theme);
    $js_dir = $theme_dir . "/js";
    $js_files = array();

    $exclude_list = array(".", "..");

    if(is_dir($js_dir))
    {
        $js_files = array_diff(scandir($js_dir), $exclude_list);
        sort($js_files);
    }

    foreach($js_files as $file)
    {
        $file_path = "";

        if(is_file("$js_dir/$file"))
        {
            $file_path = "$js_dir/$file";
        }

        $file_array = explode(".", $file_path);
        $extension = $file_array[count($file_array) - 1];

        if($extension == "js")
        {
            $scripts[] = Uri::url(
                "$file_path",
                array("v" => $theme_info["version"])
            );
        }
    }

    if(count($scripts) > 0)
    {
        foreach($scripts as $file)
        {
            $scripts_code .= "<script type=\"text/javascript\" src=\"$file\"></script>\n";
        }
    }

    Modules::hook("hook_theme_scripts", $scripts, $scripts_code);

    // Generate javascript that counts the page view.
    if(
        !isset($page_data[0]["is_system"]) &&
        !Authentication::isAdminLogged() &&
        !Settings::get("classic_views_count", "main")
    )
    {
        $page_count_url = Uri::url("api/pages");
        $page_uri = Uri::get();

        $scripts_code .= <<<SCRIPT
<script>
$(document).ready(function(){
    $.get(
        "$page_count_url",
        {
            action: "count_view",
            uri: "$page_uri"
        },
        null,
        "json"
    );
});
</script>

SCRIPT;
    }

    if(self::$additional_scripts_code)
    {
        $scripts_code .= "<script>\n"
            . rtrim(self::$additional_scripts_code, "\n") . "\n"
            . "</script>\n"
        ;
    }

    return $scripts_code;
}

/**
 * Generate the html code for the tabs.
 *
 * @param array $tabs_array Tabs in the format: array["tab_name"] = "url"
 *
 * @return string Html code ready to render or empty string.
 */
static function getTabsHTML(array $tabs_array): string
{
    //Call theme_tabs hook before proccessing the array
    Modules::hook("hook_theme_tabs", $tabs_array);

    $tabs = "";

    if(count($tabs_array) > 0)
    {
        foreach($tabs_array as $position => $fields)
        {
            $tabs .= "<ul class=\"tabs tabs-$position\">\n";

            $total_tabs = count($fields);
            $index = 0;

            if(is_array($fields))
            {
                foreach($fields as $name => $uri)
                {
                    $list_class = "";
                    if($index == 0)
                    {
                        $list_class = " class=\"first\" ";
                    }
                    else if($index + 1 == $total_tabs)
                    {
                        $list_class = " class=\"last\" ";
                    }

                    $url = Uri::url($uri['uri'], $uri['arguments']);

                    if($uri["uri"] == Uri::get())
                    {
                        $tabs .= "\t<li{$list_class}><span><a class=\"selected\" href=\"$url\">$name</a></span></li>\n";
                    }
                    else
                    {
                        $tabs .= "\t<li{$list_class}><span><a href=\"$url\">$name</a></span></li>\n";
                    }
                }
            }

            $tabs .= "</ul>\n";

            $tabs .= "<div class=\"clear tabs-clear\"></div>\n";
        }
    }

    return $tabs;
}

/**
 * Generates the html code for the messages.
 *
 * @return string html code ready to render or empty string.
 */
static function getMessagesHTML(): string
{
    if(!Session::exists())
    {
        return "";
    }

    Session::start();

    if(isset($_SESSION["messages"]))
    {
        $messages_array = $_SESSION["messages"];
        unset($_SESSION["messages"]);
    }
    else
    {
        $messages_array = array();
    }

    $messages = "";

    $marker = "";
    $separator = "";
    if(count($messages_array) > 1)
    {
        $marker = "* ";
        $separator = "<br />\n";
    }

    foreach($messages_array as $message)
    {

        $messages .= $marker;

        if($message["type"] == "error")
        {
            $messages .= "<span class=\"error\">\n" . t("error:") . " ";
        }

        $messages .= $message["text"] . $separator . "\n";

        if($message["type"] == "error")
        {
            $messages .= "</span>\n";
        }
    }

    Session::destroyIfEmpty();

    return $messages;
}

/**
 * Final function on the theme system that procceses all the data and displays the page.
 *
 * @param string $page The page uri that is going to be displayed.
 * @param array $page_data
 * @param string $content The html content output by theme_content.
 * @param string $left The left block of the page proccesed by get_block.
 * @param string $center The center block of the page proccesed by get_block.
 * @param string $right The right block of the page proccesed by get_block.
 * @param string $header The header block of the page proccesed by get_block.
 * @param string $footer The footer block of the page proccesed by get_block.
 *
 * @return string The whole html output of the page.
 */
static function render(
    string $page,
    array $page_data,
    string $content,
    string $left,
    string $center,
    string $right,
    string $header,
    string $footer
): string
{
    $title = Site::$title;
    $primary_links = Site::$primary_links;
    $secondary_links = Site::$secondary_links;
    $base_url = Site::$base_url;
    $theme = Site::$theme;
    $theme_path = Site::$theme_path;
    $theme_settings = self::$theme_settings;
    $slogan = Site::$slogan;
    $footer_message = Site::$footer_message;
    $content_title = self::$content_title;
    $tabs_list = self::$tabs_list;

    $site_title = Settings::get("title", "main");
    $footer_message = System::evalPHP($footer_message);
    $slogan = System::evalPHP($slogan);
    $meta = self::getMetaTagsHTML($page_data);
    $breadcrumb = "";
    if(Settings::get("breadcrumbs", "main"))
    {
        $breadcrumb = System::generateBreadcrumb();
    }
    $tabs = self::getTabsHTML($tabs_list);
    $messages = self::getMessagesHTML();
    $styles = self::getStylesHTML(System::getStyles());
    $scripts = self::getScriptsHTML(System::getScripts());
    $header_info = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";

    //Call theme_display hook before printing the page
    Modules::hook("hook_theme_display", $page);

    $html = "";

    ob_start();
    //This is a file that where user can create custom code for the template
    if(file_exists(Themes::directory($theme) . "functions.php"))
    {
        include(Themes::directory($theme) . "functions.php");
    }

    include(self::pageTemplate($page));

    $html = ob_get_contents();
    ob_end_clean();

    Modules::hook("hook_theme_render", $page, $html);

    return $html;
}

/**
 * Search for the best block template match
 *
 * @param string $position The position of the block: left, right, center, header or footer
 * @param string $page The page uri where the block is going to be displayed.
 * @param int $id The current id of the block
 *
 * @return string The block file to be used.
 *   It could be one of the followings in the same precedence:
 *      themes/theme/block-page.php
 *      themes/theme/block-position.php
 *      themes/theme/block.php
 */
static function blockTemplate(string $position, string $page, int $id): string
{
    $theme = Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_id = Themes::directory($theme) . "block-" . $position . "-" . $id . ".php";
    $current_page = Themes::directory($theme) . "block-" . $page . ".php";
    $current_page_position = Themes::directory($theme) . "block-" . $page . "-" . $position . ".php";
    $position_page = Themes::directory($theme) . "block-" . $position . ".php";
    $default_block = Themes::directory($theme) . "block.php";

    $template_path = "";

    if(file_exists($current_id))
    {
        $template_path = $current_id;
    }
    else if(file_exists($current_page_position))
    {
        $template_path = $current_page_position;
    }
    else if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    else if(file_exists($position_page))
    {
        $template_path = $position_page;
    }
    else
    {
        $template_path = $default_block;
    }

    if($id == "")
    {
        $id = "0";
    }

    //Call block_template hook before returning the template to use
    Modules::hook("hook_block_template", $position, $page, $id, $template_path);

    return $template_path;
}

/**
 * Search for the best content block template match
 *
 * @param string $position The position of the block: left, right, center, header or footer
 * @param string $page The page uri where the block is going to be displayed.
 * @param string $page_type The page type.
 * @param int $id The current id of the block
 *
 * @return string The block file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/content-block-page.php
 *      themes/theme/content-block-position.php
 *      themes/theme/content-block.php
 */
static function contentBlockTemplate(
    string $position, string $page, string $page_type, int $id
): string
{
    $theme = Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_id = Themes::directory($theme) . "block-content-" . $position . "-" . $id . ".php";
    $current_page_position = Themes::directory($theme) . "block-content-" . $page . "-" . $position . ".php";
    $current_page = Themes::directory($theme) . "block-content-" . $page . ".php";
    $current_page_type = Themes::directory($theme) . "block-content-" . $page_type . ".php";
    $position_page = Themes::directory($theme) . "block-content-" . $position . ".php";
    $default_block = Themes::directory($theme) . "block-content.php";

    $template_path = "";

    if(file_exists($current_id))
    {
        $template_path = $current_id;
    }
    else if(file_exists($current_page_position))
    {
        $template_path = $current_page_position;
    }
    elseif(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($current_page_type))
    {
        $template_path = $current_page_type;
    }
    else if(file_exists($position_page))
    {
        $template_path = $position_page;
    }
    else
    {
        $template_path = $default_block;
    }

    //Call content_block_template hook before returning the template to use
    Modules::hook(
        "hook_content_block_template",
        $position,
        $page,
        $template_path
    );

    return $template_path;
}

/**
 * Search for the best page template match.
 *
 * @param string $page The page uri.
 *
 * @return string The page file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/page-uri.php
 *      themes/theme/page.php
 */
static function pageTemplate(string $page): string
{
    $theme = Site::$theme;
    $page = str_replace("/", "-", $page);
    $segments = explode("-", $page);

    $one_less_section = "";

    if(count($segments) > 1)
    {
        for($i = 0; $i < (count($segments) - 1); $i++)
        {
            $one_less_section .= $segments[$i] . "-";
        }
    }

    $globa_sections_page = Themes::directory($theme) . "page-" . $one_less_section . ".php";
    $current_page = Themes::directory($theme) . "page-" . $page . ".php";
    $default_page = Themes::directory($theme) . "page.php";

    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    else if($one_less_section && file_exists($globa_sections_page))
    {
        $template_path = $globa_sections_page;
    }
    else
    {
        $template_path = $default_page;
    }

    //Call page_template hook before returning the template to use
    Modules::hook("hook_page_template", $page, $template_path);

    return $template_path;
}

/**
 * Search for the best content template match
 *
 * @param string $page The page uri that is going to be displayed.
 * @param string $type The page type machine name.
 *
 * @return string The page file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/content-uri.php
 *      themes/theme/content-type.php
 *      themes/theme/content.php
 */
static function contentTemplate(string $page, string $type): string
{
    $theme = Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_page = Themes::directory($theme) . "content-" . $page . ".php";
    $content_type = Themes::directory($theme) . "content-" . $type . ".php";
    $default_page = Themes::directory($theme) . "content.php";

    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($content_type))
    {
        $template_path = $content_type;
    }
    else
    {
        $template_path = $default_page;
    }

    //Call content_template hook before returning the template to use
    Modules::hook("hook_content_template", $page, $type, $template_path);

    return $template_path;
}

/**
 * Search for the best user profile template match
 *
 * @param string $group The users group.
 * @param string $username The users system username.
 *
 * @return string The user profile template file to be used.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/user-profile-username-username.php
 *      themes/theme/user-profile-group.php
 *      themes/theme/user-profile.php
 */
static function userProfileTemplate(string $group, string $username): string
{
    $theme = Site::$theme;

    $username_profile = Themes::directory($theme) . "user-profile-username-" . $username . ".php";
    $group_profile = Themes::directory($theme) . "user-profile-" . $group . ".php";
    $default_template = Themes::directory($theme) . "user-profile.php";

    $template_path = "";

    if(file_exists($username_profile))
    {
        $template_path = $username_profile;
    }
    elseif(file_exists($group_profile))
    {
        $template_path = $group_profile;
    }
    else
    {
        $template_path = $default_template;
    }

    //Call content_template hook before returning the template to use
    Modules::hook("hook_user_profile_template", $group, $username, $template_path);

    return $template_path;
}

/**
 * Search for the best search template match
 *
 * @param string $page The uri of the search page.
 * @param string $results_type The type of results displayed.
 * @param string $template_type The type of template to get, can be: result, header, footer.
 *
 * @return string The block file to be used or false if no template was found.
 *  It could be one of the followings in the same precedence:
 *      themes/theme/search-result-page.php
 *      themes/theme/search-result-type.php
 *      themes/theme/content-block.php
 */
static function searchTemplate(
    string $page, string $results_type="all", string $template_type="result"
): string
{
    $theme = Site::$theme;
    $page = str_replace("/", "-", $page);

    $current_template = Themes::directory($theme) . "search-$template_type.php";
    $current_page = Themes::directory($theme) . "search-$template_type-" . $page . ".php";
    $current_results_type = Themes::directory($theme) . "search-$template_type-" . $results_type . ".php";

    $template_path = "";

    if(file_exists($current_template))
    {
        $template_path = $current_template;
    }
    elseif(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($current_results_type))
    {
        $template_path = $current_results_type;
    }
    else
    {
        $template_path = false;
    }

    //Call content_block_template hook before returning the template to use
    Modules::hook("hook_search_template", $page, $results_type, $template_type, $template_path);

    return $template_path;
}

}
