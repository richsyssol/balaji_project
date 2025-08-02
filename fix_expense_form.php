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
$policy_date = '';
$time = '';
$amount = '';
$expense_type = '';
$mv_num = '';
$vehicle_type = '';
$insurance_company = '';
$prop_name = '';
$prop_holder_name = '';
$product_name = '';
$start_date = '';
$end_date = '';
$details = '';

$errors = [];
$is_edit = false;
$add_new = false;
$id = null; // Initialize $id variable

// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM fix_expense WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $policy_date = $row['policy_date'];
        $time = $row['time'];
        $amount = $row['amount'];
        $expense_type = strtoupper($row['expense_type']);
        $mv_num = $row['mv_num'];
        $vehicle_type = strtoupper($row['vehicle_type']);
        $insurance_company = strtoupper($row['insurance_company']);
        $prop_name = strtoupper($row['prop_name']);
        $prop_holder_name = strtoupper($row['prop_holder_name']);
        $product_name = strtoupper($row['product_name']);
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $details = strtoupper($row['details']);

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
    

    // Collect and sanitize inputs
    $policy_date = trim($_POST['policy_date']);
    $time = trim($_POST['time']);
    $amount = trim($_POST['amount']);
    $expense_type = strtoupper(trim($_POST['expense_type']));
    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
    $insurance_company = strtoupper(trim($_POST['insurance_company']));
    $prop_name = strtoupper(trim($_POST['prop_name']));
    $prop_holder_name = strtoupper(trim($_POST['prop_holder_name']));
    $product_name = strtoupper(trim($_POST['product_name']));
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $details = strtoupper(trim($_POST['details']));
    
    // Retrieve form data
