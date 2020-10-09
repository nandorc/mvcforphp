<?php
#region GENERAL INFORMATION
/**
 * MVC for PHP 
 * This classes allow users to manage most common operations with databases, based on mysqli functions. It also includes support for MVC components.
 * @author Daniel F. Rivera C.
 * @author tutordesoftware@gmail.com
 * @version 3.0
 * @package mvcforphp
 */
#endregion
#region PHP error checking
error_reporting(E_ALL);
ini_set('display_errors', '1');
#endregion
#region Begin of session evironment
session_start();
#endregion
#region MVC CLASS
/**
 * Class for defining page MVC components.
 */
class MVC
{
    #region METHODS
    #region public redir method
    /**
     * Redirect to the specified $dir. Optional $data could be sent.
     * @param string $dir Direction to be redirected to. If no http:// or https:// directive sent, it will redirect to a local view.
     * @param array $data (Optional) Data to be sent through GET method. $data must be an associative array.
     * @return void
     * @throws Exception If file:/// directive is send as $dir
     */
    public function redir(string $dir, array $data = array())
    {
        if (substr($dir, 0, 8) == "file:///") throw new Exception("Can't redir to a file.");
        if (get_class($this) == "Controller" && substr($dir, 0, 7) != "http://" && substr($dir, 0, 8) != "https://") $dir = "../$dir";
        $dcount = sizeof($data);
        if ($dcount > 0) {
            $keys = array_keys($data);
            $dir .= "?" . $keys[0] . "=" . $data[$keys[0]];
            for ($i = 1; $i < $dcount; $i++) $dir .= "&" . $keys[$i] . "=" . $data[$keys[$i]];
        }
        header("Location: $dir");
    }
    #endregion
    #region public static serverVariables method
    /**
     * Show on screen variables from server.
     * @return void
     */
    public static function serverVariables()
    {
        $keys = array_keys($_SERVER);
        foreach ($keys as $key) echo $key . " - " . $_SERVER[$key] . "<br/>";
    }
    #endregion
    #endregion
}
#endregion
#region MODEL CLASS
/**
 * Class for building models based on associative arrays.
 * Each member of the associative array which builds the Model is stored internally to work as a property that can be accesed or changed just by using the property name.
 */
