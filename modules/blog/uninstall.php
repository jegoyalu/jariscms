<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module uninstall file
 *
 * Stores the uninstall script for blog module.
 */

function blog_uninstall()
{
    //Remove related blog block
    Jaris\Blocks::deleteByField("block_name", "blog_user_archive");

    //Remove recent user post block
    Jaris\Blocks::deleteByField("block_name", "blog_recent_user_posts");

    //Remove recent blog block
    Jaris\Blocks::deleteByField("block_name", "blog_new_blogs");

    //Remove most viewed blog block
    Jaris\Blocks::deleteByField("block_name", "blog_most_viewed_blogs");

    //Remove navigate by categories block
    Jaris\Blocks::deleteByField("block_name", "blog_categories_blogs");
}

?>
