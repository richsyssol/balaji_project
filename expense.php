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
                <h1>EXPENSES</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">EXPENSES</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        
        <!--<div class="float-start p-3">-->
        <!--    <a href="expense-form.php?action=add" class="btn sub-btn1 w-100">ADD EXPENSES</a>-->
        <!--</div>-->

       <!-- Single Search Form -->
        <form method="POST" class="p-3">
            <div class="row">
                <?php

                // Fetch unique values
                $expense_types = [];
                $mv_num = [];
        
                $query = "SELECT DISTINCT expense_type FROM expenses";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    $expense_types[] = $row['expense_type'];
                }
        
                $query = "SELECT DISTINCT mv_num FROM expenses";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    $mv_num[] = $row['mv_num'];
                }
        
                // Get POST values
                $search_query     = $_POST['search_query']     ?? '';
                $selected_mv      = $_POST['mv_number']        ?? '';
                $vehicle_type     = $_POST['vehicle']          ?? '';
                $expense_status   = $_POST['expense_status']   ?? '';
                $start_date       = $_POST['start_date']       ?? '';
                $end_date         = $_POST['end_date']         ?? date('Y-m-d');
                ?>
        
                <!-- Expense Type -->
                <div class="col-md-3 field">
                    <label for="search_query" class="form-label">Search by Expense Type:</label>
                    <select name="search_query" id="search_query" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($expense_types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $search_query === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
        
                <!-- MV Number -->
                <div class="col-md-2 field">
                    <label for="mvnumber" class="form-label">Search by MV Number:</label>
                    <select name="mv_number" id="mvnumber" class="form-control" onchange="handleMvNumChange(this.value)">
                        <option value="">All</option>
                        <?php foreach ($mv_num as $mv): ?>
                            <option value="<?= htmlspecialchars($mv) ?>" <?= $selected_mv === $mv ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mv) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
        
                <!-- Vehicle Type -->
                <div class="col-md-2 field">
                    <label for="vehicle_type" class="form-label">Search by Vehicle:</label>
                    <input type="text" name="vehicle" id="vehicle_type" class="form-control" value="<?= htmlspecialchars($vehicle_type) ?>" readonly>
                </div>
        
                <!-- Expense Status -->
                <div class="col-md-2 field">
                    <label for="expense_status" class="form-label">Expense Status:</label>
                    <select name="expense_status" class="form-control">
                        <option value="">All</option>
                        <option value="General" <?= $expense_status === 'General' ? 'selected' : '' ?>>General</option>
                        <option value="Fix" <?= $expense_status === 'Fix' ? 'selected' : '' ?>>Fix</option>
                    </select>
                </div>
        
                <!-- Start Date -->
                <div class="col-md-2 field">
                    <label for="start_date" class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                </div>
        
                <!-- End Date -->
                <div class="col-md-2 field">
                    <label for="end_date" class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
                </div>
        
                <!-- Search Button -->
                <div class="col-md-2">
                    <button type="submit" name="generate_report" class="btn sub-btn1 mt-4">Search</button>
                </div>
        
                <!-- Add New Button -->
                <div class="col-md-1">
                    <a href="expense-form.php?action=add" class="btn sub-btn1 mt-4">ADD NEW</a>
                </div>
        
                <!-- Admin Controls -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
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
                        <a href="expense-finance" class="btn sub-btn1 mt-4">Finance</a>
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
            $mv_number = isset($_POST['mv_number']) ? trim($_POST['mv_number']) : (isset($_GET['mv_number']) ? trim($_GET['mv_number']) : '');
            $vehicle = isset($_POST['vehicle']) ? trim($_POST['vehicle']) : (isset($_GET['vehicle']) ? trim($_GET['vehicle']) : '');
            $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : (isset($_GET['start_date']) ? trim($_GET['start_date']) : '');
            $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : (isset($_GET['end_date']) ? trim($_GET['end_date']) : '');
            $expense_status = isset($_POST['expense_status']) ? trim($_POST['expense_status']) : (isset($_GET['expense_status']) ? trim($_GET['expense_status']) : '');
            
            // Get current page number from the query string, default to 1
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 10; // Records per page

            // total count variable
            $total_records = 0;
            $total_amount = 0;
            $ride_km_difference = 0;
            $offset = ($items_per_page === 'all') ? 0 : ($current_page - 1) * $items_per_page;
            
            // $items_per_page = 10; // Number of entries per page
            // $offset = ($current_page - 1) * $items_per_page;
            
            $sortColumn = 'reg_num';
            $order = 'DESC'; // Default is descending order
            
            
                // Check if a sorting option has been selected
                if (isset($_GET['sort'])) {
                    if ($_GET['sort'] === 'policy_date') {
                        $sortColumn = 'policy_date';
                    } elseif ($_GET['sort'] === 'reg_num') {
                        $sortColumn = 'reg_num';
                    }
                }
            
           // Prepare the SQL query based on the search input
        if (!empty($search_query) || !empty($start_date) || !empty($end_date) || !empty($mv_number) || !empty($vehicle)) {
            // Convert date format if necessary
            if (!empty($start_date)) {
                $start_date = date('Y-m-d', strtotime($start_date));
            }
            if (!empty($end_date)) {
                $end_date = date('Y-m-d', strtotime($end_date));
            }
        
            $sql = "SELECT * FROM `expenses` WHERE is_deleted = 0";
            $params = [];
            $param_types = '';

            // Add conditions to the SQL query
            $conditions = [];

            if (!empty($search_query)) {
                $conditions[] = "expense_type = ?";
                $params[] = $search_query;
                $param_types .= 's';
            }

            if (!empty($mv_number)) {
                $conditions[] = "mv_num LIKE ?";
                $params[] = "%$mv_number%";
                $param_types .= 's';
            }

            if (!empty($vehicle)) {
                $conditions[] = "vehicle_type LIKE ?";
                $params[] = "%$vehicle%";
                $param_types .= 's';
            }

            if (!empty($expense_status)) {
                $conditions[] = "expense_status LIKE ?";
                $params[] = "%$expense_status%";
                $param_types .= 's';
            }

            if (!empty($start_date) && !empty($end_date)) {
                $conditions[] = "policy_date BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
                $param_types .= 'ss';
            } elseif (!empty($start_date)) {
                $conditions[] = "policy_date >= ?";
                $params[] = $start_date;
                $param_types .= 's';
            } elseif (!empty($end_date)) {
                $conditions[] = "policy_date <= ?";
                $params[] = $end_date;
                $param_types .= 's';
            }

            // Combine all conditions with AND
            if (count($conditions) > 0) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            // Add ordering and pagination
            if ($items_per_page === 'all') {
                $sql .= " ORDER BY $sortColumn $order";
            } else {
                $offset = ($current_page - 1) * $items_per_page;
                $sql .= " ORDER BY $sortColumn $order LIMIT ?, ?";
                $params[] = $offset;
                $params[] = $items_per_page;
                $param_types .= 'ii';
            }

            // Debugging output: Check the generated SQL query
            // echo "Generated SQL Query: " . $sql . "<br>";

            // Execute the main query
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("MySQL error: " . $conn->error);  // Print detailed MySQL error
            }

            if ($param_types) {
                $stmt->bind_param($param_types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            // Prepare the count query for total records and total amount
            $count_sql = "SELECT COUNT(*) as total, SUM(amount) as total_amount FROM `expenses` WHERE is_deleted = 0";
            $count_params = [];
            $count_param_types = '';

            // Apply the same filters to the count query
            if (!empty($search_query)) {
                $count_sql .= " AND expense_type = ?";
                $count_params[] = $search_query;
                $count_param_types .= 's';
            }

            if (!empty($mv_number)) {
                $count_sql .= " AND mv_num LIKE ?";
                $count_params[] = "%$mv_number%";
                $count_param_types .= 's';
            }

            if (!empty($vehicle)) {
                $count_sql .= " AND vehicle_type LIKE ?";
                $count_params[] = "%$vehicle%";
                $count_param_types .= 's';
            }

            if (!empty($expense_status)) {
                $count_sql .= " AND expense_status LIKE ?";
                $count_params[] = "%$expense_status%";
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

            // Execute the count query
            $count_stmt = $conn->prepare($count_sql);
            if ($count_stmt === false) {
                die("MySQL error: " . $conn->error);  // Print detailed MySQL error
            }

            if ($count_param_types) {
                $count_stmt->bind_param($count_param_types, ...$count_params);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            $total_records = $count_data['total'];
            $total_amount = $count_data['total_amount'] ?? 0;

            // Calculate total pages
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
            header('Content-Disposition: attachment; filename=expense-report.csv');
        
            // Open the output stream to write CSV data
            $output = fopen('php://output', 'w');
        
            // Add the header row to the CSV file
            fputcsv($output, ['Reg Num', 'Date','MV No','Vehicle Type','KM','Amount', 'Type of Expense', 'Payment Mode']);
        
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                $sql = "SELECT * FROM expenses WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM expenses";
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
                        $item['mv_num'],
                        $item['vehicle_type'],
                        $item['ride_km'],
                        $item['amount'],
                        $item['expense_type'],
                        $item['pay_mode'],
                    
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
            $pdf->Cell(70, 10, 'MV No', 1);
            $pdf->Cell(70, 10, 'Vehicle Type', 1);
            $pdf->Cell(70, 10, 'KM', 1);
            $pdf->Cell(70, 10, 'Amount', 1);
            $pdf->Cell(70, 10, 'Type of Expense', 1);
            $pdf->Cell(30, 10, 'Payment Mode', 1);
           
            
            $pdf->Ln();
            
            // Get start_date and end_date from POST
            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
            
            // Prepare dynamic SQL query for date range if provided
            if (!empty($start_date) && !empty($end_date)) {
                // Format dates for SQL query
                $start_date = date('Y-m-d', strtotime($start_date));  // Ensure the date format is correct
                $end_date = date('Y-m-d', strtotime($end_date));
                
                $sql = "SELECT * FROM expenses WHERE policy_date BETWEEN ? AND ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
            } else {
                // If no date range is set, use default query
                $sql = "SELECT * FROM expenses";
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
                    $pdf->Cell(70, 10, $item['mv_num'], 1);
                    $pdf->Cell(70, 10, $item['vehicle_type'], 1);
                    $pdf->Cell(70, 10, $item['ride_km'], 1);
                    $pdf->Cell(70, 10, $item['amount'], 1);
                    $pdf->Cell(70, 10, $item['expense_type'], 1);
                    $pdf->Cell(30, 10, $item['pay_mode'], 1);

                    $pdf->Ln();
                }
            } else {
                // No data found, display a message in the PDF
                $pdf->Cell(0, 10, 'No records found for the selected date range.', 0, 1, 'C');
            }
            
            // Output the PDF (force download)
            $pdf->Output('D', 'expense-report.pdf'); // Force download
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
        
        
        <hr></hr>
        
      
        
     <div id="reportSection">  
        
        <div class="heading">
            <?php
                
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "<h1 class='text-center'>EXPENSE REPORT FOR $formatted_start_date TO $formatted_end_date </h1>"
                
            ?>
        </div>   
        

        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Reg No.</th>
                    <th scope="col">Date </th>
                    <th scope="col">MV No/Vehicle Type</th>
                    <th scope="col">KM</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Type of Expense</th>
                    
                    <th scope="col">Expense Status</th>
                    
                    <th scope="col">Payment Mode</th>
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
                            <th scope="row"><?php echo $serial_number++; ?></th>
                            <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                            <td>
                                <?php 
                                $original_date = $row['policy_date']; // date in YYYY-MM-DD format from database
                                $formatted_date = DateTime::createFromFormat('Y-m-d', $original_date)->format('d/m/Y'); // format to DD/MM/YYYY
                                echo htmlspecialchars($formatted_date); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['mv_num']); ?> <br> <?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                            <td><?php //echo htmlspecialchars($row['fuel']); ?>  <?php echo htmlspecialchars($row['ride_km']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            
                            <td><?php echo htmlspecialchars($row['expense_type']); ?></td>
                            
                            <td><?php echo htmlspecialchars($row['expense_status']); ?></td>
                            
                            <td><?php echo htmlspecialchars($row['pay_mode']); ?></td>
                            

                        
        
                            <td class="action-col">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                                    <a href="expense-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    &nbsp;/&nbsp;
                                    <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#passwordModal" data-item-id="<?php echo $row['id']; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <!--&nbsp;/&nbsp;-->
                                <?php endif; ?>
                                
                                <!--<a href="expense-form.php?action=add_new&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Add New">-->
                                <!--    <i class="fa-solid fa-plus"></i>-->
                                <!--</a>-->
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='10'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        
        <?php 
            
            // Output results
                
                echo "<h3>Summary : </h3>";
                
                echo "Total Records: <strong>$total_records</strong><br>";
                echo "Total Amount: <strong>$total_amount</strong><br>";
                // echo "Total KM (Ride): <strong>$ride_km_difference</strong><br>";

        ?>
        
    </div>
        
        <!-- Pagination Links -->
        <?php if (isset($total_pages) && $total_pages > 1) : ?>
            <nav aria-label="Page navigation">
                <ul class="pagination neumorphic-pagination">
                    
                    <!-- Add the "Show All" button -->
                    <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
                        <a class="page-link" href="?page=1&items_per_page=all<?= !empty($search_query) ? '&search_query=' . urlencode($search_query) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?><?= !empty($start_date) ? '&start_date=' . htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date=' . htmlspecialchars($end_date) : '' ?>">Show All</a>
                        
                    </li>
                    
                    <?php if ($current_page > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>&items_per_page=<?= urlencode($items_per_page) ?><?= !empty($search_query) ? '&search_query=' . urlencode($search_query) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?><?= !empty($start_date) ? '&start_date=' . htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date=' . htmlspecialchars($end_date) : '' ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                        </li>
                    <?php endif; ?>
        
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&items_per_page=<?= urlencode($items_per_page) ?><?= !empty($search_query) ? '&search_query=' . urlencode($search_query) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?><?= !empty($start_date) ? '&start_date=' . htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date=' . htmlspecialchars($end_date) : '' ?>"><?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
        
                    <?php if ($current_page < $total_pages) : ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>&items_per_page=<?= urlencode($items_per_page) ?><?= !empty($search_query) ? '&search_query=' . urlencode($search_query) : '' ?><?= !empty($status) ? '&status=' . urlencode($status) : '' ?><?= !empty($start_date) ? '&start_date=' . htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date=' . htmlspecialchars($end_date) : '' ?>" aria-label="Next">
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


<!-- Delete Confirmation Modal -->
<!-- Password Verification Modal for delete-->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="expense-delete.php" method="POST">
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

// Autofetch details when user select mv number

    async function handleMvNumChange(mv_num) {
        if (!mv_num) {
            document.getElementById('vehicle_type').value = '';
            document.getElementById('user_name').value = '';
            return;
        }

        try {
            const response = await fetch('./fetch_vehicle_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ mv_num: mv_num })
            });

            const data = await response.json();

            if (data.success) {
                document.getElementById('vehicle_type').value = data.vehicle_type;
                document.getElementById('user_name').value = data.user_name;
            } else {
                alert("Vehicle details not found.");
                document.getElementById('vehicle_type').value = '';
                document.getElementById('user_name').value = '';
            }
        } catch (error) {
            console.error("Error fetching vehicle details:", error);
        }
    }


document.getElementById('passwordModal').addEventListener('show.bs.modal', function (event) {
    // Get the anchor element that triggered the modal
    var triggerElement = event.relatedTarget;

    // Debugging: Check the trigger element
    console.log('Trigger Element:', triggerElement);

    // Extract the item ID from the data attribute
    var itemId = triggerElement.getAttribute('data-item-id');
    console.log('Extracted Item ID:', itemId); // Debug log to confirm value

    // Find the hidden input field in the modal and set its value
    var modalItemIdInput = document.getElementById('itemId');
    if (modalItemIdInput) {
        modalItemIdInput.value = itemId;
        console.log('Item ID set in input:', modalItemIdInput.value); // Debug log to confirm input value
    } else {
        console.error('Input with ID "itemId" not found!');
    }
});

    
    // when hover on icon show tooltip

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
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

</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

<?php //} ?>