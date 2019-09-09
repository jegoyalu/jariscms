<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php
        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        $type_data = Jaris\Types::get(Jaris\Pages::getType($_REQUEST["uri"]));

        print t("Delete") . " " . t($type_data["name"]);
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["delete_content"]);

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $arguments = [
            "uri" => $_REQUEST["uri"]
        ];

        //Tabs
        if (
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        if (
            Jaris\Authentication::groupHasPermission(
                "view_content_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if (
            Jaris\Authentication::groupHasPermission(
                "view_images",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if (
            Jaris\Authentication::groupHasPermission(
                "view_files",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if (
            Jaris\Authentication::groupHasPermission(
                "translate_languages",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if (
            Jaris\Authentication::groupHasPermission(
                "delete_content",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        $page_data = Jaris\Pages::get($_REQUEST["uri"]);

        if (isset($_REQUEST["btnYes"])) {
            //Delete page
            if (Jaris\Pages::delete($_REQUEST["uri"])) {
                Jaris\View::addMessage(t("Page successfully deleted."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            //Also delete page translations
            if (Jaris\Translate::deletePage($_REQUEST["uri"])) {
                Jaris\View::addMessage(t("Translations successfully deleted."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("translations_not_deleted"),
                    "error"
                );
            }

            if (
                !Jaris\Authentication::groupHasPermission(
                    "view_content",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\Uri::go("admin/user/content");
            } else {
                Jaris\Uri::go("admin/pages");
            }
        } elseif (isset($_REQUEST["btnNo"])) {
            if (
                Jaris\Authentication::groupHasPermission(
                    "edit_content",
                    Jaris\Authentication::currentUserGroup()
                )
            ) {
                Jaris\Uri::go(
                    "admin/pages/edit",
                    ["uri" => $_REQUEST["uri"]]
                );
            } else {
                Jaris\Uri::go($_REQUEST["uri"]);
            }
        }
    ?>

    <form class="page-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/delete") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to delete this?") ?>
            <div>
                <b>
                    <?php print t("Title:") ?>
                    <?php print t($page_data["title"]) ?>
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
