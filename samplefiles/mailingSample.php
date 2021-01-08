<?php
require "../vendor/autoload.php";
require "../resources/scripts/mvc4php/globals4app.php";
require "../resources/scripts/mvc4php/globals4controllers.php";

use MVC4PHP\Controller;

try {
    validatemaillogauth();
    emailcontent();
} catch (Exception $ex) {
    Controller::sendError($ex->getMessage());
}
function emailcontent()
{ ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
    </head>

    <body>
    </body>

    </html>
<?php }
