<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu delete item page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Menu Item") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_menus", "delete_menu_items")
        );

        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["menu"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        $menu_data = Jaris\Menus::getItem($_REQUEST["id"], $_REQUEST["menu"]);

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Menus::deleteItem($_REQUEST["id"], $_REQUEST["menu"]))
            {
                Jaris\View::addMessage(t("Menu item successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/menus");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/menus");
        }
    ?>

    <form class="menus-delete" method="post"
          action="<?php Jaris\Uri::url("admin/menus/delete") ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <input type="hidden" name="menu" value="<?php print $_REQUEST["menu"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the menu item?") ?>
            <div>
                <b>
                    <?php print t("Title:") ?>
                    <?php print t($menu_data["title"]) ?>
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
