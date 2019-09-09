

    <tr>
        <?php if ($content_data["thumbnail_show"]) { ?>
        <td class="image"><?php print $image ?></td>
        <?php } ?>

        <?php if ($title || $summary || $price) { ?>
        <td class="preview">
            <div class="title"><?php print $title ?></div>
            <?php if ($reviews_score) { ?>
            <div class="reviews-score">
                <?php print $reviews_score ?>
            </div>
            <?php } ?>
            <div class="summary"><?php print $summary ?></div>
            <div class="price"><?php print $price ?></div>
        </td>
        <?php } ?>

        <?php if ($content_data["display_more"]) { ?>
        <td class="view-more"><?php print $view_more ?></td>
        <?php } ?>
    </tr>