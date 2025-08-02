<?php 

ob_start();

    include 'include/header.php';  
    include 'include/head.php'; 
    include 'session_check.php';
 
?>


<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>LIC</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">LIC</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        
       <?php
            // Retrieve filter values from GET
            $search_query = $_GET['search_query'] ?? '';
            $status = $_GET['status'] ?? '';
            $sort = $_GET['sort'] ?? '';
            $start_date = $_GET['start_date'] ?? '';
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = $_GET['items_per_page'] ?? 10;

            // For sorting
            $sortColumn = '';
            $order = '';
            if (!empty($sort)) {
                $order = (strpos($sort, '_desc') !== false) ? 'DESC' : 'ASC';
                if (strpos($sort, 'reg_num') !== false) {
                    $sortColumn = 'reg_num';
                } elseif (strpos($sort, 'date') !== false) {
                    $sortColumn = 'policy_date';
                } elseif (strpos($sort, 'pay') !== false) {
                    $sortColumn = 'pay_mode';
                }
            }
            ?>

            <!-- Search Form (GET method) -->
            <form method="GET" class="p-3">
                <div class="row">
                    <div class="col-md-4 field">
                        <label class="form-label">Search :</label>
                        <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>"
                            placeholder="Search by Date, Name, Mobile and Job Type" />
                    </div>

                    <div class="col-md-1 field">
                        <label class="form-label">Status :</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="Complete" <?= ($status === 'Complete') ? 'selected' : '' ?>>Complete</option>
                            <option value="CDA" <?= ($status === 'CDA') ? 'selected' : '' ?>>CDA</option>
                            <option value="CANCELLED" <?= ($status === 'CANCELLED') ? 'selected' : '' ?>>CANCELLED</option>
                            <option value="OTHER" <?= ($status === 'OTHER') ? 'selected' : '' ?>>OTHER</option>
                        </select>
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">Sort By:</label>
                        <select name="sort" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="reg_num_desc" <?= ($sort === 'reg_num_desc') ? 'selected' : '' ?>>Reg Num (DESC)</option>
                            <option value="reg_num_asc" <?= ($sort === 'reg_num_asc') ? 'selected' : '' ?>>Reg Num (ASC)</option>
                            <option value="date_desc" <?= ($sort === 'date_desc') ? 'selected' : '' ?>>Date (DESC)</option>
                            <option value="date_asc" <?= ($sort === 'date_asc') ? 'selected' : '' ?>>Date (ASC)</option>
                            <option value="pay_desc" <?= ($sort === 'pay_desc') ? 'selected' : '' ?>>Pay Mode (DESC)</option>
                            <option value="pay_asc" <?= ($sort === 'pay_asc') ? 'selected' : '' ?>>Pay Mode (ASC)</option>
                        </select>
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">Start Date :</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                    </div>

                    <div class="col-md-2 field">
                        <label class="form-label">End Date :</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn sub-btn1 mt-4">Search</button>
                    </div>

                    <div class="col-md-1">
                        <a href="lic" class="btn sub-btn1 mt-4">Reset</a>
                    </div>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                        <div class="row justify-content-center align-items-center mt-4">
                            <div class="col-md-1">
                                <button type="button" class="btn sub-btn1" data-bs-toggle="modal" data-bs-target="#passwordModal1">EXCEL</button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn sub-btn1" data-bs-toggle="modal" data-bs-target="#passwordModal2">PDF</button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn sub-btn1" onclick="showPasswordModal()">Print</button>
                            </div>
                            <div class="col-md-2">
                                <a href="lic-finance" class="btn sub-btn1">JobTypeWise</a>
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
            $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : (isset($_GET['start_date']) ? trim($_GET['start_date']) : '');
            $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : (isset($_GET['end_date']) ? trim($_GET['end_date']) : '');
            
            // total count variable
            $total_records = 0;
            $total_amount = 0;
            
            // Get current page number from the query string, default to 1
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 10; // Records per page
            $offset = ($items_per_page === 'all') ? 0 : ($current_page - 1) * $items_per_page;

            
            // $items_per_page = 10; // Number of entries per page
            // $offset = ($current_page - 1) * $items_per_page;
            
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            // Calculate fiscal year start and end
            if ($currentMonth >= 4) {
                $fiscalYearStart = $currentYear;
                $fiscalYearEnd = $currentYear + 1;
            } else {
                $fiscalYearStart = $currentYear - 1;
                $fiscalYearEnd = $currentYear;
            }
            
            $fiscalYearStartDate = "$fiscalYearStart-04-01";
            $fiscalYearEndDate = "$fiscalYearEnd-03-31";
            
            // Default sorting
            $sortColumn = 'reg_num';
            $order = 'DESC';
            
            // Sorting options from POST
            if (isset($_POST['sort'])) {
                $sortOption = $_POST['sort'];
                if ($sortOption === 'reg_num_asc') {
                    $sortColumn = 'reg_num';
                    $order = 'ASC';
                } elseif ($sortOption === 'reg_num_desc') {
                    $sortColumn = 'reg_num';
                    $order = 'DESC';
                } elseif ($sortOption === 'date_asc') {
                    $sortColumn = 'policy_date';
                    $order = 'ASC';
                } elseif ($sortOption === 'date_desc') {
                    $sortColumn = 'policy_date';
                    $order = 'DESC';
                } elseif ($sortOption === 'pay_asc') {
                    $sortColumn = 'pay_mode';
                    $order = 'ASC';
                } elseif ($sortOption === 'pay_desc') {
                    $sortColumn = 'pay_mode';
                    $order = 'DESC';
                }
            }
                
            
           // Prepare the SQL query based on the search input
        if (!empty($search_query) || !empty($status) || !empty($start_date) || !empty($end_date)) {
            // Convert date format if necessary
            if (!empty($start_date)) {
                $start_date = date('Y-m-d', strtotime($start_date));
            }
            if (!empty($end_date)) {
                $end_date = date('Y-m-d', strtotime($end_date));
            }
        
            $sql = "SELECT * FROM `lic_entries` WHERE is_deleted = 0";
            $params = [];
            $param_types = '';
        
            // Handle Search Query
            if (!empty($search_query)) {
                $sql .= " AND (client_name LIKE ? OR contact LIKE ? OR job_type LIKE ?)";
                $search_param = "%$search_query%";
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
                $param_types .= 'sss';
            }
        
            // Handle Status Filter
            if (!empty($status) && ($status === 'Pending' || $status === 'Complete' || $status === 'CDA' || $status === 'CANCELLED' || $status === 'OTHER')) {
                $sql .= " AND form_status = ?";
                $params[] = $status;
                $param_types .= 's';
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
        
           
            
            // Add ORDER BY to prioritize current fiscal year and show latest first
            $sql .= " ORDER BY 
                        CASE 
                            WHEN policy_date >= '$fiscalYearStartDate' AND policy_date <= '$fiscalYearEndDate' THEN 1 
                            ELSE 2 
                        END,
                        $sortColumn $order";
            
            // Add pagination if not 'all'
            if ($items_per_page !== 'all') {
                $offset = ($current_page - 1) * $items_per_page;
                $sql .= " LIMIT ?, ?";
                $params[] = $offset;
                $params[] = $items_per_page;
                $param_types .= 'ii';
            }
        

            
           // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error); // Debugging prepare errors
            }

            if ($param_types) {
                $stmt->bind_param($param_types, ...$params);
            }

            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
        
            
            
            // Fetch total number of records and total amounts for pagination
                $count_sql = "SELECT 
                    COUNT(*) as total, 
                    SUM(policy_amt) as total_amount
                    FROM `lic_entries` 
                    WHERE is_deleted = 0";
                
                $count_params = [];
                $count_param_types = '';
                
                // Apply filters to the count query
            if (!empty($search_query)) {
                $count_sql .= " AND (client_name LIKE ? OR contact LIKE ? OR job_type LIKE ?)";
                $count_params[] = $search_param;
                $count_params[] = $search_param;
                $count_params[] = $search_param;
                $count_param_types .= 'sss';
            }
        
            if (!empty($status) && ($status === 'Pending' || $status === 'Complete' || $status === 'CDA' || $status === 'CANCELLED' || $status === 'OTHER')) {
                $count_sql .= " AND form_status = ?";
                $count_params[] = $status;
                $count_param_types .= 's';
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
            header('Content-Disposition: attachment; filename=lic-report.csv');
        
            // Open the output stream to write CSV data
            $output = fopen('php://output', 'w');
        
            // Add the header row to the CSV file
            fputcsv($output, ['Reg Num', 'Date', 'Client Name', 'Contact', 'Job Type','Type Of Job','Type Of Job','Policy Number','Policy Number','Pay Mode','Status']);
        
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                $sql = "SELECT * FROM lic_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM lic_entries";
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
                        $item['job_type'],
                        $item['collection_job'],
                        $item['work_status'],
                        $item['colle_policy_num'],
                        $item['policy_num'],
                        $item['pay_mode'],
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
            $pdf->Cell(0, 10, 'LIC Report', 0, 1, 'C');
            
            // Add column headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 10, 'Reg Num', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(70, 10, 'Client Name', 1);
            $pdf->Cell(30, 10, 'Contact', 1);
            $pdf->Cell(50, 10, 'Job Type', 1);
            $pdf->Cell(50, 10, 'Type Of Job', 1);
            $pdf->Cell(50, 10, 'Type Of Job', 1);
            $pdf->Cell(50, 10, 'Policy Number', 1);
            $pdf->Cell(50, 10, 'Policy Number', 1);
            $pdf->Cell(20, 10, 'Pay Mode', 1);
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
                
                $sql = "SELECT * FROM lic_entries WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM lic_entries";
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
                    $pdf->Cell(50, 10, $item['job_type'], 1);
                    $pdf->Cell(50, 10, $item['collection_job'], 1);
                    $pdf->Cell(50, 10, $item['work_status'], 1);
                    $pdf->Cell(50, 10, $item['colle_policy_num'], 1);
                    $pdf->Cell(50, 10, $item['policy_num'], 1);
                    $pdf->Cell(20, 10, $item['pay_mode'], 1);
                    $pdf->Cell(20, 10, $item['form_status'], 1);
                    
                    $pdf->Ln();    
                }
            } else {
                // No data found, display a message in the PDF
                $pdf->Cell(0, 10, 'No records found for the selected date range.', 0, 1, 'C');
            }
            
            // Output the PDF (force download)
            $pdf->Output('D', 'lic-report.pdf'); // Force download
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
    
        <div class="heading">
            <?php
                
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "<h1 class='text-center'>LIC REPORT FOR $formatted_start_date TO $formatted_end_date </h1>"
                
            ?>
        </div>

         <form method="POST" id="bulkDeleteForm">
    <div class="float-end pb-3">
        <button type="button" class="btn sub-btn1 mt-4 bg-danger text-light" data-bs-toggle="modal" data-bs-target="#passwordModalSelected">
            Delete Selected
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
                    <th scope="col">Job Type</th>
                    <th scope="col">Type Of Job</th>
                    <th scope="col">Policy Number</th>
                    <th scope="col">Agency Code</th>
                    <th scope="col">Payment Mode</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="action-col">Action</th>
                    <th scope="col" class="summary-col">Summary</th> <!-- Add Summary column for print -->
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
                            
                            <td><?php echo htmlspecialchars($row['job_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['work_status']); ?> <?php echo htmlspecialchars($row['collection_job']); ?></td>
                            <td>
                                <!-- Show policy number of SERVICING TASKS -->
                                <?php echo htmlspecialchars($row['policy_num']); ?>
                                
                                <!-- Show policy number of PREMIUM COLLECTION -->
                                <?php 
                                    // Decode colle_policy_num from JSON
                                    $colle_policy_num = json_decode($row['colle_policy_num'], true); // Decode as associative array
                                    if (is_array($colle_policy_num)) {
                                        // If it's an array, loop and print each policy
                                        echo implode('<br>', array_map('htmlspecialchars', $colle_policy_num)); 
                                    } else {
                                        // If not an array, print the single decoded value
                                        echo htmlspecialchars($colle_policy_num); 
                                    }
                                ?>
                            
                            </td>

                            
                            <td><?php echo htmlspecialchars($row['adviser']); ?></td>
                            <td><?php echo htmlspecialchars($row['pay_mode']); ?></td>
                            <td><?php echo htmlspecialchars($row['form_status']); ?></td>
                            
                            <td class="summary-col"></td> <!-- Blank Summary column for print -->
        
                            <td class="action-col">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                    <a href="lic-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a> &nbsp;/&nbsp;
                                    <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?php echo $row['id']; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    &nbsp;/&nbsp;
                                    
                                <?php endif; ?>
                                
                                <!-- <a href="lic-form.php?action=add_new&id=<?php //echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Add New">
                                    <i class="fa-solid fa-plus"></i>
                                </a> -->
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='12'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
            </form>
        <?php 
            
            // Output results
                
                echo "<h3>Summary : </h3>";
                
                echo "Total Records: <strong>$total_records</strong><br>";
                echo "Total Amount: <strong>$total_amount</strong><br>";

        ?>
    </div>
        
        <!-- Pagination Links -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination neumorphic-pagination">

                    <!-- Show All Button -->
                    <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1, 'items_per_page' => 'all'])) ?>">Show All</a>
                    </li>

                    <!-- Prev Button -->
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

                    <!-- Next Button -->
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
                <form id="verificationForm" action="lic-delete.php" method="POST">
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
        fetch('lic_all_entries_dele.php', {
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