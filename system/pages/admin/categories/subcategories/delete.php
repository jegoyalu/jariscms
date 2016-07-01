<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the delete subcategory page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Subcategory") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_subcategories", "delete_subcategories")
        );

        if(!isset($_REQUEST["id"]) || !isset($_REQUEST["category"]))
        {
            Jaris\Uri::go("admin/categories");
        }

        $subcategory_data = Jaris\Categories::getSubcategory(
            $_REQUEST["category"],
            $_REQUEST["id"]
        );

        if(isset($_REQUEST["btnYes"]))
        {
            if(
                Jaris\Categories::deleteSubcategory(
                    $_REQUEST["category"],
                    $_REQUEST["id"]
                )
            )
            {
                Jaris\View::addMessage(t("Subcategory successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go(
                "admin/categories/subcategories",
                array("category" => $_REQUEST["category"])
            );
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go(
                "admin/categories/subcategories",
                array("category" => $_REQUEST["category"])
            );
        }
    ?>

    <form class="subcategories-delete" method="post"
          action="<?php Jaris\Uri::url("admin/categories/subcategories/delete") ?>"
    >
        <input type="hidden" name="id"
               value="<?php print $_REQUEST["id"] ?>"
        />
        <input type="hidden" name="category"
               value="<?php print $_REQUEST["category"] ?>"
        />
        <div>
            <?php print t("Are you sure you want to delete the subcategory?") ?>
            <div>
                <b>
                    <?php print t("Title:") ?>
                    <?php print t($subcategory_data["title"]) ?>
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
