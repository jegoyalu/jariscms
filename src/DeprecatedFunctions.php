<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 *
 * This file stores all functions of old jariscms version for backwards
 * compatibility.
 */


function add_block($fields, $position, $page = "")
{
    return Jaris\Blocks::add($fields, $position, $page);
}

function delete_block($id, $position, $page = "")
{
    return Jaris\Blocks::delete($id, $position, $page);
}

function delete_block_by_field($field_name, $value, $page = "")
{
    return Jaris\Blocks::deleteByField($field_name, $value, $page);
}

function edit_block($id, $position, $new_data, $page = "")
{
    return Jaris\Blocks::edit($id, $position, $new_data, $page);
}

function edit_block_by_field($field_name, $value, $new_data, $page = "")
{
    return Jaris\Blocks::editByField($field_name, $value, $new_data, $page);
}

function get_block_data($id, $position, $page = "")
{
    return Jaris\Blocks::get($id, $position, $page);
}

function get_block_data_translation(&$data, $language_code)
{
    return Jaris\Blocks::getTranslated($data, $language_code);
}

function get_block_data_by_field($field_name, $value, $page = "")
{
    return Jaris\Blocks::getByField($field_name, $value, $page);
}

function get_block_list($position, $page = "")
{
    return Jaris\Blocks::getList($position, $page);
}

function move_block($id, $current_position, $new_position, $page = "")
{
    return Jaris\Blocks::move($id, $current_position, $new_position, $page);
}

function user_block_access($block)
{
    return Jaris\Blocks::userHasAccess($block);
}

function page_block_access($block, $page)
{
    return Jaris\Blocks::pageHasAccess($block, $page);
}

function set_block_post_settings($settings, $page)
{
    return Jaris\Blocks::setPostSettings($settings, $page);
}

function get_block_post_settings($page)
{
    return Jaris\Blocks::getPostSettings($page);
}

function generate_block_post_content($uri, $page_uri)
{
    return Jaris\Blocks::generatePostContent($uri, $page_uri);
}

function move_blocks_by_theme(&$header, &$left, &$right, &$center, &$footer)
{
    Jaris\Blocks::moveByTheme($header, $left, $right, $center, $footer);
}

function generate_themes_block_fields_list($selected = null)
{
    return Jaris\Blocks::generateThemesSelect($selected);
}

function blocks_get_new_id()
{
    return Jaris\Blocks::getNewId();
}

function generate_block_path($position, $page = "")
{
    return Jaris\Blocks::getPath($position, $page);
}

function create_category($machine_name, $data)
{
    return Jaris\Categories::add($machine_name, $data);
}

function delete_category($machine_name)
{
    return Jaris\Categories::delete($machine_name);
}

function edit_category($machine_name, $new_data)
{
    return Jaris\Categories::edit($machine_name, $new_data);
}

function get_category_data($machine_name)
{
    return Jaris\Categories::get($machine_name);
}

function create_subcategory($category, $data)
{
    return Jaris\Categories::addSubcategory($category, $data);
}

function delete_subcategory($category, $id, &$sub_categories=array())
{
    return Jaris\Categories::deleteSubcategory($category, $id, $sub_categories);
}

function edit_subcategory($category, $new_data, $id)
{
    return Jaris\Categories::editSubcategory($category, $new_data, $id);
}

function get_subcategory_data($category, $id)
{
    return Jaris\Categories::getSubcategory($category, $id);
}

function get_sub_subcategories($category, $parent_id = "root")
{
    return Jaris\Categories::getChildSubcategories($category, $parent_id);
}

function get_subcategories_list($category)
{
    return Jaris\Categories::getSubcategories($category);
}

function get_categories_list($type = null)
{
    return Jaris\Categories::getList($type);
}

function get_subcategories_in_parent_order(
    $category_name, $parent = "root", $position = ""
)
{
    return Jaris\Categories::getSubcategoriesInParentOrder(
        $category_name, $parent, $position
    );
}

function generate_category_fields_list(
    $selected = [], $main_category = "", $type = ""
)
{
    return Jaris\Categories::generateFields($selected, $main_category, $type);
}

function add_category_block($machine_name, $data)
{
    Jaris\Categories::addBlock($machine_name, $data);
}

function show_category_results(&$page)
{
    Jaris\Categories::showResults($page);
}

function generate_category_path($machine_name)
{
    return Jaris\Categories::getPath($machine_name);
}

function add_type_field($fields, $type)
{
    return Jaris\Fields::add($fields, $type);
}

function edit_type_field($id, $fields, $type)
{
    return Jaris\Fields::edit($id, $fields, $type);
}

function delete_type_field($id, $type)
{
    return Jaris\Fields::delete($id, $type);
}

function get_type_field_data($id, $type)
{
    return Jaris\Fields::get($id, $type);
}

function get_type_fields($type)
{
    return Jaris\Fields::getList($type);
}

function append_type_extra_fields($type, &$current_fields)
{
    return Jaris\Fields::appendFields($type, $current_fields);
}

function files_type_upload_pass($type)
{
    return Jaris\Fields::validUploads($type);
}

function files_type_save_uploads($type, $page)
{
    Jaris\Fields::saveUploads($type, $page);
}

