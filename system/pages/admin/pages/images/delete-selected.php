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
        print t("Delete Selected Images");
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("delete_images"));

        if(!isset($_REQUEST["uri"]))
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        if(!isset($_REQUEST["images"]) || count($_REQUEST["images"]) <= 0)
        {
            Jaris\View::addMessage(
                t("Please select the images to delete."),
                "error"
            );

            Jaris\Uri::go(
                "admin/pages/images",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }

        if(isset($_REQUEST["btnYes"]))
        {
            foreach($_REQUEST["images"] as $image_id)
            {
                //Delete page
                if(
                    !Jaris\Pages\Images::delete(
                        intval($image_id),
                        $_REQUEST["uri"]
                    )
                )
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );

                    Jaris\Uri::go(
                        "admin/pages/images",
                        array(
                            "uri" => $_REQUEST["uri"]
                        )
                    );
                }
            }

            Jaris\View::addMessage(t("Images successfully deleted."));

            Jaris\Uri::go(
                "admin/pages/images",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                "admin/pages/images",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }
    ?>

    <form class="images-select-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/images/delete-selected") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
    <?php foreach($_REQUEST["images"] as $image_id){ ?>
        <input type="hidden" name="images[]" value="<?php print $image_id ?>" />
    <?php } ?>

        <br />

        <div>
            <div><?php print t("Are you sure you want to delete the listed images?") ?></div>
            <?php foreach($_REQUEST["images"] as $image_id){ ?>
                <div style="padding: 5px; display: inline;">
                    <a target="_blank" href="<?php print Jaris\Uri::url("image/{$_REQUEST["uri"]}/" . $image_id) ?>">
                        <img src="<?php print Jaris\Uri::url("image/{$_REQUEST["uri"]}/" . $image_id, array("w" => "100")) ?>" />
                    </a>
                </div>
            <?php } ?>
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
