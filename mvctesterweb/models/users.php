<?php
class Users extends DBModel
{
    private $name = "users";
    private $fields = array("uid:key", "pwd");
    private $pk = "uid";
    public function __construct()
    {
        parent::__construct(new SQLTable($this->name, $this->fields, $this->pk));
    }
}
