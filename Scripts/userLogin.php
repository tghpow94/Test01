<?php

require "db_connect.php";
require "userManager.php";

$email = $_GET["email"];
$password = $_GET["password"];

$userManager = new userManager(connexionDb());
$result = $userManager->getUserConnect($email, $password);
echo json_encode($result);

?>