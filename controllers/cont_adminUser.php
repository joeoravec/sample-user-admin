<?php
session_start(); 
//include('../checksession.inc');

/*
|--------------------------------------------------------
| Controller for use with adminUser.php
|
| Include methods to create/update, retrieve one/all, 
| and delete users.
|--------------------------------------------------------
*/

// GET methods for local testing

///$methodType = $_GET[methodType];
//$uid = $_GET[uid];
//$userJson = $_GET[userJson];

require_once('User.php');

// parse incoming request for 
// methodType

$currentUser = new User($uid);
if ($methodType) {
	switch ($methodType) {
	    case "create":
	        $currentUser->updateUser($userJson);
	        $data = $currentUser->getJson();
	        break;
	    case "retrieve":
	        if ($uid) {
	        	$data = $currentUser->getJson();
	        } else {
	        	$data = $currentUser->getJsonList();
	        }
	        break;
	    case "destroy":
	        $data = $currentUser->destroyUser();
	        break;
	}
}
//return the data with anything else we need
echo $data;

?>