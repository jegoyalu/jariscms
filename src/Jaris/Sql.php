<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Contains functions to manage sql connections and data.
 */
class Sql
{

/**
 * Receives parameters: $name, $directory, $db
 * @var string
 */
const SIGNAL_SQL_OPEN = "hook_jaris_sqlite_open";

/**
 * Open a database file for use in the data/sqlite directory.
 *
 * @param string $name The name of the database file.
 * @param string $directory Relative path where the db resides.
 *
 * @return resource|bool Database handle or false on failure.
 * @original jaris_sqlite_open
 */
static function open($name, $directory = "")
{
    if(!$directory)
    {
        $directory = Site::dataDir() . "sqlite/";
    }

    $directory = rtrim($directory, "/") . "/";

    $db = null;
    $error = "";
    $opened = false;
    $db_path = $directory . $name;

    if(class_exists("SQLite3"))
    {
        $sqlite3_class_error = "";
        ob_start();
        $db = new \SQLite3($db_path);
        $db->busyTimeout(10000);
        $result = $db->query("select * from sqlite_master");
        $sqlite3_class_error = ob_get_contents();
        ob_end_clean();

        if($sqlite3_class_error == "")
        {
            $opened = true;
        }
    }

    if(class_exists("PDO") && !$opened)
    {
        try
        {
            $db = new \PDO("sqlite:$db_path");
            $db->setAttribute(\PDO::ATTR_TIMEOUT, 10000);

            //Check if database format file is version 3
            $result = $db->query("select * from sqlite_master");

            if($result)
            {
                $opened = true;
            }
        }
        catch(\PDOException $exception)
        {
            $opened = false;
        }
    }

    if(!$opened)
    {
        $db = sqlite_open($db_path, 0600, $error);
        sqlite_busy_timeout($db, 10000);
    }


    if($error != "")
    {
        View::addMessage($error, "error");
    }
    else
    {
        //Inject text search functions to sqlite (UDF)
        $udf_text_search_functions = new SearchAddons($db);

        //Hook useful to inject user defined functions to data bases
        Modules::hook("hook_jaris_sqlite_open", $name, $directory, $db);
    }

    return $db;
}

/**
 * Attach a custom function to the database.
 * @param string $name
 * @param callable $function
 * @param int $param_count
 * @param resource|object $db
 * @original jaris_sqlite_attach_function
 */
static function attachFunction($name, callable $function, $param_count, &$db)
{
    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        $db->createFunction($name, $function, $param_count);
    }
    elseif(gettype($db) == "object")
    {
        $db->sqliteCreateFunction($name, $function, $param_count);
    }
    else
    {
        sqlite_create_function($db, $name, $function, $param_count);
    }
}

/**
 * Attaches another database to an already opened database to be
 * able to make table joins from different databases
 *
 * @param string $db_name Name of the database to attach.
 * @param resource $db Currently opened database object.
 * @param string $directory Optional path where the database to attach resides.
 * @original jaris_sqlite_attach
 */
static function attach($db_name, &$db, $directory = "")
{
    if(!$directory)
    {
        $directory = Site::dataDir() . "sqlite/";
    }

    $directory = rtrim($directory, "/") . "/";

    self::query(
        "attach database '{$directory}{$db_name}' as $db_name",
        $db
    );
}

/**
 * Close sqlite database connection
 *
 * @param resource $db Object to opened database.
 * @original jaris_sqlite_close
 */
static function close(&$db)
{
    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        @$db->close();
        unset($db);
    }
    elseif(gettype($db) == "object")
    {
        unset($db);
    }
    else
    {
        sqlite_close($db);
        unset($db);
    }
}

/**
 * Uninitialize sqlite database result. This is a dummy
 * function to (seems not to work)
 * remember that sometimes not unsetting a database result can result
 * (:D) in database lock ups.
 *
 * @param resource $result The result of a database query.
 * @original jaris_sqlite_close_result
 */
static function closeResult(&$result)
{
    unset($result);
}

/**
 * Turns synchrounous off for more speed at writing
 *
 * @param resource $db Object to opened database.
 * @original jaris_sqlite_turbo
 */
