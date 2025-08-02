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
$policy_duration = '';
$start_date = '';
$end_date = '';
$client_type = '';
$policy_type = '';
$sub_type = '';
$mv_number = '';
$vehicle_type = '';
$nonmotor_type_select = '';
$nonmotor_subtype = '';
$policy_company = '';
$policy_number = '';
$amount = '';
$pay_mode = '';
$cheque_no = '';
$bank_name = '';
$cheque_dt = '';
$remark = '';
$nonmotor_type = '';
$nonmotor_subtype_select = '';
$contact_alt ='';
$email ='';
$policycustom= '';
$recovery_amount = '';
$policyInput = '';
$adv_amount = '';
$bal_amount = '';
$recov_amount = '';
$address = '';
$responsibility = '';
$form_status = '';
$birth_date = '';
$adviser_name = '';
$vehicle = '';
$branch_name = '';
$client_id = '';
$year_count = '';
$is_long_term = '';
$fiscalYear = '';
$errors = [];
$is_edit = false;
$add_new = false;
$add_client = false;
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
    $result = $conn->query("SELECT * FROM gic_entries WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $client_id  = $row['client_id'];
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $contact = $row['contact'];
        $policy_duration = $row['policy_duration'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $client_type = $row['client_type'];
        $policy_type = $row['policy_type'];
        $sub_type = $row['sub_type'];
        $mv_number = strtoupper($row['mv_number']);
        $vehicle_type = strtoupper($row['vehicle_type']);
        $nonmotor_type_select = strtoupper($row['nonmotor_type_select']);
        $nonmotor_subtype = $row['nonmotor_subtype'];
        $policy_company = strtoupper($row['policy_company']);
        $policy_number = strtoupper($row['policy_number']);
        $amount = $row['amount'];
        $pay_mode = $row['pay_mode'];
        $cheque_no = $row['cheque_no'];
        $bank_name = strtoupper($row['bank_name']);
        $cheque_dt = $row['cheque_dt'];
        $remark = strtoupper($row['remark']);
        $nonmotor_type = $row['nonmotor_type'];
        $nonmotor_subtype_select = strtoupper($row['nonmotor_subtype_select']);
        $contact_alt = $row['contact_alt'];
        $email = $row['email'];
        $policycustom = $row['policycustom'];
        $recovery_amount = $row['recovery_amount'];
        $policyInput = $row['policyInput'];
        $adv_amount = $row['adv_amount'];
        $bal_amount = $row['bal_amount'];
        $recov_amount = $row['recov_amount'];
        $address = strtoupper($row['address']);
        $responsibility = strtoupper($row['responsibility']);
        $form_status = strtoupper($row['form_status']);
        $birth_date = $row['birth_date'];
        $adviser_name = strtoupper($row['adviser_name']);
        $vehicle = strtoupper($row['vehicle']);
        $branch_name = strtoupper($row['branch_name']);
        $year_count = $row['year_count'];
        $is_long_term = $row['is_long_term'];
    } else {
        die("Entry not found.");
    }
} 

elseif (isset($_GET['action']) && $_GET['action'] === 'add_new') {
    
    $is_edit = false;
    $add_client = false;
    $add_new = true;

    // Fetch the maximum registration number for the current fiscal year
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM gic_entries WHERE policy_date BETWEEN '$fiscalYearStartDate' AND '$fiscalYearEndDate'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = isset($row['max_reg_num']) ? $row['max_reg_num'] + 1 : 1;
    } else {
        $reg_num = 1;
    }

    // Only fetch the client_name and contact if provided in the query
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM gic_entries WHERE id=$id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_id = $row['client_id'];
            $client_name = strtoupper($row['client_name']);
            $contact = $row['contact'];
            $policy_duration = $row['policy_duration'];
            $start_date = $row['start_date'];
            $end_date = $row['end_date'];
            $client_type = $row['client_type'];
            $policy_type = $row['policy_type'];
            $sub_type = $row['sub_type'];
            $mv_number = strtoupper($row['mv_number']);
            $vehicle_type = strtoupper($row['vehicle_type']);
            $nonmotor_type_select = strtoupper($row['nonmotor_type_select']);
            $nonmotor_subtype = $row['nonmotor_subtype'];
            $policy_company = strtoupper($row['policy_company']);
            $policy_number = strtoupper($row['policy_number']);
            $amount = $row['amount'];
            $pay_mode = $row['pay_mode'];
            $cheque_no = $row['cheque_no'];
            $bank_name = strtoupper($row['bank_name']);
            $cheque_dt = $row['cheque_dt'];
            $remark = strtoupper($row['remark']);
            $nonmotor_type = $row['nonmotor_type'];
            $nonmotor_subtype_select = strtoupper($row['nonmotor_subtype_select']);
            $contact_alt = $row['contact_alt'];
            $email = $row['email'];
            $policycustom = $row['policycustom'];
            $recovery_amount = $row['recovery_amount'];
            $policyInput = $row['policyInput'];
            $adv_amount = $row['adv_amount'];
            $bal_amount = $row['bal_amount'];
            $recov_amount = $row['recov_amount'];
            $address = strtoupper($row['address']);
            $responsibility = strtoupper($row['responsibility']);
            $form_status = strtoupper($row['form_status']);
            $birth_date = $row['birth_date'];
            $adviser_name = strtoupper($row['adviser_name']);
            $vehicle = strtoupper($row['vehicle']);
            $branch_name = strtoupper($row['branch_name']);
        } else {
            die("Entry not found.");
        }
    }
} 

if (isset($_GET['action']) && $_GET['action'] === 'add_client') {
    $is_edit = false;
    $add_new = false;
    $add_client = true;

    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num FROM gic_entries WHERE policy_date BETWEEN '$fiscalYearStartDate' AND '$fiscalYearEndDate'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = isset($row['max_reg_num']) ? $row['max_reg_num'] + 1 : 1;
    } else {
        $reg_num = 1;
    }

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT client_name, client_type, contact, contact_alt, address, email, birth_date FROM client WHERE id=$id");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_name = $row['client_name'];
            $client_type = $row['client_type'];
            $contact = $row['contact'];
            $contact_alt = $row['contact_alt'];
            $address = $row['address'];
            $email = $row['email'];
            $birth_date = $row['birth_date'];
        } else {
            $client_name = '';
            $client_type = '';
            $contact = '';
            $contact_alt = '';
            $address = '';
            $email = '';
            $birth_date = '';
        }
    }
}