function generate_type_form_fields($type, $values = array())
{
    return Jaris\Fields::generateFields($type, $values);
}

function generate_type_fields_path($type)
{
    return Jaris\Fields::getPath($type);
}

function add_file(
    $file_array, $description, $page = "", &$file_name = null, $move_file = true
)
{
    return Jaris\Pages\Files::add(
        $file_array, $description, $page, $file_name, $move_file
    );
}

function delete_file($id, $page)
{
    return Jaris\Pages\Files::delete($id, $page);
}

function delete_file_by_name($name, $page)
{
    return Jaris\Pages\Files::deleteByName($name, $page);
}

function edit_file($id, $new_data, $page)
{
    return Jaris\Pages\Files::edit($id, $new_data, $page);
}

function get_file_data($id, $page)
{
    return Jaris\Pages\Files::get($id, $page);
}

function get_file_data_by_name($name, $page)
{
    return Jaris\Pages\Files::getByName($name, $page);
}

function get_file_list($page)
{
    return Jaris\Pages\Files::getList($page);
}

function generate_file_path($page)
{
    return Jaris\Pages\Files::getPath($page);
}

function add_group($group_name, $fields)
{
    return Jaris\Groups::add($group_name, $fields);
}

function delete_group($group_name)
{
    return Jaris\Groups::delete($group_name);
}

function edit_group($group_name, $new_data, $new_name = "")
{
    return Jaris\Groups::edit($group_name, $new_data, $new_name);
}

function get_group_data($group_name)
{
    return Jaris\Groups::get($group_name);
}

function get_group_list()
{
    return Jaris\Groups::getList();
}

function generate_groups_fields_list(
    $selected = null,
    $field_name="groups",
    $skip_groups = array(),
    $inline=false
)
{
    return Jaris\Groups::generateFields(
        $selected, $field_name, $skip_groups, $inline
    );
}

function generate_group_path($group_name)
{
    return Jaris\Groups::getPath($group_name);
}

function add_image(
    $file_array, $description, $page = "", &$file_name = null, $move_file = true
)
{
    return Jaris\Pages\Images::add(
        $file_array, $description, $page, $file_name, $move_file
    );
}

function delete_image($id, $page)
{
    return Jaris\Pages\Images::delete($id, $page);
}

function edit_image($id, $new_data, $page)
{
    return Jaris\Pages\Images::edit($id, $new_data, $page);
}

function get_image_data(&$id, $page)
{
    return Jaris\Pages\Images::get($id, $page);
}

function get_image_list($page)
{
    return Jaris\Pages\Images::getList($page);
}

function generate_image_path($path)
{
    return Jaris\Pages\Images::getPath($path);
}

function add_input_format($name, $fields)
{
    return Jaris\InputFormats::add($name, $fields);
}

function delete_input_format($name)
{
    return Jaris\InputFormats::delete($name);
}

function edit_input_format($name, $fields)
{
    return Jaris\InputFormats::edit($name, $fields);
}

function get_input_format_data($name)
{
    return Jaris\InputFormats::get($name);
}

function get_input_formats_list()
{
    return Jaris\InputFormats::getList();
}

function parse_links($text)
{
    return Jaris\InputFormats::parseLinks($text);
}

function parse_emails($text)
{
    return Jaris\InputFormats::parseEmails($text);
}

function parse_line_breaks($text)
{
    return Jaris\InputFormats::parseLineBreaks($text);
}

function get_input_formats()
{
    return Jaris\InputFormats::getAll();
}

function filter_data($data, $input_format)
{
    return Jaris\InputFormats::filter($data, $input_format);
}

function generate_input_format_path($name)
{
    return Jaris\InputFormats::getPath($name);
}

function create_menu($menu_name)
{
    return Jaris\Menus::add($menu_name);
}

function delete_menu($menu_name)
{
    return Jaris\Menus::delete($menu_name);
}

function rename_menu($actual_name, $new_name)
{
    return Jaris\Menus::rename($actual_name, $new_name);
}

function get_menu_list()
{
    return Jaris\Menus::getList();
}

function add_menu_item($menu_name, $fields)
{
    return Jaris\Menus::addItem($menu_name, $fields);
}

function delete_menu_item($id, $menu_name)
{
    return Jaris\Menus::deleteItem($id, $menu_name);
}

function edit_menu_item($id, $menu_name, $new_data)
{
    return Jaris\Menus::editItem($id, $menu_name, $new_data);
}

function get_menu_item_data($id, $menu_name)
{
    return Jaris\Menus::getItem($id, $menu_name);
}

function get_primary_menu_name()
{
    return Jaris\Menus::getPrimaryName();
}

function get_secondary_menu_name()
{
    return Jaris\Menus::getSecondaryName();
}

function generate_menu_path($menu)
{
    return Jaris\Menus::getPath($menu);
}

function create_page($page, $data, &$uri)
{
    return Jaris\Pages::add($page, $data, $uri);
}

function delete_page($page, $disable_hook=false)
{
    return Jaris\Pages::delete($page, $disable_hook);
}

function edit_page_data($page, $new_data)
{
    return Jaris\Pages::edit($page, $new_data);
}

function count_page_view($page)
{
    return Jaris\Pages::countView($page);
}

function get_page_views($page)
{
    return Jaris\Pages::getViews($page);
}

