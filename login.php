<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


<?php 
include 'include/header.php'; 
 
session_start(); // Start the session to access error messages

// Retrieve error message from session
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
// Clear error message from session
unset($_SESSION['error_message']);
?>

<div class="container-fluid login">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4 ">
            <div class="text-center">
                <img src="asset/image/Ujjwal_pingale-SIP.png" class="img-fluid">
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!--<h3 class="card-title text-center mb-4">Login</h3>-->
                    
                    <!-- Display error message -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="auth.php" method="post"> <!--For send otp for login send-otp.php-->
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Send OTP via:</label><br>
                            <input type="radio" name="otp_method" value="sms" checked> SMS
                            <input type="radio" name="otp_method" value="whatsapp"> WhatsApp
                        </div>
                        <input type="submit" value="Submit" class="btn btn-primary w-100">
                    </form>

                    <div class="text-center mt-3">
                        <a href="forgot_pass" class="text-decoration-none">Forgot your password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/header1.php'; ?>
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>
