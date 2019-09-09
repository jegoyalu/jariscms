<table class="listing-list">
    <thead>
        <tr>
            <?php if ($content_data["thumbnail_show"]) { ?>
            <td><?php print t("Image") ?></td>
            <?php } ?>

            <?php if ($content_data["display_title"] || $content_data["display_summary"]) { ?>
            <td><?php print t("Preview") ?></td>
            <?php } ?>

            <?php if ($content_data["display_more"]) { ?>
            <td></td>
            <?php } ?>
        </tr>
    </thead>

    <tbody>
