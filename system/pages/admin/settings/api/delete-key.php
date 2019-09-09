<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content delete apikey page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Api Key") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["delete_keys_api"]);

        if (!Jaris\Sql::dbExists("api_keys") || !isset($_REQUEST["id"])) {
            Jaris\Uri::go("admin/settings/api");
        }

        $key_data = Jaris\ApiKey::getDataById($_REQUEST["id"]);

        if (isset($_REQUEST["btnYes"])) {
            //Delete page
            Jaris\ApiKey::delete($key_data["key"]);

            Jaris\View::addMessage(t("Api key successfully deleted."));

            t("Deleted api key '{key}'.");

            Jaris\Logger::info(
                "Deleted api key '{key}'.",
                [
                    "key" => $key_data["key"]
                ]
            );

            Jaris\Uri::go("admin/settings/api");
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go("admin/settings/api");
        }
    ?>

    <form class="api-delete-key" method="post"
          action="<?php Jaris\Uri::url("admin/pages/delete") ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>
            <p><?php print t("Are you sure you want to delete this api key?") ?></p>
            <div>
                <b>
                    <?php print t("Description:") ?>
                </b>
                <?php print t($key_data["description"]) ?>
            </div>

            <div>
                <b>
                    <?php print t("Key:") ?>
                </b>
                <?php print $key_data["key"] ?>
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