function get_page_data($page, $language_code = null)
{
    return Jaris\Pages::get($page, $language_code);
}

function get_page_type($page)
{
    return Jaris\Pages::getType($page);
}

function is_page_owner($page, &$page_data=array())
{
    return Jaris\Pages::userIsOwner($page, $page_data);
}

function move_page($actual_uri, &$new_uri)
{
    return Jaris\Pages::move($actual_uri, $new_uri);
}

function user_page_access($page_data)
{
    return Jaris\Pages::userHasAccess($page_data);
}

function remove_empty_directories($path)
{
    return Jaris\Pages::removeEmptyDirectories($path);
}

function add_uri_sqlite($uri, $data)
{
    Jaris\Pages::addIndex($uri, $data);
}

function edit_uri_sqlite($old_uri, $new_uri)
{
    Jaris\Pages::editIndexUri($old_uri, $new_uri);
}

function edit_uri_data_sqlite($uri, $data)
{
    Jaris\Pages::editIndex($uri, $data);
}

function remove_uri_sqlite($uri)
{
    Jaris\Pages::deleteIndex($uri);
}

function get_pages_list_sqlite($page = 0, $limit = 30)
{
    return Jaris\Pages::getNavigationList($page, $limit);
}

function generate_page_path($page)
{
    return Jaris\Pages::getPath($page);
}

function generate_system_page_path($page)
{
    return Jaris\Pages::getSystemPath($page);
}

function add_type($name, $fields)
{
    return Jaris\Types::add($name, $fields);
}

function delete_type($name)
{
    return Jaris\Types::delete($name);
}

function edit_type($name, $fields)
{
    return Jaris\Types::edit($name, $fields);
}

function get_type_data($name)
{
    return Jaris\Types::get($name);
}

function type_get_image_url(
    $name, $width=null, $height=null, $ar=null, $bg=null
)
{
    return Jaris\Types::getImageUrl($name, $width, $height, $ar, $bg);
}

function get_types_list($user_group = "", $username = "")
{
    return Jaris\Types::getList($user_group, $username);
}

function user_reached_max_posts($type, $username)
{
    return Jaris\Types::userReachedMaxPosts($type, $username);
}

function generate_types_categories_fields_list($selected = null)
{
    return Jaris\Types::generateCategoriesFields($selected);
}

function generate_types_fields_list($selected = null)
{
    return Jaris\Types::generateFields($selected);
}

function get_type_default_input_format($name)
{
    return Jaris\Types::getDefaultInputFormat($name);
}

function get_type_label($type, $label)
{
    return Jaris\Types::getLabel($type, $label);
}

function generate_type_path($name)
{
    return Jaris\Types::getPath($name);
}

function add_user($username, $group, $fields, $picture = array())
{
    return Jaris\Users::add($username, $group, $fields, $picture);
}

function delete_user($username)
{
    return Jaris\Users::delete($username);
}

function edit_user($username, $group, $new_data, $picture = array())
{
    return Jaris\Users::edit($username, $group, $new_data, $picture);
}

function get_user_data_by_email($email)
{
    return Jaris\Users::getByEmail($email);
}

function get_user_uploads_path($username)
{
    return Jaris\Users::getUploadsPath($username);
}

function get_user_picture_path($username)
{
    return Jaris\Users::getPicturePath($username);
}

function get_user_picture_url($username)
{
    return Jaris\Users::getPictureUrl($username);
}

function user_exist($username)
{
    return Jaris\Users::exists($username);
}

function add_user_sqlite($username, $data)
{
    Jaris\Users::addIndex($username, $data);
}

function edit_user_sqlite($username, $data)
{
    Jaris\Users::editIndex($username, $data);
}

function get_users_list_sqlite($page = 0, $limit = 30)
{
    return Jaris\Users::getNavigationList($page, $limit);
}

function remove_user_sqlite($username)
{
    Jaris\Users::deleteIndex($username);
}

function users_status()
{
    return Jaris\Users::getStatuses();
}

function reset_user_password_by_username($username)
{
    return Jaris\Users::resetPassword($username);
}

function reset_user_password_by_email($email)
{
    return Jaris\Users::resetPasswordByEmail($email);
}

function generate_user_password($len=10)
{
    return Jaris\Users::generatePassword($len);
}

function print_user_page()
{
    return Jaris\Users::printPage();
}

function show_user_profile(&$page)
{
    Jaris\Users::showProfile($page);
}

function user_generator($string)
{
    return Jaris\Users::formatUsername($string);
}

function generate_user_path($username, $group)
{
    return Jaris\Users::getPath($username, $group);
}

function api_init($spec=null)
{
    Jaris\Api::init($spec);
}

function api_describe()
{
    Jaris\Api::describe();
}

function api_validate_call()
{
    Jaris\Api::validateCall();
}

function api_get_action()
{
    return Jaris\Api::getAction();
}

function api_get_current_key()
{
    return Jaris\Api::getCurrentKey();
}

function api_response_add($parameter, $value)
{
    Jaris\Api::addResponse($parameter, $value);
}

function api_response_send()
{
    Jaris\Api::sendResponse();
}

function api_response_send_error($code, $message, $http_status=400)
{
    Jaris\Api::sendErrorResponse($code, $message, $http_status);
}

