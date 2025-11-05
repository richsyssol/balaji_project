<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php

include 'includes/db_conn.php';

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$task_date = '';
$task_time = '';
$username = '';
$assign_by = '';
$task = '';
$contact_to = '';
$contact_no = '';
$priority = '';
$report_to = '';
$shift_task = '';
$recurrence_type = '';
$contact = '';
$task_end_date = '';
$weekly_value = '';
$monthly_value = '';
$quarterly_value = '';
$yearly_value = '';

$errors = [];
$is_edit = false;
$add_new = false;
$id = null; // Initialize $id variable


// Check if it's an edit operation
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch the existing record to populate the form
    $result = $conn->query("SELECT * FROM tasks WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $task_date = $row['task_date'];
        $task_time = $row['task_time'];
        $username = strtoupper($row['username']);
        $assign_by = strtoupper($row['assign_by']);
        $task = strtoupper($row['task']);
        $contact_to = strtoupper($row['contact_to']);
        $contact_no = $row['contact_no'];
        $priority = strtoupper(string:$row['priority']);
        $report_to = strtoupper($row['report_to']);
        $shift_task = $row['shift_task'];
        $recurrence_type = $row['recurrence_type'];
        $contact = $row['contact'];
        $task_end_date = $row['task_end_date'];
        $weekly_value = $row['weekly_value'];
        $monthly_value = $row['monthly_value'];
        $quarterly_value = $row['quarterly_value'];
        $yearly_value = $row['yearly_value'];
        
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
    $task_date = trim($_POST['task_date']);
    $task_time = trim($_POST['task_time']);
    $username = strtoupper(trim($_POST['username']));
    $assign_by = strtoupper(trim($_POST['assign_by']));
    $task = strtoupper(trim($_POST['task']));
    $contact_to = strtoupper(trim($_POST['contact_to']));
    $contact_no = trim($_POST['contact_no']);
    $priority = strtoupper(trim($_POST['priority']));
    $report_to = strtoupper(trim($_POST['report_to']));
    $shift_task = isset($_POST['shift_task']) ? $_POST['shift_task'] : ''; 

    // Collect expense fields
    $recurrence_type = !empty($_POST['recurrence_type']) ? $_POST['recurrence_type'] : null;
    $contact = !empty($_POST['contact']) ? $_POST['contact'] : null;
    $task_end_date = trim($_POST['task_end_date']);
    $weekly_value = trim($_POST['weekly_value']);
    $monthly_value = trim($_POST['monthly_value']);
    $quarterly_value = trim($_POST['quarterly_value']);
    $yearly_value = trim($_POST['yearly_value']);

    // Prevent saving tasks for Saturdays
    $dayOfWeek = date('l', strtotime($task_date)); // Check the day of the task date
    if ($dayOfWeek == 'Saturday') {
        $errors[] = "Tasks cannot be added on Saturdays.";
    }



    
    $creation_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone
    $update_on = date('Y-m-d H:i:s'); // Get current date and time in Asia/Kolkata timezone

    // If no errors, process the form
    if (empty($errors)) {
    if ($is_edit) {
        // Update existing entry
        $sql = "UPDATE tasks SET task_date='$task_date', task_time='$task_time', username='$username', assign_by='$assign_by', task='$task', contact_to='$contact_to', contact_no='$contact_no', priority='$priority', report_to='$report_to', shift_task='$shift_task',recurrence_type='$recurrence_type',contact='$contact',task_end_date='$task_end_date', weekly_value='$weekly_value', monthly_value='$monthly_value',quarterly_value='$quarterly_value',yearly_value='$yearly_value' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: todo");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } 
    else {
        // Create unique submission identifier FIRST
        $submission_hash = md5($task_date . $task_time . $username . $task . $contact_no);
        
        // Check if this submission was already processed in current session
        if (isset($_SESSION['tasks_processed_submissions'][$submission_hash])) {
            header("Location: todo?success=1");
            exit();
        }

        // Check for duplicate entry in database FIRST
        $check_duplicate = "SELECT id FROM tasks WHERE task_date = '$task_date' AND task_time = '$task_time' AND username = '$username' AND task = '$task' AND contact_no = '$contact_no'";
        $result = $conn->query($check_duplicate);

        if ($result->num_rows > 0) {
            // Mark as processed and redirect silently
            $_SESSION['tasks_processed_submissions'][$submission_hash] = true;
            header("Location: todo?success=1");
            exit();
        }

        // enter manualy car type
        $username = $_POST['username'];
        $custom_username = strtoupper($_POST['custom_username']) ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($username == "Enter Manually") {
            $username = $custom_username;
        }

        // enter manualy car type
        $assign_by = $_POST['assign_by'];
        $custom_assign_by = strtoupper($_POST['custom_assign_by']) ?? '';
        
        // If 'Enter Manually' is selected, use the custom type
        if ($assign_by == "Enter Manually") {
            $assign_by = $custom_assign_by;
        }

        // add task
        $sql = "INSERT INTO tasks (task_date, task_time, username, assign_by, task, contact_to, contact_no, priority, report_to, shift_task, recurrence_type, contact, task_end_date, weekly_value, monthly_value, quarterly_value, yearly_value) 
                VALUES ('$task_date', '$task_time', '$username', '$assign_by', '$task', '$contact_to', '$contact_no', '$priority', '$report_to', '$shift_task', '$recurrence_type', '$contact', '$task_end_date', '$weekly_value', '$monthly_value', '$quarterly_value', '$yearly_value')";
        
        if ($conn->query($sql) === TRUE) {
            // Mark this submission as processed
            $_SESSION['tasks_processed_submissions'][$submission_hash] = true;
            
            $last_id = $conn->insert_id;
            $_SESSION['last_submission'] = $last_id;
            $_SESSION['submission_time'] = time();
            
            header("Location: todo?success=1&id=" . $last_id);
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } 
}
}

