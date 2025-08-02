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
$category = '';
$type_work = '';
$mv_no = '';
$amount = '';
$inv_status = '';
$remark = '';
$adv_amount = '';
$gov_amount = '';
$other_amount = '';
$recov_amount = '';
$address = '';
$birth_date = '';
$form_status = '';
$responsibility = '';
$adviser_name = '';
$dl_type_work = '';
$tr_type_work = '';
$nt_type_work = '';
$expenses = '';
$net_amt = '';
$vehicle_class = '';
$client_id = '';
$errors = [];
$is_edit = false;
$add_client = false;
$id = null; // Initialize $id variable


// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM rto_entries WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $contact = $row['contact']; 
        $category = $row['category'];
        $type_work = strtoupper($row['type_work']);
        $mv_no = strtoupper($row['mv_no']);
        $amount = $row['amount'];
        $inv_status = $row['inv_status'];
        $remark = strtoupper($row['remark']);
        $adv_amount = $row['adv_amount'];
        $gov_amount = $row['gov_amount'];
        $other_amount = $row['other_amount'];
        $recov_amount = $row['recov_amount'];
        $address = strtoupper($row['address']);
        $birth_date = $row['birth_date'];
        $form_status = $row['form_status'];
        $responsibility = strtoupper($row['responsibility']);
        $adviser_name = strtoupper($row['adviser_name']);
        $dl_type_work = strtoupper($row['dl_type_work']);
        $tr_type_work = strtoupper($row['tr_type_work']);
        $nt_type_work = strtoupper($row['nt_type_work']);
        $expenses = $row['expenses'];
        $net_amt = $row['net_amt'];
        $vehicle_class = $row['vehicle_class'];
    } else {
        die("Entry not found.");
    }
} 
elseif (isset($_GET['action']) && $_GET['action'] === 'add_client') {
    $is_edit = false; 
    $add_client = true;

    // Get the current month and year
    $currentYear = date('Y');
    $currentMonth = date('m');

    // Initialize reg_num
    $reg_num = 1; // Default to 1 if no records are found

    // Fetch the last registration number for the current month
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num, MONTH(policy_date) AS month, YEAR(policy_date) AS year 
                            FROM rto_entries 
                            WHERE YEAR(policy_date) = $currentYear AND MONTH(policy_date) = $currentMonth");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // If the entry is from the current month, increment reg_num
        if ((int)$row['month'] === (int)$currentMonth && (int)$row['year'] === (int)$currentYear) {
            $reg_num = (int)$row['max_reg_num'] + 1;
        }
    }

    // Only fetch the client_name and contact if ID is provided
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT client_name, contact, address, birth_date FROM client WHERE id = $id");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_name = $row['client_name'];
            $contact = $row['contact'];
            $address = $row['address'];
            $birth_date = $row['birth_date'];
        } else {
            die("Client not found.");
        }
    }
} else {
    // Get the current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Initialize reg_num
    $reg_num = 1; // Default to 1 if no records exist

    // Fetch the last registration number and month-year from the database
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num, MONTH(policy_date) AS month, YEAR(policy_date) AS year 
                            FROM rto_entries");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the last entry is from the current month and year
        if ((int)$row['month'] === (int)$currentMonth && (int)$row['year'] === (int)$currentYear) {
            $reg_num = (int)$row['max_reg_num'] + 1; // Get the next registration number
        }
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
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $type_work = isset($_POST['type_work']) ? strtoupper(trim($_POST['type_work'])) : '';
    $mv_no = isset($_POST['mv_no']) ? strtoupper(trim($_POST['mv_no'])) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $expenses = isset($_POST['expenses']) ? trim($_POST['expenses']) : '';
    $net_amt = isset($_POST['net_amt']) ? trim($_POST['net_amt']) : '';
    $inv_status = isset($_POST['inv_status']) ? trim($_POST['inv_status']) : '';
    $remark = isset($_POST['remark']) ? strtoupper(trim($_POST['remark'])) : '';
    $adv_amount = isset($_POST['adv_amount']) ? trim($_POST['adv_amount']) : '';
    $gov_amount = isset($_POST['gov_amount']) ? trim($_POST['gov_amount']) : '';
    $other_amount = isset($_POST['other_amount']) ? trim($_POST['other_amount']) : '';
    $recov_amount =isset($_POST['recov_amount']) ? trim($_POST['recov_amount']) : '';
    $address = isset($_POST['address']) ? strtoupper(trim($_POST['address'])) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $form_status = isset($_POST['form_status']) ? trim($_POST['form_status']) : '';
    $responsibility = isset($_POST['responsibility']) ? strtoupper(trim($_POST['responsibility'])) : '';
    $adviser_name = isset($_POST['adviser_name']) ? strtoupper(trim($_POST['adviser_name'])) : '';
    $dl_type_work = isset($_POST['dl_type_work']) ? strtoupper(trim($_POST['dl_type_work'])) : '';
    $tr_type_work = isset($_POST['tr_type_work']) ? strtoupper(trim($_POST['tr_type_work'])) : '';
    $nt_type_work = isset($_POST['nt_type_work']) ? strtoupper(trim($_POST['nt_type_work'])) : '';
    $vehicle_class = isset($_POST['vehicle_class']) ? strtoupper(trim($_POST['vehicle_class'])) : '';
    $client_id = trim($_POST['client_id']);

    // Validation
    if (empty($client_name) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $client_name)) {
        $errors[] = "Invalid client name";
    }

    if (empty($contact) || !preg_match("/^\d{10}$/", $contact)) {
        $errors[] = "Invalid contact number";
    }

    if (!empty($type_work) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $type_work)) {
        $errors[] = "Invalid Type Of Work number";
    }

    if (!empty($mv_no) && !preg_match('/^[A-Z0-9]+$/', $mv_no)) {
        $errors[] = "Invalid MV No./DL No. number";
    }
    
    if (!empty($amount) && !is_numeric($amount)) {
        $errors[] = "Invalid amount";
    }
    
    if (!empty($adv_amount) && !is_numeric($adv_amount)) {
        $errors[] = "Invalid Advance Amount";
    }
    
    if (!empty($gov_amount) && !is_numeric($gov_amount)) {
        $errors[] = "Invalid Government Amount";
    }
    
    if (!empty($other_amount) && !is_numeric($other_amount)) {
        $errors[] = "Invalid Other Amount";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }
    
    if (!empty($remark) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $remark)) {
        $errors[] = "Invalid remark";
    }
    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone

// echo "<pre>";
// print_r($_POST);
// exit;

    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {

            $dl_type_work = $_POST['dl_type_work'] === 'Enter Manually' ? $_POST['dl_type_work_custom'] : $_POST['dl_type_work'];
            $tr_type_work = $_POST['tr_type_work'] === 'Enter Manually' ? $_POST['tr_type_work_custom'] : $_POST['tr_type_work'];
            $nt_type_work = $_POST['nt_type_work'] === 'Enter Manually' ? $_POST['nt_type_work_custom'] : $_POST['nt_type_work'];
            $vehicle_class = $_POST['vehicle_class'] === 'Enter Manually' ? $_POST['vehicle_class_custom'] : $_POST['vehicle_class'];
            

            // Update existing entry
            $sql = "UPDATE rto_entries SET reg_num='$reg_num', policy_date='$policy_date',time='$time', client_name='$client_name', contact='$contact', category='$category', type_work='$type_work', mv_no='$mv_no', amount='$amount', inv_status='$inv_status', remark='$remark', adv_amount='$adv_amount', gov_amount='$gov_amount', other_amount='$other_amount', recov_amount='$recov_amount', address='$address',birth_date='$birth_date',form_status='$form_status',update_on='$update_on',responsibility='$responsibility',adviser_name='$adviser_name',dl_type_work='$dl_type_work',tr_type_work='$tr_type_work',nt_type_work='$nt_type_work',expenses='$expenses',net_amt='$net_amt',vehicle_class='$vehicle_class' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: rto");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
        
        elseif  ($add_client) {
            
            // username for who fill form
            $username = $_SESSION['username'];

            $type_work = strtoupper(trim($_POST['type_work'] ?? ''));
            $type_work_custom = strtoupper(trim($_POST['type_work_custom'] ?? ''));
            if ($type_work == "Enter Manually") {
                $type_work = strtoupper($type_work_custom);
            }

            $dl_type_work = strtoupper(trim($_POST['dl_type_work'] ?? ''));
            $dl_type_work_custom = strtoupper(trim($_POST['dl_type_work_custom'] ?? ''));
            if ($dl_type_work == "Enter Manually") {
                $dl_type_work = strtoupper($dl_type_work_custom);
            }

            $tr_type_work = strtoupper(trim($_POST['tr_type_work'] ?? ''));
            $tr_type_work_custom = strtoupper(trim($_POST['tr_type_work_custom'] ?? ''));
            if ($tr_type_work == "Enter Manually") {
                $tr_type_work = strtoupper($tr_type_work_custom);
            }

            $nt_type_work = strtoupper(trim($_POST['nt_type_work'] ?? ''));
            $nt_type_work_custom = strtoupper(trim($_POST['nt_type_work_custom'] ?? ''));
            if ($nt_type_work == "Enter Manually") {
                $nt_type_work = strtoupper($nt_type_work_custom);  
            }

            $adviser_name = strtoupper(trim($_POST['adviser_name'] ?? ''));
            $advisercustom = strtoupper(trim($_POST['advisercustom'] ?? ''));
            if ($adviser_name == "Enter Manually") {
                $adviser_name = strtoupper($advisercustom);
            }

            $vehicle_class = strtoupper(trim($_POST['vehicle_class'] ?? ''));
            $vehicle_class_custom = strtoupper(trim($_POST['vehicle_class_custom'] ?? ''));
            if ($vehicle_class == "Enter Manually") {
                $vehicle_class = strtoupper($vehicle_class_custom);
            }



            $sql = "INSERT INTO rto_entries (client_id,reg_num, policy_date,time, client_name, contact, category, type_work, mv_no, amount,  inv_status, remark,adv_amount,gov_amount,other_amount,recov_amount,address,username,birth_date,form_status,creation_on,responsibility,adviser_name,dl_type_work,tr_type_work,nt_type_work,expenses,net_amt,vehicle_class) 
                    VALUES ('$client_id','$reg_num', '$policy_date','$time', '$client_name', '$contact', '$category', '$type_work', '$mv_no', '$amount' , '$inv_status', '$remark', '$adv_amount', '$gov_amount' ,'$other_amount','$recov_amount','$address','$username','$birth_date','$form_status','$creation_on','$responsibility','$adviser_name','$dl_type_work','$tr_type_work','$nt_type_work','$expenses','$net_amt','$vehicle_class')";
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
                <h1>RTO FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="rto">RTO</a></li>
                <li class="breadcrumb-item active" aria-current="page">RTO FORM</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="rto-form.php<?php 
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
                <input type="text" class="form-control" name="reg_num" id="registerNumber"  value="<?= htmlspecialchars($reg_num) ?>" readonly>
            </div>
    
          <!-- Date (Current Date) -->
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
                <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"  placeholder="Enter Client Name" required readonly>
            </div>

            <!-- Mobile Number -->
            <div class="col-md-6 field">
                <label for="mobileNumber" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact) ?? $row['contact']; ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" required readonly>
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
        </div>
        
        <div class="row g-3 mb-3">

            <div class="col-md-4 field">
                <label for="paymentMode" class="form-label">Category</label>
                <select class="form-select" name="category" id="category" onchange="handleCategoryChange()">
                    <option value="">Select Category</option>
                    <option value="NT" <?php if ($is_edit && $category == 'NT') echo 'selected'; ?>>NT</option>
                    <option value="TR" <?php if ($is_edit && $category == 'TR') echo 'selected'; ?>>TR</option>
                    <option value="DL" <?php if ($is_edit && $category == 'DL') echo 'selected'; ?>>DL</option>
                </select>
            </div>

            <div class="col-md-4 field" id="dlTypeWork" style="display: <?php echo ($is_edit && $category == 'DL') ? 'block' : 'none'; ?>;">

                <label for="policyCompany" class="form-label">Type of Work for DL</label>
                <select class="form-select" name="dl_type_work" id="dl_type_work" onchange="handleDLTypeWorkChange()">
                    <!-- Default option for a new form -->
                    <option value="" disabled <?php echo (!$is_edit || empty($dl_type_work)) ? 'selected' : ''; ?>>Select Type Of Work</option>
                    <option value="2" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '2') echo 'selected'; ?>>2</option>
                    <option value="4" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '4') echo 'selected'; ?>>4</option>
                    <option value="8" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '8') echo 'selected'; ?>>8</option>
                    <option value="9" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '9') echo 'selected'; ?>>9</option>
                    <option value="REVALID" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == 'REVALID') echo 'selected'; ?>>REVALID</option>
                    <option value="LLD" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == 'LLD') echo 'selected'; ?>>LLD</option>
                    <option value="IDP" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == 'IDP') echo 'selected'; ?>>IDP</option>
                    <option value="9+CA" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '9+CA') echo 'selected'; ?>>9+CA</option>
                    <option value="9+CN" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '9+CN') echo 'selected'; ?>>9+CN</option>
                    <option value="9+LLD" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '9+LLD') echo 'selected'; ?>>9+LLD</option>
                    <option value="9+CA+CN" <?php if ($is_edit && isset($dl_type_work) && strtoupper($dl_type_work) == '9+CA+CN') echo 'selected'; ?>>9+CA+CN</option>

                    <!-- Dynamically add options from the database -->
                    <?php
                    $query = "SELECT DISTINCT dl_type_work FROM rto_entries WHERE dl_type_work NOT IN ('2','4','8','9','REVALID','LLD','IDP','9+CA','9+CN','9+LLD','9+CA+CN')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['dl_type_work'])) {
                            $type_work_option = strtoupper($row['dl_type_work']); // convert to uppercase for consistency
                            $selected = ($is_edit && strtoupper($dl_type_work) == $type_work_option) ? 'selected' : '';
                            echo "<option value='{$row['dl_type_work']}' $selected>{$row['dl_type_work']}</option>"; // original case for display
                        }
                    }
                    ?>
                    
                    <!-- Option to enter manually -->
                    <option value="Enter Manually" <?php echo ($is_edit && empty($dl_type_work) && !empty($dl_type_work_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>
                
                <!-- Input for manual entry -->
                <input type="text" name="dl_type_work_custom" value="<?php echo isset($dl_type_work_custom) && htmlspecialchars($dl_type_work_custom); ?>" class="form-control mt-2" id="dl_type_work_custom" placeholder="Enter Type Of Work" style="display:<?php echo ($is_edit && empty($dl_type_work) && !empty($dl_type_work_custom)) ? 'block' : 'none'; ?>;">

            </div>

            <div class="col-md-4 field" id="trTypeWork" style="display: <?php echo ($is_edit && $category == 'TR') ? 'block' : 'none'; ?>;">

                <label for="policyCompany" class="form-label">Type of Work for TR</label>
                <select class="form-select" name="tr_type_work" id="tr_type_work" onchange="handleTRTypeWorkChange()">
                    <!-- Default option for a new form -->
                    <option value="" disabled <?php echo (!$is_edit || empty($tr_type_work)) ? 'selected' : ''; ?>>Select Type Of Work</option>
                    <option value="CHOICE NO" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'CHOICE NO') echo 'selected'; ?>>CHOICE NO</option>
                    <option value="20" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '20') echo 'selected'; ?>>20</option>
                    <option value="25" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '25') echo 'selected'; ?>>25</option>
                    <option value="25/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '25/35') echo 'selected'; ?>>25/35</option>
                    <option value="26" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '26') echo 'selected'; ?>>26</option>
                    <option value="26/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '26/35') echo 'selected'; ?>>26/35</option>
                    <option value="26/35/30" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '26/35/30') echo 'selected'; ?>>26/35/30</option>
                    <option value="28" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '28') echo 'selected'; ?>>28</option>
                    <option value="28/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '28/35') echo 'selected'; ?>>28/35</option>
                    <option value="29/30" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '29/30') echo 'selected'; ?>>29/30</option>
                    <option value="29/30/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '29/30/35') echo 'selected'; ?>>29/30/35</option>
                    <option value="29/30/35/34" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '29/30/35/34') echo 'selected'; ?>>29/30/35/34</option>
                    <option value="29/30/34/35/26" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '29/30/34/35/26') echo 'selected'; ?>>29/30/34/35/26</option>
                    <option value="31" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '31') echo 'selected'; ?>>31</option>
                    <option value="31/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '31/35') echo 'selected'; ?>>31/35</option>
                    <option value="34" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '34') echo 'selected'; ?>>34</option>
                    <option value="35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '35') echo 'selected'; ?>>35</option>
                    <option value="34/35" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == '34/35') echo 'selected'; ?>>34/35</option>
                    <option value="BT" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'BT') echo 'selected'; ?>>BT</option>
                    <option value="RC CANCELLATION" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'RC CANCELLATION') echo 'selected'; ?>>RC CANCELLATION</option>
                    <option value="STOLEN" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'STOLEN') echo 'selected'; ?>>STOLEN</option>
                    <option value="TAX REFUND" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'TAX REFUND') echo 'selected'; ?>>TAX REFUND</option>
                    <option value="TAX EXEMPTION" <?php if ($is_edit && isset($tr_type_work) && strtoupper($tr_type_work) == 'TAX EXEMPTION') echo 'selected'; ?>>TAX EXEMPTION</option>

                    <!-- Dynamically add options from the database -->
                    <?php
                    $query = "SELECT DISTINCT tr_type_work FROM rto_entries WHERE tr_type_work NOT IN ('20','25','25/35','26','26/35','26/35/30','28','28/35','29/30','29/30/35','29/30/35/34','29/30/34/35/26','31','31/35','34','35','34/35','BT','RC CANCELLATION','STOLEN','TAX REFUND','TAX EXEMPTION')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['tr_type_work'])) {
                            $type_work_option = strtoupper($row['tr_type_work']); // convert to uppercase for consistency
                            $selected = ($is_edit && strtoupper($tr_type_work) == $type_work_option) ? 'selected' : '';
                            echo "<option value='{$row['tr_type_work']}' $selected>{$row['tr_type_work']}</option>"; // original case for display
                        }
                    }
                    ?>
                    
                    <!-- Option to enter manually -->
                    <option value="Enter Manually" <?php echo ($is_edit && empty($tr_type_work) && !empty($tr_type_work_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>
                
                <!-- Input for manual entry -->
                <input type="text" name="tr_type_work_custom" value="<?php echo isset($tr_type_work_custom) && htmlspecialchars($tr_type_work_custom); ?>" class="form-control mt-2" id="tr_type_work_custom" placeholder="Enter Type Of Work" style="display:<?php echo ($is_edit && empty($tr_type_work) && !empty($tr_type_work_custom)) ? 'block' : 'none'; ?>;">

            </div>

            <div class="col-md-4 field" id="ntTypeWork" style="display: <?php echo ($is_edit && $category == 'NT') ? 'block' : 'none'; ?>;">

                <label for="policyCompany" class="form-label">Type of Work for NT</label>
                <select class="form-select" name="nt_type_work" id="nt_type_work" onchange="handleNTTypeWorkChange()">
                    <!-- Default option for a new form -->
                    <option value="" disabled <?php echo (!$is_edit || empty($nt_type_work)) ? 'selected' : ''; ?>>Select Type Of Work</option>
                    <option value="20" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '20') echo 'selected'; ?>>20</option>
                    <option value="25/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '25/35') echo 'selected'; ?>>25/35</option>
                    <option value="26" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '26') echo 'selected'; ?>>26</option>
                    <option value="26/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '26/35') echo 'selected'; ?>>26/35</option>
                    <option value="26/35/30" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '26/35/30') echo 'selected'; ?>>26/35/30</option>
                    <option value="28" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '28') echo 'selected'; ?>>28</option>
                    <option value="28/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '28/35') echo 'selected'; ?>>28/35</option>
                    <option value="29/30" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '29/30') echo 'selected'; ?>>29/30</option>
                    <option value="29/30/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '29/30/35') echo 'selected'; ?>>29/30/35</option>
                    <option value="29/30/35/34" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '29/30/35/34') echo 'selected'; ?>>29/30/35/34</option>
                    <option value="29/30/34/35/26" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '29/30/34/35/26') echo 'selected'; ?>>29/30/34/35/26</option>
                    <option value="31" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '31') echo 'selected'; ?>>31</option>
                    <option value="31/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '31/35') echo 'selected'; ?>>31/35</option>
                    <option value="34" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '34') echo 'selected'; ?>>34</option>
                    <option value="35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '35') echo 'selected'; ?>>35</option>
                    <option value="34/35" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == '34/35') echo 'selected'; ?>>34/35</option>
                    <option value="BT" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'BT') echo 'selected'; ?>>BT</option>
                    <option value="CFRA" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'CFRA') echo 'selected'; ?>>CFRA</option>
                    <option value="ALL INDIA" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'ALL INDIA') echo 'selected'; ?>>ALL INDIA</option>
                    <option value="PERMIT RENWAL" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'PERMIT RENWAL') echo 'selected'; ?>>PERMIT RENWAL</option>
                    <option value="PERMIT CANCELLATION" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'PERMIT CANCELLATION') echo 'selected'; ?>>PERMIT CANCELLATION</option>
                    <option value="TAX REFUND" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'TAX REFUND') echo 'selected'; ?>>TAX REFUND</option>
                    <option value="TAX EXEMPTION" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'TAX EXEMPTION') echo 'selected'; ?>>TAX EXEMPTION</option>
                    <option value="RC CANCELLATION" <?php if ($is_edit && isset($nt_type_work) && strtoupper($nt_type_work) == 'RC CANCELLATION') echo 'selected'; ?>>RC CANCELLATION</option>
                    

                    <!-- Dynamically add options from the database -->
                    <?php
                    $query = "SELECT DISTINCT nt_type_work FROM rto_entries WHERE nt_type_work NOT IN ('20','25/35','26','26/35','26/35/30','28','28/35','29/30','29/30/35','29/30/35/34','29/30/34/35/26','31','31/35','34','35','34/35','BT','CFRA','ALL INDIA','PERMIT RENWAL','PERMIT CANCELLATION','TAX REFUND','TAX EXEMPTION','RC CANCELLATION')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['nt_type_work'])) {
                            $type_work_option = strtoupper($row['nt_type_work']); // convert to uppercase for consistency
                            $selected = ($is_edit && strtoupper($nt_type_work) == $type_work_option) ? 'selected' : '';
                            echo "<option value='{$row['nt_type_work']}' $selected>{$row['nt_type_work']}</option>"; // original case for display
                        }
                    }
                    ?>
                    
                    <!-- Option to enter manually -->
                    <option value="Enter Manually" <?php echo ($is_edit && empty($nt_type_work) && !empty($nt_type_work_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>
                
                <!-- Input for manual entry -->
                <input type="text" name="nt_type_work_custom" value="<?php echo isset($nt_type_work_custom) && htmlspecialchars($nt_type_work_custom); ?>" class="form-control mt-2" id="nt_type_work_custom" placeholder="Enter Type Of Work" style="display:<?php echo ($is_edit && empty($nt_type_work) && !empty($nt_type_work_custom)) ? 'block' : 'none'; ?>;">

            </div>


            
            <div class="col-md-4 field">
                <label for="premiumAmount" class="form-label">MV No./DL No.</label>
                <input type="text" class="form-control" name="mv_no" value="<?php echo htmlspecialchars($mv_no); ?>" id="premiumAmount" placeholder="Enter MV No./DL No." oninput="this.value = this.value.toUpperCase(); this.value = this.value.replace(/[^A-Z0-9]/g, '');" >
            </div>

            <div class="col-md-4 field">
                <label for="policyCompany" class="form-label">Vehicle Class</label>
                <select class="form-select" name="vehicle_class" id="vehicle_class" onchange="handleVehicleClassChange()">
                    <!-- Default option for a new form -->
                    <option value="" disabled <?php echo (!$is_edit || empty($vehicle_class)) ? 'selected' : ''; ?>>Select Vehicle Class</option>
                    

                    <!-- Dynamically add options from the database -->
                    <?php
                    $query = "SELECT DISTINCT vehicle_class FROM rto_entries";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['vehicle_class'])) {
                            $type_work_option = strtoupper($row['vehicle_class']); // convert to uppercase for consistency
                            $selected = ($is_edit && strtoupper($vehicle_class) == $type_work_option) ? 'selected' : '';
                            echo "<option value='{$row['vehicle_class']}' $selected>{$row['vehicle_class']}</option>"; // original case for display
                        }
                    }
                    ?>
                    
                    <!-- Option to enter manually -->
                    <option value="Enter Manually" <?php echo ($is_edit && empty($vehicle_class) && !empty($vehicle_class_custom)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>
                
                <!-- Input for manual entry -->
                <input type="text" name="vehicle_class_custom" value="<?php echo isset($vehicle_class_custom) && htmlspecialchars($vehicle_class_custom); ?>" class="form-control mt-2" id="vehicle_class_custom" placeholder="Enter Type Of Work" style="display:<?php echo ($is_edit && empty($vehicle_class) && !empty($vehicle_class_custom)) ? 'block' : 'none'; ?>;">
            </div>
        </div>
        
        <div class="row g-3 mb-3">
            <!-- Premium Amount -->
           <div class="col-md-4 field">
                <label for="quotationAmount" class="form-label">Premium Amount</label>
                <input type="number" class="form-control" name="amount" value="<?php echo htmlspecialchars($amount); ?>" id="quotationAmount" placeholder="Enter Quotation Amount" oninput="validateAndCalculateRecovery(this)" >
            </div>
            
            <div class="col-md-4 field">
                <label for="advanceAmount" class="form-label">Advance Amount</label>
                <input type="number" class="form-control" name="adv_amount" value="<?php echo htmlspecialchars($adv_amount); ?>" id="advanceAmount" placeholder="Enter Advance Amount" oninput="validateAndCalculateRecovery(this)" >
            </div>
            
            <div class="col-md-4 field">
                <label for="recoveryAmount" class="form-label">Recovery Amount</label>
                <input type="number" class="form-control" name="recov_amount" value="<?php echo htmlspecialchars($recov_amount); ?>" id="recoveryAmount" placeholder="Recovery Amount" readonly >
            </div>
            
            <div class="col-md-4 field">
                <label for="premiumAmount" class="form-label">Government Fees</label>
                <input type="number" class="form-control" name="gov_amount" value="<?php echo htmlspecialchars($gov_amount); ?>" id="govAmount" placeholder="Enter Amount" pattern="[0-9]*" oninput="validateAndCalculateRecovery(this)" >
            </div>
            
            <div class="col-md-4 field">
                <label for="premiumAmount" class="form-label">Cash In Hand</label>
                <input type="number" class="form-control" name="other_amount" value="<?php echo htmlspecialchars($other_amount); ?>" id="cashinhandAmt" placeholder="Enter Amount" pattern="[0-9]*" readonly>
            </div>
            
            <div class="col-md-4 field">
                <label for="premiumAmount" class="form-label">Expense Amount </label>
                <input type="number" class="form-control" name="expenses" value="<?php echo htmlspecialchars($expenses); ?>" id="premiumAmount" placeholder="Enter Amount" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '');" >
            </div>
            
            <div class="col-md-6 field">
                <label for="premiumAmount" class="form-label">Net Amount</label>
                <input type="number" class="form-control" name="net_amt" value="<?php echo htmlspecialchars($net_amt); ?>" id="premiumAmount" placeholder="Enter Amount" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '');" >
            </div>
            
            
            <div class="col-md-6 field">
                <label for="policyCompany" class="form-label">Adviser Name</label>
                <select class="form-select" name="adviser_name" id="adviser" onchange="handleAdviserChange()">
                    <!-- Default option for a new form -->
                    <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                    
                    <option value="BYP" <?php if ($is_edit && strtoupper($adviser_name) == 'BYP') echo 'selected'; ?> selected>BYP</option>

                    <!-- Dynamically add options from the database -->
                    <?php
                    $query = "SELECT DISTINCT adviser_name FROM rto_entries WHERE adviser_name NOT IN ('BYP', 'PB Partner')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['adviser_name'])) {
                            $adviser_option = strtoupper($row['adviser_name']); // convert to uppercase for consistency
                            $selected = ($is_edit && strtoupper($adviser_name) == $adviser_option) ? 'selected' : '';
                            echo "<option value='{$row['adviser_name']}' $selected>{$row['adviser_name']}</option>"; // original case for display
                        }
                    }
                    ?>
                    
                    <!-- Option to enter manually -->
                    <option value="Enter Manually" <?php echo ($is_edit && empty($adviser_name) && !empty($advisercustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>
                
                <!-- Input for manual entry -->
                <input type="text" name="advisercustom" value="<?php echo isset($advisercustom) && htmlspecialchars($advisercustom); ?>" class="form-control mt-2" id="customadviser" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($adviser_name) && !empty($advisercustom)) ? 'block' : 'none'; ?>;">

            </div>
            

            <!-- Invoice Status -->
            <!--<div class="col-md-4 field">-->
            <!--    <label for="invoiceStatus" class="form-label">Work Status</label>-->
            <!--    <select class="form-select" id="invoiceStatus" name="inv_status" onchange="toggleRemarkFields()">-->
            <!--        <option value="Done" <?php //if ($is_edit && $inv_status == 'Done') echo 'selected'; ?>>Done</option>-->
            <!--        <option value="Objection" <?php //if ($is_edit && $inv_status == 'Objection') echo 'selected'; ?>>Objection</option>-->
            <!--        <option value="Pending" <?php //if ($is_edit && $inv_status == 'Pending') echo 'selected'; ?>>Pending</option>-->
            <!--        <option value="Return To Client" <?php //if ($is_edit && $inv_status == 'Return To Client') echo 'selected'; ?>>Return To Client</option>-->
            <!--    </select>-->
            <!--</div>-->
            
            

        </div>
            
            
        <div class="row g-3 mb-3">
            
            <div class="col-md-6 field">
                <label for="remark" class="form-label">Responsibility</label>
                <textarea class="form-control" name="responsibility" id="responsibility" placeholder="Enter responsibility"><?php echo htmlspecialchars($responsibility); ?></textarea>
            </div>
            
            <div class="col-md-6 mb-3 field">
                <label for="remark" class="form-label">Remark</label>
                <textarea class="form-control" id="remark" name="remark" placeholder="Enter Remark"><?php echo htmlspecialchars($remark); ?></textarea>
            </div>
            
        </div>
        
        <div class="col-md-6 mt-3 mx-auto p-2 field" style="background-color: #ffcdcd;">
            <label for="motorSubType" class="form-label">Work Status</label>
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

// Handle Adviser selection
function handleAdviserChange() {
    var companySelect = document.getElementById('adviser');
    var customCompanyInput = document.getElementById('customadviser');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'adviser_name'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'advisercustom'; // Reset to avoid conflicts
    }
}

