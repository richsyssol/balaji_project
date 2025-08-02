<?php
include 'includes/db_conn.php';
include 'otp_functions.php';
session_start();

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = sanitize_input($_POST['password'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $otp_method = $_POST['otp_method'] ?? 'sms';
    $otp_method = $_POST['otp_method'] ?? 'whatsapp';


    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, password, role, mobile FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['temp_user'] = [
                    'id' => $user['id'],
                    'username' => $username,
                    'role' => $user['role'],
                    'mobile' => $user['mobile'],
                    'otp_method' => $otp_method
                ];

                $otp = rand(100000, 999999);
                $expires = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                $_SESSION['otp_expires_at'] = strtotime('+5 minutes'); // Store expiry timestamp

                $conn->query("UPDATE user SET otp = '$otp', otp_expiry = '$expires' WHERE id = {$user['id']}");

                if ($otp_method === 'sms') {
                    
                    sendSMSOtp($user['mobile'], $otp);
                
                } elseif ($otp_method === 'whatsapp') {
                    sendWhatsAppOtp($user['mobile'], $otp);
                }
            
                header("Location: verify_otp.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid password.";
            }
        } else {
            $_SESSION['error_message'] = "User not found.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Please fill in all required fields.";
    }
    $conn->close();
    header("Location: login.php");
    exit();
}
?>
