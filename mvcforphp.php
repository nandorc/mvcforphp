<?php
/*
MVC for PHP
Made by Daniel F. Rivera C.
This classes allow users to manage most common operations with databases, based on mysqli functions
To correctly use this class, it must be inherited by a model class and also has to be modified the parameters of connection.
*/
//
//CLASS DATABASE
class Database
{
    //PARAMETERS
    //if MODE is set on "offline" works on localhost and OFFLINE_DATABASE_NAME shoud be configured
    //if MODE is set on "online" works on online host and ONLINE_HOST_NAME, ONLINE_HOST_USER, ONLINE_HOST_PWD, ONLINE_HOST_PORT and ONLINE_DATABASE_NAME should be configured
    private const MODE = "offline";
    //Offline Parameters
    private const OFFLINE_DATABASE_NAME = "";
    //Online Parameters
    private const ONLINE_HOST_NAME = "";
    private const ONLINE_HOST_USER = "";
    private const ONLINE_HOST_PWD = "";
    private const ONLINE_HOST_PORT = "";
    private const ONLINE_DATABASE_NAME = "";
    //
    //
    //MAIN OPERATIONS
    //Connect with database
    private static function connect()
    {
        try {
            if (self::MODE == "offline") {
                $connection = mysqli_connect("localhost", "root", "", self::OFFLINE_DATABASE_NAME, "3306");
            } else if (self::MODE == "online") {
                $connection = mysqli_connect(self::ONLINE_HOST_NAME, self::ONLINE_HOST_USER, self::ONLINE_HOST_PWD, self::ONLINE_DATABASE_NAME, self::ONLINE_HOST_PORT);
            } else {
                throw new Exception("No valid mode for connection set.");
            }
            if (!$connection) {
                throw new Exception($connection->connect_error);
            }
            $connection->query("SET NAMES UTF8");
            return $connection;
        } catch (Exception $ex) {
            throw new Exception("Connection failed: " . $ex->getMessage());
        } finally {
            unset($connection);
        }
    }
    //
    //Excecute a SQL Query
    private static function runQuery(string $sqlQuery)
    {
        try {
            $connection = self::connect();
            $result = $connection->query($sqlQuery);
            $connection->close();
            return $result;
        } catch (Exception $ex) {
            throw new Exception("Query failed: " . $ex->getMessage());
        } finally {
            unset($connection, $result);
        }
    }
    //
    //Execute a SQL Transaction
    private static function runTransaction(string $sqlTransaction)
    {
        try {
            $connection = self::connect();
            if ($connection->query($sqlTransaction) !== TRUE) {
                throw new Exception($connection->error);
            }
            $connection->close();
        } catch (Exception $ex) {
            throw new Exception("Transaction failed: " . $ex->getMessage());
        } finally {
            unset($connection);
        }
    }
    //
    //
    //ADITIONAL OPERATIONS
    //GetAll elements from table
    //If refValue is a text, is used as value from first element defined on foreignKeys array
    //If refValue is a text and refId is different of 0, is used as value for the corresponding index element on foreignKeys array
    //If refValue is and array, each element is taken as value for each element on foreignKeys array
    protected static function getAll(Table $table, QueryOptions $queryConf = null)
    {
        try {
            if ($queryConf == null) {
                $sql = "select * from " . $table->tableName . " order by " . $table->primaryKey . ";";
            } else {
                $sql = "select";
                $count = sizeof($queryConf->fields);
                if ($count > 0) {
                    $sql = $sql . " " . $queryConf->fields[0];
                    for ($i = 1; $i < $count; $i++) {
                        $sql = $sql . "," . $queryConf->fields[$i];
                    }
                } else {
                    $sql = $sql . " *";
                }
                $sql = $sql . " from " . $table->tableName;
                if (sizeof($queryConf->refIds) != sizeof($queryConf->refValues)) {
                    throw new Exception("Ids and Values doesn't match on where clause.");
                }
                $count = sizeof($queryConf->refIds);
                if ($count > 0) {
                    $sql = $sql . " where " . $queryConf->refIds[0] . "='" . $queryConf->refValues[0] . "'";
                    for ($i =  1; $i < $count; $i++) {
                        $sql = $sql . " and " . $queryConf->refIds[$i] . "='" . $queryConf->refValues[$i] . "'";
                    }
                }
                $count = sizeof($queryConf->orderIds);
                if ($count > 0) {
                    $sql = $sql . " order by " . $queryConf->orderIds[0];
                    if (isset($queryConf->orderTypes[0]) && $queryConf->orderTypes[0] != "") {
                        $sql = $sql . " " . $queryConf->orderTypes[0];
                    }
                    for ($i = 1; $i < $count; $i++) {
                        $sql = $sql . ", " . $queryConf->orderIds[$i];
                        if (isset($queryConf->orderTypes[$i]) && $queryConf->orderTypes[$i] != "") {
                            $sql = $sql . " " . $queryConf->orderTypes[$i];
                        }
                    }
                } else {
                    $sql = $sql . " order by " . $table->primaryKey;
                }
                $sql = $sql . ";";
            }
            $resultsDB = self::runQuery($sql);
            if ($resultsDB->num_rows > 0) {
                $list = array();
                while ($result = $resultsDB->fetch_assoc()) {
                    $list[] = new Model($result);
                }
                return $list;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            throw new Exception("Something failed getting data: " . $ex->getMessage());
        }
    }
    //Get a row from a table depending on id
    protected static function get(Table $table, string $id)
    {
        try {
            $object = null;
            if ($id != "NULL" && $id != "") {
                $sql = "select * from " . $table->tableName . " where " . $table->primaryKey . "='$id';";
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
    //Add an item to the table
    protected static function add(Table $table, array $newData)
    {
        try {
            $count = sizeof($table->fields);
            $values = self::getFieldValue($newData[0], $table->fieldTypes[0]);
            for ($i = 1; $i < $count; $i++) {
                $values = $values . "," . self::getFieldValue($newData[$i], $table->fieldTypes[$i]);
            }
            $sql = "insert into " . $table->tableName . " values (" . $values . ");";
            self::runTransaction($sql);
        } catch (Exception $ex) {
            throw new Exception("Something failed adding data: " . $ex->getMessage());
        }
    }
    //Update an item from a table depending on id
    protected static function update(Table $table, string $id, array $newData)
    {
        try {
            $count = sizeof($table->fields);
            $values = $table->fields[0] . "=" . self::getFieldValue($newData[0], $table->fieldTypes[0]);
            for ($i = 1; $i < $count; $i++) {
                $values = $values . "," . $table->fields[$i] . "=" . self::getFieldValue($newData[$i], $table->fieldTypes[$i]);
            }
            self::runTransaction("update " . $table->tableName . " set " . $values . " where " . $table->primaryKey . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed updating data: " . $ex->getMessage());
        }
    }
    //Delete an item from a table depending on id
    protected static function delete(Table $table, string $id)
    {
        try {
            self::runTransaction("delete from " . $table->tableName . " where " . $table->primaryKey . "='$id';");
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
    //
    //
    //FUNCTIONS FOR FIELDS
    //Get value from a field
    private static function getFieldValue(string $value, string $type)
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
    private static function getKeyField($value)
    {
        if ($value == "") {
            return "NULL";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type text
    private static function getTextField($value)
    {
        return "'" . htmlspecialchars(trim($value), ENT_QUOTES) . "'";
    }
    //Get value from a field that is type number
    private static function getNumberField($value)
    {
        if ($value == "") {
            return "'0'";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type date
    private static function getDateField($value)
    {
        date_default_timezone_set('America/Bogota');
        if ($value == "") {
            return "'" . date("Y-m-d") . "'";
        } else {
            return "'" . $value . "'";
        }
    }
    //Get value from a field that is type datetime
    private static function getDateTimeField($value)
    {
        date_default_timezone_set('America/Bogota');
        if ($value == "") {
            return "'" . date("Y-m-d H:i:s") . "'";
        } else {
            return "'" . $value . "'";
        }
    }
}
//
//
//CLASS TABLE
class Table
{
    //Properties
    private $tableName;
    private $fields;
    private $fieldTypes;
    private $primaryKey;
    //
    //Constuctor
    public function __construct(string $tableName = "", array $fields = array(), array $fieldTypes = array(), string $primaryKey = "")
    {
        $this->tableName = $tableName;
        $this->fields = $fields;
        $this->fieldTypes = $fieldTypes;
        $this->primaryKey = $primaryKey;
    }
    //Get and Set Function
    public function __get($name)
    {
        return $this->$name;
    }
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
//
//
//CLASS MODEL
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
    public function toString()
    {
        $dString = "| ";
        foreach ($this->data as $d) {
            $dString = $dString . $d . " | ";
        }
    }
}
//
//
//CLASS QueryOptions
class QueryOptions
{
    //Attributes
    private $fields = array();
    private $refIds = array();
    private $refValues = array();
    private $orderIds = array();
    private $orderTypes = array();
    //Constructor
    public function __construct(array $fields = array(), array $refIds = array(), array $refValues = array(), array $orderIds = array(), array $orderTypes = array())
    {
        $this->fields = $fields;
        $this->refIds = $refIds;
        $this->refValues = $refValues;
        $this->orderIds = $orderIds;
        $this->orderTypes = $orderTypes;
    }
    //Get and Set Functions
    public function __get($name)
    {
        return $this->$name;
    }
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
