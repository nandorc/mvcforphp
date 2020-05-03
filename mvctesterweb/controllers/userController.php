<?php
require_once "../resources/scripts/mvcforphp.php";
$controller = new Controller();
$controller->useModel("users");
$controller->useModel("persons");
$users = new Users();
$controller->addAction("test", function () use ($controller, $users) {
    try {
        $usersDB = $users->getAll();
        foreach ($usersDB as $user) echo "$user <br/>";
        echo $users->lastIndex;
    } catch (Exception $ex) {
        $controller->sendError($ex->getMessage());
    }
});
if (true) {
    $controller->processAction($_GET["action"]);
}