// Handle Type Of Work selection
function handleTypeWorkChange() {
    var companySelect = document.getElementById('type_work');
    var customCompanyInput = document.getElementById('type_work_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'type_work'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'type_work_custom'; // Reset to avoid conflicts
    }
}


// Handle DL Type Of Work selection
function handleDLTypeWorkChange() {
    var companySelect = document.getElementById('dl_type_work');
    var customCompanyInput = document.getElementById('dl_type_work_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'dl_type_work'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'dl_type_work_custom'; // Reset to avoid conflicts
    }
}

// Handle TR Type Of Work selection
function handleTRTypeWorkChange() {
    var companySelect = document.getElementById('tr_type_work');
    var customCompanyInput = document.getElementById('tr_type_work_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'tr_type_work'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'tr_type_work_custom'; // Reset to avoid conflicts
    }
}

// Handle DL Type Of Work selection
function handleNTTypeWorkChange() {
    var companySelect = document.getElementById('nt_type_work');
    var customCompanyInput = document.getElementById('nt_type_work_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'nt_type_work'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'nt_type_work_custom'; // Reset to avoid conflicts
    }
} 

// Handle DL Type Of Work selection
function handleVehicleClassChange() {
    var companySelect = document.getElementById('vehicle_class');
    var customCompanyInput = document.getElementById('vehicle_class_custom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'vehicle_class'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'vehicle_class_custom'; // Reset to avoid conflicts
    }
}


