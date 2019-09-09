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
        print t("Mass Content Approve");
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage();

        if (!isset($_REQUEST["type"])) {
            $_REQUEST["type"] = "";
        }

        if (!isset($_REQUEST["author"])) {
            $_REQUEST["author"] = "";
        }

        if (!isset($_REQUEST["pages"]) || count($_REQUEST["pages"]) <= 0) {
            Jaris\View::addMessage(
                t("Please select the content to approve."),
                "error"
            );

            Jaris\Uri::go(
                "admin/pages/approve",
                [
                    "type" => $_REQUEST["type"],
                    "author" => $_REQUEST["author"]
                ]
            );
        }

        if (isset($_REQUEST["btnYes"])) {
            foreach ($_REQUEST["pages"] as $page_uri) {
                //Approve page
                if (!Jaris\Pages::approve($page_uri)) {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );

                    Jaris\Uri::go(
                        "admin/pages/approve",
                        [
                            "type" => $_REQUEST["type"],
                            "author" => $_REQUEST["author"]
                        ]
                    );
                }
            }

            Jaris\View::addMessage(t("Content successfully approved."));

            Jaris\Uri::go(
                "admin/pages/approve",
                [
                    "type" => $_REQUEST["type"],
                    "author" => $_REQUEST["author"]
                ]
            );
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go(
                "admin/pages/approve",
                [
                    "type" => $_REQUEST["type"],
                    "author" => $_REQUEST["author"]
                ]
            );
        }
    ?>

    <form class="mass-content-approve" method="post"
          action="<?php Jaris\Uri::url("admin/pages/mass-approve") ?>"
    >
        <input type="hidden" name="type" value="<?php print $_REQUEST["type"] ?>" />
        <input type="hidden" name="author" value="<?php print $_REQUEST["author"] ?>" />
    <?php foreach ($_REQUEST["pages"] as $page_uri) { ?>
        <input type="hidden" name="pages[]" value="<?php print $page_uri ?>" />
    <?php } ?>

        <br />

        <div>
            <?php print t("Are you sure you want to approve the listed content?") ?>
            <ul>
            <?php foreach ($_REQUEST["pages"] as $page_uri) { ?>
                <li>
                    <a target="_blank" href="<?php print Jaris\Uri::url($page_uri) ?>">
                        <?php
                            $page_data = Jaris\Pages::get($page_uri);
                            print $page_data["title"];
                        ?>
                    </a>
                </li>
            <?php } ?>
            </ul>
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
