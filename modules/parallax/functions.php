<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 *
 * File that stores all hook functions.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function (&$parameters, &$fieldsets) {
        if (Jaris\Uri::get() == "admin/blocks/add") {
            $fields[] = [
                "type" => "radio",
                "name" => "parallax_background",
                "value" => [
                    t("Enable") => true,
                    t("Disable") => false
                ],
                "checked" => isset($_REQUEST["parallax_background"]) ?
                    $_REQUEST["parallax_background"]
                    :
                    ""
            ];

            $fields[] = [
                "type" => "file",
                "name" => "parallax_image",
                "label" => t("Background image file:"),
                "valid_types" => "jpg, jpeg, jpe, png, gif"
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_vpos",
                "label" => t("Vertical position:"),
                "value" => [
                    t("Top") => "top",
                    t("Center") => "center",
                    t("Bottom") => "bottom"
                ],
                "checked" => $_REQUEST["parallax_vpos"] ?
                    $_REQUEST["parallax_vpos"]
                    :
                    "center",
                "inline" => true
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_hpos",
                "label" => t("Horizontal position:"),
                "value" => [
                    t("Left") => "left",
                    t("Center") => "center",
                    t("Right") => "right"
                ],
                "checked" => $_REQUEST["parallax_hpos"] ?
                    $_REQUEST["parallax_hpos"]
                    :
                    "center",
                "inline" => true
            ];

            $fields[] = [
                "type" => "other",
                "html_code" => "<div></div>"
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_size",
                "label" => t("Background size:"),
                "value" => [
                    "cover" => "cover",
                    "auto" => "auto",
                    "contain" => "contain"
                ],
                "checked" => $_REQUEST["parallax_size"] ?
                    $_REQUEST["parallax_size"]
                    :
                    "cover",
                "inline" => true
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_attachment",
                "label" => t("Background attachment:"),
                "value" => [
                    "fixed" => "fixed",
                    "scroll" => "scroll",
                    "local" => "local"
                ],
                "checked" => $_REQUEST["parallax_attachment"] ?
                    $_REQUEST["parallax_attachment"]
                    :
                    "fixed",
                "inline" => true
            ];

            $fields[] = [
                "type" => "select",
                "name" => "parallax_parent",
                "label" => t("Main Parent:"),
                "value" => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5
                ],
                "selected" => isset($_REQUEST["parallax_parent"]) ?
                    $_REQUEST["parallax_parent"]
                    :
                    2,
                "description" => t("Parent of the block that will contain the background image.")
            ];

            $fieldset[] = [
                "name" => t("Parallax Background"),
                "fields" => $fields,
                "collapsed" => true,
                "collapsible" => true
            ];

            Jaris\Forms::addFieldsets(
                $fieldset,
                "Users Access",
                $fieldsets,
                true
            );
        } elseif (Jaris\Uri::get() == "admin/blocks/edit") {
            $block_data = Jaris\Blocks::get(
                intval($_REQUEST["id"]),
                $_REQUEST["position"]
            );

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_background",
                "value" => [
                    t("Enable") => true,
                    t("Disable") => false
                ],
                "checked" => isset($_REQUEST["parallax_background"]) ?
                    $_REQUEST["parallax_background"]
                    :
                    $block_data["parallax_background"]
            ];

            if (!empty($block_data["parallax_image"])) {
                $image_url = Jaris\Uri::url(
                    Jaris\Files::get(
                        $block_data["parallax_image"],
                        "parallax/blocks"
                    )
                );

                $fields[] = [
                    "type" => "other",
                    "html_code" => "<div style=\"margin-top: 10px;\">"
                        . "<strong>" . t("Current image:") . "</strong>"
                        . "<hr />"
                        . "<img width=\"300px\" src=\"$image_url\" />"
                        . "</div>"
                ];
            }

            $fields[] = [
                "type" => "file",
                "name" => "parallax_image",
                "label" => t("Background image file:"),
                "valid_types" => "jpg, jpeg, jpe, png, gif"
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_vpos",
                "label" => t("Vertical position:"),
                "value" => [
                    t("Top") => "top",
                    t("Center") => "center",
                    t("Bottom") => "bottom"
                ],
                "checked" => $_REQUEST["parallax_vpos"] ?
                    $_REQUEST["parallax_vpos"]
                    :
                    $block_data["parallax_vpos"],
                "inline" => true
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_hpos",
                "label" => t("Horizontal position:"),
                "value" => [
                    t("Left") => "left",
                    t("Center") => "center",
                    t("Right") => "right"
                ],
                "checked" => $_REQUEST["parallax_hpos"] ?
                    $_REQUEST["parallax_hpos"]
                    :
                    $block_data["parallax_hpos"],
                "inline" => true
            ];

            $fields[] = [
                "type" => "other",
                "html_code" => "<div></div>"
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_size",
                "label" => t("Background size:"),
                "value" => [
                    "cover" => "cover",
                    "auto" => "auto",
                    "contain" => "contain"
                ],
                "checked" => $_REQUEST["parallax_size"] ?
                    $_REQUEST["parallax_size"]
                    :
                    $block_data["parallax_size"],
                "inline" => true
            ];

            $fields[] = [
                "type" => "radio",
                "name" => "parallax_attachment",
                "label" => t("Background attachment:"),
                "value" => [
                    "fixed" => "fixed",
                    "scroll" => "scroll",
                    "local" => "local"
                ],
                "checked" => $_REQUEST["parallax_attachment"] ?
                    $_REQUEST["parallax_attachment"]
                    :
                    $block_data["parallax_attachment"],
                "inline" => true
            ];

            $fields[] = [
                "type" => "select",
                "name" => "parallax_parent",
                "label" => t("Main Parent:"),
                "value" => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5
                ],
                "selected" => isset($_REQUEST["parallax_parent"]) ?
                    $_REQUEST["parallax_parent"]
                    :
                    $block_data["parallax_parent"],
                "description" => t("Parent of the block that will contain the background image.")
            ];

            $fieldset[] = [
                "name" => t("Parallax Background"),
                "fields" => $fields,
                "collapsed" => true,
                "collapsible" => true
            ];

            Jaris\Forms::addFieldsets(
                $fieldset,
                "Users Access",
                $fieldsets,
                true
            );
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_ADD_BLOCK,
    function (&$fields, &$position, &$page) {
        $fields["parallax_background"] = $_REQUEST["parallax_background"];
        $fields["parallax_parent"] = intval($_REQUEST["parallax_parent"]);
        $fields["parallax_image"] = "";
        $fields["parallax_vpos"] = $_REQUEST["parallax_vpos"];
        $fields["parallax_hpos"] = $_REQUEST["parallax_hpos"];
        $fields["parallax_size"] = $_REQUEST["parallax_size"];
        $fields["parallax_attachment"] = $_REQUEST["parallax_attachment"];

        if (
            isset($_FILES["parallax_image"])
            &&
            file_exists($_FILES["parallax_image"]["tmp_name"])
        ) {
            $fields["parallax_image"] = Jaris\Files::addUpload(
                $_FILES["parallax_image"],
                "parallax/blocks"
            );

            if ($fields["parallax_image"]) {
                chmod(
                    Jaris\Files::get(
                        $fields["parallax_image"],
                        "parallax/blocks"
                    ),
                    0755
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_EDIT_BLOCK,
    function (&$id, &$position, &$new_data, &$page) {
        $block_data_path = Jaris\Blocks::getPath($position, $page);
        $old_data = Jaris\Data::get($id, $block_data_path);

        $new_data["parallax_background"] = $_REQUEST["parallax_background"];
        $new_data["parallax_parent"] = intval($_REQUEST["parallax_parent"]);
        $new_data["parallax_vpos"] = $_REQUEST["parallax_vpos"];
        $new_data["parallax_hpos"] = $_REQUEST["parallax_hpos"];
        $new_data["parallax_size"] = $_REQUEST["parallax_size"];
        $new_data["parallax_attachment"] = $_REQUEST["parallax_attachment"];

        if (
            isset($_FILES["parallax_image"])
            &&
            file_exists($_FILES["parallax_image"]["tmp_name"])
        ) {
            $new_data["parallax_image"] = Jaris\Files::addUpload(
                $_FILES["parallax_image"],
                "parallax/blocks"
            );

            if ($new_data["parallax_image"]) {
                chmod(
                    Jaris\Files::get(
                        $new_data["parallax_image"],
                        "parallax/blocks"
                    ),
                    0755
                );
            }

            if (!empty($old_data["parallax_image"])) {
                Jaris\Files::delete(
                    $old_data["parallax_image"],
                    "parallax/blocks"
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_DELETE_BLOCK,
    function (&$id, &$position, &$data, &$page) {
        if (!empty($data["parallax_image"])) {
            Jaris\Files::delete($data["parallax_image"], "parallax/blocks");
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_BLOCK,
    function (&$position, &$page, &$data) {
        if (
            !empty($data["parallax_background"])
            &&
            !empty($data["parallax_image"])
        ) {
            $data["content"] .= parallax_get_block_script($data["id"], $data);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function (&$sections) {
        $group = Jaris\Authentication::currentUserGroup();

        $title = t("Settings");

        foreach ($sections as $index => $sub_section) {
            if ($sub_section["title"] == $title) {
                if (
                    Jaris\Authentication::groupHasPermission(
                        "edit_settings",
                        Jaris\Authentication::currentUserGroup()
                    )
                ) {
                    $sub_section["sub_sections"][] = [
                        "title" => t("Parallax Backgrounds"),
                        "url" => Jaris\Uri::url(
                            Jaris\Modules::getPageUri(
                                "admin/settings/parallax",
                                "parallax"
                            )
                        ),
                        "description" => t("To see, add and edit the parallax backgrounds of the site.")
                    ];

                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_SCRIPTS,
    function (&$scripts, &$scripts_code) {
        $base_url = Jaris\Site::$base_url;

        $backgrounds_settings = Jaris\Settings::getAll("parallax");
        $backgrounds = unserialize($backgrounds_settings["parallax_backgrounds"]);

        if (is_array($backgrounds) && count($backgrounds) > 0) {
            //Sort array from just_listed to all_except_listed
            $just_listed = [];
            $all_except_listed = [];
            foreach ($backgrounds as $id => $data) {
                if ($data["display_rule"] == "just_listed") {
                    $just_listed[$id] = $data;
                } else {
                    $all_except_listed[$id] = $data;
                }
            }

            $backgrounds = [];

            foreach ($just_listed as $id => $data) {
                $backgrounds[$id] = $data;
            }

            foreach ($all_except_listed as $id => $data) {
                $backgrounds[$id] = $data;
            }
            //end sort

            foreach ($backgrounds as $id => $data) {
                $display_rule = $data["display_rule"];
                $pages = explode(",", $data["pages"]);

                if ($display_rule == "all_except_listed") {
                    foreach ($pages as $page_check) {
                        $page_check = trim($page_check);

                        if ($page_check == "") {
                            $scripts_code .= parallax_get_script($id, $data);

                            break;
                        }

                        $page_check = str_replace(
                            ["/", "/*"],
                            ["\\/", "/.*"],
                            $page_check
                        );

                        $page_check = "/^$page_check\$/";

                        if (preg_match($page_check, Jaris\Uri::get())) {
                            break;
                        }
                    }

                    $scripts_code .= parallax_get_script($id, $data);
                } elseif ($display_rule == "just_listed") {
                    foreach ($pages as $page_check) {
                        $page_check = trim($page_check);

                        $page_check = str_replace(
                            ["/", "/*"],
                            ["\\/", "/.*"],
                            $page_check
                        );

                        $page_check = "/^$page_check\$/";

                        if (preg_match($page_check, Jaris\Uri::get())) {
                            $scripts_code .= parallax_get_script($id, $data);

                            break;
                        }
                    }
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_TABS,
    function (&$tabs_array) {
        if (Jaris\Uri::get() == "admin/settings") {
            $tabs_array[0][t("Parallax Backgrounds")] = [
                "uri" => Jaris\Modules::getPageUri(
                    "admin/settings/parallax",
                    "parallax"
                ),
                "arguments" => []
            ];
        }
    }
);

function parallax_get_style($id, $background)
{
    $element = $background["element"];

    $image_url = Jaris\Uri::url(
        Jaris\Files::get(
            $background["image"],
            "parallax"
        )
    );

    $bgcolor = $background["background_color"];

    $hpos = $background["horizontal_position"];

    $vpos = $background["vertical_position"];

    $background_size = !empty($background["background_size"]) ?
        $background["background_size"] : "cover"
    ;

    $scripts_code = <<<STYLE
<style>
$element{
    background: #$bgcolor url($image_url) no-repeat $hpos $vpos;
    background-attachment: fixed;
    background-size: $background_size;
}
</style>

STYLE;

    return $scripts_code;
}

function parallax_get_script($id, $background)
{
    $container = "var parallaxContainer = $('{$background["element"]}');";

    if (!empty($background["element"])) {
        return parallax_get_style($id, $background);
    }

    $body = "";

    if (empty($background["element"])) {
        $body .= '\n$("body > *").appendTo(parallaxContainer);' . "\n"
            . '    parallaxContainer.appendTo("body");' . "\n\n"
        ;
    }

    $image_url = Jaris\Uri::url(
        Jaris\Files::get(
            $background["image"],
            "parallax"
        )
    );

    $bgcolor = $background["background_color"];

    $hpos = $background["horizontal_position"];

    $vpos = $background["vertical_position"];

    $background_size = !empty($background["background_size"]) ?
        $background["background_size"] : "cover"
    ;

    $scripts_code = <<<SCRIPT
<script>
$(document).ready(function(){
    $container

    parallaxContainer.css(
        "background",
        "#$bgcolor url($image_url) no-repeat $hpos $vpos"
    );

    parallaxContainer.css(
        "background-attachment",
        "fixed"
    );
    $body
    parallaxContainer.css(
        "background-size",
        "$background_size"
    );
});
</script>

SCRIPT;

    return $scripts_code;
}

function parallax_get_block_script($id, $block_data)
{
    $image_url = Jaris\Uri::url(
        Jaris\Files::get(
            $block_data["parallax_image"],
            "parallax/blocks"
        )
    );

    $hpos = empty($block_data["parallax_hpos"]) ?
        $block_data["parallax_hpos"] : "center"
    ;

    $vpos = empty($block_data["parallax_vpos"]) ?
        $block_data["parallax_vpos"] : "center"
    ;

    $bgsize = !empty($block_data["parallax_size"]) ?
        $block_data["parallax_size"] : "cover"
    ;

    $bgattach = !empty($block_data["parallax_attachment"]) ?
        $block_data["parallax_attachment"] : "fixed"
    ;

    $parent = intval($block_data["parallax_parent"]);
    $parents = "";
    for ($i=0; $i<$parent; $i++) {
        $parents .= ".parent()";
    }

    $scripts_code = <<<SCRIPT
<div id="parallax-block-$id" class="parallax-block"></div>
<script>
$(document).ready(function(){
    var parallaxContainer = $('#parallax-block-$id')$parents;

    parallaxContainer.css({
        background: "transparent url($image_url) no-repeat $hpos $vpos",
        backgroundAttachment: "$bgattach",
        backgroundSize: "$bgsize"
    });
});
</script>

SCRIPT;

    return $scripts_code;
}
