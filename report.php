<?php 
    include 'session_check.php';
?> 


<?php

// Database connection 
include 'includes/db_conn.php';

// Initialize report variable
$report = [];
$total_adv_amount = 0; // To store the total advance amount
$total_recov_amount = 0; // To store the total recovery amount
$total_entries_count = 0;
// $entry_counts = []; // To store entry counts for each department

// Check if the form is submitted
if (isset($_POST['generate_report']) || isset($_POST['generate_csv']) || isset($_POST['generate_pdf'])) {

    // Get the form inputs
    $department = $_POST['department'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $search_query = $_POST['search_query'];
    $status = $_POST['status'];
    $sort_option = $_POST['sort'] ?? 'date_desc'; // Default sorting by date descending

    // Initialize the query variable
    $query = "";

    // Handle search by fields (client_name or contact)
    // Check if search query is set and not empty
    $search_condition = "";
    if (!empty($search_query)) {
        // Convert comma-separated values into an array and trim spaces
        $search_terms = explode(',', $search_query);
        $search_terms = array_map('trim', $search_terms);

        // Prepare an array to store SQL conditions
        $conditions = [];

        foreach ($search_terms as $term) {
            $conditions[] = "(client_name LIKE '%$term%' OR contact LIKE '%$term%')";
        }

        // Join conditions using OR
        if (!empty($conditions)) {
            $search_condition = " AND (" . implode(" OR ", $conditions) . ")";
        }
    }   


    // Handle search by status (Recovery, Complete, etc.)
    $status_condition = "";
    if ($status === "Recovery") {
        // Condition for recovery: recov_amount is not zero
        $status_condition = " AND recov_amount != 0";
    } elseif ($status != "All") {
        // Condition for other statuses (Complete, Pending, etc.)
        $status_condition = " AND form_status = '$status'";
    }

    // Sorting options
    $order_by = "";
    switch ($sort_option) {
        case 'date_asc':
            $order_by = " ORDER BY policy_date ASC";
            break;
        case 'date_desc':
            $order_by = " ORDER BY policy_date DESC";
            break;
        // case 'reg_num_asc':
        //     $order_by = " ORDER BY reg_num ASC";
        //     break;
        // case 'reg_num_desc':
        //     $order_by = " ORDER BY reg_num DESC";
            // break;
    }

    // Handle date range and department specific query
    switch ($department) {
        case 'BMDS':
            $table_name = "bmds_entries";
            $query = "SELECT reg_num, policy_date, client_name, contact, address, car_type, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, NULL AS mv_no, NULL AS collection_job,NULL AS work_status,NULL AS mf_option,NULL AS insurance_option,NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status,bmds_type,llr_type,mdl_type, 'BMDS' AS department 
                    FROM $table_name 
                    WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition";
            break;
    
        case 'GIC':
            $table_name = "gic_entries";
            $query = "SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type,nonmotor_type_select,nonmotor_subtype_select,policy_company, mv_number,policy_type, vehicle, NULL AS job_type, NULL AS mv_no, NULL AS collection_job,NULL AS work_status, adv_amount AS policy_amt,NULL AS mf_option,NULL AS insurance_option,NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'GIC' AS department 
                    FROM $table_name 
                    WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition";
            break;
             
        case 'LIC':
            $table_name = "lic_entries";
            $query = "SELECT reg_num, policy_date, client_name,policy_num,colle_policy_num, contact, address,collection_job,work_status, NULL AS car_type, NULL AS mv_number, NULL AS vehicle, job_type, NULL AS mv_no, NULL AS mf_option,NULL AS insurance_option,NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'LIC' AS department 
                    FROM $table_name 
                    WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition";
            break;
    
        case 'R/JOB':
            $table_name = "rto_entries";
            $query = "SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, mv_no AS mv_number, NULL AS vehicle, NULL AS job_type, mv_no,  adv_amount AS policy_amt,NULL AS collection_job,NULL AS work_status,NULL AS mf_option,NULL AS insurance_option,NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,dl_type_work, tr_type_work, nt_type_work, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'R/JOB' AS department 
                    FROM $table_name 
                    WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition";
            break;
    
        case 'MF':
            $table_name = "mf_entries";
            $query = "SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type,NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job, mf_option ,insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, amount AS policy_amt, 0 AS recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'MF' AS department 
                FROM mf_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition";
            break;
    
            case 'All':
    if ($status === "Recovery") {
        // Include only tables with valid recov_amount for Recovery status
        $query = "(SELECT reg_num, policy_date, client_name, contact, address, car_type , NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status, bmds_type, llr_type, mdl_type, 'BMDS' AS department 
                FROM bmds_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition AND recov_amount != 0)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, nonmotor_type_select,nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, mv_number, vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'GIC' AS department 
                FROM gic_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition AND recov_amount != 0)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, mv_no, dl_type_work, tr_type_work, nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'R/JOB' AS department 
                FROM rto_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition AND recov_amount != 0)";
    } else {
        $query = "(SELECT reg_num, policy_date, client_name, contact, address, car_type , NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status, bmds_type, llr_type, mdl_type, 'BMDS' AS department 
                FROM bmds_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, nonmotor_type_select,nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, mv_number, vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'GIC' AS department 
                FROM gic_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job,NULL AS mf_option ,NULL AS insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, mv_no, dl_type_work, tr_type_work, nt_type_work, adv_amount AS policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'R/JOB' AS department 
                FROM rto_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select, work_status, collection_job, NULL AS mf_option ,NULL AS insurance_option, NULL AS mv_number, NULL AS vehicle, job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, policy_amt, recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'LIC' AS department 
                FROM lic_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition)
                UNION 
                (SELECT reg_num, policy_date, client_name, contact, address, NULL AS car_type, NULL AS nonmotor_type_select,NULL AS nonmotor_subtype_select,NULL AS work_status,NULL AS collection_job, mf_option ,insurance_option, NULL AS mv_number, NULL AS vehicle, NULL AS job_type, NULL AS mv_no, NULL AS dl_type_work,NULL AS tr_type_work,NULL AS nt_type_work, amount AS policy_amt, 0 AS recov_amount, username, form_status,NULL AS bmds_type,NULL AS llr_type,NULL AS mdl_type, 'MF' AS department 
                FROM mf_entries 
                WHERE is_deleted = 0 AND policy_date BETWEEN '$from_date' AND '$to_date' $search_condition $status_condition)";
    }
    break;

            
    }
    
        // Append sorting order
        $query .= $order_by;

    // Execute the query
   // Initialize total count for matching entries
        

        // Execute the main query to fetch data
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report[] = $row; // Store results in the report array
                $total_adv_amount += $row['policy_amt']; // Sum up policy amounts
                $total_recov_amount += $row['recov_amount']; // Sum up recovery amounts
            }

            // Get total count of matched entries for the selected filters
            $count_query = "SELECT COUNT(*) as total_count FROM ($query) AS sub_query";
            $count_result = $conn->query($count_query);
            if ($count_result && $count_row = $count_result->fetch_assoc()) {
                $total_entries_count = $count_row['total_count'];
            }
        } else {
            echo "No records found.";
        }




    
    
    
    // If user clicks on 'Generate CSV'
    if (isset($_POST['generate_csv'])) {
        $input_password = $_POST['password'];
    
        // Fetch hashed password from the database
        $sql = "SELECT password FROM file WHERE file_type = 'CSV' LIMIT 1";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['password'];
    
            // Verify password using password_verify()
            if (!password_verify($input_password, $stored_hashed_password)) {
                die("Error: Incorrect Password. Please go back and try again.");
            }
    
            
    
            // Ensure $report is not empty
            if (empty($report)) {
                die("No data available for export.");
            }
    
            // Set headers for CSV file download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=report.csv');
    
            // Open output stream
            $output = fopen('php://output', 'w');
    
            // Write column headers
            fputcsv($output, ['Reg Num', 'Date', 'Client Name', 'Contact', 'Address', 'Policy Amount', 'Recovery Amount', 'Status', 'Department']);
    
            // Write rows
            foreach ($report as $item) {
                fputcsv($output, [
                    $item['reg_num'], 
                    (new DateTime($item['policy_date']))->format('d/m/Y'),
                    $item['client_name'], 
                    $item['contact'], 
                    $item['address'], 
                    $item['policy_amt'], 
                    $item['recov_amount'], 
                    $item['form_status'], 
                    $item['department']
                ]);
            }
    
            fclose($output);
            exit();
        } else {
            die("Error: Password not found in the database.");
        }
    }
    
    
    // Close the connection
    $conn->close();
    
}
?>

