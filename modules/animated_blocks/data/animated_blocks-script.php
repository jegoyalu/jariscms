<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the animated blocks dynamically generated script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Animated Blocks Script
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
    //<script>
    $(document).ready(function(){

        $("#<?php print $id ?>").cycle({
        fx: '<?php print $settings["effect_name"] ?>',
        timeout: '<?php print $settings["transition_speed"] ?>',
        speed: '<?php print $settings["effect_speed"] ?>',
        slideResize: 0,
        containerResize: 0,
        pause: '<?php print ($settings["hover_pause"] ? "1" : "0") ?>'
    <?php if($settings["display_navigation"]){ ?>
        , prev: '#<?php print $prev_id ?>',
        next: '#<?php print $next_id ?>'
    <?php } ?>
    <?php if($settings["display_pager"]){ ?>
        , pager: '#<?php print $pager_id ?>'
    <?php } ?>
        });

        function animated_block_generate()
        {
            var animated_block_container = $("#<?php print $container ?>");
            var animated_block = $("#<?php print $id ?>");
            var animated_pager = $("#<?php print $pager_id ?>");
            var animated_slide = $("#<?php print $id ?> .animated-block-slide");
            var animated_image = $("#<?php print $id ?> .animated-block-image");
            var animated_content = $("#<?php print $id ?> .animated-block-content");
            var animated_container = $("#<?php print $id ?> .animated-block-content-container");
            var animated_block_link = $("#<?php print $id ?> .animated-block-link");
            var animated_block_title = $("#<?php print $id ?> .animated-block-title");
            var animated_block_title_link = $("#<?php print $id ?> .animated-block-title a");
            var animated_block_description = $("#<?php print $id ?> .animated-block-description");

            var original_width = <?php print intval($settings["width"]) ?>;
            var original_height = <?php print intval($settings["height"]) ?>;
            var width = animated_block_container.width();
            var height = original_height;

            var resize = width / original_width;

            if(width != original_width)
            {
                height = parseInt((original_height / original_width) * width);
            }

            animated_slide.css("width", width + "px");
            animated_slide.css("height", height + "px");

            animated_block.css("width", width + "px");
            animated_block.css("height", height + "px");

            animated_block_link.css("width", width + "px");
            animated_block_link.css("height", height + "px");

            animated_pager.width(animated_block.width());
            animated_content.css("opacity", "<?php print $settings["content_opacity"] ?>");

            animated_block_title.css("font-size", <?php print intval($settings["title_size"]) ?>*resize + "px");
            animated_block_title_link.css("font-size", <?php print intval($settings["title_size"]) ?>*resize + "px");
            animated_block_description.css("font-size", <?php print intval($settings["description_size"]) ?>*resize + "px");

        <?php if($settings["content_position"] == "top"){ ?>

            animated_content.css("height", animated_container.height() + "px");
            animated_container.css("position", "absolute");
            animated_container.css("top", animated_slide.css("top"));
            animated_block_title.css("width", animated_slide.width() + "px");
            animated_block_description.css("width", animated_slide.width() + "px");

        <?php } elseif($settings["content_position"] == "bottom"){ ?>

            animated_content.css("position", "absolute");
            animated_content.css("width", animated_slide.width() + "px");
            animated_content.css("height", animated_container.height() + "px");
            animated_content.css("top", (animated_slide.height() - animated_container.height()) + "px");
            animated_container.css("position", "absolute");
            animated_container.css("top", animated_content.css("top"));
            animated_block_title.css("width", animated_slide.width() + "px");
            animated_block_description.css("width", animated_slide.width() + "px");

        <?php } elseif($settings["content_position"] == "right"){ ?>

            animated_content.css("width", (<?php print intval($settings["content_width"]) ?> * resize) + "px");
            animated_content.css("height", animated_slide.height() + "px");
            animated_content.css("position", "absolute");
            animated_content.css("top", animated_slide.css("top"));
            animated_content.css("left", animated_slide.width() - parseInt(animated_content.css("width")) + "px");
            animated_container.css("width", animated_content.css("width"));
            animated_container.css("height", animated_content.css("height"));
            animated_container.css("position", animated_content.css("position"));
            animated_container.css("top", animated_content.css("top"));
            animated_container.css("left", animated_content.css("left"));

        <?php } elseif($settings["content_position"] == "left"){ ?>

            animated_content.css("width", (<?php print intval($settings["content_width"]) ?> * resize) + "px");
            animated_content.css("height", animated_slide.height() + "px");
            animated_content.css("position", "absolute");
            animated_content.css("top", animated_slide.css("top"));
            animated_container.css("width", animated_content.css("width"));
            animated_container.css("height", animated_content.css("height"));
            animated_container.css("position", animated_content.css("position"));
            animated_container.css("top", animated_content.css("top"));
        <?php } ?>


    <?php if(!$settings["image_as_background"]){ ?>

        <?php if($settings["image_position"] == "top left"){ ?>

            animated_image.css("position", "absolute");
            animated_image.css("z-index", "-2");
            animated_image.css("top", "0px");

        <?php } elseif($settings["image_position"] == "top right"){ ?>

            animated_image.css("position", "absolute");
            animated_image.css("z-index", "-2");
            animated_image.css("top", "0px");
            animated_image.css("left", (animated_slide.width() - animated_image.width()) + "px");

        <?php } elseif($settings["image_position"] == "bottom left"){ ?>

            animated_image.css("position", "absolute");
            animated_image.css("z-index", "-2");
            animated_image.css("top", (animated_slide.height() - animated_image.height()) + "px");

        <?php } elseif($settings["image_position"] == "bottom right"){ ?>

            animated_image.css("position", "absolute");
            animated_image.css("z-index", "-2");
            animated_image.css("top", (animated_slide.height() - animated_image.height()) + "px");
            animated_image.css("left", (animated_slide.width() - animated_image.width()) + "px");

        <?php } ?>

    <?php } else{ ?>
            animated_slide.css("background-size", width + "px " + height + "px");
    <?php } ?>

        <?php if($settings["display_pager"]){ ?>
            var animated_block_pager = $("#<?php print $pager_id ?>");
            var animated_block_pager_link = $("#<?php print $pager_id ?> a");

            animated_block_pager.width(width);
            animated_block_pager_link.css("font-size", <?php print intval($settings["pager_size"]) ?>*resize + "px");
        <?php } ?>

        <?php if($settings["display_navigation"]){ ?>
            var animated_block_prev = $("#<?php print $prev_id ?> div");
            var animated_block_next = $("#<?php print $next_id ?> div");

            animated_block_prev.css("font-size", <?php print intval($settings["navigation_size"]) ?>*resize + "px");
            animated_block_next.css("font-size", <?php print intval($settings["navigation_size"]) ?>*resize + "px");

            function animated_block_show_navigation(){
                $("#<?php print $prev_id ?>").css("left", animated_block.position().left);

                $("#<?php print $prev_id ?>").css(
                    "top",
                    animated_block.position().top +
                    (animated_block.height() / 2) -
                    ($("#<?php print $prev_id ?>").height() / 2)
                );

                $("#<?php print $prev_id ?>").css("z-index", 1000);

                $("#<?php print $prev_id ?>").fadeIn("fast");

                $("#<?php print $next_id ?>").css(
                    "left",
                    animated_block.position().left +
                    (
                        animated_block.width() -
                        $("#<?php print $next_id ?>").width()
                    )
                );

                $("#<?php print $next_id ?>").css(
                    "top",
                    animated_block.position().top +
                    (animated_block.height() / 2) -
                    ($("#<?php print $next_id ?>").height() / 2)
                );

                $("#<?php print $next_id ?>").css("z-index", 1000);

                $("#<?php print $next_id ?>").fadeIn("fast");
            }

            animated_block_container.hover(
                function(){
                    animated_block_show_navigation();
                },
                function(){
                    $("#<?php print $prev_id ?>").fadeOut("fast");
                    $("#<?php print $next_id ?>").fadeOut("fast");
                }
            );

        <?php } ?>

        }

        animated_block_generate();

        $(window).resize(function(){
            animated_block_generate();
        });

    });
    //</script>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
