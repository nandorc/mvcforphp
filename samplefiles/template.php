<?php

use MVC4PHP\View;

$title = isset($data["title"]) ? $data["title"] : "";
$pagetitle = (TEMPLATE_DEFAULT_TITLE == "") ? $title : (($title == "") ? TEMPLATE_DEFAULT_TITLE : TEMPLATE_DEFAULT_TITLE . " - " . $title);
$css = isset($data["css"]) ? $data["css"] : null;
$js = isset($data["js"]) ? $data["js"] : null;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= TEMPLATE_DESCRIPTION; ?>">
    <meta name="keywords" content="<?= TEMPLATE_KEYWORDS; ?>">
    <meta name="author" content="<?= TEMPLATE_AUTHOR; ?>">
    <title><?= $pagetitle; ?></title>
    <link rel="icon" href="<?= TEMPLATE_ICON; ?>">
    <?= View::includeexternals(TEMPLATE_CSS, "style"); ?>
    <?= View::includeexternals($css, "style"); ?>
</head>

<body>
    <?= $content(); ?>
    <?= View::includeexternals(TEMPLATE_JS, "script"); ?>
    <?= View::includeexternals($js, "script"); ?>
</body>

</html>