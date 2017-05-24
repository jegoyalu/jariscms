<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content file delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete File") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("delete_files"));

        if(!isset($_REQUEST["uri"]) || !isset($_REQUEST["id"]))
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        $file_id = intval($_REQUEST["id"]);

        $file_data = Jaris\Pages\Files::get(
            $file_id,
            $_REQUEST["uri"]
        );

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Pages\Files::delete($file_id, $_REQUEST["uri"]))
            {
                Jaris\View::addMessage(t("File successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/pages/files",
                array("uri" => $_REQUEST["uri"])
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                "admin/pages/files",
                array("uri" => $_REQUEST["uri"])
            );
        }
    ?>

    <form class="files-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/files/delete") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the file?") ?>
            <div>
                <div>
                    <b>
                        <?php print t("Name:") ?>
                        <?php print $file_data["name"] ?>
                    </b>
                </div>
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
