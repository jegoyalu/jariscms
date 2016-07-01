<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
*/
?>

<div class="content">

<?php if($header){?><div class="content-header"><?php print $header ?></div><?php } ?>

    <table>
        <tr>
            <?php if($left){?><td class="content-left"><?php print $left ?></td><?php } ?>
            <td class="content">
                <?php if($center){?>
                <div class="content-center">
                    <?php print $center ?>
                </div>
                <?php } ?>
                <?php print $content; ?>
            </td>
            <?php if($right){?><td class="content-right"><?php print $right ?></td><?php } ?>
        </tr>
    </table>

<?php if($footer){?><div class="content-footer"><?php print $footer ?></div><?php } ?>

</div>
