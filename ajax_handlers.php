<?php
include 'includes/db_conn.php';
include 'session_check.php';

// Handle client search
if (isset($_GET['search_clients'])) {
    $search = $conn->real_escape_string($_GET['search_term'] ?? '');
    
    // Search by client name, contact, or contact_alt
    $query = $conn->query("SELECT id, client_name, contact, contact_alt 
                          FROM client 
                          WHERE client_name LIKE '%$search%' 
                             OR contact LIKE '%$search%' 
                             OR contact_alt LIKE '%$search%' 
                          LIMIT 10");
    
    $suggestions = [];
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $suggestions[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($suggestions);
    exit;
}

// Handle client details
if (isset($_GET['client_details'])) {
    $client_id = $conn->real_escape_string($_GET['client_id'] ?? '');
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