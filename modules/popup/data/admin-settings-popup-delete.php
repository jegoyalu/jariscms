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
        <?php print t("Delete Popup") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["edit_settings"]);

        if (!isset($_REQUEST["id"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/popup",
                    "popup"
                )
            );
        }

        $popup_data = Jaris\Data::get(
            $_REQUEST["id"],
            Jaris\Site::dataDir() . "settings/popup.php"
        );

        if (isset($_REQUEST["btnYes"])) {
            if (
                Jaris\Data::delete(
                    $_REQUEST["id"],
                    Jaris\Site::dataDir() . "settings/popup.php"
                )
            ) {
                Jaris\View::addMessage(t("Popup successfully deleted."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/popup",
                    "popup"
                )
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/popup",
                    "popup"
                )
            );
        }
    ?>

    <form
        class="popup-delete"
        method="post"
        action="<?php print Jaris\Uri::url(Jaris\Modules::getPageUri("admin/settings/popup/delete", "popup")) ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the popup?") ?>
            <div>
                <b>
                    <?php print t("Description: ") ?>
                    <?php print $popup_data["description"] ?>
                </b>
            </div>
        </div>

        <input class="form-submit" type="submit"
               name="btnYes" value="<?php print t("Yes") ?>"
        />
        <input class="form-submit" type="submit"
               name="btnNo" value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
