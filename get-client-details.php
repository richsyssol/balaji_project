<?php
include 'session_check.php';
include 'includes/db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    $stmt = $conn->prepare("SELECT client_name, contact, form_status FROM gic_entries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(null);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(null);
}
?>