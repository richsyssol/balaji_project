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
$reg_num = '';
$policy_date = '';
$time = '';
$client_name = '';
$contact = '';
$mf_type = '';
$mf_option = '';
$insurance_option = '';
$deadline = '';
$amount = '';
$referance = '';
$remark = '';
$address = '';
$birth_date = '';
$form_status = '';
$day = '';
$client_id = '';
$errors = [];
$is_edit = false;
$add_client = false;
$id = null; // Initialize $id variable

// Determine the current fiscal year based on the current date
$currentYear = date('Y');
$currentMonth = date('m');

// Determine the fiscal year start and end
if ($currentMonth >= 4) { // April or later
    $fiscalYearStart = $currentYear;
    $fiscalYearEnd = $currentYear + 1;
} else { // Before April (January to March)
    $fiscalYearStart = $currentYear - 1;
    $fiscalYearEnd = $currentYear;
}

// Define the fiscal year in "YYYY/YYYY" format
$fiscalYear = "$fiscalYearStart/$fiscalYearEnd";

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM mf_entries WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $contact = $row['contact'];
        $mf_type = $row['mf_type'];
        $mf_option = $row['mf_option'];
        $insurance_option = $row['insurance_option'];
        $deadline = $row['deadline'];
        $amount = $row['amount'];
        $referance = strtoupper($row['referance']);
        $remark = strtoupper($row['remark']);
        $address = strtoupper($row['address']);
        $birth_date = $row['birth_date'];
        $form_status = $row['form_status'];
        $day = $row['day'];
    } else {
        die("Entry not found.");
    }
} 
// elseif (isset($_GET['action']) && $_GET['action'] === 'add_new') {
//     $is_edit = false;
//     $add_new = true;

//     // Fetch new reg_num based on the current fiscal year
//     $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM mf_entries WHERE fiscal_year = '$fiscalYear'");
//     if ($result->num_rows > 0) {
//         $row = $result->fetch_assoc();
//         $reg_num = $row['max_reg_num'] + 1; // Get the next registration number
//     } else {
//         $reg_num = 1; // If no records exist for this fiscal year, start with 1
//     }

//     // Only fetch the client_name and contact if ID is provided
//     if (isset($_GET['id'])) {
//         $id = (int)$_GET['id'];
//         $result = $conn->query("SELECT client_name, contact, address,birth_date FROM mf_entries WHERE id=$id");
//         if ($result->num_rows > 0) {
//             $row = $result->fetch_assoc();
//             $client_name = $row['client_name'];
//             $contact = $row['contact'];
//             $address = $row['address'];
//             $birth_date = $row['birth_date'];
//         } else {
//             die("Entry not found.");
//         }
//     }
    
// } 

elseif (isset($_GET['action']) && $_GET['action'] === 'add_client') {
    $is_edit = false;
    $add_client = true;

    // Fetch new reg_num based on the current fiscal year
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM mf_entries WHERE fiscal_year = '$fiscalYear'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['max_reg_num'] + 1; // Get the next registration number
    } else {
        $reg_num = 1; // If no records exist for this fiscal year, start with 1
    }

    // Only fetch the client_name and contact if ID is provided
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT client_name, contact, address,birth_date FROM client WHERE id=$id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_name = $row['client_name'];
            $contact = $row['contact'];
            $address = $row['address'];
            $birth_date = $row['birth_date'];
        } else {
            die("Entry not found.");
        }
    }
    
} 

else {
    // Fetch the next available registration number from the database for the current fiscal year
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM mf_entries WHERE fiscal_year = '$fiscalYear'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['max_reg_num'] + 1; // Get the next registration number
    } else {
        $reg_num = 1; // If no records exist for this fiscal year, start with 1
    }
}

