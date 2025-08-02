<?php
include 'includes/db_conn.php';
include 'session_check.php';

if (isset($_POST['query'])) {
    $query = $_POST['query'];

    // Fetch matching client names from the database using LIKE for partial matches
    $stmt = $conn->prepare("SELECT client_name FROM lic_entries WHERE client_name LIKE ? LIMIT 10");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['client_name']) . '">' . htmlspecialchars($row['client_name']) . '</option>';
        }
    } else {
        echo ''; // No matching names found
    }
}
?>
