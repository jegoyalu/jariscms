<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

/**
 * Get a list of available books.
 * @return array Uri of the books.
 */
function books_get_books()
{
    $books = [];

    $db = Jaris\Sql::open("search_engine");

    $result = Jaris\Sql::query(
        "select * from uris where type='book'",
        $db
    );

    while ($data = Jaris\Sql::fetchArray($result)) {
        $books[] = $data["uri"];
    }

    Jaris\Sql::close($db);

    return $books;
}

/**
 * Get a list of book pages.
 * @param string $uri The uri of the main book.
 * @return array List of book pages.
 */
function books_get_book_pages($uri)
{
    $page_data = Jaris\Pages::get($uri);

    return unserialize($page_data["book_pages"]);
}

/**
 * Revursive function that returns a list of book pages organized
 * with titles indented.
 * @param array $pages
 * @param int $indet_amount
 * @param string $indent_char
 * @param string $parent Parent uri
 * @return array
 */
function books_organize_pages(&$pages, $indet_amount=0, $indent_char="&nbsp;&nbsp;", $parent="")
{
    $organized = [];

    $indet = "";

    for ($i=0; $i<$indet_amount; $i++) {
        $indet .= $indent_char;
    }

    foreach ($pages as $page_uri=>$page_parent) {
        if ($page_parent == $parent) {
            $page_data = Jaris\Pages::get($page_uri, Jaris\Language::getCurrent());

            $organized[$page_uri] = [
                "title" => $indet . $page_data["title"],
                "parent" => $page_parent
            ];

            // Append the page sub sections.
            $sub_sections = books_organize_pages(
                $pages,
                $indet_amount+1,
                $indent_char,
                $page_uri
            );

            if (count($sub_sections) > 0) {
                $organized += $sub_sections;
            }
        }
    }

    return $organized;
}

/**
 * Revursive function that returns a book table of content.
 * @param array $pages List of pages from the book.
 * @param string $parent Parent uri
 * @return string
 */
function books_get_toc(&$pages, $parent="")
{
    $sections_found = false;

    $output = "";

    $output .= '<ol>';
    foreach ($pages as $page_uri=>$page_parent) {
        if ($page_parent == $parent) {
            $sections_found = true;

            $page_data = Jaris\Pages::get($page_uri, Jaris\Language::getCurrent());

            $output .= '<li>';

            $output .= '<a href="'.Jaris\Uri::url($page_uri).'">';
            $output .= $page_data["title"];
            $output .= '</a>';

            $output .= books_get_toc($pages, $page_uri);

            $output .= '</li>';
        }
    }
    $output .= '</ol>';

    if ($sections_found) {
        return $output;
    }

    return "";
}

/**
 * Revursive function that returns a book table of content that can be used
 * as a side menu navigation of each book page.
 * @param array $pages List of pages from the book.
 * @param string $parent Parent uri
 * @return string
 */
function books_get_side_toc(&$pages, $parent="")
{
    $sections_found = false;

    $output = "";

    $output .= '<ol>';

    if ($parent) {
        $page_data = Jaris\Pages::get($parent, Jaris\Language::getCurrent());

        $output .= '<li>';

        $output .= '<a href="'.Jaris\Uri::url($parent).'">';
        $output .= $page_data["title"];
        $output .= '</a>';
    }

    $output .= '<ol>';

    foreach ($pages as $page_uri=>$page_parent) {
        if ($page_parent == $parent) {
            $page_data = Jaris\Pages::get($page_uri, Jaris\Language::getCurrent());

            $output .= '<li>';

            $output .= '<a href="'.Jaris\Uri::url($page_uri).'">';
            $output .= $page_data["title"];
            $output .= '</a>';

            $output .= '</li>';

            $sections_found = true;
        }
    }

    $output .= '</ol>';

    $output .= '</li>';

    $output .= '</ol>';

    if ($sections_found) {
        return $output;
    }

    return "";
}

/**
 * Return the previous page link for a given book page.
 * @param string $page The uri of a book page.
 * @return string Html code of link.
 */
function book_get_page_previous($page)
{
    $page_data = Jaris\Pages::get($page);

    $book_data = Jaris\Pages::get($page_data["book"]);

    $book_data["book_pages"] = unserialize($book_data["book_pages"]);

    $previous = "";

    foreach ($book_data["book_pages"] as $page_uri=>$page_parent) {
        if ($page_uri == $page) {
            break;
        }

        $previous = $page_uri;
    }

    if ($previous) {
        $previous_page = Jaris\Pages::get($previous, Jaris\Language::getCurrent());

        return '<a href="'.  Jaris\Uri::url($previous).'">'
            . '&lt; '
            . $previous_page["title"]
            . '</a>'
        ;
    }

    return "";
}

/**
 * Return the next page link for a given book page.
 * @param string $page The uri of a book page.
 * @return string Html code of link.
 */
function book_get_page_next($page)
{
    $page_data = Jaris\Pages::get($page);

    $book_data = Jaris\Pages::get($page_data["book"]);

    $book_data["book_pages"] = unserialize($book_data["book_pages"]);

    $next = "";
    $page_found = false;

    foreach ($book_data["book_pages"] as $page_uri=>$page_parent) {
        if ($page_uri == $page) {
            $page_found = true;
            continue;
        }

        if ($page_found) {
            $next = $page_uri;
            break;
        }
    }

    if ($next) {
        $next_page = Jaris\Pages::get($next, Jaris\Language::getCurrent());

        return '<a href="'.  Jaris\Uri::url($next).'">'
            . $next_page["title"]
            . ' &gt;'
            . '</a>'
        ;
    }

    return "";
}

/**
 * Return the index page link for a given book page.
 * @param string $page The uri of a book page.
 * @return string Html code of link.
 */
function book_get_page_index($page)
{
    $page_data = Jaris\Pages::get($page);

    $book_data = Jaris\Pages::get($page_data["book"]);

    return '<a href="'.  Jaris\Uri::url($page_data["book"]).'">'
        . $book_data["title"]
        . '</a>'
    ;
}
