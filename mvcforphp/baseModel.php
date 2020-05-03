<?php
#region PERSONAL MODEL FROM DB ENTINTY (CHANGE CLASS NAME ACCORDING TO ENTITY NAME)
/**
 * Derived model from entity on database
 * @property int $lastIndex Stores the last AUTO_INCREMENT value generated on an add operation.
 */
class ModelName extends DBModel
{
    #region ATTRIBUTES (CONFIGURE ACCORDING TO DB ENTITY)
    /**
     * Defines the name of the entity on relational DB.
     * @var string
     */
    private $name = "";
    /**
     * Defines a string array which contains the name and primitive datatype for each field in the entity. 
     * Each string element must be written as a "[name]:[type]" pair in order to be set. 
     * Type could be key, text, number, date, time or datetime. If type is not defined it would be text as default.
     * @var string[]
     */
    private $fields = array();
    /**
     * Defines the name of the field wich is the primary key on the DB entity.
     * @var string
     */
    private $pk = "";
    #endregion
    #region CONSTRUCTOR (DO NOT MODIFY)
    /**
     * @param int $level (Optional) Folder level for the model, based on file folder level. 0 for root folder and 1 by default.
     * @throws Exception If $level is a negative value.
     */
    public function __construct(int $level = 1)
    {
        parent::__construct(new SQLTable($this->name, $this->fields, $this->pk), $level);
    }
    #endregion
}
#endregion
