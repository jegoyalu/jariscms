<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 General License Protecting Programmers
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Some utility functions.
 */
class Util
{

/**
 * To generate just part of a string and strip its html tags.
 *
 * @param string $string The string to print.
 * @param int $word_count The amount of words to print of it.
 * @param bool $display_suspensive_points Flag to display 3 dots on
 * the end of preview.
 *
 * @return string The trimmed string.
 * @original print_content_preview
 */
static function contentPreview(
    $string, $word_count = 30, $display_suspensive_points = false
)
{
    $string = self::stripHTMLTags($string);

    $string = preg_replace("/(&nbsp;)*/i", "", $string);
    $string = str_replace("<br />", "\n", $string);

    $string_array = explode(" ", $string);

    $string_count = count($string_array);

    $string = "";

    for($i = 0; $i < $word_count && $i <= $string_count; $i++)
    {
        $string .= $string_array[$i] . " ";
    }

    $string = trim($string);

    //If last character is not a point add points to it.
    if(
        $display_suspensive_points &&
        $string != "" &&
        $string{strlen($string) - 1} != "."
    )
    {
        $string .= " ...";
    }

    return $string;
}

/**
 * Cleans html to make it more secure.
 * @param string $text
 * @param string $allowed_tags Example: "<a><b><strong><u>"
 * @param string $allowed_atts Example: "href,a,alt"
 * @return string
 * @original strip_html_tags
 */
static function stripHTMLTags($text, $allowed_tags = "", $allowed_atts="")
{
    //Allow object and embed
    if("" . stripos($allowed_tags, "object") . "" != "" ||
        "" . stripos($allowed_tags, "embed") . "" != ""
    )
    {
        $text = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                // Add line breaks before & after blocks
                '@<((br)|(hr))@iu',
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu'
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0"
            ),
            $text
        );
    }
    // PHP's strip_tags() function will remove tags, but it
    // doesn't remove scripts, styles, and other unwanted
    // invisible text between tags.  Also, as a prelude to
    // tokenizing the text, we need to insure that when
    // block-level tags (such as <p> or <div>) are removed,
    // neighboring words aren't joined.
    else
    {
        $text = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
                // Add line breaks before & after blocks
                '@<((br)|(hr))@iu',
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu'
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0"
            ),
            $text
        );
    }

    $allowed_atts_array = explode(",", $allowed_atts);

    if(
        count($allowed_atts_array) > 0 &&
        function_exists("simplexml_load_string")
    )
    {
        // Trim array elements
        $allowed_atts_array = array_map('trim', $allowed_atts_array);

        // Remove attributes
        $text_xml = simplexml_load_string(
            '<root>'. $text .'</root>',
            'SimpleXMLElement',
            LIBXML_NOERROR | LIBXML_NOXMLDECL
        );

        // strip collector
        $strip_arr = array();

        if($text_xml)
        {
            // loop all elements with an attribute
            foreach($text_xml->xpath('descendant::*[@*]') as $tag)
            {
                // loop attributes
                foreach($tag->attributes() as $name=>$value)
                {
                    // check for allowable attributes
                    if(!in_array($name, $allowed_atts_array))
                    {
                        // set attribute value to empty string
                        $tag->attributes()->$name = '';
                        // collect attribute patterns to be stripped
                        $strip_arr[$name] = '/ '. $name .'=""/';
                    }
                }
            }

            // strip unallowed attributes and root tag
            return strip_tags(
                preg_replace(
                    $strip_arr,
                    array(''),
                    $text_xml->asXML()
                ),
                $allowed_tags
            );
        }
        else
        {
            // In case simplexml fails due to invalid html
            // we strip all tags and replace newlines with breaks.
            return str_replace("\n", "<br />", strip_tags($text, ''));
        }
    }

    // Remove all remaining tags and comments and return.
    return strip_tags($text, $allowed_tags);
}

/**
 * Gets all the directories inside of a path
 *
 * @param string $directory Main path to scan for directories.
 *
 * @return array Directories found in the format:
 * directories[] = array("main"=>"main_path", "dir"=>"directory_name")
 * @original directory_browser
 */
static function directoryBrowser($directory)
{
    $main_dir = $directory . "/";
    $dir_handle = opendir($main_dir);

    $dir_array = array();

    while(($file = readdir($dir_handle)) !== false)
    {
        //just add directories inside the file
        if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
        {
            if(is_dir($main_dir . $file))
            {
                $dir_array[] = array("main" => $main_dir, "dir" => $file);
            }
        }
    }

    return Data::sort($dir_array, "dir");
}

/**
 * Generates an array suitable to create a page navigation menus.
 *
 * @param array $directories Directories to classify.
 * @param string $main_dir The main directory where
 * data resides, for example: data/pages
 *
 * @return array page navigation in the format
 * array[] = array(
 *  "type"=>"page, alphabet or section",
 *  "path"=>"path to destination relative to data/pages/"
 * )
 *
 * @see directory_browser()
 * @original generate_browser_navigation
 */
static function generateBrowserNavigation($directories, $main_dir)
{
    foreach($directories as $directory)
    {
        $full_path = $directory["main"] . $directory["dir"];
        $relative_path = str_replace($main_dir . "/", "", $full_path);

        if(file_exists($full_path . "/data.php"))
        {
            $navigation[] = array(
                "type" => "page",
                "path" => $relative_path,
                "current" => $directory["dir"]
            );
        }
        elseif(strlen($directory["dir"]) < 3)
        {
            $navigation[] = array(
                "type" => "alphabet",
                "path" => $relative_path,
                "current" => $directory["dir"]
            );
        }
        else
        {
            $navigation[] = array(
                "type" => "section",
                "path" => $relative_path,
                "current" => $directory["dir"]
            );
        }
    }

    return $navigation;
}

}