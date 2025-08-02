<?php 
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
include 'includes/db_conn.php';

// Handle Task Status Update
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $newStatus = $conn->real_escape_string($_POST['status']);

    if ($conn->query("UPDATE tasks SET status='$newStatus' WHERE id=$id")) {
        $_SESSION['message'] = "Task status updated successfully.";
    } else {
        echo "Error updating task: " . $conn->error;
    }
}

// Handle Task Deletion with Password Check
define('DELETE_PASSWORD', 'IM@BYP');
if (isset($_POST['confirm_delete'])) {
    if ($_POST['password'] === DELETE_PASSWORD) {
        $id = (int)$_POST['id'];
        if ($conn->query("DELETE FROM tasks WHERE id=$id") === TRUE) {
            $_SESSION['message'] = "Task deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting task: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Invalid password. Task not deleted.";
    }
    header("Location: todo.php");
    exit();
}

// Get Today's Date
// Initialize base values
$todaysDate = date('Y-m-d');
$todaysDay = date('l'); // 1 (Mon) to 7 (Sun)
$todaysMonthDay = date('d');
$todaysMonth = date('m');
$todaysYear = date('Y');
$todaysMD = date('m-d');
$quarter = ceil($todaysMonth / 3);
$quarterly_value_check = $quarter . '-' . (int)$todaysMonthDay;

// Initialize base query
$query = "SELECT * FROM tasks WHERE 1=1";
$params = [];
$paramTypes = "";

// Date range filter
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    $endDay = date('l', strtotime($endDate));
    $endMonthDay = (int)date('d', strtotime($endDate));
    $endMonth = date('m', strtotime($endDate));
    $endYear = date('Y', strtotime($endDate));
    $endMD = date('m-d', strtotime($endDate));
    $endQuarter = ceil($endMonth / 3);
    $endQuarterlyValue = $endQuarter . '-' . $endMonthDay;

    $query .= " AND (
        (task_date BETWEEN ? AND ?)
        OR (recurrence_type = 'Daily' AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Weekly' AND weekly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Monthly' AND monthly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Quarterly' AND quarterly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Yearly' AND yearly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
    )";

    $params = [
        $startDate, $endDate,
        $endDate, // Daily
        $endDay, $endDate, // weekly
        $endMonthDay, $endDate, // monthly
        $endQuarterlyValue, $endDate, // quarterly
        $endMD, $endDate // yearly
    ];
    $paramTypes = "sssssssssss";
}
 else {
    // No date range, apply today's filter and recurrence logic
    $query .= " AND (task_date = ? 
        OR (recurrence_type = 'Daily' AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Weekly' AND weekly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Monthly' AND monthly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Quarterly' AND quarterly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
        OR (recurrence_type = 'Yearly' AND yearly_value = ? AND (task_end_date IS NULL OR task_end_date >= ?))
    )";
    $params[] = $todaysDate;
    $params[] = $todaysDate;
    $params[] = $todaysDay;
    $params[] = $todaysDate;
    $params[] = (int)$todaysMonthDay;
    $params[] = $todaysDate;
    $params[] = $quarterly_value_check;
    $params[] = $todaysDate;
    $params[] = $todaysMD;
    $params[] = $todaysDate;
    $paramTypes .= "ssssssssss";
}

// Recurrence type filter
if (!empty($_GET['recurrence_type'])) {
    $query .= " AND recurrence_type = ?";
    $params[] = $_GET['recurrence_type'];
    $paramTypes .= "s";
}

// Recurrence value filters
switch ($_GET['recurrence_type'] ?? '') {
    case 'Weekly':
        if (!empty($_GET['weekly_value'])) {
            $query .= " AND weekly_value = ?";
            $params[] = $_GET['weekly_value'];
            $paramTypes .= "s";
        }
        break;
    case 'Monthly':
        if (!empty($_GET['monthly_value'])) {
            $query .= " AND monthly_value = ?";
            $params[] = $_GET['monthly_value'];
            $paramTypes .= "s";
        }
        break;
    case 'Quarterly':
        if (!empty($_GET['quarterly_value'])) {
            $query .= " AND quarterly_value = ?";
            $params[] = $_GET['quarterly_value'];
            $paramTypes .= "s";
        }
        break;
    case 'Yearly':
        if (!empty($_GET['yearly_value'])) {
            $query .= " AND yearly_value = ?";
            $params[] = $_GET['yearly_value'];
            $paramTypes .= "s";
        }
        break;
}

