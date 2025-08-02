<?php
include 'includes/db_conn.php';

if (isset($_GET['city'])) {
    $city = trim($_GET['city']);
    $city = $conn->real_escape_string($city);
    $sql = "SELECT pincode FROM cities WHERE city = '$city' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo htmlspecialchars($row['pincode']);
    } else {
        echo '';
    }
}
?>
