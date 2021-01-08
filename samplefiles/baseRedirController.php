<?php
require "../vendor/autoload.php";
require "../resources/scripts/mvc4php/globals4app.php";
require "../resources/scripts/mvc4php/globals4controllers.php";

use MVC4PHP\Controller;

$controller = new Controller("base");
try {
    $controller->processAction();
} catch (Exception $ex) {
    $controller->redir($controller->redirpoint, ["errormsg" => $ex->getMessage()]);
}
