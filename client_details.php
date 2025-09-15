<?php
include 'includes/db_conn.php';
include 'session_check.php';

if (isset($_GET['id'])) {
    $client_id = $conn->real_escape_string($_GET['id']);
    $query = $conn->query("SELECT * FROM client WHERE id = '$client_id'");
    
    if ($query && $query->num_rows > 0) {
        $client = $query->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($client);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Client not found']);
    }
    exit;
}
?>