function api_response_send_system_error($code)
{
    Jaris\Api::sendSystemErrorResponse($code);
}

function api_key_create_db()
{
    Jaris\ApiKey::createDatabase();
}

function api_key_add($data)
{
    return Jaris\ApiKey::add($data);
}

function api_key_edit($key, $data)
{
    Jaris\ApiKey::edit($key, $data);
}

function api_key_delete($key)
{
    Jaris\ApiKey::delete($key);
}

function api_key_get_data($key)
{
    return Jaris\ApiKey::getData($key);
}

function api_key_get_data_by_id($id)
{
    return Jaris\ApiKey::getDataById($id);
}

function api_key_get_data_by_token($token)
{
    return Jaris\ApiKey::getDataByToken($token);
}

function api_key_valid($key)
{
    return Jaris\ApiKey::isValid($key);
}

function api_key_set_permissions($key, $permissions)
{
    Jaris\ApiKey::setPermissions($key, $permissions);
}

function api_key_get_permissions($key)
{
    return Jaris\ApiKey::getPermissions($key);
}

function api_key_has_permission($key, $permission)
{
    return Jaris\ApiKey::hasPermission($key, $permission);
}

function api_token_valid($token)
{
    return Jaris\Api::isValidToken($token);
}

function api_token_extend($token)
{
    return Jaris\Api::extendToken($token);
}

function api_get_permissions_list()
{
    return Jaris\Api::getPermissionsList();
}

function save_setting($name, $value, $table)
{
    return Jaris\Settings::save($name, $value, $table);
}

function settings_override()
{
    Jaris\Site::init();
}

function directory_browser($directory)
{
    return Jaris\Util::directoryBrowser($directory);
}

function generate_browser_navigation($directories, $main_dir)
{
    return Jaris\Util::generateBrowserNavigation($directories, $main_dir);
}

function get_uri_from_path($relative_path)
{
    return Jaris\Uri::getFromPath($relative_path);
}

function data_parser($file, $lock_wait=true, $cache=true)
{
    return Jaris\Data::parse($file, $lock_wait, $cache);
}

function data_writer($data, $file, $callback=null)
{
    return Jaris\Data::write($data, $file, $callback);
}

function data_writer_no_lock($data, $file)
{
   return Jaris\Data::writeNoLock($data, $file);
}

function get_data($position, $file)
{
    return Jaris\Data::get($position, $file);
}

function add_data($fields, $file)
{
    return Jaris\Data::add($fields, $file);
}

function delete_data($position, $file)
{
    return Jaris\Data::delete($position, $file);
}

function delete_data_by_field($field_name, $value, $file)
{
    return Jaris\Data::deleteByField($field_name, $value, $file);
}

function edit_data($position, $new_data, $file, $callback=null)
{
    return Jaris\Data::edit($position, $new_data, $file, $callback);
}

function lock_data($file, $lock_type = LOCK_SH)
{
    return Jaris\Data::lock($file, $lock_type);
}

function unlock_data($file)
{
    return Jaris\Data::unlock($file);
}

function get_days_array()
{
    return Jaris\Date::getDays();
}

function get_months_array()
{
    return Jaris\Date::getMonths();
}

function get_years_array($additional_years=0)
{
    return Jaris\Date::getYears($additional_years);
}

function get_time_elapsed($timestamp)
{
    return Jaris\Date::getElapsedTime($timestamp);
}

function copy_file($source, $destination)
{
    return Jaris\FileSystem::copy($source, $destination);
}

function move_file($source, $destination)
{
    return Jaris\FileSystem::move($source, $destination);
}

function rename_file_if_exist($file_name)
{
    return Jaris\FileSystem::renameIfExist($file_name);
}

function print_file($file_uri)
{
    Jaris\Pages\Files::printIt($file_uri);
}

function search_files($path, $pattern, $callback)
{
    return Jaris\FileSystem::search($path, $pattern, $callback);
}

function get_dir_files($path)
{
    return Jaris\FileSystem::getFiles($path);
}

function make_directory($directory, $mode = 0755, $recursive = false)
{
    return Jaris\FileSystem::makeDir($directory, $mode, $recursive);
}

function recursive_move_directory($source, $target)
{
    return Jaris\FileSystem::recursiveMoveDir($source, $target);
}

function recursive_copy_directory($source, $target)
{
    return Jaris\FileSystem::recursiveCopyDir($source, $target);
}

function recursive_remove_directory($directory, $empty = false)
{
    return Jaris\FileSystem::recursiveRemoveDir($directory, $empty);
}

function print_any_file($path, $name = "file", $force_download = false, $try_compression = false)
{
    Jaris\FileSystem::printFile($path, $name, $force_download, $try_compression);
}

function strip_file_extension($filename, &$extension = null)
{
    return Jaris\FileSystem::stripExtension($filename, $extension);
}

function get_mimetype($path)
{
    return Jaris\FileSystem::getMimeType($path);
}

function get_mimetype_local($filename)
{
    return Jaris\FileSystem::getMimeTypeLocal($filename);
}

function valid_email_address($email)
{
    return Jaris\Forms::validEmail($email);
}

function valid_username($username)
{
    return Jaris\Forms::validUsername($username);
}

