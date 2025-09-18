<?php
// Hide ALL errors
// error_reporting(0);
// ini_set('display_errors', 0);


// Check if a session is not already active before calling session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Set session duration (5 minutes)
$session_duration = 18000; 

// If the user is not logged in, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Set session start time if not already set
if (!isset($_SESSION['session_start'])) {
    $_SESSION['session_start'] = time();
}

// Calculate elapsed time
$elapsed_time = time() - $_SESSION['session_start'];
$remaining_time = $session_duration - $elapsed_time;

// If session duration exceeded, log out the user
if ($elapsed_time >= $session_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit();
}

// Return session time only if requested via AJAX
if (isset($_GET['fetch']) && $_GET['fetch'] == 'time') {
    echo json_encode(["remaining_time" => $remaining_time]);
    exit();
}
?>
