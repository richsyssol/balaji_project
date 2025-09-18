<?php 
include 'session_check.php';
ob_start();

?>  

<style>
    #companyReport, #clientReport {
        display: block; /* default: show both */
    }
</style>


<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';

// Initialize search query to prevent warnings
$search_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';

// Fetch unique company names from the database
$companyQuery = "SELECT DISTINCT policy_company FROM gic_entries WHERE policy_company IS NOT NULL AND policy_company != '' ORDER BY policy_company";
$companyResult = $conn->query($companyQuery);

// Fetch unique policy types from the database
$policyTypeQuery = "SELECT DISTINCT policy_type FROM gic_entries WHERE policy_type IS NOT NULL AND policy_type != '' ORDER BY policy_type";
$policyTypeResult = $conn->query($policyTypeQuery);

// Fetch unique motor vehicle types from the database
$vehicleTypeQuery = "SELECT DISTINCT vehicle_type FROM gic_entries WHERE vehicle_type IS NOT NULL AND vehicle_type != '' ORDER BY vehicle_type";
$vehicleTypeResult = $conn->query($vehicleTypeQuery);

// Fetch unique motor subtype types from the database
$subtypeTypeQuery = "SELECT DISTINCT sub_type FROM gic_entries WHERE sub_type IS NOT NULL AND sub_type != '' ORDER BY sub_type";
$subtypeTypeResult = $conn->query($subtypeTypeQuery);

// Fetch unique nonmotor sub types from the database
$nonmotorSubtypeQuery = "SELECT DISTINCT nonmotor_subtype_select FROM gic_entries WHERE nonmotor_subtype_select IS NOT NULL AND nonmotor_subtype_select != '' ORDER BY nonmotor_subtype_select";
$nonmotorSubtypeResult = $conn->query($nonmotorSubtypeQuery);

// Initialize variables for search criteria
$policy_company = $_POST['policy_company'] ?? '';
$policy_type = $_POST['policy_type'] ?? '';
$vehicle_type = $_POST['vehicle_type'] ?? '';
$sub_type = $_POST['sub_type'] ?? '';
$nonmotor_subtype_select = $_POST['nonmotor_subtype_select'] ?? '';
$start_search_date = $_POST['start_search_date'] ?? '';
$end_search_date = $_POST['end_search_date'] ?? '';

// Initialize variables for totals
$total_entries = 0;
$total_cash = 0;
$total_online = 0;
$total_cheque = 0;
$total_cheque_count = 0;
$total_amount = 0;
$total_premium_amount = 0;
$total_recov_amount = 0;

// Execute the query if a date range is set
$results = [];

// Determine if the expiry report is requested
$isExpiryReport = isset($_POST['expiry_report']) && $_POST['expiry_report'] == '1';

// Adjust the column to filter and order by based on the report type
$dateColumn = $isExpiryReport ? 'end_date' : 'policy_date';

// Default order for both expiry report and regular report: reg_num ASC
$orderByClause = "ORDER BY reg_num ASC";

// Check if a sorting option has been selected
if (isset($_POST['sort'])) {
    $sortOption = $_POST['sort'];
    switch ($sortOption) {
        case 'reg_num_asc':
            $sortColumn = 'reg_num';
            $order = 'ASC';
            break;
        case 'reg_num_desc':
            $sortColumn = 'reg_num';
            $order = 'DESC';
            break;
        case 'date_asc':
            $sortColumn = 'policy_date';
            $order = 'ASC';
            break;
        case 'date_desc':
            $sortColumn = 'policy_date';
            $order = 'DESC';
            break;
        case 'exp_asc':
            $sortColumn = 'end_date';
            $order = 'ASC';
            break;
        case 'exp_desc':
            $sortColumn = 'end_date';
            $order = 'DESC';
            break;
        default:
            $sortColumn = 'reg_num';
            $order = 'ASC';
            break;
    }
    $orderByClause = "ORDER BY $sortColumn $order";
} 

// Force sorting by end_date ASC if expiry report is requested
if ($isExpiryReport) {
    $orderByClause = "ORDER BY end_date ASC";
}

