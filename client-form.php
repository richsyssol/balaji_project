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
$client_type = '';
$address = '';
$contact = '';
$contact_alt = '';
$birth_date = '';
$anniversary_date = '';
$email = '';
$gst_no = '';
$pan_no = '';
$aadhar_no = '';
$inquiry = '';
$reference = '';
$age = '';
$tag = '';
$pincode = '';
$errors = [];
$is_edit = false;
$add_new = false;
$id = null; // Initialize $id variable


// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM client WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reg_num = $row['reg_num'];
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $client_name = strtoupper($row['client_name']);
        $client_type = $row['client_type'];
        $address = strtoupper($row['address']);
        $contact = $row['contact'];
        $contact_alt = $row['contact_alt'];
        $birth_date = $row['birth_date'];
        $anniversary_date = $row['anniversary_date'];
        $email = $row['email'];
        $gst_no = $row['gst_no'];
        $pan_no = strtoupper($row['pan_no']);
        $aadhar_no = $row['aadhar_no'];
        $inquiry = strtoupper($row['inquiry']);
        $reference = strtoupper($row['reference']);
        $age = $row['age'];
        $tag = $row['tag'];
        $pincode = $row['pincode'];
    } else {
        die("Entry not found.");
    }
} 
elseif (isset($_GET['action']) && $_GET['action'] === 'add_new') {
    $is_edit = false;
    $add_new = true;

    // Find the smallest missing reg_num where is_deleted = 0
    $result = $conn->query("
    SELECT MIN(t1.reg_num + 1) AS next_reg_num 
    FROM client t1 
    LEFT JOIN client t2 ON t1.reg_num + 1 = t2.reg_num AND t2.is_deleted = 0
    WHERE t1.is_deleted = 0 AND t2.reg_num IS NULL
    ");

    $missingMessage = ''; // Default empty message

    if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $reg_num = $row['next_reg_num'] ?? 1; // Assign next available number

    // Check if the assigned reg_num is filling a missing gap
    $lastRegQuery = $conn->query("SELECT MAX(reg_num) AS max_reg FROM client WHERE is_deleted = 0");
    $lastRegRow = $lastRegQuery->fetch_assoc();
    $maxReg = $lastRegRow['max_reg'];

    if ($reg_num <= $maxReg) {
        $missingMessage = "Note: This registration number is filling a previously deleted number.";
    }
    } else {
    $reg_num = 1; // If no records exist, start with 1
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
    $client_type = strtoupper(trim($_POST['client_type']));
    $address = strtoupper(trim($_POST['address']));
    $contact = trim($_POST['contact']);
    $contact_alt = trim($_POST['contact_alt']);
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $anniversary_date = isset($_POST['anniversary_date']) ? trim($_POST['anniversary_date']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $gst_no = isset($_POST['gst_no']) ? trim($_POST['gst_no']) : '';
    $pan_no = isset($_POST['pan_no']) ? strtoupper(trim($_POST['pan_no'])) : '';
    $aadhar_no = isset($_POST['aadhar_no']) ? trim($_POST['aadhar_no']) : '';
    $inquiry = isset($_POST['inquiry']) ? strtoupper(trim($_POST['inquiry'])) : '';
    $reference = isset($_POST['reference']) ? strtoupper(trim($_POST['reference'])) : '';
    $age = isset($_POST['age']) ? trim($_POST['age']) : '';
    $tag = isset($_POST['tag']) ? trim($_POST['tag']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';

    // Validation
    if (empty($client_name) || !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $client_name)) {
        $errors[] = "Invalid client name";
    }
    

    if (empty($contact) || !preg_match("/^\d{10}$/", $contact)) {
        $errors[] = "Invalid contact number";
    }
    
    
    
    if (!empty($reference) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $reference)) {
        $errors[] = "Invalid referance";
    }
    
    if (!empty($inquiry) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $inquiry)) {
        $errors[] = "Invalid inquiry";
    }
    
    if (!empty($address) && !preg_match("/^[A-Za-z0-9\s\/\-\(\)\[\]\{\}\+\*\=\_\%\&\!\@\#\$\^\:\;\,\<\>\.\?\"\'\\\|]+$/", $address)) {
        $errors[] = "Invalid address";
    }
    
    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone


    // echo "<pre>";
    
    // print_r($_POST);
    
    // exit;

    // If no errors, process the form
    if (empty($errors)) {

        if ($is_edit) {
            $aadhar_no = $_POST['aadhar_no'];
            $pan_no = $_POST['pan_no'];
            
            $error_message = ""; // Initialize error message
        
            if (!empty($aadhar_no)) {
                // Check if the Aadhar number already exists, excluding the current ID
                $checkAadharQuery = "SELECT * FROM client WHERE aadhar_no = ? AND id != ?";
                $stmt = $conn->prepare($checkAadharQuery);
                $stmt->bind_param("si", $aadhar_no, $id); 
                $stmt->execute();
                $result = $stmt->get_result();
        
                if ($result && $result->num_rows > 0) {
                    $error_message = "This Aadhar number is already registered.";
                }
                $stmt->close();
            }
        
            if (empty($error_message) && !empty($pan_no)) {
                // Check if the PAN number already exists, excluding the current ID
                $checkPanQuery = "SELECT * FROM client WHERE pan_no = ? AND id != ?";
                $stmt = $conn->prepare($checkPanQuery);
                $stmt->bind_param("si", $pan_no, $id); 
                $stmt->execute();
                $result = $stmt->get_result();
        
                if ($result && $result->num_rows > 0) {
                    $error_message = "This PAN number is already registered.";
                }
                $stmt->close();
            }
        
            // Only proceed with the update if there is no error
            if (empty($error_message)) {

                $address = $_POST['address'] ?? '';
                $addressCustom = $_POST['addressCustom'] ?? '';
                
                $inquiry = $_POST['inquiry'] ?? '';
                $inquiryCustomtype = $_POST['inquiryCustomtype'] ?? '';
        
                // If 'Enter Manually' is selected, use the custom input
                if ($address == "Enter Manually") {
                    $address = strtoupper(trim($addressCustom));
                }

                if ($inquiry == "Enter Manually") {
                    $inquiry = strtoupper(trim($inquiryCustomtype));
                }
        
                // Fetch old contact number before updating
                $old_contact = "";
                $fetch_old_contact_query = "SELECT contact FROM client WHERE id = ?";
                $stmt = $conn->prepare($fetch_old_contact_query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $old_contact = $row['contact'];
                }
                $stmt->close();
        
                // Update client table
                $sql = "UPDATE client SET reg_num='$reg_num', policy_date='$policy_date', time='$time', client_name='$client_name', 
                                client_type='$client_type', address='$address', contact='$contact', contact_alt='$contact_alt', 
                                birth_date='$birth_date', anniversary_date='$anniversary_date', email='$email', gst_no='$gst_no', 
                                pan_no='$pan_no', aadhar_no='$aadhar_no', inquiry='$inquiry', reference='$reference', 
                                age='$age', tag='$tag', pincode='$pincode' WHERE id=$id";
        
                $stmt = $conn->prepare($sql);
                
                
        
                if ($stmt->execute()) {
                    // If contact or other details are changed, update them in all related tables
                    if ($old_contact !== $contact || $old_client_name !== $client_name || $old_address !== $address || $old_birth_date !== $birth_date) {
                        // Update gic_entries table separately since it has additional fields
                        $update_gic_query = "UPDATE gic_entries SET client_name = ?, client_type = ?, address = ?, contact_alt = ?, contact = ?, birth_date = ?, email = ? WHERE client_id = ?";
                        $stmt_gic = $conn->prepare($update_gic_query);
                        $stmt_gic->bind_param("sssssssi", $client_name, $client_type, $address, $contact_alt, $contact, $birth_date, $email, $id);
                        $stmt_gic->execute();
                        $stmt_gic->close();
                
                        // Tables with common fields
                        $tables = ['mf_entries', 'bmds_entries', 'lic_entries', 'rto_entries'];
                        foreach ($tables as $table) {
                            $update_query = "UPDATE $table SET client_name = ?, address = ?, contact = ?, birth_date = ? WHERE client_id = ?";
                            $stmt2 = $conn->prepare($update_query);
                            $stmt2->bind_param("ssssi", $client_name, $address, $contact, $birth_date, $id);
                            $stmt2->execute();
                            $stmt2->close();
                        }
                    }
                
                    header("Location: client");
                    exit();
                } else {
                    echo "Error updating record: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                echo $error_message; // Display the error message
            }
        }  
        
        elseif ($add_new) {

            // username for who filled the form
            $username = $_SESSION['username'];
            $aadhar_no = $_POST['aadhar_no'];
            $pan_no = $_POST['pan_no'];
        
            $error_message = ""; // Initialize error message
        
            if (!empty($aadhar_no)) {
                // Check if the Aadhar number already exists
                $checkAadharQuery = "SELECT client_name , contact FROM client WHERE aadhar_no = ?";
                $stmt = $conn->prepare($checkAadharQuery);
                $stmt->bind_param("s", $aadhar_no);
                $stmt->execute();
                $result = $stmt->get_result();
        
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $error_message = "This Aadhar number is already registered for client: <strong>" . htmlspecialchars($row['client_name']) . '/' . htmlspecialchars($row['contact']) . "</strong>.";
                }
                $stmt->close();
            }
        
            if (empty($error_message) && !empty($pan_no)) {
                // Check if the PAN number already exists
                $checkPanQuery = "SELECT client_name , contact FROM client WHERE pan_no = ?";
                $stmt = $conn->prepare($checkPanQuery);
                $stmt->bind_param("s", $pan_no);
                $stmt->execute();
                $result = $stmt->get_result();
        
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $error_message = "This PAN number is already registered for client: <strong>" . htmlspecialchars($row['client_name'])  . '/' . htmlspecialchars($row['contact']) . "</strong>.";
                }
                $stmt->close();
            }
        
            // Only proceed with insertion if there is no error
            if (empty($error_message)) {
                $address = $_POST['address'] ?? '';
                $addressCustom = $_POST['addressCustom'] ?? '';
                $inquiry = $_POST['inquiry'] ?? '';
                $inquiryCustomtype = $_POST['inquiryCustomtype'] ?? '';
                $client_type = $_POST['client_type'] ?? '';
        
                // If 'Enter Manually' is selected, use the custom input
                if ($address == "Enter Manually") {
                    $address = strtoupper(trim($addressCustom));
                }

                if ($inquiry == "Enter Manually") {
                    $inquiry = strtoupper(trim($inquiryCustomtype));
                }
                
        
                // Add a new entry for the same reg_num
                $sql = "INSERT INTO client (reg_num, policy_date, time, client_name, client_type, address, contact, contact_alt, birth_date, anniversary_date, email, gst_no, pan_no, aadhar_no, inquiry, reference, age, tag, pincode) 
                        VALUES ('$reg_num', '$policy_date', '$time', '$client_name', '$client_type', '$address', '$contact', '$contact_alt', '$birth_date', '$anniversary_date', '$email', '$gst_no', '$pan_no', '$aadhar_no', '$inquiry', '$reference', '$age', '$tag', '$pincode')";
                
                if ($conn->query($sql) === TRUE) {
                    header("Location: client");
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
    
    
    <div class="container p-5">
        
        <div class="ps-5">
            <div>
                <h1>CLIENT FORM</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="client">Client</a></li>
                <li class="breadcrumb-item active" aria-current="page">Client Form</li>
              </ol>
            </nav>
        </div>
        
        <form 
            action="client-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
                } elseif ($add_new) {
                    echo '?action=add_new'; 
                } ?>" 
            method="POST" class="p-5 shadow bg-white">
            
             <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            
            <!--ALL INPUT FIELDS ERROR SHOWS-->
            <?php if (!empty($errors)): ?>
                <div style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            
            <!--CONTACT NUMBER REPETATION ERROR-->
            <?php if (!empty($error_message)): ?>
                        <div class="text-danger mt-2"><?php echo $error_message; ?></div>
                    <?php endif; ?>
            
            <div class="row g-3 mb-3">
            <!-- Register Number (Auto Generated) -->
            <div class="col-md-6 field">
                <label for="registerNumber" class="form-label">Client Sr.No</label>
                <input type="text" class="form-control" name="reg_num" id="registerNumber" 
                    value="<?= htmlspecialchars($reg_num) ?>" readonly>
                
                <?php if (!empty($missingMessage)): ?>
                    <small class="text-danger"><?= htmlspecialchars($missingMessage) ?></small>
                <?php endif; ?>
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
            
            <div class="col-md-2 field">
                <label for="mobileNumber" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact) ?? $row['contact']; ?>" id="mobileNumber" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10" required>
                
            </div>
            
            
            

            <div class="col-md-2 field">
                <label for="clientType" class="form-label">Client Type</label>
                <select class="form-select" name="client_type" id="clientType">
                    <option value="INDIVIDUAL" <?php if ($is_edit && $client_type == 'INDIVIDUAL') echo 'selected'; ?>>INDIVIDUAL</option>
                    <option value="CORPORATE" <?php if ($is_edit && $client_type == 'CORPORATE') echo 'selected'; ?>>CORPORATE</option>
                </select>
            </div>
            
            <!-- Client Name -->
            <div class="col-md-6 field">
                <label for="clientName" class="form-label">Client Name</label>
                <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"  placeholder="Enter Client Name" required>
            </div>
            
            <div class="col-md-2 field">
                <label for="clientName" class="form-label">Tag</label>
                <select class="form-select" name="tag">
                    <option selected>Select Tag</option>
                    <option value="A" <?php if ($is_edit && $tag === 'A') echo 'selected'; ?>>A</option>
                    <option value="B" <?php if ($is_edit && $tag === 'B') echo 'selected'; ?>>B</option>
                    <option value="C" <?php if ($is_edit && $tag === 'C') echo 'selected'; ?>>C</option>
                </select> 
            </div>
        
            
           <div class="col-md-6 field">
                <label for="city" class="form-label">City :</label><br>
                <input type="text" id="city" name="address" value="<?php echo htmlspecialchars($address); ?>" class="form-control" autocomplete="off" onkeyup="searchCity()" />
                <div id="cityList" style="background-color:#d1d1d1"></div>

                <a href="city_add" type="button" class="btn btn-success ms-2 p-0">Add</a>



            </div>

            <div class="col-md-6 field">
                <label for="clientName" class="form-label">Pincode</label>
                <input 
                    type="text" 
                    class="form-control" 
                    name="pincode" 
                    id="pincode" 
                    value="<?php echo isset($pincode) ? htmlspecialchars($pincode) : ''; ?>"  
                    placeholder="Enter pincode"
                >
            </div> 




            
            
            <div class="col-md-3 field">
                <label for="mobileNumber" class="form-label">Alternate Mobile Number</label>
                <input type="tel" class="form-control" name="contact_alt" value="<?php echo htmlspecialchars($contact_alt); ?>" id="mobileNumber" value="<?php echo $row['contact_alt']; ?>" placeholder="Enter 10 digit mobile number" pattern="\d{10}" minlength="10" maxlength="10">
            </div>
            
            <div class="col-md-3 field">
                <label for="date" class="form-label">Birth Date</label>
                <input type="date" class="form-control text-success" name="birth_date" id="birthdate" value="<?php echo htmlspecialchars($birth_date);?>" onchange="calculateAge()">
            </div>
            
            <div class="col-md-3 field">
                <label for="mobileNumber" class="form-label">Age</label>
                <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($age); ?>" id="age" placeholder="Enter Age" readonly>
            </div>
            
            <div class="col-md-3 field">
                <label for="date" class="form-label">Anniversary Date</label>
                <input type="date" class="form-control text-success" name="anniversary_date" id="date" value="<?php echo htmlspecialchars($anniversary_date);?>" >
            </div>
            
            <div class="col-md-6 field">
                <label for="mobileNumber" class="form-label">Aadhar Number</label>
                <input type="tel" class="form-control" name="aadhar_no" value="<?php echo htmlspecialchars($aadhar_no); ?>" id="mobileNumber" value="<?php echo $row['aadhar_no']; ?>" placeholder="Enter aadhar number">
            </div>
            
            
            

            <div class="col-md-6 field">
                <label for="mobileNumber" class="form-label">Pan Number</label>
                <input type="tel" class="form-control" name="pan_no" value="<?php echo htmlspecialchars($pan_no); ?>" id="mobileNumber" value="<?php echo $row['pan_no']; ?>" placeholder="Enter pan number">
            </div>
            
            
            
            <div class="col-md-4 field">
                <label for="mobileNumber" class="form-label">GST Number</label>
                <input type="tel" class="form-control" name="gst_no" value="<?php echo htmlspecialchars($gst_no); ?>" id="mobileNumber" value="<?php echo $row['gst_no']; ?>" placeholder="Enter gst number">
            </div>
            
            <div class="col-md-4 field1">
                <label for="mobileNumber" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" id="mobileNumber" placeholder="Enter email address">
            </div>

            <div class="col-md-4 field">
                <label for="expenseType" class="form-label">Inquiry For</label>
                <select class="form-select" name="inquiry" id="inquiry" onchange="handleinquiryChange()">
                    <option value="" selected>Select inquiry Type</option>
                    <option value="GIC" <?php if ($is_edit && $inquiry === 'GIC') echo 'selected'; ?>>GIC</option>
                    <option value="LIC" <?php if ($is_edit && $inquiry === 'LIC') echo 'selected'; ?>>LIC</option>
                    <option value="RTO" <?php if ($is_edit && $inquiry === 'RTO') echo 'selected'; ?>>RTO</option>
                    <option value="BMDS" <?php if ($is_edit && $inquiry === 'BMDS') echo 'selected'; ?>>BMDS</option>
                    <option value="MF" <?php if ($is_edit && $inquiry === 'MF') echo 'selected'; ?>>MF</option>
                    

                    <?php
                    // Dynamically add custom options from the database
                    $query = "SELECT DISTINCT inquiry FROM client WHERE inquiry NOT IN ('GIC', 'LIC', 'RTO', 'BMDS', 'MF')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $inquiry_type = strtoupper($row['inquiry']);
                        echo "<option value='{$inquiry_type}'" . ($is_edit && $inquiry === $inquiry_type ? ' selected' : '') . ">{$row['inquiry']}</option>";
                    }
                    ?>

                    <option value="Enter Manually" <?php echo ($is_edit && empty($inquiry) && !empty($inquiryCustomtype)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>

                <input type="text" name="inquiryCustomtype" value="<?php echo isset($inquiryCustomtype) && htmlspecialchars($inquiryCustomtype); ?>" class="form-control mt-2" id="inquiryCustomtype" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($inquiry) && !empty($inquiryCustomtype)) ? 'block' : 'none'; ?>;">
            </div>
            
        </div>
        
        
            
            
            
            <div class="row g-3 mb-3">

                <!-- <div class="col-md-6 field">
                    <label for="remark" class="form-label">Inquiry For</label>
                    <textarea class="form-control" name="inquiry" id="remark" rows="3" placeholder="Enter Inquiry"><?php echo htmlspecialchars($inquiry); ?></textarea>
                </div> -->

                
                
                
                <div class="col-md-12 field">
                    <label for="remark" class="form-label">Reference From</label>
                    <textarea class="form-control" name="reference" id="remark" rows="3" placeholder="Enter Reference"><?php echo htmlspecialchars($reference); ?></textarea>
                </div>

            </div>
            
            

        <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
    
        
        

        
        
            
            
            
        </form>
    </div>
</section>

<?php
$conn->close();
?>


<script> 


    // Handle Address selection
    function toggleManualAddress() {
    var addressSelect = document.getElementById('address');
    var customAddressInput = document.getElementById('addressCustom');

    // Show or hide the manual address input field
    if (addressSelect.value === 'Enter Manually') {
        customAddressInput.style.display = 'block';
    } else {
        customAddressInput.style.display = 'none';
    }
}

//     Handle INQUIRY selection
function handleinquiryChange() {
    var companySelect = document.getElementById('inquiry');
    var customCompanyInput = document.getElementById('inquiryCustomtype');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'inquiry'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'inquiryCustomtype'; // Reset to avoid conflicts
    }
}

    // Script for calculate age from DOB 

     function calculateAge() {
        const dob = document.getElementById('birthdate').value; // Get the value of the DOB input
        const ageField = document.getElementById('age');  // Get the age input field

        if (dob) {
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDifference = today.getMonth() - birthDate.getMonth();

            // Adjust if the current date is before the birth date in the current year
            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            ageField.value = age; // Set the calculated age in the age field
        }
    }


  

// script for search city
    function searchCity() {
        const input = document.getElementById('city').value;
        const list = document.getElementById('cityList');

        if (input.length < 1) {
            list.innerHTML = '';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_cities.php?query=" + encodeURIComponent(input), true);
        xhr.onload = function () {
            if (this.status === 200) {
                list.innerHTML = this.responseText;
            }
        };
        xhr.send();
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('suggestion')) {
            const selectedCity = e.target.innerText;
            document.getElementById('city').value = selectedCity;
            document.getElementById('cityList').innerHTML = '';

            // Fetch corresponding pincode
            fetch('fetch_pincode.php?city=' + encodeURIComponent(selectedCity))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('pincode').value = data;
                });
        }
    });
</script>

  



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>