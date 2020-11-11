<?php

namespace MVC4PHP;

use Exception;

/**
 * Class for defining special SQL clauses.
 * @property string $fields Field list formatted as a comma separated list with field names.
 * @property string $wherePairs String formatted as SQL WHERE clause content.
 * @property string $orderPairs String formatted as SQL ORDER BY clause content.
 */
class SQLClauses
{
    private $fields = array();
    private $wherePairs = array();
    private $orderPairs = array();
    public function __get($name)
    {
        if ($name != "fields" && $name != "wherePairs" && $name != "orderPairs") throw new Exception("Trying to access to an unknown property");
        $clause = "";
        if ($name == "fields") {
            $count = sizeof($this->fields);
            if ($count > 0) {
                $clause .= DBModel::cleanInputValue($this->fields[0]);
                for ($i = 1; $i < $count; $i++) $clause .= "," . DBModel::cleanInputValue($this->fields[$i]);
            }
        } else if ($name == "wherePairs") {
            $count = sizeof($this->wherePairs);
            if ($count > 0) {
                $symbol = $this->wherePairs[0]["type"];
                if ($symbol != "=" && $symbol != "<=>" && $symbol != "<>" && $symbol != "!=" && $symbol != ">" && $symbol != ">=" && $symbol != "<" && $symbol != "<=")
                    $symbol = DBModel::cleanInputValue($symbol);
                $clause .= DBModel::cleanInputValue($this->wherePairs[0]["name"]) . $symbol . "'" . DBModel::cleanInputValue($this->wherePairs[0]["value"]) . "'";
                for ($i =  1; $i < $count; $i++) {
                    $symbol = $this->wherePairs[$i]["type"];
                    if ($symbol != "=" && $symbol != "<=>" && $symbol != "<>" && $symbol != "!=" && $symbol != ">" && $symbol != ">=" && $symbol != "<" && $symbol != "<=")
                        $symbol = DBModel::cleanInputValue($symbol);
                    $clause .= " and " . DBModel::cleanInputValue($this->wherePairs[$i]["name"]) . $symbol . "'" . DBModel::cleanInputValue($this->wherePairs[$i]["value"]) . "'";
                }
            }
        } else if ($name == "orderPairs") {
            $count = sizeof($this->orderPairs);
            if ($count > 0) {
                $clause .= DBModel::cleanInputValue($this->orderPairs[0][0]) . " " . DBModel::cleanInputValue($this->orderPairs[0][1]);
                for ($i = 1; $i < $count; $i++)
                    $clause .= ", " . DBModel::cleanInputValue($this->orderPairs[$i][0]) . " " . DBModel::cleanInputValue($this->orderPairs[$i][1]);
            }
        }
        return $clause;
    }
    /**
     * Creates a new SQLClauses.
     * @param array $clauses Associative array which contains info for optional fields, wherePairs or orderPairs. Each one defined if needed as a key for the array.
     * 
     * string[] "fields" => Defines a field list.
     * 
     * array "wherePairs" => Defines a WHERE clause content. Each element must be an associative array which can contain "name","value" and "type" index. If "type" is not defined, it would be "=" by default.
     * 
     * string[] "orderPairs" => Defines an ORDER BY clause content. Each string element must be written as a "[name]:[value]" pair. If value is not defined, it would be asc by default.
     * 
     * @return SQLClauses
     * @throws Exception If no valid values specified on $clauses.
     */
    public function __construct(array $clauses)
    {
        if ($this->checkStringArray("fields", $clauses))
            $this->fields = $clauses["fields"];
        if (isset($clauses["wherePairs"]))
            $this->wherePairs = $this->validateWherePairs($clauses["wherePairs"]);
        if ($this->checkStringArray("orderPairs", $clauses))
            $this->orderPairs = $this->validateOrderPairs($clauses["orderPairs"]);
    }
    /**
     * Verify if a received $key in a $master associative array exists, is an array and all its elements are strings.
     * @param string $key Name of the key to be search
     * @param array $master Associative array to be checked
     * @return bool True if all conditions are satisfied and false if $key doesn't exists.
     * @throws Exception If $key is not an array or its elements are not strings.
     */
    private function checkStringArray(string $key, array $master)
    {
        if (isset($master[$key])) {
            if (gettype($master[$key]) != "array") throw new Exception("$key must be an array.");
            foreach ($master[$key] as $element) if (gettype($element) != "string") throw new Exception("Elements in $key must be strings.");
            return true;
        } else return false;
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
            if (gettype($wherePairs[$i]) != "array") throw new Exception("Each wherePair must be an array.");
            else if (!isset($wherePairs[$i]["name"])) throw new Exception("No name defined on wherePair");
            else if (gettype($wherePairs[$i]["name"]) != "string") throw new Exception("name must be defined as a string");
            else if (!isset($wherePairs[$i]["value"])) throw new Exception("No value especified for " . $wherePairs[$i]["name"] . " where clause field");
            else if (gettype($wherePairs[$i]["value"]) != "string") throw new Exception("value must be defined as a string");
            else if (!isset($wherePairs[$i]["type"])) $wherePairs[$i]["type"] = "=";
            else if (gettype($wherePairs[$i]["type"]) != "string") throw new Exception("Type for " . $wherePairs[$i]["name"] . " must be defined as a string");
        }
        return $wherePairs;
    }
    /**
     * Validate data received to construct OrderPairs
     * @param array $orderPairs Array data to be check
     * @return array
     */
    private function validateOrderPairs(array $orderPairs)
    {
        $op = array();
        foreach ($orderPairs as $orderPair) {
            $opdata = explode(":", $orderPair);
            if (!isset($opdata[1]) || $opdata[1] == "") $opdata[1] = "asc";
            $op[] = $opdata;
        }
        return $op;
    }
}