function valid_number($input, $number_type = "float")
{
    return Jaris\Forms::validNumber($input, $number_type);
}

function enable_file_upload()
{
    Jaris\Forms::enableUpload();
}

function disable_file_upload()
{
    Jaris\Forms::disableUpload();
}

function can_upload_file()
{
    return Jaris\Forms::canUpload();
}

function process_file_uploads($field_name, $multiple_uploads = false)
{
    Jaris\Forms::processUploads($field_name, $multiple_uploads);
}

function get_upload_file_path($file_name)
{
    return Jaris\Forms::getUploadPath($file_name);
}

function delete_uploaded_files()
{
    Jaris\Forms::deleteUploads();
}

function is_required_field_empty($form_name)
{
    return Jaris\Forms::requiredFieldEmpty($form_name);
}

function generate_form($parameters, $fieldsets)
{
    return Jaris\Forms::generate($parameters, $fieldsets);
}

function forms_begin_fieldset($title, $collapsible=true, $collapsed=false)
{
    return Jaris\Forms::beginFieldset($title, $collapsible, $collapsed);
}

function forms_end_fieldset($description="")
{
    return Jaris\Forms::endFieldset($description);
}

function forms_add_field_after(array $field, $field_name, &$fieldset)
{
    Jaris\Forms::addFieldAfter($field, $field_name, $fieldset);
}

function forms_add_field_before(array $field, $field_name, &$fieldset)
{
    Jaris\Forms::addFieldBefore($field, $field_name, $fieldset);
}

function forms_add_field(array $field, $field_name, &$fieldset, $before=false)
{
    Jaris\Forms::addField($field, $field_name, $fieldset, $before);
}

function forms_add_fields(array $fields, $field_name, &$fieldset, $before=false)
{
    return Jaris\Forms::addFields($fields, $field_name, $fieldset, $before);
}

function forms_add_fieldsets(
    array $fieldsets,
    $position,
    &$fieldset,
    $before=false
)
{
    return Jaris\Forms::addFieldsets($fieldsets, $position, $fieldset, $before);
}

function global_files_add($source, $filename, $sub_path="", $move_file = true)
{
    return Jaris\Files::add($source, $filename, $sub_path, $move_file);
}

function global_files_add_upload($file_array, $sub_path="", $move_file = true)
{
    return Jaris\Files::addUpload($file_array, $sub_path, $move_file);
}

function global_files_delete($name, $sub_path="")
{
    return Jaris\Files::delete($name, $sub_path);
}

function global_files_get($name, $sub_path="")
{
    return Jaris\Files::get($name, $sub_path);
}

function global_files_directory($sub_path="")
{
    return Jaris\Files::getDir($sub_path);
}

function show_image($image_path)
{
    Jaris\Images::show($image_path);
}

function get_image_cache_name($original_image = null)
{
    return Jaris\Images::getCacheName($original_image);
}

function get_image_static_name($image_url, $full_url=true)
{
    return Jaris\Images::getStaticName($image_url, $full_url);
}

function get_image(
    $path, $width, $height = 0,
    $aspect_ratio = false, $background_color = "ffffff"
)
{
    return Jaris\Images::get(
        $path, $width, $height, $aspect_ratio, $background_color
    );
}

function image_resize(
    $path, $width, $height = 0, $aspect_ratio = false, $background_color = "ffffff"
)
{
    return Jaris\Images::resize(
        $path, $width, $height, $aspect_ratio, $background_color
    );
}

function make_image_transparent(&$image, $mime)
{
    Jaris\Images::makeTransparent($image, $mime);
}

function print_image($image)
{
    Jaris\Images::printIt($image);
}

function print_cache_image($path)
{
    Jaris\Images::printCached($path);
}

function print_user_pic($page)
{
    Jaris\Images::printUserPic($page);
}

function htmlhex_to_rgb($value)
{
    return Jaris\Images::hexToRGB($value);
}

function clear_image_cache()
{
    return Jaris\Images::clearCache();
}

function image_is_valid($path)
{
    return Jaris\Images::isValid($path);
}

function generate_language_cache($language, $files)
{
    return Jaris\Language::generateCache($language, $files);
}

function dt($data_file, $language_code = null, $force = false)
{
    return Jaris\Language::dataTranslate($data_file, $language_code, $force);
}

function get_languages()
{
    return Jaris\Language::getInstalled();
}

function get_language_info($language_code)
{
    return Jaris\Language::getInfo($language_code);
}

function get_language_name($language_code)
{
    return Jaris\Language::getName($language_code);
}

function language_exists($code)
{
    return Jaris\Language::exists($code);
}

function language_auto_detect()
{
    Jaris\Language::detect();
}

function language_codes()
{
    return Jaris\Language::getCodes();
}

function language_form()
{
    return Jaris\Language::generateForm();
}

function add_language($language_code, $name, $translator, $translator_email, $contributors)
{
    return Jaris\Language::add(
        $language_code, $name, $translator, $translator_email, $contributors
    );
}

function edit_language($language_code, $translator, $translator_email, $contributors)
{
    return Jaris\Language::edit(
        $language_code, $translator, $translator_email, $contributors
    );
}

function amount_translated($language_code)
{
    return Jaris\Language::amountTranslated($language_code);
}

function get_language_strings($language_code)
{
    return Jaris\Language::getStrings($language_code);
}