static function turbo(&$db)
{
    self::query("PRAGMA cache_size=10240", $db);
    self::query("PRAGMA temp_store=MEMORY", $db);
    self::query("PRAGMA synchronous=OFF", $db);
    //Turn this off because it was converting database from WAL back to Delete.
    //jaris_sqlite_query("PRAGMA journal_mode=OFF", $db);
    self::query("PRAGMA cache_spill=OFF", $db);
}

/**
 * Function to escape quotes ' to doueble quotes ''
 *
 * @param string $field Reference to the variable to escape its value.
 * @param string $type Can be string, int, float
 * @original jaris_sqlite_escape_var
 */
static function escapeVar(&$field, $type="string")
{
    switch($type)
    {
        case "string":
            $field = str_replace("'", "''", $field);
            break;
        case "int":
            $field = intval($field);
            break;
        case "float":
            $field = floatval($field);
    }
}

/**
 * Function to escape quotes ' to doueble quotes ''
 *
 * @param array $fields Reference to the array to escape its values.
 * @original jaris_sqlite_escape_array
 */
static function escapeArray(&$fields)
{
    foreach($fields as $name => $value)
    {
        $fields[$name] = str_replace("'", "''", $value);
    }
}

/**
 * Inserts an array to a table in a given database.
 *
 * @param string $table_name Name of the table.
 * @param array $data In the format $data["colum_name"] = "value"
 * @param resource $db Reference to the db that has the table
 * where you want to insert data.
 *
 * @return bool true on success or false on fail.
 * @original jaris_sqlite_insert_array_to_table
 */
static function insertArrayToTable($table_name, $data, &$db)
{
    $columns = "";
    $values = "";
    foreach($data as $column_name => $value)
    {
        $columns .= "$column_name,";
        $values .= "'" . str_replace("'", "''", $value) . "',";
    }

    $columns = trim($columns, ",");
    $values = trim($values, ",");

    $insert = "insert into $table_name ($columns) values($values)";

    if(!self::query($insert, $db))
    {
        return false;
    }

    return true;
}

/**
 * Generic function to delete records from a database
 *
 * @param string $database the file name of the database
 * @param string $table the table where operating delete
 * @param string $clause a condion clause like where
 * @param string $directory optinal path to the database file
 *
 * @return bool true on success or false on fail.
 * @original jaris_sqlite_delete_from_table
 */
static function deleteFromTable(
    $database, $table, $clause, $directory = ""
)
{
    if(self::dbExists($database, $directory))
    {
        $db = self::open($database, $directory);
        self::query("delete from $table $clause", $db);
        self::close($db);

        return true;
    }

    return false;
}

/**
 * To retrieve a list of data from sqlite database to generate a browser
 *
 * @param string $database File name of the database.
 * @param string $table the Name were we are retrieving a list.
 * @param int $page Current page count of pages list is being browser.
 * @param int $limit Amount of data per page to display.
 * @param string $clause Optional clause for the query like where, order by etc.
 * @param string $fields Optional fields seperated by comma or functions
 * like count(field) as result_name.
 * @param string $directory Optional path to database file.
 *
 * @return array List of result data not longer than $limit
 * @original jaris_sqlite_get_data_list
 */
static function getDataList(
    $database, $table, $page = 0, $limit = 30,
    $clause = "", $fields = "*", $directory = ""
)
{
    // To protect against sql injections be sure $page is a int
    if(!is_numeric($page))
    {
        $page = 0;
    }
    else
    {
        $page = intval($page);
    }

    $db = null;
    $page *= $limit;
    $data = array();

    if(self::dbExists($database, $directory))
    {
        $db = self::open($database, $directory);
        self::turbo($db);
        $result = self::query(
            "select $fields from $table $clause limit $page, $limit", $db
        );
    }
    else
    {
        return $data;
    }

    $fields = array();

    if($fields = self::fetchArray($result))
    {
        $data[] = $fields;

        while($fields = self::fetchArray($result))
        {
            $data[] = $fields;
        }

        self::close($db);
        return $data;
    }
    else
    {
        self::close($db);
        return $data;
    }
}

/**
 * Same as normal sqlite_query but with the SQLITE_ASSOC
 * passed on and error reporting.
 *
 * @param string $query SQL statement to execute.
 * @param resource|object $db Database handle.
 *
 * @return resource|object|bool Result handle or false on failure.
 * @original jaris_sqlite_query
 */
