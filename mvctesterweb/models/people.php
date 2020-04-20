<?php
//Required files
include_once "../resources/scripts/mvcforphp.php";
//
//Class definition
//Change ModelName according with your database and modify values
//    tableName is the name of the table in the database
//    fields is an array with names of fields in the table
//    fieldTypes is an arrat with types for each field, these could be key, text, number, date or datetime
//    primaryKey is the name of the field which is the PK of the table
class People extends Database
{
    //Constants
    private static $tableName = "people";
    private static $fields = array("persondni", "personname", "personbirthdate", "personheight", "personweight", "username");
    private static $fieldTypes = array("key", "text", "date", "number", "number", "key");
    private static $primaryKey = "persondni";
    protected static function defineTable()
    {
        self::$table = new Table(self::$tableName, self::$fields, self::$fieldTypes, self::$primaryKey);
    }
}
