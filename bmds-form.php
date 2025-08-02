
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
$policy_duration = '';
$start_date = '';
$end_date = '';
$car_type = '';
$ride = '';
$amount = '';
$remark = '';
$adv_amount = '';
$bal_amount = '';
$recov_amount = '';
$responsibility = '';
$address = '';
$birth_date = '';
$form_status = '';
$start_time = '';
$end_time = '';
$bmds_type = '';
$llr_type = '';
$mdl_type = '';
$class = '';
$client_id = '';
$llr_class = '';
$test_date = '';
$sr_num = '';
$city = '';
$errors = [];
$is_edit = false;
$add_new = false;
$add_client = false;
$id = null; // Initialize $id variable

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM bmds_entries WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $contact = $row['contact'];
        $policy_duration = $row['policy_duration'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $car_type = $row['car_type'];
        $ride = $row['ride'];
        $amount = $row['amount'];
        $remark = strtoupper($row['remark']);
        $adv_amount = $row['adv_amount'];
        $bal_amount = $row['bal_amount'];
        $recov_amount = $row['recov_amount'];
        $responsibility = strtoupper($row['responsibility']);
        $address = strtoupper($row['address']);
        $birth_date = $row['birth_date'];
        $form_status = $row['form_status'];
        $start_time = $row['start_time'];
        $end_time = $row['end_time'];
        $bmds_type = $row['bmds_type'];
        $llr_type = $row['llr_type'];
        $mdl_type = $row['mdl_type'];
        $class = $row['class'];
        $llr_class = strtoupper($row['llr_class']);
        $client_id = $row['client_id'];
        $test_date = $row['test_date'];
        $sr_num = $row['sr_num'];
        $city = strtoupper($row['city']);
    } else {
        die("Entry not found.");
    }
} 
elseif (isset($_GET['action']) && $_GET['action'] === 'add_new') {
    
    $is_edit = false;
    $add_client = false;
    $add_new = true;

    // Get the current month and year
    $currentYear = date('Y');
    $currentMonth = date('m');

    // Initialize reg_num
    $reg_num = 1; // Default to 1 if no records are found

    // Fetch the last registration number for the current month
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num, MONTH(creation_on) AS month, YEAR(creation_on) AS year 
                            FROM bmds_entries 
                            WHERE YEAR(creation_on) = $currentYear AND MONTH(creation_on) = $currentMonth");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // If the entry is from the current month, increment reg_num
        if ((int)$row['month'] === (int)$currentMonth && (int)$row['year'] === (int)$currentYear) {
            $reg_num = (int)$row['max_reg_num'] + 1;
        }
    }

    // Only fetch the client_name and contact if provided in the query
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM bmds_entries WHERE id=$id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_name = strtoupper($row['client_name']);
            $contact = $row['contact'];
            $reg_num = $row['reg_num'];
            $policy_date = $row['policy_date'];
            $time = $row['time'];
            $client_name = strtoupper($row['client_name']);
            $contact = $row['contact'];
            $policy_duration = $row['policy_duration'];
            $start_date = $row['start_date'];
            $end_date = $row['end_date'];
            $car_type = $row['car_type'];
            $ride = $row['ride'];
            $amount = $row['amount'];
            $remark = strtoupper($row['remark']);
            $adv_amount = $row['adv_amount'];
            $bal_amount = $row['bal_amount'];
            $recov_amount = $row['recov_amount'];
            $responsibility = strtoupper($row['responsibility']);
            $address = strtoupper($row['address']);
            $birth_date = $row['birth_date'];
            $form_status = $row['form_status'];
            $start_time = $row['start_time'];
            $end_time = $row['end_time'];
            $bmds_type = $row['bmds_type'];
            $llr_type = $row['llr_type'];
            $mdl_type = $row['mdl_type'];
            $class = $row['class'];
            $llr_class = strtoupper($row['llr_class']);
            $client_id = $row['client_id'];
            $test_date = $row['test_date'];
            $sr_num = $row['sr_num'];
            $city = strtoupper($row['city']);
        } else {
            die("Entry not found.");
        }
    }
} 