// Change category dropdown

function handleCategoryChange() {
    // Get the selected category value
    var category = document.getElementById('category').value;

    // Hide all specific Type Of Work dropdowns
    document.getElementById('dlTypeWork').style.display = 'none';
    document.getElementById('trTypeWork').style.display = 'none';
    document.getElementById('ntTypeWork').style.display = 'none';

    // Show the relevant Type Of Work dropdown based on category
    if (category === 'DL') {
        document.getElementById('dlTypeWork').style.display = 'block';
    } else if (category === 'TR') {
        document.getElementById('trTypeWork').style.display = 'block';
    } else if (category === 'NT') {
        document.getElementById('ntTypeWork').style.display = 'block';
    }
}


// Function to validate numeric input and calculate the recovery amount
function validateAndCalculateRecovery(input) {
    // Ensure only numbers are allowed
    input.value = input.value.replace(/[^0-9]/g, '');

    // Call the recovery calculation function
    calculateRecovery();
}

// Function to calculate the recovery amount
function calculateRecovery() {
    const quotationAmount = parseFloat(document.getElementById('quotationAmount').value) || 0;
    const advanceAmount = parseFloat(document.getElementById('advanceAmount').value) || 0;
    const govAmount = parseFloat(document.getElementById('govAmount').value) || 0;
    
    // Calculate recovery amount
    const recoveryAmount = quotationAmount - advanceAmount;
    const cashinhandAmt =  advanceAmount - govAmount;
    
    // Update the recovery amount input field
    document.getElementById('recoveryAmount').value = recoveryAmount > 0 ? recoveryAmount : 0;  // Set to 0 if negative
    document.getElementById('cashinhandAmt').value = cashinhandAmt > 0 ? cashinhandAmt : 0;  // Set to 0 if negative
}
</script>


<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>