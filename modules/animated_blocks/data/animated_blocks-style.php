<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores animated blocks dynamic generated css script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Animated Blocks Style
    field;

    field: content
    <?php
        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["position"]))
        {
            print "0";
            return;
        }

        $_REQUEST["id"] = intval($_REQUEST["id"]);
        $_REQUEST["position"] = strval($_REQUEST["position"]);

        $block_data = Jaris\Blocks::get($_REQUEST["id"], $_REQUEST["position"]);
        $settings = animated_blocks_get_settings($block_data);
        $id = "animated-block-{$_REQUEST['position']}-{$_REQUEST['id']}";
        $container = "animated-block-container-{$_REQUEST['position']}-{$_REQUEST['id']}";
        $prev_id = $id . "-prev";
        $next_id = $id . "-next";
        $pager_id = $id . "-pager";
    ?>
    /*<style>*/
    #<?php print $container ?>
    {
        width: 100%;
    }

    #<?php print $id ?> .animated-block-slide
    {
        width: 100%;
        border:  <?php print $settings["border_style"] ?> <?php print $settings["border_width"] ?> #<?php print $settings["border_color"] ?>;

        <?php if(!$settings["background_transparent"]){ ?>
        background-color: #<?php print $settings["background_color"] ?> !important;
        <?php } ?>

        <?php if($settings["image_as_background"] && $settings["image_as_background_keep_ar"]){ ?>
        background-size: contain !important;
        background-position: center center !important;
        <?php } ?>
    }

    #<?php print $id ?> .animated-block-content
    {
        background-color: #<?php print $settings["content_background_color"] ?>;
    }

    #<?php print $id ?> .animated-block-link
    {
        display: block;
    }

    #<?php print $id ?> .animated-block-title
    {
        font-size: <?php print $settings["title_size"] ?>;
        color: #<?php print $settings["title_color"] ?>;
        margin:   <?php print $settings["title_margin"] ?>;
        padding:   <?php print $settings["title_padding"] ?>;
    }

    #<?php print $id ?> .animated-block-title a
    {
        font-size: <?php print $settings["title_size"] ?>;
        color: #<?php print $settings["title_color"] ?>;
        margin:   <?php print $settings["title_margin"] ?>;
        padding:   <?php print $settings["title_padding"] ?>;
    }

    #<?php print $id ?> .animated-block-title a:hover
    {
        text-decoration: underline;
    }

    #<?php print $id ?> .animated-block-description
    {
        font-size: <?php print $settings["description_size"] ?>;
        color: #<?php print $settings["description_color"] ?>;
        margin:   <?php print $settings["description_margin"] ?>;
        padding:   <?php print $settings["description_padding"] ?>;
    }

<?php if($settings["display_pager"]){ ?>

    #<?php print $pager_id ?>
    {
        text-align: <?php print $settings["pager_align"] ?>;
        margin:   <?php print $settings["pager_bar_margin"] ?>;
        padding:   <?php print $settings["pager_bar_padding"] ?>;
        background-color: #<?php print $settings["pager_bar_background_color"] ?>;
        border:  <?php print $settings["pager_bar_border_style"] ?> <?php print $settings["pager_bar_border_width"] ?> #<?php print $settings["pager_bar_border_color"] ?>;
    }

    #<?php print $pager_id ?> a
    {
        font-size: <?php print $settings["pager_size"] ?>;
        color: #<?php print $settings["pager_color"] ?>;
        margin:   <?php print $settings["pager_margin"] ?>;
        padding:   <?php print $settings["pager_padding"] ?>;
        background-color: #<?php print $settings["pager_background_color"] ?>;
        border:  <?php print $settings["pager_border_style"] ?> <?php print $settings["pager_border_width"] ?> #<?php print $settings["pager_border_color"] ?>;
        display: inline-block;
    }

    #<?php print $pager_id ?> a.activeSlide
    {
        color: #<?php print $settings["pager_active_color"] ?>;
        background-color: #<?php print $settings["pager_active_background_color"] ?>;
    }

<?php } ?>

<?php if(!$settings["image_as_background"]){ ?>

    #<?php print $id ?> .animated-block-image
    {
        position: absolute;
        margin:   <?php print $settings["image_margin"] ?>;
        padding:  <?php print $settings["image_padding"] ?>;
        border:  <?php print $settings["image_border_style"] ?> <?php print $settings["image_border_width"] ?> #<?php print $settings["image_border_color"] ?>;
    }
<?php } ?>

<?php if($settings["display_navigation"]){ ?>
    #<?php print $prev_id ?>
    {
        float: left;
        cursor: pointer;
        display: none;
        position: absolute;
        background-color: #<?php print $settings["navigation_background_color"] ?>;
        text-decoration: none;
        -moz-border-radius-topright: 3px;
        -moz-border-radius-bottomright: 3px;
        -webkit-border-top-right-radius: 3px;
        -webkit-border-bottom-right-radius: 3px;
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
    }

    #<?php print $next_id ?>
    {
        float: right;
        cursor: pointer;
        display: none;
        position: absolute;
        background-color: #<?php print $settings["navigation_background_color"] ?>;
        text-decoration: none;
        -moz-border-radius-topleft: 3px;
        -moz-border-radius-bottomleft: 3px;
        -webkit-border-top-left-radius: 3px;
        -webkit-border-bottom-left-radius: 3px;
        border-top-left-radius: 3px;
        border-bottom-left-radius: 3px;
    }

    #<?php print $prev_id ?> div, #<?php print $next_id ?> div
    {
        padding: 4px;
        color: #<?php print $settings["navigation_foreground_color"] ?>;
        font-weight: bold;
        font-size: <?php print $settings["navigation_size"] ?>;
    }

<?php } ?>
    /*</style>*/
    field;

    field: rendering_mode
        css
    field;

    field: is_system
        1
    field;
row;