?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>To-Do List</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="todo">To-Do List</a></li>
                <li class="breadcrumb-item active" aria-current="page">TO-DO Form</li>
              </ol>
            </nav>
        </div>
            
        <form 
            action="todo-form.php<?php 
                if ($is_edit) {
                    echo '?action=edit&id=' . $id; 
                }  ?>" 
            method="POST" class="p-5 shadow bg-white">

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
                <!-- Show Error Message -->
                <?php if (!empty($errors)): ?>
                    <div style="color: red;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <div class="row">

                <div class="col-md-6 field">
                    <label for="interval">Date :</label>
                    <input type="date" name="task_date" id="task_date" class="form-control" 
                        <?php if (!$is_edit) { ?> min="<?= date('Y-m-d') ?>" <?php } ?> 
                        value="<?php 
                            if ($is_edit) {
                                echo htmlspecialchars(date('Y-m-d', strtotime($task_date))); // Show existing date in edit mode
                            } else {
                                echo date('Y-m-d'); // Show current date for adding a new entry
                            } 
                        ?>" 
                    >
                    <small class="text-danger">*Avoid Saturday</small>
                </div>

                

                    <div class="col-md-6 field">
                        <label for="interval">Time :</label>
                        <input type="time" name="task_time" class="form-control" value="<?php 
                        if ($is_edit) {
                            echo htmlspecialchars(date('H:i', strtotime($task_time))); // Show existing time in edit mode
                        } else {
                            echo date('H:i'); // Show current time for adding a new entry
                        } 
                    ?>" >
                    </div>


                <div class="col-md-4 field position-relative">
                    <label for="mvnumber" class="form-label">Task Assign To :</label>
                    <div class="d-flex align-items-center">
                        <select class="form-select" name="username" id="assign_to" onchange="fetchContactNumber(this.value)">
                            <option value="" <?php echo (!$is_edit || empty($username)) ? 'selected' : ''; ?> >Select Task Assign To</option>
                            <?php
                            $query = "SELECT DISTINCT username FROM assign_to";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($is_edit && isset($username) && $username == $row['username']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['username']) . "' $selected>" . htmlspecialchars($row['username']) . "</option>";
                            }
                            ?>
                        </select>


                        <a href="assignto_add" type="button" class="btn btn-success ms-2 p-0">
                            Add
                        </a>

                        <a type="button" class="btn btn-info ms-2 p-0" onclick="editDropdown()">
                            Edit
                        </a>

                        

                    </div>
                </div>

                <div class="col-md-4 field mt-3">
                    <label for="interval">Contact :</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($contact); ?>" id="contact" class="form-control" placeholder="Enter Contact" readonly>
                </div>

                <div class="col-md-4 field position-relative">
                    <label for="mvnumber" class="form-label">Task Assign By :</label>
                    <div class="d-flex align-items-center">
                        <select class="form-select" name="assign_by" id="assignBy">
                            <option value="" <?php echo (!$is_edit || empty($assign_by)) ? 'selected' : ''; ?> >Select Task Assign By</option>
                            <?php
                            // Query to fetch distinct username
                            $query = "SELECT DISTINCT id, assign_by FROM assignby";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                // If editing, select the current MV number
                                $selected = ($is_edit && isset($assign_by) && $assign_by == $row['assign_by']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['assign_by']) . "' $selected>" . htmlspecialchars($row['assign_by']) . "</option>";
                            }
                            ?>
                        </select>

                        <a href="assignby_add" type="button" class="btn btn-success ms-2 p-0">
                            Add
                        </a>

                        <a type="button" class="btn btn-info ms-2 p-0" onclick="editAssignByDropdown()">
                            Edit
                        </a>

                        

                    </div>
                </div>


                    <div class="col-md-6 field">
                        <label for="interval">Task To Complete :</label>
                        <textarea type="text" name="task" class="form-control" placeholder="Description Of Task"><?php echo htmlspecialchars($task); ?></textarea>
                    </div>

                   


                    <div class="col-md-6 field">
                        <label for="recurrence_type" class="form-label">Task Type:</label>
                        <select class="form-select" name="recurrence_type" id="recurrence_type" onchange="updateRecurrenceFields()">
                            <option value="None" <?php echo ($is_edit) && $recurrence_type == 'None' ? 'selected' : ''; ?>>None</option>
                            <option value="Daily" <?php echo ($is_edit) && $recurrence_type == 'Daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="Weekly" <?php echo ($is_edit) && $recurrence_type == 'Weekly' ? 'selected' : ''; ?>>Weekly</option>
                            <option value="Monthly" <?php echo ($is_edit) && $recurrence_type == 'Monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="Quarterly" <?php echo ($is_edit) && $recurrence_type == 'Quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                            <option value="Yearly" <?php echo ($is_edit) && $recurrence_type == 'Yearly' ? 'selected' : ''; ?>>Yearly</option>
                            
                        </select>
                    </div>

                    <!-- All recurrence value fields (initially hidden) -->
                    <div id="recurrence_fields_wrapper" class="row">
                        <!-- Weekly Field -->
                        <div class="col-md-6 field recurrence-field" id="weekly_field" style="display: none;">
                            <label for="weekly_value" class="form-label">Day of Week:</label>
                            <select class="form-select" name="weekly_value" id="weekly_value">
                                <option value="">Select a day</option>
                                <option value="Monday" <?php echo ($is_edit) && $weekly_value == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                                <option value="Tuesday" <?php echo ($is_edit) && $weekly_value == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                                <option value="Wednesday" <?php echo ($is_edit) && $weekly_value == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                                <option value="Thursday" <?php echo ($is_edit) && $weekly_value == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                                <option value="Friday" <?php echo ($is_edit) && $weekly_value == 'Friday' ? 'selected' : ''; ?>>Friday</option>
                                <option value="Saturday" <?php echo ($is_edit) && $weekly_value == 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                                <option value="Sunday" <?php echo ($is_edit) && $weekly_value == 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                            </select>
                        </div>

                        <!-- Monthly Field -->
                        <div class="col-md-6 field recurrence-field" id="monthly_field" style="display: none;">
                            <label for="monthly_value" class="form-label">Day of Month (1-31):</label>
                            <input type="number" class="form-control" name="monthly_value" id="monthly_value" 
                                min="1" max="31" value="<?php echo htmlspecialchars($monthly_value); ?>">
                        </div>

                        <!-- Quarterly Field -->
                        <div class="col-md-6 field recurrence-field" id="quarterly_field" style="display: none;">
                            <label for="quarterly_value" class="form-label">Quarter and Day:</label>
                            <select class="form-select" name="quarterly_value" id="quarterly_value">
                                <option value="">Select a quarter</option>
                                <option value="1-1" <?php echo ($is_edit) && $quarterly_value == '1-1' ? 'selected' : ''; ?>>Q1, Day 1</option>
                                <option value="1-15" <?php echo ($is_edit) && $quarterly_value == '1-15' ? 'selected' : ''; ?>>Q1, Day 15</option>
                                <option value="2-1" <?php echo ($is_edit) && $quarterly_value == '2-1' ? 'selected' : ''; ?>>Q2, Day 1</option>
                                <option value="2-15" <?php echo ($is_edit) && $quarterly_value == '2-15' ? 'selected' : ''; ?>>Q2, Day 15</option>
                                <option value="3-1" <?php echo ($is_edit) && $quarterly_value == '3-1' ? 'selected' : ''; ?>>Q3, Day 1</option>
                                <option value="3-15" <?php echo ($is_edit) && $quarterly_value == '3-15' ? 'selected' : ''; ?>>Q3, Day 15</option>
                                <option value="4-1" <?php echo ($is_edit) && $quarterly_value == '4-1' ? 'selected' : ''; ?>>Q4, Day 1</option>
                                <option value="4-15" <?php echo ($is_edit) && $quarterly_value == '4-15' ? 'selected' : ''; ?>>Q4, Day 15</option>
                            </select>
                        </div>

                        <!-- Yearly Field -->
                        <div class="col-md-6 field recurrence-field" id="yearly_field" style="display: none;">
                            <label for="yearly_value" class="form-label">Date (MM-DD):</label>
                            <input type="text" class="form-control" name="yearly_value" id="yearly_value" 
                                placeholder="MM-DD" pattern="\d{2}-\d{2}" 
                                value="<?php echo htmlspecialchars($yearly_value); ?>">
                        </div>

                        <!-- End Date Field -->
        


                        <div class="col-md-6 field" id="end_date_field" style="display: none;">
                            <label for="task_end_date" class="form-label">End Task Date :</label>
                            <input type="date" name="task_end_date" id="task_end_date" class="form-control" 
                            <?php if (!$is_edit) { ?> min="<?= date('Y-m-d') ?>" <?php } ?> 
                                value="<?php 
                                    if ($is_edit) {
                                        echo htmlspecialchars(date('Y-m-d', strtotime($task_end_date))); // Show existing date in edit mode
                                    } 
                                ?>" 
                            >
                            
                        </div>

                    </div>




                    <div class="col-md-6 field mt-3">
                        <label for="interval">Contact To :</label>
                        <input type="text" name="contact_to" value="<?php echo htmlspecialchars($contact_to); ?>" class="form-control" placeholder="Enter Contact To" >
                    </div>

                    <div class="col-md-6 field mt-3">
                        <label for="interval">Mobile No :</label>
                        <input type="text" name="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" class="form-control" placeholder="Enter Mobile No" >
                    </div>
                    
                    

                    <div class="col-md-6 field mt-3">
                        <label for="Priority" class="form-label">Priority :</label>
                        <select class="form-select" name="priority" id="motorSubType" >
                            <option value="HIGH" <?php if ($is_edit && $priority == 'HIGH') echo 'selected'; ?>>HIGH</option>
                            <option value="MEDIUM" <?php if ($is_edit && $priority == 'MEDIUM') echo 'selected'; ?>>MEDIUM</option>
                            <option value="LOW" <?php if ($is_edit && $priority == 'LOW') echo 'selected'; ?>>LOW</option>
                        </select>
                    </div>

                    <div class="col-md-6 field mt-3">
                        <label for="interval">Report To :</label>
                        <input type="text" name="report_to" value="<?php echo htmlspecialchars($report_to); ?>" class="form-control" placeholder="Report To" >
                    </div>

                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" value="RESHEDULED" name="shift_task" id="flexRadioDefault2" >
                        <label class="form-check-label" for="flexRadioDefault2">
                            Reschedule Task
                        </label>
                    </div>

                </div>
                <input type="submit" class="btn sub-btn" value="<?php echo $is_edit ? 'Update Task' : 'Add Task'; ?>"> 
            </form>
        <div>
    </div>
