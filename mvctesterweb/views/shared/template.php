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
    <meta name="description" content="Testing page for mvcforphp library" />
    <!-- KeyWords -->
    <meta name="keywords" content="HTML,CSS,JS,PHP,MySQL" />
    <!-- Author -->
    <meta name="author" content="Daniel F. Rivera C." />

    <!-- Page title -->
    <title>MVC Tester</title>

    <!-- Page icon -->
    <link rel="icon" href="images/icon.ico" />

    <!-- CSS Libraries -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <!-- Own library -->
    <link rel="stylesheet" href="scripts/mvctester.css" />
</head>

<body class="bg-dark text-light">
    <!-- Header for the Web Page -->
    <header class="row no-gutters">
        <div class="d-none d-lg-block col-lg-3 p-4">
            <div class="d-inline-block align-middle">
                <h1>MVC Tester</h1>
                <p>Testing HTML, CSS and JS functionalities and mvcforphp library.</p>
            </div>
        </div>
        <div class="col-md-8 col-lg-6">
            <img alt="Image for header" src="images/header.jpeg" class="img-fluid mx-auto d-block mvcheaderimage" />
        </div>
        <div class="col-md-4 col-lg-3 p-4">
            <h1>MVC Tester</h1>
            <p>Testing HTML, CSS and JS functionalities and mvcforphp library.</p>
        </div>
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <!-- Own library -->
    <script src="scripts/mvctester.js"></script>
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
