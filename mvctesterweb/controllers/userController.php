<?php
//Models required in the Controller
//called by the instruction
//   include_once "../models/[NombreModelo].php";
include_once "../models/users.php";
include_once "../models/people.php";
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
    //Add each action as cases for the switch conditional
    //if you use any Database action, use a try...catch block to consider exceptions
    try {
        switch ($action) {
            case "test":
                $usersDB = Users::getAll();
                foreach ($usersDB as $user)
                    echo "$user <br/>";
                break;
            default:
                throw new Exception("No action or no valid action sent");
        }
    } catch (Exception $ex) {
        //Display or send error message according to your needs
        echo "Error: " . $ex->getMessage();
    }
}
