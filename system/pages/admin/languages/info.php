<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the language info view page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Language Info") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_languages"));

        $info = null;

        if(isset($_REQUEST["code"]))
        {
            $info = Jaris\Language::getInfo($_REQUEST["code"]);
        }
        else
        {
            Jaris\Uri::go("admin/languages");
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "edit_languages",
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            Jaris\View::addTab(
                t("Edit Info"),
                "admin/languages/edit-info",
                array("code" => $_REQUEST["code"])
            );
        }
    ?>

    <div class="language-info">
        <div class="info">
            <div>
                <span class="label"><?php print t("Name:") ?></span>
                <?php print t($info["name"]) ?>
            </div>

            <div>
                <span class="label"><?php print t("Code:") ?></span>
                <?php print t($info["code"]) ?>
            </div>

            <div>
                <span class="label"><?php print t("Translator:") ?></span>
                <?php print t($info["translator"]) ?>
            </div>

            <div>
                <span class="label"><?php print t("E-mail:") ?></span>
                <a href="mailto:<?php print $info["translator_email"] ?>">
                    <?php print $info["translator_email"] ?>
                </a>
            </div>

            <?php if(trim($info["contributors"]) != ""){ ?>
                <hr />
                <div>
                    <span class="label"><?php print t("Contributors:") ?></span>
                    <br />
                    <?php print str_replace("\n", "<br />\n", $info["contributors"]) ?>
                </div>
            <?php } ?>
        </div>
    </div>
    field;

    field: is_system
        1
    field;
row;
