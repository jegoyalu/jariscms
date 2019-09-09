<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the type delete page type script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Type") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_types", "delete_types"]
        );

        if (!isset($_REQUEST["type"])) {
            Jaris\Uri::go("admin/types");
        }

        $type_data = Jaris\Types::get($_REQUEST["type"]);

        if (isset($_REQUEST["btnYes"])) {
            $message = Jaris\Types::delete($_REQUEST["type"]);

            if ($message == "true") {
                Jaris\View::addMessage(t("Type successfully deleted."));

                t("Deleted content type '{machine_name}'.");

                Jaris\Logger::info(
                    "Deleted content type '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["type"]
                    ]
                );
            } else {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/types");
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go("admin/types");
        }
    ?>

    <form class="type-delete" method="post"
          action="<?php Jaris\Uri::url("admin/types/delete") ?>"
    >
        <input type="hidden" name="type" value="<?php print $_REQUEST["type"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to delete the type?") ?>
            <div>
                <b>
                    <?php print t("Type:") ?>
                    <?php print t($type_data["name"]) ?>
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
