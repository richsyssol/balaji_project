<?php
include 'includes/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = $_POST['otp'] ?? '';
    $otp_user = $_SESSION['otp_user'] ?? null;

    if ($otp_user && $otp_input) {
        $stmt = $conn->prepare("SELECT otp, otp_expiry FROM user WHERE id = ?");
        $stmt->bind_param("i", $otp_user['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            if ($row['otp'] === $otp_input && strtotime($row['otp_expiry']) >= time()) {
                $_SESSION['verified_user'] = $otp_user;
                header("Location: update_pass.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid or expired OTP.";
            }
        }
    } else {
        $_SESSION['error_message'] = "OTP required.";
    }

    header("Location: verify_admin_otp.php");
    exit();
}
?>
