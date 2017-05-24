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
        print t("Delete Selected Files");
    ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("delete_files"));

        if(!isset($_REQUEST["uri"]))
        {
            Jaris\Uri::go("");
        }

        if(!Jaris\Pages::userIsOwner($_REQUEST["uri"]))
        {
            Jaris\Authentication::protectedPage();
        }

        if(!isset($_REQUEST["files"]) || count($_REQUEST["files"]) <= 0)
        {
            Jaris\View::addMessage(
                t("Please select the files to delete."),
                "error"
            );

            Jaris\Uri::go(
                "admin/pages/files",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }

        if(isset($_REQUEST["btnYes"]))
        {
            foreach($_REQUEST["files"] as $file_id)
            {
                //Delete page
                if(
                    !Jaris\Pages\Files::delete(
                        intval($file_id),
                        $_REQUEST["uri"]
                    )
                )
                {
                    Jaris\View::addMessage(
                        Jaris\System::errorMessage("write_error_data"),
                        "error"
                    );

                    Jaris\Uri::go(
                        "admin/pages/files",
                        array(
                            "uri" => $_REQUEST["uri"]
                        )
                    );
                }
            }

            Jaris\View::addMessage(t("Files successfully deleted."));

            Jaris\Uri::go(
                "admin/pages/files",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                "admin/pages/files",
                array(
                    "uri" => $_REQUEST["uri"]
                )
            );
        }

        $files = Jaris\Pages\Files::getList($_REQUEST["uri"])
    ?>

    <form class="files-select-delete" method="post"
          action="<?php Jaris\Uri::url("admin/pages/files/delete-selected") ?>"
    >
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
    <?php foreach($_REQUEST["files"] as $file_id){ ?>
        <input type="hidden" name="files[]" value="<?php print $file_id ?>" />
    <?php } ?>

        <br />

        <div>
            <?php print t("Are you sure you want to delete the listed files?") ?>
            <ul>
            <?php foreach($_REQUEST["files"] as $file_id){ ?>
                <li style="padding-bottom: 3px;">
                    <a
                        target="_blank"
                        href="<?php print Jaris\Uri::url("file/{$_REQUEST["uri"]}/" . $file_id) ?>"
                    >
                        <?php print $files[$file_id]["name"]; ?>
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
