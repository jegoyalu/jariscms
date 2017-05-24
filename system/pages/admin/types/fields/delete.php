<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the type delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Content Type Field") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_types_fields", "delete_types_fields")
        );

        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["type_name"]))
        {
            Jaris\Uri::go("admin/types");
        }

        $field_id = intval($_REQUEST["id"]);

        $field_data = Jaris\Fields::get(
            $field_id,
            $_REQUEST["type_name"]
        );

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Fields::delete($field_id, $_REQUEST["type_name"]))
            {
                Jaris\View::addMessage(t("Type field successfully deleted."));

                t("Delete field '{name}' from content type '{machine_name}'.");

                Jaris\Logger::info(
                    "Deleted field '{name}' from content type '{machine_name}'.",
                    array(
                        "name" => $field_data["name"],
                        "machine_name" => $_REQUEST["type_name"]
                    )
                );
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/types/fields", array(
                "type" => $_REQUEST["type_name"])
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/types/fields", array(
                "type" => $_REQUEST["type_name"])
            );
        }
    ?>

    <form class="type-field-delete" method="post"
          action="<?php Jaris\Uri::url("admin/types/fields/delete") ?>"
    >
        <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
        <input type="hidden" name="type_name" value="<?php print $_REQUEST["type_name"] ?>" />
        <br />
        <div>
            <?php print t("Are you sure you want to delete the field?") ?>
            <div>
                <b>
                    <?php print t("Field:") ?>
                    <?php print t($field_data["name"]) ?>
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