// Check if start_date and end_date are provided
if ($start_search_date && $end_search_date) {
    // Convert dates to proper format for comparison
    $start_date_formatted = date('Y-m-d', strtotime($start_search_date));
    $end_date_formatted = date('Y-m-d', strtotime($end_search_date));
    
    // Main query to fetch policy entries and amounts
    $query = "
        SELECT 
            policy_company,
            policy_type,
            vehicle_type,
            sub_type,
            nonmotor_subtype_select,
            COUNT(*) AS total_entries,
            SUM(CASE WHEN pay_mode = 'Cash' THEN amount ELSE 0 END) AS Cash,
            SUM(CASE WHEN pay_mode = 'Online' THEN amount ELSE 0 END) AS Online,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN amount ELSE 0 END) AS Cheque,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN 1 ELSE 0 END) AS Cheque_count,
            SUM(amount) AS total_amount,
            SUM(recov_amount) AS total_recov_amount
        FROM 
            gic_entries
        WHERE 
            " . ($isExpiryReport ? 
                // For expiry reports, find policies that expire in the selected period
                // For normal policies (1 year) or the final year of long-term policies
                "(
                    (policy_duration IN ('1YR', 'SHORT') AND end_date BETWEEN ? AND ?) 
                    OR 
                    (policy_duration = 'LONG' AND end_date BETWEEN ? AND ?)
                )
                
                -- For long-term policies, also check virtual yearly expiry dates
                OR (
                    policy_duration = 'LONG' 
                    AND YEAR(end_date) - year_count + 1 <= YEAR(?) 
                    AND YEAR(end_date) >= YEAR(?)
                    AND DATE(CONCAT(YEAR(?), '-', MONTH(end_date), '-', DAY(end_date))) BETWEEN ? AND ?
                )" 
                : "policy_date BETWEEN ? AND ?") . "
            AND is_deleted = 0
            " . ($isExpiryReport ? " AND is_renewed = 0" : "") . "
            " . ($isExpiryReport ? " AND id NOT IN (SELECT renewal_of FROM gic_entries WHERE renewal_of IS NOT NULL)" : "") . "
            " . ($policy_company ? " AND policy_company = ?" : "") . 
            ($policy_type ? " AND policy_type = ?" : "") .
            ($vehicle_type ? " AND vehicle_type = ?" : "") .
            ($sub_type ? " AND sub_type = ?" : "") .
            ($nonmotor_subtype_select ? " AND nonmotor_subtype_select = ?" : "") .
            ($search_query ? " AND (client_name LIKE ? OR contact LIKE ? OR policy_number LIKE ?)" : "") . "
        GROUP BY 
            policy_company, policy_type, vehicle_type, sub_type, nonmotor_subtype_select
    ";

    // Prepare statement with dynamic binding
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error preparing query: " . $conn->error);
    }
    
    if ($isExpiryReport) {
        // For expiry reports with multi-year policy support
        $params = [
            $start_date_formatted, $end_date_formatted, // For normal policies
            $start_date_formatted, $end_date_formatted, // For final year of long-term policies
            $end_date_formatted,   // YEAR(end_date) - year_count + 1 <= YEAR(?)
            $start_date_formatted, // YEAR(end_date) >= YEAR(?)
            $end_date_formatted,   // For virtual date construction
            $start_date_formatted, $end_date_formatted  // Virtual date BETWEEN
        ];
        $types = 'sssssssss'; // 9 string parameters
    } else {
        // For regular reports based on policy application date
        $params = [$start_date_formatted, $end_date_formatted];
        $types = 'ss';
    }

    if ($policy_company) { $params[] = $policy_company; $types .= 's'; }
    if ($policy_type) { $params[] = $policy_type; $types .= 's'; }
    if ($vehicle_type) { $params[] = $vehicle_type; $types .= 's'; }
    if ($sub_type) { $params[] = $sub_type; $types .= 's'; }
    if ($nonmotor_subtype_select) { $params[] = $nonmotor_subtype_select; $types .= 's'; }
    if ($search_query) {
        $search_term = "%$search_query%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'sss';
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate overall totals
    foreach ($results as $row) {
        $total_entries += $row['total_entries'];
        $total_cash += $row['Cash'];
        $total_online += $row['Online'];
        $total_cheque += $row['Cheque'];
        $total_cheque_count += $row['Cheque_count'];
        $total_amount += $row['total_amount'];
        $total_recov_amount += $row['total_recov_amount'];
    }

    $stmt->close();
}

