<div class="calendar-event">

<div class="publisher">
    <strong><?php print t("Published by:") ?></strong>
    <?php print $user_data["name"] ?>
</div>

<div class="date">
    <strong><?php print t("Date:") ?></strong>
    <?php
        print $event_data["day"] . "/"
            . $months[$event_data["month"]] . "/"
            . $event_data["year"]
        ;

        if(
            $event_data["day"] != $event_data["day_to"] ||
            $event_data["month"] != $event_data["month_to"] ||
            $event_data["year"] != $event_data["year_to"]
        )
        {
            print "&nbsp;&nbsp;-&nbsp;&nbsp;"
                . $event_data["day_to"] . "/"
                . $months[$event_data["month_to"]] . "/"
                . $event_data["year_to"]
            ;
        }
    ?>
</div>

<div class="hours">
    <strong><?php print t("Hours:") ?></strong>
    <?php
        print $event_data["hour"] . ":"
            . $minute . " "
            . ($event_data["is_am"] ? "AM" : "PM")
            . "&nbsp;&nbsp;-&nbsp;&nbsp;"
            . $event_data["hour_to"] . ":"
            . $minute_to . " "
            . ($event_data["is_am_to"] ? "AM" : "PM")
    ?>
</div>

<div class="description">
    <strong><?php print t("Description:") ?></strong>
    <?php print $description ?>
</div>

<?php if(trim($place) != "") { ?>
<div class="place">
    <strong><?php print t("Place:") ?></strong>
    <?php print $place ?>
</div>
<?php } ?>

<?php if(!empty($event_data["latitude"]) && !empty($event_data["longitude"])){ ?>
<div class="map">
    <strong><?php print t("Map:") ?></strong>
    <div id="map" style="width: 100%; height: 300px"></div>
    <script type="text/javascript">
        $("#map").gmap3({
            marker:{
                latLng:[
                    <?php print $event_data["latitude"] ?>,
                    <?php print $event_data["longitude"] ?>
                ]
            },
            map:{
                options:{
                    zoom: 10
                }
            }
        });
    </script>
</div>
<?php } ?>

<?php if(count($images) > 0){ ?>
<div class="images">
    <strong><?php print t("Images:") ?></strong>

    <?php
        foreach($images as $image)
        {
            $image_path = Jaris\Files::getDir()
                . "calendar/" .  str_replace("/", "-", $uri) . "/"
                . $image
            ;

            $image_url = Jaris\Uri::url($image_path);

            print '<img '
                . 'style="max-width: 800px; width: 97%;" '
                . 'src="'.$image_url.'" '
                . '/>'
            ;
        }
    ?>
</div>
<?php } ?>

<?php if(count($files) > 0){ ?>
<div class="documents">
    <strong><?php print t("Documents:") ?></strong>

    <?php
        foreach($files as $file)
        {
            $file_path = Jaris\Files::getDir()
                . "calendar/" .  str_replace("/", "-", $uri) . "/"
                . $file
            ;

            $file_url = Jaris\Uri::url($file_path);

            print '<a target="_blank" href="'.$file_url.'">'.$file.'</a>';
        }
    ?>
</div>
<?php } ?>

<?php if($url){ ?>
<div class="url">
    <a href="<?php print $url ?>" target="_blank">
        <?php print t("Register") ?>
    </a>
</div>
<?php } ?>

</div>