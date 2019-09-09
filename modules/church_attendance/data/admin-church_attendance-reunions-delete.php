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
        <?php print t("Delete Reunion") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["manage_reunions_church_attendance"]
        );

        $element_data = church_attendance_reunion_get($_REQUEST["id"]);

        if (isset($_REQUEST["btnYes"])) {
            church_attendance_reunion_delete($element_data["id"]);

            Jaris\View::addMessage(
                t("Registered reunion successfully deleted.")
            );

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/reunions",
                    "church_attendance"
                )
            );
        }
    ?>

    <form class="church-attendance-reunions-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>

            <p>
                <?php
                    print t("Are you sure you want to delete this reunion?");
                ?>
            </p>
            <div>
                <b>
                    <?php print t("Title:") ?>
                </b>
                <?php print t($element_data["title"]) ?>
            </div>
            <div>
                <b>
                    <?php print t("Date:") ?>
                </b>
                <?php
                    print $element_data["day"]
                        . "/"
                        . $element_data["month"]
                        . "/"
                        . $element_data["year"]
                    ;
                ?>
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
