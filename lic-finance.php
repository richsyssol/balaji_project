<?php 
// session_start(); // Start the session





// Check if user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ) {
//     // Redirect to login page if not logged in
//     header("Location: login.php"); // Adjust path if needed
//     exit(); // Ensure no further code is executed
// }

include 'session_check.php';

ob_start();
?>



<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';

// Initialize search query to prevent warnings
$search_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';

// Fetch unique values for dropdowns
$companyQuery = "SELECT DISTINCT job_type FROM lic_entries WHERE job_type IS NOT NULL AND job_type != '' ORDER BY job_type";
$companyResult = $conn->query($companyQuery);

$policyTypeQuery = "SELECT DISTINCT collection_job FROM lic_entries WHERE collection_job IS NOT NULL AND collection_job != '' ORDER BY collection_job";
$policyTypeResult = $conn->query($policyTypeQuery);

$vehicleTypeQuery = "SELECT DISTINCT work_status FROM lic_entries WHERE work_status IS NOT NULL AND work_status != '' ORDER BY work_status";
$vehicleTypeResult = $conn->query($vehicleTypeQuery);

$adviserTypeQuery = "SELECT DISTINCT adviser FROM lic_entries WHERE adviser IS NOT NULL AND adviser != '' ORDER BY adviser";
$adviserTypeResult = $conn->query($adviserTypeQuery);

