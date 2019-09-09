<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
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
 */
static function contentPreview(
    string $string,
    int $word_count=30,
    bool $display_suspensive_points = false
): string
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
 *
 * @param string $text
 * @param ?string $allowed_tags Example: "<a><b><strong><u>"
 * @param string $allowed_atts Example: "href,a,alt"
 *
 * @return string
 */
static function stripHTMLTags(
    ?string $text, string $allowed_tags="", string $allowed_atts=""
): string
{
    if($text == null)
    {
        return "";
    }

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
        $allowed_atts_array[0] != "" &&
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
            return str_replace("\n", "<br>", strip_tags($text, ''));
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
 */
static function directoryBrowser(string $directory): array
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
 */
static function generateBrowserNavigation(
    array $directories, string $main_dir
): array
{
    $navigation = array();

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

/**
 * Parses a user agent string into its important parts
 *
 * @author Jesse G. Donat <donatj@gmail.com>
 * @link https://github.com/donatj/PhpUserAgent
 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
 * @throws \InvalidArgumentException on not having a proper user agent to parse.
 *
 * @return array an array with browser, version and platform keys
 */
static function parseUserAgent(?string $u_agent = null): array {
    if( is_null($u_agent) ) {
        if( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            throw new \InvalidArgumentException('parse_user_agent requires a user agent');
        }
    }

    $platform = null;
    $browser  = null;
    $version  = null;

    $empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

    if( !$u_agent ) return $empty;

    if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {
        preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
                (?:\ [^;]*)?
                (?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

        $priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'FreeBSD', 'NetBSD', 'OpenBSD', 'CrOS', 'X11' );

        $result['platform'] = array_unique($result['platform']);
        if( count($result['platform']) > 1 ) {
            if( $keys = array_intersect($priority, $result['platform']) ) {
                $platform = reset($keys);
            } else {
                $platform = $result['platform'][0];
            }
        } elseif( isset($result['platform'][0]) ) {
            $platform = $result['platform'][0];
        }
    }

    if( $platform == 'linux-gnu' || $platform == 'X11' ) {
        $platform = 'Linux';
    } elseif( $platform == 'CrOS' ) {
        $platform = 'Chrome OS';
    }

    preg_match_all('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
                TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
                Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
                Valve\ Steam\ Tenfoot|
                NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
                (?:\)?;?)
                (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
        $u_agent, $result, PREG_PATTERN_ORDER);

    // If nothing matched, return null (to avoid undefined index errors)
    if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
        if( preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result) ) {
            return array( 'platform' => $platform ?: null, 'browser' => $result['browser'], 'version' => isset($result['version']) ? $result['version'] ?: null : null );
        }

        return $empty;
    }

    if( preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result) ) {
        $rv_result = $rv_result['version'];
    }

    $browser = $result['browser'][0];
    $version = $result['version'][0];

    $lowerBrowser = array_map('strtolower', $result['browser']);

    $find = function ( $search, &$key, &$value = null ) use ( $lowerBrowser ) {
        $search = (array)$search;

        foreach( $search as $val ) {
            $xkey = array_search(strtolower($val), $lowerBrowser);
            if( $xkey !== false ) {
                $value = $val;
                $key   = $xkey;

                return true;
            }
        }

        return false;
    };

    $key = 0;
    $val = '';
    if( $browser == 'Iceweasel' || strtolower($browser) == 'icecat' ) {
        $browser = 'Firefox';
    } elseif( $find('Playstation Vita', $key) ) {
        $platform = 'PlayStation Vita';
        $browser  = 'Browser';
    } elseif( $find(array( 'Kindle Fire', 'Silk' ), $key, $val) ) {
        $browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
        $platform = 'Kindle Fire';
        if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
            $version = $result['version'][array_search('Version', $result['browser'])];
        }
    } elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
        $browser = 'NintendoBrowser';
        $version = $result['version'][$key];
    } elseif( $find('Kindle', $key, $platform) ) {
        $browser = $result['browser'][$key];
        $version = $result['version'][$key];
    } elseif( $find('OPR', $key) ) {
        $browser = 'Opera Next';
        $version = $result['version'][$key];
    } elseif( $find('Opera', $key, $browser) ) {
        $find('Version', $key);
        $version = $result['version'][$key];
    } elseif( $find('Puffin', $key, $browser) ) {
        $version = $result['version'][$key];
        if( strlen($version) > 3 ) {
            $part = substr($version, -2);
            if( ctype_upper($part) ) {
                $version = substr($version, 0, -2);

                $flags = array( 'IP' => 'iPhone', 'IT' => 'iPad', 'AP' => 'Android', 'AT' => 'Android', 'WP' => 'Windows Phone', 'WT' => 'Windows' );
                if( isset($flags[$part]) ) {
                    $platform = $flags[$part];
                }
            }
        }
    } elseif( $find(array( 'IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome' ), $key, $browser) ) {
        $version = $result['version'][$key];
    } elseif( $rv_result && $find('Trident', $key) ) {
        $browser = 'MSIE';
        $version = $rv_result;
    } elseif( $find('UCBrowser', $key) ) {
        $browser = 'UC Browser';
        $version = $result['version'][$key];
    } elseif( $find('CriOS', $key) ) {
        $browser = 'Chrome';
        $version = $result['version'][$key];
    } elseif( $browser == 'AppleWebKit' ) {
        if( $platform == 'Android' && !($key = 0) ) {
            $browser = 'Android Browser';
        } elseif( strpos($platform, 'BB') === 0 ) {
            $browser  = 'BlackBerry Browser';
            $platform = 'BlackBerry';
        } elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
            $browser = 'BlackBerry Browser';
        } else {
            $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
        }

        $find('Version', $key);
        $version = $result['version'][$key];
    } elseif( $pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser'])) ) {
        $pKey = reset($pKey);

        $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
        $browser  = 'NetFront';
    }

    return array( 'platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null );
}

}