// Handle form submission (add, edit, or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Collect and sanitize inputs (All converted to uppercase where relevant)
$reg_num = strtoupper(trim($_POST['reg_num']));
$policy_date = strtoupper(trim($_POST['policy_date']));
$time = strtoupper(trim($_POST['time']));
$client_name = strtoupper(trim($_POST['client_name']));
$contact = strtoupper(trim($_POST['contact']));
$policy_duration = isset($_POST['policy_duration']) ? strtoupper(trim($_POST['policy_duration'])) : '';
$start_date = isset($_POST['start_date']) ? strtoupper(trim($_POST['start_date'])) : '';
$end_date = isset($_POST['end_date']) ? strtoupper(trim($_POST['end_date'])) : '';
$client_type = isset($_POST['client_type']) ? strtoupper(trim($_POST['client_type'])) : '';
$policy_type = isset($_POST['policy_type']) ? strtoupper(trim($_POST['policy_type'])) : '';
$sub_type = isset($_POST['sub_type']) ? strtoupper(trim($_POST['sub_type'])) : '';
$mv_number = isset($_POST['mv_number']) ? strtoupper(trim($_POST['mv_number'])) : '';
$vehicle_type = isset($_POST['vehicle_type']) ? strtoupper(trim($_POST['vehicle_type'])) : '';
$vehicleType = isset($_POST['vehicleType']) ? strtoupper(trim($_POST['vehicleType'])) : '';
$policy_company = isset($_POST['policy_company']) ? strtoupper(trim($_POST['policy_company'])) : '';
$policycustom = isset($_POST['policycustom']) ? strtoupper(trim($_POST['policycustom'])) : '';
$policy_number = isset($_POST['policy_number']) ? strtoupper(trim($_POST['policy_number'])) : '';
$amount = isset($_POST['amount']) ? strtoupper(trim($_POST['amount'])) : '';
$pay_mode = isset($_POST['pay_mode']) ? strtoupper(trim($_POST['pay_mode'])) : '';
$cheque_no = isset($_POST['cheque_no']) ? strtoupper(trim($_POST['cheque_no'])) : '';
$bank_name = isset($_POST['bank_name']) ? strtoupper(trim($_POST['bank_name'])) : '';
$bank_name_custom = isset($_POST['bank_name_custom']) ? strtoupper(trim($_POST['bank_name_custom'])) : '';
$cheque_dt = isset($_POST['cheque_dt']) ? strtoupper(trim($_POST['cheque_dt'])) : '';
$remark = isset($_POST['remark']) ? strtoupper(trim($_POST['remark'])) : '';
$contact_alt = isset($_POST['contact_alt']) ? strtoupper(trim($_POST['contact_alt'])) : '';
$email = isset($_POST['email']) ? strtoupper(trim($_POST['email'])) : '';
$recovery_amount = isset($_POST['recovery_amount']) ? strtoupper(trim($_POST['recovery_amount'])) : '';
$policyInput = isset($_POST['policyInput']) ? strtoupper(trim($_POST['policyInput'])) : '';
$adv_amount = isset($_POST['adv_amount']) ? strtoupper(trim($_POST['adv_amount'])) : '';
$bal_amount = isset($_POST['bal_amount']) ? strtoupper(trim($_POST['bal_amount'])) : '';
$recov_amount = isset($_POST['recov_amount']) ? strtoupper(trim($_POST['recov_amount'])) : '';
$address = isset($_POST['address']) ? strtoupper(trim($_POST['address'])) : '';
$responsibility = isset($_POST['responsibility']) ? strtoupper(trim($_POST['responsibility'])) : '';
$form_status = isset($_POST['form_status']) ? strtoupper(trim($_POST['form_status'])) : '';
$birth_date = isset($_POST['birth_date']) ? strtoupper(trim($_POST['birth_date'])) : '';
$adviser_name = isset($_POST['adviser_name']) ? strtoupper(trim($_POST['adviser_name'])) : '';
$advisercustom = isset($_POST['advisercustom']) ? strtoupper(trim($_POST['advisercustom'])) : '';
$vehicle = isset($_POST['vehicle']) ? strtoupper(trim($_POST['vehicle'])) : '';
$vehiclecustom = isset($_POST['vehiclecustom']) ? strtoupper(trim($_POST['vehiclecustom'])) : '';
$branch_name = isset($_POST['branch_name']) ? strtoupper(trim($_POST['branch_name'])) : '';
$branch_name_custom = isset($_POST['branch_name_custom']) ? strtoupper(trim($_POST['branch_name_custom'])) : '';
$client_id = strtoupper(trim($_POST['client_id']));
$year_count = strtoupper(trim($_POST['year_count']));
$is_long_term = strtoupper(trim($_POST['is_long_term']));

