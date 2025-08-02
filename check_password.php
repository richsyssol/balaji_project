<?php
// Assuming you have a database connection setup
include('includes/db_conn.php');

// Get the password from the POST request
$password = $_POST['password'];

// Query the database to get the stored password for 'Copy_Contact' file type
$sql = "SELECT password FROM file WHERE file_type = 'Copy_Contact' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the stored hashed password
    $row = $result->fetch_assoc();
    $storedPasswordHash = $row['password'];

    // Verify the entered password against the stored hash
    if (password_verify($password, $storedPasswordHash)) {
        echo "success"; // Password is correct
    } else {
        echo "failure"; // Password is incorrect
    }
} else {
    echo "failure"; // No password found
}

$conn->close();
?>
