

<?php 
include 'include/header.php'; 
 
session_start();
if (!isset($_SESSION['verified_user'])) {
    header("Location: forget_admin.php");
    exit();
}
$error = $_SESSION['error_message'] ?? '';
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
                    
            
                    
                    <form action="update_pass_action.php" method="post">
                        <div class="mb-3">
                            <label>New Password :</label>
                            <input type="text" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password :</label>
                            <input type="text" name="confirm_password" class="form-control" required>
                        </div>
                        
                        
                        <input type="submit" value="Submit" class="btn btn-primary w-100">
                    </form>

                
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/header1.php'; ?>

