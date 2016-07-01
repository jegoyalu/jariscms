<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Class to inject text search functions to sqlite as some other misc.
 * @original jaris_sqlite_search
 */
class SearchAddons
{

    /**
     * Cache list of keywords.
     * @var array
     */
    private $keywords;

    /**
     * Count of keywords found per result.
     * @var array
     */
    private $keywords_count;

    /**
     * String of keywords as input by user.
     * @var string
     */
    private $keywords_string;

    /**
     * Cache list of categories.
     * @var array
     */
    private $categories_to_check;

    /**
     * Flag that indicates if categories are set.
     * @var bool
     */
    private $categories_to_check_set;

    /**
     * Flag that indicates if categories are empty.
     * @var bool
     */
    private $categories_empty;

    /**
     * Initialize the class for use on a opened database.
     *
     * @param resource|object $db Handle to opened database object.
     */
    public function __construct(&$db)
    {
        $this->keywords = array();
        $this->keywords_count = array();

        $this->categories_to_check = array();
        $this->categories_to_check_set = false;
        $this->categories_empty = false;


        if(gettype($db) == "object" && class_exists("SQLite3"))
        {
            $db->createFunction("normalsearch", array(&$this, 'normal_text_search'), 2);
            $db->createFunction("leftsearch", array(&$this, 'left_text_search'), 2);
            $db->createFunction("dateformat", array(&$this, 'date_format'), 2);
            $db->createFunction("hascategories", array(&$this, 'has_categories'), 2);
            $db->createFunction("hassomecategories", array(&$this, 'has_some_categories'), 2);
            $db->createFunction("haspermission", array(&$this, 'has_permission'), 2);
            $db->createFunction("hasuserpermission", array(&$this, 'has_user_permission'), 2);
        }
        elseif(gettype($db) == "object")
        {
            $db->sqliteCreateFunction("normalsearch", array(&$this, 'normal_text_search'), 2);
            $db->sqliteCreateFunction("leftsearch", array(&$this, 'left_text_search'), 2);
            $db->sqliteCreateFunction("dateformat", array(&$this, 'date_format'), 2);
            $db->sqliteCreateFunction("hascategories", array(&$this, 'has_categories'), 2);
            $db->sqliteCreateFunction("hassomecategories", array(&$this, 'has_some_categories'), 2);
            $db->sqliteCreateFunction("haspermission", array(&$this, 'has_permission'), 2);
            $db->sqliteCreateFunction("hasuserpermission", array(&$this, 'has_user_permission'), 2);
        }
        else
        {
            sqlite_create_function($db, "normalsearch", array(&$this, 'normal_text_search'), 2);
            sqlite_create_function($db, "leftsearch", array(&$this, 'left_text_search'), 2);
            sqlite_create_function($db, "dateformat", array(&$this, 'date_format'), 2);
            sqlite_create_function($db, "hascategories", array(&$this, 'has_categories'), 2);
            sqlite_create_function($db, "hassomecategories", array(&$this, 'has_some_categories'), 2);
            sqlite_create_function($db, "haspermission", array(&$this, 'has_permission'), 2);
            sqlite_create_function($db, "hasuserpermission", array(&$this, 'has_user_permission'), 2);
        }
    }

    /**
     * Converts keywords or input text to search to array of words,
     * count it and saves keywords to cache.
     *
     * @param string $text The text to convert.
     * @param bool $keywords True to indicate if input text is user
     * keywords or false otherwise.
     *
     * @return array Array in the format array("words"=>array(), "count"=>number)
     */
    private function text_to_array($text, $keywords = false)
    {
        $original_text = $text;

        if($keywords)
        {
            if(isset($this->keywords[$text]))
            {
                return array(
                    "words" => $this->keywords[$text],
                    "count" => $this->keywords_count[$text],
                    "text" => $this->keywords_string[$text]
                );
            }
        }

        /* Since this slows down the search we already do it when storing the page on database
          $text = strip_html_tags($text);

          //Substitute some characters with spaces to improve search quality
          $text = str_replace(array(".", ",", "'", "\"", "(", ")"), " ", $text);

          //Remove repeated whitespaces
          $text = preg_replace('/\s\s+/', ' ', $text);

          $text = strtolower($text);
         */

        $words = explode(" ", $text);
        $count = count($words);

        if($keywords)
        {
            if(!$this->keywords[$original_text])
            {
                $text = strtolower($text);
                $this->keywords_string[$original_text] = $text;
                $this->keywords[$original_text] = $words;
                $this->keywords_count[$original_text] = $count;
            }
        }

        return array("words" => $words, "count" => $count, "text" => $text);
    }

