<?php
$title = isset($data["title"]) ? $data["title"] : "";
$css = isset($data["css"]) ? $data["css"] : array();
$js = isset($data["js"]) ? $data["js"] : array();
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
    <?php if (count($css) > 0) foreach ($css as $c) { ?>
        <link rel="stylesheet" type="text/css" href="<?= $c; ?>"> <?php } ?>
</head>

<body>
    <?= $content(); ?>
    <?php if (count($js) > 0) foreach ($js as $j) { ?>
        <script src="<?= $j; ?>"></script> <?php } ?>
</body>

</html>