</section>

<script>

// script for reccuring tasks

function updateRecurrenceFields() {
    const type = document.getElementById('recurrence_type').value;

    // Hide all fields
    document.querySelectorAll('.recurrence-field').forEach(el => el.style.display = 'none');
    document.getElementById('end_date_field').style.display = (type !== 'None') ? 'block' : 'none';

    switch (type) {
        case 'Weekly':
            document.getElementById('weekly_field').style.display = 'block';
            break;
        case 'Monthly':
            document.getElementById('monthly_field').style.display = 'block';
            break;
        case 'Quarterly':
            document.getElementById('quarterly_field').style.display = 'block';
            break;
        case 'Yearly':
            document.getElementById('yearly_field').style.display = 'block';
            break;
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', updateRecurrenceFields);
</script>

<script>

// This function is called when the 'Edit' button is clicked for Assign To.
function editDropdown() {
        const select = document.getElementById('assign_to');
        const selectedId = select.value;

        if (!selectedId) {
            alert("Please select an Assign To Name to edit.");
            return;
        }

        // Redirect to the edit form with the selected ID
        window.location.href = `assignto_add?action=edit&username=${selectedId}`;
    }


// Function to fetch property details based on the selected Milkat ID
function fetchContactNumber(username) {
    if (username !== "") {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "fetch_contact_details.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById("contact").value = xhr.responseText;
            }
        };
        xhr.send("username=" + encodeURIComponent(username)); // Fix the parameter name
    } else {
        document.getElementById("contact").value = "";
    }
}


