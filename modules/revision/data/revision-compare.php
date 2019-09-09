<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the revisions full compare page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Revisions Comparison") ?>
    field;

    field: content
    <?php
        $rev1 = intval($_REQUEST["rev1"]);
        $rev2 = intval($_REQUEST["rev2"]);

        if(
            !isset($_REQUEST["uri"]) ||
            trim($_REQUEST["uri"]) == "" ||
            !file_exists(Jaris\Pages::getPath($_REQUEST["uri"]) . "/data.php")
        )
        {
            Jaris\Uri::go("access-denied");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        Jaris\Authentication::protectedPage(array("view_revisions"));

        $revisions_path = Jaris\Pages::getPath($_REQUEST["uri"]) . "/revisions";

        if(!file_exists($revisions_path))
        {
            Jaris\View::addMessage(t("No revisions found."));
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        if(
            !file_exists($revisions_path . "/" . $rev1 . ".php") ||
            !file_exists($revisions_path . "/" . $rev2 . ".php")
        )
        {
            Jaris\View::addMessage(t("Invalid revisions."), "error");
            Jaris\Uri::go($_REQUEST["uri"]);
        }

        $page_data = Jaris\Pages::get(
            $_REQUEST["uri"],
            Jaris\Language::getCurrent()
        );

        // Add Edit tab if current user has proper permissions
        if(
            Jaris\Authentication::groupHasPermission(
                "edit_content",
                Jaris\Authentication::currentUserGroup()
            )
            &&
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

        // Add additional tabs
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
        $form_url = Jaris\Uri::url(
            Jaris\Modules::getPageUri("revision/compare", "revision")
        );
        print "<form action=\"" . $form_url . "\" method=\"GET\">";
        print "<input "
            . "type=\"hidden\" "
            . "name=\"uri\" "
            . "value=\"" . $_REQUEST["uri"] . "\""
            . " />"
        ;

        $options1 = "";
        foreach($revisions as $revision)
        {
            $revision = str_replace(
                array($revisions_path . "/", ".php"),
                "",
                $revision
            );

            $date = t(date("F", intval($revision)))
                . " "
                . date("d, Y (h:i:s a)", intval($revision))
            ;

            $selected = "";

            if($rev1 == $revision)
            {
                $selected = "selected=\"selected\"";
            }

            $options1 .= "<option $selected value=\"$revision\">$date</option>";
        }

        print "<b>" . t("Older:") . "</b>&nbsp;";
        print "<select name=\"rev1\">";
        print $options1;
        print "</select>&nbsp;";

        $options2 = "";
        foreach($revisions as $revision)
        {
            $revision = str_replace(
                array($revisions_path . "/", ".php"),
                "",
                $revision
            );

            $date = t(date("F", intval($revision)))
                . " "
                . date("d, Y (h:i:s a)", intval($revision))
            ;

            $selected = "";

            if($rev2 == $revision)
                $selected = "selected=\"selected\"";

            $options2 .= "<option $selected value=\"$revision\">$date</option>";
        }

        print "<b>" . t("Newer:") . "</b>&nbsp;";
        print "<select name=\"rev2\">";
        print $options2;
        print "</select>&nbsp;";

        print "<input type=\"submit\" "
            . "name=\"btnCompare\" "
            . "value=\"" . t("Compare") . "\""
            . "/>"
        ;
        print "</form>";

        print "<hr />";

        // Display comparison
        Jaris\View::addStyle(
            Jaris\Modules::directory("revision") . "styles/file.css"
        );

        $rev1_file = $revisions_path . "/$rev1.php";
        $rev2_file = $revisions_path . "/$rev2.php";

        print revision_diff_file($rev1_file, $rev2_file);
    ?>
    field;

    field: is_system
        1
    field;
row;
