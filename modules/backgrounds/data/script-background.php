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
        Background script
    field;

    field: content
    <?php if(isset($_REQUEST["id"])){ ?>

        <?php
            $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
            $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

            $background = $backgrounds[intval($_REQUEST["id"])];

            $images = array();
            $stretch = "false";
            $centerx = "false";
            $centery = "false";

            if($background["multi"])
            {
                $images = unserialize($background["images"]);
                foreach($images as $index => $image)
                {
                    //Get full url
                    $images[$index] = '"' . Jaris\Uri::url(Jaris\Files::get($image, "backgrounds")) . '"';
                }

                $images = rtrim(implode(",", $images), ",");

                $stretch = $background["stretch"] ? "true" : "false";
                $responsive_stretch = $background["responsive_stretch"] ? "true" : "false";
                $centerx = $background["centerx"] ? "true" : "false";
                $centery = $background["centery"] ? "true" : "false";
            }
        ?>

        <?php if($background["multi"]){ ?>
            $(document).ready(function(){
                $<?php if(!empty($background["element"])){ ?>("<?php print $background["element"]; ?>")<?php } ?>.backstretch(
                    [<?php print $images; ?>],
                    {
                        fade: <?php print $background["fade_speed"]; ?>,
                        duration: <?php print $background["rotation_speed"]; ?>,
                        stretch: <?php print $stretch; ?>,
                        responsiveStretch: <?php print $responsive_stretch; ?>,
                        centeredX: <?php print $centerx; ?>,
                        centeredY: <?php print $centery; ?>,
                        fixed: <?php print ($background["attachment"] == "fixed" ? "true" : "false"); ?>,
                        wrap: {
                        top: <?php print intval($background["top"]); ?>
                        <?php if($background["min_width"] > 0){ ?>
                            ,minWidth: <?php print intval($background["min_width"]); ?>
                        <?php } ?>
                        <?php if($background["min_height"] > 0){ ?>
                            ,minHeight: <?php print intval($background["min_height"]); ?>
                        <?php } ?>
                        <?php if($background["max_width"] > 0){ ?>
                            ,maxWidth: <?php print intval($background["max_width"]); ?>
                        <?php } ?>
                        <?php if($background["max_height"] > 0){ ?>
                            ,maxHeight: <?php print intval($background["max_height"]); ?>
                        <?php } ?>
                        }
                    }
                );
            });
        <?php } else{ ?>
            $(document).ready(function(){
                <?php if(empty($background["element"])){ ?>
                var backgroundContainer = $('<div class="background background-<?php print intval($_REQUEST["id"]); ?>" />');
                <?php } else{ ?>
                var backgroundContainer = $('<?php print $background["element"]; ?>');
                <?php } ?>

                backgroundContainer.css(
                    "background",
                    "transparent url(<?php print Jaris\Uri::url(Jaris\Files::get($background["image"], "backgrounds")); ?>) <?php print $background["mode"]; ?> <?php print $background["position"]; ?> <?php print intval($background["top"]); ?>px"
                );
                backgroundContainer.css(
                    "background-attachment",
                    "<?php print $background["attachment"]; ?>"
                );

                <?php if(empty($background["element"])){ ?>
                backgroundContainer.css("min-height", $(window).height()+"px");
                $("body").css(
                    "background-color",
                    "#<?php print $background["background_color"]; ?>"
                );
                $("body > *").appendTo(backgroundContainer);
                backgroundContainer.appendTo("body");
                <?php } ?>

                <?php if($background["responsive"] && intval($background["max_width"]) > 0){ ?>
                function resizeBG()
                {
                    if($(window).width() >= <?php print $background["max_width"] ?>){
                        backgroundContainer.css({
                            backgroundSize: "<?php print $background["max_width"] ?>px auto"
                        });
                    }
                    else{
                        backgroundContainer.css({
                            backgroundSize: "cover"
                        });
                    }
                }

                $(window).resize(function(){
                    resizeBG();
                });

                resizeBG();
                <?php } else{ ?>
                backgroundContainer.css({
                    backgroundSize: "<?php print !empty($background["background_size"]) ? $background["background_size"] : "auto" ?>"
                });
                <?php } ?>
            });
        <?php } ?>

    <?php } ?>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
