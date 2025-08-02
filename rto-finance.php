<?php 

ob_start();

include 'session_check.php';
?>



<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';

// Fetch dropdown options
$companyQuery = "SELECT DISTINCT category FROM rto_entries WHERE category IS NOT NULL AND category != '' ORDER BY category";
$companyResult = $conn->query($companyQuery);

$adviserTypeQuery = "SELECT DISTINCT adviser_name FROM rto_entries WHERE adviser_name IS NOT NULL AND adviser_name != '' ORDER BY adviser_name";
$adviserTypeResult = $conn->query($adviserTypeQuery);

$typeworkQuery = "SELECT DISTINCT type_work FROM rto_entries WHERE type_work IS NOT NULL AND type_work != '' ORDER BY type_work";
$workTypeResult = $conn->query($typeworkQuery);

// Initialize filters
$category = $_POST['category'] ?? '';
$adviser_name = $_POST['adviser_name'] ?? '';
$type_work = $_POST['type_work'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Initialize totals
$total_entries = 0;
$total_amount = 0;
$total_adv_amount = 0;
$total_gov_amount = 0;
$total_recov_amount = 0;
$total_other_amount = 0;
$total_expenses = 0;
$total_net_amt = 0;

$results = [];
$clientDetails = [];

// Default order
$sortColumn = 'reg_num';
$order = 'ASC';

// Handle sort option
if (isset($_POST['sort'])) {
    switch ($_POST['sort']) {
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
    }
}

if ($start_date && $end_date) {
    // ================================
    // 1. Group Totals Query
    // ================================
    $query = "
        SELECT 
            category,
            adviser_name,
            type_work,
            COUNT(*) AS total_entries,
            SUM(amount) AS total_amount,
            SUM(adv_amount) AS total_adv_amount,
            SUM(gov_amount) AS total_gov_amount,
            SUM(recov_amount) AS total_recov_amount,
            SUM(other_amount) AS total_other_amount,
            SUM(expenses) AS total_expenses,
            SUM(net_amt) AS total_net_amt
        FROM 
            rto_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            " . ($category ? " AND category = ?" : "") . 
              ($adviser_name ? " AND adviser_name = ?" : "") .
              ($type_work ? " AND type_work = ?" : "") . "
        GROUP BY 
            category, type_work, adviser_name
        ORDER BY 
            $sortColumn $order
    ";

    $stmt = $conn->prepare($query);
    $params = [$start_date, $end_date];
    $types = 'ss';

    if ($category) {
        $params[] = $category;
        $types .= 's';
    }
    if ($adviser_name) {
        $params[] = $adviser_name;
        $types .= 's';
    }
    if ($type_work) {
        $params[] = $type_work;
        $types .= 's';
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($results as $row) {
        $total_entries += $row['total_entries'];
        $total_amount += $row['total_amount'];
        $total_adv_amount += $row['total_adv_amount'];
        $total_gov_amount += $row['total_gov_amount'];
        $total_recov_amount += $row['total_recov_amount'];
        $total_other_amount += $row['total_other_amount'];
        $total_expenses += $row['total_expenses'];
        $total_net_amt += $row['total_net_amt'];
    }

    // ================================
    // 2. Client Details Query
    // ================================
    $clientQuery = "
        SELECT 
            reg_num,
            policy_date,
            client_name,
            contact,
            mv_no,
            category,
            type_work,
            dl_type_work,
            tr_type_work,
            nt_type_work,
            amount,
            recov_amount,
            gov_amount,
            other_amount,
            expenses,
            vehicle_class,
            net_amt
        FROM 
            rto_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            " . ($category ? " AND category = ?" : "") . 
                ($adviser_name ? " AND adviser_name = ?" : "") .
                ($type_work ? " AND type_work = ?" : "") . "
        ORDER BY 
            $sortColumn $order
    ";

    $stmt = $conn->prepare($clientQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $clientResult = $stmt->get_result();
    $clientDetails = $clientResult->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();


// CSV Download
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="gic-finance.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Policy Company','Policy Type', 'Cash', 'Online', 'Cheque', 'Total Amount', 'Total Recovery Amount']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['policy_company'],
            $row['policy_type'],
            $row['Cash'],
            $row['Online'],
            $row['Cheque'],
            $row['total_amount'],
            $row['total_recov_amount']
        ]);
    }
    fclose($output);
    exit();
}

