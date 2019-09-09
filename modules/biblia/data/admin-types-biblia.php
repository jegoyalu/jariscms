<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Configuraciones de Biblia
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("view_types", "edit_types"));

        //Get exsiting settings or defualt ones if main settings table doesn't exist
        $settings = biblia_get_settings($_REQUEST["type"]);

        if(isset($_REQUEST["btnSave"]))
        {
            $data = array(
                "enabled" => $_REQUEST["enabled"],
                "biblia" => $_REQUEST["biblia"]
            );

            //Check if write is possible and continue to write settings
            if(Jaris\Settings::save($_REQUEST["type"], serialize($data), "biblia"))
            {
                Jaris\View::addMessage("Tus configuraciones fueron guardadas.");
            }
            else
            {
                Jaris\View::addMessage(Jaris\System::errorMessage("write_error_data"), "error");
            }

            Jaris\Uri::go("admin/types/edit", array("type" => $_REQUEST["type"]));
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go("admin/types/edit", array("type" => $_REQUEST["type"]));
        }

        Jaris\View::addTab(
            t("Edit Type"),
            "admin/types/edit",
            array("type" => $_REQUEST["type"])
        );

        $parameters["name"] = "edit-biblia-settings";
        $parameters["class"] = "edit-biblia-settings";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "type",
            "value" => $_REQUEST["type"]
        );

        $enabled[t("Enable")] = true;
        $enabled[t("Disable")] = false;

        $fields[] = array(
            "type" => "radio",
            "name" => "enabled",
            "id" => "enabled",
            "value" => $enabled,
            "checked" => $settings["enabled"],
            "description" => "Convertir versículos en enlaces que lleven a la sección de biblia."
        );

        $biblias = biblia_get_all();
        $biblias_list = array();

        foreach($biblias as $biblia => $biblia_data)
        {
            $biblias_list[$biblia_data["codigo"]] = $biblia;
        }

        $fields[] = array(
            "type" => "select",
            "selected" => $settings["biblia"],
            "name" => "biblia",
            "value" => $biblias_list,
            "label" => t("Versión:"),
            "id" => "biblia"
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnSave",
            "value" => t("Save")
        );

        $fields[] = array(
            "type" => "submit",
            "name" => "btnCancel",
            "value" => t("Cancel")
        );

        $fieldset[] = array("fields" => $fields);

        print Jaris\Forms::generate($parameters, $fieldset);
    ?>
    field;

    field: is_system
        1
    field;
row;
