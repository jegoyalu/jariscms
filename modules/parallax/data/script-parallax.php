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
        Parallax script
    field;

    field: content
    <?php if(isset($_REQUEST["id"])){ ?>

        <?php
            $parallax_settings = Jaris\Settings::getAll("parallax");
            $backgrounds = unserialize($backgrounds_settings["parallax_backgrounds"]);

            $background = $backgrounds[intval($_REQUEST["id"])];
        ?>

        $(document).ready(function(){
            <?php if(empty($background["element"])){ ?>
            var parallaxContainer = $('<div class="parallax parallax-<?php print intval($_REQUEST["id"]); ?>" />');
            <?php } else{ ?>
            var parallaxContainer = $('<?php print $background["element"]; ?>');
            <?php } ?>

            parallaxContainer.css(
                "background",
                "<?php print $background["background_color"]; ?> url(<?php print Jaris\Uri::url(Jaris\Files::get($background["image"], "parallax")); ?>) no-repeat <?php print $background["horizontal_position"]; ?> <?php print intval($background["vertical_position"]); ?>"
            );
            parallaxContainer.css(
                "background-attachment",
                "fixed"
            );

            <?php if(empty($background["element"])){ ?>
            $("body > *").appendTo(parallaxContainer);
            parallaxContainer.appendTo("body");
            <?php } ?>

            parallaxContainer.css({
                "background-size",
                "<?php print !empty($background["background_size"]) ? $background["background_size"] : "cover" ?>"
            });
        });

    <?php } ?>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
