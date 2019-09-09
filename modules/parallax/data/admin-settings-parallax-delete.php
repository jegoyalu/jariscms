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
        <?php print t("Delete Parallax Background") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        $parallax_settings = Jaris\Settings::getAll("parallax");
        $backgrounds = unserialize($parallax_settings["parallax_backgrounds"]);

        $background = $backgrounds[intval($_REQUEST["id"])];

        if (isset($_REQUEST["btnYes"])) {
            unset($backgrounds[intval($_REQUEST["id"])]);

            if (
                Jaris\Settings::save(
                    "parallax_backgrounds",
                    serialize($backgrounds),
                    "parallax"
                )
            ) {
                Jaris\Files::delete($background["image"], "parallax");

                Jaris\View::addMessage(t("Parallax successfully deleted."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/parallax",
                    "parallax"
                )
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/parallax",
                    "parallax"
                )
            );
        }
    ?>

    <form
        class="parallax-delete"
        method="post"
        action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input
            type="hidden"
            name="id"
            value="<?php print intval($_REQUEST["id"]) ?>"
        />
        <div>
            <?php print t("Are you sure you want to delete the parallax?") ?>
            <div>
                <a href="<?php print Jaris\Uri::url(Jaris\Files::get($background['image'], "parallax")); ?>">
                    <img
                        width="300px"
                        src="<?php print Jaris\Uri::url(Jaris\Files::get($background['image'], "parallax")); ?>"
                    />
                </a>
            </div>
        </div>
        <input
            class="form-submit"
            type="submit"
            name="btnYes"
            value="<?php print t("Yes") ?>"
        />
        <input
            class="form-submit"
            type="submit"
            name="btnNo"
            value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
