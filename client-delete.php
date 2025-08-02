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
    if (isset($_POST['itemId'])) {
        $itemId = $_POST['itemId'];

        // Step 1: Check if the client exists in other tables
        $tables = ['lic_entries', 'gic_entries', 'bmds_entries', 'rto_entries', 'mf_entries'];
        $clientExists = false;

        foreach ($tables as $table) {
            $query = "SELECT 1 FROM `$table` WHERE `client_id` = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $clientExists = true;
                break;
            }
            $stmt->close();
        }

        // If the client exists in any table, show an alert and stop execution
        if ($clientExists) {
            echo "<script>alert('Client cannot be deleted as they are associated with other records.'); window.location.href='client.php';</script>";
            exit;
        }

        // Step 2: Proceed with password verification
        if (isset($_POST['password'])) {
            $password = $_POST['password'];

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

                    // Trigger the confirmation modal
                    echo "<script>
                            var confirmModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                            confirmModal.show();
                          </script>";
                } else {
                    echo "<script>alert('Incorrect password. Please try again.'); window.location.href='client.php';</script>";
                }
            } else {
                echo "<script>alert('No admin user found.'); window.location.href='client.php';</script>";
            }
        }
    }

    // Step 3: Handle confirmation and perform soft delete
    if (isset($_POST['confirmed'])) {
        $itemId = $_SESSION['itemId'];
        unset($_SESSION['itemId']);

        // Perform soft delete by setting deleted_at
        $query = "DELETE FROM client WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $itemId);

        if ($stmt->execute()) {
            echo "<script>alert('Client deleted successfully.'); window.location.href='client.php';</script>";
        } else {
            echo "<script>alert('Error deleting client.'); window.location.href='client.php';</script>";
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
        <form method="POST" action="client-delete.php">
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