<?php
class ModelName extends DBModel
{
    private $name = "";
    private $fields = array();
    private $pk = "";
    public function __construct(int $level = 1)
    {
        parent::__construct(new SQLTable($this->name, $this->fields, $this->pk), $level);
    }
}
