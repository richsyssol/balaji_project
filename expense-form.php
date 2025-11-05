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
$reg_num = '';
$policy_date = '';
$time = '';
$amount = '';
$pay_mode = '';
$cheque_no = '';
$bank_name = '';
$cheque_dt = '';
$expense_type = '';
$ride_km = '';
$fuel = '';
$details = '';
$expenseCustomtype = '';
$mv_num = '';
$user_name = '';
$liter = '';
$remark = '';
$person_name = '';
$period = '';
$vehicle_type = '';
$consumer_num = '';
$telephone_num = '';
$internet = '';
$milkat_num = '';
$pro_description = '';
$pro_location = '';
$survey_no = '';
$branch_name = '';
$consumer_number = '';
$expense_status = '';
$end_date = '';
$reference = '';
$errors = [];
$is_edit = false;
$id = null; // Initialize $id variable

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM expenses WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $amount = $row['amount'];
        $pay_mode = $row['pay_mode'];
        $cheque_no = $row['cheque_no'];
        $bank_name = strtoupper($row['bank_name']);
        $cheque_dt = $row['cheque_dt'];
        $expense_type = strtoupper($row['expense_type']);
        $ride_km = $row['ride_km'];
        $fuel = $row['fuel'];
        $details = strtoupper($row['details']);
        $expenseCustomtype = strtoupper($row['expenseCustomtype']);
        $mv_num = $row['mv_num'];
        $user_name = strtoupper($row['user_name']);
        $liter = $row['liter'];
        $remark = $row['remark'];
        $person_name = $row['person_name'];
        $period = $row['period'];
        $vehicle_type = strtoupper($row['vehicle_type']);
        $consumer_num = strtoupper($row['consumer_num']);
        $telephone_num = strtoupper($row['telephone_num']);
        $internet = strtoupper($row['internet']);
        $milkat_num = strtoupper($row['milkat_num']);
        $pro_description = strtoupper($row['pro_description']);
        $pro_location = strtoupper($row['pro_location']);
        $survey_no = strtoupper($row['survey_no']);
        $branch_name = strtoupper($row['branch_name']);
        $consumer_number = $row['consumer_number'];
        $expense_status  = strtoupper($row['expense_status']);
        $end_date = $row['end_date'];
        $reference = $row['reference'];

    } else {
        die("Entry not found.");
    }
} 
else {
    // Fetch the next available registration number from the database
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM expenses");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['max_reg_num'] + 1; // Get the next registration number
    } else {
        $reg_num = 1; // If no records exist, start with 1
    }
}


// Handle form submission (add, edit, or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    
    
    
    
    
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Handle delete action
    // if (isset($_POST['delete']) && $is_edit) {
    //     // Delete the record
    //     $sql = "DELETE FROM expenses WHERE id=$id";
    //     if ($conn->query($sql) === TRUE) {
    //         header("Location: expense.php"); // Redirect to main form after deletion
    //         exit();
    //     } else {
    //         echo "Error deleting record: " . $conn->error;
    //     }
    // }

    // Collect and sanitize inputs
    $reg_num = trim($_POST['reg_num']);
    $policy_date = trim($_POST['policy_date']);
    $time = trim($_POST['time']);
    $amount = trim($_POST['amount']);
    $pay_mode = trim($_POST['pay_mode']);
    $cheque_no = trim($_POST['cheque_no']);
    $bank_name = strtoupper(trim($_POST['bank_name']));
    $cheque_dt = trim($_POST['cheque_dt']);
    $expense_type = strtoupper(trim($_POST['expense_type']));
    // $ride_km = trim($_POST['ride_km']);
    $fuel = trim($_POST['fuel']);
    $details = isset($_POST['details']) ? strtoupper(trim($_POST['details'])) : '';
    // $expenseCustomtype = strtoupper(trim($_POST['expenseCustomtype']));
    // $mv_num = trim($_POST['mv_num']);
    $user_name = strtoupper(trim($_POST['user_name']));
    $liter = trim($_POST['liter']);
    $remark = trim($_POST['remark']);
    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
    $consumer_num = strtoupper(trim($_POST['consumer_num']));
    $telephone_num = strtoupper(trim($_POST['telephone_num']));
    $internet = strtoupper(trim($_POST['internet']));
    $milkat_num = strtoupper(trim($_POST['milkat_num']));
    $pro_description = strtoupper(trim($_POST['pro_description']));
    $pro_location = strtoupper(trim($_POST['pro_location']));
    $survey_no = strtoupper(trim($_POST['survey_no']));
    $branch_name = strtoupper(trim($_POST['branch_name']));
    $consumer_number = trim($_POST['consumer_number']);
    $expense_status = strtoupper(trim($_POST['expense_status']));
    $end_date = trim($_POST['end_date']);
    $reference = trim($_POST['reference']);
    
    // Retrieve form data
