<?php

use MVC4PHP\View;

$title = isset($data["title"]) ? $data["title"] : "";
$css = isset($data["css"]) ? $data["css"] : null;
$js = isset($data["js"]) ? $data["js"] : null;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <title><?= $title; ?></title>
    <link rel="icon" href="">
    <?= View::includeexternals($css, "style"); ?>
</head>

<body>
    <?= $content(); ?>
    <?= View::includeexternals($js, "script"); ?>
</body>

</html>