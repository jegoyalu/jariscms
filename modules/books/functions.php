<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        if(
            Jaris\Uri::get() == "admin/pages/edit"
            &&
            $parameters["name"] == "edit-page-book"
        )
        {
            Jaris\View::addScript("scripts/jquery-ui/jquery.ui.js");
            Jaris\View::addScript(
                "scripts/jquery-ui/jquery.ui.touch-punch.min.js"
            );
            Jaris\View::addScript(
                Jaris\Modules::directory("books") . "scripts/move.js"
            );

            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if(isset($page_data["book_pages"]))
            {
                $page_data["book_pages"] = unserialize(
                    $page_data["book_pages"]
                );
            }

            $attributes_html = Jaris\Forms::beginFieldset(
                t("Pages"), true, false
            );

            $attributes_html .= '<a id="book-pages-add-link" href="'
                .Jaris\Uri::url(
                    "admin/pages/add",
                    array(
                        "type"=>"book-page",
                        "book" => $_REQUEST["uri"]
                    )
                ).
                '">'
                .t("Add Page")
                .'</a>'
            ;
            $attributes_html .= '<hr />';
            $attributes_html .= '<table class="book-pages-list navigation-list">';
            $attributes_html .= '<thead>';
            $attributes_html .= '<tr>';
            $attributes_html .= '<td></td>';
            $attributes_html .= '<td>' . t("Page") . '</td>';
            $attributes_html .= '<td>' . t("Parent") . '</td>';
            $attributes_html .= '<td></td>';
            $attributes_html .= '</tr>';
            $attributes_html .= '</thead>';
            $attributes_html .= '<tbody>';

            if(is_array($page_data["book_pages"]))
            {
                $book_pages = books_organize_pages($page_data["book_pages"]);

                $book_pages_dash = books_organize_pages(
                    $page_data["book_pages"], 0, "&nbsp;-"
                );

                foreach($book_pages as $book_page_uri=>$book_page_data)
                {
                    $attributes_html .= '<tr>';

                    $attributes_html .= '<td class="handle">'
                        . '<a class="sort-handle"></a>'
                        . '</td>';

                    $attributes_html .= '<td class="page">'
                        . '<input type="hidden" name="book_pages[]" value="'.$book_page_uri.'" />'
                        . '<a href="'.Jaris\Uri::url($book_page_uri).'">'
                        . $book_page_data["title"]
                        . '</td>'
                    ;

                    $attributes_html .= '<td class="parent">'
                        . '<select name="book_pages_parent[]">'
                    ;

                    $attributes_html .= '<option value="">'
                        . t("-Parent Section-")
                        . '</option>'
                    ;

                    foreach($book_pages_dash as $dash_uri=>$dash_data)
                    {
                        if($dash_uri == $book_page_uri)
                            continue;

                        if($dash_data["parent"] == $book_page_uri)
                            continue;

                        $selected = $book_page_data["parent"] == $dash_uri ?
                            "selected" : ""
                        ;

                        $attributes_html .= '<option value="'.$dash_uri.'" '.$selected.'>'
                            . $dash_data["title"]
                            . '</option>'
                        ;
                    }

                    $attributes_html .= '</select>'
                        . '</td>'
                    ;

                    $attributes_html .= '<td class="action">'
                        . '<a href="'.Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/books/delete-page", "book"
                            ),
                            array(
                                "book"=>$_REQUEST["uri"],
                                "page"=>$book_page_uri
                            )
                        ).'">'
                        . t("Delete")
                        . '</a>'
                    ;

                    $attributes_html .= '</tr>';
                }
            }

            $attributes_html .= '</tbody>';
            $attributes_html .= '</table>';

            $attributes_html .= Jaris\Forms::endFieldset();

            $field = array("type"=>"other", "html_code"=>$attributes_html);

            Jaris\Forms::addFieldAfter($field, "content", $fieldsets);
        }
        elseif(
            Jaris\Uri::get() == "admin/pages/add"
            &&
            $parameters["name"] == "add-page-book-page"
        )
        {
            $books_list = books_get_books();

            if(count($books_list) <= 0)
            {
                Jaris\View::addMessage(
                    t("Please create a book before adding a book page.")
                );

                Jaris\Uri::go("admin/pages/types");
            }

            $books = array(t("-Parent Book-")=>"");

            foreach($books_list as $book_uri)
            {
                $book_data = Jaris\Pages::get(
                    $book_uri, Jaris\Language::getCurrent()
                );
                $books[$book_data["title"]] = $book_uri;
            }

            $books_field = array(
                "type" => "select",
                "name" => "book",
                "label" => t("Book:"),
                "id" => "language",
                "value" => $books,
                "selected" => isset($_REQUEST["book"])? $_REQUEST["book"] : "",
                "description" => t("The book this page belongs to.")
            );

            Jaris\Forms::addFieldBefore($books_field, "title", $fieldsets);
        }
        elseif(
            Jaris\Uri::get() == "admin/pages/edit"
            &&
            $parameters["name"] == "edit-page-book-page"
        )
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            $books_list = books_get_books();
            $books = array(t("-Parent Book-")=>"");

            foreach($books_list as $book_uri)
            {
                $book_data = Jaris\Pages::get(
                    $book_uri, Jaris\Language::getCurrent()
                );
                $books[$book_data["title"]] = $book_uri;
            }

            $books_field = array(
                "type" => "select",
                "name" => "book",
                "label" => t("Book:"),
                "id" => "language",
                "value" => $books,
                "selected" => isset($_REQUEST["book"]) ? 
                    $_REQUEST["book"] : $page_data["book"],
                "description" => t("The book this page belongs to.")
            );

            Jaris\Forms::addFieldBefore($books_field, "title", $fieldsets);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_CREATE_PAGE,
    function(&$uri, &$data, &$path)
    {
        if($data["type"] == "book")
        {
            $data["book_pages"] = serialize(array());
        }
        elseif($data["type"] == "book-page")
        {
            if(!isset($_REQUEST["book"]))
                return;

            $data["book"] = $_REQUEST["book"];

            $page_data = Jaris\Pages::get($_REQUEST["book"]);

            // Add page to book
            if($page_data)
            {
                $page_data["book_pages"] = unserialize($page_data["book_pages"]);

                if(is_array($page_data["book_pages"]))
                {
                    $page_data["book_pages"][$uri] = "";
                }
                else
                {
                    $page_data["book_pages"] = array();
                    $page_data["book_pages"][$uri] = "";
                }

                $page_data["book_pages"] = serialize($page_data["book_pages"]);

                Jaris\Pages::edit($_REQUEST["book"], $page_data);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function(&$page, &$new_data, &$page_path)
    {
        if($new_data["type"] == "book")
        {
            $book_pages = array();

            if(is_array($_REQUEST["book_pages"]))
            {
                foreach($_REQUEST["book_pages"] as $pos=>$uri)
                {
                    $book_pages[$uri] = $_REQUEST["book_pages_parent"][$pos];
                }

                $new_data["book_pages"] = serialize($book_pages);
            }
        }
        elseif($new_data["type"] == "book-page")
        {
            if(!isset($_REQUEST["book"]))
                return;

            $old_data = array(
                "book" => $new_data["book"]
            );

            $new_data["book"] = $_REQUEST["book"];

            if($old_data["book"] != $new_data["book"])
            {
                // Add page to new book
                $page_data = Jaris\Pages::get($new_data["book"]);

                if($page_data)
                {
                    $page_data["book_pages"] = unserialize(
                        $page_data["book_pages"]
                    );

                    if(is_array($page_data["book_pages"]))
                    {
                        $page_data["book_pages"][$page] = "";
                    }
                    else
                    {
                        $page_data["book_pages"] = array();
                        $page_data["book_pages"][$page] = "";
                    }

                    $page_data["book_pages"] = serialize(
                        $page_data["book_pages"]
                    );

                    Jaris\Pages::edit($new_data["book"], $page_data);
                }

                // Remove it from previous one
                $page_data = Jaris\Pages::get($old_data["book"]);

                if($page_data)
                {
                    $page_data["book_pages"] = unserialize(
                        $page_data["book_pages"]
                    );

                    unset($page_data["book_pages"][$page]);

                    $page_data["book_pages"] = serialize(
                        $page_data["book_pages"]
                    );

                    Jaris\Pages::edit($old_data["book"], $page_data);
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_MOVE_PAGE,
    function(&$actual_uri, &$new_uri)
    {
        $page_data = Jaris\Pages::get($actual_uri);

        if($page_data["type"] == "book")
        {
            $page_data["book_pages"] = unserialize($page_data["book_pages"]);

            foreach($page_data["book_pages"] as $page_uri=>$page_parent)
            {
                $child_page_data = Jaris\Pages::get($page_uri);
                $child_page_data["book"] = $new_uri;

                Jaris\Pages::edit($page_uri, $child_page_data);
            }
        }
        elseif($page_data["type"] == "book-page")
        {
            $book_page = Jaris\Pages::get($page_data["book"]);

            $book_page["book_pages"] = unserialize($book_page["book_pages"]);

            $book_page["book_pages"][$new_uri] = $book_page["book_pages"][$actual_uri];

            unset($book_page["book_pages"][$actual_uri]);

            foreach($book_page["book_pages"] as $page_uri=>$page_parent)
            {
                if($page_parent == $actual_uri)
                    $book_page["book_pages"][$page_uri] = $new_uri;
            }

            $book_page["book_pages"] = serialize($book_page["book_pages"]);

            Jaris\Pages::edit($page_data["book"], $book_page);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function(&$page, &$page_path)
    {
        $page_data = Jaris\Pages::get($page);

        if($page_data["type"] == "book")
        {
            $page_data["book_pages"] = unserialize($page_data["book_pages"]);

            foreach($page_data["book_pages"] as $page_uri=>$page_parent)
            {
                Jaris\Pages::delete($page_uri, true);

                Jaris\Translate::deletePage($page_uri);
            }
        }
        elseif($page_data["type"] == "book-page")
        {
            $book_data = Jaris\Pages::get($page_data["book"]);

            $book_data["book_pages"] = unserialize($book_data["book_pages"]);

            unset($book_data["book_pages"][$page]);

            $book_data["book_pages"] = serialize($book_data["book_pages"]);

            Jaris\Pages::edit($page_data["book"], $book_data);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function(&$tabs_array)
    {
        if(!Jaris\Pages::isSystem())
        {
            $page_data = Jaris\Pages::get(Jaris\Uri::get());

            if($page_data["type"] == "book")
            {
                if(
                    $page_data["author"] 
                    == 
                    Jaris\Authentication::currentUser() ||
                    Jaris\Authentication::isAdminLogged() ||
                    Jaris\Authentication::groupHasPermission(
                        "edit_all_user_content", 
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    $tabs_array[0][t("Add Page")] = array(
                        "uri" => "admin/pages/add",
                        "arguments" => array(
                            "type" => "book-page",
                            "book" => Jaris\Uri::get()
                        )
                    );
                }
            }

            if($page_data["type"] == "book-page")
            {
                if(
                    $page_data["author"] 
                    == 
                    Jaris\Authentication::currentUser() ||
                    Jaris\Authentication::isAdminLogged() ||
                    Jaris\Authentication::groupHasPermission(
                        "edit_all_user_content", 
                        Jaris\Authentication::currentUserGroup()
                    )
                )
                {
                    $tabs_array[0][t("Section Positioning")] = array(
                        "uri" => "admin/pages/edit",
                        "arguments" => array(
                            "uri" => $page_data["book"]
                        )
                    );
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_CONTENT_TEMPLATE,
    function(&$page, &$type, &$template_path)
    {
        $theme = Jaris\Site::$theme;

        $default_template = Jaris\Themes::directory($theme) . "content.php";

        if($type == "book" && $template_path == $default_template)
        {
            $template_path = Jaris\Modules::directory("books") 
                . "templates/content-book.php"
            ;
        }
        elseif($type == "book-page" && $template_path == $default_template)
        {
            $template_path = Jaris\Modules::directory("books") 
                . "templates/content-book-page.php"
            ;
        }
    }
);