<?php
//Variables for the Template
if (!isset($data["title"]))
    $data["title"] = "";
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
    <title><?= $data["title"]; ?></title>

    <!-- Page icon -->
    <!-- <link rel="icon" href="[iconPath]" /> -->

    <!-- CSS Libraries -->
    <!-- Own library -->
    <!-- <link rel="stylesheet" href="[jsLibraryPath]" /> -->
</head>

<body>
    <!-- Header for the Web Page -->
    <header>
        Header content here...
    </header>

    <!-- Navbar for the Web Page -->
    <nav>
        Navigation bar definition here...
    </nav>

    <!-- Main content of the page -->
    <main>
        <!-- Keep next line in order to show extra content defined for page -->
        <?= $content(); ?>
    </main>

    <!-- Footer for the Web Page -->
    <footer>
        Footer definition and content goes here...
    </footer>

    <!-- JS Libraries -->
    <!-- Own library -->
    <!-- <script src="[ownJSLibraryPath]"></script> -->
</body>

</html>
<?php
//Template functions