static function query($query, &$db)
{
    $development_mode = Site::$development_mode;

    $error = "";

    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        ob_start();
        try
        {
            $result = $db->query($query);
            $error = ob_get_contents();

            if($db->lastErrorMsg() != "not an error")
            {
                $output = date("c", time()) . ": ";
                $output .= $query;
                $output .= "\n" . $db->lastErrorMsg() . "\n\n";

                if($development_mode)
                {
                    View::addMessage("(" . $query .") " . $db->lastErrorMsg(), "error");
                }

                file_put_contents("sqlite.errors", $output, FILE_APPEND);
            }
        }
        catch(\Exception $e)
        {
            $output = date("c", time()) . ": ";
            $output .= $query;
            $output .= "\n" . $e->getMessage() . "\n\n";

            if($development_mode)
            {
                View::addMessage("(" . $query .") " . $e->getMessage(), "error");
            }

            file_put_contents("sqlite.errors", $output, FILE_APPEND);
        }
        ob_end_clean();
    }
    elseif(gettype($db) == "object")
    {
        try
        {
            $result = $db->prepare($query);
            $result->execute();
        }
        catch(\PDOException $exception)
        {
            $error = $exception->getMessage();
            $output = date("c", time()) . ": ";
            $output .= $query;
            $output .= "\n" . $error . "\n\n";

            if($development_mode)
            {
                View::addMessage("(" . $query .") " . $error, "error");
            }

            file_put_contents("sqlite.errors", $output, FILE_APPEND);
        }
    }
    else
    {
        $result = sqlite_unbuffered_query($db, $query, SQLITE_ASSOC, $error);
    }

    if($error != "")
    {
        View::addMessage($error, "error");
    }

    return $result;
}

/**
 * Get the id of last inserted row.
 * @param resource|object $db
 * @return int
 * @original jaris_sqlite_last_insert_row_id
 */
static function lastInsertRowId(&$db)
{
    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        return $db->lastInsertRowID();
    }
    elseif(gettype($db) == "object")
    {
        return $db->lastInsertId();
    }
    else
    {
        return sqlite_last_insert_rowid($db);
    }

    return 0;
}

/**
 * Starts a transaction which is faster for insertions or updates.
 *
 * @param resource $db Database handle.
 *
 * @return resource|bool Result handle or false on failure.
 * @original jaris_sqlite_begin_transaction
 */
static function beginTransaction(&$db)
{
    return self::query("begin transaction", $db);
}

/**
 * Ends a transaction.
 *
 * @param resource $db Database handle.
 *
 * @return resource|bool Result handle or false on failure.
 * @original jaris_sqlite_commit
 */
static function commitTransaction(&$db)
{
    return self::query("commit transaction", $db);
}

/**
 * Same as normal sqlite_fetch_array but with the SQLITE_ASSOC passed.
 *
 * @param resource|object $result An sqlite resource result of a statement.
 *
 * @return array|bool Data results or false for no data.
 * @original jaris_sqlite_fetch_array
 */
