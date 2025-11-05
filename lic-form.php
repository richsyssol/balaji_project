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
$client_name = '';
$contact = '';
$policy_no = '';
$policy_amt = '';
$pay_mode = '';
$inv_status = '';
$cheque_no = '';
$bank_name = '';
$check_dt = '';
$remark = '';
$policy_num = '';
$work_status = '';
$collection_job = '';
$address = '';
$recov_amount = '';
$job_type = '';
$birth_date = '';
$form_status = '';
$colle_policy_num = '';
$adviser = '';
$our_agency_amt = '';
$other_agency_amt = '';
$branch_name = '';
$client_id = '';
$errors = [];
$is_edit = false;
$add_new = false;
$id = null; // Initialize $id variable

$currentYear = date('Y');
$currentMonth = date('m');

if ($currentMonth >= 4) { // April or later
    $fiscalYearStart = $currentYear;
    $fiscalYearEnd = $currentYear + 1;
} else { // Before April
    $fiscalYearStart = $currentYear - 1;
    $fiscalYearEnd = $currentYear;
}

$fiscalYearStartDate = "$fiscalYearStart-04-01";
$fiscalYearEndDate = "$fiscalYearEnd-03-31";

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM lic_entries WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $contact = $row['contact'];
        $policy_no = $row['policy_no'];
        $policy_amt = $row['policy_amt'];
        $pay_mode = $row['pay_mode'];
        $inv_status = $row['inv_status'];
        $cheque_no = $row['cheque_no'];
        $bank_name = strtoupper($row['bank_name']);
        $check_dt = $row['check_dt'];
        $remark = strtoupper($row['remark']);
        $policy_num = $row['policy_num'];
        $work_status = $row['work_status'];
        $collection_job = $row['collection_job'];
        $address = strtoupper($row['address']);
        $recov_amount = $row['recov_amount'];
        $job_type = strtoupper($row['job_type']);
        $birth_date = $row['birth_date'];
        $form_status = $row['form_status'];
        $colle_policy_num = $row['colle_policy_num'];
        $adviser = $row['adviser'];
        $our_agency_amt = $row['our_agency_amt'];
        $other_agency_amt = $row['other_agency_amt'];
        $branch_name = strtoupper($row['branch_name']);
    } else {
        die("Entry not found.");
    }
} 


