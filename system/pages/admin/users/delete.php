<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the user delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete User") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
    ["view_users", "delete_users"]
        );

        if (empty($_REQUEST["username"])) {
            Jaris\Uri::go("admin/users/list");
        }

        $arguments["username"] = $_REQUEST["username"];

        Jaris\View::addTab(t("Edit"), "admin/users/edit", $arguments);

        $user_data = Jaris\Users::get($_REQUEST["username"]);

        if (isset($user_data["superadmin"]) && $user_data["superadmin"]) {
            Jaris\View::addMessage(t("Can not delete a super admin user."));

            Jaris\Uri::go("admin/users/list");
        }

        if (isset($_REQUEST["btnYes"])) {
            if (Jaris\Users::delete($_REQUEST["username"])) {
                Jaris\View::addMessage(t("User successfully deleted."));

                t("Deleted user '{username}'.");

                Jaris\Logger::info(
                    "Deleted user '{username}'.",
                    [
                        "username" => $_REQUEST["username"]
                    ]
                );
            } else {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }

            Jaris\Uri::go("admin/users/list");
        } elseif (isset($_REQUEST["btnNo"])) {
            Jaris\Uri::go("admin/users/edit", $arguments);
        }
    ?>

    <form class="user-delete" method="post"
          action="<?php Jaris\Uri::url("admin/users/delete") ?>"
    >
        <input type="hidden" name="username"
               value="<?php print $_REQUEST["username"] ?>"
        />
        <br />
        <div>
            <?php
                print t("This action will also delete all users content.") .
                    " " . t("Are you sure you want to delete the user?")
            ?>
            <div>
                <b>
                    <?php print t("Username:") ?>
                    <?php print $_REQUEST["username"] ?>
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