$mv_num = $_POST['mv_num'] ?? null;
$ride_km = $_POST['ride_km'] ?? null;
$person_name = $_POST['person_name'] ?? null;
$period = $_POST['period'] ?? null;


    // Validation
    if (!empty($amount) && !is_numeric($amount)) {
        $errors[] = "Invalid amount";
    }

    if (!empty($cheque_no) && !preg_match("/^[0-9]+$/", $cheque_no)) {
        $errors[] = "Invalid cheque number";
    }
    
    if ($pay_mode === 'Cheque') {
        if (empty($bank_name) || !preg_match("/^[A-Za-z ]+$/", $bank_name)) {
            $errors[] = "Invalid Bank Name";
        }
        if (empty($cheque_no) || !is_numeric($cheque_no)) {
            $errors[] = "Invalid Cheque Number";
        }
        if (empty($cheque_dt)) {
            $errors[] = "Cheque Date is required";
        }
    }
    
    
    if (!empty($details) && !preg_match("/^[A-Za-z0-9\/\s\-\(\),.]+$/", $details)) {
        $errors[] = "Invalid details";
    }
    
    if (empty($expense_type) || !preg_match("/^[A-Za-z0-9\/\s\-\(\),.]+$/", $expense_type)) {
        $errors[] = "Invalid Expense Type";
    }
    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $time = date('H:i:s'); // Get current date and time in Asia/Kolkata timezone
    

    // echo "<pre>";
    // print_r($_POST);
    // exit;

    // If no errors, process the form
   if (empty($errors)) {
    if ($is_edit) {
        $expense_type = $_POST['expense_type'] === 'Enter Manually' ? $_POST['expenseCustomtype'] : $_POST['expense_type'];
        $mv_num = $_POST['mv_num'] === 'Enter Manually' ? $_POST['mvcustom'] : $_POST['mv_num'];
        $consumer_num = $_POST['consumer_num'] === 'Enter Manually' ? $_POST['consumer_custom'] : $_POST['consumer_num'];
        $telephone_num = $_POST['telephone_num'] === 'Enter Manually' ? $_POST['telephone_custom'] : $_POST['telephone_num'];
        $internet = $_POST['internet'] === 'Enter Manually' ? $_POST['internet_custom'] : $_POST['internet'];
        $branch_name = $_POST['branch_name'] === 'Enter Manually' ? $_POST['branch_name_custom'] : $_POST['branch_name'];
        $bank_name = $_POST['bank_name'] === 'Enter Manually' ? $_POST['bank_name_custom'] : $_POST['bank_name'];

        // Update existing entry
        $sql = "UPDATE expenses SET reg_num='$reg_num', policy_date='$policy_date',time='$time', amount='$amount', pay_mode='$pay_mode', cheque_no='$cheque_no', bank_name='$bank_name', cheque_dt='$cheque_dt', expense_type='$expense_type', ride_km='$ride_km', fuel='$fuel', details='$details', expenseCustomtype='$expenseCustomtype', mv_num='$mv_num', user_name='$user_name', liter='$liter', remark='$remark', person_name='$person_name', period='$period',vehicle_type='$vehicle_type',consumer_num='$consumer_num',telephone_num='$telephone_num',internet='$internet',milkat_num='$milkat_num',pro_description='$pro_description',pro_location='$pro_location',survey_no='$survey_no',branch_name = '$branch_name', expense_status = '$expense_status', end_date = '$end_date' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: expense.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }  
    else {
        // username for who fill form
        $username = $_SESSION['username'];

        // Create unique submission identifier FIRST
        $submission_hash = md5($reg_num . $expense_type . $creation_on . $username);
        
        // Check if this submission was already processed in current session
        if (isset($_SESSION['expenses_processed_submissions'][$submission_hash])) {
            header("Location: expense?success=1");
            exit();
        }

        // Check for duplicate entry in database FIRST
        $check_duplicate = "SELECT id FROM expenses WHERE reg_num = '$reg_num' AND expense_type = '$expense_type' AND creation_on = '$creation_on'";
        $result = $conn->query($check_duplicate);

        if ($result->num_rows > 0) {
            // Mark as processed and redirect silently
            $_SESSION['expenses_processed_submissions'][$submission_hash] = true;
            header("Location: expense?success=1");
            exit();
        }

        $expense_type = strtoupper(trim($_POST['expense_type'] ?? ''));
        $expenseCustomtype = strtoupper(trim($_POST['expenseCustomtype'] ?? ''));
        if ($expense_type == "Enter Manually") {
            $expense_type = strtoupper($expenseCustomtype);
        }

        // Capture data from the form
        $mv_num = $_POST['mv_num'];
        $mvcustom = $_POST['mvcustom'] ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($mv_num == "Enter Manually") {
            $mv_num = $mvcustom;
        }

        // Capture data from the form
        $consumer_num = $_POST['consumer_num'];
        $consumer_custom = $_POST['consumer_custom'] ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($consumer_num == "Enter Manually") {
            $consumer_num = $consumer_custom;
        }
         
        // Capture data from the form
        $telephone_num = $_POST['telephone_num'];
        $telephone_custom = $_POST['telephone_custom'] ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($telephone_num == "Enter Manually") {
            $telephone_num = $telephone_custom;
        }
         
        // Capture data from the form
        $internet = $_POST['internet'];
        $internet_custom = $_POST['internet_custom'] ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($internet == "Enter Manually") {
            $internet = $internet_custom;
        }

        // SQL query using prepared statements
        $stmt = $conn->prepare("INSERT INTO expenses (reg_num, policy_date, time, amount, pay_mode, cheque_no, bank_name, cheque_dt, expense_type, ride_km, fuel, details, username, creation_on, mv_num, user_name, liter, remark, person_name, period,vehicle_type,consumer_num,telephone_num,internet,milkat_num,pro_description,pro_location,survey_no,branch_name,consumer_number,expense_status,end_date,reference)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssssssssssssssssssssssssss", $reg_num, $policy_date, $time, $amount, $pay_mode, $cheque_no, $bank_name, $cheque_dt, $expense_type, $ride_km, $fuel, $details, $username, $creation_on, $mv_num, $user_name, $liter, $remark, $person_name, $period,$vehicle_type,$consumer_num,$telephone_num,$internet,$milkat_num,$pro_description,$pro_location,$survey_no,$branch_name,$consumer_number,$expense_status,$end_date,$reference);
        
        // Execute the statement once
        if ($stmt->execute()) {
            // Mark this submission as processed
            $_SESSION['expenses_processed_submissions'][$submission_hash] = true;
            
            // Get inserted Expense ID
            $expense_id = $stmt->insert_id;
    
            // Insert a Reminder
            $reminder_stmt = $conn->prepare("INSERT INTO expenses_reminders (expense_id, reminder_date) VALUES (?, ?)");
            $reminder_stmt->bind_param("is", $expense_id, $end_date);
            $reminder_stmt->execute();
    
            // Redirect after successful insertion
            header("Location: expense?success=1&id=" . $expense_id);
            exit();
        } else {
            echo "Error: " . $stmt->error;
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
                <h1>Expenses Register</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="expense">Expenses</a></li>
                <li class="breadcrumb-item active" aria-current="page">Expenses Register</li>
              </ol>
            </nav>
        </div>
         <form 
            action="expense-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
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
                  <!-- Register Number (Auto Generated) -->
                <div class="col-md-6 field">
                    <label for="registerNumber" class="form-label">Register Number</label>
                    <input type="text" class="form-control" name="reg_num" id="registerNumber" value="<?= htmlspecialchars($reg_num) ?>" readonly >
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
                            echo htmlspecialchars(date('H:i', strtotime($policy_date))); // Show existing time in edit mode
                        } else {
                            echo date('H:i'); // Show current time for adding a new entry
                        } 
                    ?>" required readonly>
            </div>
                    
              </div>


            
            
            <div class="row g-3 mb-3">
                
                <!-- Enter Amount -->
            <div class="col-md-6 field">
                <label for="expenseAmount" class="form-label">Enter Amount</label>
                <input type="number" class="form-control" name="amount" value="<?= htmlspecialchars($amount) ?>" id="expenseAmount" placeholder="Enter Amount" required>
            </div>
        <!-- Payment Mode -->
        <div class="col-md-6 field">
                <label for="paymentMode" class="form-label">Payment Mode</label>
                <select class="form-select" name="pay_mode" id="paymentMode" onchange="toggleChequeFields()">
                    <option value="Cash" <?php if ($is_edit && $pay_mode == 'Cash') echo 'selected'; ?>>Cash</option>
                    <option value="Cheque" <?php if ($is_edit && $pay_mode == 'Cheque') echo 'selected'; ?>>Cheque</option>
                    <option value="Payment Link" <?php if ($is_edit && $pay_mode == 'Payment Link') echo 'selected'; ?>>Payment Link</option>
                    <option value="Online" <?php if ($is_edit && $pay_mode == 'Online') echo 'selected'; ?>>Online</option>
                    <option value="RTGS/NEFT" <?php if ($is_edit && $pay_mode == 'RTGS/NEFT') echo 'selected'; ?>>RTGS/NEFT</option>
                    <option value="Credit Card" <?php if ($is_edit && $pay_mode == 'Credit Card') echo 'selected'; ?>>Credit Card</option>
                </select>
            </div>
            
            <!-- Hidden fields for cheque details -->
            <div id="chequeDetails" style="display:<?php echo ($is_edit && $pay_mode == 'Cheque') ? 'block' : 'none'; ?>;">
                <div class="row g-3">
                    <div class="col-md-3 field mt-3">
                        <label for="chequeNumber" class="form-label">Cheque Number</label>
                        <input type="text" class="form-control" name="cheque_no" value="<?= htmlspecialchars($cheque_no) ?>" id="chequeNumber" placeholder="Enter Cheque Number">
                    </div>
                    <div class="col-md-3 field">
                        <label for="policyCompany" class="form-label">Bank Name</label>
                        <select class="form-select" name="bank_name" id="bank" onchange="handleBankNameChange()">
                            <!-- Default option for a new form -->
                            <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                            
                            
                            <!-- Dynamically add options from the database -->
                            <?php
                            $query = "SELECT DISTINCT bank_name FROM expenses";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                if (!empty($row['bank_name'])) {
                                    $bank_name_option = strtoupper($row['bank_name']); // convert to uppercase for consistency
                                    $selected = ($is_edit && strtoupper($bank_name) == $bank_name_option) ? 'selected' : '';
                                    echo "<option value='{$row['bank_name']}' $selected>{$row['bank_name']}</option>"; // original case for display
                                }
                            }
                            ?>
                            
                            <!-- Option to enter manually -->
                            <option value="Enter Manually" <?php echo ($is_edit && empty($bank_name) && !empty($bank_name_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                        </select>
                        
                        <!-- Input for manual entry -->
                        <input type="text" name="bank_name_custom" value="<?php echo isset($bank_name_custom) && htmlspecialchars($bank_name_custom); ?>" class="form-control mt-2" id="bankcustom" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($bank_name) && !empty($bank_name_custom)) ? 'block' : 'none'; ?>;">

                    </div>

                    <div class="col-md-3 field">
                        <label for="policyCompany" class="form-label">Branch Name</label>
                        <select class="form-select" name="branch_name" id="branch_name" onchange="handleBranchNameChange()">
                            <!-- Default option for a new form -->
                            <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                            
                            
                            <!-- Dynamically add options from the database -->
                            <?php
                            $query = "SELECT DISTINCT branch_name FROM expenses";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                if (!empty($row['branch_name'])) {
                                    $branch_name_option = strtoupper($row['branch_name']); // convert to uppercase for consistency
                                    $selected = ($is_edit && strtoupper($branch_name) == $branch_name_option) ? 'selected' : '';
                                    echo "<option value='{$row['branch_name']}' $selected>{$row['branch_name']}</option>"; // original case for display
                                }
                            }
                            ?>
                            
                            <!-- Option to enter manually -->
                            <option value="Enter Manually" <?php echo ($is_edit && empty($branch_name) && !empty($branch_name_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                        </select>
                        
                        <!-- Input for manual entry -->
                        <input type="text" name="branch_name_custom" value="<?php echo isset($branch_name_custom) && htmlspecialchars($branch_name_custom); ?>" class="form-control mt-2" id="branchcustom" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($branch_name) && !empty($branch_name_custom)) ? 'block' : 'none'; ?>;">

                    </div>
                    <div class="col-md-3 field mt-3">
                        <label for="chequeDate" class="form-label">Cheque Date</label>
                        <input type="date" class="form-control" name="cheque_dt" value="<?= htmlspecialchars($cheque_dt) ?>" id="chequeDate">
                    </div>
                </div>
            </div>
        </div>


        <div class="row g-3 mb-3">


            <div class="col-md-3 field">
                <label for="paymentMode" class="form-label">Expense Status</label>
                <select class="form-select" name="expense_status">
                    <option value="General" <?php if ($is_edit && $expense_status == 'GENERAL') echo 'selected'; ?>>General</option>
                    <option value="Fix" <?php if ($is_edit && $expense_status == 'FIX') echo 'selected'; ?>>Fix</option>
                </select>
            </div>

            <div class="col-md-3 field">
                    <label for="date" class="form-label">End Date (For Reminder)</label>
                    <input type="date" class="form-control text-success" name="end_date" id="date" 
                        value="<?php 
                            if ($is_edit) {
                                echo htmlspecialchars(date('Y-m-d', strtotime($end_date))); // Show existing date in edit mode
                            } else {
                                echo date('Y-m-d'); // Show current date for adding a new entry
                            } 
                        ?>" required>
                </div>
            
        
            <!-- Type of Expense -->
            <div class="col-md-6 field">
                <label for="expenseType" class="form-label">Type of Expense</label>
                <select class="form-select" name="expense_type" id="expenseType" onchange="typeExpense()">
                    <option value="" selected>Select Expense Type</option>
                    <option value="Fuel" <?php if ($is_edit && $expense_type === 'FUEL') echo 'selected'; ?>>Fuel</option>
                    <option value="Vehicle Insurance" <?php if ($is_edit && $expense_type === 'VEHICLE INSURANCE') echo 'selected'; ?>>Vehicle Insurance</option>
                    <option value="Vehicle Maintenance" <?php if ($is_edit && $expense_type === 'VEHICLE MAINTENANCE') echo 'selected'; ?>>Vehicle Maintenance</option>
                    <option value="Salary" <?php if ($is_edit && $expense_type === 'SALARY') echo 'selected'; ?>>Salary</option>
                    <option value="MSEB" <?php if ($is_edit && $expense_type === 'MSEB') echo 'selected'; ?>>MSEB</option>
                    <option value="Telephone" <?php if ($is_edit && $expense_type === 'TELEPHONE') echo 'selected'; ?>>Telephone</option>
                    <option value="Internet" <?php if ($is_edit && $expense_type === 'INTERNET') echo 'selected'; ?>>Internet</option>
                    <option value="Stationary" <?php if ($is_edit && $expense_type === 'STATIONARY') echo 'selected'; ?>>Stationary</option>
                    <option value="Staff Insurance" <?php if ($is_edit && $expense_type === 'STAFF INSURANCE') echo 'selected'; ?>>Staff Insurance</option>
                    <option value="Property Insurance" <?php if ($is_edit && $expense_type === 'PROPERTY INSURANCE') echo 'selected'; ?>>Property Insurance</option>
                    <option value="Office Accessories" <?php if ($is_edit && $expense_type === 'OFFICE ACCESSORIES') echo 'selected'; ?>>Office Accessories</option>
                    <option value="Tea Bill" <?php if ($is_edit && $expense_type === 'TEA BILL') echo 'selected'; ?>>Tea Bill</option>
                    <option value="Water Bill" <?php if ($is_edit && $expense_type === 'WATER BILL') echo 'selected'; ?>>Water Bill</option>
                    <option value="Entertainment" <?php if ($is_edit && $expense_type === 'ENTERTAINMENT') echo 'selected'; ?>>Entertainment</option>
                    <option value="Property Taxes" <?php if ($is_edit && $expense_type === 'PROPERTY TAXES') echo 'selected'; ?>>Property Taxes</option>
                    <option value="LIC Premiums" <?php if ($is_edit && $expense_type === 'LIC PREMIUMS') echo 'selected'; ?>>LIC Premiums</option>
                    <option value="GIC Premiums" <?php if ($is_edit && $expense_type === 'GIC PREMIUMS') echo 'selected'; ?>>GIC Premiums</option>
                    <option value="EMI" <?php if ($is_edit && $expense_type === 'EMI') echo 'selected'; ?>>EMI</option>
                    <option value="Charity" <?php if ($is_edit && $expense_type === 'CHARITY') echo 'selected'; ?>>Charity</option>
                    <option value="Others" <?php if ($is_edit && $expense_type === 'OTHERS') echo 'selected'; ?>>Others</option>

                    <?php
                    // Dynamically add custom options from the database
                    $query = "SELECT DISTINCT expense_type FROM expenses WHERE expense_type NOT IN ('Fuel', 'Vehicle Insurance', 'Vehicle Maintenance', 'Salary', 'MSEB', 'Telephone', 'Internet', 'Stationary', 'Staff Insurance', 'Property Insurance', 'Office Accessories', 'Tea Bill', 'Water Bill', 'Entertainment', 'Property Taxes', 'LIC Premiums', 'GIC Premiums', 'EMI', 'Charity', 'Others')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $dynamic_expense_type = strtoupper($row['expense_type']);
                        echo "<option value='{$dynamic_expense_type}'" . ($is_edit && $expense_type === $dynamic_expense_type ? ' selected' : '') . ">{$row['expense_type']}</option>";
                    }
                    ?>

                    <option value="Enter Manually" <?php echo ($is_edit && empty($expense_type) && !empty($expenseCustomtype)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>

                <input type="text" name="expenseCustomtype" value="<?php echo htmlspecialchars($expenseCustomtype); ?>" class="form-control mt-2" id="expenseCustomtype" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($expense_type) && !empty($expenseCustomtype)) ? 'block' : 'none'; ?>;">
            </div>
        
            
            
            
            
            
            
            
            <!-- MV Number Field -->
            
            <div id="mvNumberField" style="display: <?php echo ($is_edit && ($expense_type == 'FUEL' || $expense_type == 'VEHICLE INSURANCE' || $expense_type == 'VEHICLE MAINTENANCE')) ? 'block' : 'none'; ?>;" >
                
        
                <div class="row">

                <div class="col-md-6 field position-relative">
                    <label for="mvnumber" class="form-label">MV Number</label>
                    <div class="d-flex align-items-center">
                        <select class="form-select" name="mv_num" id="mvnumber" onchange="handleMvNumChange(this.value)">
                            <option value="" <?php echo (!$is_edit || empty($mv_num)) ? 'selected' : ''; ?> >Select MV Number</option>
                            <?php
                            // Query to fetch distinct vehicle numbers (MV Numbers)
                            $query = "SELECT DISTINCT id, mv_num FROM vehicle_details";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                // If editing, select the current MV number
                                $selected = ($is_edit && isset($mv_num) && $mv_num == $row['mv_num']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['mv_num']) . "' $selected>" . htmlspecialchars($row['mv_num']) . "</option>";
                            }
                            ?>
                        </select>

                        <a href="mv_add" type="button" class="btn btn-success ms-2 p-0">
                            Add
                        </a>

                        <a type="button" class="btn btn-info ms-2 p-0" onclick="editDropdown()">
                            Edit
                        </a>

                        

                    </div>
                </div>





                    <div class="col-md-6 field">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <input type="text" name="vehicle_type" value="<?php echo htmlspecialchars($vehicle_type); ?>" class="form-control" id="vehicle_type" readonly>
                    </div>

                    

                </div>



            
            </div>
            

        <div id="PropertyTaxField" style="display: <?php echo ($is_edit && $expense_type == 'PROPERTY TAXES' ) ? 'block' : 'none'; ?>;" >
                
            <div class="row">
                
            <div class="col-md-6 field position-relative">
                <label for="milkatnumber" class="form-label">Milkat Number</label>
                <div class="d-flex align-items-center">
                    <select class="form-select" name="milkat_num" id="milkatnumber" onchange="handlePropertyChange(this.value)">
                        <option value="" <?php echo (!$is_edit || empty($milkat_num)) ? 'selected' : ''; ?>>Select Milkat Number</option>
                        <?php
                        // Query to fetch distinct Milkat numbers from the database
                        $query = "SELECT DISTINCT id, milkat_no FROM property_details";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            // If editing, select the current Milkat number
                            $selected = ($is_edit && isset($milkat_num) && $milkat_num == $row['milkat_no']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['milkat_no']) . "' $selected>" . htmlspecialchars($row['milkat_no']) . "</option>";
                        }
                        ?>
                    </select>

                    <a href="property_add" class="btn btn-success ms-2 p-0">
                        Add
                    </a>

                    <a type="button" class="btn btn-info ms-2 p-0" onclick="editPropDropdown()">
                        Edit
                    </a>
                </div>
            </div>

                

                

                <div class="col-md-6 field">
                    <label for="vehicle_type" class="form-label">Description</label>
                    <input type="text" name="pro_description" value="<?php echo htmlspecialchars($pro_description); ?>" class="form-control" id="description" readonly>
                </div>

                <div class="col-md-6 field">
                    <label for="vehicle_type" class="form-label">Location</label>
                    <input type="text" name="pro_location" value="<?php echo htmlspecialchars($pro_location); ?>" class="form-control" id="location" readonly>
                </div>

                <div class="col-md-12 field">
                    <label for="vehicle_type" class="form-label">Survay Number</label>
                    <input type="text" name="survey_no" value="<?php echo htmlspecialchars($survey_no); ?>" class="form-control" id="survey_no" readonly>
                </div>
            </div>
                
        </div>

            
            <div id="periodField" style="display: <?php echo ($is_edit && ($expense_type == 'SALARY' || $expense_type == 'MSEB' || $expense_type == 'TELEPHONE' || $expense_type == 'INTERNET')) ? 'block' : 'none'; ?>;" >
            
                <div class="col-md-6 field">
                    <label for="chequeNumber" class="form-label">Month/Year</label>
                    <input type="text" class="form-control" name="period" value="<?= htmlspecialchars($period) ?>" id="chequeNumber" placeholder="Eg.00/0000">
                </div>
            
            </div>
            
        <!-- KM Field -->
            <div id="kmField" class="col-md-6 field mt-3" style="display: <?php echo ($is_edit && ($expense_type == 'FUEL' || $expense_type == 'VEHICLE MAINTENANCE')) ? 'block' : 'none'; ?>;">

                <label for="chequeNumber" class="form-label">KM</label>
                <input type="text" class="form-control" name="ride_km" value="<?= htmlspecialchars($ride_km) ?>" id="chequeNumber" placeholder="Enter KM">

            </div>
            
            
            <!-- Hidden fields for Travel-related details -->   
            
            <!-- Fuel-Specific Fields -->
            <div id="fuelSpecificFields" style="display: <?php echo ($is_edit && $expense_type == 'FUEL') ? 'block' : 'none'; ?>;">
                <div class="row">
                    

                <div class="col-md-6 field">
                    <label for="user_name" class="form-label">User Name</label>
                    <input type="text" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" class="form-control" id="user_name" readonly>
                </div>
                

                    <div class="col-md-6 field">
                        <label for="paymentMode" class="form-label">Fuel Type</label>
                        <select class="form-select" name="fuel" id="paymentMode">
                            <option value="" selected>Select Fuel Type</option>
                            <option value="Diesel" <?php if ($is_edit && $fuel == 'Diesel') echo 'selected'; ?>>Diesel</option>
                            <option value="Petrol" <?php if ($is_edit && $fuel == 'Petrol') echo 'selected'; ?>>Petrol</option>
                            <option value="CNG" <?php if ($is_edit && $fuel == 'CNG') echo 'selected'; ?>>CNG</option>
                        </select>
                    </div>
                    <div class="col-md-6 field mt-3">
                        <label for="bankName" class="form-label">Liter</label>
                        <input type="text" class="form-control" name="liter" value="<?= htmlspecialchars($liter) ?>" id="bankName" placeholder="Enter Liter">
                    </div>
                    <div class="col-md-6 field mt-3">
                        <label for="chequeNumber" class="form-label">Remark</label>
                        <input type="text" class="form-control" name="remark" value="<?= htmlspecialchars($remark) ?>" id="chequeNumber" placeholder="Enter Remark">
                    </div>
                </div>
            </div>
            
        
            <div id="salarySpecificFields" style="display: <?php echo ($is_edit && $expense_type == 'SALARY' ) ? 'block' : 'none'; ?>;">


        
                    
                    <div class="col-md-6 field mt-3">
                        <label for="chequeNumber" class="form-label">Person Name</label>
                        <input type="text" class="form-control" name="person_name" value="<?= htmlspecialchars($person_name) ?>" id="chequeNumber" placeholder="Enter Person Name">
                    </div>
                
                
                
            </div>
            
                <div id="msebnumber" style="display: <?php echo ($is_edit && $expense_type == 'MSEB') ? 'block' : 'none'; ?>;">

                    <div class="col-md-6 field">
                        <label for="expenseType" class="form-label">Consumer Number</label>
                        <select class="form-select" name="consumer_num" id="consumer_num" onchange="handleConsumerChange()">
                            <option value="" selected>Select Consumer Number</option>

                            <option value="073034080892 (OFFICE GURUKRUPA)" <?php if ($is_edit && $consumer_num == '073034080892 (OFFICE GURUKRUPA)') echo 'selected'; ?>>073034080892 (OFFICE GURUKRUPA)</option>

                            <option value="073030075223 (HOME PB)" <?php if ($is_edit && $consumer_num == '073030075223 (HOME PB)') echo 'selected'; ?>>073030075223 (HOME PB)</option>

                            <option value="049018566337 (NSK HOME)" <?php if ($is_edit && $consumer_num == '049018566337 (NSK HOME)') echo 'selected'; ?>>049018566337 (NSK HOME) </option>

                            <option value="073030052851 (BALAJI VYAPAR BHAVAN)" <?php if ($is_edit && $consumer_num == '073030052851 (BALAJI VYAPAR BHAVAN)') echo 'selected'; ?>>073030052851 (BALAJI VYAPAR BHAVAN)</option>

                            <option value="073030052801 (CHAVAN)" <?php if ($is_edit && $consumer_num == '073030052801 (CHAVAN)') echo 'selected'; ?>>073030052801 (CHAVAN)</option>

                            <!-- Dynamically add custom options here -->
                            <?php
                            $query = "SELECT DISTINCT consumer_num FROM expenses WHERE consumer_num NOT IN ('073034080892 (OFFICE GURUKRUPA)', '073030075223 (HOME PB)', '049018566337 (NSK HOME)', '073030052851 (BALAJI VYAPAR BHAVAN)', '073030052801 (CHAVAN)')";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['consumer_num']}'>{$row['consumer_num']}</option>";
                            }
                            ?>
                            
                            <!-- Option to enter manually -->
                                <option value="Enter Manually" <?php echo ($is_edit && empty($consumer_num) && !empty($consumer_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                            
                        </select>
                    
                        <!-- Input for manual entry -->
                            <input type="text" name="consumer_custom" value="<?php echo isset($consumer_custom) && htmlspecialchars($consumer_custom); ?>" class="form-control mt-2" id="consumer_custom" placeholder="Enter consumer Manually" style="display:<?php echo ($is_edit && empty($consumer_num) && !empty($consumer_custom)) ? 'block' : 'none'; ?>;">

                    </div> 
            
                </div>

                <div id="Telephone" style="display: <?php echo ($is_edit && $expense_type == 'TELEPHONE') ? 'block' : 'none'; ?>;">

                    <div class="col-md-6 field">
                        <label for="expenseType" class="form-label">Telephone Number</label>
                        <select class="form-select" name="telephone_num" id="telephone_num" onchange="handleTelephoneChange()">
                            <option value="" selected>Select Telephone Number</option>

                            <option value="253453" <?php if ($is_edit && $telephone_num == '253453') echo 'selected'; ?>>253453</option>
                            <option value="MOBILE ALL" <?php if ($is_edit && $telephone_num == 'MOBILE ALL') echo 'selected'; ?>>MOBILE ALL</option>
                            <option value="MOBILE 9422246469" <?php if ($is_edit && $telephone_num == 'MOBILE 9422246469') echo 'selected'; ?>>MOBILE 9422246469</option>

                            

                            <!-- Dynamically add custom options here -->
                            <?php
                            $query = "SELECT DISTINCT telephone_num FROM expenses WHERE telephone_num NOT IN ('253453','MOBILE ALL','MOBILE 9422246469')";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['telephone_num']}'>{$row['telephone_num']}</option>";
                            }
                            ?>
                            
                            <!-- Option to enter manually -->
                                <option value="Enter Manually" <?php echo ($is_edit && empty($telephone_num) && !empty($consumer_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                            
                        </select>
                    
                        <!-- Input for manual entry -->
                            <input type="text" name="telephone_custom" value="<?php echo isset($telephone_custom) && htmlspecialchars($telephone_custom); ?>" class="form-control mt-2" id="telephone_custom" placeholder="Enter Telephone Number Manually" style="display:<?php echo ($is_edit && empty($telephone_num) && !empty($telephone_custom)) ? 'block' : 'none'; ?>;">

                    </div> 
            
                </div>

                <div id="Internet" style="display: <?php echo ($is_edit && $expense_type == 'INTERNET') ? 'block' : 'none'; ?>;">
                
                
            <div class="row">

                <div class="col-md-4 field position-relative">
    <label for="Internet" class="form-label">Internet</label>
    <div class="d-flex align-items-center">
        <select class="form-select" name="internet" id="internet" onchange="fetchConsumerNumber(this.value)">
            <option value="" <?php echo (!$is_edit || empty($internet)) ? 'selected' : ''; ?>>Select Internet</option>
            <?php
            $query = "SELECT DISTINCT id, internet FROM internet_details";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $selected = ($is_edit && isset($internet) && $internet == $row['internet']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($row['internet']) . "' $selected>" . htmlspecialchars($row['internet']) . "</option>";
            }
            ?>
        </select>   

        <a href="internet_add" class="btn btn-success ms-2 p-0">Add</a>
        <a type="button" class="btn btn-info ms-2 p-0" onclick="editInternetDropdown()">Edit</a>
    </div>
</div>

<div class="col-md-4 field">
    <label for="consumer_number" class="form-label">Consumer Number</label>
    <input type="text" name="consumer_number" value="<?php echo htmlspecialchars($consumer_number); ?>" class="form-control" id="consumer_number" readonly>
</div>

<div class="col-md-4 field">
    <label for="reference" class="form-label">Referance</label>
    <input type="text" name="reference" value="<?php echo htmlspecialchars($reference); ?>" class="form-control" id="reference" readonly>
</div>
</div>

                </div>


                
            
                

        </div>

            <!-- Enter Details/Narration -->
            <div class="mb-3 field">
                <label for="expenseDetails" class="form-label">Enter Details/Narration</label>
                <textarea class="form-control" name="details" id="expenseDetails" rows="3" placeholder="Enter details or narration"><?= htmlspecialchars($details) ?></textarea>
            </div>

            <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
            
            <!-- Show delete button only in edit mode -->
            <?php //if ($is_edit): ?>
                <!--<button type="submit" name="delete" class="btn sub-btn" onclick="return confirm('Are you sure you want to delete this entry?');">Delete Entry</button>-->
            <?php //endif; ?>
            
            
            
            
        </form>
    </div>
</section>




<?php
$conn->close();
?>

<script>

// This function is called when the 'Edit' button is clicked.
    function editDropdown() {
        const select = document.getElementById('mvnumber');
        const selectedId = select.value;

        if (!selectedId) {
            alert("Please select an MV Number to edit.");
            return;
        }

        // Redirect to the edit form with the selected ID
        window.location.href = `mv_add.php?action=edit&mv_num=${selectedId}`;
    }

    // This function fetches vehicle details when the user selects an MV number
    async function handleMvNumChange(mv_num) {
        if (!mv_num) return;  // Exit if no ID is selected

        try {
            const url = './fetch_vehicle_details.php'; // The PHP script that fetches vehicle details
            const response = await fetch(url, {
                method: 'POST', // HTTP method
                headers: {
                    'Content-Type': 'application/json', // Send JSON data
                },
                body: JSON.stringify({ mv_num: mv_num }) // Send the selected vehicle ID
            });

            const data = await response.json(); // Parse the JSON response

            if (data.success) {
                // Populate the fields with fetched vehicle data
                document.getElementById('vehicle_type').value = data.vehicle_type;
                document.getElementById('user_name').value = data.user_name;
            } else {
                alert("Vehicle details not found.");
            }
        } catch (error) {
            console.log("Error fetching vehicle details:", error);
        }
    }


// Function for redirecting to the edit page with the selected Milkat Number ID
function editPropDropdown() {
    const select = document.getElementById('milkatnumber');
    const selectedId = select.value;

    if (!selectedId) {
        alert("Please select a Milkat Number to edit.");
        return;
    }

    // Redirect to the edit form page with the selected ID as a query parameter
    window.location.href = `property_add.php?action=edit&milkat_no=${selectedId}`;
}

// Function to fetch property details based on the selected Milkat ID
async function handlePropertyChange(milkat_no) {
    if (!milkat_no) return; // Exit if no ID is selected

    try {
        const url = './fetch_property_details.php'; // The PHP script to fetch property details
        const response = await fetch(url, {
            method: 'POST', // HTTP method
            headers: {
                'Content-Type': 'application/json', // Send JSON data
            },
            body: JSON.stringify({ milkat_no: milkat_no }) // Send the selected property ID
        });

        const data = await response.json(); // Parse the JSON response

        if (data.success) {
            // Populate the fields with fetched property data
            document.getElementById('description').value = data.description;
            document.getElementById('location').value = data.location;
            document.getElementById('survey_no').value = data.survey_no;
        } else {
            alert("Property details not found.");
        }
    } catch (error) {
        console.log("Error fetching Property details:", error);
    }
}


// Function for redirecting to the edit page with the selected Milkat Number ID
function editInternetDropdown() {
    const select = document.getElementById('internet');
    const selectedId = select.value;

    if (!selectedId) {
        alert("Please select a internet to edit.");
        return;
    }

    // Redirect to the edit form page with the selected ID as a query parameter
    window.location.href = `internet_add.php?action=edit&internet=${selectedId}`;
}


// Function to fetch property details based on the selected Milkat ID
function fetchConsumerNumber(internetValue) {
    if (internetValue !== "") {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "fetch_internet_details.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    let response = JSON.parse(xhr.responseText);
                    document.getElementById("consumer_number").value = response.consumer_number || "";
                    document.getElementById("reference").value = response.reference || "";
                } catch (e) {
                    console.error("Invalid JSON response:", xhr.responseText);
                }
            }
        };

        xhr.send("internet=" + encodeURIComponent(internetValue));
    } else {
        document.getElementById("consumer_number").value = "";
        document.getElementById("reference").value = "";
    }
}



    