// This function is called when the 'Edit' button is clicked for Assign By.
function editAssignByDropdown() {
        const select = document.getElementById('assignBy');
        const selectedId = select.value; 

        if (!selectedId) {
            alert("Please select an Assign By Name to edit.");
            return;
        }

        // Redirect to the edit form with the selected ID
        window.location.href = `assignby_add?action=edit&assign_by=${selectedId}`;
    }


    // Handle Username selection
function handleUsernameChange() {
    var carTypeSelect = document.getElementById('username');
    var customCarTypeInput = document.getElementById('custom_username');

    // Show the manual input field if "Enter Manually" is selected
    if (carTypeSelect.value === 'Enter Manually') {
        customCarTypeInput.style.display = 'block';
        customCarTypeInput.name = 'username'; // Set the custom input to the same name as the dropdown
    } else {
        customCarTypeInput.style.display = 'none';
        customCarTypeInput.name = 'custom_username'; // Reset to avoid conflicts
    }
}

// Handle Assign By selection
function handleAssignByChange() {
    var carTypeSelect = document.getElementById('assign_by');
    var customCarTypeInput = document.getElementById('custom_assign_by');

    // Show the manual input field if "Enter Manually" is selected
    if (carTypeSelect.value === 'Enter Manually') {
        customCarTypeInput.style.display = 'block';
        customCarTypeInput.name = 'assign_by'; // Set the custom input to the same name as the dropdown
    } else {
        customCarTypeInput.style.display = 'none';
        customCarTypeInput.name = 'custom_assign_by'; // Reset to avoid conflicts
    }
}


  


</script>















<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>