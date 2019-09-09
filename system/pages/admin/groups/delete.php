<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the group delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Group") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_groups", "delete_groups"]
        );

        if (!isset($_REQUEST["group"])) {
            Jaris\Uri::go("admin/groups");
        }

        $group_data = Jaris\Groups::get($_REQUEST["group"]);

        if (isset($_REQUEST["btnYes"])) {
            $message = Jaris\Groups::delete($_REQUEST["group"]);

            if ($message == "true") {
                Jaris\View::addMessage(t("Group successfully deleted."));

                t("Deleted group '{machine_name}'.");

                Jaris\Logger::info(
                    "Deleted group '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["group"]
                    ]
                );
            } else {
                //An error ocurred so display the error message
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/groups");
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go("admin/groups");
        }
    ?>

    <form class="group-delete" method="post"
          action="<?php Jaris\Uri::url("admin/groups/delete") ?>"
    >
        <input type="hidden" name="group"
               value="<?php print $_REQUEST["group"] ?>"
        />
        <br />
        <div>
            <?php print t("Are you sure you want to delete the group?") ?>
            <div>
                <b>
                    <?php print t("Group:") ?>
                    <?php print t($group_data["name"]) ?>
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