elseif (isset($_GET['action']) && $_GET['action'] === 'add_client') {
    $is_edit = false; 
    $add_new = false; 
    $add_client = true;

    // Get the current month and year
    $currentYear = date('Y');
    $currentMonth = date('m');

    // Initialize reg_num
    $reg_num = 1; // Default to 1 if no records are found

    // Fetch the last registration number for the current month
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num, MONTH(creation_on) AS month, YEAR(creation_on) AS year 
                            FROM bmds_entries 
                            WHERE YEAR(creation_on) = $currentYear AND MONTH(creation_on) = $currentMonth");

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
    $result = $conn->query("SELECT MAX(reg_num) AS max_reg_num, MONTH(creation_on) AS month, YEAR(creation_on) AS year 
                            FROM bmds_entries");

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
    $policy_duration = isset($_POST['policy_duration']) ? trim($_POST['policy_duration']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $car_type = isset($_POST['car_type']) ? trim($_POST['car_type']) : '';
    $ride = isset($_POST['ride']) ? trim($_POST['ride']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $remark = isset($_POST['remark']) ? strtoupper(trim($_POST['remark'])) : '';
    $adv_amount = isset($_POST['adv_amount']) ? trim($_POST['adv_amount']) : '';
    $bal_amount = isset($_POST['bal_amount']) ? trim($_POST['bal_amount']) : '';
    $recov_amount = isset($_POST['recov_amount']) ? trim($_POST['recov_amount']) : '';
    $responsibility = isset($_POST['responsibility']) ? strtoupper(trim($_POST['responsibility'])) : '';
    $address = isset($_POST['address']) ? strtoupper(trim($_POST['address'])) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $form_status = isset($_POST['form_status']) ? trim($_POST['form_status']) : '';
    $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
    $bmds_type = isset($_POST['bmds_type']) ? trim($_POST['bmds_type']) : '';
    $llr_type = isset($_POST['llr_type']) ? trim($_POST['llr_type']) : '';
    $mdl_type = isset($_POST['mdl_type']) ? trim($_POST['mdl_type']) : '';
    $class = isset($_POST['class']) ? trim($_POST['class']) : '';
    $client_id = trim($_POST['client_id']);
    $llr_class = isset($_POST['llr_class']) ? strtoupper(trim($_POST['llr_class'])) : '';
    $custom_llr_class = isset($_POST['custom_llr_class']) ? strtoupper(trim($_POST['custom_llr_class'])) : '';
    $test_date = isset($_POST['test_date']) ? trim($_POST['test_date']) : '';
    $sr_num = isset($_POST['sr_num']) ? trim($_POST['sr_num']) : '';
    $city = isset($_POST['city']) ? strtoupper(trim($_POST['city'])) : '';
    $custom_city = isset($_POST['custom_city']) ? strtoupper(trim($_POST['custom_city'])) : '';

    $errors = [];
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

    if (!empty($adv_amount) && !is_numeric($adv_amount)) {
        $errors[] = "Invalid Advance Amount";
    }

    
    if (!empty($remark) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $remark)) {
        $errors[] = "Invalid remark";
    }
    
    if (!empty($responsibility) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $responsibility)) {
        $errors[] = "Invalid Responsibility";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }

    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone

    // If no errors, process the form
    if (empty($errors)) {
        if ($is_edit) {

            $car_type = $_POST['car_type'] === 'Enter Manually' ? $_POST['custom_car_type'] : $_POST['car_type'];
            $llr_class = $_POST['llr_class'] === 'Enter Manually' ? $_POST['custom_llr_class'] : $_POST['llr_class'];
            $city = $_POST['city'] === 'Enter Manually' ? $_POST['custom_city'] : $_POST['city'];

            // Update existing entry
            $sql = "UPDATE bmds_entries SET reg_num='$reg_num', policy_date='$policy_date', client_name='$client_name', contact='$contact', policy_duration='$policy_duration', start_date='$start_date', end_date='$end_date', car_type='$car_type', ride='$ride' , amount='$amount' , remark='$remark', adv_amount='$adv_amount', bal_amount='$bal_amount', recov_amount='$recov_amount', responsibility='$responsibility', address='$address',fiscal_year='$fiscalYear',birth_date='$birth_date',form_status='$form_status',update_on='$update_on',start_time='$start_time',end_time='$end_time',bmds_type='$bmds_type',llr_type='$llr_type',mdl_type='$mdl_type',class='$class',llr_class='$llr_class',test_date='$test_date',sr_num='$sr_num',city='$city' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: bmds");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } 
        elseif  ($add_new) {
            
              // username for who fill form
            $username = $_SESSION['username'];

            $original_id = $_GET['id']; // ID of the old policy

             // Delete the old policy record since it's being renewed
            $deleteOldPolicy = "DELETE FROM bmds_entries WHERE id = '$original_id'";
            $conn->query($deleteOldPolicy);


            $car_type = strtoupper(trim($_POST['car_type']));
            $custom_car_type = strtoupper(trim($_POST['custom_car_type'] ?? ''));
            if ($car_type == "ENTER MANUALLY") {
                $car_type = $custom_car_type;
            }

            $llr_class = strtoupper(trim($_POST['llr_class']));
            $custom_llr_class = strtoupper(trim($_POST['custom_llr_class'] ?? ''));
            if ($llr_class == "ENTER MANUALLY") {
                $llr_class = $custom_llr_class;
            }

            $city = strtoupper(trim($_POST['city']));
            $custom_city = strtoupper(trim($_POST['custom_city'] ?? ''));
            if ($city == "ENTER MANUALLY") {
                $city = $custom_city;
            }

            
            // Add a new payment entry for the same reg_num
             
            $sql = "INSERT INTO bmds_entries (reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, car_type , ride , amount, remark,adv_amount,bal_amount,recov_amount, responsibility,address,username,fiscal_year,birth_date,form_status,creation_on,start_time,end_time,bmds_type,llr_type,mdl_type,class,llr_class,test_date,sr_num,city) 
                    VALUES ('$reg_num', '$policy_date', '$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$car_type', '$ride','$amount', '$remark', '$adv_amount', '$bal_amount' ,'$recov_amount', '$responsibility','$address','$username', '$fiscalYear','$birth_date','$form_status','$creation_on','$start_time','$end_time','$bmds_type','$llr_type','$mdl_type','$class','$llr_class','$test_date','$sr_num','$city')";
            if ($conn->query($sql) === TRUE) {
                header("Location: client");
                exit();
            } else {
                echo "Error: " . $conn->error;
            }
        }
        
        elseif  ($add_client) {
            
             // username for who fill form
            $username = $_SESSION['username'];

            // enter manualy car type
            $car_type = $_POST['car_type'];
            $custom_car_type = $_POST['custom_car_type'] ?? '';
            
            $car_type = strtoupper(trim($_POST['car_type']));
            $custom_car_type = strtoupper(trim($_POST['custom_car_type'] ?? ''));
            if ($car_type == "ENTER MANUALLY") {
                $car_type = $custom_car_type;
            }

            $llr_class = strtoupper(trim($_POST['llr_class']));
            $custom_llr_class = strtoupper(trim($_POST['custom_llr_class'] ?? ''));
            if ($llr_class == "ENTER MANUALLY") {
                $llr_class = $custom_llr_class;
            }

            $city = strtoupper(trim($_POST['city']));
            $custom_city = strtoupper(trim($_POST['custom_city'] ?? ''));
            if ($city == "ENTER MANUALLY") {
                $city = $custom_city;
            }


            
            // Add a new payment entry for the same reg_num
             
            $sql = "INSERT INTO bmds_entries (client_id,reg_num, policy_date,time, client_name, contact, policy_duration, start_date, end_date, car_type , ride , amount, remark,adv_amount,bal_amount,recov_amount, responsibility,address,username,fiscal_year,birth_date,form_status,creation_on,start_time,end_time,bmds_type,llr_type,mdl_type,class,llr_class,test_date,sr_num,city) 
                    VALUES ('$client_id','$reg_num', '$policy_date', '$time', '$client_name', '$contact', '$policy_duration', '$start_date', '$end_date', '$car_type', '$ride','$amount', '$remark', '$adv_amount', '$bal_amount' ,'$recov_amount', '$responsibility','$address','$username', '$fiscalYear','$birth_date','$form_status','$creation_on','$start_time','$end_time','$bmds_type','$llr_type','$mdl_type','$class','$llr_class','$test_date','$sr_num','$city')";
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
                <h1>BMDS FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="bmds">BMDS</a></li>
                <li class="breadcrumb-item active" aria-current="page">BMDS FORM</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="bmds-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
                } elseif ($add_new) {
                    echo '?action=add_new&id=' . $id; 
                }elseif ($add_client) {
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
                    } elseif ($add_new) {
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
                <input type="text" class="form-control" name="reg_num" id="registerNumber" value="<?= htmlspecialchars($reg_num) ?>">
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
                <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"  placeholder="Enter Client Name" required readOnly>
            </div>

            <!-- Mobile Number -->
            <div class="col-md-6 field">
                <label for="mobileNumber" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact) ?? $row['contact']; ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" required readonly>
            </div>
            
            <div class="col-md-6 field">
                <label for="clientName" class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address); ?>"  placeholder="Enter Address" readonly>
            </div>
            <div class="col-md-6 field">
                    <label for="date" class="form-label">Birth Date</label>
                    <input type="date" class="form-control text-success" name="birth_date" id="date" value="<?php echo htmlspecialchars($birth_date);?>" readOnly>
                </div>
            
        </div>
        
        <div class="row g-3">

            <div class="col-md-4 field">
                <label for="policyType" class="form-label">Select Type</label>
                <select class="form-select" name="bmds_type" id="policyType">
                    <option value="">Select Type</option>
                    <option value="LLR" <?php echo ($is_edit && $bmds_type == 'LLR') || ($add_new && $bmds_type == 'LLR') ? 'selected' : ''; ?> >LLR</option>
                    <option value="DL" <?php echo ($is_edit && $bmds_type == 'DL') || ($add_new && $bmds_type == 'DL') ? 'selected' : ''; ?>>DL</option>
                    <option value="ADM" <?php echo ($is_edit && $bmds_type == 'ADM') || ($add_new && $bmds_type == 'ADM') ? 'selected' : ''; ?>>ADM</option>
                </select>
            </div>

 
            
        </div>            


                <div id="ADMType" style="display: <?php echo ($is_edit && $bmds_type == 'ADM') ? 'block' : 'none'; ?>;">    
                    <div class="row g-3 mt-1">

                        <!-- Start Time -->
                        <div class="col-md-3 field">
                            <label for="startDate" class="form-label">Start Time</label>
                            <input type="time" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>" class="form-control text-primary" >
                        </div>

                        <!-- End Time -->
                        <div class="col-md-3 field">
                            <label for="startDate" class="form-label">End Time</label>
                            <input type="time" name="end_time" value="<?php echo htmlspecialchars($end_time); ?>" class="form-control text-primary">
                        </div>
                    
                        <!-- Start Date -->
                        <div class="col-md-3 field">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="form-control text-primary" id="startDate" >
                        </div>
                    
                        <!-- End Date -->
                        <div class="col-md-3 field">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="form-control text-primary" id="endDate" >
                        </div>

                        <div class="col-md-6 field">
                            <label for="carType" class="form-label">Select Car Type</label>
                            <select class="form-select" name="car_type" id="carType" onchange="handleCarTypeChange()">
                                <option value="">Select Car Type</option>
                                <option value="Breeza" <?php echo ($is_edit && $car_type == 'Breeza') || ($add_new && $car_type == 'Breeza') ? 'selected' : ''; ?>>Breeza</option>
                                <option value="Wagnor" <?php echo ($is_edit && $car_type == 'Wagnor') || ($add_new && $car_type == 'Wagnor') ? 'selected' : ''; ?>>Wagnor</option>
                                
                                
                                <!-- Dynamically add custom options here -->
                                <?php
                                $query = "SELECT DISTINCT car_type FROM bmds_entries WHERE car_type NOT IN ('Breeza', 'Wagnor')";
                                $result = $conn->query($query);
                                
                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($is_edit || $add_new) && $car_type == $row['car_type'] ? 'selected' : '';
                                    echo "<option value='{$row['car_type']}' $selected>{$row['car_type']}</option>";
                                }
                                ?>

                                <option value="Enter Manually">Enter Manually</option>
                                
                            </select>
                        
                            <!-- Input for manual entry -->
                            <input type="text" name="custom_car_type" value="<?php echo isset($custom_car_type) && htmlspecialchars($custom_car_type); ?>" class="form-control mt-2" id="customCarType" placeholder="Enter Car Type Manually" style="display:none;">

                        </div>
                        
                        <div class="col-md-6 field">
                            <label for="paymentMode" class="form-label">Select Km Ride</label>
                            <select class="form-select" name="ride" value="<?php echo htmlspecialchars($ride); ?>" id="paymentMode">
                                <option value="">Select Km Ride</option>
                                <option value="5Km" <?php echo ($is_edit && $ride == '5Km') || ($add_new && $ride == '5Km') ? 'selected' : ''; ?>
                                >5Km</option>
                                <option value="10Km" <?php echo ($is_edit && $ride == '10Km') || ($add_new && $ride == '10Km') ? 'selected' : ''; ?>
                                >10Km</option>
                            </select>
                        </div>
                    </div>
                </div>
            

                <div id="LLRType" style="display: <?php echo ($is_edit && $bmds_type == 'LLR') || ($add_new && $bmds_type == 'LLR') ? 'block' : 'none'; ?>;">

                    <div class="row g-3 mt-1">
                    
                        <div class="col-md-4 field">
                            <label for="llrSubType" class="form-label">Select Sub Type</label>
                            <select class="form-select" name="llr_type" id="llrSubType">
                                <option value="">Select Sub Type</option>
                                <option value="FRESH" <?php echo ($is_edit && $llr_type == 'FRESH') || ($add_new && $llr_type == 'FRESH') ? 'selected' : ''; ?>>FRESH</option>
                                <option value="EXEMPTED" <?php echo ($is_edit && $llr_type == 'EXEMPTED') || ($add_new && $llr_type == 'EXEMPTED') ? 'selected' : ''; ?>>EXEMPTED</option>
                            </select>
                        </div>

                        
                    </div>
                </div>

                <div id="MDLType" style="display: <?php echo ($is_edit && $bmds_type == 'DL') || ($add_new && $bmds_type == 'DL') ? 'block' : 'none'; ?>;">
                    <div class="row g-3 mt-1">
                        <div class="col-md-4 field">
                            <label for="mdlSubType" class="form-label">Select Sub Type</label>
                            <select class="form-select" name="mdl_type" id="mdlSubType">
                                <option value="">Select Sub Type</option>
                                <option value="FRESH" <?php echo ($is_edit && $mdl_type == 'FRESH') || ($add_new && $mdl_type == 'FRESH') ? 'selected' : ''; ?>>FRESH</option>
                                <option value="ENDST" <?php echo ($is_edit && $mdl_type == 'ENDST') || ($add_new && $mdl_type == 'ENDST') ? 'selected' : ''; ?>>ENDST</option>
                                <option value="REVALID" <?php echo ($is_edit && $mdl_type == 'REVALID') || ($add_new && $mdl_type == 'REVALID') ? 'selected' : ''; ?>>REVALID</option>
                            </select>
                        </div>

                        
                        
                    </div>
                </div>

                <div id="BothType" style="display: <?php echo ($is_edit && $bmds_type == 'LLR' || $bmds_type == 'DL') || ($add_new && $bmds_type == 'LLR' || $bmds_type == 'DL') ? 'block' : 'none'; ?>;">
                    
                    <div class="row g-3 mt-1">


                        <div class="col-md-5 field">
                            <label for="city" class="form-label">Test Place</label>
                            <select class="form-select" name="city" id="city" onchange="handlecityChange()">
                                <option value="">Select City</option>


                                <option value="NASHIK" <?php echo ($is_edit && $city == 'NASHIK') || ($add_new && $city == 'NASHIK') ? 'selected' : ''; ?>>NASHIK</option>

                                <option value="PIMPALGAON (B)" <?php echo ($is_edit && $city == 'PIMPALGAON (B)') || ($add_new && $city == 'PIMPALGAON (B)') ? 'selected' : ''; ?>>PIMPALGAON (B)</option>

                                <option value="NIPHAD" <?php echo ($is_edit && $city == 'NIPHAD') || ($add_new && $city == 'NIPHAD') ? 'selected' : ''; ?>>NIPHAD</option>

                                <option value="LASALGAON" <?php echo ($is_edit && $city == 'LASALGAON') || ($add_new && $city == 'LASALGAON') ? 'selected' : ''; ?>>LASALGAON</option>

                                <option value="CHANDWAD" <?php echo ($is_edit && $city == 'CHANDWAD') || ($add_new && $city == 'CHANDWAD') ? 'selected' : ''; ?>>CHANDWAD</option>

                                <option value="DINDORI" <?php echo ($is_edit && $city == 'DINDORI') || ($add_new && $city == 'DINDORI') ? 'selected' : ''; ?>>DINDORI</option>

                                
                                <!-- Dynamically add custom options here -->
                                <?php
                                $query = "SELECT DISTINCT city FROM bmds_entries WHERE city NOT IN ('NASHIK', 'PIMPALGAON (B)','NIPHAD','LASALGAON','CHANDWAD','DINDORI')";
                                $result = $conn->query($query);
                            

                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($is_edit || $add_new) && $city == $row['city'] ? 'selected' : '';
                                    echo "<option value='{$row['city']}' $selected>{$row['city']}</option>";
                                }
                                ?>
                                
                                <option value="Enter Manually">Enter Manually</option>

                            </select>
                        
                            <!-- Input for manual entry -->
                            <input type="text" name="custom_city" value="<?php echo isset($custom_city) && htmlspecialchars($custom_city); ?>" class="form-control mt-2" id="customcity" placeholder="Enter Class of Vehicle Manually" style="display:none;">

                        </div>

                        <div class="col-md-2 field">
                            <label for="startDate" class="form-label">Sr Number</label>
                            <input type="text" name="sr_num" value="<?php echo htmlspecialchars($sr_num); ?>" class="form-control" >
                        </div>
                        
                        <div class="col-md-3 field">
                            <label for="startDate" class="form-label">Test/Camp Date</label>
                            <input type="date" name="test_date" value="<?php echo htmlspecialchars($test_date); ?>" class="form-control text-primary" id="startDate" >
                        </div>

                        <div class="col-md-5 field">
                            <label for="carType" class="form-label">Class of Vehicle</label>
                            <select class="form-select" name="llr_class" id="LLRclass" onchange="handleListTypeChange()">
                                <option value="">Select Class of Vehicle</option>

                                <option value="M/CY.WITHOUT GEAR" <?php echo ($is_edit && $llr_class == 'M/CY.WITHOUT GEAR') || ($add_new && $llr_class == 'M/CY.WITHOUT GEAR') ? 'selected' : ''; ?>>M/CY.WITHOUT GEAR</option>

                                <option value="M/CY.WITH GEAR" <?php echo ($is_edit && $llr_class == 'M/CY.WITH GEAR') || ($add_new && $llr_class == 'M/CY.WITH GEAR') ? 'selected' : ''; ?>>M/CY.WITH GEAR</option>

                                <option value="LMV(NT)" <?php echo ($is_edit && $llr_class == 'LMV(NT)') || ($add_new && $llr_class == 'LMV(NT)') ? 'selected' : ''; ?>>LMV(NT)</option>

                                <option value="LMV(TR)" <?php echo ($is_edit && $llr_class == 'LMV(TR)') || ($add_new && $llr_class == 'LMV(TR)') ? 'selected' : ''; ?>>LMV(TR)</option>

                                <option value="TRACTOR" <?php echo ($is_edit && $llr_class == 'TRACTOR') || ($add_new && $llr_class == 'TRACTOR') ? 'selected' : ''; ?>>TRACTOR</option>

                                <option value="LMV(TT)" <?php echo ($is_edit && $llr_class == 'LMV(TT)') || ($add_new && $llr_class == 'LMV(TT)') ? 'selected' : ''; ?>>LMV(TT)</option>

                                <option value="M/CY WITHOUT GEAR + LMV (NT)" <?php echo ($is_edit && $llr_class == 'M/CY WITHOUT GEAR + LMV (NT)') || ($add_new && $llr_class == 'M/CY WITHOUT GEAR + LMV (NT)') ? 'selected' : ''; ?>>M/CY WITHOUT GEAR + LMV (NT)</option>

                                <option value="LMV (NT) + TRACTOR" <?php echo ($is_edit && $llr_class == 'LMV (NT) + TRACTOR') || ($add_new && $llr_class == 'LMV (NT) + TRACTOR') ? 'selected' : ''; ?>>LMV (NT) + TRACTOR</option>

                                <option value="LMV (TR) + TRACTOR" <?php echo ($is_edit && $llr_class == 'LMV (TR) + TRACTOR') || ($add_new && $llr_class == 'LMV (TR) + TRACTOR') ? 'selected' : ''; ?>>LMV (TR) + TRACTOR</option>

                                <option value="M/CY WITH GEAR + LMV (TR)" <?php echo ($is_edit && $llr_class == 'M/CY WITH GEAR + LMV (TR)') || ($add_new && $llr_class == 'M/CY WITH GEAR + LMV (TR)') ? 'selected' : ''; ?>>M/CY WITH GEAR + LMV (TR)</option>

                                <option value="M/CY WITH GEAR + LMV (NT) + TRACTOR" <?php echo ($is_edit && $llr_class == 'M/CY WITH GEAR + LMV (NT) + TRACTOR') || ($add_new && $llr_class == 'M/CY WITH GEAR + LMV (NT) + TRACTOR') ? 'selected' : ''; ?>>M/CY WITH GEAR + LMV (NT) + TRACTOR</option>

                                <option value="M/CY WITH GEAR + LMV (TR) + TRACTOR" <?php echo ($is_edit && $llr_class == 'M/CY WITH GEAR + LMV (TR) + TRACTOR') || ($add_new && $llr_class == 'M/CY WITH GEAR + LMV (TR) + TRACTOR') ? 'selected' : ''; ?>>M/CY WITH GEAR + LMV (TR) + TRACTOR</option>
                                
                                
                                <!-- Dynamically add custom options here -->
                                <?php
                                $query = "SELECT DISTINCT llr_class FROM bmds_entries WHERE llr_class NOT IN ('M/CY.WITHOUT GEAR', 'M/CY.WITH GEAR','LMV(NT)','LMV(TR)','TRACTOR','LMV(TT)','M/CY WITHOUT GEAR + LMV (NT)','LMV (NT) + TRACTOR','LMV (TR) + TRACTOR','M/CY WITH GEAR + LMV (TR)','M/CY WITH GEAR + LMV (NT) + TRACTOR','M/CY WITH GEAR + LMV (TR) + TRACTOR')";
                                $result = $conn->query($query);
                            

                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($is_edit || $add_new) && $llr_class == $row['llr_class'] ? 'selected' : '';
                                    echo "<option value='{$row['llr_class']}' $selected>{$row['llr_class']}</option>";
                                }
                                ?>
                                
                                <option value="Enter Manually">Enter Manually</option>

                            </select>
                        
                            <!-- Input for manual entry -->
                            <input type="text" name="custom_llr_class" value="<?php echo isset($custom_llr_class) && htmlspecialchars($custom_llr_class); ?>" class="form-control mt-2" id="customLLRclass" placeholder="Enter Class of Vehicle Manually" style="display:none;">

                        </div>

                        <div class="col-md-2 field">
                            <label for="llrClass" class="form-label">No Of Class</label>
                            <select class="form-select" name="class" id="llrClass">
                                <option value="">Select Class</option>
                                <option value="1" <?php echo ($is_edit && $class == '1') || ($add_new && $class == '1') ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo ($is_edit && $class == '2') || ($add_new && $class == '2') ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?php echo ($is_edit && $class == '3') || ($add_new && $class == '3') ? 'selected' : ''; ?>>3</option>
                            </select>
                        </div>
                    </div>
                </div>

            
            <div class="row g-3 mt-1">
            
                <div class="col-md-4 field">
                    <label for="quotationAmount" class="form-label">Quotation Amount</label>
                    <input type="number" name="amount" value="<?php echo htmlspecialchars($amount); ?>" class="form-control" id="quotationAmount" placeholder="Enter Quotation Amount" oninput="updateAmounts()" onkeypress="return isNumeric(event)" >
                </div>
                
                <div class="col-md-4 field">
                    <label for="advanceAmount" class="form-label">Advance Amount</label>
                    <input type="number" name="adv_amount" value="<?php echo htmlspecialchars($adv_amount); ?>" class="form-control" id="advanceAmount" placeholder="Enter Advance Amount" oninput="updateAmounts()" onkeypress="return isNumeric(event)" >
                </div>
                
                <div class="col-md-4 field">
                    <label for="balanceAmount" class="form-label">Excess Amount</label>
                    <input type="number" name="bal_amount" value="<?php echo htmlspecialchars($bal_amount); ?>" class="form-control" id="balanceAmount" placeholder="Balance Amount" readonly >
                </div>
                
                <div class="col-md-6 field">
                    <label for="recoveryAmount" class="form-label">Recovery Amount</label>
                    <input type="number" name="recov_amount" value="<?php echo htmlspecialchars($recov_amount); ?>" class="form-control" id="recoveryAmount" placeholder="Recovery Amount" readonly >
                </div>
                
                <div class="col-md-6 field">
                    <label for="mobileNumber" class="form-label">Responsibility </label>
                    <input type="text" name="responsibility" value="<?php echo htmlspecialchars($responsibility); ?>" class="form-control" id="mobileNumber" placeholder="Enter Responsibility " >
                </div>
            
            </div>

            <div class="col-md-12 field mt-1">
                <label for="remark" class="form-label">Remark</label>
                <textarea class="form-control" name="remark" id="remark" rows="3" placeholder="Enter Remark"><?php echo htmlspecialchars($remark); ?></textarea>
            </div>
        
            <div class="col-md-6 mt-3 mx-auto p-2 field" style="background-color: #ffcdcd;">
                <label for="motorSubType" class="form-label">Form Status</label>
                <select class="form-select" name="form_status" id="motorSubType" required>
                    <option value="PENDING" <?php if ($is_edit && $form_status == 'PENDING') echo 'selected'; ?>>PENDING</option>
                    <option value="COMPLETE" <?php if ($is_edit && $form_status == 'COMPLETE') echo 'selected'; ?>>COMPLETE</option>
                </select>
            </div>


            <!-- button -->
            <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
            
            

            
        </form>
    </div>