// Initialize variables for search criteria
$job_type = $_POST['job_type'] ?? '';
$collection_job = $_POST['collection_job'] ?? '';
$work_status = $_POST['work_status'] ?? '';
$adviser = $_POST['adviser'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Initialize totals
$total_entries = 0;
$total_cash = 0;
$total_online = 0;
$total_cheque = 0;
$total_cheque_count = 0;
$total_policy_amt = 0;
$total_our_agency_amt = 0;
$total_other_agency_amt = 0;

// Execute query if date range is set
$results = [];
if ($start_date && $end_date) {
    $query = "
        SELECT 
            job_type,
            collection_job,
            work_status,
            adviser,
            COUNT(*) AS total_entries,
            SUM(CASE WHEN pay_mode = 'Cash' THEN policy_amt ELSE 0 END) AS Cash,
            SUM(CASE WHEN pay_mode = 'Online' THEN policy_amt ELSE 0 END) AS Online,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN policy_amt ELSE 0 END) AS Cheque,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN 1 ELSE 0 END) AS Cheque_count,
            SUM(policy_amt) AS total_policy_amt,
            SUM(our_agency_amt) AS total_our_agency_amt,
            SUM(other_agency_amt) AS total_other_agency_amt
        FROM 
            lic_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            " . ($job_type ? " AND job_type = ?" : "") . 
            ($collection_job ? " AND collection_job = ?" : "") .
            ($work_status ? " AND work_status = ?" : "") . 
            ($adviser ? " AND adviser = ?" : "") .
            ($search_query ? " AND (client_name LIKE ? OR contact LIKE ?)" : "") . "
        GROUP BY 
            job_type, collection_job, work_status,adviser
    ";

    // Prepare statement
    $stmt = $conn->prepare($query);
    $params = [$start_date, $end_date];
    $types = 'ss';

    if ($job_type) { $params[] = $job_type; $types .= 's'; }
    if ($collection_job) { $params[] = $collection_job; $types .= 's'; }
    if ($work_status) { $params[] = $work_status; $types .= 's'; }
    if ($adviser) { $params[] = $adviser; $types .= 's'; }
    if ($search_query) {
        $search_term = "%$search_query%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    foreach ($results as $row) {
        $total_entries += $row['total_entries'];
        $total_cash += $row['Cash'];
        $total_online += $row['Online'];
        $total_cheque += $row['Cheque'];
        $total_cheque_count += $row['Cheque_count'];
        $total_policy_amt += $row['total_policy_amt'];
        $total_our_agency_amt += $row['total_our_agency_amt'];
        $total_other_agency_amt += $row['total_other_agency_amt'];
    }

    $stmt->close();
    
    
    // Fetch client details
    $clientQuery = "
        SELECT 
            reg_num,
            policy_date,
            policy_num,
            colle_policy_num,
            client_name,
            contact,
            policy_amt,
            pay_mode,
            our_agency_amt,
            other_agency_amt,
            job_type,
            work_status,
            collection_job
        FROM 
            lic_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            " . ($job_type ? " AND job_type = ?" : "") . 
            ($collection_job ? " AND collection_job = ?" : "") .
            ($work_status ? " AND work_status = ?" : "") . 
            ($adviser ? " AND adviser = ?" : "") .
            ($search_query ? " AND (client_name LIKE ? OR contact LIKE ? )" : "") . "
        ORDER BY 
            CASE 
            WHEN pay_mode = 'Cash' THEN 1
            WHEN pay_mode = 'Cheque' THEN 2
            ELSE 3
            END, 
            policy_date DESC
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
    fputcsv($output, ['Policy Company','Policy Type', 'Cash', 'Online', 'Cheque', 'Total policy_amt', 'Total Recovery policy_amt']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['policy_company'],
            $row['policy_type'],
            $row['Cash'],
            $row['Online'],
            $row['Cheque'],
            $row['total_policy_amt'],
            $row['total_recov_policy_amt']
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
    $pdf->Cell(30, 10, 'Total policy_amt', 1);
    $pdf->Cell(50, 10, 'Total Recovery policy_amt', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($results as $row) {
        $pdf->Cell(40, 10, $row['policy_company'], 1);
        $pdf->Cell(40, 10, $row['policy_type'], 1);
        $pdf->Cell(20, 10, $row['Cash'], 1);
        $pdf->Cell(20, 10, $row['Online'], 1);
        $pdf->Cell(20, 10, $row['Cheque'], 1);
        $pdf->Cell(30, 10, $row['total_policy_amt'], 1);
        $pdf->Cell(50, 10, $row['total_recov_policy_amt'], 1);
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
                <h1>JOBTYPEWISE REPORT</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="lic">LIC</a></li>
                <li class="breadcrumb-item active" aria-current="page">Jobtypewise Report</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">


        <?php
            // Initialize variables with POST data or default values
            $search_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';
            $job_type = isset($_POST['job_type']) ? $_POST['job_type'] : '';
            $collection_job = isset($_POST['collection_job']) ? $_POST['collection_job'] : '';
            $work_status = isset($_POST['work_status']) ? $_POST['work_status'] : '';
            $adviser = isset($_POST['adviser']) ? $_POST['adviser'] : '';

            $currentDate = date('Y-m-d');
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');

            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : $firstDayOfMonth;
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : $lastDayOfMonth;
        ?>
            
        <form method="POST">
            <div class="row">

            <div class="col-md-3 field">
                <label for="search_query" class="form-label">Search :</label>
                <input type="text" name="search_query" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="Name, Contact" />
            </div>

                <div class="col-md-2 field">
                    <label for="job_type" class="form-label">Job Type :</label>
                    <select name="job_type" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $companyResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['job_type']) ?>" <?= $job_type == $row['job_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['job_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-1 field">
                    <label for="collection_job" class="form-label">Type Of Job :</label>
                    <select name="collection_job" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $policyTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['collection_job']) ?>" <?= $collection_job == $row['collection_job'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['collection_job']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="work_status" class="form-label">S Type Of Job :</label>
                    <select name="work_status" class="form-control">
                        <option value="">All Types</option>
                        <?php while ($row = $vehicleTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['work_status']) ?>" <?= $work_status == $row['work_status'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['work_status']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="adviser" class="form-label">Adviser :</label>
                    <select name="adviser" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $adviserTypeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['adviser']) ?>" <?= $adviser == $row['adviser'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['adviser']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="start_date" class="form-label">Start Date :</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
                </div>

                <div class="col-md-2 field">
                    <label for="end_date" class="form-label">End Date :</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
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
        <h1 class="text-center">Job Type Wise Report</h1>
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Job Type</th>
                    <th>Type Of Job</th>
                    <!--<th>Vehicle Type</th>-->
                    <!--<th>Sub Type</th>-->
                    <th>Total Policy Count</th>
                    <th>Cash</th>
                    <th>Online</th>
                    <th>Cheque</th>
                    <th>Premium policy_amt</th>
                    <!--<th>Recovery policy_amt</th>-->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['job_type']); ?></td>
                        <!--<td><?php echo htmlspecialchars($row['policy_type']); ?></td>-->
                        <!--<td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>-->
                        <td><?php echo htmlspecialchars($row['work_status']); ?> <?php echo htmlspecialchars($row['collection_job']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_entries']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cash']); ?></td>
                        <td><?php echo htmlspecialchars($row['Online']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cheque']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_policy_amt']); ?></td>
                        <!--<td><?php echo htmlspecialchars($row['total_recov_policy_amt']); ?></td>-->
                    </tr>

                    

                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong><?php echo $total_entries; ?></strong></td>
                    <td><strong><?php echo $total_cash; ?></strong></td>
                    <td><strong><?php echo $total_online; ?></strong></td>
                    <td><strong><?php echo $total_cheque_count . " / " . $total_cheque; ?></strong></td>
                    <td><strong><?php echo $total_policy_amt; ?></strong></td>
                    <!--<td><strong><?php echo $total_recov_policy_amt; ?></strong></td>-->
                </tr>
            </tbody>
        </table>
        
        
         <!--Details Clients Table-->
        
    <div class="pt-5">
        
        <h2 class="text-center">
             <?php 
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "LIC Report From $formatted_start_date To $formatted_end_date";
            ?>
        </h2>
    
        <!-- Display Client Details Table -->
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Reg No</th>
                    <th>Date</th>
                    <th>Policy No</th>
                    <th>Client Name</th>
                    <th>Contact</th>
                    <th>Job Type</th>
                    <th>Type Of Job</th>
                    <th>Premium Amount</th>
                    <th>Our Agency Amt</th>
                    <th>Other Agency Amt</th>
                    <th>Pay Mode</th>
                </tr>
            </thead>
            <tbody>
    <?php 
    $srNo = 1; // Initialize serial number
    foreach ($clientDetails as $client): 
    ?>
    <tr>
        <td><?= $srNo++ ?></td>
        <td><?= htmlspecialchars($client['reg_num']) ?></td>
        <td><?= date('d/m/Y', strtotime($client['policy_date'])) ?></td>
        <td>
            <!-- Premium Collection Policy Number -->
            <?= htmlspecialchars($client['policy_num']) ?>

            <!-- Servicing Task Policy Number -->
            <?php
            $colle_policy_num = json_decode($client['colle_policy_num'], true);

            if (is_array($colle_policy_num)) {
                echo '<br>' . implode('<br>', array_map('htmlspecialchars', $colle_policy_num));
            } elseif (!empty($colle_policy_num)) {
                echo '<br>' . htmlspecialchars($colle_policy_num);
            }
            ?>
        </td>
        
        <td><?= htmlspecialchars($client['client_name']) ?></td>
        <td><?= htmlspecialchars($client['contact']) ?></td>
        <td><?= htmlspecialchars($client['job_type']) ?></td>
        <td>
            <?= htmlspecialchars($client['work_status']) ?>
            <?= htmlspecialchars($client['collection_job']) ?>
        </td>
        <td><?= $client['policy_amt'] == 0 ? '' : htmlspecialchars($client['policy_amt']) ?></td>
        <td><?= $client['our_agency_amt'] == 0 ? '' : htmlspecialchars($client['our_agency_amt']) ?></td>
        <td><?= $client['other_agency_amt'] == 0 ? '' : htmlspecialchars($client['other_agency_amt']) ?></td>
        <td><?= htmlspecialchars($client['pay_mode']) ?></td>
    </tr>
    <?php endforeach; ?>
</tbody>

        </table>
    </div>
        

        <?php
            // Display the total data
            echo "<h3>Summary :-</h3>";
            echo "Number Of Policy: <strong>" . $total_entries . "</strong><br>";
            echo "Total Premium policy_amt: <strong>" . $total_policy_amt . "</strong><br>";
            echo "Total Our Agency Amount: <strong>" . $total_our_agency_amt . "</strong><br>";
            echo "Total Other Agency Amount: <strong>" . $total_other_agency_amt . "</strong><br>";
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