function add_language_string($language_code, $original_text, $translation)
{
    return Jaris\Language::addString($language_code, $original_text, $translation);
}

function delete_language_string($language_code, $original_text)
{
    return Jaris\Language::deleteString($language_code, $original_text);
}

function po_parser($file)
{
    return Jaris\Language::poParse($file);
}

function po_parser_with_headers($file)
{
    return Jaris\Language::poParseWithHeaders($file);
}

function po_writer($strings_array, $file)
{
    return Jaris\Language::poWrite($strings_array, $file);
}

function user_login()
{
    return Jaris\Authentication::login();
}

function user_logout()
{
    Jaris\Authentication::logout();
}

function protected_page($permissions = array())
{
    return Jaris\Authentication::protectedPage($permissions);
}

function user_has_permissions($permissions, $username = null)
{
    return Jaris\Authentication::userHasPermissions($permissions, $username);
}

function get_group_permission($permission_name, $group_name)
{
    return Jaris\Authentication::groupHasPermission($permission_name, $group_name);
}

function get_type_permission($type, $group_name, $username = "")
{
    return Jaris\Authentication::hasTypeAccess($type, $group_name, $username);
}

function set_group_permission($permission_name, $value, $group_name)
{
    return Jaris\Groups::setPermission($permission_name, $value, $group_name);
}

function get_permissions_array($group)
{
    return Jaris\Groups::getPermissions($group);
}

function send_email(
    $to, $subject, $html_message, $alt_message = "", $attachments = array(),
    $reply_to = array(), $bcc = array(), $cc = array(), $from = array()
)
{
    return Jaris\Mail::send(
        $to, $subject, $html_message, $alt_message, $attachments,
        $reply_to, $bcc, $cc, $from
    );
}

function send_user_reset_password_notification($username, $user_data, $password)
{
    return Jaris\Mail::sendPasswordNotification($username, $user_data, $password);
}

function send_registration_notification($username)
{
    return Jaris\Mail::sendRegistrationNotification($username);
}

function search_content(
    $keywords, $field_values = null, $categories = array(),
    $page = 1, $amount = 10
)
{
    Jaris\Search::start(
        $keywords, $field_values, $categories,
        $page, $amount
    );
}

function search_database($page = 1, $amount = 10)
{
    Jaris\Search::database($page, $amount);
}

function search_reindex_sqlite()
{
    return Jaris\Search::reindex();
}

function search_reindex_callback($content_path)
{
    Jaris\Search::reindexCallback($content_path);
}

function get_search_content_type()
{
    return Jaris\Search::contentType();
}

function check_content($content_path, $content_data = array())
{
    Jaris\Search::checkContent($content_path, $content_data);
}

function get_search_results($page, $amount)
{
    return Jaris\Search::getResults($page, $amount);
}

function print_search_navigation($page, $amount = 10, $search_uri = "search")
{
    Jaris\Search::printNavigation($page, $amount, $search_uri);
}

function add_result($result, $position = "append", $relevancy = null)
{
    Jaris\Search::addResult($result, $position, $relevancy);
}

function get_results()
{
    Jaris\Search::getAllResults();
}

function get_results_count()
{
    return Jaris\Search::getResultsCount();
}

function reset_results()
{
    Jaris\Search::reset();
}

function add_keywords($keywords)
{
    Jaris\Search::addKeywords($keywords);
}

function get_keywords()
{
    return Jaris\Search::getKeywords();
}

function add_fields($field_values)
{
    Jaris\Search::addFields($field_values);
}

function add_search_categories($categories)
{
    Jaris\Search::addCategories($categories);
}

function get_search_categories()
{
    return Jaris\Search::getCategories();
}

function get_fields()
{
    return Jaris\Search::getFields();
}

function highlight_search_results(
    $result, $input_format = "full_html", $type = "title"
)
{
    return Jaris\Search::highlightResults($result, $input_format, $type);
}

function strip_html_tags($text, $allowed_tags = "", $allowed_atts="")
{
    return Jaris\Util::stripHTMLTags($text, $allowed_tags, $allowed_atts);
}

function get_type_search_fields($type)
{
    return Jaris\Search::getTypeFields($type);
}

function jaris_sqlite_open($name, $directory = "")
{
    return Jaris\Sql::open($name, $directory);
}

function jaris_sqlite_attach_function($name, callable $function, $param_count, &$db)
{
    Jaris\Sql::attachFunction($name, $function, $param_count, $db);
}

function jaris_sqlite_attach($db_name, &$db, $directory = "")
{
    Jaris\Sql::attach($db_name, $db, $directory);
}

function jaris_sqlite_close(&$db)
{
    Jaris\Sql::close($db);
}

function jaris_sqlite_close_result(&$result)
{
    Jaris\Sql::closeResult($result);
}

function jaris_sqlite_turbo(&$db)
{
    Jaris\Sql::turbo($db);
}

function jaris_sqlite_escape_var(&$field, $type="string")
{
    Jaris\Sql::escapeVar($field, $type);
}

function jaris_sqlite_escape_array(&$fields)
{
    Jaris\Sql::escapeArray($fields);
}

function jaris_sqlite_insert_array_to_table($table_name, $data, &$db)
{
    return Jaris\Sql::insertArrayToTable($table_name, $data, $db);
}

