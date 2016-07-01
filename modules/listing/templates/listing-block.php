<div class="listing-block">
    <?php if($image){ ?>
    <div class="list-image">
        <?php print $image ?>
    </div>
    <?php } ?>

    <?php if($title){ ?>
    <div class="list-title">
        <?php print $title ?>
    </div>
    <?php } ?>

    <div style="clear: both"></div>

    <?php if($summary){ ?>
    <div class="list-summary">
        <?php print $summary ?>
    </div>
    <?php } ?>

    <?php if($view_more){ ?>
    <div class="list-view-more">
        <?php print $view_more ?>
    </div>
    <?php } ?>

    <div style="clear: both"></div>
</div>