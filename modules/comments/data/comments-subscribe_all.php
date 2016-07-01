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
        Comments Subscribe All (only for admins)
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage();

        if(isset($_REQUEST["uri"]) && Jaris\Authentication::isAdminLogged())
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if($page_data)
            {
                if(!$page_data["is_system"])
                {
                    // Older versions of comments module did not supported notifications
                    // so we need to generate the subscribers database
                    if(Jaris\Sql::dbExists("comments", comments_page_path($_REQUEST["uri"])))
                    {
                        $db_page_comments = Jaris\Sql::open(
                            "comments",
                            comments_page_path($_REQUEST["uri"])
                        );

                        $result_page_comments = Jaris\Sql::query(
                            "select * from comments",
                            $db_page_comments
                        );

                        while($data_page_comments = Jaris\Sql::fetchArray($result_page_comments))
                        {
                            comments_notifications_initial_subscribe(
                                $data_page_comments["user"],
                                $_REQUEST["uri"]
                            );
                        }

                        Jaris\Sql::close($db_page_comments);
                    }

                    Jaris\View::addMessage(
                        t("All users on this thread will now receive e-mail notifications of new comments.")
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