$mv_num = $_POST['mv_num'] ?? null;


    // Validation
    if (!empty($amount) && !is_numeric($amount)) {
        $errors[] = "Invalid amount";
    }
    
    if (empty($expense_type) || !preg_match("/^[A-Za-z ]+$/", $expense_type)) {
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


            $$expense_type = $_POST['$expense_type'] === 'Enter Manually' ? $_POST['expenseCustomtype'] : $_POST['$expense_type'];
            $mv_num = $_POST['mv_num'] === 'Enter Manually' ? $_POST['mvcustom'] : $_POST['mv_num'];

            // Update existing entry
            $sql = "UPDATE fix_expense SET  policy_date='$policy_date',time='$time', amount='$amount', expense_type='$expense_type', mv_num='$mv_num',vehicle_type='$vehicle_type',insurance_company='$insurance_company',prop_name='$prop_name',details='$details',start_date='$start_date',end_date='$end_date',prop_holder_name = '$prop_holder_name', product_name = '$product_name' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: fix_expense");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        }   
        
        else {
            // Username for the user who filled the form
            $username = $_SESSION['username'];
        
            // Capture data from the form
            $expense_type = $_POST['expense_type'];
            $expenseCustomtype = $_POST['expenseCustomtype'] ?? '';
        
            // If 'Enter Manually' is selected, use the custom type
            if ($expense_type == "Enter Manually") {
                $expense_type = $expenseCustomtype;
            }

            $insurance_company = $_POST['insurance_company'] ?? '';
                    $insurancecustom = $_POST['insurancecustom'] ?? '';
                    
                    // If 'Enter Manually' is selected, use the custom input
                    if ($insurance_company == "Enter Manually") {
                        $insurance_company = trim($insurancecustom);
                    }
        
            // Insert Fixed Expense
            $stmt = $conn->prepare("INSERT INTO fix_expense (policy_date, time, amount, expense_type,insurance_company,prop_name, creation_on, mv_num, vehicle_type, details,start_date,end_date,prop_holder_name,product_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssss", $policy_date, $time, $amount, $expense_type,$insurance_company,$prop_name, $creation_on, $mv_num, $vehicle_type, $details, $start_date,$end_date,$prop_holder_name,$product_name);
        
            // Execute the statement once
            if ($stmt->execute()) {
                // Get inserted Expense ID
                $expense_id = $stmt->insert_id;
        
                // Insert a Reminder
                $reminder_stmt = $conn->prepare("INSERT INTO fix_expense_reminders (expense_id, reminder_date) VALUES (?, ?)");
                $reminder_stmt->bind_param("is", $expense_id, $end_date);
                $reminder_stmt->execute();
        
                // Redirect after successful insertion
                header("Location: fix_expense.php");
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
                <h1>Fix Expenses Register</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="fix_expense">Fix Expenses</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Fix Expenses Register</li>
                </ol>
            </nav>
        </div>
        <form 
            action="fix_expense_form.php<?php 
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
                <div class="col-md-6 field">
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
            
            <div class="col-md-6 field">
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
        
            <!-- Type of Expense -->
            <div class="col-md-12 field">
                <label for="expenseType" class="form-label">Type of Task</label>
                <select class="form-select" name="expense_type" id="expenseType" onchange="typeExpense()">
                    <option value="" selected>Select Expense Type</option>
                    <option value="Vehicle Insurance" <?php if ($is_edit && $expense_type === 'Vehicle Insurance') echo 'selected'; ?>>Vehicle Insurance</option>
                    <option value="Property Insurance" <?php if ($is_edit && $expense_type === 'Property Insurance') echo 'selected'; ?>>Property Insurance</option>
                    <option value="Life Insurance" <?php if ($is_edit && $expense_type === 'Life Insurance') echo 'selected'; ?>>Life Insurance</option>

                    <?php
                    // Dynamically add custom options from the database
                    $query = "SELECT DISTINCT expense_type FROM fix_expense WHERE expense_type NOT IN ('Vehicle Insurance','Property Insurance','Life Insurance')";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $dynamic_expense_type = htmlspecialchars($row['expense_type']); // Properly escape
                        echo "<option value='{$dynamic_expense_type}'" . ($is_edit && $expense_type === $dynamic_expense_type ? ' selected' : '') . ">{$dynamic_expense_type}</option>";
                    }
                    ?>

                    <option value="Enter Manually" <?php echo ($is_edit && empty($expense_type) && !empty($expenseCustomtype)) ? 'selected' : ''; ?>>Enter Manually</option>
                </select>

                <input type="text" name="expenseCustomtype" value="<?php echo htmlspecialchars($expenseCustomtype ?? ''); ?>" class="form-control mt-2" id="expenseCustomtype" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($expense_type) && !empty($expenseCustomtype)) ? 'block' : 'none'; ?>;">
            </div>

        
            
            
            
            
            
            
            
            <!-- MV Number Field -->
            
            <div id="mvNumberField" style="display: <?php echo ($is_edit && ($expense_type == 'VEHICLE INSURANCE')) ? 'block' : 'none'; ?>;" >
                
        
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
            

            <div id="propertyField" style="display: <?php echo ($is_edit && ($expense_type == 'Property Insurance')) ? 'block' : 'none'; ?>;" >

                <div class="col-md-6 field">
                    <label for="vehicle_type" class="form-label">Property name</label>
                    <input type="text" name="prop_name" value="<?php echo htmlspecialchars($prop_name); ?>" placeholder="Enter Property Name" class="form-control" id="prop_name">
                </div>

            </div>
            
            <div id="InsuranceField" style="display: <?php echo ($is_edit && ($expense_type == 'Property Insurance') || ($expense_type == 'VEHICLE INSURANCE')  || ($expense_type == 'Life Insurance')) ? 'block' : 'none'; ?>;" >

            <div class="col-md-12 field">
                    <label for="policyCompany" class="form-label">Insurance Company</label>
                    <select class="form-select" name="insurance_company" id="insurance_company" onchange="handleCompanyChange()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Insurance Company</option>
                        
                        <!-- Predefined options -->
                        <option value="ICICI LOMBARD GIC" <?php echo ($is_edit && $insurance_company == 'ICICI LOMBARD GIC') || ($add_new && $insurance_company == 'ICICI LOMBARD GIC') ? 'selected' : ''; ?>>ICICI LOMBARD GIC</option>

                        <option value="TATA AIG GIC" <?php echo ($is_edit && $insurance_company == 'TATA AIG GIC') || ($add_new && $insurance_company == 'TATA AIG GIC') ? 'selected' : ''; ?>>TATA AIG GIC</option>

                        <option value="UNITED INDIA INSURANCE CO LTD" <?php echo ($is_edit && $insurance_company == 'UNITED INDIA INSURANCE CO LTD') || ($add_new && $insurance_company == 'UNITED INDIA INSURANCE CO LTD') ? 'selected' : ''; ?>>UNITED INDIA INSURANCE CO LTD</option>

                        <option value="HDFC ERGO GIC" <?php echo ($is_edit && $insurance_company == 'HDFC ERGO GIC') || ($add_new && $insurance_company == 'HDFC ERGO GIC') ? 'selected' : ''; ?>>HDFC ERGO GIC</option>
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        // Ensure case consistency by using strtoupper for both the database value and the selected value
                        $query = "SELECT DISTINCT insurance_company FROM fix_expense WHERE insurance_company NOT IN ('ICICI LOMBARD GIC', 'Tata AIG GIC', 'United India Insurance Co Ltd','HDFC Ergo GIC')";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['insurance_company'])) {
                                // Convert insurance_company to uppercase to match the select dropdown options
                                $insurance_company_option = strtoupper($row['insurance_company']); 
                                $selected = '';
                                if (($is_edit || $add_new) && strtoupper($insurance_company) == $insurance_company_option) {
                                    $selected = 'selected';
                                }
                                echo "<option value='{$insurance_company_option}' $selected>{$insurance_company_option}</option>";
                            }
                        }
                        ?>
                    
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($insurance_company) && !empty($insurancecustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="insurancecustom" value="<?php echo isset($insurancecustom) && htmlspecialchars($insurancecustom); ?>" class="form-control mt-2" id="customCompanyType" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($insurance_company) && !empty($insurancecustom)) ? 'block' : 'none'; ?>;">
                </div>

            </div>


            <div id="LifeInsuranceField" style="display: <?php echo ($is_edit && ($expense_type == 'Life Insurance')) ? 'block' : 'none'; ?>;" >


            <div class="col-md-12 field">
                <label for="vehicle_type" class="form-label">Policy Holder Name</label>
                <input type="text" name="prop_holder_name" value="<?php echo htmlspecialchars($prop_holder_name); ?>" placeholder="Enter Policy Holder Name" class="form-control" id="prop_name">
            </div>

            <div class="col-md-12 field">
                    <label for="policyCompany" class="form-label">Product Name</label>
                    <select class="form-select" name="product_name" id="product_name" onchange="handleProductChange()">
                        <!-- Default option for a new form -->
                        <option value="" disabled <?php echo (!$is_edit) ? 'selected' : ''; ?>>Select Product Name</option>
                        
                    
                        
                        <!-- Dynamically add options from the database -->
                        <?php
                        // Ensure case consistency by using strtoupper for both the database value and the selected value
                        $query = "SELECT DISTINCT product_name FROM fix_expense";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['product_name'])) {
                                // Convert product_name to uppercase to match the select dropdown options
                                $product_name_option = strtoupper($row['product_name']); 
                                $selected = '';
                                if (($is_edit || $add_new) && strtoupper($product_name) == $product_name_option) {
                                    $selected = 'selected';
                                }
                                echo "<option value='{$product_name_option}' $selected>{$product_name_option}</option>";
                            }
                        }
                        ?>
                    
                        <!-- Option to enter manually -->
                        <option value="Enter Manually" <?php echo ($is_edit && empty($product_name) && !empty($productcustom)) ? 'selected' : ''; ?>>Enter Manually</option>
                    </select>
                    
                    <!-- Input for manual entry -->
                    <input type="text" name="productcustom" value="<?php echo isset($productcustom) && htmlspecialchars($productcustom); ?>" class="form-control mt-2" id="customCompanyType" placeholder="Enter Name Manually" style="display:<?php echo ($is_edit && empty($product_name) && !empty($productcustom)) ? 'block' : 'none'; ?>;">
                </div>

            </div>
            
            
        
            
                

              

                
            
                

        </div>


        <div class="row">   
            <!-- Enter Amount -->
            <div class="col-md-6 field">
                <label for="expenseAmount" class="form-label">Enter Amount</label>
                <input type="number" class="form-control" name="amount" value="<?= htmlspecialchars($amount) ?>" id="expenseAmount" placeholder="Enter Amount" required>
            </div>

        

        <!-- Start Date -->
            <div class="col-md-3 field">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" name="start_date" value="<?php 
                    if ($is_edit) {
                        echo htmlspecialchars($start_date); // Show the existing date if in edit mode
                    }
                ?>" class="form-control text-primary" id="startDate" onchange="adjustEndDate()" >
            </div>
            
            <!-- End Date -->
            <div class="col-md-3 field">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" name="end_date" value="<?php 
                    if ($is_edit) {
                        echo htmlspecialchars($end_date); // Show the existing date if in edit mode
                    }
                ?>" class="form-control text-primary" id="endDate" >
            </div>
        </div>

            <!-- Enter Details/Narration -->
            <div class="mb-3 field">
                <label for="expenseDetails" class="form-label">Enter Details/Narration</label>
                <textarea class="form-control" name="details" id="expenseDetails" rows="3" placeholder="Enter details or narration"><?= htmlspecialchars($details) ?></textarea>
            </div>

            <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Entry' : 'Add Entry'; ?>"> 
        
            
            
            
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





