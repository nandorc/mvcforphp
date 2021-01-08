<?php

use MVC4PHP\SQLTable;

/**
 * Defines the name of the entity on relational DB.
 * @var string
 */
$name = "";
/**
 * Defines a string array which contains the name and primitive datatype for each field in the entity. 
 * Each string element must be written as a "[name]:[type]" pair in order to be set. 
 * Type must be one of supported types included DBModel::SUPPORTED_TYPES. If type is not defined it would be text as default.
 * @var string[]
 */
$fields = [];
/**
 * Defines the name of the field wich is the primary key on the DB entity.
 * @var string
 */
$pk = "";
return new SQLTable($name, $fields, $pk);
