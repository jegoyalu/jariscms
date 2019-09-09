<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file
 */

 Jaris\Signals\SignalHandler::listenWithParams(
     Jaris\View::SIGNAL_THEME_TABS,
     function (&$tabs_array) {
         if (
             Jaris\Uri::get()
             ==
             Jaris\Modules::getPageUri(
                 "calendar/event",
                 "calendar"
             )
         ) {
             if (
                 Jaris\Authentication::groupHasPermission(
                     "manage_reunions_church_attendance",
                     Jaris\Authentication::currentUserGroup()
                 )
             ) {
                 $tabs_array[0][t("Church Reunion Attendance")] = [
                     "uri" => Jaris\Modules::getPageUri(
                         "admin/church-attendance/reunions/add",
                         "church_attendance"
                     ),
                     "arguments" => [
                         "calendar_uri" => $_REQUEST["uri"],
                         "event_id" => $_REQUEST["id"]
                     ]
                 ];
             }
         }
     }
 );

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Groups::SIGNAL_SET_GROUP_PERMISSION,
    function (&$permissions, &$group) {
        $options = [
            "manage_reunions_church_attendance" => t("Manage Reunions"),
            "manage_groups_church_attendance" => t("Manage Groups"),
            "manage_members_church_attendance" => t("Manage Members")
        ];

        $permissions[t("Church Attendance")] = $options;
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function (&$sections) {
        $group = Jaris\Authentication::currentUserGroup();

        $content = [];

        if (
            Jaris\Authentication::groupHasPermission(
                "manage_reunions_church_attendance",
                $group
            )
        ) {
            $content[] = [
                "title" => t("View Reunions Attendance"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-attendance/reunions",
                        "church_attendance"
                    )
                ),
                "description" => t("View/Edit reunions attendance.")
            ];
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "manage_members_church_attendance",
                $group
            )
        ) {
            $content[] = [
                "title" => t("Add Member or Visitor"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-attendance/members/add",
                        "church_attendance"
                    )
                ),
                "description" => t("View/Edit members.")
            ];

            $content[] = [
                "title" => t("Manage Members"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-attendance/members",
                        "church_attendance"
                    )
                ),
                "description" => t("View/Edit members.")
            ];
        }

        if (
            Jaris\Authentication::groupHasPermission(
                "manage_groups_church_attendance",
                $group
            )
        ) {
            $content[] = [
                "title" => t("Manage Groups"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-attendance/groups",
                        "church_attendance"
                    )
                ),
                "description" => t("View/Edit groups.")
            ];

            $content[] = [
                "title" => t("Manage Talents"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-attendance/talents",
                        "church_attendance"
                    )
                ),
                "description" => t("View/Edit talents.")
            ];
        }

        if (count($content) > 0) {
            $new_section[] = [
                "class" => "church-attendance",
                "title" => t("Church Attendance"),
                "sub_sections" => $content
            ];

            $original_sections = $sections;

            $sections = array_merge($new_section, $original_sections);
        }
    }
);