function jaris_sqlite_delete_from_table(
    $database, $table, $clause, $directory = ""
)
{
    return Jaris\Sql::deleteFromTable($database, $table, $clause, $directory);
}

function jaris_sqlite_get_data_list(
    $database, $table, $page = 0, $limit = 30,
    $clause = "", $fields = "*", $directory = ""
)
{
    return Jaris\Sql::getDataList(
        $database, $table, $page, $limit,
        $clause, $fields, $directory
    );
}

function jaris_sqlite_query($query, &$db)
{
    return Jaris\Sql::query($query, $db);
}

function jaris_sqlite_last_insert_row_id(&$db)
{
    return Jaris\Sql::lastInsertRowId($db);
}

function jaris_sqlite_begin_transaction(&$db)
{
    return Jaris\Sql::beginTransaction($db);
}

function jaris_sqlite_commit(&$db)
{
    return Jaris\Sql::commitTransaction($db);
}

function jaris_sqlite_fetch_array(&$result)
{
    return Jaris\Sql::fetchArray($result);
}

function jaris_sqlite_db_exists($name, $directory = "")
{
    return Jaris\Sql::dbExists($name, $directory);
}

function jaris_sqlite_list_db($directory = "")
{
    return Jaris\Sql::listDB($directory);
}

function jaris_sqlite_count_column(
    $database, $table, $column,
    $where = "", $directory = "", $select_additional = ""
)
{
    return Jaris\Sql::countColumn(
        $database, $table, $column,
        $where, $directory, $select_additional
    );
}

function jaris_sqlite_backup($name)
{
    return Jaris\Sql::backup($name);
}

function jaris_sqlite_restore($name, &$fp)
{
    return Jaris\Sql::restore($name, $fp);
}

function page_not_found()
{
    return Jaris\System::pageNotFound();
}

function http_status($code)
{
    return Jaris\Site::setHTTPStatus($code);
}

function get_system_styles()
{
    return Jaris\System::getStyles();
}

function get_system_scripts()
{
    return Jaris\System::getScripts();
}

function system_pages_blacklist($check_path = null)
{
    return Jaris\System::pagesBlackList($check_path);
}

function check_if_not_installed()
{
    Jaris\System::checkIfNotInstalled();
}

function check_if_offline()
{
    Jaris\Site::checkIfOffline();
}

function error_message($type)
{
    return Jaris\System::errorMessage($type);
}

function is_system_page($uri = "", &$page_data=array())
{
    return Jaris\Pages::isSystem($uri, $page_data);
}

function is_ssl_supported()
{
    return Jaris\System::isSSLSupported();
}

function is_ssl_connection()
{
    return Jaris\System::isSSLConnection();
}

function add_edit_tab(&$page_data=null)
{
    Jaris\System::addEditTab($page_data);
}

function php_eval($text)
{
    return Jaris\System::evalPHP($text);
}

function initiate_error_catch_system()
{
    Jaris\System::initiateErrorCatchSystem();
}

function error_catch_hook($errno, $errmsg, $filename, $linenum, $vars)
{
    return Jaris\System::errorCatchHook($errno, $errmsg, $filename, $linenum, $vars);
}

function generate_admin_page_sections()
{
    return Jaris\System::generateAdminPageSections();
}

function generate_admin_page($sections)
{
    return Jaris\System::generateAdminPage($sections);
}

function get_user_browser()
{
    return Jaris\System::getUserBrowser();
}

function print_generic_navigation(
    $total_count, $page, $uri, $module = "",
    $amount = 30, $arguments = array()
)
{
    Jaris\System::printNavigation(
        $total_count, $page, $uri, $module,
        $amount, $arguments
    );
}

function print_breadcrumb($separator = "&gt;")
{
    return Jaris\System::generateBreadcrumb($separator);
}

function add_hidden_url_parameters($parameters, $type = "get")
{
    return Jaris\System::addHiddenUrlParameters($parameters, $type);
}

function append_hidden_parameters()
{
    Jaris\System::appendHiddenParameters();
}

function print_content_preview(
    $string, $word_count = 30, $display_suspensive_points = false
)
{
    return Jaris\Util::contentPreview(
        $string, $word_count, $display_suspensive_points
    );
}

function cache_page_if_possible($uri, $page_data)
{
    Jaris\System::cachePageIfPossible($uri, $page_data);
}

function fast_cache_if_possible($uri)
{
    Jaris\System::fastCacheIfPossible($uri);
}

function save_page_to_cache_if_possible($uri, $page_data, $content)
{
    Jaris\System::savePageToCacheIfPossible(
        $uri, $page_data, $content
    );
}

function data_directory()
{
    return Jaris\Site::dataDir();
}

function get_current_site()
{
    return Jaris\Site::current();
}

function add_style($path, $arguments = array())
{
    Jaris\View::addStyle($path, $arguments);
}

function add_script($path, $arguments = array())
{
    Jaris\View::addScript($path, $arguments);
}

function add_tab($name, $uri, $arguments = array(), $row = 0)
{
    Jaris\View::addTab($name, $uri, $arguments, $row);
}

function add_message($message, $type = "normal")
{
    Jaris\View::addMessage($message, $type);
}