// For NonMotor policies
$nonmotor_type_select = isset($_POST['nonmotor_type_select']) ? strtoupper(trim($_POST['nonmotor_type_select'])) : '';
$nonmotor_type = isset($_POST['nonmotor_type']) ? strtoupper(trim($_POST['nonmotor_type'])) : '';
$nonmotor_subtype_select = isset($_POST['nonmotor_subtype_select']) ? strtoupper(trim($_POST['nonmotor_subtype_select'])) : '';
$nonmotor_subtype = isset($_POST['nonmotor_subtype']) ? strtoupper(trim($_POST['nonmotor_subtype'])) : '';

    
    
    // Validation
    if (empty($client_name) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $client_name)) {
        $errors[] = "Invalid client name";
    }

    
    if (empty($contact) && !preg_match("/^\d{10}$/", $contact)) {
        $errors[] = "Invalid Mobile number";
    }
    
    if (!empty($contact_alt) && !preg_match("/^\d{10}$/", $contact_alt)) {
        $errors[] = "Invalid Alternate Mobile Number";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }
    
    if ($policy_type == 'NONMOTOR') {
        // Non-Motor Type
        if (empty($nonmotor_type_select)) {
            $errors[] = "Policy Type is required.";
        } elseif (!preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\\|]+$/", $nonmotor_type_select)) {
            $errors[] = "Invalid Policy Type.";
        }

        // Non-Motor Sub-Type
        if (empty($nonmotor_subtype_select)) {
            $errors[] = "Sub Type is required.";
        } elseif (!preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\\|]+$/", $nonmotor_subtype_select)) {
            $errors[] = "Invalid Sub Type.";
        }
    }

    if ($policy_type == 'MOTOR') {
        // MV Number
        if (empty($mv_number)) {
            $errors[] = "MV No. is required.";
        } elseif (!preg_match('/^[A-Z0-9\*]+$/', $mv_number)) {
            $errors[] = "Invalid MV No. Only uppercase letters and numbers are allowed.";
        }

        // Vehicle
        if (empty($vehicle)) {
            $errors[] = "Vehicle is required.";
        } elseif (!preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\\|]+$/", $vehicle)) {
            $errors[] = "Invalid vehicle.";
        }
    }
    
    if (!empty($amount) && !is_numeric($amount)) {
        $errors[] = "Invalid Amount";
    }
    
    if (!empty($adv_amount) && !is_numeric($adv_amount)) {
        $errors[] = "Invalid Advance Amount";
    }
    
    

    // Policy Company
    if (empty($policy_company)) {
        $errors[] = "Policy company is required.";
    } elseif (!preg_match("/^[A-Za-z ]+$/", $policy_company)) {
        $errors[] = "Invalid policy company.";
    }

    
    
   
    
    if ($pay_mode == 'Cheque') {
        if (!empty($cheque_no) && !is_numeric($cheque_no)) {
            $errors[] = "Invalid Cheque Number";
        }
        
        if (!empty($bank_name) && !preg_match("/^[A-Za-z ]+$/", $bank_name)) {
            $errors[] = "Invalid Bank Name";
        }
    }
    
    if ($pay_mode == 'Recovery') {
        if (!empty($recovery_amount) && !is_numeric($recovery_amount)) {
            $errors[] = "Invalid Recovery Amount";
        }
    }
    
    if (!empty($policy_number) && !preg_match('/^[0-9a-zA-Z\/\*\-\s]+$/', $policy_number)) {
        $errors[] = "Invalid policy number.";
    }

    
    if (!empty($remark) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $remark)) {
        $errors[] = "Invalid remark";
    }

    
    if (!empty($responsibility) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $responsibility)) {
        $errors[] = "Invalid responsibility";
    }
    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $time = date('H:i:s'); // Get current time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone

    // echo "<pre>";
    
    // print_r($_POST);
    
    // exit;

    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {
            $policy_type = $_POST['policy_type'];
            
            if ($policy_type === 'Motor') {
                $vehicle_type = $_POST['vehicle_type'] === 'Enter Manually' ? $_POST['vehicleType'] : $_POST['vehicle_type'];
                $policy_company = $_POST['policy_company'] === 'Enter Manually' ? $_POST['policycustom'] : $_POST['policy_company'];
                $adviser_name = $_POST['adviser_name'] === 'Enter Manually' ? $_POST['advisercustom'] : $_POST['adviser_name'];
                $vehicle = $_POST['vehicle'] === 'Enter Manually' ? $_POST['vehiclecustom'] : $_POST['vehicle'];
                $branch_name = $_POST['branch_name'] === 'Enter Manually' ? $_POST['branch_name_custom'] : $_POST['branch_name'];
                $bank_name = $_POST['bank_name'] === 'Enter Manually' ? $_POST['bank_name_custom'] : $_POST['bank_name'];
                
                $sql = "UPDATE gic_entries SET client_id = '$client_id', reg_num='$reg_num', policy_date='$policy_date',time='$time', client_name='$client_name', contact='$contact', policy_duration='$policy_duration', start_date='$start_date', end_date='$end_date', client_type='$client_type', policy_type='$policy_type', sub_type='$sub_type', mv_number='$mv_number', vehicle_type='$vehicle_type',policy_company='$policy_company',policy_number='$policy_number', amount='$amount', pay_mode='$pay_mode', cheque_no='$cheque_no', bank_name='$bank_name', cheque_dt='$cheque_dt', remark='$remark',contact_alt='$contact_alt', email='$email', policycustom='$policycustom',recovery_amount='$recovery_amount', policyInput='$policyInput', adv_amount='$adv_amount', bal_amount='$bal_amount', recov_amount='$recov_amount', address='$address',fiscal_year='$fiscalYear',responsibility='$responsibility',form_status='$form_status',birth_date='$birth_date',update_on='$update_on',adviser_name='$adviser_name',vehicle = '$vehicle',branch_name = '$branch_name',year_count='$year_count',is_long_term='$is_long_term' WHERE id=$id";
            }
            elseif ($policy_type === 'NonMotor'){
                $nonmotor_type_select = $_POST['nonmotor_type_select'] === 'Enter Manually' ? $_POST['nonmotor_type'] : $_POST['nonmotor_type_select'];
                $nonmotor_subtype_select = $_POST['nonmotor_subtype_select'] === 'Enter Manually' ? $_POST['nonmotor_subtype'] : $_POST['nonmotor_subtype_select'];
                $policy_company = $_POST['policy_company'] === 'Enter Manually' ? $_POST['policycustom'] : $_POST['policy_company'];
                $adviser_name = $_POST['adviser_name'] === 'Enter Manually' ? $_POST['advisercustom'] : $_POST['adviser_name'];
                $branch_name = $_POST['branch_name'] === 'Enter Manually' ? $_POST['branch_name_custom'] : $_POST['branch_name'];
                $bank_name = $_POST['bank_name'] === 'Enter Manually' ? $_POST['bank_name_custom'] : $_POST['bank_name'];
                
                $sql = "UPDATE gic_entries SET client_id = '$client_id', reg_num='$reg_num', policy_date='$policy_date',time='$time', client_name='$client_name', contact='$contact', policy_duration='$policy_duration', start_date='$start_date', end_date='$end_date', client_type='$client_type', policy_type='$policy_type', nonmotor_type_select='$nonmotor_type_select', nonmotor_type='$nonmotor_type', nonmotor_subtype='$nonmotor_subtype',nonmotor_subtype_select='$nonmotor_subtype_select',policy_company='$policy_company',policy_number='$policy_number', amount='$amount', pay_mode='$pay_mode', cheque_no='$cheque_no', bank_name='$bank_name', cheque_dt='$cheque_dt', remark='$remark', contact_alt='$contact_alt', email='$email', policycustom='$policycustom', recovery_amount='$recovery_amount', policyInput='$policyInput', adv_amount='$adv_amount', bal_amount='$bal_amount', recov_amount='$recov_amount', address='$address',fiscal_year='$fiscalYear',responsibility='$responsibility',form_status='$form_status',birth_date='$birth_date',update_on='$update_on',adviser_name='$adviser_name',branch_name = '$branch_name',year_count='$year_count',is_long_term='$is_long_term' WHERE id=$id";
            }

            if (isset($sql) && $conn->query($sql) === TRUE) {
                header("Location: gic");
                exit();
            } else {
                echo "Error updating record: " . ($conn->error ?? "SQL query not properly constructed");
            }
        } 
        elseif ($add_new) {
            $original_id = $_GET['id'];
            $policy_type = $_POST['policy_type'];

            $deleteOldPolicy = "DELETE FROM gic_entries WHERE id = '$original_id'";
            $conn->query($deleteOldPolicy);
            
            if ($policy_type === 'Motor') {
                $policy_company = strtoupper(trim($_POST['policy_company'] ?? ''));
                $policycustom = strtoupper(trim($_POST['policycustom'] ?? ''));
                if ($policy_company == "Enter Manually") {
                    $policy_company = strtoupper($policycustom);
                }

                $adviser_name = strtoupper(trim($_POST['adviser_name']));
                $advisercustom = strtoupper(trim($_POST['advisercustom'] ?? ''));
                if ($adviser_name == "ENTER MANUALLY") {
                    $adviser_name = $advisercustom;
                }

                $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
                $vehicleType = strtoupper(trim($_POST['vehicleType'] ?? ''));
                if ($vehicle_type == "ENTER MANUALLY") {
                    $vehicle_type = $vehicleType;
                }

                $vehicle = strtoupper(trim($_POST['vehicle']));
                $vehiclecustom = strtoupper(trim($_POST['vehiclecustom'] ?? ''));
                if ($vehicle == "ENTER MANUALLY") {
                    $vehicle = $vehiclecustom;
                }

                $bank_name = strtoupper(trim($_POST['bank_name']));
                $bank_name_custom = strtoupper(trim($_POST['bank_name_custom'] ?? ''));
                if ($bank_name == "ENTER MANUALLY") {
                    $bank_name = $bank_name_custom;
                }

                $branch_name = strtoupper(trim($_POST['branch_name']));
                $branch_name_custom = strtoupper(trim($_POST['branch_name_custom'] ?? ''));
                if ($branch_name == "ENTER MANUALLY") {
                    $branch_name = $branch_name_custom;
                }
                
                $sql = "INSERT INTO gic_entries (client_id,reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, client_type, policy_type, sub_type, mv_number, vehicle_type, policy_company, policy_number, amount, pay_mode, cheque_no, bank_name, cheque_dt, remark, contact_alt, email, policycustom, recovery_amount, policyInput, adv_amount, bal_amount, recov_amount, address, username, fiscal_year, responsibility, form_status, creation_on, birth_date,adviser_name,vehicle,branch_name,is_renewed, renewal_of,year_count,is_long_term) 
                        VALUES ('$client_id','$reg_num', '$policy_date','$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$client_type', '$policy_type', '$sub_type', '$mv_number', '$vehicle_type', '$policy_company', '$policy_number', '$amount', '$pay_mode', '$cheque_no', '$bank_name', '$cheque_dt', '$remark', '$contact_alt', '$email', '$policycustom', '$recovery_amount', '$policyInput', '$adv_amount', '$bal_amount', '$recov_amount', '$address', '$username', '$fiscalYear', '$responsibility', '$form_status', '$creation_on', '$birth_date','$adviser_name','$vehicle','$branch_name', '1', '$original_id','$year_count','$is_long_term')";
            } 
            elseif ($policy_type === 'NonMotor') {
                $nonmotor_type_select = strtoupper(trim($_POST['nonmotor_type_select']));
                $nonmotor_type = strtoupper(trim($_POST['nonmotor_type'] ?? ''));
                if ($nonmotor_type_select == "ENTER MANUALLY" || $nonmotor_type_select == "Enter Manually") {
                    $nonmotor_type_select = $nonmotor_type;
                }

                $nonmotor_subtype_select = strtoupper(trim($_POST['nonmotor_subtype_select']));
                $nonmotor_subtype = strtoupper(trim($_POST['nonmotor_subtype'] ?? ''));
                if ($nonmotor_subtype_select == "ENTER MANUALLY" || $nonmotor_subtype_select == "Enter Manually") {
                    $nonmotor_subtype_select = $nonmotor_subtype;
                }

                $policy_company = strtoupper(trim($_POST['policy_company'] ?? ''));
                $policycustom = strtoupper(trim($_POST['policycustom'] ?? ''));
                if ($policy_company == "Enter Manually") {
                    $policy_company = trim($policycustom);
                }

                $adviser_name = strtoupper(trim($_POST['adviser_name']));
                $advisercustom = strtoupper(trim($_POST['advisercustom'] ?? ''));
                if ($adviser_name == "Enter Manually") {
                    $adviser_name = $advisercustom;
                }


                $bank_name = strtoupper(trim($_POST['bank_name']));
                $bank_name_custom = strtoupper(trim($_POST['bank_name_custom'] ?? ''));
                if ($bank_name == "Enter Manually") {
                    $bank_name = $bank_name_custom;
                }

                $branch_name = strtoupper(trim($_POST['branch_name']));
                $branch_name_custom = strtoupper(trim($_POST['branch_name_custom'] ?? ''));
                if ($branch_name == "Enter Manually") {
                    $branch_name = $branch_name_custom;
                }

                $sql = "INSERT INTO gic_entries (client_id,reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, client_type, policy_type, nonmotor_type_select, nonmotor_type, nonmotor_subtype_select, nonmotor_subtype, policy_company, policy_number, amount, pay_mode, cheque_no, bank_name, cheque_dt, remark, contact_alt, email, policycustom,  recovery_amount, policyInput, adv_amount, bal_amount, recov_amount, address, username, fiscal_year, responsibility, form_status, creation_on, birth_date,adviser_name,branch_name,is_renewed, renewal_of,year_count,is_long_term) 
                        VALUES ('$client_id','$reg_num', '$policy_date','$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$client_type', '$policy_type', '$nonmotor_type_select', '$nonmotor_type', '$nonmotor_subtype_select', '$nonmotor_subtype', '$policy_company', '$policy_number', '$amount', '$pay_mode', '$cheque_no', '$bank_name', '$cheque_dt', '$remark', '$contact_alt', '$email', '$policycustom', '$recovery_amount', '$policyInput', '$adv_amount', '$bal_amount', '$recov_amount', '$address', '$username', '$fiscalYear', '$responsibility', '$form_status', '$creation_on', '$birth_date','$adviser_name','$branch_name', '1', '$original_id','$year_count','$is_long_term')";
            }

            if (isset($sql) && $conn->query($sql) === TRUE) {
                header("Location: gic");
                exit();
            } else {
                echo "Error: " . ($conn->error ?? "SQL query not properly constructed");
            }
        }
        elseif ($add_client) {
            $username = $_SESSION['username'];
            $policy_type = $_POST['policy_type'];
            
        
            if ($policy_type === 'Motor') {
                $mv_number = $_POST['mv_number'];
        
                $mv_check_sql = "SELECT * FROM gic_entries WHERE mv_number = '$mv_number'";
                $mv_check_result = $conn->query($mv_check_sql);
        
                if ($mv_check_result->num_rows > 0) {
                    $errors[] = "A record with this MV Number already exists.";
                } else {
                    $policy_company = strtoupper(trim($_POST['policy_company'] ?? ''));
                    $policycustom = strtoupper(trim($_POST['policycustom'] ?? ''));
                    if ($policy_company == "Enter Manually") {
                        $policy_company = strtoupper($policycustom);
                    }

                    $adviser_name = strtoupper(trim($_POST['adviser_name']));
                    $advisercustom = strtoupper(trim($_POST['advisercustom'] ?? ''));
                    if ($adviser_name == "ENTER MANUALLY") {
                        $adviser_name = $advisercustom;
                    }


                    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
                    $vehicleType = strtoupper(trim($_POST['vehicleType'] ?? ''));
                    if ($vehicle_type == "ENTER MANUALLY") {
                        $vehicle_type = $vehicleType;
                    }

                    $vehicle = strtoupper(trim($_POST['vehicle']));
                    $vehiclecustom = strtoupper(trim($_POST['vehiclecustom'] ?? ''));
                    if ($vehicle == "ENTER MANUALLY") {
                        $vehicle = $vehiclecustom;
                    }

                    $bank_name = strtoupper(trim($_POST['bank_name']));
                    $bank_name_custom = strtoupper(trim($_POST['bank_name_custom'] ?? ''));
                    if ($bank_name == "ENTER MANUALLY") {
                        $bank_name = $bank_name_custom;
                    }

                    $branch_name = strtoupper(trim($_POST['branch_name']));
                    $branch_name_custom = strtoupper(trim($_POST['branch_name_custom'] ?? ''));
                    if ($branch_name == "ENTER MANUALLY") {
                        $branch_name = $branch_name_custom;
                    }

                    $sql = "INSERT INTO gic_entries (client_id,reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, client_type, policy_type, sub_type, mv_number, vehicle_type, policy_company, policy_number, amount, pay_mode, cheque_no, bank_name, cheque_dt, remark, contact_alt, email, policycustom,  recovery_amount, policyInput, adv_amount, bal_amount, recov_amount, address, username, fiscal_year, responsibility, form_status, creation_on, birth_date,adviser_name,vehicle,branch_name,year_count,is_long_term) 
                        VALUES ('$client_id','$reg_num', '$policy_date','$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$client_type', '$policy_type', '$sub_type', '$mv_number', '$vehicle_type', '$policy_company', '$policy_number', '$amount', '$pay_mode', '$cheque_no', '$bank_name', '$cheque_dt', '$remark', '$contact_alt', '$email', '$policycustom', '$recovery_amount', '$policyInput', '$adv_amount', '$bal_amount', '$recov_amount', '$address', '$username', '$fiscalYear', '$responsibility', '$form_status', '$creation_on', '$birth_date','$adviser_name','$vehicle','$branch_name','$year_count','$is_long_term')";
                }
            } 
            elseif ($policy_type === 'NonMotor') {
                $nonmotor_type_select = strtoupper(trim($_POST['nonmotor_type_select']));
                $nonmotor_type = strtoupper(trim($_POST['nonmotor_type'] ?? ''));
                if ($nonmotor_type_select == "ENTER MANUALLY" || $nonmotor_type_select == "Enter Manually") {
                    $nonmotor_type_select = $nonmotor_type;
                }

                $nonmotor_subtype_select = strtoupper(trim($_POST['nonmotor_subtype_select']));
                $nonmotor_subtype = strtoupper(trim($_POST['nonmotor_subtype'] ?? ''));
                if ($nonmotor_subtype_select == "ENTER MANUALLY" || $nonmotor_subtype_select == "Enter Manually") {
                    $nonmotor_subtype_select = $nonmotor_subtype;
                }

                $policy_company = strtoupper(trim($_POST['policy_company'] ?? ''));
                $policycustom = strtoupper(trim($_POST['policycustom'] ?? ''));
                if ($policy_company == "Enter Manually") {
                    $policy_company = trim($policycustom);
                }

                $adviser_name = strtoupper(trim($_POST['adviser_name']));
                $advisercustom = strtoupper(trim($_POST['advisercustom'] ?? ''));
                if ($adviser_name == "Enter Manually") {
                    $adviser_name = $advisercustom;
                }

         
                

                $bank_name = strtoupper(trim($_POST['bank_name'] ?? ''));
                $bank_name_custom = strtoupper(trim($_POST['bank_name_custom'] ?? ''));
                if ($bank_name == "Enter Manually") {
                    $bank_name = $bank_name_custom;
                }

                $branch_name = strtoupper(trim($_POST['branch_name'] ?? ''));
                $branch_name_custom = strtoupper(trim($_POST['branch_name_custom'] ?? ''));
                if ($branch_name == "Enter Manually") {
                    $branch_name = $branch_name_custom;
                }

                $sql = "INSERT INTO gic_entries (client_id,reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, client_type, policy_type, nonmotor_type_select, nonmotor_type, nonmotor_subtype_select, nonmotor_subtype, policy_company, policy_number, amount, pay_mode, cheque_no, bank_name, cheque_dt, remark, contact_alt, email, policycustom,  recovery_amount, policyInput, adv_amount, bal_amount, recov_amount, address, username, fiscal_year, responsibility, form_status, creation_on, birth_date,adviser_name,branch_name,vehicle,year_count,is_long_term) 
                    VALUES ('$client_id','$reg_num', '$policy_date','$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$client_type', '$policy_type', '$nonmotor_type_select', '$nonmotor_type', '$nonmotor_subtype_select', '$nonmotor_subtype', '$policy_company', '$policy_number', '$amount', '$pay_mode', '$cheque_no', '$bank_name', '$cheque_dt', '$remark', '$contact_alt', '$email', '$policycustom', '$recovery_amount', '$policyInput', '$adv_amount', '$bal_amount', '$recov_amount', '$address', '$username', '$fiscalYear', '$responsibility', '$form_status', '$creation_on', '$birth_date','$adviser_name','$branch_name','$vehicle','$year_count','$is_long_term')";
            }

            if (isset($sql) && empty($errors)) {
                if ($conn->query($sql) === TRUE) {
                    header("Location: gic");
                    exit();
                } else {
                    echo "Error: " . $conn->error;
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
                <h1>GIC FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="gic">GIC</a></li>
                <li class="breadcrumb-item active" aria-current="page">GIC FORM</li>
              </ol>
            </nav>
        </div>
        
        <form 
           action="gic-form.php<?php 
    echo $is_edit ? '?action=edit&id=' . $id : 
        ($add_new ? '?action=add_new&id=' . $id : 
        ($add_client ? '?action=add_client' : '')); 
?>"

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
                    } elseif ($add_new) {
                        echo htmlspecialchars($client_id);
                    } else {
                        echo htmlspecialchars($id);
                    }
                ?>"
            >
            
            <div class="row g-3 mb-3">

                <div class="col-md-6 field">
                    <label for="registerNumber" class="form-label">Register Number</label>
                    <input type="text" class="form-control" name="reg_num" id="registerNumber"  value="<?= htmlspecialchars($reg_num) ?>" >
                </div>
       
            
                <div class="col-md-6 field">
                    <label for="time" class="form-label">Time</label>
                    <input type="time" class="form-control text-success" name="time" id="time" 
                        value="<?php 
                            if ($is_edit) {
                                echo htmlspecialchars(date('H:i', strtotime($time))); // Show existing time in edit mode
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
                
                

                <div class="col-md-6 field">
                    <label for="clientName" class="form-label">Client Type</label>
                    <input type="text" class="form-control" name="client_type" value="<?php echo htmlspecialchars($client_type); ?>"  placeholder="Enter Type" required readonly>
                </div>


                
            </div>
            
            <div class="row g-3 mb-3">
                <!-- Mobile Number -->
                <div class="col-md-6 field">
                    <label for="mobileNumber" class="form-label">Mobile Number</label>
                    <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact) ?? $row['contact']; ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" required readonly>
                </div>
                
                <div class="col-md-6 field">
                    <label for="mobileNumber" class="form-label">Alternate Mobile Number</label>
                    <input type="tel" class="form-control" name="contact_alt" value="<?php echo htmlspecialchars($contact_alt  ?? $row['contact_alt']); ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" readonly>
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
            
              <!-- Client Type -->
            <div class="row g-3 mb-3">
            
                <!-- Policy Type -->
                <div class="col-md-6 field">
                    <label for="policyType" class="form-label">Policy Type</label>
                    <select class="form-select" name="policy_type" id="policyType">
                        <option value="Motor" <?php echo ($is_edit && $policy_type == 'Motor') || ($add_new && $policy_type == 'Motor') ? 'selected' : ''; ?> selected >Motor</option>
                        <option value="NonMotor" <?php echo ($is_edit && $policy_type == 'NonMotor') || ($add_new && $policy_type == 'NonMotor') ? 'selected' : ''; ?>>Non Motor</option>
                    </select>
                </div>
                
                <div class="col-md-6 field1">
                    <label for="mobileNumber" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email) ?? $row['email']; ?>" id="mobileNumber" placeholder="Enter email address" readonly>
                </div>
                
            </div>
    
            
            
        
        
    
        <!-- Motor Policy Subtype -->
        <div id="motorPolicy" style="display: <?php echo ($is_edit && $policy_type == 'Motor') || ($add_new && $policy_type == 'Motor') || !$is_edit && !$add_new ? 'block' : 'none'; ?>;">
            
            <div class="row g-3 mb-3">
                <div class="col-md-3 field">
                    <label for="motorSubType" class="form-label">Select Sub Type</label>
                    <select class="form-select" name="sub_type" id="motorSubType">
                        <option selected>Select Sub Type</option>
                        <option value="A" <?php echo ($is_edit && $sub_type == 'A') || ($add_new && $sub_type == 'A') ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo ($is_edit && $sub_type == 'B') || ($add_new && $sub_type == 'B') ? 'selected' : ''; ?>>B</option>
                        <option value="SAOD" <?php echo ($is_edit && $sub_type == 'SAOD') || ($add_new && $sub_type == 'SAOD') ? 'selected' : ''; ?>>SAOD</option>
                        <option value="ENDST" <?php echo ($is_edit && $sub_type == 'ENDST') || ($add_new && $sub_type == 'ENDST') ? 'selected' : ''; ?>>ENDST</option>
                    </select>
                </div>

        
                <div class="col-md-3 field">
                    <label for="mvNumber" class="form-label">MV Number</label>
                    <input type="text" name="mv_number" value="<?php echo htmlspecialchars($mv_number); ?>" class="form-control" id="mvNumber" placeholder="Enter MV Number" oninput="this.value = this.value.toUpperCase(); this.value = this.value.replace(/[^A-Z0-9\*]/g, '');" >
                   
                </div>
                
                
                <div class="col-md-3 field">
                    <label for="expenseType" class="form-label">Vehicle Type</label>
                    <select class="form-select" name="vehicle" id="vehicleType" onchange="handleVehicleChange()">
                        <option value="">Select Vehicle Type</option>
                        <option value="INNOVA" <?php echo ($is_edit || $add_new) && $vehicle == 'INNOVA' ? 'selected' : ''; ?>>INNOVA</option>
                        <option value="THAR" <?php echo ($is_edit || $add_new) && $vehicle == 'THAR' ? 'selected' : ''; ?>>THAR</option>
                        <option value="CRETA" <?php echo ($is_edit || $add_new) && $vehicle == 'CRETA' ? 'selected' : ''; ?>>CRETA</option>
                        <option value="BREEZA" <?php echo ($is_edit || $add_new) && $vehicle == 'BREEZA' ? 'selected' : ''; ?>>BREEZA</option>
                        <option value="WAGON R" <?php echo ($is_edit || $add_new) && $vehicle == 'WAGON R' ? 'selected' : ''; ?>>WAGON R</option>
                        <option value="BULLET" <?php echo ($is_edit || $add_new) && $vehicle == 'BULLET' ? 'selected' : ''; ?>>BULLET</option>
                        <option value="ACTIVA" <?php echo ($is_edit || $add_new) && $vehicle == 'ACTIVA' ? 'selected' : ''; ?>>ACTIVA</option>
                        <option value="SPLENDOUR" <?php echo ($is_edit || $add_new) && $vehicle == 'SPLENDOUR' ? 'selected' : ''; ?>>SPLENDOUR</option>

                        <!-- Dynamically add custom options here -->
                        <?php
                        $query = "SELECT DISTINCT vehicle FROM gic_entries WHERE vehicle NOT IN ('INNOVA', 'THAR', 'CRETA', 'BREEZA', 'WAGON R', 'BULLET', 'ACTIVA', 'SPLENDOUR ')";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($is_edit || $add_new) && $vehicle == $row['vehicle'] ? 'selected' : '';
                            echo "<option value='{$row['vehicle']}' $selected>{$row['vehicle']}</option>";
                        }
                        ?>
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($vehicle) && !empty($vehiclecustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="vehiclecustom" 
                        value="<?php echo isset($vehiclecustom) ? htmlspecialchars($vehiclecustom) : ''; ?>" 
                        class="form-control mt-2" 
                        id="vehiclecustom" 
                        placeholder="Enter Name Manually" 
                        style="display:<?php echo ($is_edit && empty($vehicle) && !empty($vehiclecustom)) ? 'block' : 'none'; ?>;">
                </div>


                
                
                <div class="col-md-3 field">
                    <label for="vehicleType" class="form-label">Type of Vehicle</label>
                    <select class="form-select" name="vehicle_type" id="vehiType" onchange="handleVehicleTypeChange()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Type of Vehicle</option>
                        
                        <!-- Static options with exact casing and values as expected from the database -->
                        <option value="2W" <?php echo ($is_edit && $vehicle_type == '2W') || ($add_new && $vehicle_type == '2W') ? 'selected' : ''; ?>>2W</option>

                        <option value="3W Pvt" <?php echo ($is_edit && $vehicle_type == '3W PVT') || ($add_new && $vehicle_type == '3W PVT') ? 'selected' : ''; ?>>3W Pvt</option>

                        <option value="3W CV" <?php echo ($is_edit && $vehicle_type == '3W CV') || ($add_new && $vehicle_type == '3W CV') ? 'selected' : ''; ?>>3W CV</option>

                        <option value="3W PCV" <?php echo ($is_edit && $vehicle_type == '3W PCV') || ($add_new && $vehicle_type == '3W PCV') ? 'selected' : ''; ?>>3W PCV</option>

                        <option value="4W Pvt" <?php echo ($is_edit && $vehicle_type == '4W PVT') || ($add_new && $vehicle_type == '4W PVT') ? 'selected' : ''; ?>>4W Pvt</option>

                        <option value="4W PCV" <?php echo ($is_edit && $vehicle_type == '4W PCV') || ($add_new && $vehicle_type == '4W PCV') ? 'selected' : ''; ?>>4W PCV</option>

                        <option value="PICKUP CV" <?php echo ($is_edit && $vehicle_type == 'PICKUP CV') || ($add_new && $vehicle_type == 'PICKUP CV') ? 'selected' : ''; ?>>PICKUP CV</option>

                        <option value="COMMERCIAL VEHICLE" <?php echo ($is_edit && $vehicle_type == 'COMMERCIAL VEHICLE') || ($add_new && $vehicle_type == 'COMMERCIAL VEHICLE') ? 'selected' : ''; ?>>COMMERCIAL VEHICLE</option>

                        <option value="TRACTOR" <?php echo ($is_edit && $vehicle_type == 'TRACTOR') || ($add_new && $vehicle_type == 'TRACTOR') ? 'selected' : ''; ?>>TRACTOR</option>

                        <option value="TRACTOR AND TRAILOR" <?php echo ($is_edit && $vehicle_type == 'TRACTOR AND TRAILOR') || ($add_new && $vehicle_type == 'TRACTOR AND TRAILOR') ? 'selected' : ''; ?>>TRACTOR AND TRAILOR</option>

                        <option value="JCB" <?php echo ($is_edit && $vehicle_type == 'JCB') || ($add_new && $vehicle_type == 'JCB') ? 'selected' : ''; ?>>JCB</option>

                        <option value="AMBULANCE" <?php echo ($is_edit && $vehicle_type == 'AMBULANCE') || ($add_new && $vehicle_type == 'AMBULANCE') ? 'selected' : ''; ?>>AMBULANCE</option>

                        <option value="HEARSES VAN" <?php echo ($is_edit && $vehicle_type == 'HEARSES VAN') || ($add_new && $vehicle_type == 'HEARSES VAN') ? 'selected' : ''; ?>>HEARSES VAN</option>

                        <option value="TANKER(PETROL AND DIESEL)" <?php echo ($is_edit && $vehicle_type == 'TANKER(PETROL AND DIESEL)') || ($add_new && $vehicle_type == 'TANKER(PETROL AND DIESEL)') ? 'selected' : ''; ?>>TANKER(PETROL AND DIESEL)</option>

                        <option value="TANKER(GAS)" <?php echo ($is_edit && $vehicle_type == 'TANKER(GAS)') || ($add_new && $vehicle_type == 'TANKER(GAS)') ? 'selected' : ''; ?>>TANKER(GAS)</option>

                        <option value="TANKER(MILK)" <?php echo ($is_edit && $vehicle_type == 'TANKER(MILK)') || ($add_new && $vehicle_type == 'TANKER(MILK)') ? 'selected' : ''; ?>>TANKER(MILK)</option>

                        <option value="TANKER(WATER)" <?php echo ($is_edit && $vehicle_type == 'TANKER(WATER)') || ($add_new && $vehicle_type == 'TANKER(WATER)') ? 'selected' : ''; ?>>TANKER(WATER)</option>

                        <option value="TRAILOR" <?php echo ($is_edit && $vehicle_type == 'TRAILOR') || ($add_new && $vehicle_type == 'TRAILOR') ? 'selected' : ''; ?>>TRAILOR</option>

                        <option value="CRANE" <?php echo ($is_edit && $vehicle_type == 'CRANE') || ($add_new && $vehicle_type == 'CRANE') ? 'selected' : ''; ?>>CRANE</option>

                        <option value="BULKER" <?php echo ($is_edit && $vehicle_type == 'BULKER') || ($add_new && $vehicle_type == 'BULKER') ? 'selected' : ''; ?>>BULKER</option>

                        <option value="STAFF BUS" <?php echo ($is_edit && $vehicle_type == 'STAFF BUS') || ($add_new && $vehicle_type == 'STAFF BUS') ? 'selected' : ''; ?>>STAFF BUS</option>

                        <option value="SCHOOL BUS" <?php echo ($is_edit && $vehicle_type == 'SCHOOL BUS') || ($add_new && $vehicle_type == 'SCHOOL BUS') ? 'selected' : ''; ?>>SCHOOL BUS</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        $query = "SELECT DISTINCT vehicle_type FROM gic_entries WHERE vehicle_type NOT IN ('2W', '3W Pvt', '3W CV', '3W PCV', '4W Pvt', '4W PCV', 'PICKUP CV', 'COMMERCIAL VEHICLE', 'TRACTOR', 'TRACTOR AND TRAILOR', 'JCB', 'AMBULANCE', 'HEARSES VAN', 'TANKER(PETROL AND DIESEL)', 'TANKER(GAS)', 'TANKER(MILK)', 'TANKER(WATER)', 'TRAILOR', 'CRANE', 'BULKER', 'STAFF BUS', 'SCHOOL BUS')";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['vehicle_type'])) {
                                $vehicle_type_option = $row['vehicle_type']; // Maintain original casing for display
                                $selected = (($is_edit || $add_new) && strtoupper($vehicle_type) == strtoupper($vehicle_type_option)) ? 'selected' : '';
                                echo "<option value='{$vehicle_type_option}' $selected>{$vehicle_type_option}</option>";
                            }
                        }
                        ?>
                        
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($vehicle_type) && !empty($vehicleType)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="vehicleType" value="<?php echo isset($vehicleType) && htmlspecialchars($vehicleType); ?>" class="form-control mt-2" id="vehicustom" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($vehicle_type) && !empty($vehicleType)) ? 'block' : 'none'; ?>;">
                </div>  
                
            </div>
            
        </div>
        
          <!-- Non-Motor Policy Subtype -->
          <div id="nonMotorPolicy" style="display: <?php echo ($is_edit && $policy_type == 'NonMotor') || ($add_new && $policy_type == 'NonMotor') ? 'block' : 'none'; ?>;">
              
            <div class="row g-3 mb-3">
                
                 <div class="col-md-6 field">
                    <label for="nonMotorSubType" class="form-label">Select Policy Type</label>
                    <select class="form-select" name="nonmotor_type_select" id="nonpolicyType" onchange="handlePolicyType()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Policy Type</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        // Ensure the case consistency by using strtoupper for both the database value and the selected value
                        $query = "SELECT DISTINCT nonmotor_type_select FROM gic_entries";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['nonmotor_type_select'])) {
                                // Convert vehicle_type to uppercase to match the select dropdown options
                                $vehicle_type_option = strtoupper($row['nonmotor_type_select']); 
                                $selected = '';
                                if (($is_edit || $add_new) && strtoupper($nonmotor_type_select) == $vehicle_type_option) {
                                    $selected = 'selected';
                                }
                                echo "<option value='{$vehicle_type_option}' $selected>{$vehicle_type_option}</option>";
                            }
                        }
                        ?>
                
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($nonmotor_type_select) && !empty($nonmotor_type)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                
                    <!-- Input for manual entry -->
                    <input type="text" name="nonmotor_type" value="<?php echo isset($nonmotor_type) && htmlspecialchars($nonmotor_type); ?>" class="form-control mt-2" id="customType" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($nonmotor_type_select) && !empty($nonmotor_type)) ? 'block' : 'none'; ?>;">

                </div>

                <div class="col-md-6 field">
                    <label for="nonMotorSubType" class="form-label">Select Sub Type</label>
                    <select class="form-select" name="nonmotor_subtype_select" id="nonMotorSubType" onchange="handleSubType()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Sub Type</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        // Ensure the case consistency by using strtoupper for both the database value and the selected value
                        $query = "SELECT DISTINCT nonmotor_subtype_select FROM gic_entries";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['nonmotor_subtype_select'])) {
                                // Convert vehicle_type to uppercase to match the select dropdown options
                                $vehicle_type_option = strtoupper($row['nonmotor_subtype_select']); 
                                $selected = '';
                                if (($is_edit || $add_new) && strtoupper($nonmotor_subtype_select) == $vehicle_type_option) {
                                    $selected = 'selected';
                                }
                                echo "<option value='{$vehicle_type_option}' $selected>{$vehicle_type_option}</option>";
                            }
                        }
                        ?>
                
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($nonmotor_subtype_select) && !empty($nonmotor_subtype)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                
                    <!-- Input for manual entry -->
                    <input type="text" name="nonmotor_subtype" value="<?php echo isset($nonmotor_subtype) && htmlspecialchars($nonmotor_subtype); ?>" class="form-control mt-2" id="customsubType" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($nonmotor_subtype_select) && !empty($nonmotor_subtype)) ? 'block' : 'none'; ?>;">
                </div>
                


                
               
                
            </div>
            

            
          </div>
          
          <div class="row g-3 mb-3">
                
                <div class="col-md-3 field">
                    <label for="quotationAmount" class="form-label">Premium Amount</label>
                    <input type="number" name="amount" value="<?php echo htmlspecialchars($amount); ?>" class="form-control" id="quotationAmount" placeholder="Enter Quotation Amount" oninput="handleQuotationAmount()" onkeypress="return isNumeric(event)">
                </div>

                <div class="col-md-3 field">
                    <label for="advanceAmount" class="form-label">Advance Amount</label>
                    <input type="number" name="adv_amount" value="<?php echo htmlspecialchars($adv_amount); ?>" class="form-control" id="advanceAmount" placeholder="Enter Advance Amount" oninput="updateAmounts()" onkeypress="return isNumeric(event)">
                </div>

                <div class="col-md-3 field">
                    <label for="balanceAmount" class="form-label">Balance Amount</label>
                    <input type="number" name="bal_amount" value="<?php echo htmlspecialchars($bal_amount); ?>" class="form-control" id="balanceAmount" placeholder="Balance Amount" readonly>
                </div>

                <div class="col-md-3 field">
                    <label for="recoveryAmount" class="form-label">Recovery Amount</label>
                    <input type="number" name="recov_amount" value="<?php echo htmlspecialchars($recov_amount); ?>" class="form-control" id="recoveryAmount" placeholder="Recovery Amount" readonly>
                </div>


                        
                <div class="col-md-2 field">
                    <label for="policyCompany" class="form-label">Adviser Name</label>
                    <select class="form-select" name="adviser_name" id="adviser" onchange="handleAdviserChange()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Name</option>
                        
                        <option value="BYP" <?php echo ($is_edit && $adviser_name == 'BYP') || ($add_new && $adviser_name == 'BYP') ? 'selected' : ''; ?> selected>BYP</option>
                        <option value="PB PARTNER" <?php echo ($is_edit && $adviser_name == 'PB PARTNER') || ($add_new && $adviser_name == 'PB PARTNER') ? 'selected' : ''; ?>>PB PARTNER</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        $query = "SELECT DISTINCT adviser_name FROM gic_entries WHERE adviser_name NOT IN ('BYP', 'PB Partner')";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['adviser_name'])) {
                                $adviser_option = strtoupper($row['adviser_name']); // convert to uppercase for consistency
                                $selected = (($is_edit || $add_new) && strtoupper($adviser_name) == $adviser_option) ? 'selected' : '';
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

                <div class="col-md-2 field">
                    <label for="registerNumber" class="form-label">Register Number</label>
                    <input type="text" class="form-control" name="reg_num" id="registerNumber"  value="<?= htmlspecialchars($reg_num) ?>" >
                </div>
    
          <!-- Date (Current Date) -->
            <div class="col-md-2 field">
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
                  <label for="policyNumber" class="form-label">Policy Number</label>
                  <input type="text" class="form-control" name="policy_number" value="<?php echo htmlspecialchars($policy_number); ?>" id="policyNumber" placeholder="Enter Policy Number">
                </div>
                
                
                <div class="col-md-3 field">
                    <label for="policyCompany" class="form-label">Insurance Company</label>
                    <select class="form-select" name="policy_company" id="policy_company" onchange="handleCompanyChange()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Insurance Company</option>
                        
                        <!-- Predefined options -->
                        <option value="ICICI LOMBARD GIC" <?php echo ($is_edit && $policy_company == 'ICICI LOMBARD GIC') || ($add_new && $policy_company == 'ICICI LOMBARD GIC') ? 'selected' : ''; ?>>ICICI LOMBARD GIC</option>

                        <option value="TATA AIG GIC" <?php echo ($is_edit && $policy_company == 'TATA AIG GIC') || ($add_new && $policy_company == 'TATA AIG GIC') ? 'selected' : ''; ?>>TATA AIG GIC</option>

                        <option value="UNITED INDIA INSURANCE CO LTD" <?php echo ($is_edit && $policy_company == 'UNITED INDIA INSURANCE CO LTD') || ($add_new && $policy_company == 'UNITED INDIA INSURANCE CO LTD') ? 'selected' : ''; ?>>UNITED INDIA INSURANCE CO LTD</option>

                        <option value="HDFC ERGO GIC" <?php echo ($is_edit && $policy_company == 'HDFC ERGO GIC') || ($add_new && $policy_company == 'HDFC ERGO GIC') ? 'selected' : ''; ?>>HDFC ERGO GIC</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        // Ensure case consistency by using strtoupper for both the database value and the selected value
                        $query = "SELECT DISTINCT policy_company FROM gic_entries WHERE policy_company NOT IN ('ICICI LOMBARD GIC', 'Tata AIG GIC', 'United India Insurance Co Ltd','HDFC Ergo GIC')";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['policy_company'])) {
                                // Convert policy_company to uppercase to match the select dropdown options
                                $policy_company_option = strtoupper($row['policy_company']); 
                                $selected = '';
                                if (($is_edit || $add_new) && strtoupper($policy_company) == $policy_company_option) {
                                    $selected = 'selected';
                                }
                                echo "<option value='{$policy_company_option}' $selected>{$policy_company_option}</option>";
                            }
                        }
                        ?>
                    
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($policy_company) && !empty($policycustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="policycustom" value="<?php echo isset($policycustom) && htmlspecialchars($policycustom); ?>" class="form-control mt-2" id="customCompanyType" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($policy_company) && !empty($policycustom)) ? 'block' : 'none'; ?>;">
                </div>


        


            </div>

        <!-- PHP to JS flag -->
