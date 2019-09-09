<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Polls Vote") ?>
    field;

    field: content
    <?php
        if (isset($_REQUEST["uri"])) {
            if (
                !isset($_COOKIE["poll"][$_REQUEST["uri"]]) &&
                !poll_expired($_REQUEST["uri"])
            ) {
                $page_data = Jaris\Pages::get($_REQUEST["uri"]);

                if ($page_data["type"] == "poll") {
                    $page_data["option_value"] = unserialize(
                        $page_data["option_value"]
                    );

                    $page_data["option_value"][$_REQUEST["id"]] += 1;

                    $page_data["option_value"] = serialize(
                        $page_data["option_value"]
                    );

                    Jaris\Pages::edit($_REQUEST["uri"], $page_data);

                    setcookie(
                        "poll[{$_REQUEST['uri']}]",
                        "1",
                        time() + ((((60 * 60) * 24) * 365) * 10),
                        "/"
                    );

                    Jaris\View::addMessage(t("Your vote was successfully submitted."));
                }
            } else {
                Jaris\View::addMessage(t("You have already voted!"), "error");
            }

            if (isset($_REQUEST["actual_uri"])) {
                Jaris\Uri::go($_REQUEST["actual_uri"]);
            } else {
                Jaris\Uri::go($_REQUEST["uri"]);
            }
        } else {
            Jaris\Uri::go("");
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
