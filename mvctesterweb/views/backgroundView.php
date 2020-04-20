<?php
//Models required in the View to show data
//are called by the instruction changing the [model]
//   include_once "../models/[model].php";
//
//Begin the session evironment
session_start();
//
//Specific variables for the page
//
//Specific content of the page
$content = function () {
    //Content variables
    //Remember to use $GLOBALS if the variable is out of the function scope
    //
?>
    <!-- Content of the page -->
    <style>
        body {
            margin: 0px;
        }

        img.background {
            position: fixed;
            width: 100%;
            height: 100%;
            left: 0px;
            top: 0px;
            z-index: -1;
        }

        img.background-grayscale {
            filter: grayscale(100%);
        }

        img.background-sepia {
            filter: sepia(100%);
        }

        img.background-inverted {
            filter: invert(100%);
        }

        img.background-transparent {
            filter: opacity(35%);
        }

        img.background-blured {
            filter: blur(5px);
        }
    </style>
    <img class="background background-blured" src="images/sample1.jpeg" alt="Imagen de fondo" />
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
    <p>Content example</p>
<?php
};
//
//Validate and load the page
//If is necessary to validate conditions, replace the true value on the if sentence and add an else block
//To redirect, use the instruction header like is shown putting the name of the view
//    header("Location: [view]");
if (true) {
    include_once 'shared/template.php';
}
//
//Extra functions
//If necessary add extra functions to simplify the code inside $content
