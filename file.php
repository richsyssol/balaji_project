<?php 
// session_start(); // Start the session

// // Check if user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ) {
//     // Redirect to login page if not logged in
//     header("Location: login.php"); // Adjust path if needed
//     exit(); // Ensure no further code is executed
// }
// else{
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>



<?php
// Include your database connection
include 'includes/db_conn.php'; // Update this with the correct path to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $password = $_POST['password'];
    $file_id = $_POST['file_id'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepare the SQL query to update the password for the selected file_id
    $sql = "UPDATE file SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $hashed_password, $file_id);

        if ($stmt->execute()) {
            echo "<div class='container alert alert-success'>Password updated successfully.</div>";
        } else {
            echo "<div class='container alert alert-danger'>Error updating password: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='container alert alert-danger'>Error preparing the query: " . $conn->error . "</div>";
    }

    // Close the connection
    $conn->close();
}
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>File Password</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">File Password</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        

        <div class="text-center">
            <h2>Update Password</h2>
        </div>

        <form method="POST" action="">
            <div class="row">
                <div class="mb-3 field">
                    <label for="file_id" class="form-label">Select Record:</label>
                    <select id="file_id" name="file_id" class="form-control" required>
                        <option value="">Select a Record</option>
                        <?php
                        // Fetch records to populate the dropdown
                        $sql = "SELECT id, file_type FROM file";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['file_type'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No records available</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3 field">
                    <label for="password" class="form-label">New Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn sub-btn1">Update</button>
            </div>
        </form>

        </div>  
    </div>
</section>




<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>