</script>


<script>
    
    function typeExpense() {
        const expenseType = document.getElementById('expenseType').value;
        var companySelect = document.getElementById('expenseType');
        var customCompanyInput = document.getElementById('expenseCustomtype');

        // Show/Hide MV Number field
        document.getElementById('mvNumberField').style.display =
            (expenseType === 'Fuel' || expenseType === 'Vehicle Insurance' || expenseType === 'Vehicle Maintenance') ? 'block' : 'none';

        // Show/Hide KM field
        document.getElementById('kmField').style.display =
            (expenseType === 'Fuel' || expenseType === 'Vehicle Maintenance') ? 'block' : 'none';

        // Show/Hide Fuel-specific fields
        document.getElementById('fuelSpecificFields').style.display =
            (expenseType === 'Fuel') ? 'block' : 'none';

        // Show/Hide Property Tax-specific fields
        document.getElementById('PropertyTaxField').style.display =
            (expenseType === 'Property Taxes') ? 'block' : 'none';
            
        // Show/Hide Salary-specific fields
        document.getElementById('salarySpecificFields').style.display =
            (expenseType === 'Salary') ? 'block' : 'none';
            
        // Show/Hide MV Number field
        document.getElementById('periodField').style.display =
            (expenseType === 'Salary' || expenseType === 'MSEB' || expenseType === 'Telephone' || expenseType === 'Internet') ? 'block' : 'none';

        // Show/Hide Salary-specific fields
        document.getElementById('msebnumber').style.display =
            (expenseType === 'MSEB') ? 'block' : 'none';

        // Show/Hide Salary-specific fields
        document.getElementById('Telephone').style.display =
            (expenseType === 'Telephone') ? 'block' : 'none';

        // Show/Hide Salary-specific fields
        document.getElementById('Internet').style.display =
            (expenseType === 'Internet') ? 'block' : 'none';
            
        // Show the manual input field if "Enter Manually" is selected
        if (companySelect.value === 'Enter Manually') {
            customCompanyInput.style.display = 'block';
            customCompanyInput.name = 'expense_type'; // Set the custom input to the same name as the dropdown
        } else {
            customCompanyInput.style.display = 'none';
            customCompanyInput.name = 'expenseCustomtype'; // Reset to avoid conflicts
        }
    }
    
    


