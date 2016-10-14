<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the users export page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Export Users List") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_users"));

        Jaris\View::addTab(t("Navigation View"), "admin/users");
        Jaris\View::addTab(t("List View"), "admin/users/list");
        Jaris\View::addTab(t("Create User"), "admin/users/add");
        Jaris\View::addTab(t("Groups"), "admin/groups");
        Jaris\View::addTab(t("Export"), "admin/users/export");

        $users_csv = Jaris\Site::dataDir() . "users/users.csv";

        if(file_exists($users_csv))
        {
            Jaris\View::addTab(
                t("Download Last Generated"),
                "admin/users/export",
                array("download" => 1),
                1
            );
        }

        $page = 1;

        if(isset($_REQUEST["btnYes"]))
        {
            $file = fopen($users_csv, "w");

            if($file)
            {
                ini_set('max_execution_time', '0');

                fputs(
                    $file,
                    "username,email,register_date,user_group,"
                        . "picture,ip_address,gender,birth_date,"
                        . "register_date_readable\n"
                );

                $db = Jaris\Sql::open("users");
                $select = "select * from users";
                $result = Jaris\Sql::query($select, $db);

                while($data = Jaris\Sql::fetchArray($result))
                {
                    $data["register_date_readable"] = date(
                        "m/d/Y g:i:s a",
                        $data["register_date"]
                    );

                    fputcsv($file, $data, ",", "\"");
                }

                fclose($file);
                Jaris\Sql::close($db);

                Jaris\FileSystem::printFile($users_csv, "users.csv", true, true);
            }
            else
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }
        }
        elseif(isset($_REQUEST["download"]))
        {
            Jaris\FileSystem::printFile($users_csv, "users.csv", true, true);
        }
        elseif(isset($_REQUEST["btnNo"]))
        {
            Jaris\Uri::go("admin/users");
        }
    ?>

    <form class="export_users_list" method="post"
          action="<?php Jaris\Uri::url("admin/users/export") ?>"
    >
        <div>
            <?php print t("The process of creating a csv file of the users database could take a long time.<br />Do you want to the generate export file?") ?>
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
