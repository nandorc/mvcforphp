<?php

namespace MVC4PHP;

use Exception;

/**
 * Class for defining special SQL clauses.
 * @property string $fields Field list formatted as a comma separated list with field names.
 * @property string $wherePairs String formatted as SQL WHERE clause content.
 * @property string $orderPairs String formatted as SQL ORDER BY clause content.
 * @property string $limit String formatted as SQL LIMIT clause.
 */
class SQLClauses
{
    private $fields = [];
    private $wherePairs = [];
    private $orderPairs = [];
    private $limit = 0;
    public function __get($name)
    {
        if ($name != "fields" && $name != "wherePairs" && $name != "orderPairs" && $name != "limit")
            throw new Exception("Trying to access to an unknown property");
        $clause = "";
        if ($name == "fields") {
            $count = sizeof($this->fields);
            if ($count > 0) {
                $clause .= DBModel::cleanInputValue($this->fields[0]);
                for ($i = 1; $i < $count; $i++) $clause .= "," . DBModel::cleanInputValue($this->fields[$i]);
            }
        } else if ($name == "wherePairs") {
            $count = count($this->wherePairs);
            for ($i =  0; $i < $count; $i++) {
                if ($i != 0)
                    $clause .= " " . DBModel::cleanInputValue($this->wherePairs[$i]["conector"]) . " ";
                if ($this->wherePairs[$i]["prepend"]) $clause .= "(";
                $clause .= DBModel::cleanInputValue($this->wherePairs[$i]["name"]);
                $clause .= " " . $this->validateSymbol($this->wherePairs[$i]["type"]) . " ";
                $clause .= "'" . DBModel::cleanInputValue($this->wherePairs[$i]["value"]) . "'";
                if ($this->wherePairs[$i]["append"]) $clause .= ")";
            }
        } else if ($name == "orderPairs") {
            $count = sizeof($this->orderPairs);
            if ($count > 0) {
                $clause .= DBModel::cleanInputValue($this->orderPairs[0][0]) . " " . DBModel::cleanInputValue($this->orderPairs[0][1]);
                for ($i = 1; $i < $count; $i++)
                    $clause .= ", " . DBModel::cleanInputValue($this->orderPairs[$i][0]) . " " . DBModel::cleanInputValue($this->orderPairs[$i][1]);
            }
        } else if ($name == "limit") {
            if ($this->limit > 0)
                $clause .= "limit " . DBModel::cleanInputValue($this->limit);
        }
        return $clause;
    }
    public function __set($name, $value)
    {
        if ($name != "fields" && $name != "wherePairs" && $name != "orderPairs" && $name != "limit")
            throw new Exception("Trying to access to an unknown property");
        else if ($name == "fields")
            $this->fields = $this->validateFields($value);
        else if ($name == "wherePairs")
            $this->wherePairs = $this->validateWherePairs($value);
        else if ($name == "orderPairs")
            $this->orderPairs = $this->validateOrderPairs($value);
        else if ($name == "limit")
            $this->limit = $this->validateLimit($value);
    }
    /**
     * Creates a new SQLClauses.
     * @param array $clauses Associative array which contains info for optional fields, wherePairs or orderPairs. Each one defined if needed as a key for the array.
     * 
     * string[] "fields" => Defines a field list.
     * 
     * array "wherePairs" => Defines a WHERE clause content. 
     * Each element must be an associative array which can contain "name","value", "type",
     * "prepend", "append" and "conector" indexes. If "type" is not defined, it would 
     * be "=" by default. If "prepend" or "append" are not defined, would be false by 
     * default. If "conector" is not defined, it would be "and" by default.
     * 
     * string[] "orderPairs" => Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
     * 
     * int "limit" => Defines limit value.
     * 
     * @return SQLClauses
     * @throws Exception If no valid values specified on $clauses.
     */
    public function __construct(array $clauses)
    {
        if (isset($clauses["fields"]))
            $this->fields = $this->validateFields($clauses["fields"]);
        if (isset($clauses["wherePairs"]))
            $this->wherePairs = $this->validateWherePairs($clauses["wherePairs"]);
        if (isset($clauses["orderPairs"]))
            $this->orderPairs = $this->validateOrderPairs($clauses["orderPairs"]);
        if (isset($clauses["limit"]))
            $this->limit = $this->validateLimit($clauses["limit"]);
    }
    /**
     * Validate symbols for where clauses
     * @param string $symbol Symbol to be checked
     * @return string
     */
    private function validateSymbol(string $symbol)
    {
        if ($symbol != "=" && $symbol != "<=>" && $symbol != "<>" && $symbol != "!=" && $symbol != ">" && $symbol != ">=" && $symbol != "<" && $symbol != "<=")
            $symbol = DBModel::cleanInputValue($symbol);
        return $symbol;
    }
    /**
     * Validate data received to construct Fields
     * @param array $Fields Array data to be check
     * @return array
     * @throws Exception If something fails while checking
     */
    private function validateFields(array $fields)
    {
        $this->checkStringArray($fields);
        return $fields;
    }
    /**
     * Validate data received to construct WherePairs
     * @param array $wherePairs Array data to be check
     * @return array
     * @throws Exception If something fails while checking
     */
    private function validateWherePairs(array $wherePairs)
    {
        $c = count($wherePairs);
        for ($i = 0; $i < $c; $i++) {
            if (gettype($wherePairs[$i]) != "array")
                throw new Exception("Each wherePair must be an array.");
            if (!isset($wherePairs[$i]["name"]))
                throw new Exception("No name defined on wherePair");
            else if (gettype($wherePairs[$i]["name"]) != "string")
                throw new Exception("name must be defined as a string");
            if (!isset($wherePairs[$i]["value"]))
                throw new Exception("No value especified for " . $wherePairs[$i]["name"] . " where clause field");
            else if (gettype($wherePairs[$i]["value"]) != "string")
                throw new Exception("value must be defined as a string");
            if (!isset($wherePairs[$i]["type"]))
                $wherePairs[$i]["type"] = "=";
            else if (gettype($wherePairs[$i]["type"]) != "string")
                throw new Exception("Type for " . $wherePairs[$i]["name"] . " must be defined as a string");
            if (!isset($wherePairs[$i]["prepend"]))
                $wherePairs[$i]["prepend"] = false;
            else if (gettype($wherePairs[$i]["prepend"]) != "boolean")
                throw new Exception("Prepend for " . $wherePairs[$i]["name"] . " must be defined as a boolean");
            if (!isset($wherePairs[$i]["append"]))
                $wherePairs[$i]["append"] = false;
            else if (gettype($wherePairs[$i]["append"]) != "boolean")
                throw new Exception("Append for " . $wherePairs[$i]["name"] . " must be defined as a boolean");
            if (!isset($wherePairs[$i]["conector"]))
                $wherePairs[$i]["conector"] = "and";
            else if (gettype($wherePairs[$i]["conector"]) != "string")
                throw new Exception("Conector for " . $wherePairs[$i]["name"] . " must be defined as a string");
        }
        return $wherePairs;
    }
    /**
     * Validate data received to construct OrderPairs
     * @param array $orderPairs Array data to be check
     * @return array
     * @throws Exception If something fails while checking
     */
    private function validateOrderPairs(array $orderPairs)
    {
        $this->checkStringArray($orderPairs);
        $op = array();
        foreach ($orderPairs as $orderPair) {
            $opdata = explode(":", $orderPair);
            if (!isset($opdata[1]) || $opdata[1] == "") $opdata[1] = "asc";
            $op[] = $opdata;
        }
        return $op;
    }
    /**
     * Validate data received to set Limit
     * @param int $limit Limit value sent
     * @return int
     * @throws Exception If something fails while checking
     */
    private function validateLimit(int $limit)
    {
        if (gettype($limit) != "integer")
            throw new Exception("Limit must be a integer.");
        $limit = ($limit < 0) ? 0 : $limit;
        return $limit;
    }
    /**
     * Verify if all elements in the received array are strings.
     * @param array $array Array to be checked
     * @return void If all conditions are satisfied.
     * @throws Exception If $array is not an array or its elements are not strings.
     */
    private function checkStringArray(array $array)
    {
        foreach ($array as $element) if (gettype($element) != "string")
            throw new Exception("Elements in array must be strings.");
    }
}
