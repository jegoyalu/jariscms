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
        <?php print t("Delete Members Group") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_groups_church_attendance"]
        );

        if ($_REQUEST["id"] < 6) {
            Jaris\View::addMessage(
                t("You can not delete the predefined groups."),
                "error"
            );

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/church-attendance/groups",
                    "church_accounting"
                )
            );
        }

        $element_data = church_attendance_group_get($_REQUEST["id"]);

        if (isset($_REQUEST["btnYes"])) {
            church_attendance_group_delete($element_data["id"]);

            church_attendance_member_move_to_other($element_data["id"]);

            Jaris\View::addMessage(t("Attendance group successfully deleted."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/church-attendance/groups",
                    "church_attendance"
                )
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/settings/church-attendance/groups",
                    "church_attendance"
                )
            );
        }
    ?>

    <form class="church-attendance-groups-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>

            <p>
                <?php
                    print t("Deleting a group will affect all associations for existing members.");
                    print t("Are you sure you want to delete this group?");
                ?>
            </p>
            <div>
                <b>
                    <?php print t("Label:") ?>
                </b>
                <?php print t($element_data["label"]) ?>
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
