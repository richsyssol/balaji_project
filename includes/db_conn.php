<?php

$config = include __DIR__ . '/config.php';

// Create connection
$conn = mysqli_connect(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Kolkata');
?>