</section>


<?php
$conn->close();
?>      

<script>

     // Show/hide relevant sections based on Policy Type
     document.getElementById('policyType').addEventListener('change', function () {
        const type = this.value;

        document.getElementById('LLRType').style.display = type === 'LLR' ? 'block' : 'none';
        document.getElementById('MDLType').style.display = type === 'DL' ? 'block' : 'none';
        document.getElementById('BothType').style.display = (type === 'DL' || type === 'LLR') ? 'block' : 'none';
        document.getElementById('ADMType').style.display = type === 'ADM' ? 'block' : 'none';
    });


// Handle car type selection
function handleCarTypeChange() {
    var carTypeSelect = document.getElementById('carType');
    var customCarTypeInput = document.getElementById('customCarType');

    // Show the manual input field if "Enter Manually" is selected
    if (carTypeSelect.value === 'Enter Manually') {
        customCarTypeInput.style.display = 'block';
        customCarTypeInput.name = 'car_type'; // Set the custom input to the same name as the dropdown
    } else {
        customCarTypeInput.style.display = 'none';
        customCarTypeInput.name = 'custom_car_type'; // Reset to avoid conflicts
    }
}

// Handle List type selection
function handleListTypeChange() {
    var carTypeSelect = document.getElementById('LLRclass');
    var customCarTypeInput = document.getElementById('customLLRclass');

    // Show the manual input field if "Enter Manually" is selected
    if (carTypeSelect.value === 'Enter Manually') {
        customCarTypeInput.style.display = 'block';
        customCarTypeInput.name = 'llr_class'; // Set the custom input to the same name as the dropdown
    } else {
        customCarTypeInput.style.display = 'none';
        customCarTypeInput.name = 'custom_llr_class'; // Reset to avoid conflicts
    }
}