class Model
{
    #region ATTRIBUTES
    /**
     * Stores the model data fields as an associative array.
     * @var array
     */
    private $data = array();
    #region public get
    public function __get($name)
    {
        return $this->data[$name];
    }
    #endregion
    #region public set
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    #endregion
    #endregion
    #region CONSTRUCTOR
    /**
     * @param array $data (Optional) Defines an associative array which contains the fields of the model.
     * @return Model
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    #endregion
    #region METHODS
    /**
     * Shows a model data as a JSON string.
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
    #endregion
}
#endregion
#region SQLTABLE CLASS
/**
 * Defines basic structure for SQL Tables based on Database entities.
 * @property string $name Name of the entity on relational DB.
 * @property string[] $fields String array which contains the name for each field in the entity.
 * @property string $pk Name of the field wich is the prior primary key on the DB entity.
 */
class SQLTable
{
    #region ATTRIBUTES
    private $name;
    private $fields;
    private $pk;
    #region public get
    public function __get($name)
    {
        if ($name != "name" && $name != "fields" && $name != "pk") throw new Exception("Trying to access to an unknown property");
        if ($name == "fields") {
            $fieldNames = array();
            foreach ($this->fields as $field) {
                $fieldInfo = explode(":", $field);
                $fieldNames[] = $fieldInfo[0];
            }
            return $fieldNames;
        } else return $this->$name;
    }
    #endregion
    #endregion
    #region CONSTRUCTOR
    /**
     * @param string $name Defines the name of the entity on relational DB.
     * @param string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair in order to be set. Type could be key, text, number, date, time or datetime. If type is not defined it would be text as default.
     * @param string $pk Defines the name of the field wich is the primary key on the DB entity.
     * @return SQLTable
     * @throws Exception If table $name is empty
     * @throws Exception If $fields array has no elements.
     * @throws Exception If $pk is an empty value.
     */
    public function __construct(string $name, array $fields, string $pk)
    {
        if ($name == "") throw new Exception("Name can't be empty.");
        if (sizeof($fields) == 0) throw new Exception("A table must have at least one field");
        if ($pk == "") throw new Exception("PK can't be empty");
        $this->name = $name;
        $count = sizeof($fields);
        for ($i = 0; $i < $count; $i++) {
            $fieldPair = explode(":", $fields[$i]);
            if (!isset($fieldPair[1]) || $fieldPair[1] == "") $fields[$i] = $fieldPair[0] . ":text";
        }
        $this->fields = $fields;
        $this->pk = $pk;
    }
    #endregion
    #region METHODS
    #region public parseValueOf method
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
    #endregion
    #region private fieldValue method
    /**
     * Get parsed value depending on field type.
     * @param string $type Type of field to be parsed. It could be key, text, number, date, time or datetime.
     * @param string $value Value to be parsed according to field type defined.
     * @return string
     * @throws Exception If no valid field type given.
     */
    private function fieldValue(string $type, string $value)
    {
        if ($type == "key") return ($value == "") ? "NULL" : "'" . $value . "'";
        elseif ($type == "text") return "'" . htmlspecialchars(trim($value), ENT_QUOTES) . "'";
        elseif ($type == "number") return ($value == "") ? "'0'" : "'" . $value . "'";
        elseif ($type == "date") {
            date_default_timezone_set('America/Bogota');
            return ($value == "") ? "'" . date("Y-m-d") . "'" : "'" . $value . "'";
        } elseif ($type == "time") {
            date_default_timezone_set('America/Bogota');
            return ($value == "") ? "'" . date("H:i:s") . "'" : "'" . $value . "'";
        } elseif ($type == "datetime") {
            date_default_timezone_set('America/Bogota');
            return ($value == "") ? "'" . date("Y-m-d H:i:s") . "'" : "'" . $value . "'";
        } else throw new Exception("No valid field type received.");
    }
    #endregion
    #endregion
}
#endregion
#region SQLClauses CLASS
/**
 * Class for defining special SQL clauses.
 * @property string $fields Field list formatted as a comma separated list with field names.
 * @property string $wherePairs String formatted as SQL WHERE clause content.
 * @property string $orderPairs String formatted as SQL ORDER BY clause content.
 */
class SQLClauses
{
    #region ATTRIBUTES
    private $fields = array();
    private $wherePairs = array();
    private $orderPairs = array();
    #region public get
    public function __get($name)
    {
        if ($name != "fields" && $name != "wherePairs" && $name != "orderPairs") throw new Exception("Trying to access to an unknown property");
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
                    $clause = $wherePair[0] . $wherePair[2] . "'" . $wherePair[1] . "'";
                    for ($i =  1; $i < $count; $i++) {
                        $wherePair = explode(":", $this->wherePairs[$i]);
                        $clause = $clause . " and " . $wherePair[0] . $wherePair[2] . "'" . $wherePair[1] . "'";
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
    #endregion
    #region public set
    public function __set($name, $value)
    {
        if ($name != "fields" && $name != "wherePairs" && $name != "orderPairs") throw new Exception("Trying to access to an unknown property");
        switch ($name) {
            case "wherePairs":
                $count = sizeof($value);
                for ($i =  0; $i < $count; $i++) {
                    $wherePair = explode(":", $value[$i]);
                    if (!isset($wherePair[1])) throw new Exception("No value especified for where clause field.");
                    else if (!isset($wherePair[2]) || $wherePair[2] == "") $value[$i] = $wherePair[0] . ":" . $wherePair[1] . ":=";
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
    #endregion
    #endregion
    #region CONSTRUCTOR    
    /**
     * Creates a new SQLClauses.
     * @param string[] $fields Defines a field list. If not defined an empty array() must be put.
     * @param string[] $wherePairs Defines a WHERE clause content. Each string element must be written in "[name]:[value]:[type]" format. If type is not definen, it would be = by default. If not defined an empty array() must be put.
     * @param string[] $orderPairs Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default. If not defined an empty array() must be put.
     * @return SQLClauses
     * @throws Exception If no value is specified in a $wherePairs array value.
     */
    public function __construct(array $fields = array(), array $wherePairs = array(), array $orderPairs = array())
    {
        $this->fields = $fields;
        $count = sizeof($wherePairs);
        for ($i =  0; $i < $count; $i++) {
            $wherePair = explode(":", $wherePairs[$i]);
            if (!isset($wherePair[1])) throw new Exception("No value especified for where clause field.");
            else if (!isset($wherePair[2]) || $wherePair[2] == "") $wherePairs[$i] = $wherePair[0] . ":" . $wherePair[1] . ":=";
        }
        $this->wherePairs = $wherePairs;
        $count = sizeof($orderPairs);
        for ($i = 0; $i < $count; $i++) {
            $orderPair = explode(":", $orderPairs[$i]);
            if (!isset($orderPair[1]) || $orderPair[1] == "") $orderPairs[$i] = $orderPair[0] . ":asc";
        }
        $this->orderPairs = $orderPairs;
    }
    #endregion
}
#endregion
#region DBMODEL CLASS
/**
 * Class for database access and modification functions.
 * @property int $lastIndex Stores the last AUTO_INCREMENT value generated on an add operation.
 */
class DBModel extends MVC
{
    #region ATTRIBUTES
    protected $lastIndex;
    /**
     * SQLTable object to set table definition attributes.
     * @var SQLTable
     */
    protected $table;
    #region public get
    public function __get($name)
    {
        if ($name != "lastIndex") throw new Exception("Trying to access to an unknown property");
        return $this->$name;
    }
    #endregion
    #endregion
    #region CONSTRUCTOR
    /**
     * @param SQLTable $table Defines the sql table wich is the base for DBModel.
     * @return DBModel
     */
    public function __construct(SQLTable $table)
    {
        $this->lastIndex = 0;
        $this->table = $table;
    }
    #endregion
    #region METHODS
    #region BASIC METHODS
    #region private connect method
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
            $path = "../resources/scripts/mvcforphp/dbconf.json";
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
    #endregion
    #region private runQuery method
    /**
     * Excecute a SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return mysqli_result if query succeed
     * @throws Exception if query failed
     */
    private function runQuery(string $sqlQuery)
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
    #endregion
    #region private runTransaction method
    /**
     * Execute a SQL Transaction
     * @param string $sqlTransaction Text with SQL Transaction to execute
     * @throws Exception if transaction failed
     */
    private function runTransaction(string $sqlTransaction)
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
    #endregion
    #region public executeCustomQuery method
    /**
     * Excecute a Custom SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return Model[] List of elements obtained by the query
     * @throws Exception If something fail getting data.
     */
    public function executeCustomQuery(string $sqlQuery)
    {
        try {
            $list = array();
            $resultsDB = $this->runQuery($sqlQuery);
            if ($resultsDB->num_rows > 0) while ($result = $resultsDB->fetch_assoc()) $list[] = new Model($result);
            return $list;
        } catch (Exception $ex) {
            throw new Exception("Something failed getting data: " . $ex->getMessage());
        }
    }
    #endregion
    #region public executeCustomTransaction method
    /**
     * Execute a SQL Transaction
     * @param string $sqlTransaction Text with SQL Transaction to execute
     * @return void If operation succeed.
     * @throws Exception if transaction failed
     */
    public function executeCustomTransaction(string $sqlTransaction)
    {
        try {
            $this->runTransaction($sqlTransaction);
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }

    #endregion
    #endregion
    #region CRUD METHODS
    #region SELECT METHODS
    #region public getAll method
    /**
     * Get all rows from a table model.
     * @param SQLClauses $queryConf (Optional) Options for query.
     * @return Model[] List of elements obtained by the query.
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
    #endregion
    #region public get method
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
    #endregion
    #endregion
    #region INSERT METHODS
    #region public add method
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
    #endregion
    #endregion
    #region UPDATE METHODS
    #region public update method
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
    #endregion
    #endregion
    #region DELETE METHODS
    #region public deleteAll method
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
    #endregion
    #region public delete method
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
    #endregion
    #endregion
    #endregion
    #endregion
}
#endregion
#region VIEW CLASS
/**
 * Class for defining page Views.
 */
class View extends MVC
{
    #region METHODS
    #region ELEMENT METHODS
    #region public static insertComponent method
    /**
     * Inserts a component from a file while rendering, $data is an optional associative array to send data to component.
     * @param string $componentName Name of the component to be included.
     * @param array $data (Optional) Additional data to be send to the component.
     * @return void
     * @throws Exception When component doesn't exists on components folder.
     */
    public static function insertComponent(string $componentName, array $data = array())
    {
        $path = "../views/shared/components/" . $componentName . ".php";
        if (!file_exists($path)) throw new Exception("Component doesn't exists in components folder ($path).");
        require $path;
    }
    #endregion
    #region public render method
    /**
     * Render and show the page. $content is an anonymous functios containing additional content for the page. $data is an optional associative array to send data to the page template.
     * @param function $content Delegate function with additional content for the page.
     * @param array $data (Optional) Additional data to be send to the page.
     * @return void
     */
    public static function render($content = null, array $data = array())
    {
        if ($content == null) $content = function () {
            echo "No content defined for view";
        };
        require_once "../views/shared/template.php";
    }
    #endregion
    #endregion
    #region MISC METHODS
    #region public static validateMessage method
    /**
     * Validate and modify vars assigned to manage info and error messages from de page.
     * @param string $errormsg Assigned variable to manage error messages.
     * @param string $infomsg Assigned variable to manage information messages.
     * @return void
     */
    public static function validateMessages(string &$errormsg, string &$infomsg)
    {
        if (isset($_GET["errormsg"]) || isset($_GET["infomsg"])) {
            $uri = $_SERVER["REQUEST_URI"];
            $query = self::extractQuery($uri);
            $view = self::getViewName($uri);
            if (isset($_GET["errormsg"])) self::moveGetToSession("errormsg", $query);
            if (isset($_GET["infomsg"])) self::moveGetToSession("infomsg", $query);
            $dir = ($query == "") ? $view : $view . "?" . $query;
            header("Location: $dir");
        } else {
            if (isset($_SESSION["errormsg"])) self::moveSessionToVar("errormsg", $errormsg);
            if (isset($_SESSION["infomsg"])) self::moveSessionToVar("infomsg", $infomsg);
        }
    }
    #endregion
    #region private static extractQuery method
    /**
     * Modify $uri removing query string and returning it as new variable.
     * @param string $uri Assigned variable for requested url
     * @return string Query string from requested url
     */
    private static function extractQuery(string &$uri)
    {
        $uriquery = explode("?", $uri);
        $uri = $uriquery[0];
        return (count($uriquery) == 2) ? $uriquery[1] : "";
    }
    #endregion
    #region private static getViewName method
    /**
     * Formats $uri to return the view name
     * @param string $uri URL to be check
     * @return string View name
     */
    private static function getViewName(string $uri)
    {
        $viewdir = explode("/", $uri);
        return $viewdir[count($viewdir) - 1];
    }
    #endregion
    #region private static moveGetToSession method
    /**
     * Takes a variable in $_GET and move it to $_SESSION and removes it from assigned $query
     * @param string $name Variable name to be moved
     * @param string $query Assigned query string to be altered
     * @return void
     */
    private static function moveGetToSession(string $name, string &$query)
    {
        $_SESSION[$name] = $_GET[$name];
        $pos = strpos($query, $name);
        $query = ($pos == 0) ? "" : substr($query, 0, $pos - 1);
    }
    #endregion
    #region private static moveSessionToVar method
    /**
     * Takes a variable in $_SESSION and move it to an assigned $var and unset $_SESSION variable
     * @param string $name Variable name to be moved
     * @param mixed $var Assigned variable to receive new value.
     * @return void
     */
    private static function moveSessionToVar(string $name, &$var)
    {
        $var = $_SESSION[$name];
        unset($_SESSION[$name]);
    }
    #endregion
    #endregion
    #endregion
}
#endregion
#region CONTROLLER CLASS
/**
 * Class for defining site Controllers.
 */
class Controller extends MVC
{
    #region ATTRIBUTES
    /**
     * Associative array containig delegate functions for actions.
     * @var array
     */
    private $actions = array();
    #endregion
    #region METHODS
    #region SEND METHODS
    #region public static sendResponse method
    /**
     * Send a response message
     * @param string $message Message to be sent.
     * @return void
     */
    public static function sendResponse(string $message)
    {
        echo "OK: $message";
    }
    #endregion
    #region public static sendError method
    /**
     * Send an error message
     * @param string $message Message to be sent.
     * @return void
     */
    public static function sendError(string $message)
    {
        echo "Error: $message";
    }
    #endregion
    #endregion
    #region ACTION METHODS
    #region public addAction method
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
    #endregion
    #region public processAction method
    /**
     * Process action from the action array.
     * @param string $actionName Action to be processed.
     * @return void
     * @throws Exception If no valid action sent to be processed.
     */
    public function processAction(string $actionName)
    {
        $actionsCount = sizeof($this->actions);
        if ($actionsCount == 0) $this->redir("index.php");
        if (!array_key_exists($actionName, $this->actions)) throw new Exception("No action or no valid action sent");
        $this->actions[$actionName]();
    }
    #endregion
    #endregion
    #region MISC METHODS
    #region public static useModel method
    /**
     * Include model files to use on code based on $modelName.
     * @param string $modelName Name of the model to be included.
     * @return DBModel Containing the object model to make operations.
     * @throws Exception When model doesn't exists on models folder.
     */
    public static function useModel(string $modelName)
    {
        $path = "../models/" . $modelName . ".php";
        if (!file_exists($path)) throw new Exception("Model doesn't exists in models folder ($path).");
        $model = require_once $path;
        return new DBModel($model);
    }
    #endregion
    #region public static checkPOSTData method
    /**
     * Verify if $dataIndexes exists as indexes on superglobal $_POST and returns variables on associative array.
     * @param string[] $dataIndexes String array containing names of indexes to be check on POST.
     * @return Model Containing data from superglobal POST.
     * @throws Exception When index not found on POST.
     */
    public static function checkPOSTData(array $dataIndexes)
    {
        $data = array();
        foreach ($dataIndexes as $dataIndex) {
            if (!isset($_POST[$dataIndex])) throw new Exception("No valid data received on POST.");
            $data[$dataIndex] = $_POST[$dataIndex];
        }
        return new Model($data);
    }
    #endregion
    #region public static toJSONArray method
    /**
     * Write on a JSON array format a list of Models
     * @param array $models array of Model elements to be formatted
     * @return string Text on JSON format for the array
     */
    public static function toJSONArray(array $models)
    {
        $response = "[";
        if (sizeof($models) > 0) {
            $response .= $models[0];
            for ($i = 1; $i < sizeof($models); $i++) $response .= "," . $models[$i];
        }
        $response .= "]";
        return $response;
    }
    #endregion
    #endregion
    #endregion
}
#endregion
