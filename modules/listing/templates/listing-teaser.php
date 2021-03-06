<div class="listing-teaser">
    <?php if ($title) { ?>
    <div class="title">
        <h2><?php print $title ?></h2>
    </div>
    <?php } ?>

    <?php if ($image) { ?>
    <div class="image">
        <?php print $image ?>
    </div>
    <?php } ?>

    <?php if ($reviews_score) { ?>
    <div class="reviews-score">
        <?php print $reviews_score ?>
    </div>
    <?php } ?>

    <?php if ($summary) { ?>
    <div class="summary">
        <?php print $summary ?>
    </div>
    <?php } ?>

    <?php if ($price) { ?>
    <div class="price">
        <?php print $price ?>
    </div>
    <?php } ?>

    <?php if ($view_more) { ?>
    <div class="view-more">
        <?php print $view_more ?>
    </div>
    <?php } ?>

    <div style="clear: both"></div>
</div>