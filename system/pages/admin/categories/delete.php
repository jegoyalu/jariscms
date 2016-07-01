<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the categories delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Category") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_categories", "delete_categories")
        );

        if(!isset($_REQUEST["category"]))
        {
            Jaris\Uri::go("admin/categories");
        }

        $category_data = Jaris\Categories::get($_REQUEST["category"]);

        if(isset($_REQUEST["btnYes"]))
        {
            if(Jaris\Categories::delete($_REQUEST["category"]))
            {
                Jaris\View::addMessage(t("Category successfully deleted."));
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/categories");
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/categories");
        }
    ?>

    <form class="categorye-delete" method="post"
          action="<?php Jaris\Uri::url("admin/categories/delete") ?>"
    >
        <input type="hidden" name="category"
               value="<?php print $_REQUEST["category"] ?>"
        />
        <br />
        <div>
            <?php print t("Are you sure you want to delete the category?") ?>
            <div>
                <b>
                    <?php print t("Category:") ?>
                    <?php print t($category_data["name"]) ?>
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