// Fetch client details with dynamic ORDER BY
$clientQuery = "
    SELECT 
        reg_num,
        policy_date,
        start_date,
        contact,
        client_name,
        policy_type,
        mv_number,
        vehicle,
        amount,
        policy_number,
        end_date,
        policy_company,
        vehicle_type,
        sub_type,
        nonmotor_subtype_select,
        nonmotor_type_select,
        amount,
        pay_mode,
        recov_amount,
        policy_duration,
        year_count,
        is_renewed,
        renewal_of
    FROM 
        gic_entries
    WHERE 
        " . ($isExpiryReport ? 
            // For expiry reports, find policies that expire in the selected period
            // For normal policies (1 year) or the final year of long-term policies
            "(
                (policy_duration IN ('1YR', 'SHORT') AND end_date BETWEEN ? AND ?) 
                OR 
                (policy_duration = 'LONG' AND end_date BETWEEN ? AND ?)
            )
            
            -- For long-term policies, also check virtual yearly expiry dates
            OR (
                policy_duration = 'LONG' 
                AND YEAR(end_date) - year_count + 1 <= YEAR(?) 
                AND YEAR(end_date) >= YEAR(?)
                AND DATE(CONCAT(YEAR(?), '-', MONTH(end_date), '-', DAY(end_date))) BETWEEN ? AND ?
            )" 
            : "policy_date BETWEEN ? AND ?") . "
        AND is_deleted = 0
        " . ($isExpiryReport ? " AND is_renewed = 0" : "") . "
        " . ($isExpiryReport ? " AND id NOT IN (SELECT renewal_of FROM gic_entries WHERE renewal_of IS NOT NULL)" : "") . "
        " . ($policy_company ? " AND policy_company = ?" : "") . 
        ($policy_type ? " AND policy_type = ?" : "") .
        ($vehicle_type ? " AND vehicle_type = ?" : "") .
        ($sub_type ? " AND sub_type = ?" : "") .
        ($nonmotor_subtype_select ? " AND nonmotor_subtype_select = ?" : "") .
        ($search_query ? " AND (client_name LIKE ? OR contact LIKE ? OR policy_number LIKE ?)" : "") . "
    $orderByClause
";

// Prepare and bind parameters for client query
$stmt = $conn->prepare($clientQuery);

if ($stmt === false) {
    die("Error preparing client query: " . $conn->error);
}

if ($isExpiryReport) {
    // For expiry reports with multi-year policy support
    $params = [
        $start_date_formatted, $end_date_formatted, // For normal policies
        $start_date_formatted, $end_date_formatted, // For final year of long-term policies
        $end_date_formatted,   // YEAR(end_date) - year_count + 1 <= YEAR(?)
        $start_date_formatted, // YEAR(end_date) >= YEAR(?)
        $end_date_formatted,   // For virtual date construction
        $start_date_formatted, $end_date_formatted  // Virtual date BETWEEN
    ];
    $types = 'sssssssss'; // 9 string parameters
} else {
    // For regular reports based on policy application date
    $params = [$start_date_formatted, $end_date_formatted];
    $types = 'ss';
}

