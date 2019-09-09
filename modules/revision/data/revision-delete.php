<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the revision delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Revision"); ?>
    field;

    field: content
    <?php
        $_REQUEST["rev"] = intval($_REQUEST["rev"]);

        $revision = $_REQUEST["rev"];

        $revision_file = Jaris\Pages::getPath($_REQUEST["uri"])
            . "/revisions/" . $revision . ".php"
        ;

        if (
            !isset($_REQUEST["uri"])
            ||
            !isset($_REQUEST["rev"])
            ||
            trim($_REQUEST["uri"]) == ""
            ||
            trim($_REQUEST["rev"]) == ""
            ||
            !file_exists(Jaris\Pages::getPath($_REQUEST["uri"]) . "/data.php")
            ||
            !file_exists($revision_file)
        ) {
            Jaris\Uri::go("access-denied");
        }

        if (!Jaris\Pages::userIsOwner($_REQUEST["uri"])) {
            Jaris\Authentication::protectedPage();
        }

        Jaris\Authentication::protectedPage(["delete_revisions"]);


        if (isset($_REQUEST["btnYes"])) {
            if (unlink($revision_file)) {
                Jaris\View::addMessage(t("Revision successfully removed."));
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                Jaris\Modules::getPageUri("revisions", "revision"),
                ["uri" => $_REQUEST["uri"]]
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("revisions", "revision"),
                ["uri" => $_REQUEST["uri"]]
            );
        }
    ?>

    <form
        class="revision-delete" method="post"
        action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("revision/delete", "revision")) ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="rev" value="<?php print $_REQUEST["rev"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to delete this revision?") ?>
            <div>
                <b>
                    <?php print t("Revision:") ?>
                    <?php
                        print t(date("F", intval($revision)))
                            . " "
                            . date("d, Y (h:i:s a)", intval($revision))
                        ;
                    ?>
                </b>
            </div>
        </div>
        <input
            class="form-submit"
            type="submit"
            name="btnYes"
            value="<?php print t("Yes") ?>"
        />
        <input
            class="form-submit"
            type="submit"
            name="btnNo"
            value="<?php print t("No") ?>"
        />
    </form>
    field;

    field: is_system
        1
    field;
row;
