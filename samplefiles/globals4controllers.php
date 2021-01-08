<?php

use MVC4PHP\DBModel;
use MVC4PHP\Controller;

const GMAIL = [
    "name" => "",
    "email" => "",
    "pwd" => ""
];
const HTTP = [
    "env" => "test",
    "test" => "",
    "prod" => "",
];
function getuser()
{
    if (!isset($_SESSION[APP_NAME]))
        throw new Exception("No session on app initialized");
    if (!isset($_SESSION[APP_NAME]["user"]))
        throw new Exception("No user found");
    return $_SESSION[APP_NAME]["user"];
}
function getcurrentdate()
{
    date_default_timezone_set('America/Bogota');
    return date("Y-m-d H:i:s");
}
function generateuricod()
{
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, 10);
}
function getmaillogauth(DBModel $model)
{
    $cod = generateuricod();
    $auth = $model->get($cod);
    if ($auth == null) return $cod;
    else return getmaillogauth($model);
}
function validatemaillogauth()
{
    $auth = isset($_GET["auth"]) ? $_GET["auth"] : "";
    if ($auth == "") throw new Exception();
    $_maillogs = Controller::useModel("maillogs");
    $log = $_maillogs->get($auth);
    if ($log == null) throw new Exception();
    $curr = strtotime(getcurrentdate());
    $expdt = strtotime($log->expdt);
    if ($curr > $expdt) {
        $_maillogs->delete($auth);
        throw new Exception();
    }
}
function requestmailcontent(string $request, array $data = [])
{
    $_maillogs = Controller::useModel("maillogs");
    $auth = getmaillogauth($_maillogs);
    $crtdt = getcurrentdate();
    $expdt = date("Y-m-d H:i:s", strtotime($crtdt) + (60 * 10));
    $_maillogs->add([$auth, $crtdt, $expdt, $request]);
    $content = @file_get_contents(HTTP[HTTP["env"]] . "$request?auth=$auth" . buildextradata($data));
    $_maillogs->delete($auth);
    if ($content === false) throw new Exception("No se autorizó el envío del email");
    return $content;
}
function buildextradata(array $data = [])
{
    $result = "";
    if (count($data) > 0) {
        $keys = array_keys($data);
        foreach ($keys as $k) $result .= "&$k=" . $data[$k];
    }
    return $result;
}