<script>
    const isEditMode = <?php echo $is_edit ? 'true' : 'false'; ?>;
</script>

<div class="row g-3 mb-3">
    <!-- Policy Duration -->
    <div class="col-md-2 field">
        <label for="policyDuration" class="form-label">Select Policy Duration</label>
        <select class="form-select" name="policy_duration" id="policyDuration" onchange="updateDates()">
            <option value="1yr" <?php echo ($is_edit && $policy_duration == '1YR') || ($add_new && $policy_company == '1YR') ? 'selected' : ''; ?>>1 Year</option>
            <option value="short" <?php echo ($is_edit && $policy_duration == 'SHORT') || ($add_new && $policy_company == 'SHORT') ? 'selected' : ''; ?>>Short Term</option>
            <option value="long" <?php echo ($is_edit && $policy_duration == 'LONG') || ($add_new && $policy_company == 'LONG') ? 'selected' : ''; ?>>Long Term</option>
        </select>
    </div>

    <!-- Start Date -->
    <div class="col-md-3 field">
        <label for="startDate" class="form-label">Start Date</label>
        <input type="date" name="start_date" value="<?php 
            if ($is_edit) {
                echo htmlspecialchars($start_date);
            } elseif ($add_new) {
                $today = new DateTime();
                $nextStartDate = $today->format('Y-m-d');
                if (!empty($end_date)) {
                    $endDateObj = DateTime::createFromFormat('Y-m-d', $end_date);
                    if ($endDateObj && $endDateObj >= $today) {
                        $endDateObj->modify('+1 day');
                        $nextStartDate = $endDateObj->format('Y-m-d');
                    }
                }
                echo htmlspecialchars($nextStartDate);
            }
        ?>" class="form-control text-primary" id="startDate" onchange="adjustEndDate()" >
    </div>

    <!-- End Date -->
    <div class="col-md-3 field">
        <label for="endDate" class="form-label">End Date</label>
        <input type="date" name="end_date" value="<?php 
            if ($is_edit) {
                echo htmlspecialchars($end_date);
            } elseif ($add_new) {
                echo htmlspecialchars($end_date);
            }
        ?>" class="form-control text-primary" id="endDate">
    </div>

    <!-- Year Count (readonly) -->
    <div class="col-md-2 field">
        <label for="yearCount" class="form-label">Policy Duration (Years)</label>
        <input type="text" name="year_count" id="yearCount" class="form-control" readonly>
    </div>

    <!-- Is Long Term (readonly) -->
    <div class="col-md-2 field">
        <label for="isLongTerm" class="form-label">Long Term Policy</label>
        <input type="text" name="is_long_term" id="isLongTerm" class="form-control" readonly>
    </div>