// Execute query
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $entries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Query preparation failed: " . $conn->error);
}



if (isset($_POST['generate_csv'])) {
    // Get the submitted password
    $admin_password = $_POST['admin_password'] ?? '';

    // Fetch the stored hashed password from the database
    $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1"; // Assuming only one admin password
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        // Verify the password
        if (password_verify($admin_password, $hashed_password)) {
            // Clear output buffer to avoid unwanted HTML
            if (ob_get_length()) {
                ob_end_clean();
            }

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Task-Report.csv');

            // Open the output stream
            $output = fopen('php://output', 'w');

            // Add CSV header row
            fputcsv($output, ['Assign To', 'Task', 'Contact', 'Assigned By', 'Date', 'Task End Date', 'Contact To', 'Contact No', 'Report To','Priority','Status','Task Type']);

            // Fetch data from the database
            $sql = "SELECT * FROM tasks";
            if (!empty($start_date) && !empty($end_date)) {
                $sql .= " WHERE task_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date);
            } else {
                $stmt = $conn->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            // Write rows to the CSV
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $row['username'],
                        $row['task'],
                        $row['contact'],
                        $row['assign_by'],
                        (new DateTime($row['task_date']))->format('d/m/Y'),
                        (new DateTime($row['task_end_date']))->format('d/m/Y'),
                        $row['contact_to'],
                        $row['contact_no'],
                        $row['report_to'],
                        $row['priority'],
                        $row['status'],
                        $row['recurrence_type'],
                    ]);
                }
            } else {
                fputcsv($output, ['No records found']);
            }

            // Close the output stream
            fclose($output);
            
            // Exit to prevent further execution
            exit();
        } else {
            // Incorrect password
            echo "<script>
                    alert('Incorrect password. Please try again.');
                    window.history.back();
                </script>";
            exit();
        }
    } else {
        // No password stored
        echo "<script>
                alert('Admin password is not set. Please contact support.');
                window.history.back();
            </script>";
        exit();
    }
}


?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5">
        <div class="ps-5">
            <h1>To-Do List</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">To-Do List</li>
                </ol>
            </nav>
        </div>

        <div class="bg-white con-tbl p-5">
            <!-- Display Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="GET" action="">
    <div class="row">
        <!-- Date Range Filter -->
        <div class="col-md-3 field">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? $todaysDate ?>">
        </div>
        <div class="col-md-3 field">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? $todaysDate ?>">
        </div>

        <!-- Recurrence Type Filter -->
        <div class="col-md-3 field">
            <label for="recurrence_type">Recurrence Type:</label>
            <select name="recurrence_type" id="recurrence_type" class="form-select" onchange="toggleRecurrenceFields()">
                <option value="">All</option>
                <option value="Daily" <?= ($_GET['recurrence_type'] ?? '') == 'Daily' ? 'selected' : '' ?>>Daily</option>
                <option value="Weekly" <?= ($_GET['recurrence_type'] ?? '') == 'Weekly' ? 'selected' : '' ?>>Weekly</option>
                <option value="Monthly" <?= ($_GET['recurrence_type'] ?? '') == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                <option value="Quarterly" <?= ($_GET['recurrence_type'] ?? '') == 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                <option value="Yearly" <?= ($_GET['recurrence_type'] ?? '') == 'Yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
        </div>

        <!-- Recurrence Value Fields -->
        <div class="col-md-3 field" id="weekly_field" style="display: none;">
            <label>Day of Week:</label>
            <select name="weekly_value" class="form-select">
                <option value="">Select</option>
                <option value="Monday" <?= ($_GET['weekly_value'] ?? '') == 'Monday' ? 'selected' : '' ?>>Monday</option>
                <option value="Tuesday" <?= ($_GET['weekly_value'] ?? '') == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                <option value="Wednesday" <?= ($_GET['weekly_value'] ?? '') == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                <option value="Thursday" <?= ($_GET['weekly_value'] ?? '') == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                <option value="Friday" <?= ($_GET['weekly_value'] ?? '') == 'Friday' ? 'selected' : '' ?>>Friday</option>
                <option value="Saturday" <?= ($_GET['weekly_value'] ?? '') == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                <option value="Sunday" <?= ($_GET['weekly_value'] ?? '') == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
            </select>
        </div>

        <div class="col-md-3 field" id="monthly_field" style="display: none;">
            <label>Day of Month:</label>
            <input type="number" name="monthly_value" class="form-control" min="1" max="31" value="<?= $_GET['monthly_value'] ?? '' ?>">
        </div>

        <div class="col-md-3 field" id="quarterly_field" style="display: none;">
            <label>Quarter and Day:</label>
            <select name="quarterly_value" class="form-select">
                <option value="">Select</option>
                <option value="1-1" <?= ($_GET['quarterly_value'] ?? '') == '1-1' ? 'selected' : '' ?>>Q1 Day 1</option>
                <option value="1-15" <?= ($_GET['quarterly_value'] ?? '') == '1-15' ? 'selected' : '' ?>>Q1 Day 15</option>
                <option value="2-1" <?= ($_GET['quarterly_value'] ?? '') == '2-1' ? 'selected' : '' ?>>Q2 Day 1</option>
                <option value="2-15" <?= ($_GET['quarterly_value'] ?? '') == '2-15' ? 'selected' : '' ?>>Q2 Day 15</option>
                <option value="3-1" <?= ($_GET['quarterly_value'] ?? '') == '3-1' ? 'selected' : '' ?>>Q3 Day 1</option>
                <option value="3-15" <?= ($_GET['quarterly_value'] ?? '') == '3-15' ? 'selected' : '' ?>>Q3 Day 15</option>
                <option value="4-1" <?= ($_GET['quarterly_value'] ?? '') == '4-1' ? 'selected' : '' ?>>Q4 Day 1</option>
                <option value="4-15" <?= ($_GET['quarterly_value'] ?? '') == '4-15' ? 'selected' : '' ?>>Q4 Day 15</option>
            </select>
        </div>

        <div class="col-md-3 field" id="yearly_field" style="display: none;">
            <label>MM-DD:</label>
            <input type="text" name="yearly_value" class="form-control" placeholder="MM-DD" pattern="\d{2}-\d{2}" value="<?= $_GET['yearly_value'] ?? '' ?>">
        </div>

        <!-- Submit -->
        <div class="col-md-4 mt-3">
            <button type="submit" class="btn sub-btn">Search</button>
        </div>
    </div>
