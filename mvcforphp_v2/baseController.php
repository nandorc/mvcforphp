<?php
//PHP error checking
error_reporting(E_ALL);
ini_set('display_errors', '1');
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
    //Add each action as cases for the switch conditional
    //Use sendResponse and sendError functions to send formatted messages to the page
    try {
        switch ($action) {
            default:
                throw new Exception("No action or no valid action sent");
        }
    } catch (Exception $ex) {
        //Display or send error message according to your needs
        sendError($ex->getMessage());
    }
}
//Functions for controllers
/**
 * Send a response message
 * @param string $message Message to be sent to the page
 * @return string
 */
function sendResponse(string $message)
{
    echo "OK: $message";
}
/**
 * Send an error message
 * @param string $message Error message to be sent to the page
 * @return string
 */
function sendError(string $message)
{
    echo "Error: $message";
}
