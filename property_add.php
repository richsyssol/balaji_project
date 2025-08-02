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

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$description = '';
$location = '';
$milkat_no = '';
$survey_no = '';
$errors = [];
$is_edit = false;
$original_milkat_no = null; // Initialize $id variable

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['milkat_no'])) {
    $milkat_no = $conn->real_escape_string(trim($_GET['milkat_no']));
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM property_details WHERE milkat_no='$milkat_no' ");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $description = strtoupper($row['description']);
        $location = strtoupper($row['location']);
        $original_milkat_no = strtoupper($row['milkat_no']);
        $survey_no = strtoupper($row['survey_no']);

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
        $original_milkat_no = $conn->real_escape_string(trim($original_milkat_no));
        
        // If mv_num is a string, add quotes around it for the SQL query
        $original_milkat_no_quoted = "'" . $original_milkat_no . "'";

        // SQL to delete the record based on mv_num
        $sql = "DELETE FROM property_details WHERE milkat_no=$original_milkat_no_quoted";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: expense-form.php"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }


    // Collect and sanitize inputs
    $description = strtoupper(trim($_POST['description']));
    $location = strtoupper(trim($_POST['location']));
    $milkat_no = strtoupper(trim($_POST['milkat_no']));
    $survey_no = strtoupper(trim($_POST['survey_no']));
    


    if (empty($description) || !preg_match("/^[A-Za-z ]+$/", $description)) {
        $errors[] = "Invalid description";
    }

    if (empty($location) || !preg_match("/^[A-Za-z ]+$/", $location)) {
        $errors[] = "Invalid location";
    }

    if (!empty($milkat_no) && !preg_match("/^[A-Za-z0-9\/ ]+$/", $milkat_no)) {
        $errors[] = "Invalid milkat number";
    }

    if (!empty($survey_no) && !preg_match("/^[A-Za-z0-9\/,.\-\+= ]+$/", $survey_no)) {
        $errors[] = "Invalid survey number";
    }
    
    
    
    // If no errors, process the form
    if (empty($errors)) {

        if ($is_edit) {
            $sql = "UPDATE property_details 
                    SET description='$description', location='$location', milkat_no='$milkat_no',survey_no='$survey_no' 
                    WHERE milkat_no='$original_milkat_no'";
            if ($conn->query($sql) === TRUE) {
                header("Location: expense-form.php");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
    
        else {
            
            
            // SQL query to insert the expense-form
            $sql = "INSERT INTO property_details (description, location, milkat_no,survey_no) 
                    VALUES ('$description', '$location', '$milkat_no','$survey_no')";
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
                <h1>ADD Property</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="expense-form">Expense Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Property</li>
                </ol>
            </nav>
        </div>
        
            <form 

            action="property_add.php<?= $is_edit ? '?action=edit&milkat_no=' . htmlspecialchars($original_milkat_no) : '' ?>" 
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
                <label for="chequeNumber" class="form-label">Description</label>
                <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($description) ?>" id="chequeNumber" placeholder="Enter description">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Location</label>
                <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($location) ?>" id="chequeNumber" placeholder="Enter location">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Milakt Number</label>
                <input type="text" class="form-control" name="milkat_no" value="<?= htmlspecialchars($original_milkat_no) ?>" id="chequeNumber" placeholder="Enter Milkat Number">
            </div>

            <div class="col-md-6 field">
                <label for="chequeNumber" class="form-label">Survay Number</label>
                <input type="text" class="form-control" name="survey_no" value="<?= htmlspecialchars($survey_no) ?>" id="chequeNumber" placeholder="Enter Survay Number">
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