<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        print t("Delete Book Page");
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["delete_content"]);

        if (!Jaris\Pages::userIsOwner($_REQUEST["page"])) {
            Jaris\Authentication::protectedPage();
        }

        $book_data = Jaris\Pages::get($_REQUEST["book"], Jaris\Language::getCurrent());
        $page_data = Jaris\Pages::get($_REQUEST["page"], Jaris\Language::getCurrent());

        if (!$book_data || $book_data["type"] != "book") {
            Jaris\View::addMessage(t("No valid parent book given."), "error");
            Jaris\Uri::go("");
        }

        if (!$page_data || $page_data["type"] != "book-page") {
            Jaris\View::addMessage(t("Trying to delete a non book page."), "error");
            Jaris\Uri::go("");
        }

        if (isset($_REQUEST["btnYes"])) {
            //Delete page
            if (Jaris\Pages::delete($_REQUEST["page"])) {
                Jaris\View::addMessage(t("Book page successfully deleted."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            //Also delete page translations
            if (Jaris\Translate::deletePage($_REQUEST["page"])) {
                Jaris\View::addMessage(t("Translations successfully deleted."));
            } else {
                Jaris\View::addMessage(Jaris\System::errorMessage("translations_not_deleted"), "error");
            }

            Jaris\Uri::go($_REQUEST["book"]);
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go($_REQUEST["book"]);
        }
    ?>

    <form class="book-page-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/books/delete-page", "books")) ?>"
    >
        <input type="hidden" name="book" value="<?php print $_REQUEST["book"] ?>" />
        <input type="hidden" name="page" value="<?php print $_REQUEST["page"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to delete this book page?") ?>
            <div>
                <b>
                    <?php print t("Parent Book:") ?>
                </b>
                <?php print t($book_data["title"]) ?>
            </div>
            <div>
                <b>
                    <?php print t("Page title:") ?>
                </b>
                <?php print t($page_data["title"]) ?>
            </div>
        </div>
        <input class="form-submit" type="submit"
               name="btnYes" value="<?php print t("Yes") ?>"
        />
        <input class="form-submit" type="submit"
               name="btnNo" value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
