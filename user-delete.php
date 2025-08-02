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
    include 'include/header1.php'; 
    include 'include/footer.php';

include 'includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Verify the password
    if (isset($_POST['password'], $_POST['itemId'])) {
        $password = $_POST['password'];
        $itemId = $_POST['itemId'];

        // Fetch the admin password from the database
        $query = "SELECT password FROM user WHERE role = 'admin' LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password verified, store itemId in session
                $_SESSION['itemId'] = $itemId;

                // Trigger the confirmation modal for deletion
                echo "<script>
                        var confirmModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                        confirmModal.show();
                      </script>";
            } else {
                // Incorrect password
                echo "<script>alert('Incorrect password. Please try again.'); window.location.href='user.php';</script>";
            }
        } else {
            echo "<script>alert('No admin user found.'); window.location.href='user.php';</script>";
        }
    }

    // Step 2: Handle confirmation and perform soft delete
    if (isset($_POST['confirmed'])) {
        $itemId = $_SESSION['itemId']; // Retrieve itemId from session
        unset($_SESSION['itemId']); // Clear session

        // Perform  delete
        $query = "DELETE FROM user WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $itemId);

        if ($stmt->execute()) {
            echo "<script>alert('Item deleted successfully.'); window.location.href='user.php';</script>";
        } else {
            echo "<script>alert('Error deleting item.'); window.location.href='user.php';</script>";
        }
    }
}
?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this item?
      </div>
      <div class="modal-footer">
        <form method="POST" action="user-delete.php">
          <input type="hidden" name="confirmed" value="1">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Show confirmation modal after password verification
document.addEventListener('DOMContentLoaded', function () {
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    if (confirmModal) {
        confirmModal.show();
    }
});
</script>




<?php //} ?>