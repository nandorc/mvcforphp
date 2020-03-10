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
    <!-- ViewPort -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Description -->
    <meta name="description" content="Description for the web page content" />
    <!-- KeyWords -->
    <meta name="keywords" content="Keywords,For,The,Web,Page,Content" />
    <!-- Author -->
    <meta name="author" content="Name of the Author" />

    <!-- Page title -->
    <title>Page Title</title>

    <!-- Page icon -->
    <link rel="icon" href="[iconPath]" />

    <!-- CSS Libraries -->
    <!-- Own library -->
    <link rel="stylesheet" href="[ownCSSLibraryPath]" />
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
        <!-- You can make any additional distribution for the page content here inside this main tag -->
        <!-- Don't forget to put the loadContent() function from PHP code anywhere you want the variable content to appear -->
        <?= loadContent(); ?>
    </main>

    <!-- Footer for the Web Page -->
    <footer>
        Footer definition and content goes here...
    </footer>

    <!-- JS Libraries -->
    <!-- Own library -->
    <script src="[ownJSLibraryPath]"></script>
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
