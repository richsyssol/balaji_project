<?php
include 'includes/db_conn.php';
include 'otp_functions.php';
session_start();
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['temp_user'];
$otp = rand(100000, 999999);
$expires = date("Y-m-d H:i:s", strtotime('+5 minutes'));
$conn->query("UPDATE user SET otp = '$otp', otp_expiry = '$expires' WHERE id = {$user['id']}");

if ($user['otp_method'] === 'sms') {
    sendSMSOtp($user['mobile'], $otp);
} elseif ($user['otp_method'] === 'whatsapp') {
    sendWhatsAppOtp($user['mobile'], $otp);
}

$conn->close();
header("Location: verify_otp.php");
exit();
?>
