<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the translate content page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Translate content") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("translate_languages"));

        if(!isset($_REQUEST["code"]))
        {
            Jaris\Uri::go("");
        }

        $language_code = $_REQUEST["code"];
        $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : "";
        $save = isset($_REQUEST["save"]) ? $_REQUEST["save"] : "";

        //Display translation form
        if(isset($type) && $type == "page")
        {
            $uri = $_REQUEST["uri"];
            $original_data = Jaris\Pages::get($uri, $language_code);

            $parameters["name"] = "translate-page";
            $parameters["class"] = "translate-page";
            $parameters["action"] = Jaris\Uri::url("admin/languages/translate");
            $parameters["method"] = "post";

            $fields[] = array(
                "type" => "hidden",
                "name" => "uri",
                "value" => $uri
            );

            $fields[] = array(
                "type" => "hidden",
                "name" => "code",
                "value" => $language_code
            );

            $fields[] = array(
                "type" => "hidden",
                "name" => "save",
                "value" => "page"
            );

            $fields[] = array(
                "type" => "text",
                "value" => $original_data["title"],
                "name" => "title",
                "label" => t("Title:"),
                "id" => "title",
                "required" => true
            );

            $fields[] = array(
                "type" => "textarea",
                "value" => $original_data["content"],
                "name" => "content",
                "label" => t("Content:"),
                "id" => "content"
            );

            $fieldset[] = array("fields" => $fields);

            $fields_meta[] = array(
                "type" => "textarea",
                "value" => $original_data["meta_title"],
                "name" => "meta_title",
                "label" => t("Title:"),
                "id" => "meta_title",
                "limit" => 70,
                "description" => t("Overrides the original page title on search engine results. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "value" => $original_data["description"],
                "name" => "description",
                "label" => t("Description:"),
                "id" => "description",
                "limit" => 160,
                "description" => t("Used to generate the meta description for search engines. Leave blank for default.")
            );

            $fields_meta[] = array(
                "type" => "textarea",
                "value" => $original_data["keywords"],
                "name" => "keywords",
                "label" => t("Keywords:"),
                "id" => "keywords",
                "description" => t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default.")
            );

            $fieldset[] = array(
                "fields" => $fields_meta,
                "name" => t("Meta tags"),
                "collapsible" => true,
                "collapsed" => true
            );

            $fields_buttons[] = array(
                "type" => "submit",
                "name" => "btnSave",
                "value" => t("Save")
            );

            $fields_buttons[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields_buttons);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
        elseif(isset($type) && $type == "block")
        {
            $id = intval($_REQUEST["id"]);
            $position = $_REQUEST["position"];

            $original_data = Jaris\Blocks::get(
                $id,
                $position
            );

            Jaris\Blocks::getTranslated($original_data, $language_code);

            $parameters["name"] = "translate-block";
            $parameters["class"] = "translate-block";
            $parameters["action"] = Jaris\Uri::url("admin/languages/translate");
            $parameters["method"] = "post";

            $fields[] = array(
                "type" => "hidden",
                "name" => "id",
                "value" => $id
            );

            $fields[] = array(
                "type" => "hidden",
                "name" => "position",
                "value" => $position
            );

            $fields[] = array(
                "type" => "hidden",
                "name" => "code",
                "value" => $language_code
            );

            $fields[] = array(
                "type" => "hidden",
                "name" => "save",
                "value" => "block"
            );

            $fields[] = array(
                "type" => "text",
                "name" => "description",
                "label" => t("Description:"),
                "id" => "description",
                "value" => $original_data["description"],
                "required" => true
            );

            $fields[] = array(
                "type" => "text",
                "name" => "title",
                "label" => t("Title:"),
                "id" => "title",
                "value" => $original_data["title"]
            );

            if(!$original_data["is_system"])
            {
                $fields[] = array(
                    "type" => "textarea",
                    "name" => "content",
                    "label" => t("Content:"),
                    "id" => "content",
                    "value" => $original_data["content"]
                );
            }

            $fieldset[] = array("fields" => $fields);

            $fields_other[] = array(
                "type" => "submit",
                "name" => "btnSave",
                "value" => t("Save")
            );

            $fields_other[] = array(
                "type" => "submit",
                "name" => "btnCancel",
                "value" => t("Cancel")
            );

            $fieldset[] = array("fields" => $fields_other);

            print Jaris\Forms::generate($parameters, $fieldset);
        }
        elseif(isset($type) && $type == "content-block")
        {

        }
        elseif(isset($type) && $type == "menu")
        {

        }

        //Save translations
        if(isset($save) && $save == "page")
        {
            $uri = $_REQUEST["uri"];

            if(isset($_REQUEST["btnSave"]))
            {
                $original_data = Jaris\Pages::get($uri, $language_code);

                $original_data["title"] = $_REQUEST["title"];
                $original_data["content"] = $_REQUEST["content"];
                $original_data["meta_title"] = $_REQUEST["meta_title"];
                $original_data["description"] = $_REQUEST["description"];
                $original_data["keywords"] = $_REQUEST["keywords"];

                if(!Jaris\Translate::page($uri, $original_data, $language_code))
                {
                    Jaris\View::addMessage(
                        t("Check your write permissions on the <b>language</b> directory."),
                        "error"
                    );
                }
                else
                {
                    Jaris\View::addMessage(t("Translation saved successfully!"));

                    t("Translated page '{uri}' to '{code}'.");

                    Jaris\Logger::info(
                        "Translated page '{uri}' to '{code}'.",
                        array(
                            "uri" => $uri,
                            "code" => $language_code
                        )
                    );
                }
            }

            Jaris\Uri::go($uri);
        }
        elseif(isset($save) && $save == "block")
        {
            $id = intval($_REQUEST["id"]);
            $position = $_REQUEST["position"];

            if(isset($_REQUEST["btnSave"]))
            {
                $original_data = Jaris\Blocks::get($id, $position);
                $block_title = $original_data["title"];

                if(!isset($original_data["id"]))
                {
                    $original_data["id"] = Jaris\Blocks::getNewId();
                    Jaris\Blocks::edit($id, $position, $original_data);
                }

                $original_data["description"] = $_REQUEST["description"];
                $original_data["title"] = $_REQUEST["title"];

                if(isset($_REQUEST["content"]))
                {
                    $original_data["content"] = $_REQUEST["content"];
                }

                if(!Jaris\Translate::block($original_data, $language_code))
                {
                    Jaris\View::addMessage(
                        t("Check your write permissions on the <b>language</b> directory."),
                        "error"
                    );
                }
                else
                {
                    Jaris\View::addMessage(t("Translation saved successfully!"));

                    t("Translated global block '{title}' to '{code}'.");

                    Jaris\Logger::info(
                        "Translated global block '{title}' to '{code}'.",
                        array(
                            "title" => $block_title,
                            "code" => $language_code
                        )
                    );
                }
            }

            Jaris\Uri::go("admin/blocks");
        }
        elseif(isset($save) && $save == "content-block")
        {

        }
        elseif(isset($save) && $save == "menu")
        {

        }
    ?>
    field;

    field: is_system
        1
    field;
row;
