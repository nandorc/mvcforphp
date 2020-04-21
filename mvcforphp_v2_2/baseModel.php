<?php
class ModelName extends DBModel
{
    private $name = "";
    private $fields = array();
    private $pk = "";
    public function __construct()
    {
        parent::__construct(new SQLTable($this->name, $this->fields, $this->pk));
    }
}
