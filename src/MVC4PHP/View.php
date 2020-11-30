<?php

namespace MVC4PHP;

use Exception;

/**
 * Class for defining page Views.
 * @property array $model JSON associative array containing model data for the view.
 */
class View extends MVC
{
    private $model = "";
    public function __get($name)
    {
        if ($name != "model") throw new Exception("Trying to access to no valid property.");
        return json_decode($this->model, true);
    }
    /**
     * Creates a new View
     * @param string $model (OPTIONAL) JSON string with model information.
     * @return View
     */
    public function __construct(string $model = "")
    {
        $this->model = $model;
    }
    /**
     * Inserts a component from a file while rendering, $data is an optional associative array to send data to component.
     * @param string $componentName Name of the component to be included.
     * @param array $data (Optional) Additional data to be send to the component.
     * @return void
     * @throws Exception When component doesn't exists on components folder.
     */
    public static function insertComponent(string $componentName, array $data = array())
    {
        $path = "../views/shared/components/" . $componentName . ".php";
        if (!file_exists($path)) throw new Exception("Component doesn't exists in components folder ($path).");
        require $path;
    }
    /**
     * Render and show the page. $content is an anonymous functios containing additional content for the page. $data is an optional associative array to send data to the page template.
     * @param function $content Delegate function with additional content for the page.
     * @param array $data (Optional) Additional data to be send to the page.
     * @return void
     */
    public static function render($content = null, array $data = array())
    {
        if ($content == null) $content = function () {
            echo "No content defined for view";
        };
        require_once "../views/shared/template.php";
    }
    /**
     * Validate and modify vars assigned to manage info and error messages from de page.
     * @return array Associative array containing "info" and "error" indexes for messages.
     */
    public static function validateMessages()
    {
        $messages = ["info" => "", "error" => ""];
        if (session_status() != PHP_SESSION_ACTIVE) session_start();
        if (isset($_GET["errormsg"]) || isset($_GET["infomsg"])) {
            $uri = $_SERVER["REQUEST_URI"];
            $query = self::extractQuery($uri);
            $view = self::getViewName($uri);
            if (isset($_GET["errormsg"])) self::moveGetToSession("errormsg", $query);
            if (isset($_GET["infomsg"])) self::moveGetToSession("infomsg", $query);
            $dir = ($query == "") ? $view : $view . "?" . $query;
            header("Location: $dir");
        } else {
            if (isset($_SESSION["errormsg"])) self::moveSessionToVar("errormsg", $messages["error"]);
            if (isset($_SESSION["infomsg"])) self::moveSessionToVar("infomsg", $messages["info"]);
        }
        return $messages;
    }
    /**
     * Modify $uri removing query string and returning it as new variable.
     * @param string $uri Assigned variable for requested url
     * @return string Query string from requested url
     */
    private static function extractQuery(string &$uri)
    {
        $uriquery = explode("?", $uri);
        $uri = $uriquery[0];
        return (count($uriquery) == 2) ? $uriquery[1] : "";
    }
    /**
     * Formats $uri to return the view name
     * @param string $uri URL to be check
     * @return string View name
     */
    private static function getViewName(string $uri)
    {
        $viewdir = explode("/", $uri);
        return $viewdir[count($viewdir) - 1];
    }
    /**
     * Takes a variable in $_GET and move it to $_SESSION and removes it from assigned $query
     * @param string $name Variable name to be moved
     * @param string $query Assigned query string to be altered
     * @return void
     */
    private static function moveGetToSession(string $name, string &$query)
    {
        $_SESSION[$name] = $_GET[$name];
        $pos = strpos($query, $name);
        $query = ($pos == 0) ? "" : substr($query, 0, $pos - 1);
    }
    /**
     * Takes a variable in $_SESSION and move it to an assigned $var and unset $_SESSION variable
     * @param string $name Variable name to be moved
     * @param mixed $var Assigned variable to receive new value.
     * @return void
     */
    private static function moveSessionToVar(string $name, &$var)
    {
        $var = $_SESSION[$name];
        unset($_SESSION[$name]);
    }
}
