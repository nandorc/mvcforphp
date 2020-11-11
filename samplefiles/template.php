<?php

use MVC4PHP\View;
//Variables for the Template
$title = isset($data["title"]) ? $data["title"] : "";
$errormsg = isset($data["errormsg"]) ? $data["errormsg"] : "";
$infomsg = isset($data["infomsg"]) ? $data["infomsg"] : "";
View::validateMessages($errormsg, $infomsg);
?>
<!DOCTYPE html>
<html>

<head>
    <!-- Metadata -->
    <!-- Charset -->
    <meta charset="utf-8" />
    <!-- ViewPort -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Description -->
    <meta name="description" content="" />
    <!-- KeyWords -->
    <meta name="keywords" content="" />
    <!-- Author -->
    <meta name="author" content="" />

    <!-- Page title -->
    <title><?= $title; ?></title>

    <!-- Page icon -->
    <!--<link rel="icon" href="" />-->

    <!-- CSS Libraries -->
    <!-- Own library -->
    <!--<link rel="stylesheet" href="" />-->
</head>

<body>
    <main>
        <?php if ($infomsg != "") echo $infomsg; ?>
        <?php if ($errormsg != "") echo $errormsg; ?>
        <?= $content(); ?>
    </main>

    <!-- JS Libraries -->
    <!-- Own library -->
    <!--<script src=""></script>-->
</body>

</html>
<?php
//Template functions
