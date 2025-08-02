<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';

include 'includes/db_conn.php';

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$assign_by = '';
$errors = [];
$is_edit = false;
$original_assign_by = '';

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['assign_by'])) {
    $assign_by = $conn->real_escape_string(trim($_GET['assign_by']));
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM assignby WHERE assign_by='$assign_by' ");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $original_assign_by = $row['assign_by']; // Assign the value correctly
        $assign_by = strtoupper($row['assign_by']);

    } else {
        die("Entry not found.");
    }
}



// Handle form submission (add, edit, or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Handle delete action
    if (isset($_POST['delete']) && $is_edit) {
        // Sanitize and escape the assign_by value
        $original_assign_by = $conn->real_escape_string(trim($original_assign_by));
        
        // If assign_by is a string, add quotes around it for the SQL query
        $original_assign_by_quoted = "'" . $original_assign_by . "'";

        // SQL to delete the record based on assign_by
        $sql = "DELETE FROM assignby WHERE assign_by=$original_assign_by_quoted";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: todo-form"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }


    // Collect and sanitize inputs
    $assign_by = strtoupper(trim($_POST['assign_by']));
    


    if (!empty($assign_by) && !preg_match("/^[A-Za-z0-9\/ ]+$/", $assign_by)) {
        $errors[] = "Invalid assign_by";
    }
    
    
    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            $sql = "UPDATE assignby 
                    SET  assign_by='$assign_by' 
                    WHERE assign_by='$original_assign_by'";
            if ($conn->query($sql) === TRUE) {
                header("Location: todo-form");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        }  
    
        else {
            
            
            // SQL query to insert the expense-form
            $sql = "INSERT INTO assignby (assign_by) 
                    VALUES ( '$assign_by')";
            if ($conn->query($sql) === TRUE) {
                header("Location: todo-form");
                exit();
            } else {
                echo "Error: " . $conn->error;
            }
        
    }
        
    }
    
}
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php';  ?>
    
    <div class="container p-5">
        
        <div>
            <div>
                <h1>ADD ASSIGN BY</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="todo-form">ToDo Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ADD ASSIGN BY</li>
                </ol>
            </nav>
        </div>
            <form 
                action="assignby_add<?= $is_edit ? '?action=edit&assign_by=' . htmlspecialchars($original_assign_by) : '' ?>" 
                method="POST" class="p-5 shadow bg-white">

            
             <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            <?php if (!empty($errors)): ?>
                <div style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <div class="row g-3 mb-3">
            
            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Assign To</label>
                <input type="text" class="form-control" name="assign_by" value="<?= htmlspecialchars($assign_by) ?>" id="chequeNumber" placeholder="Enter User Name">
            </div>
        
            
            
        </div>
            
        

            <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
            
            <!-- Show delete button only in edit mode -->
            <?php if ($is_edit): ?>
                <button type="submit" name="delete" class="btn sub-btn" onclick="return confirm('Are you sure you want to delete this entry?');">Delete Entry</button>
            <?php endif; ?>
            
            
            
            
        </form>
    </div>
</section>


<?php
$conn->close();
?>





<?php 
    include 'include/footer.php';
    include 'include/header1.php';
?>

<?php 
    
// }
?>