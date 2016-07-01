<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the global delete block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_blocks", "delete_blocks"));

        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["position"]))
        {
            Jaris\Uri::go("admin/blocks");
        }

        $block_data = Jaris\Blocks::get($_REQUEST["id"], $_REQUEST["position"]);

        if($block_data["is_system"])
        {
            Jaris\View::addMessage(
                t("Can't delete system generated block."),
                "error"
            );

            Jaris\Uri::go("admin/blocks");
        }

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Blocks::delete($_REQUEST["id"], $_REQUEST["position"]))
            {
                Jaris\View::addMessage(t("Block successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/blocks");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/blocks");
        }
    ?>

    <form class="blocks-delete" method="post" action="<?php Jaris\Uri::url("admin/blocks/delete") ?>">
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <input type="hidden" name="position" value="<?php print $_REQUEST["position"] ?>" />

        <div>
            <?php print t("Are you sure you want to delete the block?") ?>
            <div>
                <b>
                    <?php print t("Description: ") ?>
                    <?php print $block_data["description"] ?>
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