</div>




                    
        
        <div class="row g-3 ">
                         
                
                
                <!-- Payment Mode -->
                <div class="col-md-12 field">
                    <label for="paymentMode" class="form-label">Payment Mode</label>
                    <select class="form-select" name="pay_mode" id="paymentMode">
                        <option value="Cash" <?php if ($is_edit && $pay_mode == 'Cash') echo 'selected'; ?>>Cash</option>
                        <option value="Cheque" <?php if ($is_edit && $pay_mode == 'CHEQUE') echo 'selected'; ?>>Cheque</option>
                        <option value="Payment Link" <?php if ($is_edit && $pay_mode == 'Payment Link') echo 'selected'; ?>>Payment Link</option>
                        <option value="Online" <?php if ($is_edit && $pay_mode == 'Online') echo 'selected'; ?>>Online</option>
                        <option value="RTGS/NEFT" <?php if ($is_edit && $pay_mode == 'RTGS/NEFT') echo 'selected'; ?>>RTGS/NEFT</option>
                    </select>
                </div>
            
             <!-- Hidden fields for cheque details -->
            <div id="chequeDetails" style="display: <?php echo ($is_edit && $pay_mode == 'Cheque') ? 'block' : 'none'; ?>;">
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
            
            
          

    
          <!-- Remark -->
          <div class="col-md-6 field">
            <label for="remark" class="form-label">Responsibility</label>
            <textarea class="form-control" name="responsibility" id="responsibility" placeholder="Enter responsibility"><?php echo htmlspecialchars($responsibility); ?></textarea>
          </div>
          
          <div class="col-md-6 field">
            <label for="remark" class="form-label">Remark</label>
            <textarea class="form-control" name="remark" id="remark" placeholder="Enter Remark"><?php echo htmlspecialchars($remark); ?></textarea>
          </div>
          
        <div class="col-md-6 mx-auto p-2 field" style="background-color: #ffcdcd;">
          <label for="motorSubType" class="form-label">Form Status</label>
          <select class="form-select" name="form_status" id="motorSubType" required>
            <option value="PENDING" <?php if ($is_edit && $form_status == 'PENDING') echo 'selected'; ?>>PENDING</option>
            <option value="COMPLETE" <?php if ($is_edit && $form_status == 'COMPLETE') echo 'selected'; ?>>COMPLETE</option>
            <option value="CDA" <?php if ($is_edit && $form_status == 'CDA') echo 'selected'; ?>>CDA</option>
            <option value="CANCELLED" <?php if ($is_edit && $form_status == 'CANCELLED') echo 'selected'; ?>>CANCELLED</option>
            <option value="OTHER" <?php if ($is_edit && $form_status == 'OTHER') echo 'selected'; ?>>OTHER</option>
            
          </select>
        </div>

          
         </div>
                            
        <!-- Form Submit button -->
            <input type="submit" class="btn sub-btn" value="<?php 
                if ($is_edit) {
                    echo 'Update Entry';
                } elseif ($add_new) {
                    echo 'Add New Entry';
                } elseif ($add_client) {
                    echo 'Add Client';
                } else {
                    echo 'Submit';
                }
            ?>">
 
            
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

