<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Background") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $backgrounds_settings = Jaris\Settings::getAll("backgrounds");
        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        $background = $backgrounds[intval($_REQUEST["id"])];

        if(isset($_REQUEST["btnYes"]))
        {
            unset($backgrounds[intval($_REQUEST["id"])]);

            if(Jaris\Settings::save("backgrounds", serialize($backgrounds), "backgrounds"))
            {
                if($background["multi"])
                {
                    $images = unserialize($background["images"]);
                    foreach($images as $image)
                    {
                        Jaris\Files::delete($image, "backgrounds");
                    }
                }
                else
                {
                    Jaris\Files::delete($background["image"], "backgrounds");
                }

                Jaris\View::addMessage(t("Background successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/backgrounds",
                    "backgrounds"
                )
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/backgrounds",
                    "backgrounds"
                )
            );
        }
    ?>

    <?php if($background["multi"]){ ?>
        <form class="background-delete" method="post" action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/settings/backgrounds/delete", "backgrounds")) ?>">
            <input type="hidden" name="id" value="<?php print intval($_REQUEST["id"]) ?>" />
            <div><?php print t("Are you sure you want to delete the multi-image background?") ?>
                <div>
                    <?php
                    $images = unserialize($background["images"]);
                    foreach($images as $image)
                    {
                        ?>
                        <a style="display: block; margin-bottom: 7px" href="<?php print Jaris\Uri::url(Jaris\Files::get($image, "backgrounds")); ?>">
                            <img width="300px" src="<?php print Jaris\Uri::url(Jaris\Files::get($image, "backgrounds")); ?>" />
                        </a>
                    <?php } ?>
                </div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    <?php } else{ ?>
        <form class="background-delete" method="post" action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("admin/settings/backgrounds/delete", "backgrounds")) ?>">
            <input type="hidden" name="id" value="<?php print intval($_REQUEST["id"]) ?>" />
            <div><?php print t("Are you sure you want to delete the background image?") ?>
                <div>
                    <a href="<?php print Jaris\Uri::url(Jaris\Files::get($background['image'], "backgrounds")); ?>">
                        <img width="300px" src="<?php print Jaris\Uri::url(Jaris\Files::get($background['image'], "backgrounds")); ?>" />
                    </a>
                </div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    <?php } ?>
    field;

    field: is_system
        1
    field;
row;