</script>


<script>
    
    function typeExpense() {
    const expenseType = document.getElementById('expenseType').value;
    const customExpenseInput = document.getElementById('expenseCustomtype');

    // Show/Hide MV Number field for "Vehicle Insurance"
    document.getElementById('mvNumberField').style.display = 
        (expenseType === 'Vehicle Insurance') ? 'block' : 'none';

    document.getElementById('propertyField').style.display = 
        (expenseType === 'Property Insurance') ? 'block' : 'none';

    document.getElementById('InsuranceField').style.display =   
        (expenseType === 'Life Insurance') || (expenseType === 'Vehicle Insurance') || (expenseType === 'Property Insurance') ? 'block' : 'none';

    document.getElementById('LifeInsuranceField').style.display = 
        (expenseType === 'Life Insurance') ? 'block' : 'none';

    document.getElementById('propertyField').style.display = 
        (expenseType === 'Property Insurance') ? 'block' : 'none';

    // Show the manual input field if "Enter Manually" is selected
    if (expenseType === 'Enter Manually') {
        customExpenseInput.style.display = 'block';
        customExpenseInput.setAttribute('name', 'expense_type'); // Override dropdown name
        customExpenseInput.required = true;
    } else {
        customExpenseInput.style.display = 'none';
        customExpenseInput.setAttribute('name', 'expenseCustomtype'); // Reset
        customExpenseInput.required = false;
    }
}





/// Handle Company selection
function handleCompanyChange() {
    var companySelect = document.getElementById('insurance_company');
    var customCompanyInput = document.getElementById('customCompanyType');

    // Show the manual input field if "Enter Manually" is selected
    if (companySelect.value === 'Enter Manually') {
        customCompanyInput.style.display = 'block';
        customCompanyInput.name = 'insurance_company'; // Set the custom input to the same name as the dropdown
    } else {
        customCompanyInput.style.display = 'none';
        customCompanyInput.name = 'insurancecustom'; // Reset to avoid conflicts
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