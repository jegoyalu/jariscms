<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions to search the content.
 */
class Search
{

/**
 * Search for pages that match specific keywords.
 *
 * @param string $keywords The words to search for.
 * @param array $field_values An array of specific field with exact
 * values in the format fields["name"] = "value"
 * @param array $categories an array of categories to match the page.
 * @param int $page
 * @param int $amount
 * @original search_content
 */
static function start(
    $keywords, $field_values = null, $categories = array(),
    $page = 1, $amount = 10
)
{
    // To protect agains sql injections be sure $page is a int
    if(!is_numeric($page))
    {
        $page = 1;
    }
    else
    {
        $page = intval($page);
    }

    if(!is_numeric($amount))
    {
        $amount = 10;
    }
    else
    {
        $amount = intval($amount);
    }

    self::reset();

    if($keywords)
    {
        self::addKeywords($keywords);
    }

    self::addFields($field_values);
    self::addCategories($categories);

    //We try to use sqlite database for better speed if possible
    if(Sql::dbExists("search_engine"))
    {
        self::database($page, $amount);
    }

    //Else we search the entire pages directory
    else
    {
        FileSystem::search(
            Site::dataDir() . "pages",
            "/.*data\.php/",
            function($fullpath, $stopsearch){
                \Jaris\Search::checkContent($fullpath);
            }
        );
    }
}

/**
 * Instead of recursing the whole pages directory we open an
 * sqlite database that stores all pages data
 *
 * @param int $page Current displayed page.
 * @param int $amount The amount of results to display per page.
 * @original search_database
 */
static function database($page = 1, $amount = 10)
{
    // To protect agains sql injections be sure $page is a int
    if(!is_numeric($page))
    {
        $page = 1;
    }
    else
    {
        $page = intval($page);
    }

    if(!is_numeric($amount))
    {
        $amount = 10;
    }
    else
    {
        $amount = intval($amount);
    }

    if(Sql::dbExists("search_engine"))
    {
        $page -= 1;
        $page *= $amount;

        $db = Sql::open("search_engine");

        Sql::turbo($db);

        $type = self::contentType();

        $user = Authentication::currentUser();
        $group = Authentication::currentUserGroup();

        //Search by keywords and categories
        if(count(self::getKeywords()) > 0)
        {
            $keywords = implode(" ", self::getKeywords());
            $keywords = str_replace("'", "''", $keywords);
            $categories = serialize(self::getCategories());
            $categories = str_replace("'", "''", $categories);

            $order_clause = false;
            switch($_REQUEST["order"])
            {
                case "newest":
                    $order_clause = "order by created_date desc";
                    break;
                case "oldest":
                    $order_clause = "order by created_date desc";
                    break;
                case "title_desc":
                    $order_clause = "order by title desc";
                    break;
                case "title_asc":
                    $order_clause = "order by title asc";
                    break;
                default:
                    $order_clause = false;
                    break;
            }

            $select = "select
            leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
            normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
            hascategories(categories, '$categories') as has_category,
            haspermission(groups, '$group') as has_permissions,
            hasuserpermission(users, '$user') as has_user_permissions,
            uri from uris where
            ((title_relevancy > 0 or content_relevancy > 0 or
            description_normal > 0 or keywords_normal > 0) and
            has_category > 0 and has_permissions > 0 and
            has_user_permissions > 0 and approved='a') $type
            order by title_relevancy desc, content_relevancy desc,
            description_normal desc, keywords_normal desc limit $page, $amount";

            //Force ordering by user choice instead or relevancy
            if($order_clause != false)
            {
                $select = "select
                leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
                normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
                hascategories(categories, '$categories') as has_category,
                haspermission(groups, '$group') as has_permissions,
                hasuserpermission(users, '$user') as has_user_permissions,
                uri from uris where
                ((title_relevancy > 0 or content_relevancy > 0 or
                description_normal > 0 or keywords_normal > 0) and
                has_category > 0 and has_permissions > 0 and
                has_user_permissions > 0 and approved='a') $type
                $order_clause limit $page, $amount";
            }

            $result = Sql::query($select, $db);

            while($data = Sql::fetchArray($result))
            {
                self::addResult($data["uri"]);
            }
        }

        //Search by categories only
        else if(count(self::getCategories()) > 0)
        {
            $categories = serialize(self::getCategories());
            $categories = str_replace("'", "''", $categories);

            $order_clause = "";
            switch($_REQUEST["order"])
            {
                case "newest":
                    $order_clause = "order by created_date desc";
                    break;
                case "oldest":
                    $order_clause = "order by created_date desc";
                    break;
                case "title_desc":
                    $order_clause = "order by title desc";
                    break;
                default:
                    $order_clause = "order by title asc";
                    break;
            }

            $select = "select
            hascategories(categories, '$categories') as has_category,
            haspermission(groups, '$group') as has_permissions,
            hasuserpermission(users, '$user') as has_user_permissions,
            uri from uris where
            has_category > 0 and has_permissions > 0 and
            has_user_permissions > 0 and approved='a'
            $type $order_clause limit $page, $amount";

            $result = Sql::query($select, $db);

            while($data = Sql::fetchArray($result))
            {
                self::addResult($data["uri"]);
            }
        }

        Sql::close($db);
    }
}

/**
 * Regenerates the sqlite uri database from all the existent content on the system
 * by recursive searching the file system for content pages.
 * @original search_reindex_sqlite
 */
static function reindex()
{
    if(Sql::dbExists("search_engine"))
    {
        unlink(Site::dataDir() . "sqlite/search_engine");
    }

    //Recreate database and table
    $db = Sql::open("search_engine");

    if(!$db)
    {
        return false;
    }

    Sql::query("PRAGMA journal_mode=WAL", $db);

    Sql::query(
        "create table uris ("
        . "title text,"
        . "content text,"
        . "description text,"
        . "keywords text,"
        . "users text,"
        . "groups text,"
        . "categories text,"
        . "input_format text,"
        . "created_date text,"
        . "last_edit_date text,"
        . "last_edit_by text,"
        . "author text,"
        . "type text,"
        . "approved text,"
        . "views integer,"
        . "views_day integer,"
        . "views_day_count integer,"
        . "views_week integer,"
        . "views_week_count integer,"
        . "views_month integer,"
        . "views_month_count integer,"
        . "uri text,"
        . "data text"
        . ")",
        $db
    );

    Sql::query(
        "create index uris_index on uris ("
        . "title desc,"
        . "content desc,"
        . "description desc,"
        . "keywords desc,"
        . "categories desc,"
        . "created_date desc,"
        . "last_edit_date desc,"
        . "author desc,"
        . "type desc,"
        . "approved desc,"
        . "views desc,"
        . "views_day_count desc,"
        . "views_week_count desc,"
        . "views_month_count desc,"
        . "uri desc)",
        $db
    );

    Sql::close($db);

    FileSystem::search(
        Site::dataDir() . "pages",
        "/.*data\.php/",
        function($fullpath, $stopsearch){
            \Jaris\Search::reindexCallback($fullpath);
        }
    );

    return true;
}

/**
 * Assist on the generation of the search database.
 * @param string $content_path Path to content to index.
 * @see search_reindex_sqlite()
 * @original search_reindex_callback
 */
static function reindexCallback($content_path)
{
    //Obviate system pages from indexation using
    //black list for performance improvement
    if(System::pagesBlackList($content_path))
    {
        return;
    }

    $uri = Uri::getFromPath(
        str_replace(
            "/data.php",
            "",
            str_replace(Site::dataDir() . "pages/", "", $content_path)
        )
    );

    $page_data = Pages::get($uri);

    if($page_data["is_system"])
    {
        return;
    }

    $data = $page_data;
    $data["users"] = serialize($data["users"]);
    $data["groups"] = serialize($data["groups"]);
    $data["categories"] = serialize($data["categories"]);

    // Prepare title
    $data["title"] = Util::stripHTMLTags($data["title"]);

    //Substitute some characters with spaces to improve search quality
    $data["title"] = str_replace(
        array(".", ",", "'", "\"", "(", ")"),
        " ",
        $data["title"]
    );

    //Remove repeated whitespaces
    $data["title"] = preg_replace('/\s\s+/', ' ', $data["title"]);
    $data["title"] = strtolower($data["title"]);

    // Prepare content
    $data["content"] = Util::stripHTMLTags($data["content"]);

    //Substitute some characters with spaces to improve search quality
    $data["content"] = str_replace(
        array(".", ",", "'", "\"", "(", ")"),
        " ",
        $data["content"]
    );

    //Remove repeated whitespaces
    $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
    $data["content"] = strtolower($data["content"]);

    $data["approved"] = isset($page_data["approved"]) ?
        $page_data["approved"] : "a"
    ;

    //Get views
    $views_file = Pages::getPath($uri) . "/views.php";
    $views = Data::get(0, $views_file);

    if(!$views["views"])
    {
        $data["views"] = "0";
    }
    else
    {
        $data["views"] = $views["views"];
    }

    Sql::escapeArray($data);

    $page_data = str_replace("'", "''", serialize($page_data));

    $uri = str_replace("'", "''", $uri);

    $db = Sql::open("search_engine");
    Sql::turbo($db);

    Sql::query("insert into uris
    (title, content, description, keywords, users, groups, categories, input_format,
     created_date, last_edit_date, last_edit_by, author, type, approved, views, uri, data)

    values ('{$data['title']}', '{$data['content']}', '{$data['description']}',
    '{$data['keywords']}', '{$data['users']}', '{$data['groups']}',
    '{$data['categories']}', '{$data['input_format']}', '{$data['created_date']}',
    '{$data['last_edit_date']}', '{$data['last_edit_by']}', '{$data['author']}',
    '{$data['type']}', '{$data['approved']}', {$data['views']}, '$uri', '$page_data')",
    $db);

    Sql::close($db);
}

/**
 * Get a sql statement to select the proper content type to search.
 * @return string SQL statement
 * @original get_search_content_type
 */
static function contentType()
{
    if(trim($_REQUEST["type"]) != "")
    {
        $type = str_replace("'", "''", $_REQUEST["type"]);
        return "and type='$type'";
    }

    return "";
}

/**
 * Checks a list of keywords and fields to match
 * the content of a page database file.
 *
 * @param string $content_path The path of the database file to check.
 * @param array $content_data optional parameter to already pass
 * page data and prevent opening a page file twice.
 * @original check_content
 */
static function checkContent($content_path, $content_data = array())
{
    //Obviate system pages from search using black list for performance improvement
    if(System::pagesBlackList($content_path))
    {
        return;
    }

    $uri = Uri::getFromPath(
        str_replace(
            "/data.php",
            "",
            str_replace(Site::dataDir() . "pages/", "", $content_path)
        )
    );

    if(!$content_data)
    {
        $content_data = Pages::get($uri, Language::getCurrent());
    }

    //Skip pages marked as system in case is not specified on the blacklist
    if($content_data["is_system"])
    {
        return;
    }

    if(!Pages::userHasAccess($content_data))
    {
        return;
    }

    $fields_to_search = self::getFields();

    $all_fields_same = true;
    foreach($fields_to_search as $key => $fields)
    {
        if($fields["code"])
        {
            eval("?>" . $fields["code"]);
        }
        elseif(trim($content_data[$fields["name"]]) != $fields["value"])
        {
            $all_fields_same = false;
            break;
        }
    }

    if($all_fields_same)
    {
        //Check if the user does not selected a category
        $categories = self::getCategories();
        $all_none_selected = true;
        foreach($categories as $machine_name => $values)
        {
            foreach($categories[$machine_name] as $id => $subcategories)
            {
                if("" . $categories[$machine_name][$id] . "" != "-1")
                {
                    $all_none_selected = false;
                    break 2;
                }
            }
        }

        //Check categories that match on content if user selected a category
        $found_category_match = false;
        if(!$all_none_selected)
        {
            foreach($categories as $machine_name => $sub_categories)
            {
                if(count($sub_categories) > 1)
                {
                    if(isset($content_data["categories"][$machine_name]))
                    {
                        foreach($sub_categories as $subcategory_id)
                        {
                            foreach($content_data["categories"][$machine_name] as $content_subcategory_id)
                            {
                                if($subcategory_id == $content_subcategory_id)
                                {
                                    $found_category_match = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
                elseif($sub_categories[0] != "-1")
                {
                    if(isset($content_data["categories"][$machine_name]))
                    {
                        foreach($content_data["categories"][$machine_name] as $subcategory_id)
                        {
                            if($subcategory_id == $sub_categories[0])
                            {
                                $found_category_match = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        $keywords = self::getKeywords();

        if(count($keywords) <= 0 && count(self::getFields()) >= 1)
        {
            self::addResult($uri);
        }
        elseif(count($keywords) > 0)
        {
            if($all_none_selected || $found_category_match)
            {
                $title = strtolower($content_data["title"]);

                $content = strtolower(
                    Util::stripHTMLTags(
                        InputFormats::filter(
                            $content_data["content"],
                            $content_data["input_format"]
                        )
                    )
                );

                $keywords_string = implode(" ", $keywords);

                $long_word = 0;
                foreach($keywords as $word)
                {
                    $len = strlen($word);
                    if($len > $long_word)
                    {
                        $long_word = $len;
                    }
                }

                $found = false;
                $keyword_count = count($keywords);
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
                        if("" . stripos($title, $keywords_string) . "" != "")
                        {
                            self::addResult($uri, "title", $i + $keyword_count);
                            $found = true;
                            break;
                        }
                    }
                }

                if(!$found)
                {
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
                            //Second search for exact matches on content
                            if("" . stripos($content, $keywords_string) . "" != "")
                            {
                                self::addResult($uri, "content", $i + $keyword_count);
                                $found = true;
                                break;
                            }
                        }
                    }
                }

                if(!$found)
                {
                    sort($keywords);

                    for($i = $keyword_count - 1; $i >= 0; $i--)
                    {
                        $keywords_string = $keywords[$i];

                        if(strlen($keywords_string) >= $long_word)
                        {
                            if("" . stripos($title, $keywords_string) . "" != "")
                            {
                                self::addResult($uri, "title", $i);
                                break;
                            }
                            elseif("" . stripos($content, $keywords_string) . "" != "")
                            {
                                self::addResult($uri, "content", $i);
                                break;
                            }
                        }
                    }
                }
            }
        }
        else if($found_category_match)
        {
            self::addResult($uri);
        }
    }

    unset($content_data);
}

/**
 * Gets a set of results for a list of uris stored on _SESSION['search']
 * @param  int $page
 * @param  int $amount
 * @return array
 * @original get_search_results
 */
static function getResults($page=1, $amount=10)
{
    // To protect against sql injections be sure $page is a int
    if(!is_numeric($page))
    {
        $page = 1;
    }
    else
    {
        $page = intval($page);
    }

    if(!is_numeric($amount))
    {
        $amount = 10;
    }
    else
    {
        $amount = intval($amount);
    }

    unset($_SESSION["search"]["results"]);

    //First we sort title results and content results by relevancy
    $title_results = Data::sort(
        $_SESSION["search"]["results_title"],
        "relevancy",
        SORT_DESC
    );

    $content_results = Data::sort(
        $_SESSION["search"]["results_content"],
        "relevancy",
        SORT_DESC
    );

    if(is_array($title_results))
    {
        //Add title results to search results session
        foreach($title_results as $values)
        {
            $_SESSION["search"]["results"][] = $values["uri"];
        }
    }

    if(is_array($content_results))
    {
        //Add content results to search results session
        foreach($content_results as $values)
        {
            $_SESSION["search"]["results"][] = $values["uri"];
        }
    }

    if(is_array($_SESSION["search"]["results_normal"]))
    {
        //Add normal results to search results session
        foreach($_SESSION["search"]["results_normal"] as $value)
        {
            $_SESSION["search"]["results"][] = $value;
        }
    }

    unset($_SESSION["search"]["results_title"]);
    unset($_SESSION["search"]["results_content"]);
    unset($_SESSION["search"]["results_normal"]);

    $page_count = 0;
    $remainder_pages = 0;

    if(self::getResultsCount() <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor(self::getResultsCount() / $amount);
        $remainder_pages = self::getResultsCount() % $amount;

        if($remainder_pages > 0)
        {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range
    if($page > $page_count || $page < 1)
    {
        return array();
    }

    if(Sql::dbExists("search_engine"))
    {
        $start_result = 0;
        $end_result = $amount - 1;
    }
    else
    {
        $start_result = ($page * $amount) - $amount;
        $end_result = ($page * $amount) - 1;
    }

    $results_data = array();
    for(
        $start_result;
        isset($_SESSION["search"]["results"][$start_result]) && $start_result <= $end_result;
        $start_result++
    )
    {
        $page_data = Pages::get(
            $_SESSION["search"]["results"][$start_result],
            Language::getCurrent()
        );

        $page_data["uri"] = $_SESSION["search"]["results"][$start_result];

        $results_data[] = $page_data;

        unset($page_data);
    }

    return $results_data;
}

/**
 * Generates the html of the search navigation.
 * @param  int $page
 * @param  int $amount
 * @param  string $search_uri
 * @return bool
 * @original print_search_navigation
 */
static function printNavigation($page, $amount = 10, $search_uri = "search")
{
    // To protect agains sql injections be sure $page is a int
    if(!is_numeric($page))
    {
        $page = 1;
    }
    else
    {
        $page = intval($page);
    }

    if(!is_numeric($amount))
    {
        $amount = 10;
    }
    else
    {
        $amount = intval($amount);
    }

    //In case person is searching with category aliases set search uri to it
    if(Uri::type(Uri::get()) == "category" && $search_uri == "search")
    {
        $search_uri = Uri::get();
    }

    $page_count = 0;
    $remainder_pages = 0;

    if(self::getResultsCount() <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor(self::getResultsCount() / $amount);
        $remainder_pages = self::getResultsCount() % $amount;

        if($remainder_pages > 0)
        {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if($page > $page_count || $page < 0 || $page_count == 1)
    {
        return false;
    }

    //Generate list of selected categories to pass
    $categories = Categories::getList();
    $categories_string = "";

    //If category uri alias dont generate category arguments list
    if(Uri::type(Uri::get()) != "category")
    {
        if($categories)
        {
            foreach($categories as $category_name => $values)
            {
                if(isset($_REQUEST[$category_name]))
                {
                    foreach($_REQUEST[$category_name] as $selected)
                    {
                        $categories_string .= $category_name . "[]=" . $selected . "&";
                    }
                }
            }
        }
    }

    if($categories_string)
    {
        $categories_string = "&" . rtrim($categories_string, "&");
    }

    print "<div class=\"navigation\">\n";
    if($page != 1)
    {
        if(isset($_REQUEST["keywords"]))
        {
            $previous_page = Uri::url(
                $search_uri,
                array(
                    "page" => $page - 1,
                    "keywords" => $_REQUEST["keywords"],
                    "type" => $_REQUEST["type"],
                    "order" => $_REQUEST["order"],
                    "results_count" => $_REQUEST["results_count"]
                )
            ) . $categories_string;
        }
        else
        {
            $previous_page = Uri::url(
                $search_uri,
                array(
                    "page" => $page - 1,
                    "type" => $_REQUEST["type"],
                    "order" => $_REQUEST["order"],
                    "results_count" => $_REQUEST["results_count"]
                )
            ) . $categories_string;
        }
        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + $amount;

    for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
    {
        $text = $start_page;

        if($start_page > $page || $start_page < $page)
        {
            if(isset($_REQUEST["keywords"]))
            {
                $url = Uri::url(
                    $search_uri,
                    array(
                        "page" => $start_page,
                        "keywords" => $_REQUEST["keywords"],
                        "type" => $_REQUEST["type"],
                        "order" => $_REQUEST["order"],
                        "results_count" => $_REQUEST["results_count"]
                    )
                ) . $categories_string;
            }
            else
            {
                $url = Uri::url(
                    $search_uri,
                    array(
                        "page" => $start_page,
                        "type" => $_REQUEST["type"],
                        "order" => $_REQUEST["order"],
                        "results_count" => $_REQUEST["results_count"]
                    )
                ) . $categories_string;
            }
            print "<a class=\"page\" href=\"$url\">$text</a>";
        }
        else
        {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if($page < $page_count)
    {
        if(isset($_REQUEST["keywords"]))
        {
            $next_page = Uri::url(
                $search_uri,
                array(
                    "page" => $page + 1,
                    "keywords" => $_REQUEST["keywords"],
                    "type" => $_REQUEST["type"],
                    "order" => $_REQUEST["order"],
                    "results_count" => $_REQUEST["results_count"]
                )
            ) . $categories_string;
        }
        else
        {
            $next_page = Uri::url(
                $search_uri,
                array(
                    "page" => $page + 1,
                    "type" => $_REQUEST["type"],
                    "order" => $_REQUEST["order"],
                    "results_count" => $_REQUEST["results_count"]
                )
            ) . $categories_string;
        }
        $next_text = t("Next");
        print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
    }
    print "</div>\n";

    return true;
}

/**
 * Cache search results into _SESSION["search"]
 * @param string $result    Uri of page.
 * @param string $position Can be: title, content or append.
 * @param float $relevancy Used to sort when displaying the content.
 * @original add_result
 */
static function addResult($result, $position = "append", $relevancy = null)
{
    switch($position)
    {
        case "title":
            $_SESSION["search"]["results_title"][] = array(
                "uri" => $result,
                "relevancy" => $relevancy
            );
            break;

        case "content":
            $_SESSION["search"]["results_content"][] = array(
                "uri" => $result,
                "relevancy" => $relevancy
            );
            break;

        case "append":
            $_SESSION["search"]["results_normal"][] = $result;
            break;
    }

    $_SESSION["search"]["count"]++;
}

/**
 * Get list of results.
 * @return array
 * @original get_results
 */
static function getAllResults()
{
    return $_SESSION["search"]["results"];
}

/**
 * Get amout of results.
 * @return int
 * @original get_results_count
 */
static function getResultsCount()
{
    static $count;

    if(Sql::dbExists("search_engine"))
    {
        if($count <= 0)
        {
            $db = Sql::open("search_engine");
            Sql::turbo($db);

            $count = 0;

            $type = self::contentType();

            $user = Authentication::currentUser();
            $group = Authentication::currentUserGroup();

            //Search by keywords and categories
            if(count(self::getKeywords()) > 0)
            {
                $keywords = implode(" ", self::getKeywords());
                $keywords = str_replace("'", "''", $keywords);
                $categories = serialize(self::getCategories());
                $categories = str_replace("'", "''", $categories);

                $select = "select
                leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
                normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
                hascategories(categories, '$categories') as has_category,
                haspermission(groups, '$group') as has_permissions,
                hasuserpermission(users, '$user') as has_user_permissions,
                count(uri) as uri_count from uris where
                ((title_relevancy > 0 or content_relevancy > 0 or
                description_normal > 0 or keywords_normal > 0) and
                has_category > 0 and has_permissions > 0 and
                has_user_permissions > 0 and approved='a') $type";

                $result = Sql::query($select, $db);

                if($data = Sql::fetchArray($result))
                {
                    $count = $data["uri_count"];
                }
            }

            //Search by categories only
            else if(count(self::getCategories()) > 0)
            {
                $categories = serialize(self::getCategories());
                $categories = str_replace("'", "''", $categories);

                $select = "select
                hascategories(categories, '$categories') as has_category,
                haspermission(groups, '$group') as has_permissions,
                hasuserpermission(users, '$user') as has_user_permissions,
                count(uri) as uri_count from uris where
                has_category > 0 and has_permissions > 0 and
                has_user_permissions > 0 and approved='a' $type";

                $result = Sql::query($select, $db);

                if($data = Sql::fetchArray($result))
                {
                    $count = $data["uri_count"];
                }
            }

            Sql::close($db);
        }

        return $count;
    }

    return $_SESSION["search"]["count"];
}

/**
 * Empty search results stored on _SESSION["search"]
 * @original reset_results
 */
static function reset()
{
    unset($_SESSION["search"]);
}

/**
 * Store keywords in _SESSION["search"]["keywords"]
 * @param string $keywords
 * @original add_keywords
 */
static function addKeywords($keywords)
{
    $keywords = trim($keywords);
    $keywords = preg_replace("/ +/", " ", $keywords);
    $words = explode(" ", $keywords);
    $_SESSION["search"]["keywords"] = $words;
}

/**
 * Get keywords.
 * @return array
 * @original get_keywords
 */
static function getKeywords()
{
    return $_SESSION["search"]["keywords"];
}

/**
 * Store fields to display on search results.
 * @param array $field_values [description]
 * @original add_fields
 */
static function addFields($field_values)
{
    $_SESSION["search"]["field_values"] = $field_values;
}

/**
 * Categories to search.
 * @param array $categories
 * @original add_search_categories
 */
static function addCategories($categories)
{
    $_SESSION["search"]["categories"] = $categories;
}

/**
 * Get categories to search
 * @return array
 * @original get_search_categories
 */
static function getCategories()
{
    return $_SESSION["search"]["categories"];
}

/**
 * Get the fields to display on search results.
 * @return array
 * @original get_fields
 */
static function getFields()
{
    if(!$_SESSION["search"]["field_values"])
    {
        return array();
    }

    return $_SESSION["search"]["field_values"];
}

/**
 * Highlights the matched words on the search result.
 * @param  string $result
 * @param  string $input_format
 * @param  string $type
 * @return string
 * @original highlight_search_results
 */
static function highlightResults(
    $result, $input_format = "full_html", $type = "title"
)
{
    if($input_format == "php_code")
    {
        $result = System::evalPHP($result);
    }

    $result = Util::stripHTMLTags($result);
    $result = str_replace("<br />", " ", $result);

    $keywords = self::getKeywords() ? self::getKeywords() : array();
    $keywords_string = implode(" ", $keywords);

    $result = preg_replace("/ +/", " ", $result);

    if("" . stripos($result, $keywords_string) . "" != "")
    {
        $result = str_ireplace(
            $keywords_string,
            "<span class=\"search-highlight\">" . $keywords_string . "</span>",
            $result
        );
    }
    else
    {
        $result_words = explode(" ", $result);

        $long_word = 0;
        foreach($keywords as $word)
        {
            $len = strlen($word);
            if($len > $long_word)
            {
                $long_word = $len;
            }
        }

        foreach($result_words as $index => $result_word)
        {
            foreach($keywords as $word)
            {
                if(
                    ("" . stripos($result_word, $word) . "" == "0") &&
                    (strlen($word) >= $long_word || strlen($word) >= 3)
                )
                {
                    $result_words[$index] = str_ireplace(
                        $word,
                        "<span class=\"search-highlight\">" . $word . "</span>",
                        $result_words[$index]
                    );

                    break;
                }
            }
        }

        $result = implode(" ", $result_words);
    }

    $sentences = explode("</span>", $result);
    $sentences_count = count($sentences);

    //If no word was hightlited
    if($sentences_count - 1 <= 0)
    {
        if($type != "title")
        {
            return Util::contentPreview($result, 35, true);
        }
        else
        {
            return Util::contentPreview($result, 35, false);
        }
    }

    $final_result = "";
    for($i = 0; $i < $sentences_count; $i++)
    {
        $len = strlen($sentences[$i]);

        if($len > 80 && $type != "title")
        {
            $new_sentence = " ... ";
            $new_sentence .= substr($sentences[$i], $len - 80, 80);
            $new_sentence .= "</span>";
        }
        else
        {
            $new_sentence = $sentences[$i];

            if($i != $sentences_count - 1)
            {
                $new_sentence .= "</span>";
            }
        }

        if(strlen($final_result) <= 500)
        {
            $final_result .= $new_sentence;
        }
        else
        {
            break;
        }
    }

    return $final_result;
}

/**
 * Get the fields to display on search results for a given content type.
 * @param  string $type Machine name of content type.
 * @return array
 * @original get_type_search_fields
 */
static function getTypeFields($type)
{
    static $types_array;

    if(!$types_array[$type])
    {
        $fields_string = Settings::get("{$type}_fields", "main");

        if(trim($fields_string) != "")
        {
            $fields = explode(",", $fields_string);
            $fields_array = array();
            foreach($fields as $name)
            {
                $fields_data = explode(":", $name);

                if(count($fields_data) > 1)
                {
                    $fields_array[t(trim($fields_data[0])) . ":"] = trim($fields_data[1]);
                }
                else
                {
                    $fields_array[] = trim($fields_data[0]);
                }
            }

            $types_array[$type] = $fields_array;
        }
        else
        {
            $types_array[$type][] = "content";
        }
    }

    return $types_array[$type];
}

}