    /**
     * Gets a number representing the percent of the keywords on the text to search.
     *
     * @param array $text An array of the words to search returned by text_to_array.
     * @param array $keywords An array of the keywords returned by text_to_array.
     *
     * @return float The keywords density on the text.
     */
    private function get_matching_percent($text, $keywords)
    {
        $matching_sum = 0;
        foreach($keywords["words"] as $keyword)
        {
            foreach($text["words"] as $position => $text_word)
            {
                $position += 1;
                if($text_word == $keyword)
                {
                    $matching_sum += ($position * $position) / $text["count"];
                }
            }
        }

        $divisor = ($text["count"] + 1) * ($text["count"] / 2);

        return $matching_sum / $divisor;
    }

    /**
     * To perform a normal text search.
     *
     * @param string $text The haystack.
     * @param string $keywords The needle.
     * @param string $input_format The text format.
     *
     * @return float The matching percent of needles in the haystack.
     */
    public function normal_text_search($text, $keywords, $input_format = "php_code")
    {
        /* Slowers down search
          $text = filter_data($text, $input_format);
         */

        if(strlen($text) <= 0)
        {
            return 0;
        }

        $matching_percent = 0;

        $keywords = $this->text_to_array($keywords, true);
        $keywords_count = $keywords["count"];
        $keywords = $keywords["words"];

        $keywords_string = "";

        for($i = 0; $i < $keywords_count; $i++)
        {
            $keywords_string .= $keywords[$i] . " ";

            $substring_count = substr_count($text, $keywords_string);

            if($substring_count > 0)
            {
                $matching_percent += ($substring_count * ($i + 1));
            }
        }

        return $matching_percent;
    }

    /**
     * Perform a text search with priority to the starting words.
     *
     * @param string $text The haystack.
     * @param string $keywords The needle.
     * @param string $input_format The text format.
     *
     * @return float Matching percent.
     */
    public function left_text_search($text, $keywords, $input_format = "php_code")
    {
        /* Slowers down search
          $text = filter_data($text, $input_format);
         */

        if(strlen($text) <= 0)
        {
            return 0;
        }

        $keywords = $this->text_to_array($keywords, true);

        $keyword_count = $keywords["count"];
        $keywords = $keywords["words"];

        $long_word = 0;
        foreach($keywords as $word)
        {
            $len = strlen($word);
            if($len > $long_word)
            {
                $long_word = $len;
            }
        }

        for($i = $keyword_count - 1; $i >= 0; $i--)
        {
            $keywords_array = array();
            for($y = 0; $y <= $i; $y++)
            {
                $keywords_array[] = $keywords[$y];
            }

            $keywords_string = implode(" ", $keywords_array);

            $len = strlen($keywords_string);
            if($len > 1 && $len >= $long_word)
            {
                //First search for exact matches on title
                if("" . stripos($text, $keywords_string) . "" != "")
                {
                    return $i + $keyword_count;
                }
            }
        }

        return 0;
    }

    /**
     * To format a given timestamp.
     *
     * @param int $timestamp A given time stamp to format.
     * @param string $format The format options wanted as output for
     * the given time stamp.
     *
     * @return string A formatted time stamp.
     */
    public function date_format($timestamp, $format)
    {
        return date($format, $timestamp);
    }

    /**
     * Used for the search_engine database to check if a given content
     * has a given categories.
     *
     * @param string $categories The serialized categories stored on
     * search_engine database.
     * @param string $categories_to_check_input A serialized categories array
     * to compare against the stored categories.
     *
     * @return int 1 on true 0 on false.
     */
    public function has_categories($categories, $categories_to_check_input)
    {
        if(!$this->categories_to_check_set)
        {
            $this->categories_to_check = unserialize($categories_to_check_input);
            $this->categories_to_check_set = true;

            //Remove categories where no subcategory was selected
            $categories_to_check_copy = $this->categories_to_check;
            foreach($categories_to_check_copy as $machine_name => $sub_categories)
            {
                if(count($sub_categories) == 1)
                {
                    if($sub_categories[0] == "-1")
                    {
                        unset($this->categories_to_check[$machine_name]);
                    }
                }
            }

            //Look if a category was selected
            $category_selected = false;

            foreach($this->categories_to_check as $machine_name => $sub_categories)
            {
                if(count($sub_categories) > 1)
                {
                    foreach($sub_categories as $subcategory_id)
                    {
                        if($subcategory_id != "-1")
                        {
                            $category_selected = true;
                            break 2;
                        }
                    }
                }
                else
                {
                    if($sub_categories[0] != "-1")
                    {
                        $category_selected = true;
                        break;
                    }
                }
            }

            //If no category selected return 1 and dont check anymore for
            //categories just return 1
            if(!$category_selected)
            {
                $this->categories_empty = true;
                return 1;
            }
        }

        if(!$this->categories_empty)
        {
            $found_all_categories = 1;

            $categories = unserialize($categories);

            if(!is_array($categories) || count($categories) < 1)
                return 0;

            foreach($this->categories_to_check as $machine_name => $sub_categories)
            {
                if(isset($categories[$machine_name]))
                {
                    if(count($sub_categories) > 1)
                    {
                        foreach($sub_categories as $subcategory_id)
                        {
                            if(!in_array($subcategory_id, $categories[$machine_name]))
                            {
                                $found_all_categories = 0;
                                break 2;
                            }
                        }
                    }
                    else
                    {
                        if(!in_array($sub_categories[0], $categories[$machine_name]))
                        {
                            $found_all_categories = 0;
                            break;
                        }
                    }
                }
                else
                {
                    $found_all_categories = 0;
                    break;
                }
            }

            return $found_all_categories;
        }

        return 1;
    }

