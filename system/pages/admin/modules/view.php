<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the modules info view page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Module Info") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_modules"));

        $info = null;

        if(isset($_REQUEST["path"]))
        {
            $info = Jaris\Modules::get($_REQUEST["path"]);
        }
        else
        {
            Jaris\Uri::go("admin/modules");
        }
    ?>

    <div class="module-info">
        <div class="info">
            <div>
                <span class="label"><?php print t("Name:") ?></span>
                <?php print t($info["name"]) ?>
            </div>
            <div>
                <span class="label"><?php print t("Version:") ?></span>
                <?php print t($info["version"]) ?>
            </div>
            <div>
                <span class="label"><?php print t("Description:") ?></span>
                <?php print t($info["description"]) ?>
            </div>
            <div>
                <span class="label"><?php print t("Author:") ?></span>
                <?php print t($info["author"]) ?>
            </div>
            <div>
                <span class="label"><?php print t("Email:") ?></span>
                <a href="mailto:<?php print $info["email"] ?>">
                    <?php print $info["email"] ?>
                </a>
            </div>
            <div>
                <span class="label"><?php print t("Website:") ?></span>
                <a target="_blank" href="<?php print $info["website"] ?>">
                    <?php print t($info["website"]) ?>
                </a>
            </div>
        </div>
    </div>
    field;

    field: is_system
        1
    field;
row;
