<?php
include 'session_check.php';

header('Content-Type: application/json');
include('includes/db_conn.php'); 


// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Get raw POST data
    $milkat_no = $data['milkat_no'] ?? null; // Get the property ID

    if ($milkat_no) {
        // Prepare the query to fetch property details based on the ID
        $query = "SELECT description, location, survey_no FROM property_details WHERE milkat_no = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $milkat_no); // Bind the property ID
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Return the data as a JSON response
            echo json_encode([
                'success' => true,
                'description' => $row['description'],
                'location' => $row['location'],
                'survey_no' => $row['survey_no']
            ]);
        } else {
            // If no record is found
            echo json_encode(['success' => false, 'message' => 'Property details not found']);
        }
    } else {
        // If the property ID is invalid
        echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    }
} else {
    // Handle if the request method is not POST
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}





?>