if ($policy_company) { $params[] = $policy_company; $types .= 's'; }
if ($policy_type) { $params[] = $policy_type; $types .= 's'; }
if ($vehicle_type) { $params[] = $vehicle_type; $types .= 's'; }
if ($sub_type) { $params[] = $sub_type; $types .= 's'; }
if ($nonmotor_subtype_select) { $params[] = $nonmotor_subtype_select; $types .= 's'; }
if ($search_query) {
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$clientResult = $stmt->get_result();
$clientDetails = $clientResult->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
 

// CSV Download
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="gic-finance.csv"');
    $output = fopen('php://output', 'w');

    // Write headers
    fputcsv($output, ['Policy Company', 'Policy Type', 'Vehicle Type', 'NonMotor Sub Type', 'Motor Sub Type', 'Total Policy Count', 'Cash', 'Online', 'Cheque', 'Total Amount', 'Total Recovery Amount']);
    
    // Add data from $results
    foreach ($results as $row) {
        fputcsv($output, [
            $row['policy_company'],
            $row['policy_type'],
            $row['vehicle_type'],
            $row['nonmotor_subtype_select'],
            $row['sub_type'],
            $row['total_entries'],
            $row['Cash'],
            $row['Online'],
            $row['Cheque'],
            $row['total_amount'],
            $row['total_recov_amount']
        ]);
    }

    // Add data from $clientDetails
    fputcsv($output, []); // Empty line to separate sections
    fputcsv($output, ['Reg Num', 'Date', 'Contact', 'Client Name', 'Policy Type', 'MV Number', 'Vehicle', 'Amount', 'Policy Number', 'End Date', 'Policy Company', 'Vehicle Type', 'Sub Type', 'NonMotor Sub Type', 'Amount']);
    foreach ($clientDetails as $client) {
        fputcsv($output, [
            $client['reg_num'],
            $client['policy_date'],
            $client['contact'],
            $client['client_name'],
            $client['policy_type'],
            $client['mv_number'],
            $client['vehicle'],
            $client['amount'],
            $client['policy_number'],
            $client['end_date'],
            $client['policy_company'],
            $client['vehicle_type'],
            $client['sub_type'],
            $client['nonmotor_subtype_select'],
            $client['amount']
        ]);
    }

    fclose($output);
    exit();
}


