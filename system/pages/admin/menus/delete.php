<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the menu delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Menu") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_menus", "delete_menus")
        );

        if(!isset($_REQUEST["menu"]))
        {
            Jaris\Uri::go("admin/menus");
        }

        if(isset($_REQUEST["btnYes"]))
        {
            //Store the current primary and secondary menus names
            $primary = Jaris\Settings::get("primary_menu", "main");
            $secondary = Jaris\Settings::get("secondary_menu", "main");

            $is_primary = $primary == $_REQUEST["menu"] ? true : false;

            $is_secondary = $secondary == $_REQUEST["menu"] && $primary != "" ?
                true
                :
                false
            ;

            //Check if no primary or secondary menu configuration
            //exist and checks if system default
            if(!$primary && $_REQUEST["menu"] == "primary")
            {
                $is_primary = true;
            }
            elseif(!$secondary && $_REQUEST["menu"] == "secondary")
            {
                $is_secondary = true;
            }

            if(!$is_primary && !$is_secondary)
            {
                if(Jaris\Menus::delete($_REQUEST["menu"]))
                {
                    //Delete the menu block
                    Jaris\Blocks::deleteByField("menu_name", $_REQUEST["menu"]);

                    Jaris\View::addMessage(t("Menu successfully deleted."));
                }
                else
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );
                }
            }
            else
            {
                if($is_primary)
                {
                    Jaris\View::addMessage(
                        t("Can't delete primary menu."),
                        "error"
                    );
                }
                else
                {
                    Jaris\View::addMessage(
                        t("Can't delete secondary menu."),
                        "error"
                    );
                }
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
        <input type="hidden" name="menu" value="<?php print $_REQUEST["menu"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the menu?") ?>
            <div>
                <b>
                    <?php print t("Name:") ?>
                    <?php print t($_REQUEST["menu"]) ?>
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
