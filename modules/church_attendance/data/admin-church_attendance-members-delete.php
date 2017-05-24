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
        <?php print t("Delete Member or Visitor") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("manage_members_church_attendance")
        );

        $element_data = church_attendance_member_get($_REQUEST["id"]);

        if(isset($_REQUEST["btnYes"]))
        {
            church_attendance_member_delete($element_data["id"]);

            Jaris\View::addMessage(t("Member or visitor successfully deleted."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members",
                    "church_attendance"
                )
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-attendance/members",
                    "church_attendance"
                )
            );
        }
    ?>

    <form class="church-attendance-member-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>

            <p>
                <?php
                    print t("Are you sure you want to delete this member?");
                ?>
            </p>

            <p>
                <strong>
                <?php
                    print $element_data["first_name"] . " "
                        . $element_data["last_name"] . " "
                        . $element_data["maiden_name"]
                    ;
                ?>
                </strong>
            </p>
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
