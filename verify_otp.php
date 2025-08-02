<?php
include 'include/header.php'; 

session_start();
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}
$error_message = $_SESSION['error_message'] ?? '';
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
                    
                    <form action="verify_otp_action.php" method="post">
                        
                        <div class="mb-3">
                            <label>Enter OTP :</label>
                            <input type="text" name="otp" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Verify OTP</button>
                    </form>

                    <?php
$remaining_seconds = 0;
if (isset($_SESSION['otp_expires_at'])) {
    $remaining_seconds = $_SESSION['otp_expires_at'] - time();
    if ($remaining_seconds < 0) $remaining_seconds = 0;
}
?>

<div id="otp-timer" class="mb-3 text-danger fw-bold">
    OTP expires in <span id="countdown"></span>
</div>

                    
                    <form action="resend_otp.php" method="post" class="mt-3">
                        <button type="submit" class="btn btn-info w-100">Resend OTP</button>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Set initial seconds from PHP
let secondsRemaining = <?php echo $remaining_seconds; ?>;

function updateCountdown() {
    const countdown = document.getElementById('countdown');
    if (secondsRemaining <= 0) {
        countdown.textContent = "00:00";
        return;
    }

    const minutes = String(Math.floor(secondsRemaining / 60)).padStart(2, '0');
    const seconds = String(secondsRemaining % 60).padStart(2, '0');
    countdown.textContent = `${minutes}:${seconds}`;

    secondsRemaining--;
    setTimeout(updateCountdown, 1000);
}

updateCountdown();
</script>