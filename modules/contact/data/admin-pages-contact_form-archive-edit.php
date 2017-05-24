<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the content delete apikey page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Archived Message on Contact Form") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_content"));

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        $message = contact_archive_message_get($_REQUEST["id"]);

        if(empty($message))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                array("uri" => $_REQUEST["uri"])
            );
        }

        $arguments["uri"] = $_REQUEST["uri"];
        $page_data = Jaris\Pages::get($_REQUEST["uri"]);

        //Tabs
        if(Jaris\Authentication::groupHasPermission("edit_content", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Edit"), "admin/pages/edit", $arguments);
        }
        Jaris\View::addTab(t("View"), $_REQUEST["uri"]);
        if(Jaris\Authentication::groupHasPermission("view_content_blocks", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Blocks"), "admin/pages/blocks", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("view_images", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Images"), "admin/pages/images", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("view_files", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Files"), "admin/pages/files", $arguments);
        }
        if(Jaris\Authentication::groupHasPermission("translate_languages", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Translate"), "admin/pages/translate", $arguments);
        }
        if($page_data["message_archive"])
        {
            Jaris\View::addTab(
                t("Messages Archive"),
                Jaris\Modules::getPageUri(
                    "admin/pages/contact-form/archive",
                    "contact"
                ),
                $arguments
            );
        }
        if(Jaris\Authentication::groupHasPermission("delete_content", Jaris\Authentication::currentUserGroup()))
        {
            Jaris\View::addTab(t("Delete"), "admin/pages/delete", $arguments);
        }

        print $message["message"];

        if(!empty($message["attachments"]))
        {
            print "<h3>" . t("Attachments") . "</h3>";

            print "<ul>";
            foreach($message["attachments"] as $attachment)
            {
                $url = print_url(
                    Jaris\Files::get(
                        $attachment,
                        "contact/" . str_replace("/", "-", $_REQUEST["uri"])
                    )
                );

                print "<li>"
                    . "<a target=\"_blank\" href=\"$url\">"
                    . $attachment
                    . "</a>"
                    . "</li>"
                ;
            }
            print "</ul>";
        }
    ?>
    field;

    field: is_system
        1
    field;
row;
