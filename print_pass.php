

<?php
include 'includes/db_conn.php';

// Get the user-provided password from the AJAX request
$userPassword = $_POST['password'] ?? '';

if (empty($userPassword)) {
    echo json_encode(['error' => 'Password is required']);
    exit;
}

// Fetch the admin's hashed password from the database
$sql = "SELECT password FROM file WHERE file_type = 'Print' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPassword = $row['password'];

    // Verify the password
    if (password_verify($userPassword, $hashedPassword)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
    }
} else {
    echo json_encode(['error' => 'No admin user found']);
}

$conn->close();
?>

