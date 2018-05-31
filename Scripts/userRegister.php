<?php

require "db_connect.php";
require "userManager.php";

$user["firstname"] = $_POST["firstname"];
$user["lastname"] = $_POST["lastname"];
$user["username"] = $_POST["username"];
$user["password"] = $_POST["password"];
$user["email"] = $_POST["email"];

$userManager = new userManager(connexionDb());
$result = $userManager->addUser($user);
echo json_encode($result);

?>