<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * @file Jaris CMS module install file
 *
 * Stores the installation script for module.
 */

function books_install()
{
    $string = t("Book");
    $string = t("A collection of pages that document the usage of a product, software, etc...");

    $string = t("Book Page");
    $string = t("A documentation page that belongs to a book.");

    // Add book type
    $book_fields["name"] = "Book";
    $book_fields["description"] = "A collection of pages that document the usage of a product, software, etc...";

    Jaris\Types::add("book", $book_fields);

    // Add a book page type
    $bookpage_fields["name"] = "Book Page";
    $bookpage_fields["description"] = "A documentation page that belongs to a book.";

    Jaris\Types::add("book-page", $bookpage_fields);
}
