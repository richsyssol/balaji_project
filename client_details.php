<?php
header('Content-Type: application/json');

// Database connection
include 'includes/db_conn.php';

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'Client ID not provided']));
}

$client_id = $conn->real_escape_string($_GET['id']);
$query = $conn->query("SELECT * FROM client WHERE id = '$client_id'");

if (!$query) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

if ($query->num_rows === 0) {
    die(json_encode(['error' => 'Client not found']));
}

echo json_encode($query->fetch_assoc());
$conn->close();