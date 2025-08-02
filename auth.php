<?php 
include 'includes/db_conn.php';

// Start the session
session_start();

// Function to sanitize inputs
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize error message
$error_message = '';

// Login user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required fields are set
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = sanitize_input($_POST['username']);
        $password = sanitize_input($_POST['password']);

        // Validate inputs
        if (empty($username) || empty($password)) {
            $error_message = "Please fill in all required fields.";
        } else {
            // Prepare the SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT password, role FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
                $role = $row['role'];  // Assuming you have a role column

                // Verify password
                if (password_verify($password, $hashed_password)) {
                    // Set session variables
                    $_SESSION['username'] = $username;
                    $_SESSION['loggedin'] = true;
                    $_SESSION['role'] = $role; // Store the role in the session

                    // Redirect based on role
                    if ($role == 'admin') {
                        header("Location: todo.php"); // Redirect to admin dashboard
                    } else {
                        header("Location: todo.php"); // Redirect to user dashboard
                    }
                    exit();
                } else {
                    $error_message = "Invalid password.";
                }
            } else {
                $error_message = "User not found.";
            }

            $stmt->close();
        }
    } else {
        $error_message = "Please fill in the required fields.";
    }
}

// Close the database connection
$conn->close();

// If there's an error, store it in session and redirect back to login page
if (!empty($error_message)) {
    $_SESSION['error_message'] = $error_message;
    header("Location: login.php");
    exit();
}
?>
