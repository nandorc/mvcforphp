<?php
//If you want to include components from the components folder, create them and add them using the include function
//don't forget to put the instruction between the begin and end tags for php code.
//    include_once "components/[componentName].php;
//
//Variables for the Template
?>
<!DOCTYPE html>
<html>

<head>
    <!-- Metadata -->
    <!-- Charset -->
    <meta charset="utf-8" />

    <!-- Page title -->
    <title></title>

    <!-- Page icon -->
    <link rel="icon" href="images/icon.png" />
    <link rel="favicon" href="images/icon.png" />

    <!-- CSS Libraries -->
    <!-- Own library -->
    <link rel="stylesheet" href="scripts/mylib.css" />
</head>

<body>
    <?= loadContent(); ?>

    <!-- JS Libraries -->
    <!-- Own library -->
    <script src="scripts/mylib.js"></script>
</body>

</html>
<?php
//Template functions
function loadContent()
{
    if (isset($GLOBALS["content"])) {
        $GLOBALS["content"]();
    }
}
