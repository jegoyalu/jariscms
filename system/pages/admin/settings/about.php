<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the jariscms about page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("About JarisCMS") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage();

        print "<b>" . t("You are using Jaris CMS Version:") . "</b> ";

        print Jaris\System::VERSION;

        print "<p>";
        print sprintf(
            t("Copyright &copy; 2008 - %s, All Rights Reserved by JegoYalu."),
            date("Y", time())
        );

        print " " . t("JarisCMS is developed by JegoYalu");

        print " <a target=\"_blank\" href=\"http://jegoyalu.com\">(jegoyalu.com)</a>";

        print " " . t("and is under the GPL license") . " ";

        print "<a target=\"_blank\" href=\"https://opensource.org/licenses/GPL-3.0\">"
            . "(https://opensource.org/licenses/GPL-3.0)"
            . "</a>"
        ;
        print "</p>";

        print "<p>";
        print t("For more information about JarisCMS visit:") . " ";
        print " <a target=\"_blank\" href=\"http://jariscms.com\">http://jariscms.com</a>";
        print "</p>";

        print "<h3>" . t("Authors") . "</h3>";

        print "<textarea readonly style=\"width: 100%; min-height: 80px;\">"
            . "Programming: Jefferson González - (jegoyalu.com)\n"
            . "Graphics: Yaritza Luyando - (jegoyalu.com)"
            . "</textarea>"
        ;

        if(file_exists("changes.txt"))
        {
            print "<h3>" . t("Changes Log") . "</h3>";

            print "<textarea readonly style=\"width: 100%; min-height: 400px;\">";
            $contents = file("changes.txt");
            unset($contents[0]);
            unset($contents[1]);
            print implode("", $contents);
            print "</textarea>";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
