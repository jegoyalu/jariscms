<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the modules install page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Install Module") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(
            array("view_modules", "install_modules")
        );

        if(file_exists(Jaris\Site::dataDir() . "data_cache"))
        {
            Jaris\View::addMessage(
                t("Data caching is enabled, please disable it first."),
                "error"
            );
        }
        elseif(isset($_REQUEST["path"]))
        {
            $needs_dependency = false;
            if(Jaris\Modules::install($_REQUEST["path"], $needs_dependency))
            {
                Jaris\View::addMessage(t("Module successfully installed."));
            }
            else if(!$needs_dependency)
            {
                Jaris\View::addMessage(
                    Jaris\System::errorMessage("write_error_data"),
                    "error"
                );
            }
        }

        Jaris\Uri::go("admin/modules");
    ?>
    field;

    field: is_system
        1
    field;
row;