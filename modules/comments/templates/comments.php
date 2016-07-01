<div class="comment" id="comment-<?php print $id ?>">
    <div class="comment-user">
        <?php print $user_data["name"] ?>
    </div>

    <div class="comment-actions">
        <?php print $flag_url ?> <?php print $reply_url ?> <?php print $delete_url ?>
    </div>

    <div style="clear: both"></div>

    <?php if($reply_to && !$include_replies){ ?>
    <div class="comment-reply-to">
      <?php print t("Reply to:") . " " . $reply_user_data["name"]; ?>
    </div>
    <?php } ?>

    <div class="comment-content"><?php print $content ?></div>

    <?php if($include_replies){ ?>
    <div class="comment-replies">
      <?php print $replies ?>
    </div>
    <?php } ?>
</div>