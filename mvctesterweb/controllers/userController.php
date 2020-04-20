<?php
require_once "../resources/scripts/mvcforphp.php";
$controller = new Controller();
$controller->useModel("users");
$controller->useModel("people");
$controller->addAction("test", function () use ($controller) {
    try {
        $usersDB = Users::getAll();
        foreach ($usersDB as $user) echo "$user <br/>";
    } catch (Exception $ex) {
        $controller->sendError($ex->getMessage());
    }
});
if (true) {
    $controller->processAction($_GET["action"]);
}
