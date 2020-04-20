<?php
//Required files
include_once "../resources/scripts/mvcforphp.php";
//
//Class definition
//Change ModelName according with your database and modify values
/**
 * @property string $tableName Defines the name of the entity on relational DB.
 * @property array<string> $fields Defines a string array which contains the name and primitive datatype for each field in the entity. Each string element must be written as a "[name]:[type]" pair. Type could be key, text, number, date or datetime. If type is not defined it would be text as default.
 * @property string $primaryKey Defines the name of the field wich is the primary key on the DB entity.
 */
class Users extends Database
{
    //Constants
    private static $tableName = "users";
    private static $fields = array("username:key", "userpwd");
    private static $primaryKey = "username";
    protected static function defineTable()
    {
        self::$table = new Table(self::$tableName, self::$fields, self::$primaryKey);
    }
}
