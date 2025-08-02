<?php
include 'session_check.php';

header('Content-Type: application/json');
include('includes/db_conn.php'); 

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Get the raw POST data
    $mv_num = $data['mv_num'] ?? null; // Get the vehicle ID

    if ($mv_num) {
        // Prepare the query to fetch vehicle details based on the ID
        $query = "SELECT user_name, vehicle_type FROM vehicle_details WHERE mv_num = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $mv_num); // Bind the vehicle ID parameter
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Return the data as a JSON response
            echo json_encode([
                'success' => true,
                'user_name' => $row['user_name'],
                'vehicle_type' => $row['vehicle_type']
            ]);
        } else {
            // If no record is found
            echo json_encode(['success' => false, 'message' => 'No vehicle details found']);
        }
    } else {
        // If the vehicle ID is invalid
        echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID']);
    }
} else {
    // Handle if the request method is not POST
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}




?>