// Handle form submission (add, edit, or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }



    // Collect and sanitize inputs
    $reg_num = trim($_POST['reg_num']);
    $policy_date = trim($_POST['policy_date']);
    $time = trim($_POST['time']);
    $client_name = strtoupper(trim($_POST['client_name']));
    $contact = trim($_POST['contact']);
    $mf_type = isset($_POST['mf_type']) ? trim($_POST['mf_type']) : '';
    $mf_option = isset($_POST['mf_option']) ? trim($_POST['mf_option']) : '';
    $insurance_option = isset($_POST['insurance_option']) ? trim($_POST['insurance_option']) : '';
    $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $referance = isset($_POST['referance']) ? strtoupper(trim($_POST['referance'])) : '';
    $remark = isset($_POST['remark']) ? strtoupper(trim($_POST['remark'])) : '';
    $address = isset($_POST['address']) ? strtoupper(trim($_POST['address'])) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $form_status = isset($_POST['form_status']) ? trim($_POST['form_status']) : '';
    $day = isset($_POST['day']) ? trim($_POST['day']) : '';
    $client_id = trim($_POST['client_id']);

    // Check and set empty if the value is "Select option"
    if ($mf_option === 'Select option') {
        $mf_option = '';
    }

    if ($insurance_option === 'Select option') {
        $insurance_option = '';
    }

    
    // Validation
    if (empty($client_name) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $client_name)) {
        $errors[] = "Invalid client name";
    }

    if (empty($contact) || !preg_match("/^\d{10}$/", $contact)) {
        $errors[] = "Invalid contact number";
    }
    
    if (!empty($amount) && !is_numeric($amount)) {
        $errors[] = "Invalid amount";
    }
    
    if (!empty($referance) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $referance)) {
        $errors[] = "Invalid referance";
    }
    
    if (!empty($remark) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $remark)) {
        $errors[] = "Invalid remark";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }
    
    $creation_on = date('Y-m-d H:i:s'); // Get current policy_date and time in Asia/Kolkata timezone
    $uppolicy_date_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone

    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            // Update existing entry
            $sql = "UPDATE mf_entries SET reg_num='$reg_num', policy_date='$policy_date', client_name='$client_name', contact='$contact', mf_type='$mf_type', mf_option='$mf_option', insurance_option='$insurance_option', deadline='$deadline', amount='$amount', referance='$referance', remark='$remark', fiscal_year='$fiscalYear', address='$address',birth_date='$birth_date',form_status='$form_status',update_on='$update_on',day='$day' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: mf");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
        elseif ($add_client) {
            
             // username for who fill form
            $username = $_SESSION['username'];
            
            // Add a new entry for the same reg_num
            $sql = "INSERT INTO mf_entries (client_id,reg_num, policy_date,time, client_name, contact, mf_type, mf_option, insurance_option, deadline, amount, referance, remark, fiscal_year, address,username,birth_date,form_status,creation_on,day) 
                    VALUES ('$client_id','$reg_num', '$policy_date', '$time' , '$client_name', '$contact', '$mf_type', '$mf_option', '$insurance_option', '$deadline', '$amount', '$referance', '$remark', '$fiscalYear', '$address', '$username','$birth_date','$form_status','$creation_on','$day')";
            if ($conn->query($sql) === TRUE) {
                header("Location: client");
                exit();
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            
             // username for who fill form
            $username = $_SESSION['username'];
            
            // Add new entry
            $sql = "INSERT INTO mf_entries (client_id,reg_num, policy_date,time, client_name, contact, mf_type, mf_option, insurance_option, deadline, amount, referance, remark, fiscal_year, address,username,birth_date,form_status,creation_on,day) 
                    VALUES ('$client_id','$reg_num', '$policy_date', '$time' , '$client_name', '$contact', '$mf_type', '$mf_option', '$insurance_option', '$deadline', '$amount', '$referance', '$remark', '$fiscalYear', '$address', '$username','$birth_date','$form_status','$creation_on','$day')";
            if ($conn->query($sql) === TRUE) {
                header("Location: client");
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
                <h1>MF/INSURANCE FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="mf">MF/INSURANCE</a></li>
                <li class="breadcrumb-item active" aria-current="page">MF/INSURANCE FORM</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="mf-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
                } elseif ($add_client) {
                    echo '?action=add_client&id=' . $id; 
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

            <!-- client table id -->
            <input type="hidden" name="client_id" 
                value="<?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
                        echo htmlspecialchars($_POST['client_id']); // Keep value after error/reload
                    } elseif ($is_edit) {
                        echo htmlspecialchars($client_id);
                    } else {
                        echo htmlspecialchars($id);
                    }
                ?>"
            >
            
            <div class="row g-3 mb-3">
              <!-- Register Number (Auto Generated) -->
            <div class="col-md-6 field">
                <label for="registerNumber" class="form-label">Register Number</label>
                <input type="text" class="form-control" name="reg_num" id="registerNumber"  value="<?= htmlspecialchars($reg_num) ?>" >
            </div>
    
          <!-- Date (Current Date) -->
          <div class="col-md-3 field">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control text-success" name="policy_date" id="date" 
                    value="<?php 
                        if ($is_edit) {
                            echo htmlspecialchars(date('Y-m-d', strtotime($policy_date))); // Show existing date in edit mode
                        } else {
                            echo date('Y-m-d'); // Show current date for adding a new entry
                        } 
                    ?>" required>
            </div>
            
            
            <div class="col-md-3 field">
                <label for="time" class="form-label">Time</label>
                <input type="time" class="form-control text-success" name="time" id="time" 
                    value="<?php 
                        if ($is_edit) {
                            echo htmlspecialchars(date('H:i', strtotime($date))); // Show existing time in edit mode
                        } else {
                            echo date('H:i'); // Show current time for adding a new entry
                        } 
                    ?>" required readonly>
            </div>


          </div> 
            
        <div class="row g-3 mb-3">
            <!-- Client Name -->
            <div class="col-md-6 field">
                <label for="clientName" class="form-label">Client Name</label>
                <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"  placeholder="Enter Client Name" readonly required>
            </div>

            <!-- Mobile Number -->
            <div class="col-md-6 field">
                <label for="mobileNumber" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact) ?? $row['contact']; ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" readonly required>
            </div>
            
            <div class="col-md-6 field">
                <label for="clientName" class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address); ?>"  placeholder="Enter Address" readonly>
            </div>
            
            <div class="col-md-6 field">
                    <label for="date" class="form-label">Birth Date</label>
                    <input type="date" class="form-control text-success" name="birth_date" id="date" value="<?php echo htmlspecialchars($birth_date);?>" readonly >
                </div>
            
        </div>
            
            <div class="row g-3 mb-3">
                
                 <!-- Select Type Dropdown -->
                <div class="col-md-6 field">
                    <label for="paymentMode" class="form-label">Select Type</label>
                    <select class="form-select" name="mf_type" id="paymentMode" onchange="toggleTypeDropdown()">
                        <option selected>Select type</option>
                        <option value="MF" <?php if ($is_edit && $mf_type == 'MF') echo 'selected'; ?>>MF</option>
                        <option value="INSURANCE" <?php if ($is_edit && $mf_type == 'INSURANCE') echo 'selected'; ?>>INSURANCE</option>
                    </select>
                </div>
                
                <!-- Dropdown for MF options (SIP, SWP, Lumsum) -->
                <div class="col-md-6 field mt-3" id="mfOptions" style="display: <?php echo ($is_edit && $mf_type == 'MF') ? 'block' : 'none'; ?>;">
                    <label for="mfSelect" class="form-label">MF Options</label>
                    <select class="form-select" name="mf_option" id="mfSelect">
                        <option selected>Select option</option>
                        <option value="SIP" <?php if ($is_edit && $mf_option == 'SIP') echo 'selected'; ?>>SIP</option>
                        <option value="SWP" <?php if ($is_edit && $mf_option == 'SWP') echo 'selected'; ?>>SWP</option>
                        <option value="Lumsum" <?php if ($is_edit && $mf_option == 'Lumsum') echo 'selected'; ?>>Lumsum</option>
                    </select>
                </div>
                
                <!-- Dropdown for INSURANCE options (LIC, GIC) -->
                <div class="col-md-6 field mt-3" id="insuranceOptions" style="display: <?php echo ($is_edit && $mf_type == 'INSURANCE') ? 'block' : 'none'; ?>;">
                    <label for="insuranceSelect" class="form-label">Insurance Options</label>
                    <select class="form-select" name="insurance_option" id="insuranceSelect">
                        <option selected>Select option</option>
                        <option value="LIC" <?php if ($is_edit && $insurance_option == 'LIC') echo 'selected'; ?>>LIC</option>
                        <option value="GIC" <?php if ($is_edit && $insurance_option == 'GIC') echo 'selected'; ?>>GIC</option>
                    </select>
                </div>
          </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-4 field">
                    <label for="mobileNumber" class="form-label">Enter Amount</label>
                    <input type="number" name="amount" value="<?php echo htmlspecialchars($amount); ?>"  class="form-control" id="mobileNumber" placeholder="Enter Enter Amount" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '');" >
                </div>

                <!-- <div class="col-md-4 field">
                    <label for="day" class="form-label">Day of the Month</label>
                    <select class="form-control" name="day" id="day">
                        <?php
                        // Assume $selectedDay contains the day value to pre-select in edit mode
                        // $selectedDay = $is_edit ? $day : null; // Set $day to the value from your database or form
                        // for ($i = 1; $i <= 31; $i++) {
                        //     $isSelected = ($i == $selectedDay) ? 'selected' : '';
                        //     echo "<option value='$i' $isSelected>$i</option>";
                        // }
                        ?>
                    </select>
                </div> -->

                <div class="col-md-4 field">
                    <label for="date" class="form-label">Day of the Month</label>
                    <input type="date" class="form-control text-success" name="day" id="date" 
                        value="<?php 
                            if ($is_edit) {
                                echo htmlspecialchars(date('Y-m-d', strtotime($day))); // Show existing date in edit mode
                            } else {
                                echo date('Y-m-d'); // Show current date for adding a new entry
                            } 
                        ?>">
                </div>


                
                <div class="col-md-4 field">
                    <label for="date" class="form-label">Deadline</label>
                    <input type="date" class="form-control" name="deadline" id="date" 
                        value="<?php 
                            if ($is_edit) {
                                echo htmlspecialchars($deadline); // Show the existing date if in edit mode
                            } else {
                                echo date('Y-m-d'); // Show the current date for adding a new entry
                            } 
                        ?>">
                </div>
            </div>
            
            <div class="row g-3 mb-3">

                <div class="col-md-6 field">
                    <label for="remark" class="form-label">Reference</label>
                    <textarea class="form-control" name="referance" id="remark" rows="3" placeholder="Enter Reference"><?php echo htmlspecialchars($referance); ?></textarea>
                  </div>
                  
                  <div class="col-md-6 field">
                    <label for="remark" class="form-label">Remark</label>
                    <textarea class="form-control" name="remark" id="remark" rows="3" placeholder="Enter Remark"><?php echo htmlspecialchars($remark); ?></textarea>
                </div>

            </div>
            
            <div class="col-md-6 mt-3 mx-auto p-2 field" style="background-color: #ffcdcd;">
              <label for="motorSubType" class="form-label">Form Status</label>
              <select class="form-select" name="form_status" id="motorSubType" required>
                <option value="PENDING" <?php if ($is_edit && $form_status == 'PENDING') echo 'selected'; ?>>PENDING</option>
                <option value="COMPLETE" <?php if ($is_edit && $form_status == 'COMPLETE') echo 'selected'; ?>>COMPLETE</option>
              </select>
            </div>

           <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
            
            <!-- Show delete button only in edit mode -->
            <!--<?php if ($is_edit): ?>-->
            <!--    <button type="submit" name="delete" class="btn sub-btn" onclick="return confirm('Are you sure you want to delete this entry?');">Delete Entry</button>-->
            <!--<?php endif; ?>-->
            
        </form>
    </div>
</section>

<?php
$conn->close();
?>



<script>
   
    
    // for feach date automatic on policy duration
function updateDates() {
    const duration = document.getElementById('policyDuration').value;
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    // Get today's date
    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];

    // Set start date based on selection
    if (duration === '1yr') {
        const oneYearLater = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
        const oneYearLaterFormatted = oneYearLater.toISOString().split('T')[0];

        startDateInput.value = todayFormatted;
        endDateInput.value = oneYearLaterFormatted;
        endDateInput.disabled = false;
    } else if (duration === 'short' || duration === 'long') {
        startDateInput.value = todayFormatted;
        endDateInput.value = ''; // Clear the end date for short/long term
        endDateInput.disabled = true; // Disable end date input
    } else {
        startDateInput.value = '';
        endDateInput.value = '';
        endDateInput.disabled = true; // Disable end date input
    }
}

// Call the function on page load to set the default dates
window.onload = updateDates;
</script>
  



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>