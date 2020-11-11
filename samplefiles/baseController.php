<?php
require_once "../vendor/autoload.php";

use MVC4PHP\Controller;

$controller = new Controller();
$controller->processAction($_GET["action"]);
