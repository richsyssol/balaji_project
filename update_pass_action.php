<?php
include 'includes/db_conn.php';
session_start();

if (!isset($_SESSION['verified_user'])) {
    header("Location: forgot_pass.php");
    exit();
}

$new_pass = trim($_POST['new_password'] ?? '');
$confirm_pass = trim($_POST['confirm_password'] ?? '');
$user = $_SESSION['verified_user'];

if (empty($new_pass) || empty($confirm_pass)) {
    $_SESSION['error_message'] = "All fields are required.";
    header("Location: update_pass.php");
    exit();
}

if ($new_pass !== $confirm_pass) {
    $_SESSION['error_message'] = "Passwords do not match.";
    header("Location: update_pass.php");
    exit();
}

$hashed = password_hash($new_pass, PASSWORD_DEFAULT);

// Update password in database
$stmt = $conn->prepare("UPDATE user SET password = ?, otp = NULL, otp_expiry = NULL WHERE id = ?");
$stmt->bind_param("si", $hashed, $user['id']);

if ($stmt->execute()) {
    // Clear session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
} else {
    $_SESSION['error_message'] = "Failed to update password. Please try again.";
    header("Location: update_pass.php");
    exit();
}
?>
