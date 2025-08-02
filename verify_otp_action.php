<?php
include 'includes/db_conn.php';
session_start();
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$otp_input = trim($_POST['otp'] ?? '');
$user = $_SESSION['temp_user'];
$user_id = $user['id'];

$stmt = $conn->prepare("SELECT otp, otp_expiry FROM user WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error); // debug line
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data && $otp_input == $data['otp']) {
    if (strtotime($data['otp_expiry']) >= time()) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = $user['role'];

        $conn->query("UPDATE user SET otp = NULL, otp_expiry = NULL WHERE id = $user_id");

        unset($_SESSION['temp_user']);

        header("Location: todo.php");
        exit();
    } else {
        $_SESSION['error_message'] = "OTP expired. Please request a new one.";
    }
} else {
    $_SESSION['error_message'] = "Invalid OTP.";
}
$conn->close();
header("Location: verify_otp.php");
exit();
?>