</form>
<script>
function toggleRecurrenceFields() {
    var type = document.getElementById("recurrence_type").value;

    // Hide all recurrence value fields
    document.getElementById("weekly_field").style.display = "none";
    document.getElementById("monthly_field").style.display = "none";
    document.getElementById("quarterly_field").style.display = "none";
    document.getElementById("yearly_field").style.display = "none";

    // Show only the relevant one
    if (type === "Weekly") document.getElementById("weekly_field").style.display = "block";
    if (type === "Monthly") document.getElementById("monthly_field").style.display = "block";
    if (type === "Quarterly") document.getElementById("quarterly_field").style.display = "block";
    if (type === "Yearly") document.getElementById("yearly_field").style.display = "block";
}

// Run on page load (in case values are already selected)
window.onload = toggleRecurrenceFields;
</script>



            <!-- Add Task Button -->
            <a href="todo-form.php" class="btn sub-btn1 mt-3">Add Task</a>

            <div id="reportSection">

            <div class="heading">
                <?php
                 
                    $formatted_start_date = date("d/m/Y", strtotime($startDate));
                    $formatted_end_date = date("d/m/Y", strtotime($endDate));
                    echo "<h1 class='text-center'>TODO TASK REPORT FOR $formatted_start_date TO $formatted_end_date </h1>"
                    
                ?>
            </div>

            <!-- Task Table -->
            <table class="table mt-4">
                <thead>
                    <tr>
                        
                        <th>Assign To</th>
                        <th>Task</th>
                        <th>Contact</th>
                        <th>Assigned By</th>
                        <th>Date & Time</th>
                        <th>Task End Date</th>
                        <th>Contact To</th>
                        <th>Contact No</th>
                        <th>Report To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Task Type</th>
                        <th>Task Value</th>
                        <th class="action-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($entries)): ?>
                        <?php
                        // Group tasks by username
                        $groupedTasks = [];
                        foreach ($entries as $row) {
                            $groupedTasks[$row['username']][] = $row;
                        }

                        // Loop through each user and create a separate table
                        foreach ($groupedTasks as $username => $tasks): ?>
                            <tr>
                                <td colspan="12" class="text-center"><strong><?= htmlspecialchars($username) ?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                            <th>Assign To</th>
                                            <th>Task</th>
                                            <th>Contact</th>
                                            <th>Assigned By</th>
                                            <th>Date & Time</th>
                                            <th>Task End Date</th>
                                            <th>Contact To</th>
                                            <th>Contact No</th>
                                            <th>Report To</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Task Type</th>
                                            <th>Task Value</th>
                                            <th colspan="2" class="action-col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tasks as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                                    <td><?= htmlspecialchars($row['task']) ?></td>
                                                    <td><?= htmlspecialchars($row['contact']) ?></td>
                                                    <td><?= htmlspecialchars($row['assign_by']) ?></td>
                                                    <td><?= date('d/m/Y h:i A', strtotime($row['task_date'] . ' ' . $row['task_time'])) ?></td>
                                                    <td>
                                                        <?php
                                                        if (!empty($row['task_end_date']) && $row['task_end_date'] !== '0000-00-00') {
                                                            echo date("d/m/Y", strtotime($row['task_end_date'])) . "<br>";
                                                        }
                                                        ?>
                                                    </td>

                                                    <td><?= htmlspecialchars($row['contact_to']) ?></td>
                                                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                                                    <td><?= htmlspecialchars($row['report_to']) ?></td>
                                                    <td><?= htmlspecialchars($row['priority']) ?></td>
                                                    <td><?= ucfirst($row['status']) ?></td>
                                                    <td><?= htmlspecialchars($row['recurrence_type']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($row['weekly_value']) ?>
                                                        <?= htmlspecialchars($row['monthly_value']) ?>
                                                        <?= htmlspecialchars($row['quarterly_value']) ?>
                                                        <?= htmlspecialchars($row['yearly_value']) ?>
                                                    </td>
                                                    <td class="action-col">
                                                        <!-- Buttons to change status -->
                                                        <?php if ($row['status'] != 'complete'): ?>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="status" value="PENDING">
                                                                <button type="submit" name="update_status" class="btn btn-warning btn-sm">PENDING</button>
                                                            </form>
                                                            <br>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="status" value="WIP">
                                                                <button type="submit" name="update_status" class="btn btn-info btn-sm">WIP</button>
                                                            </form>
                                                            <br> 
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="status" value="COMPLETED">
                                                                <button type="submit" name="update_status" class="btn btn-success btn-sm">COMPLETED</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-col">
                                                        <a href="todo-form.php?action=edit&id=<?= $row['id']; ?>" class="text-dark">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        &nbsp;/&nbsp;
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $row['id']; ?>" class="text-dark">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="13" class="text-center">No tasks found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
                </div>
        </div>
    </div>
</section>

<!-- Password verification Modal for print screen -->
<div class="modal fade" id="printpasswordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For Print</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="password" id="passwordInput" class="form-control" placeholder="Enter password" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="validatePassword()">Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="todo.php">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Enter the password to confirm deletion:</p>
                    <input type="hidden" name="id" id="deleteId">
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Excel Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For EXCEL Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="csvDownloadForm" method="post">
        <div class="modal-body">
          <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="generate_csv" value="generate_csv" class="btn btn-primary" id="downloadButton">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>

// Show the password modal when the button is clicked
function showPasswordModal() {
        $('#printpasswordModal').modal('show');
    }

    // Validate the password entered
    async function validatePassword() {
        const userPassword = document.getElementById('passwordInput').value;

        if (!userPassword) {
            alert("Password is required.");
            return;
        }

        // Validate the entered password with the backend
        const validationResult = await validatePasswordOnServer(userPassword);

        if (validationResult.success) {
            // Password is correct, proceed with print
            window.print();
            $('#printpasswordModal').modal('hide'); // Close the modal
        } else {
            // Show error message if the password is incorrect
            alert(validationResult.error || "Incorrect password!");
        }
    }

    // Function to send password to server for validation
    async function validatePasswordOnServer(userPassword) {
        try {
            const response = await fetch('print_pass.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `password=${encodeURIComponent(userPassword)}`
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("Error validating password:", error);
            return { success: false, error: "Error validating password" };
        }
    }


// Excel download Close modal and refresh page when "Download" button is clicked
document.getElementById('downloadButton').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });



document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('deleteModal').addEventListener('show.bs.modal', event => {
        document.getElementById('deleteId').value = event.relatedTarget.getAttribute('data-id');
    });
});
</script>

<?php include 'include/footer.php'; ?>
