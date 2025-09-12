<?php 
ob_start();  // Start output buffering
include 'include/header.php'; 
include 'include/head.php'; 
include 'session_check.php';
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>R/JOBS</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">R/JOBS</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        

       <!-- Single Search Form -->

       <?php
            include 'includes/db_conn.php';

            // Get form values
            $search_query = $_POST['search_query'] ?? '';
            $status = $_POST['status'] ?? '';
            $category = $_POST['category'] ?? '';
            $type_work_selected = $_POST['type_work'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? date('Y-m-d');

            // Fetch distinct Type Of Work values from multiple columns
            $type_work_sql = "
                SELECT DISTINCT dl_type_work AS type_work FROM `rto_entries` WHERE is_deleted = 0
                UNION
                SELECT DISTINCT tr_type_work AS type_work FROM `rto_entries` WHERE is_deleted = 0
                UNION
                SELECT DISTINCT nt_type_work AS type_work FROM `rto_entries` WHERE is_deleted = 0
            ";
            $type_work_result = $conn->query($type_work_sql);
            $type_work_options = [];
            while ($row = $type_work_result->fetch_assoc()) {
                $type_work_options[] = $row['type_work'];
            }
        ?>

        <form method="GET" class="p-3">
            <?php
            // Assign default values from GET request
            $search_query = $_GET['search_query'] ?? '';
            $status = $_GET['status'] ?? '';
            $category = $_GET['category'] ?? '';
            $type_work_selected = $_GET['type_work'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $current_page = $_GET['page'] ?? 1;
            $items_per_page = $_GET['items_per_page'] ?? 10;
            ?>

            <div class="row">
                <!-- Search -->
                <div class="col-md-4 field">
                    <label class="form-label">Search :</label>
                    <input type="text" name="search_query" class="form-control"
                        value="<?= htmlspecialchars($search_query) ?>"
                        placeholder="Search by Date, Name, Mobile and Job Type" />
                </div>

                <!-- Status -->
                <div class="col-md-1 field">
                    <label class="form-label">Status :</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Complete" <?= $status === 'Complete' ? 'selected' : '' ?>>Complete</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="col-md-1 field">
                    <label class="form-label">Category:</label>
                    <select name="category" class="form-control">
                        <option value="">All</option>
                        <option value="NT" <?= $category === 'NT' ? 'selected' : '' ?>>NT</option>
                        <option value="TR" <?= $category === 'TR' ? 'selected' : '' ?>>TR</option>
                        <option value="DL" <?= $category === 'DL' ? 'selected' : '' ?>>DL</option>
                    </select>
                </div>

                <!-- Type of Work -->
                <div class="col-md-1 field">
                    <label class="form-label">Type Of Work</label>
                    <select name="type_work" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($type_work_options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= $type_work_selected === $option ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="col-md-2 field">
                    <label class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                </div>

                <!-- End Date -->
                <div class="col-md-2 field">
                    <label class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                </div>

                <div class="col-md-1">
                    <button type="submit" name="generate_report" class="btn sub-btn1 mt-4">Search</button>
                </div>

                <div class="col-md-1">
                    <a href="rto" class="btn sub-btn1 mt-4">Reset</a>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                    <div class="row justify-content-center align-item-center">
                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal1">EXCEL</button>
                        </div>

                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal2">PDF</button>
                        </div>

                        <div class="col-md-1">
                            <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                        </div>

                        <div class="col-md-2">
                            <a href="rto-finance" class="btn sub-btn1 mt-4">Finance</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        
        
        <?php 
        
        include 'includes/db_conn.php';
        $report = [];
            
          
        // if (isset($_POST['generate_report']) || isset($_POST['generate_csv']) || isset($_POST['generate_pdf'])) {

            // Initialize search variables
            $search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : (isset($_GET['search_query']) ? trim($_GET['search_query']) : '');
            $status = isset($_POST['status']) ? trim($_POST['status']) : (isset($_GET['status']) ? trim($_GET['status']) : '');
            $category = isset($_POST['category']) ? trim($_POST['category']) : (isset($_GET['category']) ? trim($_GET['category']) : '');
            $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : (isset($_GET['start_date']) ? trim($_GET['start_date']) : '');
            $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : (isset($_GET['end_date']) ? trim($_GET['end_date']) : '');
            $type_work = isset($_POST['type_work']) ? trim($_POST['type_work']) : (isset($_GET['type_work']) ? trim($_GET['type_work']) : '');
            
            // total count variable
            $total_records = 0;
            $total_amount = 0;
            $total_recov_amount = 0;
            $total_adv_amount = 0;
            $total_gov_amount = 0;
            $total_other_amount = 0;
            
        
            // Get current page number from the query string, default to 1
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 10; // Records per page
            
            
            // $items_per_page = 10; // Number of entries per page
            // $offset = ($current_page - 1) * $items_per_page;
            
            $sortColumn = 'policy_date';
            $order = 'DESC'; // Default is descending order
            
            
                // Check if a sorting option has been selected
                // if (isset($_POST['sort'])) {
                //     $sortOption = $_POST['sort'];
                //     if ($sortOption === 'reg_num_asc') {
                //         $sortColumn = 'reg_num';
                //         $order = 'ASC';
                //     } elseif ($sortOption === 'reg_num_desc') {
                //         $sortColumn = 'reg_num';
                //         $order = 'DESC';
                //     } elseif ($sortOption === 'date_asc') {
                //         $sortColumn = 'date';
                //         $order = 'ASC';
                //     } elseif ($sortOption === 'date_desc') {
                //         $sortColumn = 'date';
                //         $order = 'DESC';
                //     }
                // }
            
           // Prepare the SQL query based on the search input
        if (!empty($search_query) || !empty($category) || !empty($category) || !empty($start_date) || !empty($end_date)) {
            // Convert date format if necessary
            if (!empty($start_date)) {
                $start_date = date('Y-m-d', strtotime($start_date));
            }
            if (!empty($end_date)) {
                $end_date = date('Y-m-d', strtotime($end_date));
            }
        
            $sql = "SELECT * FROM `rto_entries` WHERE is_deleted = 0";
            $params = [];
            $param_types = '';
        
            // Handle Search Query
            if (!empty($search_query)) {
                $sql .= " AND (client_name LIKE ? OR contact LIKE ? OR mv_no LIKE ?)";
                $search_param = "%$search_query%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
                $param_types .= 'sss';
            }
        
            // Handle Status Filter
            if (!empty($status) && ($status === 'Pending' || $status === 'Complete')) {
                $sql .= " AND form_status = ?";
                $params[] = $status;
                $param_types .= 's';
            }
            
            if (!empty($category) && ($category === 'NT' || $category === 'TR' || $category === 'DL')) {
                $sql .= " AND category = ?";
                $params[] = $category;
                $param_types .= 's';
            }

            // Handle Type of Work Filter
            if (!empty($type_work)) {
                $sql .= " AND (
                    dl_type_work LIKE ? 
                    OR tr_type_work LIKE ? 
                    OR nt_type_work LIKE ?
                )";
                $type_work_param = "%$type_work%";
                $params[] = $type_work_param; // For dl_type_work
                $params[] = $type_work_param; // For tr_type_work
                $params[] = $type_work_param; // For nt_type_work
                $param_types .= 'sss'; // Three string parameters
            }

        
            // Handle Date Range
            if (!empty($start_date) && !empty($end_date)) {
                $sql .= " AND policy_date BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
                $param_types .= 'ss';
            } elseif (!empty($start_date)) {
                $sql .= " AND policy_date >= ?";
                $params[] = $start_date;
                $param_types .= 's';
            } elseif (!empty($end_date)) {
                $sql .= " AND policy_date <= ?";
                $params[] = $end_date;
                $param_types .= 's';
            }
        
            // Add ordering and pagination
            // $sql .= " ORDER BY $sortColumn $order LIMIT ?, ?";
            // $params[] = $offset;
            // $params[] = $items_per_page;
            // $param_types .= 'ii';
            
            // code for pagination
            
            $offset = 0;

            if ($items_per_page === 'all') {
                $sql .= " ORDER BY $sortColumn $order, reg_num DESC"; // Optional: tie-break by reg_num
            } else {
                $offset = ($current_page - 1) * $items_per_page;
                $sql .= " ORDER BY $sortColumn $order, reg_num DESC LIMIT ?, ?";
                $params[] = $offset;
                $params[] = $items_per_page;
                $param_types .= 'ii';
            }

        
            // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            if ($param_types) {
                $stmt->bind_param($param_types, ...$params);
            }
        
            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
        
            // Count total records for pagination
            // $count_sql = "SELECT COUNT(*) as total FROM `rto_entries` WHERE is_deleted = 0";
            // $count_params = [];
            // $count_param_types = '';
        
            // // Apply filters to the count query
            // if (!empty($search_query)) {
            //     $count_sql .= " AND (client_name LIKE ? OR contact LIKE ? OR mv_no LIKE ?)";
            //     $count_params[] = $search_param;
            //     $count_params[] = $search_param;
            //     $count_params[] = $search_param;
            //     $count_param_types .= 'sss';
            // }
        
            // if (!empty($status) && ($status === 'Pending' || $status === 'Complete')) {
            //     $count_sql .= " AND form_status = ?";
            //     $count_params[] = $status;
            //     $count_param_types .= 's';
            // }
            
            // if (!empty($category) && ($category === 'NT' || $category === 'TR' || $category === 'DL')) {
            //     $sql .= " AND category = ?";
            //     $params[] = $category;
            //     $param_types .= 's';
            // }
        
            // if (!empty($start_date) && !empty($end_date)) {
            //     $count_sql .= " AND date BETWEEN ? AND ?";
            //     $count_params[] = $start_date;
            //     $count_params[] = $end_date;
            //     $count_param_types .= 'ss';
            // } elseif (!empty($start_date)) {
            //     $count_sql .= " AND date >= ?";
            //     $count_params[] = $start_date;
            //     $count_param_types .= 's';
            // } elseif (!empty($end_date)) {
            //     $count_sql .= " AND date <= ?";
            //     $count_params[] = $end_date;
            //     $count_param_types .= 's';
            // }
        
            // $count_stmt = $conn->prepare($count_sql);
            // if ($count_param_types) {
            //     $count_stmt->bind_param($count_param_types, ...$count_params);
            // }
        
            // $count_stmt->execute();
            // $count_result = $count_stmt->get_result();
            // $total_records = $count_result->fetch_assoc()['total'];
            // $total_pages = ceil($total_records / $items_per_page);
            
            
            // Fetch total number of records and total amounts for pagination
                $count_sql = "SELECT 
                            COUNT(*) as total, 
                            SUM(amount) as total_amount, 
                            SUM(recov_amount) as total_recov_amount,
                            SUM(net_amt) as total_net_amount, 
                            SUM(expenses) as total_expenses_amount, 
                            SUM(other_amount) as total_other_amount,
                            SUM(adv_amount) as total_adv_amount,
                            SUM(gov_amount) as total_gov_amount
                            FROM `rto_entries` 
                            WHERE is_deleted = 0";
                
                $count_params = [];
                $count_param_types = '';
                
                // Apply filters to the count query
                if (!empty($search_query)) {
                    $count_sql .= " AND (client_name LIKE ? OR contact LIKE ? OR mv_no LIKE ?)";
                    $count_params[] = $search_param;
                    $count_params[] = $search_param;
                    $count_params[] = $search_param;
                    $count_param_types .= 'sss';
                }
            
                if (!empty($status) && ($status === 'Pending' || $status === 'Complete')) {
                    $count_sql .= " AND form_status = ?";
                    $count_params[] = $status;
                    $count_param_types .= 's';
                }
                
                if (!empty($category) && ($category === 'NT' || $category === 'TR' || $category === 'DL')) {
                    $count_sql .= " AND category = ?";
                    $count_params[] = $category;
                    $count_param_types .= 's';
                }

                

                // Handle Type of Work Filter
                if (!empty($type_work)) {
                    $count_sql .= " AND (
                        dl_type_work LIKE ? 
                        OR tr_type_work LIKE ? 
                        OR nt_type_work LIKE ?
                    )";
                    $type_work_param = "%$type_work%";
                    $count_params[] = $type_work_param; // For dl_type_work
                    $count_params[] = $type_work_param; // For tr_type_work
                    $count_params[] = $type_work_param; // For nt_type_work
                    $count_param_types .= 'sss'; // Three string parameters
                }

            
                if (!empty($start_date) && !empty($end_date)) {
                    $count_sql .= " AND policy_date BETWEEN ? AND ?";
                    $count_params[] = $start_date;
                    $count_params[] = $end_date;
                    $count_param_types .= 'ss';
                } elseif (!empty($start_date)) {
                    $count_sql .= " AND policy_date >= ?";
                    $count_params[] = $start_date;
                    $count_param_types .= 's';
                } elseif (!empty($end_date)) {
                    $count_sql .= " AND policy_date <= ?";
                    $count_params[] = $end_date;
                    $count_param_types .= 's';
                }
                
                $count_stmt = $conn->prepare($count_sql);
                if ($count_param_types) {
                    $count_stmt->bind_param($count_param_types, ...$count_params);
                }
                
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $count_data = $count_result->fetch_assoc();
                
                // Extract the values
                $total_records = $count_data['total'];
                $total_amount = $count_data['total_amount'] ?? 0; // Fallback to 0 if null
                $total_recov_amount = $count_data['total_recov_amount'] ?? 0; // Fallback to 0 if null
                $total_net_amount = $count_data['total_net_amount'] ?? 0;
                $total_expenses_amount = $count_data['total_expenses_amount'] ?? 0;
                $total_other_amount = $count_data['total_other_amount'] ?? 0;
                $total_gov_amount = $count_data['total_gov_amount'] ?? 0;
                $total_adv_amount = $count_data['total_adv_amount'] ?? 0;
                
                // $total_pages = ceil($total_records / $items_per_page); // Calculate total pages
                
                $total_pages = $items_per_page === 'all' ? 1 : ceil($total_records / $items_per_page);
            
            
        } else {
            $result = null; // If no search criteria, show empty table
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
            header('Content-Disposition: attachment; filename=rto-report.csv');
        
            // Open the output stream to write CSV data
            $output = fopen('php://output', 'w');
        
            // Add the header row to the CSV file
            fputcsv($output, ['Reg Num', 'Date', 'Client Name', 'Contact','Category','MV/DL No','Type Of Work','Premium','Advance','Recovery','Gov Fee','Cash In Hand','Expense','Net Amt','Adviser', 'Status']);
        
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                $sql = "SELECT * FROM rto_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM rto_entries";
                $stmt = $conn->prepare($sql);
            }
        
            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                // Fetch each row and write it to the CSV file
                while ($item = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $item['reg_num'],
                        (new DateTime($item['policy_date']))->format('d/m/Y'),  // Format date as 'd/m/Y'
                        $item['client_name'],
                        $item['contact'],
                        $item['category'],
                        $item['mv_no'],
                        $item['type_work'],
                        $item['amount'],
                        $item['adv_amount'],
                        $item['recov_amount'],
                        $item['gov_amount'],
                        $item['other_amount'],
                        $item['expenses'],
                        $item['net_amt'],
                        $item['adviser_name'],
                        $item['form_status'],
                    ]);
                }
            } else {
                // If no records are found, display a message
                fputcsv($output, ['No records found']);
            }
        
            // Close the database connection
            $conn->close();
        
            // Close the CSV output
            fclose($output);
        
            // End the script
            exit();
        }
        else {
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

    
         // If user clicks on 'Generate PDF'
        if (isset($_POST['generate_pdf'])) {

            // Get the submitted password
            $admin_password = $_POST['admin_password'] ?? '';
        
            // Fetch the stored hashed password from the database
            $sql = "SELECT password FROM file WHERE file_type = 'PDF' LIMIT 1"; // Assuming only one admin password
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

            require('fpdf/fpdf.php');
            
            // Instantiate and use the FPDF class 
            $pdf = new FPDF('L', 'mm', 'A3'); // Landscape mode with A3 size
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'GIC Report', 0, 1, 'C');
            
            // Add column headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 10, 'Reg Num', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(70, 10, 'Client Name', 1);
            $pdf->Cell(30, 10, 'Contact', 1);
            $pdf->Cell(20, 10, 'Category', 1);
            $pdf->Cell(30, 10, 'MV/DL No', 1);
            $pdf->Cell(30, 10, 'Type Of Work', 1);
            $pdf->Cell(20, 10, 'Premium', 1);
            $pdf->Cell(20, 10, 'Advance', 1);
            $pdf->Cell(20, 10, 'Recovery', 1);
            $pdf->Cell(20, 10, 'Gov Fee', 1);
            $pdf->Cell(30, 10, 'Cash In Hand', 1);
            $pdf->Cell(20, 10, 'Expense', 1);
            $pdf->Cell(20, 10, 'Net Amt', 1);
            $pdf->Cell(30, 10, 'Adviser', 1);
            $pdf->Cell(20, 10, 'Status', 1);
            
            $pdf->Ln();
            
            // Get start_date and end_date from POST
            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
            
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                // Format dates for SQL query
                $start_date = date('Y-m-d', strtotime($start_date));  // Ensure the date format is correct
                $end_date = date('Y-m-d', strtotime($end_date));
                
                $sql = "SELECT * FROM rto_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM rto_entries";
                $stmt = $conn->prepare($sql);
            }
            
            // Execute query
            $stmt->execute();
            $result = $stmt->get_result(); // Fetch results
            
            // Add data rows if there are results
            $pdf->SetFont('Arial', '', 10);
            if ($result->num_rows > 0) {
                while ($item = $result->fetch_assoc()) {
                    $pdf->Cell(20, 10, $item['reg_num'], 1);
                    $pdf->Cell(25, 10, (new DateTime($item['policy_date']))->format('d/m/Y'), 1);
                    $pdf->Cell(70, 10, $item['client_name'], 1);
                    $pdf->Cell(30, 10, $item['contact'], 1);
                    $pdf->Cell(20, 10, $item['category'], 1);
                    $pdf->Cell(30, 10, $item['mv_no'], 1);
                    $pdf->Cell(30, 10, $item['type_work'], 1);
                    $pdf->Cell(20, 10, $item['amount'], 1);
                    $pdf->Cell(20, 10, $item['adv_amount'], 1);
                    $pdf->Cell(20, 10, $item['recov_amount'], 1);
                    $pdf->Cell(20, 10, $item['gov_amount'], 1);
                    $pdf->Cell(30, 10, $item['other_amount'], 1);
                    $pdf->Cell(20, 10, $item['expenses'], 1);
                    $pdf->Cell(20, 10, $item['net_amt'], 1);
                    $pdf->Cell(30, 10, $item['adviser_name'], 1);
                    $pdf->Cell(20, 10, $item['form_status'], 1);
                    
                    $pdf->Ln();
                }
            } else {
                // No data found, display a message in the PDF
                $pdf->Cell(0, 10, 'No records found for the selected date range.', 0, 1, 'C');
            }
            
            // Output the PDF (force download)
            $pdf->Output('D', 'rto-report.pdf'); // Force download
            exit();
        }
        else {
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
        
            
            
            // Close the connection
            $conn->close();
    
        // }
            
            
            
?>
        
        
    
        
    
        
    <div id="reportSection">   
        
    <h3>Summary : </h3>
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>No Of Job's</th>
                    <th>Premium</th>
                    <th>Advance</th>
                    <th>Recovery</th>
                    <th>Gov Fee</th>
                    <th>Cash In Hand</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $total_records ?></td>
                    <td><?php echo $total_amount ?></td>
                    <td><?php echo $total_adv_amount ?> </td>
                    <td><?php echo $total_recov_amount ?> </td>
                    <td><?php echo $total_gov_amount ?> </td>
                    <td><?php echo $total_other_amount ?> </td>
                </tr>
            </tbody>
        </table>
        
        <div class="heading">
            <?php
                
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "<h1 class='text-center'>RTO REPORT FOR $formatted_start_date TO $formatted_end_date </h1>"
                
            ?>
        </div>

         <form method="POST" id="bulkDeleteForm">
    <div class="float-end pb-3">
        <button type="button" class="btn sub-btn1 mt-4 bg-danger text-light" data-bs-toggle="modal" data-bs-target="#passwordModalSelected">
            Clear
        </button>
    </div>

    


        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th scope="col">#</th>
                    <th scope="col" class="action-col">Client ID</th>
                    <th scope="col">Reg No.</th>
                    <th scope="col">Date </th>
                    <th scope="col">Client Name</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Category</th>
                    <th scope="col">MV/DL No</th>
                    <th scope="col">Type Of Work</th>
                    <th scope="col">Premium</th>
                    <th scope="col">Advance</th>
                    <th scope="col">Recovery</th>
                    <th scope="col">Gov Fees</th>
                    <th scope="col">Cash In Hand</th>
                    <th scope="col">Expense</th>
                    <th scope="col">Net Amt</th>
                    <th scope="col">Adviser</th>
                    <th scope="col">Remark</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="action-col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($result) && $result->num_rows > 0) {
                    $serial_number = $offset + 1; // Initialize serial number
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>"></td>
                            <th scope="row"><?php echo $serial_number++; ?></th>
                            <td class="action-col"> <?php echo htmlspecialchars($row['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                            <td>
                                <?php 
                                $original_date = $row['policy_date']; // date in YYYY-MM-DD format from database
                                $formatted_date = DateTime::createFromFormat('Y-m-d', $original_date)->format('d/m/Y'); // format to DD/MM/YYYY
                                echo htmlspecialchars($formatted_date);  
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['mv_no']); ?> <br>
                                <?php echo htmlspecialchars($row['vehicle_class']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['dl_type_work']); ?>
                                <?php echo htmlspecialchars($row['tr_type_work']); ?>
                                <?php echo htmlspecialchars($row['nt_type_work']); ?>
                            </td>
                            <td><?php echo ($row['amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo ($row['adv_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['adv_amount']); ?></td>
                            <td><?php echo ($row['recov_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['recov_amount']); ?></td>
                            <td><?php echo ($row['gov_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['gov_amount']); ?></td>
                            <td><?php echo ($row['other_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['other_amount']); ?></td>
                            <td><?php echo ($row['expenses'] ?? 0) == 0 ? '' : htmlspecialchars($row['expenses']); ?></td>
                            <td><?php echo ($row['net_amt'] ?? 0) == 0 ? '' : htmlspecialchars($row['net_amt']); ?></td>
                            <td><?php echo htmlspecialchars($row['adviser_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['remark']); ?></td>
                            <td><?php echo htmlspecialchars($row['form_status']); ?></td>
                            
        
                            <td class="action-col">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                    <a href="rto-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a> &nbsp;/&nbsp;
                                    <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?php echo $row['id']; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    &nbsp;/&nbsp;
                                <?php endif; ?>
                                
                                <!-- <a href="rto-form.php?action=add_new&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Add New">
                                    <i class="fa-solid fa-plus"></i>
                                </a> -->
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='17'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
            </form>
        
        
    
    </div>
        
        <!-- Pagination Links -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination neumorphic-pagination">
                    <!-- Show All -->
                    <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1, 'items_per_page' => 'all'])) ?>">Show All</a>
                    </li>

                    <!-- Previous -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
        
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

<!-- Password Verification Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="rto-delete.php" method="POST">
                    <input type="hidden" name="itemId" id="itemId"> <!-- Hidden input to hold the item ID -->
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="passwordInput" placeholder="********" required>
                    </div>
                    <div id="passwordError" class="text-danger" style="display: none;">Incorrect password. Please try again.</div>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Excel Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal1" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
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


<!-- PDF Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal2" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="passwordModalLabel">Enter Password For PDF Download</h5> -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="csvDownloadForm" method="post">
        <div class="modal-body">
          <input type="password" name="admin_password" class="form-control" placeholder="Enter Admin Password" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="generate_pdf" value="generate_pdf" class="btn btn-primary" id="downloadpdf">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Password Modal for Delete Selected -->
<div class="modal fade" id="passwordModalSelected" tabindex="-1" aria-labelledby="passwordModalLabelSelected" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enter Admin Password to Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="adminPasswordBulk" class="form-label">Admin Password</label>
                    <input type="password" class="form-control" id="adminPasswordBulk" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBulkDelete()">Verify & Delete</button>
            </div>
        </div>
    </div>
</div>

<script>

    // Script for Delete Selected

    document.getElementById('selectAll').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function submitBulkDelete() {
        const password = document.getElementById('adminPasswordBulk').value;
        if (!password.trim()) {
            alert("Please enter the admin password.");
            return;
        }

        // Collect form data
        const form = document.getElementById('bulkDeleteForm');
        const formData = new FormData(form);
        formData.append('password', password);

        // Send via POST using Fetch API
        fetch('rto_all_entries_dele.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            document.open();
            document.write(response);
            document.close();
        })
        .catch(error => {
            alert('Failed to delete records.');
            console.error(error);
        });
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
</script>



<script>

// Script for show data with pagination or not

    // function setItemsPerPage(value) {
    //     // Redirect or submit form with items_per_page
    //     const urlParams = new URLSearchParams(window.location.search);
    //     urlParams.set('items_per_page', value);
    //     window.location.search = urlParams.toString(); // Reload with updated parameter
    // }


// script for delete id

document.getElementById('passwordModal').addEventListener('show.bs.modal', function (event) {
    // Get the anchor element that triggered the modal
    var triggerElement = event.relatedTarget;
    
    // Extract the item ID from the data attribute
    var itemId = triggerElement.getAttribute('data-item-id');
    
    // Find the hidden input field in the modal and set its value
    var modalItemIdInput = document.getElementById('itemId');
    modalItemIdInput.value = itemId;
});

// Excel download Close modal and refresh page when "Download" button is clicked
document.getElementById('downloadButton').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal1'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });

// pdf download Close modal and refresh page when "Download" button is clicked
document.getElementById('downloadpdf').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal2'));
    modal.hide();

    // Refresh the page after closing the modal
    setTimeout(function() {
      window.location.reload();  // This refreshes the page
    }, 500); // Delay to ensure modal closes before page reload
  });


    // when hover on icon show tooltip

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>






<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>