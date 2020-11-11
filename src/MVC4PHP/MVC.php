<?php

namespace MVC4PHP;
use Exception;
/**
 * Class for defining page MVC components.
 */
class MVC
{
    /**
     * Redirect to the specified $dir. Optional $data could be sent.
     * @param string $dir Direction to be redirected to. If no http:// or https:// directive sent, it will redirect to a local view.
     * @param array $data (Optional) Data to be sent through GET method. $data must be an associative array.
     * @return void
     * @throws Exception If file:/// directive is send as $dir
     */
    public function redir(string $dir, array $data = array())
    {
        if (substr($dir, 0, 8) == "file:///") throw new Exception("Can't redir to a file.");
        if (get_class($this) == "Controller" && substr($dir, 0, 7) != "http://" && substr($dir, 0, 8) != "https://") $dir = "../$dir";
        $dcount = sizeof($data);
        if ($dcount > 0) {
            $keys = array_keys($data);
            $dir .= "?" . $keys[0] . "=" . $data[$keys[0]];
            for ($i = 1; $i < $dcount; $i++) $dir .= "&" . $keys[$i] . "=" . $data[$keys[$i]];
        }
        header("Location: $dir");
    }
    /**
     * Show on screen variables from server.
     * @return void
     */
    public static function serverVariables()
    {
        $keys = array_keys($_SERVER);
        foreach ($keys as $key) echo $key . " - " . $_SERVER[$key] . "<br/>";
    }
}