function get_page_meta_tags(&$page_data=array())
{
    return Jaris\View::getMetaTagsHTML($page_data);
}

function theme_content($content_list, $page)
{
    return Jaris\View::getContentHTML($content_list, $page);
}

function theme_block($arrData, $position, $page)
{
    return Jaris\View::getBlocksHTML($arrData, $position, $page);
}

function theme_content_block($arrData, $position, $page, $page_type)
{
    return Jaris\View::getContentBlocksHTML($arrData, $position, $page, $page_type);
}

function theme_styles($styles)
{
    return Jaris\View::getStylesHTML($styles);
}

function theme_scripts($scripts)
{
    return Jaris\View::getScriptsHTML($scripts);
}

function theme_tabs($tabs_array)
{
    return Jaris\View::getTabsHTML($tabs_array);
}

function theme_messages()
{
    return Jaris\View::getMessagesHTML();
}

function theme_display($page, $page_data, $content, $left, $center, $right, $header, $footer)
{
    return Jaris\View::render(
        $page, $page_data, $content, $left, $center, $right, $header, $footer
    );
}

function block_template($position, $page, $id)
{
    return Jaris\View::blockTemplate($position, $page, $id);
}

function content_block_template($position, $page, $page_type, $id)
{
    return Jaris\View::contentBlockTemplate($position, $page, $page_type, $id);
}

function page_template($page)
{
    return Jaris\View::pageTemplate($page);
}

function content_template($page, $type)
{
    return Jaris\View::contentTemplate($page, $type);
}

function user_profile_template($group, $username)
{
    return Jaris\View::userProfileTemplate($group, $username);
}

function search_template($page, $results_type = "all", $template_type = "result")
{
    return Jaris\View::searchTemplate($page, $results_type, $template_type);
}

function get_themes()
{
    return Jaris\Themes::getList();
}

function theme_directory($name="")
{
    return Jaris\Themes::directory($name);
}

function get_themes_path()
{
    return Jaris\Themes::getUploadPath();
}

function get_enabled_themes()
{
    return Jaris\Themes::getEnabled();
}

function get_theme_info($path)
{
    return Jaris\Themes::get($path);
}

function get_default_theme()
{
    return Jaris\Themes::getDefault();
}

function get_user_theme()
{
    return Jaris\Themes::getUserTheme();
}

function get_timezones()
{
    return Jaris\Timezones::getList();
}

function translate_page($page, $data, $language_code)
{
    return Jaris\Translate::page($page, $data, $language_code);
}

function move_page_translations($actual_uri, $new_uri)
{
    return Jaris\Translate::movePage($actual_uri, $new_uri);
}

function delete_page_translations($page)
{
    return Jaris\Translate::deletePage($page);
}

function translate_block($data, $language_code)
{
    return Jaris\Translate::block($data, $language_code);
}

function remove_empty_directories_language($path, $code)
{
    Jaris\Translate::removeEmptyDirectories($path, $code);
}

function get_uri_type($uri)
{
    return Jaris\Uri::type($uri);
}

function translate_image_uri($uri)
{
    return Jaris\Uri::getImagePath($uri);
}

function translate_file_uri($path)
{
    return Jaris\Uri::getFilePath($path);
}

function translate_user_picture_uri($path)
{
    return Jaris\Uri::getUserPicturePath($path);
}

function goto_page($uri, $arguments = array(), $ssl = false)
{
    return Jaris\Uri::go($uri, $arguments, $ssl);
}

function url_exists($url)
{
    return Jaris\Uri::urlExists($url);
}

function text_to_uri($string, $allow_slashes = false)
{
    return Jaris\Uri::fromText($string, $allow_slashes);
}

function generate_uri_for_type($type, $title, $user)
{
    return Jaris\Types::generateURI($type, $title, $type);
}

function hook_module(
    $hook_function, &$var1 = "null", &$var2 = "null",
    &$var3 = "null", &$var4 = "null"
)
{
    Jaris\Modules::hook(
        $hook_function, $var1, $var2,
        $var3, $var4
    );
}

function module_directory($name="")
{
    return Jaris\Modules::directory($name);
}

function get_modules_path()
{
    return Jaris\Modules::getUploadPath();
}

function get_modules()
{
    return Jaris\Modules::getAll();
}

function get_module($name)
{
    return Jaris\Modules::get($name);
}

function get_installed_modules()
{
    return Jaris\Modules::getInstalled();
}

function get_installed_version_module($name)
{
    return Jaris\Modules::getInstalledVersion($name);
}

function check_module_dependecies($name)
{
    return Jaris\Modules::checkDependecies($name);
}

function is_module_dependency($name)
{
    return Jaris\Modules::isDependency($name);
}

function install_module($name, &$needs_dependency = false)
{
    return Jaris\Modules::install($name, $needs_dependency);
}

function uninstall_module($name, &$is_dependency = false)
{
    return Jaris\Modules::uninstall($name, $is_dependency);
}

function upgrade_module($name)
{
    return Jaris\Modules::upgrade($name);
}

function get_page_uri_module($original_uri, $module_name)
{
    return Jaris\Modules::getPageUri($original_uri, $module_name);
}

function get_module_page_path($page)
{
    return Jaris\Modules::getPagePath($page);
}