<?php

namespace MVC4PHP;

use Exception;

/**
 * Defines basic structure for SQL Tables based on Database entities.
 * @property string $name Name of the entity on relational DB.
 * @property string $fieldslist Comma separated string with field names
 * @property string[] $fieldsarray String array which contains the name for each field in the entity.
 * @property array $fieldsdata Associative array containig fields name-type data. Names as keys and types as values.
 * @property string $pk Name of the field wich is the prior primary key on the DB entity.
 */
class SQLTable
{
    private $name = "";
    private $fields = array();
    private $pk = "";
    public function __get($name)
    {
        if ($name != "name" && $name != "fieldslist" && $name != "fieldsarray" && $name != "fieldsdata" && $name != "pk") throw new Exception("Trying to access to an unknown property");
        else if ($name == "fieldslist") {
            $list = "";
            $count = count($this->fields);
            for ($i = 0; $i < $count; $i++)
                if ($i == 0) $list .= DBModel::cleanInputValue($this->fields[0][0]);
                else $list .= "," . DBModel::cleanInputValue($this->fields[$i][0]);
            return $list;
        } else if ($name == "fieldsarray") {
            $array = array();
            foreach ($this->fields as $field)
                $array[] = $field[0];
            return $array;
        } else if ($name == "fieldsdata") {
            $array = array();
            foreach ($this->fields as $field)
                $array[$field[0]] = $field[1];
            return $array;
        } else if ($name == "name") return $this->name;
        else return $this->pk;
    }
    /**
     * @param string $name Defines the name of the entity on relational DB.
     * @param string[] $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair in order to be set. Supported types could be check with array_keys(DBModel::SUPPORTED_TYPES) function. If type is not defined it would be the first supported type as default.
     * @param string $pk Defines the name of the field wich is the primary key on the DB entity.
     * @return SQLTable
     * @throws Exception If table $name is empty
     * @throws Exception If $fields array has no elements.
     * @throws Exception If $fields array has no supported data types.
     * @throws Exception If $pk is an empty value.
     */
    public function __construct(string $name, array $fields, string $pk)
    {
        if ($name == "") throw new Exception("Name can't be empty.");
        if (sizeof($fields) == 0) throw new Exception("A table must have at least one field");
        if ($pk == "") throw new Exception("PK can't be empty");
        $this->name = $name;
        $this->fields = array();
        foreach ($fields as $field) {
            $fieldPair = explode(":", $field);
            if (!isset($fieldPair[1]) || $fieldPair[1] == "") $fieldPair[1] = array_keys(DBModel::SUPPORTED_TYPES)[0];
            else if (!isset(DBModel::SUPPORTED_TYPES[$fieldPair[1]])) throw new Exception("Data type " . $fieldPair[1] . " not supported.");
            $this->fields[] = $fieldPair;
        }
        $this->pk = $pk;
    }
}
