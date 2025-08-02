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
                <h1>CLIENT</h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">CLIENT</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
        

        <!-- Single Search Form -->
        <?php
            // Define all form variables using GET method (since method="GET")
            $search_query = $_GET['search_query'] ?? '';
            $reg_num = $_GET['reg_num'] ?? '';
            $min_age = $_GET['min_age'] ?? '';
            $max_age = $_GET['max_age'] ?? '';
            $address_value = $_GET['address'] ?? '';
            $pincode = $_GET['pincode'] ?? '';
            $tag = $_GET['tag'] ?? '';
            $table_select = $_GET['table_select'] ?? '';
            $inquiry_value = $_GET['inquiry'] ?? '';
            $start_date = $_GET['start_date'] ?? '2024-10-10';
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            
            // Fetch address and inquiry options
            include 'includes/db_conn.php';
            
            $address_sql = "SELECT DISTINCT address FROM client WHERE is_deleted = 0 ORDER BY address ASC";
            $address_result = $conn->query($address_sql);
            $address_options = [];
            while ($row = $address_result->fetch_assoc()) {
                $address_options[] = $row['address'];
            }
            
            $inquiry_sql = "SELECT DISTINCT inquiry FROM client WHERE is_deleted = 0 ORDER BY inquiry ASC";
            $inquiry_result = $conn->query($inquiry_sql);
            $inquiry_options = [];
            while ($row = $inquiry_result->fetch_assoc()) {
                $inquiry_options[] = $row['inquiry'];
            }
        ?>
        
        <form method="GET" class="p-3">
            <div class="row">
                <div class="col-md-3 field">
                    <label class="form-label">Search by Name, Mobile, Aadhar, Pan no:</label>
                    <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="by Name, Mobile, Aadhar, Pan no" />
                </div>
        
                <div class="col-md-1 field">
                    <label class="form-label">Search Reg</label>
                    <input type="text" name="reg_num" class="form-control" value="<?= htmlspecialchars($reg_num) ?>" />
                </div>
        
                <div class="col-md-1 field">
                    <label class="form-label">Min Age :</label>
                    <input type="number" name="min_age" class="form-control" value="<?= htmlspecialchars($min_age) ?>" placeholder="Min Age" />
                </div>
        
                <div class="col-md-1 field">
                    <label class="form-label">Max Age :</label>
                    <input type="number" name="max_age" class="form-control" value="<?= htmlspecialchars($max_age) ?>" placeholder="Max Age" />
                </div>
        
                <div class="col-md-2 field">
                    <label class="form-label">Address :</label>
                    <select name="address" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($address_options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= ($address_value === $option) ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
        
                <div class="col-md-2 field">
                    <label class="form-label">Pincode :</label>
                    <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($pincode) ?>" placeholder="by Pincode" />
                </div>
        
                <div class="col-md-1 field">
                    <label class="form-label">Tag :</label>
                    <select name="tag" class="form-control">
                        <option value="">All</option>
                        <option value="A" <?= ($tag === 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= ($tag === 'B') ? 'selected' : '' ?>>B</option>
                        <option value="C" <?= ($tag === 'C') ? 'selected' : '' ?>>C</option>
                    </select>
                </div>
        
                <div class="col-md-1 field">
                    <label class="form-label">Select Table:</label>
                    <select name="table_select" class="form-control">
                        <option value="">All</option>
                        <option value="gic" <?= ($table_select === 'gic') ? 'selected' : '' ?>>GIC</option>
                        <option value="lic" <?= ($table_select === 'lic') ? 'selected' : '' ?>>LIC</option>
                        <option value="mf" <?= ($table_select === 'mf') ? 'selected' : '' ?>>MF</option>
                        <option value="rto" <?= ($table_select === 'rto') ? 'selected' : '' ?>>RTO</option>
                        <option value="bmds" <?= ($table_select === 'bmds') ? 'selected' : '' ?>>BMDS</option>
                    </select>
                </div>
        
                <div class="col-md-2 field">
                    <label class="form-label">Inquiry For :</label>
                    <select name="inquiry" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($inquiry_options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= ($inquiry_value === $option) ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
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
                    <button type="submit" name="generate_report" class="btn sub-btn1 mt-4">Search</button>
                </div>
        
                <div class="col-md-1">
                    <a href="client-form.php?action=add_new" class="btn sub-btn1 mt-4">ADD NEW</a>
                </div>
        
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal">EXCEL</button>
                    </div>
        
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" data-bs-toggle="modal" data-bs-target="#passwordModal1">PDF</button>
                    </div>
        
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                    </div>
        
                    <div class="col-md-1">
                        <a href="birthday" class="btn sub-btn1 mt-4">BIRTHDAY</a>
                    </div>
        
                    <div class="col-md-1">
                        <a href="anniversary" class="btn sub-btn1 mt-4">ANNIVERSARY</a>
                    </div>

                    <div class="col-md-1">
                        <a href="send_whatsapp" class="btn sub-btn1 mt-4">Whatsapp Msg</a>
                    </div>
        
                    <div class="col-md-1">
                        <button type="button" class="btn sub-btn1 mt-4" onclick="copyContacts()">Copy Contacts</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn sub-btn1 mt-4" id="toggleContact" type="button">Contact Hide</button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
                
<?php 
     
        
        // Database connection
include 'includes/db_conn.php';

// Get current page number, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = isset($_GET['items_per_page']) && $_GET['items_per_page'] === 'all' ? 'all' : 100; // Records per page

// Get search parameters (Supports both GET & POST)
$search_query = isset($_REQUEST['search_query']) ? trim($_REQUEST['search_query']) : '';
$reg_num = isset($_REQUEST['reg_num']) ? trim($_REQUEST['reg_num']) : '';
$inquiry = isset($_REQUEST['inquiry']) ? trim($_REQUEST['inquiry']) : '';
$start_date = isset($_REQUEST['start_date']) ? trim($_REQUEST['start_date']) : '';
$end_date = isset($_REQUEST['end_date']) ? trim($_REQUEST['end_date']) : '';
$tag = isset($_REQUEST['tag']) ? trim($_REQUEST['tag']) : '';
$min_age = isset($_REQUEST['min_age']) ? (int)$_REQUEST['min_age'] : '';
$max_age = isset($_REQUEST['max_age']) ? (int)$_REQUEST['max_age'] : '';
$address = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';
$pincode = isset($_REQUEST['pincode']) ? trim($_REQUEST['pincode']) : '';
$total_records = 0;
// Initialize SQL Query
$sql = "SELECT * FROM `client` WHERE is_deleted = 0";
$params = [];
$param_types = '';

if (!empty($search_query) || !empty($start_date) || !empty($end_date) || !empty($min_age) || !empty($max_age) || !empty($address) || !empty($inquiry) || !empty($table_select) || !empty($pincode) || !empty($reg_num)) {

// Convert date format if necessary
if (!empty($start_date)) {
    $start_date = date('Y-m-d', strtotime($start_date));
}
if (!empty($end_date)) {
    $end_date = date('Y-m-d', strtotime($end_date));
}

// ** Apply Filters ** //
// ** Apply Filters ** //
if (!empty($search_query)) {
    // Split search query into words
    $words = explode(' ', trim($search_query));
    $name_conditions = [];
    
    foreach ($words as $word) {
        $name_conditions[] = "client_name LIKE ?";
        $params[] = "%$word%";
        $param_types .= 's';
    }

    // Combine conditions using AND to ensure all words are matched in the name
    $sql .= " AND (" . implode(" AND ", $name_conditions) . " 
            OR contact LIKE ? 
            OR contact_alt LIKE ? 
            OR pan_no LIKE ? 
            OR aadhar_no LIKE ?
            )";

    // Add parameters for non-name fields
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ssss';
}

// Other Filters
if (!empty($reg_num) && is_numeric($reg_num)) {
    $sql .= " AND reg_num = ?";
    $params[] = (int)$reg_num;
    $param_types .= 'i';
}



if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND policy_date BETWEEN ? AND ?";
    array_push($params, $start_date, $end_date);
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

// Age Filter
$currentDate = new DateTime();
if (!empty($min_age)) {
    $minDate = clone $currentDate;
    $minDate->modify('-' . intval($min_age) . ' years');
    $minDateFormatted = $minDate->format('Y-m-d');
    $sql .= " AND birth_date <= ?";
    $params[] = $minDateFormatted;
    $param_types .= 's';
}
if (!empty($max_age)) {
    $maxDate = clone $currentDate;
    $maxDate->modify('-' . (intval($max_age) + 1) . ' years')->modify('+1 day');
    $maxDateFormatted = $maxDate->format('Y-m-d');
    $sql .= " AND birth_date >= ?";
    $params[] = $maxDateFormatted;
    $param_types .= 's';
}

// Other Filters
if (!empty($address)) {
    $sql .= " AND address LIKE ?";
    $params[] = "%$address%";
    $param_types .= 's';
}
if (!empty($pincode)) {
    $sql .= " AND pincode LIKE ?";
    $params[] = "%$pincode%";
    $param_types .= 's';
}
if (!empty($inquiry)) {
    $sql .= " AND inquiry LIKE ?";
    $params[] = "%$inquiry%";
    $param_types .= 's';
}

// Tag Filter
if (!empty($tag) && in_array($tag, ['A', 'B', 'C'])) {
    $sql .= " AND tag = ?";
    $params[] = $tag;
    $param_types .= 's';
}

// Add ORDER BY to show new entries on top
$sql .= " ORDER BY id DESC"; // or use `created_at` if available

// Add LIMIT for pagination
if ($items_per_page !== 'all') {
    $offset = ($current_page - 1) * $items_per_page;
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $items_per_page;
    $param_types .= 'ii';
}

// ** Prepare & Execute Query **
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($param_types) && count($params) === strlen($param_types)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Error: SQL Prepare Failed - " . $conn->error);
}

// ** Count Total Records for Pagination **
$count_sql = "SELECT COUNT(*) as total FROM `client` WHERE is_deleted = 0";
$count_params = [];
$count_param_types = '';

// Apply Filters to the count query
if (!empty($search_query)) {
    $words = explode(' ', trim($search_query));
    $name_conditions = [];

    foreach ($words as $word) {
        $name_conditions[] = "client_name LIKE ?";
        $count_params[] = "%$word%";
        $count_param_types .= 's';
    }

    $count_sql .= " AND (" . implode(" AND ", $name_conditions) . " 
                OR contact LIKE ? 
                OR contact_alt LIKE ? 
                OR pan_no LIKE ? 
                OR aadhar_no LIKE ?
                )";


    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_param_types .= 'ssss';
}

// Other Filters
if (!empty($reg_num) && is_numeric($reg_num)) {
    $count_sql .= " AND reg_num = ?";
    $count_params[] = (int)$reg_num;
    $count_param_types .= 'i';
}


if (!empty($start_date) && !empty($end_date)) {
    $count_sql .= " AND policy_date BETWEEN ? AND ?";
    array_push($count_params, $start_date, $end_date);
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

// Age Filter
$currentDate = new DateTime();
if (!empty($min_age)) {
    $minDate = clone $currentDate;
    $minDate->modify('-' . intval($min_age) . ' years');
    $minDateFormatted = $minDate->format('Y-m-d');
    $count_sql .= " AND birth_date <= ?";
    $count_params[] = $minDateFormatted;
    $count_param_types .= 's';
}
if (!empty($max_age)) {
    $maxDate = clone $currentDate;
    $maxDate->modify('-' . (intval($max_age) + 1) . ' years')->modify('+1 day');
    $maxDateFormatted = $maxDate->format('Y-m-d');
    $count_sql .= " AND birth_date >= ?";
    $count_params[] = $maxDateFormatted;
    $count_param_types .= 's';
}

// Other Filters
if (!empty($address)) {
    $count_sql .= " AND address LIKE ?";
    $count_params[] = "%$address%";
    $count_param_types .= 's';
}
if (!empty($pincode)) {
    $count_sql .= " AND pincode LIKE ?";
    $count_params[] = "%$pincode%";
    $count_param_types .= 's';
}
if (!empty($inquiry)) {
    $count_sql .= " AND inquiry LIKE ?";
    $count_params[] = "%$inquiry%";
    $count_param_types .= 's';
}

// Tag Filter
if (!empty($tag) && in_array($tag, ['A', 'B', 'C'])) {
    $count_sql .= " AND tag = ?";
    $count_params[] = $tag;
    $count_param_types .= 's';
}

// ** Prepare & Execute Count Query **
// $count_stmt = $conn->prepare($count_sql);
// if ($count_stmt) {
//     if (!empty($count_param_types) && count($count_params) === strlen($count_param_types)) {
//         $count_stmt->bind_param($count_param_types, ...$count_params);
//     }
//     $count_stmt->execute();
//     $count_result = $count_stmt->get_result();
//     $total_records = $count_result->fetch_assoc()['total'];
//     $total_pages = ceil($total_records / $items_per_page);
// } else {
//     die("Error: SQL Count Prepare Failed - " . $conn->error);
// }

// Prepare and Execute the COUNT Query
$count_stmt = $conn->prepare($count_sql);

if ($count_stmt === false) {
    die('Error preparing query: ' . $conn->error);
}

if (!empty($count_param_types)) {
    // Bind parameters for COUNT query
    $count_stmt->bind_param($count_param_types, ...$count_params);
}





$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();

// Extract the Values for pagination
$total_records = $count_data['total'];


$total_pages = $items_per_page === 'all' ? 1 : ceil($total_records / $items_per_page);  

// Check if the data was returned
if ($result->num_rows > 0) {
    // Process your results here (e.g., display the entries)
} else {
    // No records found
    echo "No entries found for the selected filters.";
}

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
                header('Content-Disposition: attachment; filename=client-report.csv');
    
                // Open the output stream
                $output = fopen('php://output', 'w');
    
                // Add CSV header row
                fputcsv($output, ['Sr.No', 'Date', 'Address', 'DOB', 'Age', 'Client Name', 'Contact', 'Alt Contact', 'Email']);
    
                // Fetch data from the database
                $sql = "SELECT * FROM client";
                if (!empty($start_date) && !empty($end_date)) {
                    $sql .= " WHERE policy_date BETWEEN ? AND ?";
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
                            $row['reg_num'],
                            (new DateTime($row['policy_date']))->format('d/m/Y'),
                            $row['address'],
                            $row['birth_date'],
                            $row['age'],
                            $row['client_name'],
                            $row['contact'],
                            $row['contact_alt'],
                            $row['email'],
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
                $pdf->Cell(0, 10, 'Client Report', 0, 1, 'C');
                
                // Add column headers
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(20, 10, 'Sr.No', 1);
                $pdf->Cell(25, 10, 'Date', 1);
                $pdf->Cell(100, 10, 'Client Name', 1);
                $pdf->Cell(70, 10, 'Address', 1);
                $pdf->Cell(30, 10, 'DOB', 1);
                $pdf->Cell(30, 10, 'Age', 1);
                $pdf->Cell(30, 10, 'Contact', 1);
                $pdf->Cell(30, 10, 'Alt Contact', 1);
                $pdf->Cell(70, 10, 'Email', 1);

                $pdf->Ln();
                
                // Get start_date and end_date from POST
                $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
                $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
                
                // Prepare dynamic SQL query for date range if provided
                if (!empty($start_date) && !empty($end_date)) {
                    // Format dates for SQL query
                    $start_date = date('Y-m-d', strtotime($start_date));  // Ensure the date format is correct
                    $end_date = date('Y-m-d', strtotime($end_date));
                    
                    $sql = "SELECT * FROM client WHERE policy_date BETWEEN ? AND ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $start_date, $end_date); // Bind parameters for date range
                } else {
                    // If no date range is set, use default query
                    $sql = "SELECT * FROM client";
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
                        $pdf->Cell(100, 10, $item['client_name'], 1);
                        $pdf->Cell(70, 10, $item['address'], 1);
                        $pdf->Cell(30, 10, $item['birth_date'], 1);
                        $pdf->Cell(30, 10, $item['age'], 1);
                        $pdf->Cell(30, 10, $item['contact'], 1);
                        $pdf->Cell(30, 10, $item['contact_alt'], 1);
                        $pdf->Cell(70, 10, $item['email'], 1);
                        
                        
                        $pdf->Ln();
                    }
                } else {
                    // No data found, display a message in the PDF
                    $pdf->Cell(0, 10, 'No records found for the selected date range.', 0, 1, 'C');
                }
                
                // Output the PDF (force download)
                $pdf->Output('D', 'client-report.pdf'); // Force download
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
                echo "<h1 class='text-center'>CLIENT REPORT FOR $formatted_start_date TO $formatted_end_date </h1>"
                
            ?>
        </div>
        
        <div><h1>Total Clients: <strong><?php echo $total_records; ?></strong></h1></div>

        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">ID</th>
                    <th scope="col">Date</th>
                    <th scope="col">Reg No</th>
                    <th scope="col">Client Name</th>
                    <th scope="col">Address</th>
                    <th scope="col">DOB</th>
                    <th scope="col">Age</th>
                    <th class="contact-data" scope="col">Contact</th>
                    <th class="contact-data" scope="col">Alt Contact</th>
                    <th scope="col">Email</th>
                    <th scope="col">Tag</th>
                    <th scope="col">Inquiry For</th>
                    <th scope="col" class="action-col">Available In</th>
                    <th scope="col" class="action-col">Action</th>
                    <th scope="col" class="action-col">Job Type</th>
                </tr>
            </thead>
            <tbody>
    <?php 
    include 'includes/db_conn.php';

    if (isset($result) && $result->num_rows > 0) {
        // $serial_number = $offset + 1; // Initialize serial number
        $srNo = 1; // Initialize serial number
        
        while ($row = $result->fetch_assoc()) {
            // Query to check which tables contain the client
            $client_id = $row['id'];
            $tables = ['lic_entries' => 'LIC', 
                        'gic_entries' => 'GIC', 
                        'bmds_entries' => 'BMDS', 
                        'rto_entries' => 'RTO', 
                        'mf_entries' => 'MF'];
            $found_tables = [];

            // Loop through each table to check if the client exists
            foreach ($tables as $table => $table_name) {
                $sql = "SELECT 1 FROM `$table` WHERE `client_id` = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $client_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $found_tables[] = $table_name; // Add table name to the array
                }
                $stmt->close();
            }
            ?>
            <tr>
                <th scope="row"><?= $srNo++ ?></th>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td>
                    <?php 
                    $original_date = isset($row['policy_date']) ? $row['policy_date'] : ''; // date in YYYY-MM-DD format from database
                    $formatted_date = !empty($original_date) ? DateTime::createFromFormat('Y-m-d', $original_date)->format('d/m/Y') : '--'; // format to DD/MM/YYYY
                    echo htmlspecialchars($formatted_date); 
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['reg_num']); ?></td>
                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); echo '<br>'; echo htmlspecialchars($row['pincode']);?></td>

                <!-- formatted DOB -->
                <td>
                    <?php 
                    $date = isset($row['birth_date']) ? $row['birth_date'] : ''; // Ensure birth_date exists
                    if ($date == '0000-00-00' || empty($date)) {
                        // Handle the invalid date, for example, use NULL or a default date
                        echo "--";
                    } else {
                        // Format the valid date
                        echo date("d-m-Y", strtotime($date));
                    }
                    ?>
                </td>
                    
                <!-- show age using DOB  -->
                <td>
                    <?php 
                    $dob = isset($row['birth_date']) ? $row['birth_date'] : ''; // Ensure birth_date exists
                    if ($dob == '0000-00-00' || empty($dob)) {
                        // Handle invalid date
                        echo "--";
                    } else {
                        // Format the valid s
                        $formattedDate = date("d-m-Y", strtotime($dob));
                        

                        // Calculate age
                        $dobDateTime = new DateTime($dob);
                        $currentDate = new DateTime();
                        $age = $currentDate->diff($dobDateTime)->y;

                        // Display age in parentheses
                        echo  $age;
                    }
                    ?>
                </td>

                <!-- <td><?php //echo htmlspecialchars($row['age']); ?></td> -->
                <td class="contact-data"><?php echo isset($row['contact']) ? htmlspecialchars($row['contact']) : '--'; ?></td>
                <td class="contact-data"><?php echo isset($row['contact_alt']) ? htmlspecialchars($row['contact_alt']) : '--'; ?></td>
                <td><?php echo isset($row['email']) ? htmlspecialchars($row['email']) : '--'; ?></td>
                <td><?php echo isset($row['tag']) ? htmlspecialchars($row['tag']) : '--'; ?></td>
                <td><?php echo isset($row['inquiry']) ? htmlspecialchars($row['inquiry']) : '--'; ?></td>
                <td class="action-col">
                    <!-- Display the tables where the client is found -->
                    <?php 
                    if (!empty($found_tables)) {
                        echo implode(', ', $found_tables);
                    } else {
                        echo "Not Registered.";
                    }
                    ?>
                </td>

                <td class="action-col">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                        <a href="client-form.php?action=edit&id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a> 

                        /

                        <a class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#deleteModal" data-item-id="<?php echo $row['id']; ?>">
                            <i class="fa-solid fa-trash"></i>
                        </a>

                    <?php endif; ?>
                </td>

                <td class="action-col">
                    <div class="form-group field ">
                        <select id="inputState" class="form-control" onchange="navigateToJob(this)">
                            <option selected>SELECT JOB</option>
                            <option value="gic-form.php?action=add_client&id=<?= $row['id']; ?>">GIC</option>
                            <option value="lic-form.php?action=add_client&id=<?= $row['id']; ?>">LIC</option>
                            <option value="rto-form.php?action=add_client&id=<?= $row['id']; ?>">RTO</option>
                            <option value="bmds-form.php?action=add_client&id=<?= $row['id']; ?>">BMDS</option>
                            <option value="mf-form.php?action=add_client&id=<?= $row['id']; ?>">MF</option>
                        </select>
                    </div>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='16'>No records found.</td></tr>";
    }
    ?>
