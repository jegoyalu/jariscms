<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
*/
?>
<div class="content-block content-block-<?php print $id ?>">
    <?php if($title || $post_title){ ?>
    <div class="title">
    <?php
        if($post)
            print $post_title;
        else
            print $title;
    ?>
    </div>
    <?php } ?>

    <div class="content">
        <?php if($image){ ?>
        <div class="block-image-thumbnail">
            <?php print $image ?>
        </div>
        <?php } ?>

        <?php print $content ?>
    </div>

    <?php if($post){ ?>
    <div class="clear"></div>
    <?php } ?>

    <?php if($view_more){ ?>
    <div class="block-view-more">
        <?php print $view_more ?>
    </div>
    <?php } ?>
</div>
