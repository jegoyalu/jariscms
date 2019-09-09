<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the administration page for lightbox.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Popups
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        Jaris\View::addTab(
            t("Add"),
            Jaris\Modules::getPageUri("admin/settings/popup/add", "popup")
        );

        $popups = Jaris\Data::parse(
            Jaris\Site::dataDir() . "settings/popup.php"
        );
    ?>

    <?php if(count($popups) > 0){ ?>
    <table class="popups-list navigation-list">
        <thead>
            <tr>
                <td><?php print t("Description") ?></td>
                <td><?php print t("Actions") ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($popups as $popup_id=>$popup){ ?>
            <tr>
                <td><?php print $popup["description"] ?></td>
                <td>
                    <?php
                        $edit_url = Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/popup/edit",
                                "popup"
                            ),
                            array("id"=>$popup_id)
                        );

                        $delete_url = Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/popup/delete",
                                "popup"
                            ),
                            array("id"=>$popup_id)
                        );
                    ?>
                    <a href="<?php print $edit_url ?>">
                        <?php print t("Edit") ?>
                    </a>
                    <a href="<?php print $delete_url ?>">
                        <?php print t("Delete") ?>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else{ ?>
        <h3><?php print t("Click add to create a new popup.") ?></h3>
    <?php } ?>
    field;

    field: is_system
        1
    field;
row;