//     Handle consumer_number selection
function handleConsumerChange() {
    var companySelect = document.getElementById('consumer_num');
    var customCompanyInput = document.getElementById('consumer_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'consumer_num'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'consumer_custom'; // Reset to avoid conflicts
    }
}

//     Handle telephone number selection
function handleTelephoneChange() {
    var companySelect = document.getElementById('telephone_num');
    var customCompanyInput = document.getElementById('telephone_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'telephone_num'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'telephone_custom'; // Reset to avoid conflicts
    }
}

//     Handle telephone number selection
function handleInternetChange() {
    var companySelect = document.getElementById('internet');
    var customCompanyInput = document.getElementById('internet_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'internet'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'internet_custom'; // Reset to avoid conflicts
    }
}

// Handle Bank Name selection
function handleBankNameChange() {
    var companySelect = document.getElementById('bank');
    var customCompanyInput = document.getElementById('bankcustom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'bank_name'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'bank_name_custom'; // Reset to avoid conflicts
    }
}

// Handle Branch Name selection
function handleBranchNameChange() {
    var companySelect = document.getElementById('branch_name');
    var customCompanyInput = document.getElementById('branchcustom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'branch_name'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'branch_name_custom'; // Reset to avoid conflicts
    }
}

</script>



<?php 
    include 'include/footer.php';
    include 'include/header1.php';
?>

<?php 
    
// }
?>