</tbody>

        </table>
    </div>
        
        <!-- Pagination Links -->
        <?php if (isset($total_pages) && $total_pages > 1) : ?>
            <nav aria-label="Page navigation">
                <ul class="pagination neumorphic-pagination">
                    <?php
                    // Preserve search parameters for pagination links
                    $query_params = [];

                    foreach ($_GET as $key => $value) {
                        // Accept only known, valid keys
                        if (in_array($key, [
                            'reg_num', 'min_age', 'max_age', 'address', 'pincode',
                            'tag', 'table_select', 'inquiry', 'start_date', 'end_date',
                            'generate_report', 'page'
                        ])) {
                            $query_params[$key] = $value;
                        }
                    }
                    // Get all existing GET parameters
                    unset($query_params['page']); // Remove old page number

                    function buildPageUrl($page)
    {
        global $query_params;
        $query_params['page'] = $page;
        $query_params['items_per_page'] = $_GET['items_per_page'] ?? 100;
        return '?' . http_build_query($query_params);
    }

                    // Function to build "Show All" URL
    function buildShowAllUrl()
    {
        global $query_params;
        $query_params['page'] = 1;
        $query_params['items_per_page'] = 'all';
        return '?' . http_build_query($query_params);
    }

                    // Define range of pages to display
                    $max_visible_pages = 10; // Show 10 pages at a time
                    $start_page = max(1, $current_page - floor($max_visible_pages / 2));
                    $end_page = min($total_pages, $start_page + $max_visible_pages - 1);

                    // Ensure the pagination is correctly adjusted when near the start or end
                    if ($end_page - $start_page < $max_visible_pages - 1) {
                        $start_page = max(1, $end_page - $max_visible_pages + 1);
                    }
                    ?>

                    <!-- Show All Button -->
        <li class="page-item <?= ($items_per_page === 'all') ? 'active' : '' ?>">
            <a class="page-link" href="<?= buildShowAllUrl() ?>">Show All</a>
        </li>


                    <!-- First Page Link -->
                    <?php if ($current_page > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildPageUrl(1) ?>" aria-label="First">First</a>
                        </li>
                    <?php endif; ?>

                    <!-- Previous Page Link -->
                    <?php if ($current_page > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildPageUrl($current_page - 1) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Show "..." before first visible page if necessary -->
                    <?php if ($start_page > 1) : ?>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                    <?php endif; ?>

                    <!-- Display limited page numbers -->
                    <?php for ($i = $start_page; $i <= $end_page; $i++) :
                        $active_class = ($i === $current_page) ? 'active' : '';
                    ?>
                        <li class="page-item <?= $active_class ?>">
                            <a class="page-link" href="<?= buildPageUrl($i) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Show "..." after last visible page if necessary -->
                    <?php if ($end_page < $total_pages) : ?>
                        <li class="page-item disabled">
                            <a class="page-link">...</a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Page Link -->
                    <?php if ($current_page < $total_pages) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildPageUrl($current_page + 1) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Last Page Link -->
                    <?php if ($current_page < $total_pages) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildPageUrl($total_pages) ?>" aria-label="Last">Last</a>
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

<!-- PDF Download Modal for Entering Password -->
<div class="modal fade" id="passwordModal1" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
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

<!-- Password Verification Modal for delete-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="passwordModalLabel">Password Verification</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" action="client-delete.php" method="POST">
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



<script>
    function copyContacts() {
    const contactMap = new Map();
    const invalidContacts = [];
    const validContacts = [];
    let duplicateCount = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        // Get primary and alternate contacts
        const primaryContact = row.querySelector('td:nth-child(9)')?.innerText.trim();
        const altContact = row.querySelector('td:nth-child(10)')?.innerText.trim();

        [primaryContact, altContact].forEach(contact => {
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


    // script for delete id

document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
    // Get the anchor element that triggered the modal
    var triggerElement = event.relatedTarget;
    
    // Extract the item ID from the data attribute
    var itemId = triggerElement.getAttribute('data-item-id');
    
    // Find the hidden input field in the modal and set its value
    var modalItemIdInput = document.getElementById('itemId');
    modalItemIdInput.value = itemId;
});
</script>


<script>

// script for redirection on form 

function navigateToJob(select) {
    const url = select.value;
    if (url !== "SELECT JOB") {
        window.location.href = url;
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

 // Pdf download Close modal and refresh page when "Download" button is clicked
 document.getElementById('downloadpdf').addEventListener('click', function () {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal1'));
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
    include 'include/footer.php';
    include 'include/header1.php'; 
    
?>

<?php //} ?>