// PDF Download
if (isset($_POST['download_pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Financial Report', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Policy Company', 1);
     $pdf->Cell(40, 10, 'Policy Type', 1);
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
        $pdf->Cell(20, 10, $row['Cash'], 1);
        $pdf->Cell(20, 10, $row['Online'], 1);
        $pdf->Cell(20, 10, $row['Cheque'], 1);
        $pdf->Cell(30, 10, $row['total_amount'], 1);
        $pdf->Cell(50, 10, $row['total_recov_amount'], 1);
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
                <h1>CATEGORYWISE REPORT</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="rto">R/JOBS</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categorywise Report</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            

        <?php
            // Preserve selected values after form submit
            $category = $_POST['category'] ?? '';
            $type_work = $_POST['type_work'] ?? '';
            
            // Set default dates
            $currentDate = date('Y-m-d');
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            
            $start_date = $_POST['start_date'] ?? $firstDayOfMonth;
            $end_date = $_POST['end_date'] ?? $lastDayOfMonth;

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
            } 

            // Re-run database queries to repopulate dropdowns
            $companyResult = $conn->query("SELECT DISTINCT category FROM rto_entries");
            $workTypeResult = $conn->query("SELECT DISTINCT type_work FROM rto_entries");
        ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-2 field">
                    <label for="category" class="form-label">Category :</label>
                    <select name="category" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $companyResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['category']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="type_work" class="form-label">Type Of Work :</label>
                    <select name="type_work" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $workTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['type_work']) ?>" <?= $type_work === $row['type_work'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['type_work']) ?>
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
                        
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="start_date" class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date); ?>" />
                </div>

                <div class="col-md-2 field">
                    <label for="end_date" class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date); ?>" />
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
        
                <!-- Trigger Button -->
                <div class="col-md-1">
                    <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                </div>
            </div>
        </form>

        
        <?php if (!empty($results)): ?>
    <div id="reportSection" class="mt-5">
        <h1 class="text-center">Categorywise Report</h1>
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total Policy Count</th>
                    <th>Premium</th>
                    <th>Advance</th>
                    <th>Government</th>
                    <th>Recovery Amount</th>
                    <th>Cash In hand</th>
                    <th>Expenses</th>
                    <th>Net Amt</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): 
                
                    // Check for the condition to set the remark for each row
                $row_remark = ($row['total_adv_amount'] == $row['total_gov_amount']) ? 'FC' : '';
                
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                       
                        <td><?php echo htmlspecialchars($row['total_entries']); ?></td>
                        <td><?php echo ($row['total_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_amount']); ?></td>
                        <td><?php echo ($row['total_adv_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_adv_amount']); ?></td>
                        <td><?php echo ($row['total_gov_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_gov_amount']); ?></td>
                        <td><?php echo ($row['total_recov_amount'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_recov_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_other_amount']); ?></td>
                        <td><?php echo ($row['total_expenses'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_expenses']); ?></td>
                        <td><?php echo ($row['total_net_amt'] ?? 0) == 0 ? '' : htmlspecialchars($row['total_net_amt']); ?></td>
                        <td><?php echo htmlspecialchars($row_remark); ?></td>
                        
                    </tr>
                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $total_entries; ?></strong></td>
                    <td><strong><?php echo $total_amount; ?></strong></td>
                    <td><strong><?php echo $total_adv_amount; ?></strong></td>
                    <td><strong><?php echo $total_gov_amount; ?></strong></td>
                    <td><strong><?php echo $total_recov_amount; ?></strong></td>
                    <td><strong><?php echo $total_other_amount; ?></strong></td>
                    <td><strong><?php echo $total_expenses; ?></strong></td>
                    <td><strong><?php echo $total_net_amt; ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        
        <!--Details Clients Table-->
        
    <div class="pt-5">
        
        <h2 class="text-center">
             <?php 
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "RTO Report From $formatted_start_date To $formatted_end_date";
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
                    <th>Contact</th>
                    <th>MV No</th>
                    <th>Category</th>
                    <th>Type Of Work</th>
                    <th>Premium Amt</th>
                    <th>Recovery Amt</th>
                    <th>Govt Fee</th>
                    <th>Cash In Hand</th>
                    <th>Expenses</th>
                    <th>Net AMt</th>
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
                    <td><?= $client['contact'] ?></td>
                    <td>
                        <?= $client['mv_no'] ?> <br>
                        <?= $client['vehicle_class'] ?>
                    </td>
                    <td><?= $client['category'] ?></td>
                    <td>
                        <?= $client['dl_type_work'] ?>
                        <?= $client['tr_type_work'] ?>
                        <?= $client['nt_type_work'] ?>
                    </td>
                    <td><?= $client['amount'] == 0 ? '' : $client['amount'] ?></td>
                    <td><?= $client['recov_amount'] == 0 ? '' : $client['recov_amount'] ?></td>
                    <td><?= $client['gov_amount'] == 0 ? '' : $client['gov_amount'] ?></td>
                    <td><?= $client['other_amount'] == 0 ? '' : $client['other_amount'] ?></td>
                    <td><?= $client['expenses'] == 0 ? '' : $client['expenses'] ?></td>
                    <td><?= $client['net_amt'] == 0 ? '' : $client['net_amt'] ?></td>
                    
                    
                    
                
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
        

        <?php
            // Display the total data
            echo "<h3>Summary :-</h3>";
            echo "Number Of Policy: <strong>" . $total_entries . "</strong><br>";
            echo "Total Premium Amount: <strong>" . $total_amount . "</strong><br>";
            echo "Total Recovery Amount: <strong>" . $total_recov_amount . "</strong><br>";
        ?>
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



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