// Handle city selection
function handlecityChange() {
    var carTypeSelect = document.getElementById('city');
    var customCarTypeInput = document.getElementById('customcity');

    // Show the manual input field if "Enter Manually" is selected
    if (carTypeSelect.value === 'Enter Manually') {
        customCarTypeInput.style.display = 'block';
        customCarTypeInput.name = 'city'; // Set the custom input to the same name as the dropdown
    } else {
        customCarTypeInput.style.display = 'none';
        customCarTypeInput.name = 'custom_city'; // Reset to avoid conflicts
    }
}


// Function to calculate balance and recovery amounts
function updateAmounts() {
    const quotationAmount = parseFloat(document.getElementById('quotationAmount').value) || 0;
    const advanceAmount = parseFloat(document.getElementById('advanceAmount').value) || 0;

    let balance = 0;
    let recovery = 0;

    if (advanceAmount > quotationAmount) {
        balance = advanceAmount - quotationAmount;
    } else if (advanceAmount < quotationAmount) {
        recovery = quotationAmount - advanceAmount;
    }

    document.getElementById('balanceAmount').value = balance;
    document.getElementById('recoveryAmount').value = recovery;
}



// Allow only numeric input
function isNumeric(event) {
    const keyCode = event.keyCode || event.which;
    const keyValue = String.fromCharCode(keyCode);

    // Allow only digits (0-9) and restrict any other characters
    return /^[0-9]*$/.test(keyValue);
}

// script for featching city name.
function searchCity() {
    const input = document.getElementById('city').value;
    const list = document.getElementById('cityList');

    if (input.length < 1) {
        list.innerHTML = '';
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_cities.php?query=" + input, true);
    xhr.onload = function () {
        if (this.status === 200) {
            list.innerHTML = this.responseText;
        }
    };
    xhr.send();
}

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('suggestion')) {
        document.getElementById('city').value = e.target.innerText;
        document.getElementById('cityList').innerHTML = '';
    }
});

</script>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php 
    
// }
?>