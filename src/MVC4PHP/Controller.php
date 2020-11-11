<?php

namespace MVC4PHP;

use Exception;

/**
 * Class for defining site Controllers.
 */
class Controller extends MVC
{
    /**
     * Associative array containig delegate functions for actions.
     * @var array
     */
    private $actions = array();
    /**
     * Send a response message
     * @param string $message Message to be sent.
     * @return void
     */
    public static function sendResponse(string $message)
    {
        echo $message;
    }
    /**
     * Send an error message
     * @param string $message Message to be sent.
     * @return void
     */
    public static function sendError(string $message)
    {
        header($_SERVER["SERVER_PROTOCOL"] . ' 400 Bad Request: Error: ' . $message);
    }
    /**
     * Create a new action for the controller. $name is the name of the action and $action is an anonymous functions with the action content.
     * @param string $name Name to identify the action.
     * @param function $action Delegate function for the action.
     * @return void
     */
    public function addAction(string $name, $action)
    {
        $this->actions[$name] = $action;
    }
    /**
     * Process action from the action array.
     * @param string $actionName Action to be processed.
     * @return void
     * @throws Exception If no valid action sent to be processed.
     */
    public function processAction(string $actionName)
    {
        $actionsCount = sizeof($this->actions);
        if ($actionsCount == 0) $this->redir("index.php");
        if (!array_key_exists($actionName, $this->actions)) throw new Exception("No action or no valid action sent");
        $this->actions[$actionName]();
    }
    /**
     * Include model files to use on code based on $modelName.
     * @param string $modelName Name of the model to be included.
     * @return DBModel Containing the object model to make operations.
     * @throws Exception When model doesn't exists on models folder.
     */
    public static function useModel(string $modelName)
    {
        $path = "../models/" . $modelName . ".php";
        if (!file_exists($path)) throw new Exception("Model doesn't exists in models folder ($path).");
        $model = require_once $path;
        return new DBModel($model);
    }
    /**
     * Verify if $dataIndexes exists as indexes on superglobal $_POST and returns variables on associative array.
     * @param string[] $dataIndexes String array containing names of indexes to be check on POST.
     * @return Model Containing data from superglobal POST.
     * @throws Exception When index not found on POST.
     */
    public static function checkPOSTData(array $dataIndexes)
    {
        $data = array();
        foreach ($dataIndexes as $dataIndex) {
            if (!isset($_POST[$dataIndex])) throw new Exception("No valid data received on POST.");
            $data[$dataIndex] = $_POST[$dataIndex];
        }
        return new Model($data);
    }
}