// script for show years count using date

function updateDates() {
    const duration = document.getElementById('policyDuration').value;
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];

    if (!startDateInput.value) {
        startDateInput.value = todayFormatted;
    }

    if (duration === '1yr') {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(startDate);
        endDate.setFullYear(startDate.getFullYear() + 1);
        endDate.setDate(endDate.getDate() - 1); // subtract 1 day to get one day before anniversary
        endDateInput.value = endDate.toISOString().split('T')[0];
        endDateInput.disabled = false;
    } else {
        endDateInput.disabled = false;
    }

    calculateAndDisplayDuration();
}

function adjustEndDate() {
    const duration = document.getElementById('policyDuration').value;
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    if (duration === '1yr') {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(startDate);
        endDate.setFullYear(startDate.getFullYear() + 1);
        endDate.setDate(endDate.getDate() - 1); // 1 day before anniversary
        endDateInput.value = endDate.toISOString().split('T')[0];
    }

    calculateAndDisplayDuration();
}

function calculateAndDisplayDuration() {
    const startDateVal = document.getElementById('startDate').value;
    const endDateVal = document.getElementById('endDate').value;

    if (!startDateVal || !endDateVal) return;

    const startDate = new Date(startDateVal);
    const endDate = new Date(endDateVal);

    if (isNaN(startDate) || isNaN(endDate)) return;

    let yearCount = endDate.getFullYear() - startDate.getFullYear();

    const anniversary = new Date(startDate);
    anniversary.setFullYear(startDate.getFullYear() + yearCount);

    const oneDayBeforeAnniversary = new Date(anniversary);
    oneDayBeforeAnniversary.setDate(anniversary.getDate() - 1);

    if (endDate < oneDayBeforeAnniversary) {
        yearCount--;
    }

    // If exactly 1 day before anniversary, still count as full year
    if (endDate.getTime() === oneDayBeforeAnniversary.getTime()) {
        // keep yearCount as is
    }

    yearCount = Math.max(0, yearCount);
    const isLongTerm = yearCount >= 1 ? "1" : "0";

    document.getElementById('yearCount').value = yearCount;
    document.getElementById('isLongTerm').value = isLongTerm;
}

