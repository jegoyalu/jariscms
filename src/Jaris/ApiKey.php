<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Facilities to handle api keys.
 */
class ApiKey
{

/**
 * Create an api key database if doesn't exists.
 * @original api_key_create_db
 */
public static function createDatabase()
{
    if(Sql::dbExists("api_keys"))
        return;

    $db = Sql::open("api_keys");

    Sql::query(
        "create table api_keys ("
        . "id integer primary key, "
        . "key text, "
        . "description text, "
        . "username text, "
        . "ip_host text, "
        . "created_date text, "
        . "permissions text, "
        . "token text, "
        . "token_expires text"
        . ")",
        $db
    );

    Sql::query(
        "create index api_keys_index on api_keys ( "
        . "key desc, "
        . "username desc, "
        . "created_date desc, "
        . "token desc"
        . ")",
        $db
    );

    Sql::close($db);
}

/**
 * Adds new api key.
 * @param array $data
 * @return string The generated api key.
 * @original api_key_add
 */
public static function add($data)
{
    $db = Sql::open("api_keys");

    Sql::escapeArray($data);

    $key = Users::generatePassword(64);

    // Make sure we are generating a unique api key
    while(self::isValid($key))
        $key = Users::generatePassword(64);

    $key_orig = $key;
    $key = str_replace("'", "''", $key);

    $insert = "insert into api_keys "
        . "("
        . "key,"
        . "description,"
        . "username,"
        . "ip_host,"
        . "created_date, "
        . "permissions"
        . ") "
        . "values("
        . "'$key', "
        . "'{$data['description']}', "
        . "'{$data['username']}', "
        . "'{$data['ip_host']}', "
        . "'".time()."', "
        . "'".str_replace("'", "''", serialize(array()))."'"
        . ")"
    ;

    Sql::query($insert, $db);

    Sql::close($db);

    return $key_orig;
}

/**
 * Edits an api key.
 * @param string $key
 * @param array $data
 * @original api_key_edit
 */
public static function edit($key, $data)
{
    $db = Sql::open("api_keys");

    Sql::escapeArray($data);

    $key = str_replace("'", "''", $key);

    $update = "update api_keys set "
        . "key='$key',"
        . "description='{$data['description']}',"
        . "username='{$data['username']}', "
        . "ip_host='{$data['ip_host']}' "
        . "where key='$key'"
    ;

    Sql::query($update, $db);

    Sql::close($db);
}

/**
 * Deletes an api key.
 * @param string $key
 * @original api_key_delete
 */
public static function delete($key)
{
    $db = Sql::open("api_keys");

    $key = str_replace("'", "''", $key);

    $delete = "delete from api_keys where key='$key'";

    Sql::query($delete, $db);

    Sql::close($db);
}

/**
 * Retreive the data of api key by its key.
 * @param string $key
 * @return array|null
 * @original api_key_get_data
 */
public static function getData($key)
{
    $db = Sql::open("api_keys");

    $key = str_replace("'", "''", $key);

    $select = "select * from api_keys where key='$key'";

    $result = Sql::query($select, $db);

    $data = Sql::fetchArray($result);

    if(is_array($data))
    {
        $data["permissions"] = unserialize($data["permissions"]);
    }

    Sql::close($db);

    return $data;
}

/**
 * Retreive the data of api key by its id.
 * @param string $id
 * @return array|null
 * @original api_key_get_data_by_id
 */
public static function getDataById($id)
{
    $db = Sql::open("api_keys");

    $id = intval($id);

    $select = "select * from api_keys where id=$id";

    $result = Sql::query($select, $db);

    $data = Sql::fetchArray($result);

    if(is_array($data))
    {
        $data["permissions"] = unserialize($data["permissions"]);
    }

    Sql::close($db);

    return $data;
}

/**
 * Retreive the data of api key by its token.
 * @param string $token
 * @return array|null
 * @original api_key_get_data_by_token
 */
public static function getDataByToken($token)
{
    $db = Sql::open("api_keys");

    $token = str_replace("'", "''", $token);

    $select = "select * from api_keys where token='$token'";

    $result = Sql::query($select, $db);

    $data = Sql::fetchArray($result);

    if(is_array($data))
    {
        $data["permissions"] = unserialize($data["permissions"]);
    }

    Sql::close($db);

    return $data;
}

/**
 * Check if a given api key is valid.
 * @param string $key
 * @return bool
 * @original api_key_valid
 */
public static function isValid($key)
{
    return is_array(self::getData($key));
}

/**
 * Sets all permissions for a key.
 * @param string $key
 * @param array $permissions
 * @original api_key_set_permissions
 */
public static function setPermissions($key, $permissions)
{
    $db = Sql::open("api_keys");

    Sql::escapeArray($permissions);

    $key = str_replace("'", "''", $key);

    $permissions = str_replace("'", "''", serialize($permissions));

    $update = "update api_keys set "
        . "permissions='$permissions' "
        . "where key='$key'"
    ;

    Sql::query($update, $db);

    Sql::close($db);
}

/**
 * Get the list of permissions for the api key.
 * @param string $key
 * @return array
 * @original api_key_get_permissions
 */
public static function getPermissions($key)
{
    $data = self::getData($key);

    if(is_array($data))
    {
        return $data["permissions"];
    }

    return array();
}

/**
 * Check if a given api key has a specific permission.
 * @param string $key
 * @param string|array $permission
 * @return bool
 * @original api_key_has_permission
 */
public static function hasPermission($key, $permission)
{
    $permissions = self::getPermissions($key);

    if(!is_array($permission))
    {
        if(isset($permissions[$permission]))
            return $permissions[$permission];
    }
    else
    {
        foreach($permission as $current_permission)
        {
            if(isset($permissions[$current_permission]))
            {
                // In case permission is set to false
                if(!$permissions[$current_permission])
                    return false;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    return false;
}

}