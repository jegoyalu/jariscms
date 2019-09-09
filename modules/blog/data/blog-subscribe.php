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
        Blog Subscription
    field;

    field: content
    <?php
        if (!Jaris\Authentication::hasTypeAccess("blog", Jaris\Authentication::currentUserGroup())) {
            Jaris\Authentication::protectedPage();
        }

        if (isset($_REQUEST["user"])) {
            if ($user_data = Jaris\Users::get($_REQUEST["user"])) {
                if (Jaris\Authentication::hasTypeAccess("blog", $user_data["group"])) {
                    if (blog_subscribe($_REQUEST["user"], Jaris\Authentication::currentUser())) {
                        Jaris\View::addMessage(t("Subscribtion done."));

                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri("blog/user", "blog") .
                            "/" .
                            $_REQUEST["user"]
                        );
                    } else {
                        Jaris\View::addMessage(t("Already subscribed."));

                        Jaris\Uri::go(
                            Jaris\Modules::getPageUri("blog/user", "blog") .
                            "/" .
                            $_REQUEST["user"]
                        );
                    }
                }
            }
        }

        Jaris\View::addMessage(t("Blog does not exist."));
        Jaris\Uri::go("");
    ?>
    field;

    field: is_system
        1
    field;
row;