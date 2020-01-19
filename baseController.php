<?php
//Models required in the Controller
//called by the instruction
//   include_once "../models/[NombreModelo].php";
//
//Begin the session environment
session_start();
//
//Validate and load actions
//To redirect, use the instruction header like is show putting the name of the view
//    header("Location: ../[view]");
if (!isset($_GET["action"])) {
    header("Location: ../index.php");
} else {
    $action = $_GET["action"];
    //Add each action usign a if...elseif...else structure
    //if you use any Database action, use a try...catch block to consider exceptions
    
}
