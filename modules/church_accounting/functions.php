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
    function(&$permissions, &$group)
    {
        $options["view_income_church_accounting"] = t("View Income");
        $options["add_income_church_accounting"] = t("Add Income");
        $options["edit_income_church_accounting"] = t("Edit Income");
        $options["delete_income_church_accounting"] = t("Delete Income");

        $options["view_expenses_church_accounting"] = t("View Expenses");
        $options["add_expenses_church_accounting"] = t("Add Expenses");
        $options["edit_expenses_church_accounting"] = t("Edit Expenses");
        $options["delete_expenses_church_accounting"] = t("Delete Expenses");

        $options["manage_categories_church_accounting"] = t("Manage Categories");
        $options["manage_tithers_church_accounting"] = t("Manage Tithers");

        $permissions[t("Church Accounting")] = $options;
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\System::SIGNAL_GENERATE_ADMIN_PAGE,
    function(&$sections)
    {
        $group = Jaris\Authentication::currentUserGroup();

        $content = array();

        if(
            Jaris\Authentication::groupHasPermission(
                "add_income_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("Add offerings"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/income/offerings/add",
                        "church_accounting"
                    )
                ),
                "description" => t("Add offerings and thites.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "add_income_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("Add tithes"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/tithers",
                        "church_accounting"
                    )
                ),
                "description" => t("Add offerings and thites.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "view_income_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("View all income"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/income",
                        "church_accounting"
                    )
                ),
                "description" => t("View or manage existing income.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "add_expenses_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("Add expenses"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/expenses/add",
                        "church_accounting"
                    )
                ),
                "description" => t("Add expenses on rent, materials, etc...")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "view_expenses_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("View all expenses"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/expenses",
                        "church_accounting"
                    )
                ),
                "description" => t("View or manage existing expenses.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "manage_tithers_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("Manage tithers"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/church-accounting/tithers",
                        "church_accounting"
                    )
                ),
                "description" => t("View or manage tithers.")
            );
        }

        if(
            Jaris\Authentication::groupHasPermission(
                "manage_categories_church_accounting", 
                $group
            )
        )
        {
            $content[] = array(
                "title" => t("Manage income categories"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-accounting/categories/income",
                        "church_accounting"
                    )
                ),
                "description" => t("View or manage income categories.")
            );

            $content[] = array(
                "title" => t("Manage expense categories"),
                "url" => Jaris\Uri::url(
                    Jaris\Modules::getPageUri(
                        "admin/settings/church-accounting/categories/expenses",
                        "church_accounting"
                    )
                ),
                "description" => t("View or manage expense categories.")
            );
        }

        if(count($content) > 0)
        {
            $new_section[] = array(
                "class" => "church-accounting",
                "title" => t("Church Accounting"),
                "sub_sections" => $content
            );

            $original_sections = $sections;

            $sections = array_merge($new_section, $original_sections);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_PAGE_TEMPLATE,
    function(&$page, &$template_path)
    {
        $theme = Jaris\Site::$theme;
        $default_template = Jaris\Themes::directory($theme) . "page.php";

        if($template_path == $default_template)
        {
            if(Jaris\Uri::get() == "church-accounting-something")
            {
                $template_path = Jaris\Modules::directory("church_accounting") . "templates/page-empty.php";
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_CONTENT_TEMPLATE,
    function(&$page, &$type, &$template_path)
    {
        $theme = Jaris\Site::$theme;
        $default_template = Jaris\Themes::directory($theme) . "content.php";

        if($template_path == $default_template)
        {
            if(Jaris\Uri::get() == "church-accounting-something")
            {
                $template_path = Jaris\Modules::directory("church_accounting") . "templates/content-empty.php";
            }
        }
    }
);