<?php
    include 'include/header.php';
    include 'include/head.php'; 
    include 'include/navbar.php'; 
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    
    <div class="container data-table p-5">
        <div class="bg-white con-tbl p-5">
            <h1 class="text-center">Search/Generate Report</h1>

            <!-- Form and HTML code here -->
            <form method="POST" class="p-3">
            
                <?php
                // Retain form values after submit
                $from_date = $_POST['from_date'] ?? date('Y-m-01');
                $to_date = $_POST['to_date'] ?? date('Y-m-t');
                $department = $_POST['department'] ?? 'All';
                $status = $_POST['status'] ?? 'All';
                $search_query = $_POST['search_query'] ?? '';
                $sort = $_POST['sort'] ?? 'date_asc';
                ?>
            
                <div class="row">
            
                    <!-- Date Range Fields -->
                    <div class="mb-3 date-fields field col-md-2">
                        <label for="from_date" class="form-label">From</label>
                        <input type="date" name="from_date" class="form-control" id="from_date" value="<?= htmlspecialchars($from_date) ?>">
                    </div>
            
                    <div class="mb-3 date-fields field col-md-2">
                        <label for="to_date" class="form-label">To</label>
                        <input type="date" name="to_date" class="form-control" id="to_date" value="<?= htmlspecialchars($to_date) ?>">
                    </div>
            
                    <!-- Department Field -->
                    <div class="mb-3 field col-md-2">
                        <label for="department" class="form-label">Select Department</label>
                        <select class="form-select" name="department" id="department">
                            <?php
                            $departments = ['All', 'GIC', 'LIC', 'R/JOB', 'BMDS', 'MF'];
                            foreach ($departments as $dept) {
                                $selected = ($department == $dept) ? 'selected' : '';
                                echo "<option value=\"$dept\" $selected>$dept</option>";
                            }
                            ?>
                        </select>
                    </div>
            
                    <!-- Status Dropdown -->
                    <div class="mb-3 field col-md-2">
                        <label for="status" class="form-label">Select Status</label>
                        <select class="form-select" name="status" id="status">
                            <?php
                            $statuses = ['All', 'Complete', 'Pending', 'Recovery'];
                            foreach ($statuses as $stat) {
                                $selected = ($status == $stat) ? 'selected' : '';
                                echo "<option value=\"$stat\" $selected>$stat</option>";
                            }
                            ?>
                        </select>
                    </div>
            
                    <!-- Search Query -->
                    <div class="mb-3 field col-md-4">
                        <label for="search_query" class="form-label">Enter Search Query (Use Comma For Multi-Selection)</label>
                        <input type="text" name="search_query" class="form-control" id="search_query" value="<?= htmlspecialchars($search_query) ?>" placeholder="Enter client name or contact">
                    </div>
            
                    <!-- Sort Dropdown -->
                    <div class="mb-3 field col-md-2">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" name="sort">
                            <option value="date_asc" <?= ($sort == 'date_asc') ? 'selected' : '' ?>>Date (Asc)</option>
                            <option value="date_desc" <?= ($sort == 'date_desc') ? 'selected' : '' ?>>Date (Desc)</option>
                        </select>
                    </div>
            
                </div>
            
                <!-- Buttons -->
                <button type="submit" name="generate_report" class="btn sub-btn1">Search</button>
            
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                    <button type="button" class="btn sub-btn1" onclick="showPasswordModal()">Print</button>
                    <button id="showPasswordField" class="btn sub-btn1">Excel</button>
                    <div class="row">
                        <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" onclick="copyContacts()">Copy Contacts</button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn sub-btn1 mt-4" id="toggleContact" type="button">Contact Hide</button>
                    </div>
                    </div>
                <?php endif; ?>
            
            </form>

            <form id="csvForm" action="report.php" method="post">
                <div id="passwordField" style="display: none;">
                    <label for="password">Enter Password:</label>
                    <input type="password" name="password" >
                    <button type="submit" name="generate_csv">Submit</button>
                </div>
            </form>

            

            <div id="reportSection">
            <?php if (!empty($report)): ?>

                
            

                <div class="my-5">
                    
                    <?php
                        $formatted_start_date = date("d/m/Y", strtotime($from_date));
                        $formatted_end_date = date("d/m/Y", strtotime($to_date));
                        echo "<h4 class='text-center'>$department Report From $formatted_start_date TO $formatted_end_date </h4>"
                    ?>

                </div>
                <table class="table table-bordered pt-5">
                    <h3>Summary : </h3>
                    <thead>
                        <tr>
                            <th>Total Entries</th>
                            <th>Total Policy Amount</th>
                            <th>Total Recovery Amount</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $total_entries_count ?></td>
                            <td><?php echo $total_adv_amount ?></td>
                            <td><?php echo $total_recov_amount ?> </td>
                            
                        </tr>
                    </tbody>
                </table>

            <form id="whatsappForm">
                <div class="row mb-3 action-col">
                    <div class="col-md-3 field">
                        <label for="message_type">Message Type:</label>
                        <select name="message_type" id="message_type" class="form-control" required>
                            <option value="template">Template Message</option>
                            <option value="media">Media Message</option>
                        </select>
                    </div>
                    <div class="col-md-3 field" id="template_name_group">
                        <label for="template_name">Template Name:</label>
                        <input type="text" name="template_name" id="template_name" class="form-control" placeholder="e.g., demo_msg" required>
                    </div>
                    <div class="col-md-4 field d-none" id="media_details_group">
                        <label for="media_url">Media URL:</label>
                        <input type="url" name="media_url" id="media_url" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="col-md-2 mt-4">
                         <button type="submit" class="btn btn-primary mb-3 action-col">Send WhatsApp Messages</button>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="action-col"><input type="checkbox" id="selectAll"></th>
                            <th>Sr No</th>
                            <!--<th>Reg Num</th>-->
                            <th>Date</th>
                            <th>Client Name</th>
                            <th class="contact-data">Contact</th>
                            <th>Address</th>
                        
                            <?php
                                if ($department === "GIC") {
                                    echo "<th>Policy Type</th>";
                                    echo "<th>MV No</th>";
                                    echo "<th>Policy Subtype</th>";
                                    echo "<th>Policy Company</th>";
                                }
                            ?>

                            <?php
                                if ($department === "LIC") {
                                    echo "<th>Job Type</th>";
                                    echo "<th colspan='2'>Policy Number</th>";
                                    
                                }
                            ?>

                            <?php
                                if ($department === "R/JOB") {
                                    echo "<th>MV/DL No</th>";
                                    
                                }
                            ?>

                            <?php
                                if ($department === "BMDS") {
                                    echo "<th>Car Type</th>";
                                    
                                }
                            ?>

                            <?php
                                if ($department === "MF") {
                                    echo "<th>Type</th>";
                                    
                                }
                            ?>

                            <th>Policy Amount</th>
                            <th>Recovery Amount</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Referance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $sr_no = 1; // Initialize the serial number
                            foreach ($report as $item): 
                        ?>
                            <tr>
                                <td class="action-col"><input type="checkbox" name="selected_clients[]" value="<?= $item['contact'] . '|' . $item['client_name'] ?>"></td>
                                <td><?php echo $sr_no++; ?></td>
                                <!--<td><?= htmlspecialchars($item['reg_num']); ?></td>-->
                                <td><?= htmlspecialchars((new DateTime($item['policy_date']))->format('d/m/Y')); ?></td>
                                <td><?= htmlspecialchars($item['client_name']); ?></td>
                                <td  class="contact-data"><?= htmlspecialchars($item['contact']); ?></td>
                                <td><?= htmlspecialchars($item['address']); ?></td>
                            
                                
                                <?php
                                    if ($department === "GIC") {
                                        echo "<td>" . htmlspecialchars($item['policy_type']) . "</td>";
                                        echo "<td>" . htmlspecialchars($item['mv_number']) . "<br>" . htmlspecialchars($item['vehicle']) . "</td>";
                                        echo "<td>" . htmlspecialchars($item['nonmotor_type_select']) ."</td>";
                                        echo "<td>" . htmlspecialchars($item['policy_company']) . "</td>";
                                    }
                                ?>

                                <?php
                                    if ($department === "LIC") {
                                        // Echo job_type
                                        echo "<td>" . htmlspecialchars($item['job_type']) . "</td>";
                                        
                                        // Echo policy_num
                                        echo "<td>" . htmlspecialchars($item['policy_num']) . "</td>";

                                        // Decode colle_policy_num from JSON
                                        $colle_policy_num = json_decode($item['colle_policy_num'], true); // Decode as associative array
                                        
                                        echo "<td>";

                                        if (is_array($colle_policy_num)) {
                                            // If it's an array, loop and print each policy
                                            echo implode('<br>', array_map('htmlspecialchars', $colle_policy_num)); 
                                        } else {
                                            // If not an array, print the single decoded value
                                            echo htmlspecialchars($colle_policy_num); 
                                        }

                                        echo "</td>";
                                    }
                                ?>



                                <?php
                                    if ($department === "R/JOB") {
                                    
                                        echo "<td>" . htmlspecialchars($item['mv_no']) . "</td>";
                                    }
                                ?>

                                <?php
                                    if ($department === "BMDS") {
                                    
                                        echo "<td>" . htmlspecialchars($item['car_type']) . "</td>";
                                    }
                                ?>

                                <?php
                                    if ($department === "MF") {
                                    
                                        echo "<td>" . htmlspecialchars($item['bmds_type']) . "</td>";
                                    }
                                ?>

                                <td><?= htmlspecialchars($item['policy_amt']); ?></td>
                                <td><?= htmlspecialchars($item['recov_amount']); ?></td>
                                <td><?= htmlspecialchars($item['form_status']); ?></td>
                                <td><?= htmlspecialchars($item['department']); ?></td>
                                <td>
                                    <?php
                                    $fields = [
                                        'mv_number',
                                        'vehicle',
                                        'nonmotor_type_select',
                                        'nonmotor_subtype_select',
                                        'job_type',
                                        'collection_job',
                                        'mv_no',
                                        'work_status',
                                        'car_type',
                                        'mf_option',
                                        'insurance_option',
                                        'dl_type_work',
                                        'tr_type_work',
                                        'nt_type_work',
                                        'bmds_type',
                                        'llr_type',
                                        'mdl_type'
                                        
                                    ];

                                    foreach ($fields as $field) {
                                        if (!empty($item[$field])) {
                                            echo htmlspecialchars($item[$field]) . '<br>';
                                        }
                                    }
                                    ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
               
            </form>

