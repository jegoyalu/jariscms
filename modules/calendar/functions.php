<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function (&$sections) {
        if (
            Jaris\Authentication::groupHasPermission(
                "add_blocks",
                Jaris\Authentication::currentUserGroup()
            )
        ) {
            $content = [
                "title" => t("Add Calendar Block"),
                "url" => Jaris\Uri::url(
                    "admin/blocks/add",
                    ["calendar_block" => 1]
                ),
                "description" => t("Create blocks to display events of a particular calendar page.")
            ];
        }

        if (isset($content)) {
            foreach ($sections as $section_index => $section_data) {
                if ($section_data["class"] == "blocks") {
                    $sections[$section_index]["sub_sections"][] = $content;
                    break;
                }
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_ADD_EDIT_TAB,
    function (&$uri, &$page_data, &$is_page_owner) {
        if ($page_data["type"] == "calendar") {
            if (calendar_event_can_add(Jaris\Uri::get(), $page_data)) {
                Jaris\View::addTab(
                    t("Add Event"),
                    Jaris\Modules::getPageUri(
                        "admin/calendar/events/add",
                        "calendar"
                    ),
                    ["uri"=>$uri]
                );

                Jaris\View::addTab(
                    t("Manage Events"),
                    Jaris\Modules::getPageUri(
                        "admin/calendar/events",
                        "calendar"
                    ),
                    ["uri"=>$uri]
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function (&$parameters, &$fieldsets) {
        if (
            Jaris\Uri::get() == "admin/pages/add" &&
            $parameters["name"] == "add-page-calendar"
        ) {
            $fieldset = [];

            $field_style[] = [
                "type" => "select",
                "label" => t("Calendar Style:"),
                "name" => "calendar_style",
                "value" => [
                    t("Traditional") => "traditional",
                    t("Consecutive") => "consecutive"
                ],
                "selected" => empty($_REQUEST["calendar_style"]) ?
                    "traditional" : $_REQUEST["calendar_style"],
                "description" => t("Select the default style used to display the calendar events.")
            ];

            $fieldset[] = [
                "fields" => $field_style
            ];

            $fieldset[] = [
                "fields" => Jaris\Groups::generateFields(
                    null,
                    "groups_add_event",
                    ["administrator", "guest"],
                    true
                ),
                "name" => t("Groups"),
                "description" => t("The groups that can publish events to this calendar."),
                "collapsible" => true,
                "collapsed" => false
            ];

            $approval = [
                t("Enable") => true,
                t("Disable") => false
            ];

            $field_approval[] = [
                "type" => "radio",
                "name" => "add_event_approval",
                "value" => $approval,
                "checked" => $_REQUEST["add_event_approval"]
            ];

            $fieldset[] = [
                "fields" => $field_approval,
                "name" => t("Require Approval"),
                "description" => t("Enable if adding events require approval."),
                "collapsible" => true,
                "collapsed" => false
            ];

            Jaris\Forms::addFieldsets(
                $fieldset,
                "Meta tags",
                $fieldsets,
                true
            );
        } elseif (
            Jaris\Uri::get() == "admin/pages/edit" &&
            $parameters["name"] == "edit-page-calendar"
        ) {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);
            $page_data["groups_add_event"] = unserialize(
                $page_data["groups_add_event"]
            );

            $url_add = Jaris\Uri::url(
                Jaris\Modules::getPageUri(
                    "admin/calendar/events/add",
                    "calendar"
                ),
                ["uri"=>$_REQUEST["uri"]]
            );

            $url_edit = Jaris\Uri::url(
                 Jaris\Modules::getPageUri(
                    "admin/calendar/events",
                    "calendar"
                ),
                 ["uri"=>$_REQUEST["uri"]]
            );

            $html = '<div id="calendar-buttons">';
            $html .= '<a href="'.$url_add.'">'.t("Add Event").'</a> | ';
            $html .= '<a href="'.$url_edit.'">'.t("View Events").'</a>';
            $html .= '<hr />';
            $html .= '</div>';

            $field = ["type"=>"other", "html_code"=>$html];

            Jaris\Forms::addFieldBefore($field, "title", $fieldsets);

            $fieldset = [];

            $field_style[] = [
                "type" => "select",
                "label" => t("Calendar Style:"),
                "name" => "calendar_style",
                "value" => [
                    t("Traditional") => "traditional",
                    t("Consecutive") => "consecutive"
                ],
                "selected" => empty($page_data["calendar_style"]) ?
                    "traditional" : $page_data["calendar_style"],
                "description" => t("Select the default style used to display the calendar events.")
            ];

            $fieldset[] = [
                "fields" => $field_style
            ];

            $fieldset[] = [
                "fields" => Jaris\Groups::generateFields(
                    $page_data["groups_add_event"],
                    "groups_add_event",
                    ["administrator", "guest"],
                    true
                ),
                "name" => t("Groups"),
                "description" => t("The groups that can publish events to this calendar."),
                "collapsible" => true,
                "collapsed" => false
            ];

            $approval = [
                t("Enable") => true,
                t("Disable") => false
            ];

            $field_approval[] = [
                "type" => "radio",
                "name" => "add_event_approval",
                "value" => $approval,
                "checked" => $page_data["add_event_approval"]
            ];

            $fieldset[] = [
                "fields" => $field_approval,
                "name" => t("Require Approval"),
                "description" => t("Enable if adding events require approval."),
                "collapsible" => true,
                "collapsed" => false
            ];

            Jaris\Forms::addFieldsets(
                $fieldset,
                "Meta tags",
                $fieldsets,
                true
            );
        } elseif (Jaris\Uri::get() == "admin/blocks/add") {
            if (isset($_REQUEST["calendar_block"])) {
                $fields[] = [
                    "type" => "hidden",
                    "name" => "calendar_block",
                    "value" => 1
                ];

                $fields[] = [
                    "type" => "textarea",
                    "name" => "pre_content",
                    "id" => "pre_content",
                    "label" => t("Pre-content:"),
                    "value" => isset($_REQUEST["pre_content"]) ?
                        $_REQUEST["pre_content"]
                        :
                        "",
                    "description" => t("Content that will appear above the results.")
                ];

                $fields[] = [
                    "type" => "textarea",
                    "name" => "sub_content",
                    "id" => "sub_content",
                    "label" => t("Sub-content:"),
                    "value" => isset($_REQUEST["sub_content"]) ?
                        $_REQUEST["sub_content"]
                        :
                        "",
                    "description" => t("Content that will appear below the results.")
                ];

                $fields[] = [
                    "type" => "text",
                    "name" => "results_to_show",
                    "value" => isset($_REQUEST["results_to_show"]) ?
                        $_REQUEST["results_to_show"]
                        :
                        5,
                    "label" => t("Results to show:"),
                    "id" => "results_to_show",
                    "required" => true,
                    "description" => t("The amount of results to display.")
                ];

                $fields[] = [
                    "type" => "uri",
                    "name" => "calendar_uri",
                    "value" => isset($_REQUEST["calendar_uri"]) ?
                        $_REQUEST["calendar_uri"]
                        :
                        "",
                    "label" => t("Calendar uri:"),
                    "required" => true,
                    "description" => t("The uri of the calendar from which to display the events.")
                ];

                $fieldset[] = [
                    "fields" => $fields,
                ];

                Jaris\Forms::addFieldsets(
                    $fieldset,
                    "Users Access",
                    $fieldsets,
                    true
                );
            }
        } elseif (Jaris\Uri::get() == "admin/blocks/edit") {
            $block_data = Jaris\Blocks::get(
                intval($_REQUEST["id"]),
                $_REQUEST["position"]
            );

            if (isset($block_data["is_calendar_block"])) {
                $fields[] = [
                    "type" => "hidden",
                    "name" => "calendar_block",
                    "value" => 1
                ];

                $fields[] = [
                    "type" => "textarea",
                    "name" => "pre_content",
                    "id" => "pre_content",
                    "label" => t("Pre-content:"),
                    "value" => isset($_REQUEST["pre_content"]) ?
                        $_REQUEST["pre_content"]
                        :
                        $block_data["pre_content"],
                    "description" => t("Content that will appear above the results.")
                ];

                $fields[] = [
                    "type" => "textarea",
                    "name" => "sub_content",
                    "id" => "sub_content",
                    "label" => t("Sub-content:"),
                    "value" => isset($_REQUEST["sub_content"]) ?
                        $_REQUEST["sub_content"]
                        :
                        $block_data["sub_content"],
                    "description" => t("Content that will appear below the results.")
                ];

                $fields[] = [
                    "type" => "text",
                    "name" => "results_to_show",
                    "value" => isset($_REQUEST["results_to_show"]) ?
                        $_REQUEST["results_to_show"]
                        :
                        $block_data["results_to_show"],
                    "label" => t("Results to show:"),
                    "id" => "results_to_show",
                    "required" => true,
                    "description" => t("The amount of results to display.")
                ];

                if (isset($block_data["calendar_uri"])) {
                    $fields[] = [
                        "type" => "uri",
                        "name" => "calendar_uri",
                        "value" => isset($_REQUEST["calendar_uri"]) ?
                            $_REQUEST["calendar_uri"]
                            :
                            $block_data["calendar_uri"],
                        "label" => t("Calendar uri:"),
                        "required" => true,
                        "description" => t("The uri of the calendar from which to display the events.")
                    ];
                }

                $fieldset[] = [
                    "fields" => $fields,
                ];

                Jaris\Forms::addFieldsets(
                    $fieldset,
                    "Users Access",
                    $fieldsets,
                    true
                );
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_CREATE_PAGE,
    function (&$uri, &$data, &$path) {
        if (
            Jaris\Uri::get() == "admin/pages/add" &&
            $data["type"] == "calendar"
        ) {
            $data["calendar_style"] = $_REQUEST["calendar_style"];
            $data["add_event_approval"] = $_REQUEST["add_event_approval"];

            if (is_array($_REQUEST["groups_add_event"])) {
                $data["groups_add_event"] = serialize(
                    $_REQUEST["groups_add_event"]
                );
            } else {
                $data["groups_add_event"] = serialize([]);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function (&$page, &$new_data, &$page_path) {
        if (
            Jaris\Uri::get() == "admin/pages/edit" &&
            $new_data["type"] == "calendar"
        ) {
            $new_data["calendar_style"] = $_REQUEST["calendar_style"];
            $new_data["add_event_approval"] = $_REQUEST["add_event_approval"];

            if (is_array($_REQUEST["groups_add_event"])) {
                $new_data["groups_add_event"] = serialize(
                    $_REQUEST["groups_add_event"]
                );
            } else {
                $new_data["groups_add_event"] = serialize([]);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_MOVE_PAGE,
    function (&$actual_uri, &$new_uri) {
        $current_path = Jaris\Files::getDir()
            . "calendar/" .  str_replace("/", "-", $actual_uri)
        ;

        if (is_dir($current_path)) {
            $new_path = Jaris\Files::getDir()
                . "calendar/" .  str_replace("/", "-", $new_uri)
            ;

            rename($current_path, $new_path);
        }

        // Update calendar events from global db.
        $db = Jaris\Sql::open("calendar_events");

        Jaris\Sql::query(
            "update calendar_events set "
            . "uri='$new_uri' "
            . "where uri='$actual_uri'",
            $db
        );

        Jaris\Sql::close($db);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_DELETE_PAGE,
    function (&$page, &$page_path) {
        $current_path = Jaris\Files::getDir()
            . "calendar/" .  str_replace("/", "-", $page)
        ;

        if (is_dir($current_path)) {
            Jaris\FileSystem::recursiveRemoveDir($current_path);
        }

        // Delete calendar events from global db.
        $db = Jaris\Sql::open("calendar_events");

        Jaris\Sql::query(
            "delete from calendar_events where uri='$page'",
            $db
        );

        Jaris\Sql::close($db);
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_ADD_BLOCK,
    function (&$fields, &$position, &$page) {
        if (
            Jaris\Uri::get() == "admin/blocks/add" &&
            isset($_REQUEST["calendar_block"])
        ) {
            if (trim($_REQUEST["content"]) == "") {
                $fields["content"] = "<div></div>";
            }
            $fields["pre_content"] = $_REQUEST["pre_content"];
            $fields["sub_content"] = $_REQUEST["sub_content"];
            $fields["is_calendar_block"] = 1;
            $fields["results_to_show"] = intval($_REQUEST["results_to_show"]);
            $fields["calendar_uri"] = $_REQUEST["calendar_uri"];
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Blocks::SIGNAL_EDIT_BLOCK,
    function (&$id, &$position, &$new_data, &$page) {
        if (
            Jaris\Uri::get() == "admin/blocks/edit" &&
            isset($_REQUEST["calendar_block"])
        ) {
            if (trim($_REQUEST["content"]) == "") {
                $new_data["content"] = "<div></div>";
            }
            $new_data["pre_content"] = $_REQUEST["pre_content"];
            $new_data["sub_content"] = $_REQUEST["sub_content"];
            $new_data["results_to_show"] = intval($_REQUEST["results_to_show"]);

            if (isset($_REQUEST["calendar_uri"])) {
                $new_data["calendar_uri"] = $_REQUEST["calendar_uri"];
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_BLOCK,
    function (&$position, &$page, &$field) {
        if ($field["is_calendar_block"]) {
            Jaris\View::addStyle(
                Jaris\Modules::directory("calendar")
                    . "styles/events.css"
            );

            if (isset($field["calendar_uri"])) {
                $field["content"] = Jaris\System::evalPHP(
                    $field["pre_content"]
                );

                $field["content"] .= calendar_block_print_results(
                    $field,
                    $field["calendar_uri"]
                );

                $field["content"] .= Jaris\System::evalPHP(
                    $field["sub_content"]
                );
            } else {
                $field["content"] .= Jaris\System::evalPHP(
                    $field["pre_content"]
                );

                $field["content"] .= calendar_block_print_results(
                    $field
                );

                $field["content"] .= Jaris\System::evalPHP(
                    $field["sub_content"]
                );
            }

            $field["is_system"] = true;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function (&$content, &$content_title, &$content_data) {
        if ($content_data["type"] == "calendar") {
            Jaris\View::addStyle(
                Jaris\Modules::directory("calendar")
                    . "styles/calendar.css"
            );

            if (
                empty($content_data["calendar_style"]) ||
                $content_data["calendar_style"] == "traditional"
            ) {
                $month = date("n", time());
                $year = date("Y", time());

                if (isset($_REQUEST["month"])) {
                    $rmonth = intval($_REQUEST["month"]);

                    if ($rmonth >= 1 && $rmonth <= 12) {
                        $month = $rmonth;
                    }
                }

                if (isset($_REQUEST["year"])) {
                    $year = intval($_REQUEST["year"]);
                }

                $content .= calendar_generate(
                    $month,
                    $year,
                    Jaris\Uri::get()
                );
            } else {
                $content .= calendar_generate_consecutive(
                    Jaris\Uri::get()
                );
            }
        }
    }
);
