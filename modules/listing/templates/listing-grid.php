<div class="listing-grid">
    <?php if($image){ ?>
    <div class="image">
        <?php print $image ?>
    </div>
    <?php } ?>

    <?php if($title){ ?>
    <div class="title">
        <h2><?php print $title ?></h2>
    </div>
    <?php } ?>

    <?php if($summary){ ?>
    <div class="summary">
        <?php print $summary ?>
    </div>
    <?php } ?>

    <?php if($view_more){ ?>
    <div class="view-more">
        <?php print $view_more ?>
    </div>
    <?php } ?>

    <div style="clear: both"></div>
</div>