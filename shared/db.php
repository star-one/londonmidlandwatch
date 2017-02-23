<?php
	$host_name  = "[hostname]";
	$database   = "[database]";
	$user_name  = "[username]";
	$password   = "[password]";
	
	date_default_timezone_set ('Europe/London');

    global $connect;
	$connect = new mysqli($host_name, $user_name, $password, $database);
    if ($connect->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	}
?>