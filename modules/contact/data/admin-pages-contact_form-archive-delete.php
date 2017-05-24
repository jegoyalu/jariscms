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
        <?php print t("Delete Archived Message on Contact Form") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_content"));

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        if(isset($_REQUEST["btnYes"]))
        {
            contact_archive_message_delete($_REQUEST["id"]);

            Jaris\View::addMessage(t("Archived message successfully deleted."));

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                array("uri" => $_REQUEST["uri"])
            );
        }
    ?>

    <form class="contact-archived-message-delete" method="post"
          action="<?php Jaris\Uri::url(Jaris\Uri::get()) ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>
            <p>
                <?php
                    print t("Are you sure you want to delete the archived message?");
                ?>
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
