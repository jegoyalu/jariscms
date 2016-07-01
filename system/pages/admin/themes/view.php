<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the themes info view page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Theme Info") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $info = array();

        if(isset($_REQUEST["path"]))
        {
            $info = Jaris\Themes::get($_REQUEST["path"]);
        }
        else
        {
            Jaris\Uri::go("admin/themes");
        }

        if(Jaris\Themes::directory($_REQUEST["path"]) != "themes/{$_REQUEST["path"]}/")
        {
            Jaris\View::addTab(
                "Delete",
                "admin/themes/delete",
                array("path" => $_REQUEST["path"])
            );
        }
    ?>

    <div class="theme-info">
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
            <?php if(!empty($info["developer"])){ ?>
            <div>
                <span class="label"><?php print t("Developer:") ?></span>
                <?php print htmlspecialchars($info["developer"]) ?>
            </div>
            <?php } ?>
            <div>
                <span class="label"><?php print t("Website:") ?></span>
                <a href="<?php print $info["website"] ?>">
                    <?php print t($info["website"]) ?>
                </a>
            </div>
        </div>

        <div class="preview">
            <div class="label"><?php print t("Preview") ?></div>
            <img src="<?php print Jaris\Uri::url(Jaris\Themes::directory($_REQUEST['path']) . "preview.png"); ?>" />
        </div>
    </div>
    field;

    field: is_system
        1
    field;
row;
