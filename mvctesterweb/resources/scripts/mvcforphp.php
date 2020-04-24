<?php
//GENERAL INFORMATION
/**
 * MVC for PHP 
 * This classes allow users to manage most common operations with databases, based on mysqli functions. It also includes support for MVC components.
 * @author Daniel F. Rivera C.
 * @author tutordesoftware@gmail.com
 * @version 2.2
 * @package mvcforphp
 */
//PHP error checking
error_reporting(E_ALL);
ini_set('display_errors', '1');
//Begin of session evironment
session_start();
//CLASS MVC
/**
 * Class for defining page MVC elements.
 * @property int $level Folder level for the file. By default is 1 for MVC.
 * @method void useModel(string $modelName, int level) Include model files to use on code based on $modelName.
 * @method void redir(string $dir, array $data) Redirect to the specified $dir. Optional $data could be sent.
 * @method static void serverVariables() Show on screen variables from server.
 */
class MVC
{
    /**
     * Folder level for the file. By default is 1 for MVC.
     * @var int
     */
    private $level = 1;
    /**
     * Return the value of a private field on class.
     */
    public function __get($name)
    {
        return $this->$name;
    }
    /**
     * Set the value of a private field on class.
     */
    public function __set($name, $value)
    {
        if ($name == "level" && !is_int($value)) throw new Exception("Level must be an integer.");
        $this->$name = $value;
    }
    /**
     * Include model files to use on code based on $modelName.
     * @param string $modelName Name of the model to be included.
     * @return void
     * @throws Exception When model doesn't exists on models folder.
     */
    public function useModel(string $modelName)
    {
        $path = $this->rootPath() . "models/" . $modelName . ".php";
        if (!file_exists($path)) throw new Exception("Model doesn't exists in models folder ($path).");
        require_once $path;
    }
    /**
     * Redirect to the specified $dir. Optional $data could be sent.
     * @param string $dir Direction to be redirected to.
     * @param array $data (Optional) Data to be sent through GET method. $data must be an associative array.
     * @return void
     */
    public function redir(string $dir, array $data = array())
    {
        $dcount = sizeof($data);
        if ($dcount > 0) {
            $keys = array_keys($data);
            $dir .= "?" . $keys[0] . "=" . $data[$keys[0]];
            for ($i = 1; $i < $dcount; $i++) $dir .= "&" . $keys[$i] . "=" . $data[$keys[$i]];
        }
        header("Location: $dir");
    }
    /**
     * Show on screen variables from server.
     * @return void
     */
    public static function serverVariables()
    {
        $keys = array_keys($_SERVER);
        foreach ($keys as $key) echo $key . " - " . $_SERVER[$key] . "<br/>";
    }
    /**
     * Constructs root path based on class defined $level.
     * @return string
     */
    protected function rootPath()
    {
        $rootPath = "";
        for ($i = 0; $i < $this->level; $i++) $rootPath .= "../";
        return $rootPath;
    }
}
//CLASS MODEL
/**
 * Class for building models based on associative arrays.
 * 
 * Each member of the associative array which builds the Model is stored internally to work as a property that can be accesed or changed just by using the property name.
 */