static function fetchArray(&$result)
{
    if(gettype($result) == "object" && class_exists("SQLite3"))
    {
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    elseif(gettype($result) == "object")
    {
        return $result->fetch(\PDO::FETCH_ASSOC);
    }
    else
    {
        return sqlite_fetch_array($result, SQLITE_ASSOC);
    }

    return false;
}

/**
 * Checks if the given sqlite database exists in the data/sqlite directory
 *
 * @param string $name The name of the database file.
 * @param string $directory Optional path to database file.
 *
 * @return bool True if exist false if not.
 * @original jaris_sqlite_db_exists
 */
static function dbExists($name, $directory = "")
{
    if(!$directory)
    {
        $directory = Site::dataDir() . "sqlite/";
    }

    $directory = rtrim($directory, "/") . "/";

    return file_exists($directory . $name);
}

/**
 * List all the databases available on the system.
 *
 * @param string $directory Optional path for database files.
 *
 * @return  array All the databases available on the system.
 * @original jaris_sqlite_list_db
 */
static function listDB($directory = "")
{
    if(!$directory)
    {
        $directory = Site::dataDir() . "sqlite/";
    }

    $directory = rtrim($directory, "/") . "/";

    $dh = opendir($directory);

    $databases = array();

    while(($file = readdir($dh)) !== false)
    {
        if(is_file($directory . $file) && !preg_match("/(.*)(\.sql)/", $file))
        {
            $databases[] = $file;
        }
    }

    closedir($dh);

    return $databases;
}

/**
 * Counts a given column
 *
 * @param string $database Name of database where table resides
 * @param string $table Name of table where column resides.
 * @param string $column The column to count.
 * @param string $where Optional parameter to indicate a where clause,
 * example: "where user='test'"
 * @param string $directory Optional path to database file.
 * @param string $select_additional Additional fields to select.
 *
 * @return int count
 * @original jaris_sqlite_count_column
 */
static function countColumn(
    $database, $table, $column,
    $where = "", $directory = "", $select_additional = ""
)
{
    if(self::dbExists($database, $directory))
    {
        $db = self::open($database, $directory);

        self::turbo($db);

        if($select_additional != "")
        {
            $select_additional = ", " . ltrim(trim($select_additional), ",");
        }

        $result = self::query(
            "select count($column) as 'total_count' $select_additional "
            . "from $table $where", $db
        );

        $count = self::fetchArray($result);

        self::close($db);

        return $count["total_count"];
    }
    else
    {
        return 0;
    }
}

/**
 * Creates an sql file backup of all database tables.
 *
 * @param string $name The name of the database to backup.
 * @original jaris_sqlite_backup
 */
static function backup($name)
{
    if(self::dbExists($name))
    {
        $backup_path = Site::dataDir() . "sqlite/" . $name . ".sql";

        $db = self::open($name);

        $result = self::query(
            "select * from sqlite_master where type = 'table' order by name asc",
            $db
        );

        $tables = array();

        while($row = self::fetchArray($result))
        {
            $tables[] = $row;
        }

        $backup_file = fopen($backup_path, "w");

        foreach($tables as $values)
        {
            fwrite(
                $backup_file,
                "/*CREATE " .
                strtoupper($values["name"]) .
                " TABLE*/" . "\n"
            );

            fwrite(
                $backup_file,
                $values["sql"] . ";\n\n"
            );

            fwrite(
                $backup_file,
                "/*INSERT ALL " .
                strtoupper($values["name"]) .
                " TABLE DATA*/" . "\n"
            );

            $result = self::query(
                "select * from " . $values["name"],
                $db
            );

            while($row = self::fetchArray($result))
            {
                $column_name_string = "";
                $column_name_array = array();

                $column_value_string = "";
                $column_value_array = array();

                foreach($row as $colum_name => $colum_value)
                {
                    $column_name_array[] = $colum_name;
                    $column_value_array[] = "'" . str_replace(
                        array("'", "\r", "\n"),
                        array("''", "\\r", "\\n"),
                        $colum_value
                    ) . "'";
                }

                $column_name_string = "(" .
                    implode(",", $column_name_array) .
                    ")"
                ;

                $column_value_string = "(" .
                    implode(",", $column_value_array) .
                    ")"
                ;

                $insert = "insert into " . $values["name"] . " " .
                    $column_name_string . " values " . $column_value_string
                ;

                fwrite($backup_file, $insert . ";\n");
            }

            fwrite($backup_file, "\n");
        }

        fclose($backup_file);

        self::close($db);
    }
}

/**
 * Restores or creates a database from a .sql file pointer.
 *
 * @param string $name The name of the database.
 * @param resource $fp A pointer to a file.
 * @original jaris_sqlite_restore
 */
static function restore($name, &$fp)
{
    unlink(Site::dataDir() . "sqlite/$name");

    $db = self::open($name);

    while(!feof($fp))
    {
        $sql_statement = fgets($fp);

        //Ignore empty lines and comments
        if(
            $sql_statement != "" &&
            !preg_match("/^(\/\*)(.*)(\*\/)$/", $sql_statement)
        )
        {
            $sql_statement = str_replace(
                array("\\r", "\\n"),
                array("\r", "\n"),
                $sql_statement
            );

            self::query($sql_statement, $db);
        }
    }
}

}