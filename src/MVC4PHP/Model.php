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
    private $data = [];
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
        $keys = array_keys($data);
        foreach ($keys as $k) $data[$k] = htmlspecialchars_decode($data[$k], ENT_QUOTES);
        $this->data = $data;
    }
    public function __toString()
    {
        return $this->toJSON();
    }
    /**
     * Shows a model data as a JSON string.
     * @return string Text as JSON formatted object or "ERROR" message if failure
     */
    public function toJSON()
    {
        $json = json_encode($this->data);
        if ($json === false) return "ERROR";
        return $json;
    }
    /**
     * Shows a model data as an associative array.
     * @return array Associative array containing model data
     */
    public function toArray()
    {
        return $this->data;
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
    /**
     * Returns an array of associative arrays of Models
     * @param Model[] $models array of Model elements to be parsed
     * @return array Array of associative arrays
     */
    public static function toModelsArray(array $models)
    {
        $result = [];
        foreach ($models as $m) $result[] = $m->toArray();
        return $result;
    }
}
