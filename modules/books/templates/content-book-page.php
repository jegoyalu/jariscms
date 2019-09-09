<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *https://opensource.org/licenses/GPL-3.0.
*/
$book_pages = books_get_book_pages($content_data["book"]);
?>

<div class="content">

<?php if ($header) {?><div class="content-header"><?php print $header ?></div><?php } ?>

    <table>
        <tr>
            <?php if ($left) {?><td class="content-left"><?php print $left ?></td><?php } ?>
            <td class="content">
                <?php if ($center) {?>
                <div class="content-center">
                    <?php print $center ?>
                </div>
                <?php } ?>
                <?php print $content; ?>
                <?php if ($toc = books_get_toc($book_pages, Jaris\Uri::get())) { ?>
                <div class="book-page-toc">
                    <h3><?php print t("Subsections") ?></h3>

                    <?php print $toc ?>
                </div>
                <?php }?>
                <hr />
                <table class="book-page-navigation">
                    <tbody>
                        <tr>
                            <td class="previous" style="width: 33%; text-align: left">
                                <?php print book_get_page_previous(Jaris\Uri::get()) ?>
                            </td>
                            <td class="index" style="width: 33%; text-align: center">
                                <?php print book_get_page_index(Jaris\Uri::get()) ?>
                            </td>
                            <td class="next" style="width: 33%; text-align: right">
                                <?php print book_get_page_next(Jaris\Uri::get()) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <?php if ($right || ($side_toc = books_get_side_toc($book_pages, $book_pages[Jaris\Uri::get()]))) {?>
            <td class="content-right"><?php print "<h2>".t("Related Sections")."</h3>" . $side_toc ?><?php print $right ?></td>
            <?php } ?>
        </tr>
    </table>

<?php if ($footer) {?><div class="content-footer"><?php print $footer ?></div><?php } ?>

</div>
