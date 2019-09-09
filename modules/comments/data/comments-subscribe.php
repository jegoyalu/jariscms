<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Comments Subscribe
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["view_comments"]);

        if (isset($_REQUEST["uri"]) && Jaris\Authentication::isUserLogged()) {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if ($page_data) {
                if (!$page_data["is_system"]) {
                    comments_notifications_subscribe(
                        Jaris\Authentication::currentUser(),
                        $_REQUEST["uri"]
                    );

                    Jaris\View::addMessage(
                        t("You will now receive e-mail notifications of comments on this page.")
                    );

                    Jaris\Uri::go($_REQUEST["uri"]);
                }
            }
        }

        Jaris\Uri::go("");
    ?>
    field;

    field: is_system
        1
    field;
row;
