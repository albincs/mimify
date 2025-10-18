<?php
// Error Reporting Turn On
ini_set('error_reporting', E_ALL);

// Setting up the time zone
date_default_timezone_set('Asia/Kolkata');

// Host Name
$dbhost = 'srv834.hstgr.io';

// Database Name
$dbname = 'u352242787_mimifydb';

// Database Username
$dbuser = 'u352242787_mimifyadmin';

// Database Password
$dbpass = 'M1m1fy@@dm!n';

// Defining base url
define("BASE_URL", "https://mimify.in/");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $exception ) {
	echo "Connection error :" . $exception->getMessage();
}