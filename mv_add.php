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
$vehicle_type = '';
$mv_num = '';
$user_name = '';
$errors = [];
$is_edit = false;
$original_mv_num = '';

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['mv_num'])) {
    $mv_num = $conn->real_escape_string(trim($_GET['mv_num']));
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM vehicle_details WHERE mv_num='$mv_num' ");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $vehicle_type = strtoupper($row['vehicle_type']);
        $original_mv_num = $row['mv_num']; // Assign the value correctly
        $user_name = strtoupper($row['user_name']);

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
        // Sanitize and escape the mv_num value
        $original_mv_num = $conn->real_escape_string(trim($original_mv_num));
        
        // If mv_num is a string, add quotes around it for the SQL query
        $original_mv_num_quoted = "'" . $original_mv_num . "'";

        // SQL to delete the record based on mv_num
        $sql = "DELETE FROM vehicle_details WHERE mv_num=$original_mv_num_quoted";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: expense-form.php"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }


    // Collect and sanitize inputs
    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
    $mv_num = strtoupper(trim($_POST['mv_num']));
    $user_name = strtoupper(trim($_POST['user_name']));
    


    if (empty($vehicle_type) || !preg_match("/^[A-Za-z ]+$/", $vehicle_type)) {
        $errors[] = "Invalid Vehicle Type";
    }

    if (empty($user_name) || !preg_match("/^[A-Za-z ]+$/", $user_name)) {
        $errors[] = "Invalid User Name";
    }

    if (!empty($mv_num) && !preg_match("/^[A-Za-z0-9\/ ]+$/", $mv_num)) {
        $errors[] = "Invalid MV Number";
    }
    
    
    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            $sql = "UPDATE vehicle_details 
                    SET vehicle_type='$vehicle_type', mv_num='$mv_num', user_name='$user_name' 
                    WHERE mv_num='$original_mv_num'";
            if ($conn->query($sql) === TRUE) {
                header("Location: expense-form.php");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        }  
    
        else {
            
            
            // SQL query to insert the expense-form
            $sql = "INSERT INTO vehicle_details (vehicle_type, mv_num, user_name) 
                    VALUES ('$vehicle_type', '$mv_num', '$user_name')";
            if ($conn->query($sql) === TRUE) {
                header("Location: expense-form.php");
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
                <h1>ADD MV</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="expense-form">Expense Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ADD MV</li>
                </ol>
            </nav>
        </div>
            <form 
                action="mv_add.php<?= $is_edit ? '?action=edit&mv_num=' . htmlspecialchars($original_mv_num) : '' ?>" 
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
                <label for="chequeNumber" class="form-label">Vehicle Type</label>
                <input type="text" class="form-control" name="vehicle_type" value="<?= htmlspecialchars($vehicle_type) ?>" id="chequeNumber" placeholder="Enter Vehicle Type">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">MV Number</label>
                <input type="text" class="form-control" name="mv_num" value="<?= htmlspecialchars($original_mv_num) ?>" id="chequeNumber" placeholder="Enter MV Number">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">User Name</label>
                <input type="text" class="form-control" name="user_name" value="<?= htmlspecialchars($user_name) ?>" id="chequeNumber" placeholder="Enter User Name">
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