// Event Listeners
document.getElementById('startDate').addEventListener('change', function () {
    adjustEndDate();
    calculateAndDisplayDuration();
});

document.getElementById('endDate').addEventListener('change', calculateAndDisplayDuration);

document.getElementById('policyDuration').addEventListener('change', function () {
    updateDates();
});

// On Page Load
window.addEventListener('load', function () {
    if (!isEditMode) {
        updateDates();
    }
    calculateAndDisplayDuration();
});
</script>

<script>

    // script for open cheque details div 
    document.addEventListener('DOMContentLoaded', function () {
        const paymentMode = document.getElementById('paymentMode');
        const chequeDetails = document.getElementById('chequeDetails');

        function toggleChequeDetails() {
            if (paymentMode.value === 'Cheque') {
                chequeDetails.style.display = 'block';
            } else {
                chequeDetails.style.display = 'none';
            }
        }

        // Initial check on page load (in case of edit mode)
        toggleChequeDetails();

        // Listen for change events
        paymentMode.addEventListener('change', toggleChequeDetails);
    });


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

     // Show/hide relevant sections based on Policy Type
    document.getElementById('policyType').addEventListener('change', function () {
      if (this.value === 'Motor') {
        document.getElementById('motorPolicy').style.display = 'block';
        document.getElementById('nonMotorPolicy').style.display = 'none';
      } else {
        document.getElementById('motorPolicy').style.display = 'none';
        document.getElementById('nonMotorPolicy').style.display = 'block';
      }
    });

