<?php
include 'includes/db_conn.php';
include 'otp_functions.php';
session_start();

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $otp_method = sanitize_input($_POST['otp_method'] ?? 'sms');

    if (!empty($username)) {
        // Fetch user info
        $stmt = $conn->prepare("SELECT id, username, role, mobile FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Optional: Restrict to admin only
                if ($user['role'] !== 'admin') {
                    $_SESSION['error_message'] = "Only admin users can reset password.";
                    header("Location: forgot_pass.php");
                    exit();
                }

                // Generate OTP
                $otp = rand(100000, 999999);
                $expires = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                $_SESSION['otp_expires_at'] = strtotime($expires);

                // Save OTP in DB
                $update_stmt = $conn->prepare("UPDATE user SET otp = ?, otp_expiry = ?, otp_medium = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $otp, $expires, $otp_method, $user['id']);
                $update_stmt->execute();

                // Save user for session
                $_SESSION['otp_user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'mobile' => $user['mobile']
                ];

                // Send OTP
                if ($otp_method === 'sms') {
                    sendSMSOtp($user['mobile'], $otp);
                } elseif ($otp_method === 'whatsapp') {
                    sendWhatsAppOtp($user['mobile'], $otp);
                }

                // Go to OTP verification page
                header("Location: verify_admin_otp.php");
                exit();

            } else {
                $_SESSION['error_message'] = "User not found.";
            }
        } else {
            $_SESSION['error_message'] = "Database error.";
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Username is required.";
    }

    $conn->close();
    header("Location: forgot_pass.php");
    exit();
}
?>