    /**
     * Used for the search_engine database to check if a given content
     * has some of the given categories.
     *
     * @param string $categories The serialized categories stored on
     * search_engine database.
     * @param string $categories_to_check_input A serialized categories array
     * to compare against the stored categories.
     *
     * @return int 1 on true 0 on false.
     */
    public function has_some_categories($categories, $categories_to_check_input)
    {
        if(!$this->categories_to_check_set)
        {
            $this->categories_to_check = unserialize($categories_to_check_input);
            $this->categories_to_check_set = true;

            //Remove categories where no subcategory was selected
            $categories_to_check_copy = $this->categories_to_check;
            foreach($categories_to_check_copy as $machine_name => $sub_categories)
            {
                if(count($sub_categories) == 1)
                {
                    if($sub_categories[0] == "-1")
                    {
                        unset($this->categories_to_check[$machine_name]);
                    }
                }
            }

            //Look if a category was selected
            $category_selected = false;

            foreach($this->categories_to_check as $machine_name => $sub_categories)
            {
                if(count($sub_categories) > 1)
                {
                    foreach($sub_categories as $subcategory_id)
                    {
                        if($subcategory_id != "-1")
                        {
                            $category_selected = true;
                            break 2;
                        }
                    }
                }
                else
                {
                    if($sub_categories[0] != "-1")
                    {
                        $category_selected = true;
                        break;
                    }
                }
            }

            //If no category selected return 1 and dont check anymore for
            //categories just return 1
            if(!$category_selected)
            {
                $this->categories_empty = true;
                return 1;
            }
        }

        if(!$this->categories_empty)
        {
            $categories = unserialize($categories);

            if(!is_array($categories) || count($categories) < 1)
                return 0;

            foreach($this->categories_to_check as $machine_name => $sub_categories)
            {
                if(isset($categories[$machine_name]))
                {
                    if(count($sub_categories) > 1)
                    {
                        foreach($sub_categories as $subcategory_id)
                        {
                            if(in_array($subcategory_id, $categories[$machine_name]))
                            {
                                return 1;
                            }
                        }
                    }
                    else
                    {
                        if(in_array($sub_categories[0], $categories[$machine_name]))
                        {
                            return 1;
                        }
                    }
                }
            }

            return 0;
        }

        return 1;
    }

    /**
     * Used on the search_engine database to check if a current group
     * has permission to view a content.
     *
     * @param string $groups The column of groups stored and serialized on
     * the search engine database.
     * @param string $current_group The current group to check if has permissions.
     *
     * @return int 1 if has permission 0 if doesn't.
     */
    public function has_permission($groups, $current_group)
    {
        //Groups is null or groups array is empty
        if($groups == "N;" || $groups == "a:0:{}")
        {
            return 1;
        }

        if("" . strpos($groups, '"' . $current_group . '"') . "" != "")
        {
            return 1;
        }

        return 0;
    }

    /**
     * Used on the search_engine database to check if a current user
     * has permission to view a content.
     *
     * @param string $users The column of users stored and serialized on
     * the search engine database.
     * @param string $current_user The current user to check if has permissions.
     *
     * @return int 1 if has permission 0 if doesn't.
     */
    public function has_user_permission($users, $current_user)
    {
        //Groups is null, false or groups array is empty
        if(
            $users == "N;" || $users == "b:0;" ||
            $users == "a:0:{}" || $users == 'a:1:{i:0;s:0:"";}'
        )
        {
            return 1;
        }

        if("" . strpos($users, '"' . $current_user . '"') . "" != "")
        {
            return 1;
        }

        return 0;
    }

}

?>