<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the revisions revert page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Revert to Revision"); ?>
    field;

    field: content
    <?php
        $_REQUEST["rev"] = intval($_REQUEST["rev"]);

        $revision = $_REQUEST["rev"];

        $revision_file = Jaris\Pages::getPath($_REQUEST["uri"])
            . "/revisions/" . $revision . ".php"
        ;

        if(
            !isset($_REQUEST["uri"]) ||
            !isset($_REQUEST["rev"]) ||
            trim($_REQUEST["uri"]) == "" ||
            trim($_REQUEST["rev"]) == "" ||
            !file_exists(Jaris\Pages::getPath($_REQUEST["uri"]) . "/data.php") ||
            !file_exists($revision_file)
        )
        {
            Jaris\Uri::go("access-denied");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        Jaris\Authentication::protectedPage(array("revert_revisions"));


        if(isset($_REQUEST["btnYes"]))
        {
            $revision_data = Jaris\Data::get(0, $revision_file);
            $page_data_path = Jaris\Pages::getPath($_REQUEST["uri"]) . "/data.php";

            if(Jaris\Data::edit(0, $revision_data, $page_data_path))
            {
                Jaris\View::addMessage(t("Revert to revision was successfull."));
                Jaris\Uri::go($_REQUEST["uri"]);
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");

                Jaris\Uri::go(
                    Jaris\Modules::getPageUri("revisions", "revision"),
                    array("uri" => $_REQUEST["uri"])
                );
            }
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri("revisions", "revision"),
                array("uri" => $_REQUEST["uri"])
            );
        }
    ?>

    <form class="revision-revert" method="post"
          action="<?php Jaris\Uri::url(Jaris\Modules::getPageUri("revision/revert", "revision")) ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <input type="hidden" name="rev" value="<?php print $_REQUEST["rev"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to revert to this revision?") ?>
            <div>
                <b>
                    <?php print t("Revision:") ?>
                    <?php print t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision) ?>
                </b>
            </div>
        </div>
        <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
        <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
    </form>
    field;

    field: is_system
        1
    field;
row;
