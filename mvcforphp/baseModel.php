<?php
#region MODEL DATA (CONFIGURE ACCORDING TO DB ENTITY)
/**
 * Defines the name of the entity on relational DB.
 * @var string
 */
$name = "";
/**
 * Defines a string array which contains the name and primitive datatype for each field in the entity. 
 * Each string element must be written as a "[name]:[type]" pair in order to be set. 
 * Type could be key, text, number, date, time or datetime. If type is not defined it would be text as default.
 * @var string[]
 */
$fields = array();
/**
 * Defines the name of the field wich is the primary key on the DB entity.
 * @var string
 */
$pk = "";
#endregion
#region Returned value when required
return new SQLTable($name, $fields, $pk);
#endregion