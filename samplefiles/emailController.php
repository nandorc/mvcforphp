<?php
require "../vendor/autoload.php";
require "../resources/scripts/mvc4php/globals4app.php";
require "../resources/scripts/mvc4php/globals4controllers.php";

use MVC4PHP\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

const DEBUG_MODE = SMTP::DEBUG_OFF;

$controller = new Controller();
$controller->addAction("send", function () {
    $mailer = setupmailer();
    $data = getmaildata();
    $mailer->setFrom(GMAIL["email"], GMAIL["name"]);
    foreach ($data["receivers"] as $receiver)
        $mailer->addAddress($receiver["email"], $receiver["name"]);
    $mailer->Subject = $data["subject"];
    $mailer->msgHTML($data["msg"]);
    if (!$mailer->send()) throw new Exception($mailer->ErrorInfo);
    Controller::sendResponse(json_encode(["status" => "success"]));
});
try {
    $controller->processAction();
} catch (Exception $ex) {
    $controller->sendResponse(json_encode(["status" => "error", "message" => $ex->getMessage()]));
}
function setupmailer()
{
    $mailer = new PHPMailer();
    $mailer->isSMTP();
    $mailer->setLanguage("es");
    $mailer->CharSet = "utf-8";
    $mailer->SMTPDebug = DEBUG_MODE;
    $mailer->Host = "smtp.gmail.com";
    $mailer->Port = 465;
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mailer->SMTPAuth = true;
    $mailer->Username = GMAIL["email"];
    $mailer->Password = GMAIL["pwd"];
    return $mailer;
}
function getmaildata()
{
    $subject = validatevaronsession("subject", "Subject", "string");
    $msg = validatevaronsession("msg", "Message", "string");
    $receivers = validatevaronsession("receivers", "Receivers", "array");
    unset($_SESSION[APP_NAME]["mailer"]);
    foreach ($receivers as $r) validatereceiver($r);
    return ["subject" => $subject, "msg" => $msg, "receivers" => $receivers];
}
function validatevaronsession(string $index, string $name, string $type, bool $empty = false)
{
    if (!isset($_SESSION[APP_NAME]["mailer"][$index]))
        throw new Exception("$name must be set on [APP_NAME][\"mailer\"][\"$index\"] index on SESSION variable");
    validatevartype($_SESSION[APP_NAME]["mailer"][$index], $name, $type, $empty);
    return $_SESSION[APP_NAME]["mailer"][$index];
}
function validatevartype($var, string $name, string $type, bool $empty = false)
{
    if (gettype($var) != $type) throw new Exception("$name must be of $type type");
    else if (!$empty && $type == "string" && $var == "") throw new Exception("$name can't be an empty string");
    else if (!$empty && $type == "array" && count($var) <= 0) throw new Exception("$name has no elements");
}
function validatereceiver($receiver)
{
    validatevartype($receiver, "Email Receiver", "array");
    validatereceiverindex($receiver, "name", "Name", "string");
    validatereceiverindex($receiver, "email", "Email", "string");
}
function validatereceiverindex($receiver, string $index, string $name, string $type)
{
    if (!isset($receiver[$index]))
        throw new Exception("$name must be set on [\"$index\"] index on each email receiver array element");
    validatevartype($receiver[$index], $name, $type);
}