elseif (isset($_GET['action']) && $_GET['action'] === 'add_client') {
    $is_edit = false;
    $add_new = true;

    // Fetch the maximum registration number for the current fiscal year
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM lic_entries WHERE policy_date BETWEEN '$fiscalYearStartDate' AND '$fiscalYearEndDate'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = isset($row['max_reg_num']) ? $row['max_reg_num'] + 1 : 1;
    } else {
        $reg_num = 1;
    }

    // Only fetch the client_name and contact if provided in the query
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
    // Fetch the maximum registration number for the current fiscal year
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM lic_entries WHERE policy_date BETWEEN '$fiscalYearStartDate' AND '$fiscalYearEndDate'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = isset($row['max_reg_num']) ? $row['max_reg_num'] + 1 : 1;
    } else {
        $reg_num = 1;
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
    $policy_no = isset($_POST['policy_no']) ? trim($_POST['policy_no']) : '';
    $policy_amt = isset($_POST['policy_amt']) ? trim($_POST['policy_amt']) : '';
    $pay_mode = isset($_POST['pay_mode']) ? trim($_POST['pay_mode']) : '';
    $inv_status = isset($_POST['inv_status']) ? trim($_POST['inv_status']) : '';
    $cheque_no = isset($_POST['cheque_no']) ? trim($_POST['cheque_no']) : '';
    $bank_name = isset($_POST['bank_name']) ? strtoupper(trim($_POST['bank_name'])) : '';
    $check_dt = isset($_POST['check_dt']) ? trim($_POST['check_dt']) : '';
    $remark = isset($_POST['remark']) ? strtoupper(trim($_POST['remark'])) : '';
    $policy_num = isset($_POST['policy_num']) ? trim($_POST['policy_num']) : '';
    $work_status = isset($_POST['work_status']) ? trim($_POST['work_status']) : '';
    $collection_job = isset($_POST['collection_job']) ? strtoupper(trim($_POST['collection_job'])) : '';
    $recov_amount = isset($_POST['recov_amount']) ? trim($_POST['recov_amount']) : '';
    $address = isset($_POST['address']) ? strtoupper(trim($_POST['address'])) : '';
    $job_type = isset($_POST['job_type']) ? strtoupper(trim($_POST['job_type'])) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $form_status = isset($_POST['form_status']) ? trim($_POST['form_status']) : '';
    $colle_policy_num = isset($_POST['colle_policy_num']) && is_array($_POST['colle_policy_num']) 
    ? array_map('strtoupper', array_map('trim', $_POST['colle_policy_num'])) 
    : [];
    $adviser = isset($_POST['adviser']) ? trim($_POST['adviser']) : '';
    $our_agency_amt = isset($_POST['our_agency_amt']) ? trim($_POST['our_agency_amt']) : '';
    $other_agency_amt = isset($_POST['other_agency_amt']) ? trim($_POST['other_agency_amt']) : '';
    $branch_name = isset($_POST['branch_name']) ? strtoupper(trim($_POST['branch_name'])) : '';
    $client_id = trim($_POST['client_id']);


    if ($collection_job === "Select Type Of Job") {
        $collection_job = ''; // Convert the placeholder to an empty string
    }
    
    if ($work_status === "Select Type Of Job") {
        $work_status = ''; // Convert the placeholder to an empty string
    }

    // Validation
    if (empty($client_name) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $client_name)) {
        $errors[] = "Invalid client name";
    }

    if (empty($contact) || !preg_match("/^\d{10}$/", $contact)) {
        $errors[] = "Invalid contact number";
    }

    
    if ($job_type == 'Collection'){
        
        if (!empty($policy_no) && !preg_match('/^[0-9a-zA-Z\/\-\s]+$/', $policy_no)) {
            $errors[] = "Invalid policy number.";
        }
        
        if (!empty($policy_amt) && !is_numeric($policy_amt)) {
            $errors[] = "Invalid Premium Amount";
        }
    }
    
    if ($job_type == 'Servicing Tasks'){
        if (!empty($work_status) || !preg_match("/^[A-Za-z ]+$/", $work_status)) {
            $errors[] = "Invalid Type Of Job";
        }
        
        if (!empty($policy_num) && !preg_match('/^[A-Za-z0-9\/\s\-\(\),.]+$/', $policy_num)) {
            $errors[] = "Invalid policy number.";
        }
    }
    
    if (!empty($remark) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $remark)) {
        $errors[] = "Invalid remark";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }

    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone



    // If no errors, process the form
   if (empty($errors)) {
    if ($is_edit) {
        // Ensure $colle_policy_num is JSON encoded for storage
        if (!empty($colle_policy_num) && is_array($colle_policy_num)) {
            $colle_policy_num_json = json_encode($colle_policy_num);
        } else {
            $colle_policy_num_json = json_encode([]);
        }
    
        // Update existing entry
        $sql = "UPDATE lic_entries SET 
                    reg_num='$reg_num', 
                    policy_date='$policy_date', 
                    client_name='$client_name', 
                    contact='$contact', 
                    policy_no='$policy_no', 
                    policy_amt='$policy_amt', 
                    pay_mode='$pay_mode', 
                    inv_status='$inv_status', 
                    cheque_no='$cheque_no', 
                    bank_name='$bank_name', 
                    check_dt='$check_dt', 
                    remark='$remark', 
                    policy_num='$policy_num', 
                    work_status='$work_status', 
                    address='$address', 
                    recov_amount='$recov_amount',
                    job_type='$job_type',
                    fiscal_year='$fiscalYear',
                    birth_date='$birth_date',
                    form_status='$form_status',
                    update_on='$update_on',
                    collection_job='$collection_job',
                    time='$time',
                    colle_policy_num='$colle_policy_num_json',
                    adviser='$adviser',
                    our_agency_amt='$our_agency_amt',
                    other_agency_amt='$other_agency_amt', 
                    branch_name='$branch_name' 
                WHERE id=$id";
    
        if ($conn->query($sql) === TRUE) {
            header("Location: lic");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
    elseif ($_GET['action']==='add_new') {
        if (!empty($colle_policy_num) && is_array($colle_policy_num)) {
            $colle_policy_num_json = json_encode($colle_policy_num);
        } else {
            $colle_policy_num_json = json_encode([]);
        }

        $username = $_SESSION['username'];

        // Create unique submission identifier
        $submission_hash = md5($client_id . $reg_num . $creation_on . $username);
        
        // Check if this submission was already processed in current session
        if (isset($_SESSION['lic_processed_submissions'][$submission_hash])) {
            header("Location: client?success=1");
            exit();
        }

        // Check for duplicate entry in database
        $check_duplicate = "SELECT id FROM lic_entries WHERE client_id = '$client_id' AND reg_num = '$reg_num' AND creation_on = '$creation_on'";
        $result = $conn->query($check_duplicate);

        if ($result->num_rows > 0) {
            // Mark as processed and redirect silently
            $_SESSION['lic_processed_submissions'][$submission_hash] = true;
            header("Location: client?success=1");
            exit();
        } else {
            // Use prepared statement for insertion
            $sql = "INSERT INTO lic_entries (client_id, reg_num, policy_date, client_name, contact, policy_no, policy_amt, pay_mode, inv_status, cheque_no, bank_name, check_dt, remark, policy_num, work_status, job_type, address, recov_amount, username, fiscal_year, birth_date, form_status, creation_on, collection_job, time, colle_policy_num, adviser, our_agency_amt, other_agency_amt, branch_name) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssssssssssssssssssssssssss", 
                $client_id, $reg_num, $policy_date, $client_name, $contact, $policy_no, 
                $policy_amt, $pay_mode, $inv_status, $cheque_no, $bank_name, $check_dt, 
                $remark, $policy_num, $work_status, $job_type, $address, $recov_amount, 
                $username, $fiscalYear, $birth_date, $form_status, $creation_on, 
                $collection_job, $time, $colle_policy_num_json, $adviser, $our_agency_amt, 
                $other_agency_amt, $branch_name
            );
            
            if ($stmt->execute()) {
                // Mark this submission as processed
                $_SESSION['lic_processed_submissions'][$submission_hash] = true;
                
                $last_id = $conn->insert_id;
                $_SESSION['last_submission'] = $last_id;
                $_SESSION['submission_time'] = time();
                
                header("Location: client?success=1&id=" . $last_id);
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
    else {
        if (!empty($colle_policy_num) && is_array($colle_policy_num)) {
            $colle_policy_num_json = json_encode($colle_policy_num);
        } else {
            $colle_policy_num_json = json_encode([]);
        }

        $username = $_SESSION['username'];

        // Create unique submission identifier
        $submission_hash = md5($client_id . $reg_num . $creation_on . $username);
        
        // Check if this submission was already processed in current session
        if (isset($_SESSION['lic_processed_submissions'][$submission_hash])) {
            header("Location: client?success=1");
            exit();
        }

        // Check for duplicate entry in database
        $check_duplicate = "SELECT id FROM lic_entries WHERE client_id = '$client_id' AND reg_num = '$reg_num' AND creation_on = '$creation_on'";
        $result = $conn->query($check_duplicate);

        if ($result->num_rows > 0) {
            // Mark as processed and redirect silently
            $_SESSION['lic_processed_submissions'][$submission_hash] = true;
            header("Location: client?success=1");
            exit();
        } else {
            // Use prepared statement for insertion
            $sql = "INSERT INTO lic_entries (client_id, reg_num, policy_date, client_name, contact, policy_no, policy_amt, pay_mode, inv_status, cheque_no, bank_name, check_dt, remark, policy_num, work_status, job_type, address, recov_amount, username, fiscal_year, birth_date, form_status, creation_on, collection_job, time, colle_policy_num, adviser, our_agency_amt, other_agency_amt, branch_name) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssssssssssssssssssssssssss", 
                $client_id, $reg_num, $policy_date, $client_name, $contact, $policy_no, 
                $policy_amt, $pay_mode, $inv_status, $cheque_no, $bank_name, $check_dt, 
                $remark, $policy_num, $work_status, $job_type, $address, $recov_amount, 
                $username, $fiscalYear, $birth_date, $form_status, $creation_on, 
                $collection_job, $time, $colle_policy_num_json, $adviser, $our_agency_amt, 
                $other_agency_amt, $branch_name
            );
            
            if ($stmt->execute()) {
                // Mark this submission as processed
                $_SESSION['lic_processed_submissions'][$submission_hash] = true;
                
                $last_id = $conn->insert_id;
                $_SESSION['last_submission'] = $last_id;
                $_SESSION['submission_time'] = time();
                
                header("Location: client?success=1&id=" . $last_id);
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
}
}
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php';  ?>
    
    
    <div class="container p-5 ">
        
        <div class="ps-5">
            <div>
                <h1>LIC FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lic">LIC</a></li>
                <li class="breadcrumb-item active" aria-current="page">LIC FORM</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="lic-form.php<?php 
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
                <input type="text" class="form-control" name="reg_num" id="registerNumber"  value="<?= htmlspecialchars($reg_num) ?>">
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
    
                <div class="col-md-6 field">
                    <label for="clientName" class="form-label">Client Name</label>
                    <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"  placeholder="Enter Client Name" required readonly>
                </div>
                    
                <!-- Mobile Number -->
                <div class="col-md-6 field">
                    <label for="mobileNumber" class="form-label">Mobile Number</label>
                    <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact ?? $row['contact']); ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" required readonly>
                </div>
            </div>
            
            <div class="row g-3 mb-3">
                
                <div class="col-md-6 field">
                    <label for="clientName" class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address); ?>"  placeholder="Enter Address" readonly>
                </div>
                
                <div class="col-md-6 field">
                    <label for="date" class="form-label">Birth Date</label>
                    <input type="date" class="form-control text-success" name="birth_date" id="date" value="<?php echo htmlspecialchars($birth_date);?>" readonly>
                </div>
                
                <div class="col-md-6 field">
                    <label for="invoiceStatus" class="form-label">Job Type</label>
                    <select class="form-select" id="tasktype" name="job_type" onchange="jobtype()">
                        <option selected>Select Job Type</option>
                        <option value="Collection" <?php if ($is_edit && $job_type == 'COLLECTION') echo 'selected'; ?>>Collection</option>
                        <option value="Servicing Tasks" <?php if ($is_edit && $job_type == 'SERVICING TASKS') echo 'selected'; ?>>Servicing Tasks</option>
                    </select>
                </div>
               
                <div class="col-md-6 field">
                    <label for="policyCompany" class="form-label">Agency</label>
                    <select class="form-select" name="adviser" id="adviser" onchange="handleAdviserChange()"> -->
                        <!-- Default option for a new form -->
                         <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                        
                        <option value="BYP" <?php if ($is_edit && strtoupper($adviser) == 'BYP') echo 'selected'; ?> selected>BYP</option>
                        <option value="OTHER" <?php if ($is_edit && strtoupper($adviser) == 'OTHER') echo 'selected'; ?>>OTHER</option> 
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                            $query = "SELECT DISTINCT adviser FROM lic_entries WHERE adviser NOT IN ('BYP', 'OTHER')";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                if (!empty($row['adviser'])) {
                                    $adviser_option = strtoupper($row['adviser']); // convert to uppercase for consistency
                                    $selected = ($is_edit && strtoupper($adviser) == $adviser_option) ? 'selected' : '';
                                    echo "<option value='{$row['adviser']}' $selected>{$row['adviser']}</option>"; // original case for display
                                }
                            }
                        ?>
                        
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($adviser) && !empty($advisercustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="advisercustom" value="<?php echo isset($advisercustom) && htmlspecialchars($advisercustom); ?>" class="form-control mt-2" id="customadviser" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($adviser) && !empty($advisercustom)) ? 'block' : 'none'; ?>;">

                </div>

            </div>
            
            
            
            <div id="typetask" style="display: <?php echo ($is_edit && $job_type == 'COLLECTION') ? 'block' : 'none'; ?>;">

                <div class="row g-3 mb-3">
                    
                    <div class="col-md-4 field">
                        <label for="paymentMode" class="form-label">Type Of Job</label>
                        <select class="form-select" name="collection_job" id="collection_job" onchange="handleJobTypeChange()">
                            <option value="" >Select Type Of Job</option>
                            <option value="New Business" <?php if ($is_edit && $collection_job == 'NEW BUSINESS') echo 'selected'; ?>>New Business</option>
                            <option value="Renewal Business" <?php if ($is_edit && $collection_job == 'RENEWAL BUSINESS') echo 'selected'; ?>>Renewal Business</option>
                            <option value="Revival" <?php if ($is_edit && $collection_job == 'REVIVAL') echo 'selected'; ?>>Revival</option>
                            <option value="Loan Interest payment" <?php if ($is_edit && $collection_job == 'LOAN INTEREST PAYMENT') echo 'selected'; ?>>Loan Interest payment</option>
                            <option value="Loan Full Repayment" <?php if ($is_edit && $collection_job == 'LOAN FULL REPAYMENT') echo 'selected'; ?>>Loan Full Repayment</option>
                            <option value="Other" <?php if ($is_edit && $collection_job == 'OTHER') echo 'selected'; ?>>Other</option>
                            <option value="Enter Manually" <?php if ($is_edit && empty($collection_job) && !empty($collectionjob)) echo 'selected'; ?>>Enter Manually</option>

                            <!-- Dynamically add custom options -->
                            <?php
                            $query = "SELECT DISTINCT collection_job FROM lic_entries WHERE collection_job NOT IN ('New Business', 'Renewal Business', 'Revival', 'Loan Interest payment', 'Loan Full Repayment', 'Other')";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($is_edit && $collection_job == $row['collection_job']) ? 'selected' : '';
                                echo "<option value='{$row['collection_job']}' $selected>{$row['collection_job']}</option>";
                            }
                            ?>
                        </select>

                        <!-- Input for manual entry -->
                        <input type="text" name="collectionjob" 
                            class="form-control mt-2" 
                            id="collectionjob" 
                            placeholder="Enter Job Type Manually" 
                            style="display:<?php echo ($is_edit && empty($collection_job) && !empty($collectionjob)) ? 'block' : 'none'; ?>;" 
                            value="<?php echo isset($collectionjob) ? htmlspecialchars($collectionjob) : ''; ?>">
                    </div>

                    
                    <!-- Number of Policies -->
                    <div class="col-md-4 field">
                        <label for="numberOfPolicies" class="form-label">Number of Policies</label>
                        <input type="number" class="form-control" name="policy_no" value="<?php echo htmlspecialchars($policy_no); ?>" id="numberOfPolicies" placeholder="Enter number of policies">
                    </div>
                    
                    <div class="col-md-4 field">
                        <label for="numberOfPolicies" class="form-label">Policy Numbers</label>

                        <!-- Existing policy numbers -->
                        <?php if ($is_edit && !empty($colle_policy_num)) : ?>
                            <?php
                                // Decode the JSON string to an array
                                $colle_policy_num_array = json_decode($colle_policy_num, true);
                                if (is_array($colle_policy_num_array)) {
                                    foreach ($colle_policy_num_array as $policy) :
                            ?>
                                        <div class="mt-2 field">
                                            <input type="text" class="form-control" name="colle_policy_num[]" value="<?php echo htmlspecialchars($policy); ?>" placeholder="Enter Policy Number">
                                        </div>
                            <?php
                                    endforeach;
                                }
                            ?>
                        <?php else : ?>
                            <!-- Initial field for new entry -->
                            <input type="text" class="form-control" name="colle_policy_num[]" id="numberOfPolicies" placeholder="Enter Policy Number">
                        <?php endif; ?>

                        <!-- Button to add more fields -->
                        <button type="button" id="addPolicy" class="btn btn-success mt-2">Add Another Policy</button>
                    </div>

                    <!-- Container for additional policy fields -->
                    <div id="additionalPolicies"></div>


        
                    <!-- Premium Amount -->
                    <div class="col-md-6 field">
                        <label for="premiumAmount" class="form-label">Premium Amount</label>
                        <input type="number" class="form-control" name="policy_amt" value="<?php echo htmlspecialchars($policy_amt); ?>" id="premiumAmount" placeholder="Enter Premium Amount" pattern="[0-9]*" oninput="calculateRemainingAmount()">
                    </div>
                    
                    <div id="premium" style="display: <?php echo ($is_edit && $collection_job == 'RENEWAL BUSINESS') ? 'block' : 'none'; ?>;">
                    <div class="row">
                        <div class="col-md-6 field">
                            <label for="clientName" class="form-label">Our Agency Premium</label>
                            <input type="text" class="form-control" name="our_agency_amt" value="<?php echo htmlspecialchars($our_agency_amt); ?>"  placeholder="Our Agency Premium" id="ourAgencyAmt" readonly >
                        </div>
                        
                        <div class="col-md-6 field">
                            <label for="clientName" class="form-label">Other Agency Premium</label>
                            <input type="text" class="form-control" name="other_agency_amt" value="<?php echo htmlspecialchars($other_agency_amt); ?>"  placeholder="Other Agency Premium"  id="otherAgencyAmt" oninput="calculateRemainingAmount()">
                        </div>
                    </div>

            </div>
                    
                    
                    
                    <!-- Payment Mode -->
                    <div class="col-md-6 field">
                        <label for="paymentMode" class="form-label">Payment Mode</label>
                        <select class="form-select" name="pay_mode" id="paymentMode" onchange="toggleFields()">
                            <option value="Cash" <?php if ($is_edit && $pay_mode == 'Cash') echo 'selected'; ?>>Cash</option>
                            <option value="Cheque" <?php if ($is_edit && $pay_mode == 'Cheque') echo 'selected'; ?>>Cheque</option>
                            <option value="Payment Link" <?php if ($is_edit && $pay_mode == 'Payment Link') echo 'selected'; ?>>Payment Link</option>
                            <option value="Online" <?php if ($is_edit && $pay_mode == 'Online') echo 'selected'; ?>>Online</option>
                            <option value="RTGS/NEFT" <?php if ($is_edit && $pay_mode == 'RTGS/NEFT') echo 'selected'; ?>>RTGS/NEFT</option>
                            <option value="Recovery" <?php if ($is_edit && $pay_mode == 'Recovery') echo 'selected'; ?>>Recovery Amount</option>
                        </select>
                    </div>
                    
                </div>
            </div>


        
            
            
            <div id="typetask1" style="display: <?php echo ($is_edit && $job_type == 'SERVICING TASKS') ? 'block' : 'none'; ?>;">
                <div class="row g-3 mb-3">
                    
                    <div class="col-md-6 field">
                        <label for="invoiceStatus" class="form-label">Type of Job</label>
                        <select class="form-select" id="typeofjob" name="work_status" onchange="handleJobType()">
                            <option selected>Select Type Of Job</option>
                            <!--<option value="" disabled <?php if ($is_edit && $work_status == '') echo 'selected'; ?>>Select Job Type</option>-->
                            <option value="Lone" <?php if ($is_edit && $work_status == 'Lone') echo 'selected'; ?>>Lone</option>
                            <option value="SV" <?php if ($is_edit && $work_status == 'SV') echo 'selected'; ?>>SV</option>
                            <option value="SB" <?php if ($is_edit && $work_status == 'SB') echo 'selected'; ?>>SB</option>
                            <option value="Maturity" <?php if ($is_edit && $work_status == 'Maturity') echo 'selected'; ?>>Maturity</option>
                            <option value="Annuity" <?php if ($is_edit && $work_status == 'Annuity') echo 'selected'; ?>>Annuity</option>
                            <option value="Nomination" <?php if ($is_edit && $work_status == 'Nomination') echo 'selected'; ?>>Nomination</option>
                            <option value="Death Claim" <?php if ($is_edit && $work_status == 'Death Claim') echo 'selected'; ?>>Death Claim</option>
                            <option value="DAB" <?php if ($is_edit && $work_status == 'DAB') echo 'selected'; ?>>DAB</option>
                            <option value="Other" <?php if ($is_edit && $work_status == 'Other') echo 'selected'; ?>>Other</option>
        
                            <option value="Enter Manually" <?php if ($is_edit && $work_status == 'Enter Manually') echo 'selected'; ?>>Enter Manually</option>
                            
                            <!-- Dynamically add custom options here -->
                            <?php
                            $query = "SELECT DISTINCT work_status FROM lic_entries WHERE work_status NOT IN ('Lone', 'SV', 'SB', 'Maturity', 'Annuity','Nomination','Death Claim','DAB','Other')";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['work_status']}' " . ($is_edit && $work_status == $row['work_status'] ? 'selected' : '') . ">{$row['work_status']}</option>";
                            }
                            ?>
                        </select>
                        
                        <!-- Input for manual entry -->
                        <input type="text" name="manualJobType" class="form-control mt-2" id="manualJobInput" placeholder="Enter Job Type Manually" style="<?php echo ($is_edit && $work_status == 'Enter Manually') ? 'display:block;' : 'display:none;'; ?>" value="<?php echo isset($work_status) && ($is_edit && $work_status == 'Enter Manually') ? htmlspecialchars($job_type) : ''; ?>">

                    </div>
                    
                    <div class="col-md-6 field">
                        <label for="numberOfPolicies" class="form-label">Policy Number</label>
                        <input type="text" class="form-control" name="policy_num" value="<?php echo htmlspecialchars($policy_num); ?>" id="numberOfPolicies" placeholder="Enter Policy Number">
                    </div>
                    

                </div>
            </div>
            
            <div class="row g-3 mb-3">
                 <!-- Payment Mode -->
            
            
            <!-- Hidden fields for cheque details -->
            <div id="chequeDetails" style="display: <?php echo ($is_edit && $pay_mode == 'Cheque') ? 'block' : 'none'; ?>;" >
                <div class="row g-3">
                    <div class="col-md-3 field mt-3">
                        <label for="chequeNumber" class="form-label">Cheque Number</label>
                        <input type="text" name="cheque_no" value="<?php echo htmlspecialchars($cheque_no); ?>" class="form-control" id="chequeNumber" placeholder="Enter Cheque Number">
                    </div>
                

                    <div class="col-md-3 field">
                        <label for="policyCompany" class="form-label">Bank Name</label>
                        <select class="form-select" name="bank_name" id="bank" onchange="handleBankNameChange()">
                            <!-- Default option for a new form -->
                            <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                            
                            
                            <!-- Dynamically add options from the database -->
                            <?php
                            $query = "SELECT DISTINCT bank_name FROM lic_entries";
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
                            $query = "SELECT DISTINCT branch_name FROM lic_entries";
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
                        <input type="date" name="check_dt" value="<?php if ($is_edit) { echo htmlspecialchars($check_dt); } ?>" class="form-control" id="chequeDate">
                    </div>
                </div>
            </div>
            
            <!-- Hidden fields for recovery amount details -->
            <div id="recoveryDetails" style="display:<?php echo ($is_edit && $pay_mode == 'Recovery') ? 'block' : 'none'; ?>;">
                <div class="row g-3">
                    <div class="col-md-4 field mt-3">
                        <label for="recoveryAmount" class="form-label">Recovery Amount</label>
                        <input type="number" name="recov_amount" value="<?php echo htmlspecialchars($recov_amount); ?>" class="form-control" id="recoveryAmount" placeholder="Enter Recovery Amount">
                    </div>
                </div>
            </div>
          
            <div class="row g-3">
                
                
                
            </div>
                
                
            </div>
            
            <!-- Remark -->
                <div class="col-md-12 field">
                    <label for="remark" class="form-label">Remark</label>
                    <textarea class="form-control" id="remark" name="remark"  rows="3" placeholder="Enter Remark"><?php echo htmlspecialchars($remark); ?></textarea>
                </div>
                
                <div class="col-md-6 mt-3 mx-auto p-2 field" style="background-color: #ffcdcd;">
                    <label for="motorSubType" class="form-label">Form Status</label>
                    <select class="form-select" name="form_status" id="motorSubType" required>
                        <option value="PENDING" <?php if ($is_edit && $form_status == 'PENDING') echo 'selected'; ?>>PENDING</option>
                        <option value="COMPLETE" <?php if ($is_edit && $form_status == 'COMPLETE') echo 'selected'; ?>>COMPLETE</option>
                        <option value="CDA" <?php if ($is_edit && $form_status == 'CDA') echo 'selected'; ?>>CDA</option>
                        <option value="CANCELLED" <?php if ($is_edit && $form_status == 'CANCELLED') echo 'selected'; ?>>CANCELLED</option>
                        <option value="OTHER" <?php if ($is_edit && $form_status == 'OTHER') echo 'selected'; ?>>OTHER</option>
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
     // when select paid open for fill details
