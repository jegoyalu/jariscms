<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * @note File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Groups::SIGNAL_SET_GROUP_PERMISSION,
    function(&$permissions)
    {
        $import = array(
            "import_content_importer"=>t("Import Content")
        );

        $import = array(
            "export_content_importer"=>t("Export Content")
        );

        $permissions[t("Importing/Exporting")] = $import;
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        $content = array();

        if(
            Jaris\Authentication::groupHasPermission(
                "import_content_importer", 
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $content[] = array(
                "title" => t("Import Content"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/import", "importer"
                    )
                ),
                "description" => t("Import content into the system by reading a csv file.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "export_content_importer", 
                Jaris\Authentication::currentUserGroup()
            )
        )
        {
            $content[] = array(
                "title" => t("Export Content"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/pages/export", "importer"
                    )
                ),
                "description" => t("Export content from the system into a csv file.")
            );
        }

        if(count($content) > 0)
        {
            foreach($sections as $section_index => $section_data)
            {
                if($section_data["class"] == "content")
                {
                    foreach($content as $section)
                    {
                        $sections[$section_index]["sub_sections"][] = $section;
                    }

                    break;
                }
            }
        }
    }
);