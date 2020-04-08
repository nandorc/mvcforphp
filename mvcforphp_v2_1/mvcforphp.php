<?php
//GENERAL INFORMATION
/**
 * MVC for PHP 
 * This classes allow users to manage most common operations with databases, based on mysqli functions. It also includes support for MVC components.
 * @author Daniel F. Rivera C.
 * @author tutordesoftware@gmail.com
 * @version 2.1
 * @package mvcforphp
 */
// DATABASE PARAMETERS
/**
 * Name for server host for database.
 */
const HOST_NAME = "";
/**
 * Name for server host user to access to database.
 */
const HOST_USER = "";
/**
 * Name for server host password to access to database.
 */
const HOST_PWD = "";
/**
 * Name for server host port to connect to database.
 */
const HOST_PORT = "";
/**
 * Name for database to be accessed on server.
 */
const DATABASE_NAME = "";
//
//
//
//PHP error checking
error_reporting(E_ALL);
ini_set('display_errors', '1');
//
//Begin of session evironment
session_start();
//
//CLASS DATABASE
/**
 * Class for database access and modification functions.
 * @property int $lastIndex (STATIC) Stores the last AUTO_INCREMENT value generated on an add operation.
 * @method static mysqli_result runQuery(string $sqlQuery) Executes an $sqlQuery and return mysqli_result.
 * @method static void runTransaction(string $sqlTransaction) Executes an $sqlTransaction on database.
 * @method static Model[] getAll(SQLClauses $queryConf) Get all data on a table depending on optional SQLClauses defined.
 * @method static null|Model get(string $id) Get a row from a table depending on $id.
 * @method static void add(string[] $newData, SQLClauses $transConf) Add the $newData to Database Table depending on optional SQLClauses defined.
 * @method static void update(string $id, string[] $newData, SQLClauses $transConf) Update $newData of an item on a table depending on $id and optional SQLClases defined.
 * @method static void delete(string $id) Delete an item from a table depending on $id.
 * @method static int getNextIndex() Returns the next index for auto_increment value on table.
 */