function toggleRemarkFields() {
    const paymentMode = document.getElementById('invoiceStatus').value;
    const chequeDetails = document.getElementById('invoiceRemark');

    // Show cheque fields only when "Cheque" is selected
    if (paymentMode === 'Paid') {
        chequeDetails.style.display = 'block';
    } else {
        chequeDetails.style.display = 'none';
    }
}

// Handle Agency selection
function handleAdviserChange() {
    var companySelect = document.getElementById('adviser');
    var customCompanyInput = document.getElementById('customadviser');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'adviser'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'advisercustom'; // Reset to avoid conflicts
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

// Function to handle the job type selection
function handleJobType() {
    var select = document.getElementById('typeofjob');
    var manualInput = document.getElementById('manualJobInput');
    
    if (select.value === 'Enter Manually') {
        manualInput.style.display = 'block';
    } else {
        manualInput.style.display = 'none';
    }
}


// Function to handle job type selection
function jobtype() {
    const taskType = document.getElementById('tasktype').value; // Get the selected job type
    const premiumCollectionFields = document.getElementById('typetask'); // Div for Collection
    const servicingTaskFields = document.getElementById('typetask1'); // Div for Servicing Tasks

    // Show or hide fields based on selected job type
    if (taskType === 'Servicing Tasks') {
        servicingTaskFields.style.display = 'block'; // Show servicing task details
        premiumCollectionFields.style.display = 'none'; // Hide Collection details
    } else if (taskType === 'Collection') {
        premiumCollectionFields.style.display = 'block'; // Show Collection details
        servicingTaskFields.style.display = 'none'; // Hide Servicing Tasks details
    } else {
        premiumCollectionFields.style.display = 'none';
        servicingTaskFields.style.display = 'none';
    }
}

// Run jobtype() on page load to handle edit mode
window.onload = function() {
    jobtype(); // This will execute when the page loads
};

function handleJobTypeChange() {
    const collectionJob = document.getElementById('collection_job').value;
    const premiumSection = document.getElementById('premium');
    const customJobInput = document.getElementById('collectionjob');

    // Show/Hide Premium Section based on the selected job type
    if (collectionJob === 'Renewal Business') {
        premiumSection.style.display = 'block';
    } else {
        premiumSection.style.display = 'none';
    }

    // Show/Hide Manual Input Field
    if (collectionJob === 'Enter Manually') {
        customJobInput.style.display = 'block';
        customJobInput.name = 'collection_job'; // Set custom input name to match
    } else {
        customJobInput.style.display = 'none';
        customJobInput.name = 'collectionjob'; // Reset name to avoid conflicts
    }
}


// calculate amount script
function calculateRemainingAmount() {
        const policyAmt = parseFloat(document.getElementById('premiumAmount').value) || 0;
        const otherAgencyAmt = parseFloat(document.getElementById('otherAgencyAmt').value) || 0;

        // Calculate remaining amount for the other agency
        const remainingAmount = policyAmt - otherAgencyAmt;

        // Set the remaining amount in the Other Agency Premium input field
        document.getElementById('ourAgencyAmt').value = remainingAmount > 0 ? remainingAmount : 0;
    }
    
    
// Script for open multiple policy number input
    document.getElementById('addPolicy').addEventListener('click', function () {
        const container = document.getElementById('additionalPolicies');
        const inputField = `
            <div class="mt-2">
                <input type="text" class="form-control" name="colle_policy_num[]" placeholder="Enter Policy Number">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', inputField);
    });

</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>