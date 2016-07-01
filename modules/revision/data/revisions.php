<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the revisions list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Revisions") ?>
    field;

    field: content
    <?php
        if(
            !isset($_REQUEST["uri"]) ||
            trim($_REQUEST["uri"]) == "" ||
            !file_exists(Jaris\Pages::getPath($_REQUEST["uri"]) . "/data.php")
        )
            Jaris\Uri::go("access-denied");

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
            Jaris\Authentication::protectedPage();

        Jaris\Authentication::protectedPage(array("view_revisions"));

        $revisions_path = Jaris\Pages::getPath($_REQUEST["uri"]) . "/revisions";

        if(!file_exists($revisions_path))
        {
            Jaris\View::addMessage(t("No revisions found."));
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        $page_data = Jaris\Pages::get($_REQUEST["uri"], Jaris\Language::getCurrent());

        // Add Edit/View tabs if current user has proper permissions
        if(
            Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup()) &&
            !trim($page_data["is_system"])
        )
        {
            if(Jaris\Pages::userIsOwner($_REQUEST["uri"]))
            {
                Jaris\View::addTab(
                    t("Edit"),
                    "admin/pages/edit",
                    array("uri" => $_REQUEST["uri"])
                );
            }
        }

        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);

        Jaris\View::addTab(
            t("Revisions"),
            Jaris\Modules::getPageUri("revisions", "revision"),
            array("uri" => $_REQUEST["uri"])
        );

        $revisions = Jaris\FileSystem::getFiles($revisions_path);
        rsort($revisions);

        print "<h2>" . t("Page:") . " " . $page_data["title"] . "</h2>";

        // Display comparison chooser form
        if(count($revisions) > 1)
        {
            print "<form action=\"" .
                Jaris\Uri::url(
                    Jaris\Modules::getPageUri("revision/compare", "revision")
                ) . "\" method=\"GET\">"
            ;

            print "<input type=\"hidden\" name=\"uri\" value=\"" . $_REQUEST["uri"] . "\">";

            $options = "";
            foreach($revisions as $revision)
            {
                $revision = str_replace(
                    array($revisions_path . "/", ".php"),
                    "",
                    $revision
                );

                $date = t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision);

                $options .= "<option value=\"$revision\">$date</option>";
            }

            print "<b>" . t("Older:") . "</b>&nbsp;";
            print "<select name=\"rev1\">";
            print $options;
            print "</select>&nbsp;";

            print "<b>" . t("Newer:") . "</b>&nbsp;";
            print "<select name=\"rev2\">";
            print $options;
            print "</select>&nbsp;";

            print "<input type=\"submit\" name=\"btnCompare\" value=\"" . t("Compare") . "\">";
            print "</form>";

            print "<hr />";
        }

        if(count($revisions) > 0)
        {
            print "<table class=\"navigation-list\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Date") . "</td>";
            print "<td colspan=\"3\">" . t("Action") . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";

            foreach($revisions as $revision)
            {
                $revision = str_replace(
                    array($revisions_path . "/", ".php"),
                    "",
                    $revision
                );

                print "<tr>";

                print "<td>"
                    . t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision)
                    . "</td>"
                ;

                // View
                print "<td>" .
                    "<a href=\"" .
                    Jaris\Uri::url(
                        $_REQUEST["uri"], array("rev" => $revision)
                    ) . "\">" .
                    t("View") .
                    "</a></td>"
                ;

                // Select
                print "<td>" .
                    "<a href=\"" .
                    Jaris\Uri::url(
                        Jaris\Modules::getPageUri("revision/revert", "revision"),
                        array("uri" => $_REQUEST["uri"], "rev" => $revision)
                    ) . "\">" .
                    t("Revert") .
                    "</a></td>"
                ;

                // Delete
                print "<td>" .
                    "<a href=\"" .
                    Jaris\Uri::url(
                        Jaris\Modules::getPageUri("revision/delete", "revision"),
                        array("uri" => $_REQUEST["uri"], "rev" => $revision)
                    ) . "\">" .
                    t("Delete") .
                    "</a></td>"
                ;

                print "</tr>";
            }

            print "</tbody>";

            print "</table>";
        }
        else
        {
            Jaris\View::addMessage(t("No revisions found."));
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
