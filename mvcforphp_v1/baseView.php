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
