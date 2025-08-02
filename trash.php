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
 
include 'includes/db_conn.php';

// Fetch soft-deleted entries from all tables
$tables = [
    'GIC' => ['table_name' => 'gic_entries', 'display_name' => 'GIC'],
    'LIC' => ['table_name' => 'lic_entries', 'display_name' => 'LIC'],
    'MF' => ['table_name' => 'mf_entries', 'display_name' => 'MF'],
    'RTO' => ['table_name' => 'rto_entries', 'display_name' => 'RTO'],
    'BMDS' => ['table_name' => 'bmds_entries', 'display_name' => 'BMDS']
];

$soft_deleted_entries = [];

foreach ($tables as $key => $table) {
    $sql = "SELECT * FROM " . $table['table_name'] . " WHERE is_deleted = 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['table_name'] = $table['table_name']; // Correctly set the table name
            $row['display_name'] = $table['display_name']; // Correctly set the display name
            $soft_deleted_entries[] = $row;
        }
    }
}

// Handle restore and permanent delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['restore_id'])) {
        $restore_id = $_POST['restore_id'];
        $table_name = $_POST['table_name'];

        // Restore entry
        $restore_sql = "UPDATE $table_name SET is_deleted = 0 WHERE id = ?";
        $stmt = $conn->prepare($restore_sql);
        $stmt->bind_param("i", $restore_id);
        $stmt->execute();
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $table_name = $_POST['table_name'];

        // Permanently delete entry
        $delete_sql = "DELETE FROM $table_name WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    elseif (isset($_POST['restore_all'])) {
        // Restore all entries
        foreach ($tables as $table) {
            $table_name = $table['table_name']; // Access table_name correctly
            $restore_all_sql = "UPDATE $table_name SET is_deleted = 0 WHERE is_deleted = 1";
            $conn->query($restore_all_sql);
        }
    } 
    elseif (isset($_POST['delete_all'])) {
        // Permanently delete all entries
        foreach ($tables as $table) {
            $table_name = $table['table_name']; // Access table_name correctly
            $delete_all_sql = "DELETE FROM $table_name WHERE is_deleted = 1";
            $conn->query($delete_all_sql);
        }
    }
    
    // Refresh the soft-deleted entries list after any action
    header("Location: trash.php");
    exit;
    
}
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    
    <div class="container p-5">
        
        <div class="ps-5">
            <div>
                <h1>TRASH BIN</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">TRASH BIN</li>
              </ol>
            </nav>
        </div>
        
        <div class="bg-white con-tbl p-5">
            
            
            <div class="float-end">
                <!-- Restore All and Delete All Buttons -->
                <form method="POST" class="mb-3">
                    <button type="submit" name="restore_all" class="btn sub-btn1" onclick="return confirmRestoreAll()">Restore All</button>
                    <button type="submit" name="delete_all" class="btn sub-btn1" onclick="return confirmDeleteAll()">Delete All</button>
                </form>
            </div>
            
        
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Table</th>
                        <th scope="col">Reg No.</th>
                        <th scope="col">Client Name</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Deleted At</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($soft_deleted_entries) > 0) {
                        $serial_number = 1;
                        foreach ($soft_deleted_entries as $row) {
                            $unique_id = $row['id']; // Use entry ID to create unique form IDs
                            ?>
                            <tr>
                                <th scope="row"><?php echo $serial_number++; ?></th>
                                <td><?php echo htmlspecialchars($row['display_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td><?php echo htmlspecialchars($row['deleted_at']); ?></td>
                                <td>
                                    <!-- Restore Form with unique ID -->
                                    <form id="restoreForm_<?php echo $unique_id; ?>" method="POST" onsubmit="return confirmRestore(<?php echo $unique_id; ?>)" style="display:inline;">
                                        <input type="hidden" name="restore_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="table_name" value="<?php echo $row['table_name']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Restore</button>
                                    </form>
                                    <!-- Delete Form with unique ID -->
                                    <form id="deleteForm_<?php echo $unique_id; ?>" method="POST" onsubmit="return confirmDelete(<?php echo $unique_id; ?>)" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="table_name" value="<?php echo $row['table_name']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Permanently Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='7'>No soft deleted entries found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        
        </div>
    </div>
</section>


<script>
function confirmRestore(id) {
    if (confirm("Are you sure you want to restore this entry?")) {
        document.getElementById('restoreForm_' + id).submit(); // Submit the form after confirmation
        return true; // Allow form submission
    }
    return false; // Cancel form submission if not confirmed
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this entry?")) {
        document.getElementById('deleteForm_' + id).submit(); // Submit the form after confirmation
        return true; // Allow form submission
    }
    return false; // Cancel form submission if not confirmed
}

function confirmRestoreAll() {
    return confirm('Are you sure you want to restore all entries?');
}

function confirmDeleteAll() {
    return confirm('Are you sure you want to permanently delete all entries?');
}

</script>






















<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>