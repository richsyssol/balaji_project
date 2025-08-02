
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
// $date = '';
$thought = '';

$errors = [];
$is_edit = false;
$add_new = false;
$id = null; // Initialize $id variable



// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM thought WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // $date = $row['date'];
        $thought = strtoupper($row['thought']);
    
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
        // Delete the record
        $sql = "DELETE FROM thought WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: thought"); // Redirect to main form after deletion
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }

    // Collect and sanitize inputs
    // $date = trim($_POST['date']);
    $thought = strtoupper(trim($_POST['thought']));
    

    // Validation
   
     
    if (empty($thought) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $thought)) {
        $errors[] = "Invalid thought";
    }






   


    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            // Update existing entry
            $sql = "UPDATE thought SET thought='$thought' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: thought");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
        
        else {
            
            
            // Add new entry
            $sql = "INSERT INTO thought (thought) VALUES ('$thought')";
            if ($conn->query($sql) === TRUE) {
                header("Location: thought");
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
        
        <div class="ps-5">
            <div>
                <h1>ADD THOUGHT</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="thought">THOUGHT</a></li>
                <li class="breadcrumb-item active" aria-current="page">ADD THOUGHT</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="thought-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
                } elseif ($add_new) {
                    echo '?action=add_new&id=' . $id; 
                } ?>" 
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
             
    
          <!-- Date (Current Date) -->
            <!-- <div class="col-md-12 field">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" name="date" id="date" 
                    value="<?php 
                        // if ($is_edit) {
                        //     echo htmlspecialchars($date); // Show the existing date if in edit mode
                        // } else {
                        //     echo date('Y-m-d'); // Show the current date for adding a new entry
                        // } 
                    ?>">
                </div> -->

          </div>
            
        <div class="row g-3 mb-3">
            <!-- Client Name -->
            <div class="col-md-12 field">
                <label for="clientName" class="form-label">Thought</label>
                <input type="text" class="form-control" name="thought" value="<?php echo htmlspecialchars($thought); ?>"  placeholder="Enter Thought" required>
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

<script>

function updateDates() {
    const duration = document.getElementById('policyDuration').value;
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    // Get today's date
    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];

    if (duration === '1 Month') {
        startDateInput.value = startDateInput.value || todayFormatted;
        adjustEndDate(); // Auto-calculate end date based on start date

    } else if (duration === 'Short Term' || duration === 'Long Term') {
        startDateInput.value = todayFormatted;

        // Clear the end date so the user can manually input it
        endDateInput.value = '';
        endDateInput.disabled = false;
        endDateInput.readOnly = false; // Allow manual entry for end date
    } else {
        startDateInput.value = '';
        endDateInput.value = '';
        endDateInput.disabled = true;
    }
}

function adjustEndDate() {
    const duration = document.getElementById('policyDuration').value;
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    if (duration === '1 Month') {
        // Calculate the end date one month less than the next month
        let startDate = new Date(startDateInput.value);
        let nextMonth = new Date(startDate.setMonth(startDate.getMonth() + 1)); // Move to next month
        let oneDayLess = new Date(nextMonth.setDate(nextMonth.getDate() - 1)); // Subtract one day

        endDateInput.value = oneDayLess.toISOString().split('T')[0]; // Set end date
        endDateInput.readOnly = true; // Make the input readonly so it's submitted but not editable
    }
}

// Call the updateDates function on page load to set default values
window.onload = updateDates;


// Function to calculate balance and recovery amounts
function updateAmounts() {
    const quotationAmount = parseFloat(document.getElementById('quotationAmount').value) || 0;
    const advanceAmount = parseFloat(document.getElementById('advanceAmount').value) || 0;

    // Calculate balance amount
    const balanceAmount = quotationAmount - advanceAmount;
    
    // Update both balance and recovery amount fields with the same value
    document.getElementById('balanceAmount').value = balanceAmount > 0 ? balanceAmount : 0; // Prevent negative values
    document.getElementById('recoveryAmount').value = balanceAmount > 0 ? balanceAmount : 0; // Same value for recovery amount
}

// Allow only numeric input
function isNumeric(event) {
    const keyCode = event.keyCode || event.which;
    const keyValue = String.fromCharCode(keyCode);

    // Allow only digits (0-9) and restrict any other characters
    return /^[0-9]*$/.test(keyValue);
}

</script>
  



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php 
    
// }
?>