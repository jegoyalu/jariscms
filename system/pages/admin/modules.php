<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the modules management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Modules") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_modules"));

        Jaris\View::addTab(t("Upload"), "admin/modules/upload");

        print "<table class=\"modules-list\">\n";

        print "<thead><tr>\n";

        print "<td>" . t("Name") . "</td>\n";
        print "<td>" . t("Status") . "</td>\n";
        print "<td>" . t("Operation") . "</td>\n";
        print "<td>" . t("Dependencies") . "</td>\n";

        print "</tr></thead>\n";

        $modules = Jaris\Modules::getAll();

        foreach($modules as $module_path => $module_info)
        {
            $title = t("View module info.");

            $more_url = Jaris\Uri::url(
                "admin/modules/view",
                array("path" => $module_path)
            );

            $installed_version = Jaris\Modules::getInstalledVersion($module_path);

            print "<tr>\n";

            print "<td><a title=\"$title\" href=\"$more_url\">{$module_info['name']}</a></td>\n";

            print "<td>";
            if(Jaris\Modules::isInstalled($module_path))
            {
                print t("Enabled");
                print "<br />" . t("Version installed:") . " " . $installed_version;

                if($installed_version < $module_info["version"])
                {
                    print "<br />" . t("Actual version:") . " " . $module_info["version"];
                }
            }
            else
            {
                print t("Disabled");
                print "<br />" . t("Version:") . " " . $module_info["version"];
            }
            print "</td>\n";

            print "<td>";
            if(!Jaris\Modules::isInstalled($module_path))
            {
                print "<a href=\"" .
                    Jaris\Uri::url(
                        "admin/modules/install",
                        array("path" => $module_path)
                    ) . "\">" .
                    t("Install") .
                    "</a>"
                ;

                if(Jaris\Modules::directory($module_path) != "modules/$module_path/")
                {
                    print "&nbsp;<a href=\"" .
                        Jaris\Uri::url(
                            "admin/modules/delete",
                            array("path" => $module_path)
                        ) . "\">" .
                        t("Delete") .
                        "</a>"
                    ;
                }
            }
            else
            {
                print "<a href=\"" .
                    Jaris\Uri::url(
                        "admin/modules/uninstall",
                        array("path" => $module_path)
                    ) . "\">" .
                    t("Uninstall") .
                    "</a>"
                ;

                if($installed_version < $module_info["version"])
                {
                    print "&nbsp;<a href=\"" .
                        Jaris\Uri::url(
                            "admin/modules/upgrade",
                            array("path" => $module_path)
                        ) . "\">" .
                        t("Upgrade") .
                        "</a>"
                    ;
                }
            }
            print "</td>\n";

            print "<td>";
            if(isset($module_info["dependencies"]))
            {
                $dependencies = "";
                foreach($module_info["dependencies"] as $dependency_name)
                {
                    $dependency_data = Jaris\Modules::get($dependency_name);

                    if($dependency_data)
                    {
                        $dependencies .= $dependency_data["name"] . ", ";
                    }
                    else
                    {
                        $dependencies .= $dependency_name . ", ";
                    }

                    unset($dependency_data);
                }

                print trim($dependencies, ", ");
            }
            print "</td>\n";

            print "</tr>\n";
        }

        print "</table>"
    ?>
    field;

    field: is_system
        1
    field;
row;
