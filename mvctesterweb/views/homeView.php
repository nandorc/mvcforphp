<?php
require_once "../resources/scripts/mvcforphp.php";
$view = new View();
$view->useModel("users");
$users = new Users();
if (true) {
    $view->render(function () use ($users) {
        $usersDB = $users->getAll();
        foreach ($usersDB as $userDB) echo $userDB . "<br/>";
    });
}
