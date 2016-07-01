<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
*/
?>

<table class="user-profile">
    <tr>
    <td class="picture">
        <img src="<?php print get_user_picture_url($username) ?>" />
    </td>

    <?php if($personal_text){ ?>
        <td class="personal-text">
            <?php print $personal_text ?>
        </td>
    <?php } ?>

        <td class="details">
            <div><b><?php print t("Member since:") . "</b> " . $register_date ?></div>
            <div><b><?php print t("Gender:") . "</b> " . $gender ?></div>
            <div><b><?php print t("Birth date:") . "</b> " . $birth_date ?></div>
        </td>
    </tr>
</table>

<?php print $latest_post ?>