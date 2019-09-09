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
     * @param int $busy_timeout The amount of time to wait in milliseconds
     * if the database is locked. Default: 60000 or 60 seconds.
     *
     * @return mixed|bool Database handle or false on failure.
     */
    public static function open(
    string $name,
    string $directory = "",
    int $busy_timeout=60000
) {
        if (!$directory) {
            $directory = Site::dataDir() . "sqlite/";
        }

        $directory = rtrim($directory, "/") . "/";

        $db = null;
        $db_path = $directory . $name;

        if (class_exists("PDO")) {
            $db = new \PDO("sqlite:$db_path");
            $db->setAttribute(\PDO::ATTR_TIMEOUT, $busy_timeout/1000);
        } elseif (class_exists("SQLite3")) {
            $sqlite3_class_error = "";

            ob_start();
            $db = new \SQLite3($db_path);
            $db->busyTimeout($busy_timeout);
            $sqlite3_class_error = ob_get_contents();
            ob_end_clean();

            $db->filename = $db_path;

            if ($sqlite3_class_error != "") {
                throw new \Exception(
                $sqlite3_class_error
            );
            }
        } else {
            throw new \Exception(
            "You have to install PDO with SQLite support or SQLite3 extension."
        );
        }

        //Inject text search functions to sqlite (UDF)
        $udf_text_search_functions = new SearchAddons($db);

        //Hook useful to inject user defined functions to data bases
        Modules::hook("hook_jaris_sqlite_open", $name, $directory, $db);

        return $db;
    }

    /**
     * Attach a custom function to the database.
     *
     * @param string $name
     * @param callable $function
     * @param int $param_count
     * @param resource|object $db
     */
    public static function attachFunction(
    string $name,
    callable $function,
    int $param_count,
    &$db
): void {
        if (get_class($db) == "PDO") {
            $db->sqliteCreateFunction($name, $function, $param_count);
        } elseif (get_class($db) == "SQLite3") {
            $db->createFunction($name, $function, $param_count);
        } else {
            throw new \Exception("Invalid SQLite object.");
        }
    }

    /**
     * Attaches another database to an already opened database to be
     * able to make table joins from different databases
     *
     * @param string $db_name Name of the database to attach.
     * @param resource $db Currently opened database object.
     * @param string $directory Optional path where the database to attach resides.
     */
    public static function attach(string $db_name, &$db, string $directory = ""): void
    {
        if (get_class($db) == "SQLite3") {
            if (!isset($db->attachments)) {
                $db->attachments = [];
            }

            $db->attachments[] = [
            "dir" => $directory,
            "db" => $db_name
        ];
        }
    
        if (!$directory) {
            $directory = Site::dataDir() . "sqlite/";
        }

        $directory = rtrim($directory, "/") . "/";

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $directory = getcwd() . "\\" . str_replace("/", "\\", $directory);
        }

        self::query(
        "attach database '{$directory}{$db_name}' as $db_name",
        $db
    );
    }

    /**
     * Close sqlite database connection
     *
     * @param mixed $db Object to opened database.
     */
    public static function close(&$db): void
    {
        if (get_class($db) == "PDO") {
            unset($db);
        } elseif (get_class($db) == "SQLite3") {
            @$db->close();
            unset($db);
        } else {
            throw new \Exception("Invalid SQLite object.");
        }
    }

    /**
     * Uninitialize sqlite database result. This is a dummy
     * function to (seems not to work)
     * remember that sometimes not unsetting a database result can result
     * (:D) in database lock ups.
     *
     * @param resource $result The result of a database query.
     */
    public static function closeResult(&$result): void
    {
        unset($result);
    }

    /**
     * Turns synchrounous off for more speed at writing
     *
     * @param resource $db Object to opened database.
     * @param string $sync Can be FULL, NORMAL and OFF,
     * for databases in wal mode NORMAL is safe and much faster than FULL,
     * by default this function uses OFF which is the fastest but unsafe.
     */
    public static function turbo(&$db, $sync="OFF"): void
    {
        //self::query("PRAGMA cache_size=10240", $db);
        //self::query("PRAGMA temp_store=MEMORY", $db);
        self::query("PRAGMA synchronous=$sync", $db);
        //Turn this off because it was converting database from WAL back to Delete.
    //jaris_sqlite_query("PRAGMA journal_mode=OFF", $db);
    //self::query("PRAGMA cache_spill=OFF", $db);
    }

    /**
     * Function to escape quotes ' to doueble quotes ''
     *
     * @param mixed $field Reference to the variable to escape its value.
     * @param string $type Can be string, int, float
     */
    public static function escapeVar(&$field, string $type="string"): void
    {
        switch ($type) {
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
     */
    public static function escapeArray(array &$fields): void
    {
        foreach ($fields as $name => $value) {
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
     */
    public static function insertArrayToTable(string $table_name, array $data, &$db): bool
    {
        $columns = "";
        $values = "";
        foreach ($data as $column_name => $value) {
            $columns .= "$column_name,";
            $values .= "'" . str_replace("'", "''", $value) . "',";
        }

        $columns = trim($columns, ",");
        $values = trim($values, ",");

        $insert = "insert into $table_name ($columns) values($values)";

        if (!self::query($insert, $db)) {
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
     */
    public static function deleteFromTable(
    string $database,
    string $table,
    string $clause,
    string $directory = ""
): bool {
        if (self::dbExists($database, $directory)) {
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
     */
    public static function getDataList(
    string $database,
    string $table,
    int $page = 0,
    int $limit = 30,
    string $clause = "",
    string $fields = "*",
    string $directory = ""
): array {
        // To protect against sql injections be sure $page is a int
        if (!is_numeric($page)) {
            $page = 0;
        } else {
            $page = intval($page);
        }

        $db = null;
        $page *= $limit;
        $data = [];
        $result = null;

        if (self::dbExists($database, $directory)) {
            $db = self::open($database, $directory);

            $result = self::query(
            "select $fields from $table $clause limit $page, $limit",
            $db
        );
        } else {
            return $data;
        }

        $fields = [];

        if ($fields = self::fetchArray($result)) {
            $data[] = $fields;

            while ($fields = self::fetchArray($result)) {
                $data[] = $fields;
            }
        }

        unset($result);
        self::close($db);
        return $data;
    }

    /**
     * Same as normal sqlite_query but with the SQLITE_ASSOC
     * passed on and error reporting.
     *
     * @param string $query SQL statement to execute.
     * @param resource|object $db Database handle.
     *
     * @return resource|object|bool Result handle or false on failure.
     */
    public static function query(string $query, &$db)
    {
        $development_mode = Site::$development_mode;

        if (get_class($db) == "PDO") {
            try {
                $result = $db->prepare($query);
                $result->execute();
            } catch (\PDOException $exception) {
                $error = $exception->getMessage();
                $output = date("c", time()) . ": ";
                $output .= $query;
                $output .= "\n" . $error . "\n\n";

                if ($development_mode) {
                    View::addMessage("(" . $query .") " . $error, "error");
                }

                file_put_contents("sqlite.errors", $output, FILE_APPEND);

                throw new \Exception(
                $output,
                $exception->getCode()
            );
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
                $output = date("c", time()) . ": ";
                $output .= $query;
                $output .= "\n" . $error . "\n\n";

                if ($development_mode) {
                    View::addMessage("(" . $query .") " . $error, "error");
                }

                file_put_contents("sqlite.errors", $output, FILE_APPEND);

                throw new \Exception(
                $output,
                $exception->getCode()
            );
            }
        } elseif (get_class($db) == "SQLite3") {
            try {
                $result = @$db->query($query);

                if ($db->lastErrorMsg() != "not an error") {
                    $syntax_error = true;
                    if (strstr($db->lastErrorMsg(), "database is locked") !== false) {
                        $time_spent = 0;
                        $locked = true;
                        while ($locked && $time_spent < 60000 /*60 seconds*/) {
                            $db->close();
                            $db->open($db->filename);
                            if (isset($db->attachments)) {
                                foreach ($db->attachments as $attachment) {
                                    self::attach(
                                    $attachment["db"],
                                    $db,
                                    $attachment["dir"]
                                );
                                }
                            }

                            $result = @$db->query($query);
                     
                            if ($db->lastErrorMsg() == "not an error") {
                                $locked = false;
                                $syntax_error = false;
                            } elseif (strstr($db->lastErrorMsg(), "database is locked") !== false) {
                                // up to 100 milliseconds
                                $sleep_amount = 1000 * rand(1, 100);
                                usleep($sleep_amount);
                                $time_spent += $sleep_amount / 1000;
                            }
                        }
                    }
                
                    if ($syntax_error) {
                        throw new \Exception(
                        "(" . $query . ") " . $db->lastErrorMsg(),
                        $db->lastErrorCode()
                    );
                    }
                }
            } catch (\Exception $e) {
                $output = date("c", time()) . ": ";
                $output .= $e->getMessage() . "\n\n";

                if ($development_mode) {
                    View::addMessage(
                    $e->getMessage(),
                    "error"
                );
                }

                file_put_contents("sqlite.errors", $output, FILE_APPEND);

                System::exceptionCatchHook($e);
            }
        } else {
            throw new \Exception("Invalid SQLite object.");
        }

        return $result;
    }

    /**
     * Get the id of last inserted row.
     *
     * @param resource|object $db
     *
     * @return int
     */
    public static function lastInsertRowId(&$db): int
    {
        if (get_class($db) == "PDO") {
            return $db->lastInsertId();
        } elseif (get_class($db) == "SQLite3") {
            return $db->lastInsertRowID();
        } else {
            throw new \Exception("Invalid SQLite object.");
        }

        return 0;
    }

    /**
     * Starts a transaction which is faster for insertions or updates.
     *
     * @param resource $db Database handle.
     *
     * @return resource|bool Result handle or false on failure.
     */
    public static function beginTransaction(&$db)
    {
        return self::query("begin transaction", $db);
    }

    /**
     * Instantly starts a transaction in write mode, and should wait until other
     * clients stop writing.
     *
     * @param resource $db Database handle.
     *
     * @return resource|bool Result handle or false on failure.
     */
    public static function beginWriteTransaction(&$db)
    {
        return self::query("begin immediate transaction", $db);
    }

    /**
     * Ends a transaction.
     *
     * @param resource $db Database handle.
     *
     * @return resource|bool Result handle or false on failure.
     */
    public static function commitTransaction(&$db)
    {
        return self::query("commit transaction", $db);
    }

    /**
     * Same as normal sqlite_fetch_array but with the SQLITE_ASSOC passed.
     *
     * @param resource|object $result An sqlite resource result of a statement.
     *
     * @return array|bool Data results or false for no data.
     */
    public static function fetchArray(&$result)
    {
        if (get_class($result) == "PDOStatement") {
            return $result->fetch(\PDO::FETCH_ASSOC);
        } elseif (get_class($result) == "SQLite3Result") {
            return $result->fetchArray(SQLITE3_ASSOC);
        } else {
            throw new \Exception("Invalid SQLite Result object.");
        }

        return false;
    }

    /**
     * Checks if the given sqlite database exists in the data/sqlite directory
     *
     * @param string $name The name of the database file.
     * @param string $directory Optional path to database file.
     * @param bool $validity_check Determine if database is valid and if not remove it.
     *
     * @return bool True if exist and valid (if used validity_check) false if not.
     */
    public static function dbExists(
    string $name,
    string $directory = "",
    bool $validity_check=false
): bool {
        if (!$directory) {
            $directory = Site::dataDir() . "sqlite/";
        }

        $directory = rtrim($directory, "/") . "/";

        $exists = file_exists($directory . $name);

        if ($exists && $validity_check) {
            if (!Sql::dbValid($name, $directory)) {
                unlink($directory . $name);
                return false;
            }
        }

        return $exists;
    }

    /**
     * Checks if a database file is valid.
     *
     * @param string $name
     * @param string $directory
     *
     * @return bool True if valid, otherwise false.
     */
    public static function dbValid(string $name, string $directory=""): bool
    {
        $db = Sql::open($name, $directory);

        $query = "pragma schema_version";

        if (get_class($db) == "PDO") {
            try {
                $result = $db->prepare($query);
                $result->execute();
            } catch (\PDOException $exception) {
                return false;
            }
        } elseif (get_class($db) == "SQLite3") {
            ob_start();
            try {
                $result = $db->query($query);
                $error = ob_get_contents();

                if ($db->lastErrorMsg() != "not an error") {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
            ob_end_clean();
        } else {
            throw new \Exception("Invalid SQLite object.");
        }

        return true;
    }

    /**
     * Check if a table exists.
     *
     * @param string $table
     * @param string $database
     * @param string $directory
     *
     * @return bool
     */
    public static function tableExists(
    string $table,
    string $database,
    string $directory=""
): bool {
        if (!$directory) {
            $directory = Site::dataDir() . "sqlite/";
        }

        $directory = rtrim($directory, "/") . "/";

        $db = Sql::open($database, $directory);

        $result = Sql::query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='$table'",
        $db
    );

        $data = Sql::fetchArray($result);

        Sql::close($db);

        return isset($data["name"]);
    }

    /**
     * List all the databases available on the system.
     *
     * @param string $directory Optional path for database files.
     *
     * @return  array All the databases available on the system.
     */
    public static function listDB(string $directory = ""): array
    {
        if (!$directory) {
            $directory = Site::dataDir() . "sqlite/";
        }

        $directory = rtrim($directory, "/") . "/";

        $dh = opendir($directory);

        $databases = [];

        while (($file = readdir($dh)) !== false) {
            if (is_file($directory . $file) && !preg_match("/(.*)(\.sql)/", $file)) {
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
     */
    public static function countColumn(
    string $database,
    string $table,
    string $column,
    string $where = "",
    string $directory = "",
    string $select_additional = ""
): int {
        if (self::dbExists($database, $directory)) {
            $db = self::open($database, $directory);

            if ($select_additional != "") {
                $select_additional = ", " . ltrim(trim($select_additional), ",");
            }

            $result = self::query(
            "select count($column) as 'total_count' $select_additional "
            . "from $table $where",
            $db
        );

            $count = self::fetchArray($result);

            self::close($db);

            return $count["total_count"] ?? 0;
        } else {
            return 0;
        }
    }

    /**
     * Creates an sql file backup of all database tables.
     *
     * @param string $name The name of the database to backup.
     */
    public static function backup(string $name): void
    {
        if (self::dbExists($name)) {
            $backup_path = Site::dataDir() . "sqlite/" . $name . ".sql";

            $db = self::open($name);

            $result = self::query(
            "select * from sqlite_master where type = 'table' order by name asc",
            $db
        );

            $tables = [];

            while ($row = self::fetchArray($result)) {
                $tables[] = $row;
            }

            $backup_file = fopen($backup_path, "w");

            foreach ($tables as $values) {
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

                while ($row = self::fetchArray($result)) {
                    $column_name_string = "";
                    $column_name_array = [];

                    $column_value_string = "";
                    $column_value_array = [];

                    foreach ($row as $colum_name => $colum_value) {
                        $column_name_array[] = $colum_name;
                        $column_value_array[] = "'" . str_replace(
                        ["'", "\r", "\n"],
                        ["''", "\\r", "\\n"],
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
     */
    public static function restore(string $name, &$fp): void
    {
        unlink(Site::dataDir() . "sqlite/$name");

        $db = self::open($name);

        while (!feof($fp)) {
            $sql_statement = fgets($fp);

            //Ignore empty lines and comments
            if (
            $sql_statement != "" &&
            !preg_match("/^(\/\*)(.*)(\*\/)$/", $sql_statement)
        ) {
                $sql_statement = str_replace(
                ["\\r", "\\n"],
                ["\r", "\n"],
                $sql_statement
            );

                self::query($sql_statement, $db);
            }
        }
    }
}