// Handle policy type selection
document.getElementById('nonpolicyType').addEventListener('change', function () {
    var customType = document.getElementById('customType');
    if (this.value === 'Enter Manually') {
        customType.style.display = 'block';
    } else {
        customType.style.display = 'none';
        customType.value = '';  // Clear the custom type input if not selected
    }
});

// Handle sub-type selection
document.getElementById('nonMotorSubType').addEventListener('change', function () {
    var customsubType = document.getElementById('customsubType');
    if (this.value === 'Enter Manually') {
        customsubType.style.display = 'block';
    } else {
        customsubType.style.display = 'none';
        customsubType.value = '';  // Clear the custom subtype input if not selected
    }
});

// Handle Company selection
function handleCompanyChange() {
    var companySelect = document.getElementById('policy_company');
    var customCompanyInput = document.getElementById('customCompanyType');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'policy_company'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'policycustom'; // Reset to avoid conflicts
    }
}
  
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

 


//     Handle VEHICLE selection
function handleVehicleChange() {
    var companySelect = document.getElementById('vehicleType');
    var customCompanyInput = document.getElementById('vehiclecustom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'vehicle'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'vehiclecustom'; // Reset to avoid conflicts
    }
} 
 

//     Handle VEHICLE selection
function handleVehicleTypeChange() {
    var companySelect = document.getElementById('vehiType');
    var customCompanyInput = document.getElementById('vehicustom');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'vehicle_type'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'vehicleType'; // Reset to avoid conflicts
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

// Handle policy status selection
document.getElementById('policyStatus').addEventListener('change', function () {
    var customsubType = document.getElementById('policyInput');  // Correct ID reference here
    if (this.value === 'Enter Manually') {
        customsubType.style.display = 'block'; 
    } else {
        customsubType.style.display = 'none';
        customsubType.value = '';  // Clear the custom input if not selected
    }
});



// Before submitting the form, check if manual input is selected
document.querySelector('form').addEventListener('submit', function (e) {
    var policyType = document.getElementById('nonpolicyType').value;
    var customType = document.getElementById('customType').value;
    var subType = document.getElementById('nonMotorSubType').value;
    var customsubType = document.getElementById('customsubType').value;
    var companyType = document.getElementById('policy_company').value;
    var customCompanyType = document.getElementById('customCompanyType').value;

    // If "Enter Manually" is selected for Policy Type, set the value of the hidden field
    if (policyType === 'Enter Manually' && customType !== '') {
        document.getElementsByName('nonmotor_type')[0].value = customType;
    }

    // If "Enter Manually" is selected for Sub Type, set the value of the hidden field
    if (subType === 'Enter Manually' && customsubType !== '') {
        document.getElementsByName('nonmotor_subtype')[0].value = customsubType;
    }
    
    // If "Enter Manually" is selected for Sub Type, set the value of the hidden field
    if (companyType === 'Enter Manually' && customCompanyType !== '') {
        document.getElementsByName('policy_company')[0].value = customCompanyType;
    }
});







// Function to calculate balance and recovery amounts
// Handle input in quotationAmount
function handleQuotationAmount() {
    const quotationAmount = parseFloat(document.getElementById('quotationAmount').value) || 0;

    // Set adv_amount equal to quotationAmount initially
    document.getElementById('advanceAmount').value = quotationAmount;

    // Set bal_amount and recov_amount to 0 initially
    document.getElementById('balanceAmount').value = 0;
    document.getElementById('recoveryAmount').value = 0;
}

// Update balance and recovery amounts dynamically
function updateAmounts() {
    const quotationAmount = parseFloat(document.getElementById('quotationAmount').value) || 0;
    const advanceAmount = parseFloat(document.getElementById('advanceAmount').value) || 0;

    // Calculate remaining amount
    const balanceAmount = quotationAmount - advanceAmount;

    // Update bal_amount and recov_amount
    document.getElementById('balanceAmount').value = balanceAmount > 0 ? balanceAmount : 0;
    document.getElementById('recoveryAmount').value = balanceAmount > 0 ? balanceAmount : 0;
}

// Allow only numeric input
function isNumeric(event) {
    const keyCode = event.keyCode || event.which;
    const keyValue = String.fromCharCode(keyCode);

    // Allow only digits (0-9)
    return /^[0-9]*$/.test(keyValue);
}



</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

