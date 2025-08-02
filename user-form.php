<?php 
// session_start(); // Start the session

// // Check if user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     // Redirect to login page if not logged in
//     header("Location: login.php");
//     exit();
// }

include 'session_check.php';

// Restrict access if not admin
if ($_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied. You do not have permission to access this page.</div>";
    exit(); // Ensure that no further code is executed for non-admins
}

// If the user is an admin, include the rest of the page
include 'include/header.php'; 
include 'include/head.php'; 
?>

<?php
// Include database connection
include 'includes/db_conn.php';

// Initialize error variables
$passwordError = "";
$usernameError = "";
$editMode = false; // Track if form is in edit mode

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    // Validate email
    if (empty($username) || !filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $usernameError = "Please enter a valid email address.";
    }

    // Validate password for new users or password change during edit
    if (empty($userId) || !empty($password)) {
        if (strlen($password) < 8) {
            $passwordError = "Password must be at least 8 characters.";
        } elseif (!preg_match("#[0-9]+#", $password)) {
            $passwordError = "Password must include at least one number.";
        } elseif (!preg_match("#[A-Z]+#", $password)) {
            $passwordError = "Password must include at least one uppercase letter.";
        } elseif (!preg_match("#[a-z]+#", $password)) {
            $passwordError = "Password must include at least one lowercase letter.";
        }
    }

    // If there are no validation errors
    if (empty($usernameError) && empty($passwordError)) {
        if ($userId) {
            // Edit user
            if (!empty($password)) {
                // Hash the new password if provided
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE user SET username = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $username, $hashedPassword, $userId);
            } else {
                $sql = "UPDATE user SET username = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $username, $userId);
            }
            $stmt->execute();
            echo "<div class='alert alert-success'>User updated successfully!</div>";
        } else {
            // New user registration
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user (username, password, role) VALUES (?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashedPassword);
            $stmt->execute();
            echo "<div class='alert alert-success container'>User registered successfully!</div>";
        }
        $stmt->close();
    }
}

// Handle delete request
// if (isset($_GET['delete'])) {
//     $userId = $_GET['delete'];
//     $sql = "DELETE FROM user WHERE id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $userId);
//     $stmt->execute();
//     header("Location: user");
//     exit;
//     $stmt->close();
// }

// Fetch users
$result = $conn->query("SELECT * FROM user");
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>

    <div class="container p-5">
        <h1>ADD / EDIT USER</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="user.php">User</a></li>
                <li class="breadcrumb-item active" aria-current="page">ADD USER</li>
            </ol>
        </nav>

        <form action="#" method="POST" class="p-5 shadow bg-white">
            <input type="hidden" name="user_id" value="<?php echo isset($_GET['edit']) ? $_GET['edit'] : ''; ?>">

            <div class="mb-3 field1">
                <label for="username" class="form-label">Email address</label>
                <input type="email" class="form-control <?php echo (!empty($usernameError)) ? 'is-invalid' : ''; ?>" name="username" id="username" value="<?php echo isset($_GET['edit']) ? htmlspecialchars($_GET['edit_username']) : ''; ?>" placeholder="Enter Email">
                <div class="invalid-feedback">
                    <?php echo $usernameError; ?>
                </div>
            </div>

            <div class="mb-3 field1">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control <?php echo (!empty($passwordError)) ? 'is-invalid' : ''; ?>" name="password" id="password" placeholder="Enter Password">
                <div class="invalid-feedback">
                    <?php echo $passwordError; ?>
                </div>
            </div>

            <button type="submit" name="submit" class="btn sub-btn1">Submit</button>

            <?php if (isset($_GET['edit'])): ?>
                <a href="user-form.php?delete=<?php echo $_GET['edit']; ?>" class="btn sub-btn1">Delete</a>
                <!-- Button trigger modal -->
                <!--<a type="button" class="btn sub-btn1" data-bs-toggle="modal" data-bs-target="#exampleModal">-->
                <!--  Delete-->
                <!--</a>-->
            <?php endif; ?>
        </form>

      
    </div>
</section>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          
        <form>
            <div class="mb-3">
              <label for="exampleFormControlInput1" class="form-label">Username</label>
              <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
            </div>
            
            <div class="mb-3">
              <label for="exampleFormControlInput1" class="form-label">Password</label>
              <input type="password" class="form-control" id="exampleFormControlInput1" placeholder="********">
            </div>
        </form>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Delete</button>
      </div>
    </div>
  </div>
</div>








<?php 
include 'include/footer.php';
$conn->close();
?>
