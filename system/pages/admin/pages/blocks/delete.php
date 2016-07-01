<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content blocks delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Page Block") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("delete_content_blocks"));

        if(
            !isset($_REQUEST["uri"]) ||
            !isset($_REQUEST["id"]) ||
            !isset($_REQUEST["position"])
        )
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        $block_data = Jaris\Blocks::get(
            $_REQUEST["id"],
            $_REQUEST["position"],
            $_REQUEST["uri"]
        );

        if(isset($_REQUEST["btnYes"]))
        {
            if(
                Jaris\Blocks::delete(
                    $_REQUEST["id"],
                    $_REQUEST["position"],
                    $_REQUEST["uri"]
                )
            )
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

            Jaris\Uri::go(
                "admin/pages/blocks",
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                "admin/pages/blocks",
                array("uri" => $_REQUEST["uri"])
            );
        }
    ?>

    <form class="blocks-page-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/blocks/delete") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <input type="hidden" name="position" value="<?php print $_REQUEST["position"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the block?") ?>
            <div>
                <b>
                    <?php print t("Description:") ?>
                    <?php print t($block_data["description"]) ?>
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