class Model
{
    /**
     * Stores the model data fields as an associative array.
     * @var array
     */
    private $data = array();
    /**
     * Creates a new Model.
     * @param array $data (Optional) Defines an associative array which contains the fields of the model.
     * @return Model
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    /**
     * Return the value of a private field on class.
     */
    public function __get($name)
    {
        return $this->data[$name];
    }
    /**
     * Set the value of a private field on class.
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    /**
     * Shows a model data as a JSON string.
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}
//CLASS SQLTABLE
/**
 * Defines basic structure for SQL Tables based on Database entities.
 * @property string $name Defines the name of the entity on relational DB.
 * @property string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair in order to be set. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
 * @property string $pk Defines the name of the field wich is the primary key on the DB entity.
 * @method string parseValueOf(string $fieldName, string $value) Parses the $value given based on specific field type for the given $fieldName.
 */
class SQLTable
{
    /**
     * Defines the name of the entity.
     */
    private $name;
    /**
     * Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair in order to be set. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
     */
    private $fields;
    /**
     * Defines the name of the field wich is the primary key on the DB entity.
     */
    private $pk;
    /**
     * Creates a new SQLTable.
     * @param string $name Defines the name of the entity on relational DB.
     * @param string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair in order to be set. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
     * @param string $pk Defines the name of the field wich is the primary key on the DB entity.
     * @return SQLTable
     */
    public function __construct(string $name, array $fields, string $pk)
    {
        $this->name = $name;
        $count = sizeof($fields);
        for ($i = 0; $i < $count; $i++) {
            $fieldPair = explode(":", $fields[$i]);
            if (!isset($fieldPair[1]) || $fieldPair[1] == "") $fields[$i] = $fieldPair[0] . ":text";
        }
        $this->fields = $fields;
        $this->pk = $pk;
    }
    /**
     * Return the value of a private field on class.
     */
    public function __get($name)
    {
        if ($name == "fields") {
            $fieldNames = array();
            foreach ($this->fields as $field) {
                $fieldInfo = explode(":", $field);
                $fieldNames[] = $fieldInfo[0];
            }
            return $fieldNames;
        } else return $this->$name;
    }
    /**
     * Parses the $value given based on specific field type for the given field $fieldName
     * @param string $fieldName The name of the field on the table to use as reference.
     * @param string $value The string value to be parsed based on the referenced field type.
     * @return string
     * @throws Exception If $fieldName doesn't match with any field on table.
     */
    public function parseValueOf(string $fieldName, string $value)
    {
        $parsedValue = "";
        foreach ($this->fields as $field) {
            $fieldInfo = explode(":", $field);
            if ($fieldInfo[0] == $fieldName) {
                $parsedValue = $this->fieldValue($fieldInfo[1], $value);
                break;
            }
        }
        if ($parsedValue == "") throw new Exception("Parse error: Fieldname couldn't be found on table.");
        return $parsedValue;
    }
    /**
     * Get parsed value depending on field type.
     * @param string $type Type of field to be parsed. It could be key, text, number, date or datetime.
     * @param string $value Value to be parsed according to field type defined.
     * @return string
     * @throws Exception If no valid field type given.
     */
    private function fieldValue(string $type, string $value)
    {
        if ($type == "key") return $this->keyValue($value);
        elseif ($type == "text") return $this->textValue($value);
        elseif ($type == "number") return $this->numberValue($value);
        elseif ($type == "date") return $this->dateValue($value);
        elseif ($type == "datetime") return $this->datetimeValue($value);
        else throw new Exception("No valid field type received.");
    }
    /**
     * Get value from a field that is primary or foreign key
     * @param string $value Value to be parsed.
     * @return string
     */
    private function keyValue(string $value)
    {
        return ($value == "") ? "NULL" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type text
     * @param string $value Value to be parsed.
     * @return string
     */
    private function textValue(string $value)
    {
        return "'" . htmlspecialchars(trim($value), ENT_QUOTES) . "'";
    }
    /**
     * Get value from a field that is type number
     * @param string $value Value to be parsed.
     * @return string
     */
    private function numberValue(string $value)
    {
        return ($value == "") ? "'0'" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type date
     * @param string $value Value to be parsed.
     * @return string
     */
    private function dateValue(string $value)
    {
        date_default_timezone_set('America/Bogota');
        return ($value == "") ? "'" . date("Y-m-d") . "'" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type datetime.
     * @param string $value Value to be parsed.
     * @return string
     */
    private function datetimeValue(string $value)
    {
        date_default_timezone_set('America/Bogota');
        return ($value == "") ? "'" . date("Y-m-d H:i:s") . "'" : "'" . $value . "'";
    }
}
//CLASS SQLClauses
/**
 * Class for defining special SQL clauses.
 * @property string[] $fields Defines a field list.
 * @property string[] $wherePairs Defines a WHERE clause content. Each string element must be written as a "[name]:[value]" pair.
 * @property string[] $orderPairs Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
 */
class SQLClauses
{
    /**
     * Defines a field list.
     * @var string[]
     */
    private $fields = array();
    /**
     * Defines a WHERE clause content. Each string element must be written as a "[name]:[value]" pair.
     * @var string[]
     */
    private $wherePairs = array();
    /**
     * Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
     * @var string[]
     */
    private $orderPairs = array();
    /**
     * Creates a new SQLClauses.
     * @param string[] $fields Defines a field list.
     * @param string[] $wherePairs Defines a WHERE clause content. Each string element must be written as a "[name]:[value]" pair.
     * @param string[] $orderPairs Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
     * @return SQLClauses
     */
    public function __construct(array $fields = array(), array $wherePairs = array(), array $orderPairs = array())
    {
        $this->fields = $fields;
        $this->wherePairs = $wherePairs;
        $this->orderPairs = $orderPairs;
    }
    /**
     * Return the value of a private field on class.
     */
    public function __get($name)
    {
        $clause = "";
        switch ($name) {
            case "fields":
                $count = sizeof($this->fields);
                if ($count > 0) {
                    $clause = $this->fields[0];
                    for ($i = 1; $i < $count; $i++) $clause = $clause . "," . $this->fields[$i];
                }
                break;
            case "wherePairs":
                $count = sizeof($this->wherePairs);
                if ($count > 0) {
                    $wherePair = explode(":", $this->wherePairs[0]);
                    $clause = $wherePair[0] . "='" . $wherePair[1] . "'";
                    for ($i =  1; $i < $count; $i++) {
                        $wherePair = explode(":", $this->wherePairs[$i]);
                        $clause = $clause . " and " . $wherePair[0] . "='" . $wherePair[1] . "'";
                    }
                }
                break;
            case "orderPairs":
                $count = sizeof($this->orderPairs);
                if ($count > 0) {
                    $orderPair = explode(":", $this->orderPairs[0]);
                    $clause = $orderPair[0] . " " . $orderPair[1];
                    for ($i = 1; $i < $count; $i++) {
                        $orderPair = explode(":", $this->orderPairs[$i]);
                        $clause = $clause . ", " . $orderPair[0] . " " . $orderPair[1];
                    }
                }
                break;
        }
        return $clause;
    }
    /**
     * Set the value of a private field on class.
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case "wherePairs":
                $count = sizeof($value);
                for ($i =  0; $i < $count; $i++) {
                    $wherePair = explode(":", $value[$i]);
                    if (!isset($wherePair[1])) throw new Exception("No value especified for where clause field.");
                }
                break;
            case "orderPairs":
                $count = sizeof($value);
                for ($i = 0; $i < $count; $i++) {
                    $orderPair = explode(":", $value[$i]);
                    if (!isset($orderPair[1]) || $orderPair[1] == "") $value[$i] = $orderPair[0] . ":asc";
                }
                break;
        }
        $this->$name = $value;
    }
}
//CLASS DBMODEL
/**
 * Class for database access and modification functions.
 * @property int $lastIndex Stores the last AUTO_INCREMENT value generated on an add operation.
 * @method mysqli_result runQuery(string $sqlQuery) Executes an $sqlQuery and return mysqli_result.
 * @method void runTransaction(string $sqlTransaction) Executes an $sqlTransaction on database.
 * @method Model[] getAll(SQLClauses $queryConf) Get all data on a table depending on optional SQLClauses defined.
 * @method null|Model get(string $id) Get a row from a table depending on $id.
 * @method void add(string[] $newData, SQLClauses $transConf) Add the $newData to Database SQLTable depending on optional SQLClauses defined.
 * @method void update(string $id, string[] $newData, SQLClauses $transConf) Update $newData of an item on a table depending on $id and optional SQLClases defined.
 * @method void deleteAll(SQLClauses $transConf) Delete all items from a table depending on SQLClauses.
 * @method static void delete(string $id) Delete an item from a table depending on $id.
 */
class DBModel extends MVC
{
    /**
     * Stores the last AUTO_INCREMENT value generated on an add operation.
     * @var int
     */
    protected $lastIndex;
    /**
     * SQLTable object to set table definition attributes.
     * @var SQLTable
     */
    protected $table;
    /**
     * Creates a new DBModel.
     * @param SQLTable $table Defines the sql table wich is the base for DBModel.
     * @param int $level (Optional) Folder level for the file. By default is 1 for MVC.
     * @return DBModel
     */
    public function __construct(SQLTable $table, int $level = 1)
    {
        $this->table = $table;
        $this->level = $level;
    }
    /**
     * Return the value of a private field on class.
     */
    public function __get($name)
    {
        return $this->$name;
    }
    /**
     * Excecute a SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return mysqli_result if query succeed
     * @throws Exception if query failed
     */
    public function runQuery(string $sqlQuery)
    {
        try {
            $connection = $this->connect();
            $result = $connection->query($sqlQuery);
            if (!$result) throw new Exception($connection->error);
            return $result;
        } catch (Exception $ex) {
            throw new Exception("Query failed: " . $ex->getMessage());
        } finally {
            $connection->close();
            unset($connection, $result);
        }
    }
    /**
     * Execute a SQL Transaction
     * @param string $sqlTransaction Text with SQL Transaction to execute
     * @throws Exception if transaction failed
     */
    public function runTransaction(string $sqlTransaction)
    {
        try {
            $connection = $this->connect();
            if ($connection->query($sqlTransaction) !== TRUE) throw new Exception($connection->error);
            $this->lastIndex = mysqli_insert_id($connection);
        } catch (Exception $ex) {
            throw new Exception("Transaction failed: " . $ex->getMessage());
        } finally {
            $connection->close();
            unset($connection);
        }
    }
    /**
     * Get all rows from a table model.
     * @param SQLClauses $queryConf (Optional) Options for query.
     * @return Model[] List of elements obtained by the query.
     * @throws Exception If no table is defined when using this function.
     * @throws Exception If something fail getting data.
     */
    public function getAll(SQLClauses $queryConf = null)
    {
        try {
            if ($queryConf == null) $sql = "select * from " . $this->table->name . " order by " . $this->table->pk . ";";
            else {
                $sql = "select ";
                if ($queryConf->fields != "") $sql .= $queryConf->fields;
                else $sql .= "*";
                $sql .= " from " . $this->table->name;
                if ($queryConf->wherePairs != "") $sql .= " where " . $queryConf->wherePairs;
                if ($queryConf->orderPairs != "") $sql .= " order by " . $queryConf->orderPairs;
                $sql .= ";";
            }
            $list = array();
            $resultsDB = $this->runQuery($sql);
            if ($resultsDB->num_rows > 0) while ($result = $resultsDB->fetch_assoc()) $list[] = new Model($result);
            return $list;
        } catch (Exception $ex) {
            throw new Exception("Something failed getting data: " . $ex->getMessage());
        }
    }
    /**
     * Get a row from a table depending on id 
     * @param string $id Reference value to search on the table.
     * @return Model If data was found on the table.
     * @return null If no data was found on the table.
     * @throws Exception If something fail getting data.
     */
    public function get(string $id)
    {
        try {
            $object = null;
            if ($id != "NULL" && $id != "") {
                $resultDB = $this->runQuery("select * from " . $this->table->name . " where " . $this->table->pk . "='$id';");
                if ($resultDB->num_rows > 0) {
                    $result = $resultDB->fetch_assoc();
                    $object = new Model($result);
                }
            }
            return $object;
        } catch (Exception $ex) {
            throw new Exception("Something failed getting data: " . $ex->getMessage());
        }
    }
    /**
     * Add an item to the table
     * @param string[] $newData Data to be send on INSERT sentence.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->fields must be defined in order to define which fields are going to be send to the INSERT sentence.
     * @return void
     * @throws Exception If something fail adding data.
     */
    public function add(array $newData, SQLClauses $transConf = null)
    {
        try {
            $fcount = sizeof($this->table->fields);
            $dcount = sizeof($newData);
            if ($dcount > $fcount) throw new Exception("Data list can't have more elements than field list.");
            else if ($dcount < $fcount && $transConf == null) throw new Exception("If data list has less elements than field list, a fields list must be defined.");
            else if ($dcount < $fcount) {
                $tcfcount = sizeof(explode(",", $transConf->fields));
                if ($dcount != $tcfcount) throw new Exception("No valid fields list for data list given.");
            } else {
                $transConf = new SQLClauses($this->table->fields);
            }
            $fieldsToAdd = explode(",", $transConf->fields);
            $values = $this->table->parseValueOf($fieldsToAdd[0], $newData[0]);
            for ($i = 1; $i < $dcount; $i++) $values .= "," . $this->table->parseValueOf($fieldsToAdd[$i], $newData[$i]);
            $this->runTransaction("insert into " . $this->table->name . " (" . $transConf->fields . ") values (" . $values . ");");
        } catch (Exception $ex) {
            throw new Exception("Something failed adding data: " . $ex->getMessage());
        }
    }
    /**
     * Update an item from a table depending on id
     * @param string $id PK from the element to update.
     * @param string[] $newData Array which contains the new data to save on the row.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->fields must be defined in order to define which fields are going to be send to the UPDATE sentence.
     * @return void If operation succeed.
     * @throws Exception If something updating data fail.
     */
    public function update(string $id, array $newData, SQLClauses $transConf = null)
    {
        try {
            $fcount = sizeof($this->table->fields);
            $dcount = sizeof($newData);
            if ($dcount > $fcount) throw new Exception("Data list can't have more elements than field list.");
            else if ($dcount < $fcount && $transConf == null) throw new Exception("If data list has less elements than field list, a fields list must be defined.");
            else if ($dcount < $fcount) {
                $tcfcount = sizeof(explode(",", $transConf->fields));
                if ($dcount != $tcfcount) throw new Exception("No valid fields list for data list given.");
            } else {
                $transConf = new SQLClauses($this->table->fields);
            }
            $fieldsToChange = explode(",", $transConf->fields);
            $values = $fieldsToChange[0] . "=" . $this->table->parseValueOf($fieldsToChange[0], $newData[0]);
            for ($i = 1; $i < $dcount; $i++) $values .= "," . $fieldsToChange[$i] . "=" . $this->table->parseValueOf($fieldsToChange[$i], $newData[$i]);
            $this->runTransaction("update " . $this->table->name . " set " . $values . " where " . $this->table->pk . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed updating data: " . $ex->getMessage());
        }
    }
    /**
     * Delete all items from a table depending on SQLClauses.
     * @param SQLClauses $transConf (Optional) Options for delete operation.
     * @return void If something deleting rows fail.
     * @throws Exception If something fail deleting data.
     */
    public function deleteAll(SQLClauses $transConf = null)
    {
        try {
            $sql = "delete from " . $this->table->name;
            if ($transConf != null && $transConf->wherePairs != "") $sql .= " where " . $transConf->wherePairs;
            $sql .= ";";
            $this->runTransaction($sql);
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
    /**
     * Delete an item from a table depending on id
     * @param string $id PK of element to be deleted.
     * @return void If operation succeed.
     * @throws Exception If something deleting rows fail.
     */
    public function delete(string $id)
    {
        try {
            $this->runTransaction("delete from " . $this->table->name . " where " . $this->table->pk . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
    /**
     * Connect to database
     * @return mysqli if connection succeed.
     * @throws Exception if something fails while connecting to database.
     * @throws Exception if no json file found with database configuration.
     * @throws Exception if no dbname defined on dbconf.json file.
     */
    private function connect()
    {
        try {
            $path = $this->rootPath() . "resources/scripts/dbconf.json";
            if (!file_exists($path)) throw new Exception("No dbconf.json file found.");
            $dbconf = json_decode(file_get_contents($path), true);
            $hostname = (isset($dbconf["hostname"]) && $dbconf["hostname"] != "") ? $dbconf["hostname"] : "localhost";
            $hostuser = (isset($dbconf["hostuser"]) && $dbconf["hostuser"] != "") ? $dbconf["hostuser"] : "root";
            $hostpwd = (isset($dbconf["hostpwd"])) ? $dbconf["hostpwd"] : "";
            $hostport = (isset($dbconf["hostport"]) && $dbconf["hostport"] != "") ? $dbconf["hostport"] : "3306";
            if (!isset($dbconf["dbname"]) || $dbconf["dbname"] == "") throw new Exception("Database name must be defined on dbconf.json file.");
            $dbname = $dbconf["dbname"];
            $connection = new mysqli($hostname, $hostuser, $hostpwd, $dbname, $hostport);
            if ($connection->connect_errno) {
                $errno = $connection->connect_errno;
                $error = $connection->connect_error;
                throw new Exception("Failed to connect to MySQL($errno): $error");
            }
            $connection->query("SET NAMES UTF8");
            return $connection;
        } catch (Exception $ex) {
            throw new Exception("Connection failed: " . $ex->getMessage());
        } finally {
            unset($connection);
        }
    }
}
//CLASS View
/**
 * Class for defining page Views.
 * @method void useComponent(string $componentName, array $data) Includes a component file while rendering, $data is an optional associative array to send data to component.
 * @method void render(function $content, array $data) Render and show the page. $content is an anonymous functios containing additional content for the page. $data is an optional associative array to send data to the page template.
 */
class View extends MVC
{
    /**
     * Creates a new View.
     * @param int $level (Optional) Folder level for the file. By default is 1 for MVC.
     * @return View
     */
    public function __construct(int $level = 1)
    {
        $this->level = $level;
    }
    /**
     * Includes a component file while rendering, $data is an optional associative array to send data to component.
     * @param string $componentName Name of the component to be included.
     * @param array $data (Optional) Additional data to be send to the component.
     * @return void
     * @throws Exception When component doesn't exists on components folder.
     */
    public function useComponent(string $componentName, array $data = array())
    {
        $path = $this->rootPath() . "views/shared/components/" . $componentName . ".php";
        if (!file_exists($path)) throw new Exception("Component doesn't exists in components folder.");
        require_once $path;
    }
    /**
     * Render and show the page. $content is an anonymous functios containing additional content for the page. $data is an optional associative array to send data to the page template.
     * @param function $content Delegate function with additional content for the page.
     * @param array $data (Optional) Additional data to be send to the page.
     * @return void
     */
    public function render($content = null, array $data = array())
    {
        if ($content == null) $content = function () {
            echo "No content defined for view";
        };
        require_once $this->rootPath() . "views/shared/template.php";
    }
}
//CLASS Controller
/**
 * Class for defining site Controllers.
 * @method void sendResponse(string $message) Send a response message
 * @method void sendError(string $message) Send an error message
 * @method void addAction(string $name, function $action) Create a new action for the controller. $name is the name of the action and $action is an anonymous functions with the action content.
 * @method void processAction(string $actionName) Process action from the action array.
 * @method array checkPOSTData(string[] $dataIndexes) Verify if $dataIndexes exists as indexes on superglobal $_POST and returns variables on associative array.
 */
class Controller extends MVC
{
    /**
     * Associative array containig delegate functions for actions.
     * @var array
     */
    private $actions = array();
    /**
     * Creates a new Controller.
     * @param int $level (Optional) Folder level for the file. By default is 1 for MVC.
     * @return Controller
     */
    public function __construct(int $level = 1)
    {
        $this->level = $level;
    }
    /**
     * Send a response message
     * @param string $message Message to be sent.
     * @return void
     */
    public function sendResponse(string $message)
    {
        echo "OK: $message";
    }
    /**
     * Send an error message
     * @param string $message Message to be sent.
     * @return void
     */
    public function sendError(string $message)
    {
        echo "Error: $message";
    }
    /**
     * Create a new action for the controller. $name is the name of the action and $action is an anonymous functions with the action content.
     * @param string $name Name to identify the action.
     * @param function $action Delegate function for the action.
     * @return void
     */
    public function addAction(string $name, $action)
    {
        $this->actions[$name] = $action;
    }
    /**
     * Process action from the action array.
     * @param string $actionName Action to be processed.
     * @return void
     * @throws Exception If no valid action sent to be processed.
     */
    public function processAction(string $actionName)
    {
        try {
            $actionsCount = sizeof($this->actions);
            if ($actionsCount == 0) header("Location: " . $this->rootPath() . "index.php");
            if (!array_key_exists($actionName, $this->actions)) throw new Exception("No action or no valid action sent");
            $this->actions[$actionName]();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }
    /**
     * Verify if $dataIndexes exists as indexes on superglobal $_POST and returns variables on associative array.
     * @param string[] $dataIndexes String array containing names of indexes to be check on POST.
     * @return array Associative array containging data from superglobal POST.
     * @throws Exception When index not found on POST.
     */
    public function checkPOSTData(array $dataIndexes)
    {
        $data = array();
        foreach ($dataIndexes as $dataIndex) {
            if (!isset($_POST[$dataIndex])) throw new Exception("No valid data received on POST.");
            $data[$dataIndex] = $_POST[$dataIndex];
        }
        return $data;
    }
}
