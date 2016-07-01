<div class="blog-post">

    <h3 class="title">
        <a class="thumbnail" href="<?php print $url ?>">
            <?php print $title ?>
        </a>
    </h3>

    <div class="details">

        <span class="date">
            <strong>
                <?php print t("Date:") ?>
            </strong>
            <?php print date("m/d/Y @ g:i:s a", $page_data["created_date"]) ?>
        </span> &nbsp;

        <span class="views">
            <strong><?php print t("Views:") ?></strong>
            <?php print $views ?>
        </span>

    </div>

    <div class="description">
        <?php if($thumbnail) { ?>
        <a class="thumbnail" href="<?php print $url ?>">
            <img alt="<?php print $title ?>" src="<?php print $thumbnail ?>" />
        </a>
        <?php } ?>

        <?php print $description ?>

        <div class="view-more">
            <a href="<?php print $url?>"><?php print t("view more") ?></a>
        </div>

    </div>


</div>