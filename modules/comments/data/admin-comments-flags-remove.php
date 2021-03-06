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
        <?php print t("Remove Flags") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_comments_flags"]
        );

        $id = $_REQUEST["id"];
        $page = $_REQUEST["page"];
        $user = $_REQUEST["user"];

        comments_flag_remove($id, $page, $user);
        Jaris\View::addMessage(t("Flags successfully removed."));
        Jaris\Uri::go(Jaris\Modules::getPageUri("admin/comments/flags", "comments"));
    ?>
    field;

    field: is_system
        1
    field;
row;