<script>

    // Select/Deselect all checkboxes
    document.getElementById('selectAll').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('input[name="selected_clients[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    document.getElementById("message_type").addEventListener("change", function () {
        const mediaGroup = document.getElementById("media_details_group");
        if (this.value === "media") {
            mediaGroup.classList.remove("d-none");
        } else {
            mediaGroup.classList.add("d-none");
        }
    });

    document.getElementById("whatsappForm").addEventListener("submit", function (e) {
        e.preventDefault();

        const form = new FormData(this);

        fetch("send_whatsapp_msg.php", {
            method: "POST",
            body: form
        })
        .then(res => res.text())
        .then(data => {
            alert(data);       // Show message
            location.reload(); // Refresh page after alert
        })
        .catch(err => {
            alert("Error: " + err);
        });
    });
</script>

            <!-- show totals -->
                
            
                
                
            <?php endif; ?>
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



<script>
    // script for copy contacts to clipboard
function copyContacts() {
    const contactMap = new Map();
    const invalidContacts = [];
    const validContacts = [];
    let duplicateCount = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        const contact = row.querySelector('td:nth-child(5)')?.innerText.trim();
        if (contact && contact !== '--') {
            const cleaned = contact.replace(/\D/g, '');
            if (/^\d{10,12}$/.test(cleaned)) {
                if (contactMap.has(cleaned)) {
                    contactMap.set(cleaned, contactMap.get(cleaned) + 1);
                    duplicateCount++;
                } else {
                    contactMap.set(cleaned, 1);
                    validContacts.push(cleaned);
                }
            } else {
                invalidContacts.push(contact);
            }
        }
    });

    let message = `📋 Copy Results:\n\n`;
    message += `✅ Valid contacts prepared: ${validContacts.length}\n`;
    message += `❌ Invalid contacts: ${invalidContacts.length}\n`;
    message += `♻️ Duplicate contacts found: ${duplicateCount}\n\n`;

    if (invalidContacts.length > 0) {
        message += `--- Invalid Numbers (not 10-12 digits) ---\n`;
        message += invalidContacts.slice(0, 5).join('\n');
        if (invalidContacts.length > 5) message += `\n...and ${invalidContacts.length - 5} more`;
        message += `\n\n`;
    }

    if (validContacts.length > 0) {
        // Fallback method: create hidden textarea
        const textarea = document.createElement('textarea');
        textarea.value = validContacts.join('\n');
        document.body.appendChild(textarea);
        textarea.select();
        try {
            const success = document.execCommand('copy');
            if (success) {
                message += `📋 ${validContacts.length} contacts copied to clipboard!`;
            } else {
                message += `⚠️ Could not copy to clipboard automatically.\nPlease copy manually:\n\n${textarea.value}`;
            }
        } catch (err) {
            message += `⚠️ Copy failed: ${err}\nPlease copy manually:\n\n${textarea.value}`;
        }
        document.body.removeChild(textarea);
        alert(message);
    } else {
        alert(message + `\n\n⚠️ No valid contacts to copy!`);
    }
}

