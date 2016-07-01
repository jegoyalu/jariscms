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
        <?php print t("Edit Tither") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("manage_tithers_church_accounting"));

        $tither_data = church_accounting_tither_get($_REQUEST["id"]);

        if(!is_array($tither_data))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }

        Jaris\View::addTab(
            t("Add Tithe"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/income/tithes/add",
                "church_accounting"
            ),
            array("tid" => $_REQUEST["id"])
        );

        Jaris\View::addTab(
            t("Reports"),
            Jaris\Modules::getPageUri(
                "admin/church-accounting/tithers/report",
                "church_accounting"
            ),
            array("id" => $_REQUEST["id"])
        );

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("edit-tither")
        )
        {
            $data = array(
                "first_name" => $_REQUEST["first_name"],
                "last_name" => $_REQUEST["last_name"],
                "maiden_name" => $_REQUEST["maiden_name"],
                "postal_address" => $_REQUEST["postal_address"],
                "email" => $_REQUEST["email"],
                "phone" => $_REQUEST["phone"],
                "mobile_phone" => $_REQUEST["mobile_phone"]
            );

            church_accounting_tither_edit($tither_data["id"], $data);

            Jaris\View::addMessage("Tither successfully edited.");

            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }
        elseif(isset($_REQUEST["btnCancel"]))
        {
            Jaris\Uri::go(
                Jaris\Modules::getPageUri(
                    "admin/church-accounting/tithers",
                    "church_accounting"
                )
            );
        }

        $parameters["name"] = "edit-tither";
        $parameters["class"] = "edit-tither";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "hidden",
            "name" => "id",
            "value" => $tither_data["id"]
        );

        $fields[] = array(
            "type" => "text",
            "name" => "first_name",
            "value" => isset($_REQUEST["first_name"]) ?
                $_REQUEST["first_name"]
                :
                $tither_data["first_name"],
            "label" => t("First name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "last_name",
            "value" => isset($_REQUEST["last_name"]) ?
                $_REQUEST["last_name"]
                :
                $tither_data["last_name"],
            "label" => t("Last name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "maiden_name",
            "value" => isset($_REQUEST["maiden_name"]) ?
                $_REQUEST["maiden_name"]
                :
                $tither_data["maiden_name"],
            "label" => t("Maiden name:")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "postal_address",
            "value" => isset($_REQUEST["postal_address"]) ?
                $_REQUEST["postal_address"]
                :
                $tither_data["postal_address"],
            "label" => t("Postal address:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "email",
            "value" => isset($_REQUEST["email"]) ?
                $_REQUEST["email"]
                :
                $tither_data["email"],
            "label" => t("E-mail:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "phone",
            "value" => isset($_REQUEST["phone"]) ?
                $_REQUEST["phone"]
                :
                $tither_data["phone"],
            "label" => t("Phone:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "mobile_phone",
            "value" => isset($_REQUEST["mobile_phone"]) ?
                $_REQUEST["mobile_phone"]
                :
                $tither_data["mobile_phone"],
            "label" => t("Mobile phone:")
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
