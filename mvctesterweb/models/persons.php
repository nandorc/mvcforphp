<?php
class Persons extends DBModel
{
    private $name = "persons";
    private $fields = array("dni:key", "name", "birthdate:date", "height:number", "weight:number", "user:key");
    private $pk = "dni";
    public function __construct()
    {
        parent::__construct(new SQLTable($this->name, $this->fields, $this->pk));
    }
}
