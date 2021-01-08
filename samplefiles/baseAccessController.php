<?php
require "../vendor/autoload.php";
require "../resources/scripts/mvc4php/globals4app.php";
require "../resources/scripts/mvc4php/globals4controllers.php";

use MVC4PHP\Controller;

$controller = new Controller();
try {
    $controller->processAction();
} catch (Exception $ex) {
    Controller::sendError($ex->getMessage());
}
