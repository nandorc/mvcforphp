<?php
require_once "../resources/scripts/mvcforphp/mvcforphp.php";
$controller = new Controller();
$controller->processAction($_GET["action"]);
