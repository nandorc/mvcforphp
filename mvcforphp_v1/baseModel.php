<?php
//Required files
include_once "mvcforphp.php";
//
//Class definition
//Change ModelName according with your database and modify values
//    tableName is the name of the table in the database
//    fields is an array with names of fields in the table
//    primaryKey is the name of the field which is the PK of the table
//    foreignKeys is an array with the name of the fields which are FK on the table
class Element extends Database
{
    //Constants
    private static $tableName = "";
    private static $fields = array();
    private static $fieldTypes = array();
    private static $primaryKey = "";
    //
    //Functions used in model depending of needs, add your own functions here
    //Don't forget to construct the main variable to specify table information as is shown
    //    $table = new Table(self::$tableName, self::$fields, self::$fieldTypes, self::$primaryKey);
    public static function getAllElements()
    {
        try {
            $table = new Table(self::$tableName, self::$fields, self::$fieldTypes, self::$primaryKey);
            return self::getAll($table);
        } catch (Exception $ex) {
            throw new Exception("Something fail getting elements: " . $ex->getMessage());
        }
    }
}
