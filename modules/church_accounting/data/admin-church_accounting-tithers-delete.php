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
        <?php print t("Delete Tither") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("manage_tithers_church_accounting"));

        $element_data = church_accounting_tither_get($_REQUEST["id"]);

        if(isset($_REQUEST["btnYes"]))
        {
            church_accounting_tither_delete($element_data["id"]);

            Jaris\View::addMessage(t("Tither successfully deleted."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }
    ?>

    <form class="church-accounting-tither-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>

            <p>
                <?php
                    print t("Are you sure you want to delete this tither?");
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
