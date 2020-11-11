<?php

namespace MVC4PHP;

/**
 * Class for building models based on associative arrays.
 * Each member of the associative array which builds the Model is stored internally to work as a property that can be accesed or changed just by using the property name.
 */
class Model
{
    /**
     * Stores the model data fields as an associative array.
     * @var array
     */
    private $data = array();
    public function __get($name)
    {
        return $this->data[$name];
    }
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    /**
     * @param array $data (Optional) Defines an associative array which contains the fields of the model.
     * @return Model
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }
    /**
     * Shows a model data as a JSON string.
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
    /**
     * Write on a JSON array format a list of Models
     * @param Model[] $models array of Model elements to be formatted
     * @return string Text on JSON format for the array
     */
    public static function toJSONArray(array $models)
    {
        $response = "[";
        if (sizeof($models) > 0) {
            $response .= $models[0];
            for ($i = 1; $i < sizeof($models); $i++) $response .= "," . $models[$i];
        }
        $response .= "]";
        return $response;
    }
}
