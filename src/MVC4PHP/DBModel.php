<?php

namespace MVC4PHP;

use Exception;
use mysqli;

/**
 * Class for database access and modification functions.
 * @property int $lastIndex Stores the last AUTO_INCREMENT value generated on an add operation.
 * @property bool $bypass Controls if SQL statements must be shown before being executed.
 */
class DBModel
{
    /**
     * Associative array with supported data types and default values.
     */
    const SUPPORTED_TYPES = array(
        "text" => "''",
        "key" => "NULL",
        "number" => "'0'",
        "date" => "curdate()",
        "time" => "curtime()",
        "datetime" => "now()"
    );
    private $lastIndex;
    private $table;
    private $bypass = false;
    public function __get($name)
    {
        if ($name != "lastIndex") throw new Exception("Trying to access to an unknown property");
        return $this->lastIndex;
    }
    public function __set($name, $value)
    {
        if ($name != "bypass") throw new Exception("Trying to access to an unknown or forbidden property");
        else if (gettype($value) != "boolean") throw new Exception("bypass must be boolean");
        $this->bypass = $value;
    }
    /**
     * @param SQLTable $table Defines the sql table wich is the base for DBModel.
     * @return DBModel
     */
    public function __construct(SQLTable $table)
    {
        $this->lastIndex = 0;
        $this->table = $table;
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
            $path = "../resources/scripts/mvc4php/dbconf.json";
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
    /**
     * Excecute a SQL Query
     * @param string $sqlQuery Text with SQL Query to execute.
     * @return mysqli_result if query succeed
     * @throws Exception if query failed
     */
    private function runQuery(string $sqlQuery)
    {
        try {
            if ($this->bypass) echo $sqlQuery;
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
    private function runTransaction(string $sqlTransaction)
    {
        try {
            if ($this->bypass) echo $sqlTransaction;
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
                $resultDB = $this->runQuery("select * from " . $this->table->name . " where " . $this->table->pk . "='" . DBModel::cleanInputValue($id) . "';");
                if ($resultDB->num_rows > 0) $object = new Model($resultDB->fetch_assoc());
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
            $transConf = $this->validateFieldsCount($newData, $transConf);
            $dcount = sizeof($newData);
            $fieldsToAdd = explode(",", $transConf->fields);
            $values = $this->parseValueOf($fieldsToAdd[0], $newData[0]);
            for ($i = 1; $i < $dcount; $i++) $values .= "," . $this->parseValueOf($fieldsToAdd[$i], $newData[$i]);
            $this->runTransaction("insert into " . $this->table->name . " (" . $transConf->fields . ") values (" . $values . ");");
        } catch (Exception $ex) {
            throw new Exception("Something failed adding data: " . $ex->getMessage());
        }
    }
    /**
     * Update all specified items from a table
     * @param string[] $newData Array which contains the new data to save on the row.
     * @param SQLClauses $transConf (Optional) If $newData array has less elements than $table->fields, a SQLClauses->fields must be defined in order to define which fields are going to be send to the UPDATE sentence.
     * @return void If operation succeed.
     * @throws Exception If something updating data fail.
     */
    public function updateAll(array $newData, SQLClauses $transConf = null)
    {
        try {
            $transConf = $this->validateFieldsCount($newData, $transConf);
            $dcount = sizeof($newData);
            $fieldsToChange = explode(",", $transConf->fields);
            $values = $fieldsToChange[0] . "=" . $this->parseValueOf($fieldsToChange[0], $newData[0]);
            for ($i = 1; $i < $dcount; $i++) $values .= "," . $fieldsToChange[$i] . "=" . $this->parseValueOf($fieldsToChange[$i], $newData[$i]);
            $sql = "update " . $this->table->name . " set " . $values;
            if ($transConf->wherePairs != "") $sql .= " where " . $transConf->wherePairs;
            $sql .= ";";
            $this->runTransaction($sql);
        } catch (Exception $ex) {
            throw new Exception("Something failed updating data: " . $ex->getMessage());
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
            $transConf = $this->validateFieldsCount($newData, $transConf);
            $dcount = sizeof($newData);
            $fieldsToChange = explode(",", $transConf->fields);
            $values = $fieldsToChange[0] . "=" . $this->parseValueOf($fieldsToChange[0], $newData[0]);
            for ($i = 1; $i < $dcount; $i++) $values .= "," . $fieldsToChange[$i] . "=" . $this->parseValueOf($fieldsToChange[$i], $newData[$i]);
            $this->runTransaction("update " . $this->table->name . " set " . $values . " where " . $this->table->pk . "='" . DBModel::cleanInputValue($id) . "';");
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
            $this->runTransaction("delete from " . $this->table->name . " where " . $this->table->pk . "='" . DBModel::cleanInputValue($id) . "';");
        } catch (Exception $ex) {
            throw new Exception("Something failed deleting data: " . $ex->getMessage());
        }
    }
    /**
     * Validate data array to match with table fields
     * @param string[] $data String array to be checked.
     * @param SQLClauses $config (OPTIONAL) Clauses to be validated in case fields count doesn't match.
     * @return SQLClauses
     * @throws Exception If data doesn't match
     */
    private function validateFieldsCount(array $data, SQLClauses $config = null)
    {
        $fcount = sizeof($this->table->fieldsarray);
        $dcount = sizeof($data);
        if ($dcount > $fcount)
            throw new Exception("Data list can't have more elements than field list.");
        else if ($dcount < $fcount && $config == null)
            throw new Exception("If data list has less elements than field list, a fields list must be defined.");
        else if ($dcount < $fcount) {
            $ccount = sizeof(explode(",", $config->fields));
            if ($dcount != $ccount) throw new Exception("No valid fields list for data list given.");
        } else if ($config != null) {
            $config->fields = $this->table->fieldsarray;
        } else {
            $config = new SQLClauses(["fields" => $this->table->fieldsarray]);
        }
        return $config;
    }
    /**
     * Parses the $value given based on specific field type for the given field $fieldName
     * @param string $fieldName The name of the field on the table to use as reference.
     * @param string $value The string value to be parsed based on the referenced field type.
     * @return string
     * @throws Exception If $fieldName doesn't match with any field on table.
     */
    private function parseValueOf(string $fieldName, string $value)
    {
        $tabledata = $this->table->fieldsdata;
        if (!isset($tabledata[$fieldName])) throw new Exception("Parse error: Fieldname couldn't be found on table.");
        else if ($value == "") return DBModel::SUPPORTED_TYPES[$tabledata[$fieldName]];
        else return "'" . DBModel::cleanInputValue($value) . "'";
    }
    /**
     * Check and clean data to be put on SQL statements. Prevents SQL injection.
     * @param string $value Data to be cleaned.
     * @return string
     */
    public static function cleanInputValue(string $value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES);
    }
    /**
     * Get formatted value for current date, time or datetime depending on $type.
     * @param array $type (OPTIONAL) Type of value to return, could be "date", "time" or "datetime" (default).
     * @return string
     * @throws Exception If no valid $type given.
     */
    public static function sqlFormattedCurrentDateTime(string $type = "datetime")
    {
        if ($type != "date" && $type != "time" && $type != "datetime") throw new Exception("No valid type received.");
        date_default_timezone_set('America/Bogota');
        if ($type == "date") return date("Y-m-d");
        else if ($type == "time") return date("H:i:s");
        else return date("Y-m-d H:i:s");
    }
}
