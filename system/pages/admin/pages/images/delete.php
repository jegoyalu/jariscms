<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content images delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Image") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["delete_images"]);

        if (!isset($_REQUEST["uri"])) {
            Jaris\Uri::go("");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        $image_data = Jaris\Pages\Images::get(
            $_REQUEST["id"],
            $_REQUEST["uri"]
        );

        if (isset($_REQUEST["btnYes"])) {
            if (
                Jaris\Pages\Images::delete(
                    intval($_REQUEST["id"]),
                    $_REQUEST["uri"]
                )
            ) {
                Jaris\View::addMessage(t("Image successfully deleted."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/pages/images",
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                "admin/pages/images",
                ["uri" => $_REQUEST["uri"]]
            );
        }
    ?>

    <form class="images-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/images/delete") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <div>
            <?php print t("Are you sure you want to delete the image?") ?>
            <div>
                <a
                    href="<?php print Jaris\Uri::url("image/{$_REQUEST['uri']}/{$image_data['name']}"); ?>"
                >
                    <img src="<?php print Jaris\Uri::url("image/{$_REQUEST['uri']}/{$image_data['name']}", ["w" => "100"]); ?>" />
                </a>
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
