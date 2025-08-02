<?php
session_start();
if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        $_SESSION['username'] = $_SESSION['otp_username'];
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = $_SESSION['otp_role'];

        unset($_SESSION['otp'], $_SESSION['otp_username'], $_SESSION['otp_role'], $_SESSION['otp_mobile']);

        header("Location: todo.php");
        exit();
    } else {
        $error = "Invalid OTP.";
    }
}
?>

<form method="post">
    <div class="mb-3">
        <label>Enter OTP</label>
        <input type="number" name="otp" class="form-control" required>
    </div>
    <input type="submit" value="Verify OTP" class="btn btn-success">
</form>

<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
