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
        <?php print t("Add Tither") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("manage_tithers_church_accounting"));

        if(
            isset($_REQUEST["btnSave"]) &&
            !Jaris\Forms::requiredFieldEmpty("add-tither")
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

            church_accounting_tither_add($data);

            Jaris\View::addMessage("Tither successfully added.");

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

        $parameters["name"] = "add-tither";
        $parameters["class"] = "add-tither";
        $parameters["action"] = Jaris\Uri::url(Jaris\Uri::get());
        $parameters["method"] = "post";

        $fields[] = array(
            "type" => "text",
            "name" => "first_name",
            "value" => $_REQUEST["first_name"],
            "label" => t("First name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "last_name",
            "value" => $_REQUEST["last_name"],
            "label" => t("Last name:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "maiden_name",
            "value" => $_REQUEST["maiden_name"],
            "label" => t("Maiden name:")
        );

        $fields[] = array(
            "type" => "textarea",
            "name" => "postal_address",
            "value" => $_REQUEST["postal_address"],
            "label" => t("Postal address:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "email",
            "value" => $_REQUEST["email"],
            "label" => t("E-mail:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "phone",
            "value" => $_REQUEST["phone"],
            "label" => t("Phone:")
        );

        $fields[] = array(
            "type" => "text",
            "name" => "mobile_phone",
            "value" => $_REQUEST["mobile_phone"],
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
