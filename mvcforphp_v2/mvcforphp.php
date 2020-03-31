<?php
//
/**
 * MVC for PHP 
 * 
 * This classes allow users to manage most common operations with databases, based on mysqli functions
 * To correctly use this class, it must be inherited by a model class and also has to be modified the parameters of connection.
 * 
 * @author Daniel F. Rivera C.
 * @author tutordesoftware@gmail.com
 * @version 2.0
 * @package mvcforphp
 */
//
//PARAMETERS
/**
 * MODE for database connections.
 * 
 * if MODE is set on "offline" works on localhost and OFFLINE_DATABASE_NAME shoud be configured.
 * 
 * if MODE is set on "online" works on online host and ONLINE_HOST_NAME, ONLINE_HOST_USER, ONLINE_HOST_PWD, ONLINE_HOST_PORT and ONLINE_DATABASE_NAME should be configured.
 */
const MODE = "offline";
//Offline mode parameters.
const OFFLINE_DATABASE_NAME = "";
//Online mode parameters
const ONLINE_HOST_NAME = "";
const ONLINE_HOST_USER = "";
const ONLINE_HOST_PWD = "";
const ONLINE_HOST_PORT = "";
const ONLINE_DATABASE_NAME = "";
//
//CLASS DATABASE
/**
 * Class for database access and modification functions.
 */