class Database
{
    //PUBLIC STATIC ATTRIBUTES
    /**
     * (STATIC) Stores the last AUTO_INCREMENT value generated on an add operation.
     * @var int
     */
    public static $lastIndex;
    //PROTECTED STATIC ATTRIBUTES
    /**
     * Table object to set table definition attributes.
     * @var Table
     */
    protected static $table;
    //
    //PRIVATE STATIC ATTRIBUTES
    /**
     * Name for server host for database.
     * @var string
     */
    private static $hostName;
    /**
     * Name for server host user to access to database.
     * @var string
     */
    private static $hostUser;
    /**
     * Name for server host password to access to database.
     * @var string
     */
    private static $hostPwd;
    /**
     * Name for server host port to connect to database.
     * @var string
     */
    private static $hostPort;
    /**
     * Name for database to be accessed on server.
     * @var string
     */
    private static $databaseName;
    //
    //PUBLIC STATIC METHODS
    /**
     * Excecute a SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return mysqli_result if query succeed
     * @throws Exception if query failed
     */
    public static function runQuery(string $sqlQuery)
    {
        try {
            $connection = self::connect();
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
    public static function runTransaction(string $sqlTransaction)
    {
        try {
            $connection = self::connect();
            if ($connection->query($sqlTransaction) !== TRUE) throw new Exception($connection->error);
            self::$lastIndex = mysqli_insert_id($connection);
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
    public static function getAll(SQLClauses $queryConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null) throw new Exception("No table defined to make this operation.");
            if ($queryConf == null) $sql = "select * from " . self::$table->tableName . " order by " . self::$table->primaryKey . ";";
            else {
                $sql = "select ";
                $selectFields = $queryConf->selectFields;
                if ($selectFields != "") $sql = $sql . $selectFields;
                else $sql = $sql . "*";
                $sql = $sql . " from " . self::$table->tableName;
                $wherePairs = $queryConf->wherePairs;
                if ($wherePairs != "") $sql = $sql . " where " . $wherePairs;
                $orderPairs = $queryConf->orderPairs;
                if ($orderPairs != "") $sql = $sql . " order by " . $orderPairs;
                else $sql = $sql . " order by " . self::$table->primaryKey;
                $sql = $sql . ";";
            }
            $list = array();
            $resultsDB = self::runQuery($sql);
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
    public static function get(string $id)
    {
        try {
            static::defineTable();
            if (self::$table == null) throw new Exception("No table defined to make this operation.");
            $object = null;
            if ($id != "NULL" && $id != "") {
                $resultDB = self::runQuery("select * from " . self::$table->tableName . " where " . self::$table->primaryKey . "='$id';");
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
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->selectFields must be defined in order to define which fields are going to be send to the INSERT sentence.
     * @return void
     * @throws Exception If something fail adding data.
     */
    public static function add(array $newData, SQLClauses $transConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null) throw new Exception("No table defined to make this operation.");
            $fieldsCount = sizeof(self::$table->fields);
            $dataCount = sizeof($newData);
            if ($dataCount > $fieldsCount) throw new Exception("Data list can't have more elements than field list.");
            else if ($dataCount < $fieldsCount && $transConf == null) throw new Exception("If data list has less elements than field list, a selectFields list must be defined.");
            else if ($dataCount < $fieldsCount) {
                $sfCount = sizeof(explode(",", $transConf->selectFields));
                if ($dataCount != $sfCount) throw new Exception("No valid selectFields list for data list given.");
            } else {
                $transConf = new SQLClauses();
                $transConf->selectFields = self::$table->getFieldNames();
            }
            $fieldsToAdd = explode(",", $transConf->selectFields);
            $values = self::$table->parseValueOf($fieldsToAdd[0], $newData[0]);
            for ($i = 1; $i < $dataCount; $i++) $values = $values . "," . self::$table->parseValueOf($fieldsToAdd[$i], $newData[$i]);
            $sql = "insert into " . self::$table->tableName . " (" . $transConf->selectFields . ") values (" . $values . ");";
            self::runTransaction($sql);
        } catch (Exception $ex) {
            throw new Exception("Something failed adding data: " . $ex->getMessage());
        }
    }
    /**
     * Update an item from a table depending on id
     * @param string $id PK from the element to update.
     * @param string[] $newData Array which contains the new data to save on the row.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->selectFields must be defined in order to define which fields are going to be send to the UPDATE sentence.
     * @return void If operation succeed.
     * @throws Exception If something updating data fail.
     */
    public static function update(string $id, array $newData, SQLClauses $transConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null) throw new Exception("No table defined to make this operation.");
            $fieldsCount = sizeof(self::$table->fields);
            $dataCount = sizeof($newData);
            if ($dataCount > $fieldsCount) throw new Exception("Data list can't have more elements than field list.");
            else if ($dataCount < $fieldsCount && $transConf == null) throw new Exception("If data list has less elements than field list, a selectFields list must be defined.");
            else if ($dataCount < $fieldsCount) {
                $upCount = sizeof(explode(",", $transConf->selectFields));
                if ($dataCount != $upCount) throw new Exception("No valid updatePairs list for data list given.");
            } else {
                $transConf = new SQLClauses();
                $transConf->selectFields = self::$table->getFieldNames();
            }
            $fieldsToChange = explode(",", $transConf->selectFields);
            $values = $fieldsToChange[0] . "=" . self::$table->parseValueOf($fieldsToChange[0], $newData[0]);
            for ($i = 1; $i < $dataCount; $i++) $values = $values . "," . $fieldsToChange[$i] . "=" . self::$table->parseValueOf($fieldsToChange[$i], $newData[$i]);
            self::runTransaction("update " . self::$table->tableName . " set " . $values . " where " . self::$table->primaryKey . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed updating data: " . $ex->getMessage());
        }
    }
    /**
     * Delete an item from a table depending on id
     * @param string $id PK of element to be deleted.
     * @return void If operation succeed.
     * @throws Exception If something deleting rows fail.
     */
    public static function delete(string $id)
    {
        try {
            static::defineTable();
            if (self::$table == null) throw new Exception("No table defined to make this operation.");
            self::runTransaction("delete from " . self::$table->tableName . " where " . self::$table->primaryKey . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
    //
    //PROTECTED STATIC METHODS
    /**
     * Set table model to database in orden to execute actions.
     */
    protected static function defineTable()
    {
        self::$table = null;
    }
    //
    //PRIVATE STATIC METHODS
    /**
     * Connect to database
     * @return mysqli if connection succeed.
     * @throws Exception if something fails while connecting to database.
     */
    private static function connect()
    {
        try {
            Database::$hostName = (HOST_NAME == "") ? "localhost" : HOST_NAME;
            Database::$hostUser = (HOST_USER == "") ? "root" : HOST_USER;
            Database::$hostPwd = HOST_PWD;
            Database::$hostPort = (HOST_PORT == "") ? "3306" : HOST_PORT;
            Database::$databaseName = DATABASE_NAME;
            $connection = new mysqli(Database::$hostName, Database::$hostUser, Database::$hostPwd, Database::$databaseName, Database::$hostPort);
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
//
//CLASS TABLE
/**
 * Defines basic structure for TableModels based on Database entities.
 * @property string $tableName Defines the name of the entity on relational DB.
 * @property string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
 * @property string $primaryKey Defines the name of the field wich is the primary key on the DB entity.
 * @method string[] getFieldNames() Get an array with field names for table.
 * @method string parseValueOf(string $name, string $value) Parses the $value given based on specific field type for the given field $name.
 */
class Table
{
    //PRIVATE ATTRIBUTES
    /**
     * Defines the name of the entity.
     */
    private $tableName;
    /**
     * Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair. Type could be key, text, number, date or datetime. If type is not defined it would be text as default. 
     */
    private $fields;
    /**
     * Defines the name of the field wich is the primary key on the DB entity.
     */
    private $primaryKey;
    //
    //PUBLIC METHODS
    /**
     * Creates a new Table defining table components.
     * @param string $tableName Defines the name of the entity on relational DB.
     * @param string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
     * @param string $primaryKey Defines the name of the field wich is the primary key on the DB entity.
     * @return Table
     */
    public function __construct(string $tableName = "", array $fields = array(), string $primaryKey = "")
    {
        $this->tableName = $tableName;
        $this->fields = $fields;
        $this->primaryKey = $primaryKey;
    }
    /**
     * Return the value of an attribute on class
     * @param string $name Name of the attribute to be returned
     * @return string|string[] 
     */
    public function __get($name)
    {
        return $this->$name;
    }
    /**
     * Set value to an attribute on class
     * @param string $name Name of the attribute to be set.
     * @param string|string[] $value Value to be set to the attribute.
     * @return void
     */
    public function __set($name, $value)
    {
        if ($name == "fields") {
            $count = sizeof($value);
            for ($i = 0; $i < $count; $i++) {
                $fieldPair = explode(":", $value[$i]);
                if (!isset($fieldPair[1]) || $fieldPair[1] == "") $value[$i] = $fieldPair[0] . ":text";
            }
        }
        $this->$name = $value;
    }
    /**
     * Get an array with field names for table
     * @return string[]
     */
    public function getFieldNames()
    {
        $fieldNames = array();
        foreach ($this->fields as $field) {
            $fieldInfo = explode(":", $field);
            $fieldNames[] = $fieldInfo[0];
        }
        return $fieldNames;
    }
    /**
     * Parses the $value given based on specific field type for the given field $name
     * @param string $name The name of the field on the table to use as reference.
     * @param string $value The string value to be parsed based on the referenced field type.
     * @return string
     * @throws Exception If $name doesn't match with any fieldName on table.
     */
    public function parseValueOf(string $name, string $value)
    {
        $parsedValue = "";
        foreach ($this->fields as $field) {
            $fieldInfo = explode(":", $field);
            if ($fieldInfo[0] == $name) {
                $parsedValue = $this->getFieldValue($fieldInfo[1], $value);
                break;
            }
        }
        if ($parsedValue == "") throw new Exception("Parse error: Fieldname couldn't be found on table.");
        return $parsedValue;
    }
    //
    //PRIVATE METHODS
    /**
     * Get parsed value depending on field type.
     * @param string $type Type of field to be parsed. It could be key, text, number, date or datetime.
     * @param string $value Value to be parsed according to field type defined.
     * @return string Empty if no valid field type given.
     */
    private function getFieldValue(string $type, string $value)
    {
        if ($type == "key") return self::getKeyField($value);
        elseif ($type == "text") return self::getTextField($value);
        elseif ($type == "number") return self::getNumberField($value);
        elseif ($type == "date") return self::getDateField($value);
        elseif ($type == "datetime") return self::getDateTimeField($value);
        else return "";
    }
    /**
     * Get value from a field that is primary or foreign key
     * @param string $value Value to be parsed.
     * @return string
     */
    private function getKeyField(string $value)
    {
        return ($value == "") ? "NULL" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type text
     * @param string $value Value to be parsed.
     * @return string
     */
    private function getTextField(string $value)
    {
        return "'" . htmlspecialchars(trim($value), ENT_QUOTES) . "'";
    }
    /**
     * Get value from a field that is type number
     * @param string $value Value to be parsed.
     * @return string
     */
    private function getNumberField(string $value)
    {
        return ($value == "") ? "'0'" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type date
     * @param string $value Value to be parsed.
     * @return string
     */
    private function getDateField(string $value)
    {
        date_default_timezone_set('America/Bogota');
        return ($value == "") ? "'" . date("Y-m-d") . "'" : "'" . $value . "'";
    }
    /**
     * Get value from a field that is type datetime.
     * @param string $value Value to be parsed.
     * @return string
     */
    private function getDateTimeField(string $value)
    {
        date_default_timezone_set('America/Bogota');
        return ($value == "") ? "'" . date("Y-m-d H:i:s") . "'" : "'" . $value . "'";
    }
}
//
//CLASS SQLClauses
/**
 * Class for defining special SQL clauses.
 * @property string[] $selectFields Defines a field list.
 * @property string[] $wherePairs Defines a WHERE clause content. Each string element must be written as a "[name]:[value]" pair.
 * @property string[] $orderPairs Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
 */
class SQLClauses
{
    //PRIVATE ATTRIBUTES
    /**
     * Defines a field list.
     * @var string[]
     */
    private $selectFields = array();
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
    //
    //PUBLIC METHODS
    /**
     * Return the value of an attribute on class
     * @param string $name Name of the attribute to be returned
     * @return string
     */
    public function __get($name)
    {
        $clause = "";
        switch ($name) {
            case "selectFields":
                $count = sizeof($this->selectFields);
                if ($count > 0) {
                    $clause = $this->selectFields[0];
                    for ($i = 1; $i < $count; $i++) $clause = $clause . "," . $this->selectFields[$i];
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
     * Set value to an attribute on class
     * @param string $name Name of the attribute to be set.
     * @param string[] $value Value to be set to the attribute.
     * @return void
     * @throws Exception When setting data to WherePairs when no value is specified.
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
//
//CLASS MVC
/**
 * Class for defining page MVC components.
 * 
 * @property int $folderLevel Defines the folder level since root folder. 0 for root folder and by default is 1 for Views and Controllers.
 * @method void useModel(string $modelName) Include model files to use on code based on $modelName.
 * @method static void showServerVariables() Show on screen variables from server.
 */
class MVC
{
    //PUBLIC ATTRIBUTES
    /**
     * Defines the folder level since root folder. 0 for root folder and by default is 1 for Views and Controllers.
     * @var int
     */
    public $folderLevel = 1;
    //
    //PUBLIC METHODS
    /**
     * Include model files to use on code based on $modelName.
     * @param string $modelName Name of the model to be included.
     * @return void
     */
    public function useModel(string $modelName)
    {
        require_once $this->getRootPath() . "models/" . $modelName . ".php";
    }
    //PUBLIC STATIC METHODS
    /**
     * Show on screen variables from server.
     * @return void
     */
    public static function showServerVariables()
    {
        $keys = array_keys($_SERVER);
        foreach ($keys as $key) echo $key . " - " . $_SERVER[$key] . "<br/>";
    }
    //
    //PROTECTED METHODS
    /**
     * Constructs root path based on class folder level.
     * @return void
     */
    protected function getRootPath()
    {
        $rootPath = "";
        for ($i = 0; $i < $this->folderLevel; $i++) $rootPath .= "../";
        return $rootPath;
    }
}
//
//CLASS MODEL
/**
 * Class for building models based on associative arrays.
 * 
 * Each member of the associative array which builds the Model is stored internally to work as a property that can be accesed or changed just by using the property name.
 */
class Model
{
    //PRIVATE ATTRIBUTES
    /**
     * Stores the model data fields.
     * @var string[] Associative
     */
    private $data = array();
    //
    //PUBLIC METHODS
    /**
     * Creates a new Model defining its fields.
     * @param string[] $data Defines a string associative array which contains the fields of the model.
     * @return Model
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    /**
     * Return the value of a field on model.
     * @param string $name Name of the field to be returned
     * @return string 
     */
    public function __get($name)
    {
        return $this->data[$name];
    }
    /**
     * Set value to a field on the model.
     * @param string $name Name of the field to be set.
     * @param string $value Value to be set to the field.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    /**
     * Shows a model data as a JSON string.
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}
//
//CLASS View
/**
 * Class for defining page Views.
 * 
 * @property int $folderLevel Defines the folder level since root folder. 0 for root folder and by default is 1 for Views.
 * @method void useModel(string $modelName) Include model files to use on code.
 * @method void useComponent(string $componentName, array $data) Includes a component file while rendering, $data is an optional associative array to send data to component.
 * @method void render(function $content, array $data) Render and show the page. $content is an anonymous functios containing additional content for the page. $data is an optional associative array to send data to the page template.
 */
class View extends MVC
{
    //PUBLIC METHODS
    /**
     * Includes a component file while rendering, $data is an optional associative array to send data to component.
     * @param string $componentName Name of the component to be included.
     * @param array $data (Optional) Additional data to be send to the component.
     * @return void
     */
    public function useComponent(string $componentName, array $data = array())
    {
        require_once $this->getRootPath() . "views/shared/components/" . $componentName . ".php";
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
        require_once $this->getRootPath() . "views/shared/template.php";
    }
}
//
//CLASS Controller
/**
 * Class for defining site Controllers.
 * 
 * @property int $folderLevel Defines the folder level since root folder. 0 for root folder and by default is 1 for Controllers.
 * @method void useModel(string $modelName) Include model files to use on code.
 * @method void sendResponse(string $message) Send a response message
 * @method void sendError(string $message) Send an error message
 * @method void setAction(string $name, function $action) Create a new action for the controller. $name is the name of the action and $action is an anonymous functions with the action content.
 * @method void processAction(string $actionName) Process action from the action array.
 */
class Controller extends MVC
{
    //PRIVATE ATTRIBUTES
    /**
     * Associative array containig delegate functions for actions.
     * @var array
     */
    private $actions = array();
    //
    //PUBLIC FUNCTIONS
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
    public function setAction(string $name, $action)
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
            if ($actionsCount == 0) header("Location: " . $this->getRootPath() . "index.php");
            if (!array_key_exists($actionName, $this->actions)) throw new Exception("No action or no valid action sent");
            $this->actions[$actionName]();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }
}
