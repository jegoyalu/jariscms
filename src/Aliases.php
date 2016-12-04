<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 *
 * Includes some shorthand functions commonly used on legacy templates.
 */

function t($textToTranslate, $po_file = null)
{
    return Jaris\Language::translate($textToTranslate, $po_file);
}

function get_current_language()
{
    return Jaris\Language::getCurrent();
}

function print_url($uri, $arguments = array())
{
    return Jaris\Uri::url($uri, $arguments);
}

function get_uri()
{
    return Jaris\Uri::get();
}

function get_setting($name, $table)
{
    return Jaris\Settings::get($name, $table);
}

function get_settings($table)
{
    return Jaris\Settings::getAll($table);
}

function is_user_logged()
{
    return Jaris\Authentication::isUserLogged();
}

function is_admin_logged()
{
    return Jaris\Authentication::isAdminLogged();
}

function get_user_data($username)
{
    return Jaris\Users::get($username);
}

function current_user_group()
{
    return Jaris\Authentication::currentUserGroup();
}

function current_user()
{
    return Jaris\Authentication::currentUser();
}

function is_module_installed($name)
{
    return Jaris\Modules::isInstalled($name);
}

function theme_links($arrLinks, $menu_name)
{
    return Jaris\View::getLinksHTML($arrLinks, $menu_name);
}

function sort_data($data_array, $field_name, $sort_method = SORT_ASC)
{
    return Jaris\Data::sort($data_array, $field_name, $sort_method);
}

function get_menu_items_list($menu_name)
{
    return Jaris\Menus::getItemsList($menu_name);
}

function get_sub_menu_items($menu_name, $parent_id = "root")
{
    return Jaris\Menus::getChildItems($menu_name, $parent_id);
}

function category_menu($machine_name, $parent_id="root")
{
    return Jaris\Categories::generateMenu($machine_name, $parent_id);
}