class Database
{
    //PROTECTED ATTRIBUTES
    protected static $table;
    //PROTECTED METHODS
    protected static function defineTable()
    {
        self::$table = null;
    }
    //PRIVATE METHODS
    /**
     * Connect to database
     * @return mysqli if connection succeed.
     * @throws Exception if no valid mode is selected.
     * @throws Exception if no name is given on offline connection mode.
     * @throws Exception if any value is blank on online connection mode (except ONLINE_HOST_PWD).
     */
    private static function connect()
    {
        try {
            if (MODE != "offline" && MODE != "online")
                throw new Exception("No valid mode for connection set.");
            else if (MODE == "offline" && OFFLINE_DATABASE_NAME == "")
                throw new Exception("No name for database defined on offline connection.");
            else if (MODE == "online" && (ONLINE_HOST_NAME == "" || ONLINE_HOST_USER == "" || ONLINE_HOST_PORT == "" || ONLINE_DATABASE_NAME == ""))
                throw new Exception("Missing parameters for online connection to database.");
            if (MODE == "offline")
                $connection = new mysqli("localhost", "root", "", OFFLINE_DATABASE_NAME, "3306");
            else
                $connection = new mysqli(ONLINE_HOST_NAME, ONLINE_HOST_USER, ONLINE_HOST_PWD, ONLINE_DATABASE_NAME, ONLINE_HOST_PORT);
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
    /**
     * Excecute a SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return \mysqli_result if query succeed
     * @throws Exception if query failed
     */
    public static function runQuery(string $sqlQuery)
    {
        try {
            $connection = self::connect();
            $result = $connection->query($sqlQuery);
            if (!$result)
                throw new Exception($connection->error);
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
            if ($connection->query($sqlTransaction) !== TRUE)
                throw new Exception($connection->error);
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
     * @return array<Model> List of elements obtained by the query.
     * @throws Exception If no table is defined when using this function.
     * @throws Exception If something fail getting data.
     */
    public static function getAll(SQLClauses $queryConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null)
                throw new Exception("No table defined to make this operation.");
            if ($queryConf == null) {
                $sql = "select * from " . self::$table->tableName . " order by " . self::$table->primaryKey . ";";
            } else {
                $sql = "select ";
                $selectFields = $queryConf->selectFields;
                if ($selectFields != "")
                    $sql = $sql . $selectFields;
                else
                    $sql = $sql . "*";
                $sql = $sql . " from " . self::$table->tableName;
                $wherePairs = $queryConf->wherePairs;
                if ($wherePairs != "")
                    $sql = $sql . " where " . $wherePairs;
                $orderPairs = $queryConf->orderPairs;
                if ($orderPairs != "")
                    $sql = $sql . " order by " . $orderPairs;
                else
                    $sql = $sql . " order by " . self::$table->primaryKey;
                $sql = $sql . ";";
            }
            $list = array();
            $resultsDB = self::runQuery($sql);
            if ($resultsDB->num_rows > 0) {
                while ($result = $resultsDB->fetch_assoc()) {
                    $list[] = new Model($result);
                }
            }
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
    //
    public static function get(string $id)
    {
        try {
            static::defineTable();
            if (self::$table == null)
                throw new Exception("No table defined to make this operation.");
            $object = null;
            if ($id != "NULL" && $id != "") {
                $sql = "select * from " . self::$table->tableName . " where " . self::$table->primaryKey . "='$id';";
                $resultDB = self::runQuery($sql);
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
     * @param array $newData Data to be send on INSERT sentence.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->selectFields must be defined in order to define which fields are going to be send to the INSERT sentence.
     * @return void
     * @throws Exception If something fail adding data.
     */
    public static function add(array $newData, SQLClauses $transConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null)
                throw new Exception("No table defined to make this operation.");
            $fieldsCount = sizeof(self::$table->fields);
            $dataCount = sizeof($newData);
            if ($dataCount > $fieldsCount)
                throw new Exception("Data list can't have more elements than field list.");
            else if ($dataCount < $fieldsCount && $transConf == null)
                throw new Exception("If data list has less elements than field list, a selectFields list must be defined.");
            else if ($dataCount < $fieldsCount) {
                $sfCount = sizeof(explode(",", $transConf->selectFields));
                if ($dataCount != $sfCount)
                    throw new Exception("No valid selectFields list for data list given.");
            } else {
                $transConf = new SQLClauses();
                $transConf->selectFields = self::$table->getFieldNames();
            }
            $fieldsToAdd = explode(",", $transConf->selectFields);
            $values = self::$table->parseValueOf($fieldsToAdd[0], $newData[0]);
            for ($i = 1; $i < $dataCount; $i++) {
                $values = $values . "," . self::$table->parseValueOf($fieldsToAdd[$i], $newData[$i]);
            }
            $sql = "insert into " . self::$table->tableName . " (" . $transConf->selectFields . ") values (" . $values . ");";
            self::runTransaction($sql);
        } catch (Exception $ex) {
            throw new Exception("Something failed adding data: " . $ex->getMessage());
        }
    }
    /**
     * Update an item from a table depending on id
     * @param string $id PK from the element to update.
     * @param array<string> $newData Array which contains the new data to save on the row.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->selectFields must be defined in order to define which fields are going to be send to the UPDATE sentence.
     * @return void If operation succeed.
     * @throws Exception If something updating data fail.
     */
    public static function update(string $id, array $newData, SQLClauses $transConf = null)
    {
        try {
            static::defineTable();
            if (self::$table == null)
                throw new Exception("No table defined to make this operation.");
            $fieldsCount = sizeof(self::$table->fields);
            $dataCount = sizeof($newData);
            if ($dataCount > $fieldsCount)
                throw new Exception("Data list can't have more elements than field list.");
            else if ($dataCount < $fieldsCount && $transConf == null)
                throw new Exception("If data list has less elements than field list, a selectFields list must be defined.");
            else if ($dataCount < $fieldsCount) {
                $upCount = sizeof(explode(",", $transConf->selectFields));
                if ($dataCount != $upCount)
                    throw new Exception("No valid updatePairs list for data list given.");
            } else {
                $transConf = new SQLClauses();
                $transConf->selectFields = self::$table->getFieldNames();
            }
            $fieldsToChange = explode(",", $transConf->selectFields);
            $values = $fieldsToChange[0] . "=" . self::$table->parseValueOf($fieldsToChange[0], $newData[0]);
            for ($i = 1; $i < $dataCount; $i++) {
                $values = $values . "," . $fieldsToChange[$i] . "=" . self::$table->parseValueOf($fieldsToChange[$i], $newData[$i]);
            }
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
            if (self::$table == null)
                throw new Exception("No table defined to make this operation.");
            self::runTransaction("delete from " . self::$table->tableName . " where " . self::$table->primaryKey . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
}
//CLASS TABLE
/**
 * Defines basic structure for TableModels based on Database entities.
 * @property string $tableName Defines the name of the entity on relational DB.
 * @property array<string> $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
 * @property string $primaryKey Defines the name of the field wich is the primary key on the DB entity.
 */
class Table
{
    //Properties
    private $tableName;
    private $fields;
    private $primaryKey;
    //
    //Constuctor
    public function __construct(string $tableName = "", array $fields = array(), string $primaryKey = "")
    {
        $this->tableName = $tableName;
        $this->fields = $fields;
        $this->primaryKey = $primaryKey;
    }
    //Get and Set Function
    public function __get($name)
    {
        return $this->$name;
    }
    public function __set($name, $value)
    {
        if ($name == "fields") {
            $count = sizeof($value);
            for ($i = 0; $i < $count; $i++) {
                $fieldPair = explode(":", $value[$i]);
                if (!isset($fieldPair[1]) || $fieldPair[1] == "")
                    $value[$i] = $fieldPair[0] . ":text";
            }
        }
        $this->$name = $value;
    }
    /**
     * Get an array with field names for table
     * @return array<string>
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
    //PARSING FUNCTIONS
    /**
     * Parses the $value given based on specific field type for the given field $name
     * @param string $name The name of the field on the table to use as reference.
     * @param string $value The string value to be parsed based on the referenced field type.
     */
    public function parseValueOf(string $name, string $value)
    {
        $value = "";
        foreach ($this->fields as $field) {
            $fieldInfo = explode(":", $field);
            if ($fieldInfo[0] == $name) {
                $value = $this->getFieldValue($fieldInfo[1], $value);
                break;
            }
        }
        return $value;
    }
    //Get value from a field
    private function getFieldValue(string $type, string $value)
    {
        if ($type == "key") {
            return self::getKeyField($value);
        } elseif ($type == "text") {
            return self::getTextField($value);
        } elseif ($type == "number") {
            return self::getNumberField($value);
        } elseif ($type == "date") {
            return self::getDateField($value);
        } elseif ($type == "datetime") {
            return self::getDateTimeField($value);
        } else {
            return "";
        }
    }
    //Get value from a field that is primary or foreign key
    private function getKeyField($value)
    {
        if ($value == "") {
            return "NULL";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type text
    private function getTextField($value)
    {
        return "'" . htmlspecialchars(trim($value), ENT_QUOTES) . "'";
    }
    //Get value from a field that is type number
    private function getNumberField($value)
    {
        if ($value == "") {
            return "'0'";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type date
    private function getDateField($value)
    {
        date_default_timezone_set('America/Bogota');
        if ($value == "") {
            return "'" . date("Y-m-d") . "'";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type datetime
    private function getDateTimeField($value)
    {
        date_default_timezone_set('America/Bogota');
        if ($value == "") {
            return "'" . date("Y-m-d H:i:s") . "'";
        } else {
            return "'" . $value . "'";
        }
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
    //Data atributes
    private $data = array();
    //Constructor
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    //Get and Set Function
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    public function __get($name)
    {
        return $this->data[$name];
    }
    /**
     * Shows a model data as a single string divided by | symbols.
     */
    public function __toString()
    {
        $dString = "| ";
        foreach ($this->data as $d) {
            $dString = $dString . $d . " | ";
        }
        return $dString;
    }
}
//CLASS SQLClauses
/**
 * Class for defining special SQL clauses.
 * @property array<string> $selectFields Defines a field list.
 * @property array<string> $wherePairs Defines a WHERE clause content. Each string element must be written as a "[name]:[value]" pair.
 * @property array<string> $orderPairs Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
 * @property array<string> $updatePairs Defines clause for UPDATE transactions field-value list. Each string element must be written as a "[name]:[value]" pair.
 */
class SQLClauses
{
    //Attributes
    private $selectFields = array();
    private $wherePairs = array();
    private $orderPairs = array();

    //Get and Set Functions
    public function __get($name)
    {
        $clause = "";
        switch ($name) {
            case "selectFields":
                $count = sizeof($this->selectFields);
                if ($count > 0) {
                    $clause = $this->selectFields[0];
                    for ($i = 1; $i < $count; $i++)
                        $clause = $clause . "," . $this->selectFields[$i];
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
    public function __set($name, $value)
    {
        switch ($name) {
            case "wherePairs":
                $count = sizeof($value);
                for ($i =  0; $i < $count; $i++) {
                    $wherePair = explode(":", $value[$i]);
                    if (!isset($wherePair[1]))
                        throw new Exception("No value especified for where clause field.");
                }
                break;
            case "orderPairs":
                $count = sizeof($value);
                for ($i = 0; $i < $count; $i++) {
                    $orderPair = explode(":", $value[$i]);
                    if (!isset($orderPair[1]) || $orderPair[1] == "")
                        $value[$i] = $orderPair[0] . ":asc";
                }
                break;
        }
        $this->$name = $value;
    }
}
