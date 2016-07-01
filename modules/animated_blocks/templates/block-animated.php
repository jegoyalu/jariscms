<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
*/
?>
<div class="block animated-block block-<?php print $id ?>">
    <?php if($title){ ?><div class="title"><?php print $title ?></div><?php } ?>

    <div class="content">
       <?php
            print $content;
            print animated_blocks_print_block($id, $position);
       ?>
    </div>
</div>
