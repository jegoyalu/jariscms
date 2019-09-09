<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the input format delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
    <?php print t("Delete Input Format") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(["delete_input_formats"]);

        if (!isset($_REQUEST["input_format"])) {
            Jaris\Uri::go("admin/input-formats");
        }

        $input_format_data = Jaris\InputFormats::get($_REQUEST["input_format"]);

        if (isset($_REQUEST["btnYes"])) {
            $message = Jaris\InputFormats::delete($_REQUEST["input_format"]);

            if ($message == "true") {
                Jaris\View::addMessage(t("Input format successfully deleted."));

                t("Deleted input format '{machine_name}'.");

                Jaris\Logger::info(
                    "Deleted input format '{machine_name}'.",
                    [
                        "machine_name" => $_REQUEST["input_format"]
                    ]
                );
            } else {
                Jaris\View::addMessage($message, "error");
            }

            Jaris\Uri::go("admin/input-formats");
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go("admin/input-formats");
        }
    ?>

    <form class="input-format-delete" method="post"
          action="<?php Jaris\Uri::url("admin/input-formats/delete") ?>"
    >
        <input type="hidden" name="input_format"
               value="<?php print $_REQUEST["input_format"] ?>"
        />
        <br />
        <div>
            <?php print t("Are you sure you want to delete the input format?") ?>
            <div>
                <b>
                    <?php print t("Input format:") ?>
                    <?php print t($input_format_data["name"]) ?>
                </b>
            </div>
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