// PDF Download
if (isset($_POST['download_pdf'])) {
    $pdf = new FPDF('L', 'mm', 'A3');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Financial Report', 0, 1, 'C');
    
    // Add data from $results
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Policy Company', 1);
    $pdf->Cell(40, 10, 'Policy Type', 1);
    $pdf->Cell(40, 10, 'Vehicle Type', 1);
    $pdf->Cell(40, 10, 'NonMotor Sub Type', 1);
    $pdf->Cell(40, 10, 'Motor Sub Type', 1);
    $pdf->Cell(40, 10, 'Total Policy Count', 1);
    $pdf->Cell(20, 10, 'Cash', 1);
    $pdf->Cell(20, 10, 'Online', 1);
    $pdf->Cell(20, 10, 'Cheque', 1);
    $pdf->Cell(30, 10, 'Total Amount', 1);
    $pdf->Cell(50, 10, 'Total Recovery Amount', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($results as $row) {
        $pdf->Cell(40, 10, $row['policy_company'], 1);
        $pdf->Cell(40, 10, $row['policy_type'], 1);
        $pdf->Cell(40, 10, $row['vehicle_type'], 1);
        $pdf->Cell(40, 10, $row['nonmotor_subtype_select'], 1);
        $pdf->Cell(40, 10, $row['sub_type'], 1);
        $pdf->Cell(40, 10, $row['total_entries'], 1);
        $pdf->Cell(20, 10, $row['Cash'], 1);
        $pdf->Cell(20, 10, $row['Online'], 1);
        $pdf->Cell(20, 10, $row['Cheque'], 1);
        $pdf->Cell(30, 10, $row['total_amount'], 1);
        $pdf->Cell(50, 10, $row['total_recov_amount'], 1);
        $pdf->Ln();
    }

    // Add data from $clientDetails
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'Reg Num', 1);
    $pdf->Cell(20, 10, 'Date', 1);
    $pdf->Cell(40, 10, 'Contact', 1);
    $pdf->Cell(40, 10, 'Client Name', 1);
    $pdf->Cell(30, 10, 'Policy Type', 1);
    $pdf->Cell(30, 10, 'MV Number', 1);
    $pdf->Cell(30, 10, 'Vehicle', 1);
    $pdf->Cell(20, 10, 'Amount', 1);
    $pdf->Cell(30, 10, 'Policy Number', 1);
    $pdf->Cell(30, 10, 'End Date', 1);
    $pdf->Cell(40, 10, 'Policy Company', 1);
    $pdf->Cell(30, 10, 'Vehicle Type', 1);
    $pdf->Cell(30, 10, 'Sub Type', 1);
    $pdf->Cell(30, 10, 'NonMotor Sub Type', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($clientDetails as $client) {
        $pdf->Cell(20, 10, $client['reg_num'], 1);
        $pdf->Cell(20, 10, $client['policy_date'], 1);
        $pdf->Cell(40, 10, $client['contact'], 1);
        $pdf->Cell(40, 10, $client['client_name'], 1);
        $pdf->Cell(30, 10, $client['policy_type'], 1);
        $pdf->Cell(30, 10, $client['mv_number'], 1);
        $pdf->Cell(30, 10, $client['vehicle'], 1);
        $pdf->Cell(20, 10, $client['amount'], 1);
        $pdf->Cell(30, 10, $client['policy_number'], 1);
        $pdf->Cell(30, 10, $client['end_date'], 1);
        $pdf->Cell(40, 10, $client['policy_company'], 1);
        $pdf->Cell(30, 10, $client['vehicle_type'], 1);
        $pdf->Cell(30, 10, $client['sub_type'], 1);
        $pdf->Cell(30, 10, $client['nonmotor_subtype_select'], 1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'gic-finance.pdf');
    exit();
}

?>

<?php
    include 'include/header.php'; 
    include 'include/head.php'; 
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        
        <div class="ps-5">
            <div>
                <h1>Companywise Booked Business Report</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="gic">GIC</a></li>
                <li class="breadcrumb-item active" aria-current="page">Companywise Booked Business Report</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">


        <?php
            // Handle form values
            $search_query = $_POST['search_query'] ?? '';
            $policy_company = $_POST['policy_company'] ?? '';
            $policy_type = $_POST['policy_type'] ?? '';
            $vehicle_type = $_POST['vehicle_type'] ?? '';
            $sub_type = $_POST['sub_type'] ?? '';
            $nonmotor_subtype_select = $_POST['nonmotor_subtype_select'] ?? '';
            $sort = $_POST['sort'] ?? '';

            $sortColumn = '';
            $order = '';
            if ($sort === 'reg_num_desc') {
                $sortColumn = 'reg_num';
                $order = 'DESC';
            } elseif ($sort === 'reg_num_asc') {
                $sortColumn = 'reg_num';
                $order = 'ASC';
            } elseif ($sort === 'date_desc') {
                $sortColumn = 'date';
                $order = 'DESC';
            } elseif ($sort === 'date_asc') {
                $sortColumn = 'date';
                $order = 'ASC';
            } elseif ($sort === 'exp_asc') {
                $sortColumn = 'end_date';
                $order = 'ASC';
            }

            $currentDate = date('Y-m-d');
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');

            $start_search_date = $_POST['start_search_date'] ?? $firstDayOfMonth;
            $end_search_date = $_POST['end_search_date'] ?? $lastDayOfMonth;
        ?>
        
        <form method="POST">

        
            <div class="row">
                
                <div class="col-md-3 field">
                    <label for="search_query" class="form-label">Search :</label>
                    <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="Name, Contact" />
                </div>

                <div class="col-md-2 field">
                    <label for="policy_company" class="form-label">Company Name :</label>
                    <select name="policy_company" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $companyResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['policy_company']) ?>" <?= $policy_company == $row['policy_company'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['policy_company']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-1 field">
                    <label for="policy_type" class="form-label">Policy Type :</label>
                    <select name="policy_type" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $policyTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['policy_type']) ?>" <?= $policy_type == $row['policy_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['policy_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="vehicle_type" class="form-label">Vehicle Type :</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $vehicleTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['vehicle_type']) ?>" <?= $vehicle_type == $row['vehicle_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['vehicle_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-1 field">
                    <label for="sub_type" class="form-label">SubType :</label>
                    <select name="sub_type" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $subtypeTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['sub_type']) ?>" <?= $sub_type == $row['sub_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['sub_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="nonmotor_subtype_select" class="form-label">Sub Type :</label>
                    <select name="nonmotor_subtype_select" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $nonmotorSubtypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['nonmotor_subtype_select']) ?>" <?= $nonmotor_subtype_select == $row['nonmotor_subtype_select'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nonmotor_subtype_select']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-1 field">
                    <label for="sort" class="form-label">Sort By:</label>
                    <select name="sort" class="form-control">
                        <option value="reg_num_desc" <?= ($sortColumn === 'reg_num' && $order === 'DESC') ? 'selected' : '' ?>>Reg Num (DESC)</option>
                        <option value="reg_num_asc" <?= ($sortColumn === 'reg_num' && $order === 'ASC') ? 'selected' : '' ?>>Reg Num (ASC)</option>
                        <option value="date_desc" <?= ($sortColumn === 'date' && $order === 'DESC') ? 'selected' : '' ?>>Date (DESC)</option>
                        <option value="date_asc" <?= ($sortColumn === 'date' && $order === 'ASC') ? 'selected' : '' ?>>Date (ASC)</option>
                        <option value="exp_asc" <?= ($sortColumn === 'end_date' && $order === 'ASC') ? 'selected' : '' ?>>Expiry Date (ASC)</option>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="start_search_date" class="form-label">Start Date :</label>
                    <input type="date" name="start_search_date" class="form-control" value="<?= htmlspecialchars($start_search_date); ?>" />
                </div>

                <div class="col-md-2 field">
                    <label for="end_search_date" class="form-label">End Date :</label>
                    <input type="date" name="end_search_date" class="form-control" value="<?= htmlspecialchars($end_search_date); ?>" />
                </div>
        
                <div class="col-md-1">
                    <button type="submit" class="btn sub-btn1 mt-4">Search</button>
                </div>
        
                <!-- <div class="col-md-1">
                    <button type="submit" name="download_csv" class="btn sub-btn1 mt-4">EXCEL</button>
                </div>
        
                <div class="col-md-1">
                    <button type="submit" name="download_pdf" class="btn sub-btn1 mt-4">PDF</button>
                </div> -->
        
                
                <div class="col-md-2">
                    <button type="submit" class="btn sub-btn1 mt-4 w-75" name="expiry_report" value="1">Expiry Report</button>
                </div>

                <!-- Trigger Button -->
                <div class="col-md-1">
                    <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                </div>

                <div class="col-md-3 mt-3">
                    <button class="btn sub-btn1" id="toggleContact" type="button">Contact Hide</button>
                </div>

                <div class="text-center my-4">
                    <label><input type="radio" name="tableToggle" value="both" checked> Show Both</label>
                    <label class="mx-3"><input type="radio" name="tableToggle" value="company"> Show Companywise Total Report</label>
                    <label><input type="radio" name="tableToggle" value="clients"> Show User Detail Table</label>
                </div>

            </div>
        </form>
        <?php if (!empty($results)): ?>
            <div id="reportSection" class="mt-5">
                

                    <div id="companyReport">
                        <h1 class="text-center">Companywise Booked Business Report</h1>
                        <table class="table table-bordered my-5">
                            <thead>
                                <tr>
                                    <th>Company Name</th>
                                    <th>Policy Type</th>
                                    <th>Vehicle Type</th>
                                    <th>Sub Type</th>
                                    <th>Total Policy Count</th>
                                    <th>Cash</th>
                                    <th>Online</th>
                                    <th>Cheque</th>
                                    <th>Premium Amount</th>
                                    <!-- <th>Recovery Amount</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['policy_company']); ?></td>
                                        <td><?php echo htmlspecialchars($row['policy_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nonmotor_subtype_select']); ?> <?php echo htmlspecialchars($row['sub_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_entries']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Cash']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Online']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Cheque']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                                        <!-- <td><?php //echo htmlspecialchars($row['total_recov_amount']); ?></td> -->
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Totals Row -->
                                <tr>
                                    <td colspan="4"><strong>Total</strong></td>
                                    <td><strong><?php echo $total_entries; ?></strong></td>
                                    <td><strong><?php echo $total_cash; ?></strong></td>
                                    <td><strong><?php echo $total_online; ?></strong></td>
                                    <td><strong><?php echo $total_cheque_count . " / " . $total_cheque; ?></strong></td>
                                    <td><strong><?php echo $total_amount; ?></strong></td>
                                    <!-- <td><strong><?php //echo $total_recov_amount; ?></strong></td> -->
                                </tr>
                            </tbody>
                        </table>
                    </div>
            
                <!--Details Clients Table-->
                
                    <div id="clientReport">
                        <div class="pt-5">
                        
                        <h2 class="text-center">
                            <?php 
                                $formatted_start_date = date("d/m/Y", strtotime($start_search_date));
                                $formatted_end_date = date("d/m/Y", strtotime($end_search_date));
                                echo "GIC Report From $formatted_start_date To $formatted_end_date";
                            ?>
                        </h2>
                    
                        <!-- Display Client Details Table -->
                        <table class="table table-bordered my-5">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Reg No</th>
                                    <th>Date</th>
                                    <th>Client Name</th>
                                    <th class="contact-data">Contact</th>
                                    <th>Policy Type</th>
                                    <th>MV no/Type</th>
                                    <th>Sub Type</th>
                                    <th>Amount</th>
                                    <th>Policy Company</th>
                                    <th>Policy Number</th>
                                    <th>Expiry</th>
                                    <th>Remark</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $srNo = 1; // Initialize serial number
                                foreach ($clientDetails as $client): 
                                ?>
                                <tr>
                                    <td><?= $srNo++ ?></td>
                                    <td><?= $client['reg_num'] ?></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($client['policy_date'])) ?>
                                    </td>
                                    <td><?= $client['client_name'] ?></td>
                                    <td class="contact-data"><?= $client['contact'] ?></td>
                                    <td><?= $client['policy_type'] ?></td>
                                    <td><?= $client['mv_number'] ?> <br> <?= $client['vehicle'] ?> <?= $client['nonmotor_type_select'] ?></td>
                                    <td><?= $client['sub_type'] ?> <?= $client['nonmotor_subtype_select'] ?></td>
                                    <td><?= $client['amount'] ?></td>
                                    <td><?= $client['policy_company'] ?></td>
                                    <td><?= $client['policy_number'] ?></td>
                                    <td>
                                        <?= (!empty($client['end_date']) && $client['end_date'] !== '0000-00-00') 
                                            ? date('d/m/Y', strtotime($client['end_date'])) 
                                            : '00-00-0000' ?>
                                            <?= $client['policy_duration'] ?>
                                    </td>

                                    
                                    
                                    <td></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            
                <!--Show summary with totals-->
                <hr>  
                    <?php
                        // Display the total data
                        echo "<h3>Summary :-</h3>";
                        echo "Number Of Policy: <strong>" . $total_entries . "</strong><br>";
                        echo "Total Premium Amount: <strong>" . $total_amount . "</strong><br>";
                        echo "Total Recovery Amount: <strong>" . $total_recov_amount . "</strong><br>";
                    ?>
                <hr>  
            
            </div>

        <?php else: ?>
            <!--<p>No results found for the specified criteria.</p>-->
        <?php endif; ?>
    </div>
</div>
</section>

<!-- Password verification Modal for print screen -->
<div class="modal fade" id="printpasswordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalLabel">Enter Password For Print</h5>
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
    document.querySelectorAll('input[name="tableToggle"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const companySection = document.getElementById('companyReport');
            const clientSection = document.getElementById('clientReport');
            
            if (this.value === 'company') {
                companySection.style.display = 'block';
                clientSection.style.display = 'none';
            } else if (this.value === 'clients') {
                companySection.style.display = 'none';
                clientSection.style.display = 'block';
            } else {
                companySection.style.display = 'block';
                clientSection.style.display = 'block';
            }
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
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