</script>



<!-- script for print password verify -->
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


    // csv file download form script
        $(document).ready(function () {
            $("#showPasswordField").click(function (e) {
                e.preventDefault(); // Prevent default button behavior
                $("#passwordField").slideDown(); // Show password input
                $(this).hide(); // Hide the "Download CSV" button
            });
        });



    // script for date - take end_date bydefault last day of month when user select start day of month
    document.getElementById("from_date").addEventListener("change", function () {
    const fromDateValue = this.value;
    
    // Make sure a valid date is selected
    if (fromDateValue) {
        const fromDate = new Date(fromDateValue);
        const year = fromDate.getFullYear();
        const month = fromDate.getMonth(); // JavaScript month is 0-indexed

        // Get the last date of the selected month
        const lastDate = new Date(year, month + 1, 0); // Next month, 0th day = last day of this month

        // Ensure proper formatting to YYYY-MM-DD
        const yyyy = lastDate.getFullYear();
        const mm = String(lastDate.getMonth() + 1).padStart(2, '0'); // add leading zero
        const dd = String(lastDate.getDate()).padStart(2, '0'); // add leading zero
        const formattedDate = `${yyyy}-${mm}-${dd}`;

        // Set to_date field
        document.getElementById("to_date").value = formattedDate;
    }
});

  // script for hide and unhide contact column from table
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('toggleContact').addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        e.preventDefault(); // Prevent default behavior
        
        const contactCells = document.querySelectorAll('.contact-data');
        const isHidden = contactCells[0].classList.contains('hidden');
        
        contactCells.forEach(cell => {
            cell.classList.toggle('hidden');
        });
        
        this.textContent = isHidden ? 'Contact Hide' : 'Contact Show';
    });
});

</script>


<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>


<?php //} ?>