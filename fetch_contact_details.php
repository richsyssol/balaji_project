<?php
include 'session_check.php';
include 'includes/db_conn.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"])) {
    $username = $conn->real_escape_string($_POST["username"]);

    // Query to fetch contact number
    $query = "SELECT contact FROM assign_to WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($contact);
    $stmt->fetch();

    echo $contact ? htmlspecialchars($contact) : "";

    $stmt->close();
    $conn->close();
}
?>
