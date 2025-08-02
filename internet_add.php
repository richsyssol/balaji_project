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
$internet = '';
$consumer_number = '';
$reference = '';
$errors = [];
$is_edit = false;
$original_internet = null; // Initialize $id variable

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['internet'])) {
    $internet = $conn->real_escape_string(trim($_GET['internet']));
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM internet_details WHERE internet='$internet' ");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $original_internet = strtoupper($row['internet']);
        $consumer_number = strtoupper($row['consumer_number']);
        $reference = strtoupper($row['reference']);

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
        $original_internet = $conn->real_escape_string(trim($original_internet));
        
        // If mv_num is a string, add quotes around it for the SQL query
        $original_internet_quoted = "'" . $original_internet . "'";

        // SQL to delete the record based on mv_num
        $sql = "DELETE FROM internet_details WHERE internet=$original_internet_quoted";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: expense-form.php"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }


    // Collect and sanitize inputs
    $internet = strtoupper(trim($_POST['internet']));
    $consumer_number = strtoupper(trim($_POST['consumer_number']));
    $reference = strtoupper(trim($_POST['reference']));
    


    if (empty($internet) || !preg_match("/^[A-Za-z ]+$/", $internet)) {
        $errors[] = "Invalid Internet";
    }

    if (empty($consumer_number) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)]+$/", $consumer_number)) {
        $errors[] = "Invalid Consumer Number";
    }

    if (empty($reference) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)]+$/", $reference)) {
        $errors[] = "Invalid reference";
    }
    


    
    
    
    // If no errors, process the form
    if (empty($errors)) {

        if ($is_edit) {
            $sql = "UPDATE internet_details 
                    SET internet='$internet', consumer_number='$consumer_number', reference='$reference'
                    WHERE internet='$original_internet'";
            if ($conn->query($sql) === TRUE) {
                header("Location: expense-form.php");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
    
        else {
            
            
            // SQL query to insert the expense-form
            $sql = "INSERT INTO internet_details (internet, consumer_number, reference) 
                    VALUES ('$internet', '$consumer_number', '$reference')";
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
                <h1>ADD INTERNET</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="expense-form">Expense Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Internet</li>
                </ol>
            </nav>
        </div>
        
            <form 

            action="internet_add.php<?= $is_edit ? '?action=edit&internet=' . htmlspecialchars($original_internet) : '' ?>" 
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
                <label for="chequeNumber" class="form-label">Internet</label>
                <input type="text" class="form-control" name="internet" value="<?= htmlspecialchars($original_internet) ?>" id="chequeNumber" placeholder="Enter Internet">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Consumer Number</label>
                <input type="text" class="form-control" name="consumer_number" value="<?= htmlspecialchars($consumer_number) ?>" id="chequeNumber" placeholder="Enter Consumer Number">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Reference</label>
                <input type="text" class="form-control" name="reference" value="<?= htmlspecialchars($reference) ?>" id="chequeNumber" placeholder="Enter Reference">
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