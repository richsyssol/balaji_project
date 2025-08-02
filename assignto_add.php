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
$username = '';
$contact = '';
$errors = [];
$is_edit = false;
$original_username = '';

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['username'])) {
    $username = $conn->real_escape_string(trim($_GET['username']));
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM assign_to WHERE username='$username' ");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $original_username = $row['username']; // Assign the value correctly
        $username = strtoupper($row['username']);
        $contact = $row['contact'];

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
        // Sanitize and escape the username value
        $original_username = $conn->real_escape_string(trim($original_username));
        
        // If username is a string, add quotes around it for the SQL query
        $original_username_quoted = "'" . $original_username . "'";

        // SQL to delete the record based on username
        $sql = "DELETE FROM assign_to WHERE username=$original_username_quoted  AND contact='$contact'";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: todo-form"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }


    // Collect and sanitize inputs
    $username = strtoupper(trim($_POST['username']));
    $contact = trim($_POST['contact']);
    


    if (!empty($username) && !preg_match("/^[A-Za-z0-9\/ ]+$/", $username)) {
        $errors[] = "Invalid Username";
    }

    if (!empty($contact) && !preg_match("/^[0-9]{10}$/", $contact)) {
        $errors[] = "Invalid Contact Number. It must be a 10-digit number.";
    }
    
    
    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            $sql = "UPDATE assign_to 
                    SET  username='$username' , contact='$contact' 
                    WHERE username='$original_username'";
            if ($conn->query($sql) === TRUE) {
                header("Location: todo-form");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        }  
    
        else {
            
            
            // SQL query to insert the expense-form
            $sql = "INSERT INTO assign_to (username, contact) 
                    VALUES ( '$username', '$contact')";
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
                <h1>ADD ASSIGN TO</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="todo-form">ToDo Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ADD ASSIGN TO</li>
                </ol>
            </nav>
        </div>


        <form action="assignto_add.php<?= $is_edit ? '?action=edit&username=' . urlencode($original_username) . '&contact=' . urlencode($contact) : '' ?>" 
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
            
            <div class="col-md-12 field">
                <label for="chequeNumber" class="form-label">Assign To</label>
                <input type="text" class="form-control" name="username" value=" <?= htmlspecialchars($username) ?>" id="chequeNumber" placeholder="Enter User Name">
            </div>
        
            <div class="col-md-12 field">
                <label for="contactNumber" class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($contact) ?>" id="contactNumber">
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