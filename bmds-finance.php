<?php 

ob_start();


include 'session_check.php';

?>



<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';

// Fetch unique company names from the database
$companyQuery = "SELECT DISTINCT car_type FROM bmds_entries WHERE car_type IS NOT NULL AND car_type != '' ORDER BY car_type";
$companyResult = $conn->query($companyQuery);

// Fetch unique company names from the database
$classQuery = "SELECT DISTINCT llr_class FROM bmds_entries WHERE llr_class IS NOT NULL AND llr_class != '' ORDER BY llr_class";
$classResult = $conn->query($classQuery);

// Fetch unique type from the database
$typeQuery = "SELECT DISTINCT bmds_type FROM bmds_entries WHERE bmds_type IS NOT NULL AND bmds_type != '' ORDER BY bmds_type";
$typeResult = $conn->query($typeQuery);

// Fetch class from the database
$classNumQuery = "SELECT DISTINCT class FROM bmds_entries WHERE class IS NOT NULL AND class != '' ORDER BY class";
$classNumResult = $conn->query($classNumQuery);


// Initialize variables for search criteria
$car_type = $_POST['car_type'] ?? '';
$llr_class = $_POST['llr_class'] ?? '';
$bmds_type = $_POST['bmds_type'] ?? '';
$class = $_POST['class'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Initialize variables for totals
$total_entries = 0;
$total_amount = 0;
$total_recov_amount = 0;
$total_bal_amount = 0;

// Execute the query if a date range is set
$results = [];
if ($start_date && $end_date) {
    $query = "
        SELECT 
            car_type,
            bmds_type,
            llr_class,
            class,
            COUNT(*) AS total_entries,
            SUM(amount) AS total_amount,
            SUM(recov_amount) AS total_recov_amount,
            SUM(bal_amount) AS total_bal_amount
    
        FROM 
            bmds_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            " . ($car_type ? " AND car_type = ?" : "") .
                ($llr_class ? " AND llr_class = ?" : "") . 
                ($bmds_type ? " AND bmds_type = ?" : "") . 
                ($class ? " AND class = ?" : "") . "
        GROUP BY 
            car_type,llr_class,bmds_type,class
    ";

    // Prepare statement with dynamic binding
    $stmt = $conn->prepare($query);
    $params = [$start_date, $end_date];
    $types = 'ss';

    if ($car_type) {
        $params[] = $car_type;
        $types .= 's';
    }

    if ($llr_class) {
        $params[] = $llr_class;
        $types .= 's';
    }

    if ($bmds_type) {
        $params[] = $bmds_type;
        $types .= 's';
    }

    if ($class) {
        $params[] = $class;
        $types .= 's';
    }
    

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    
    
    
    // Calculate overall totals
    foreach ($results as $row) {
        $total_entries += $row['total_entries'];
        $total_amount += $row['total_amount'];
        $total_recov_amount += $row['total_recov_amount'];
        $total_bal_amount += $row['total_bal_amount'];
    }
    
   

    $stmt->close();
    
    
    // Fetch client details
    $clientQuery = "
        SELECT 
            reg_num,
            policy_date,
            client_name,
            amount,
            car_type,
            llr_class,
            bmds_type,
            ride,
            recov_amount,
            bal_amount,
            start_time,
            end_time,
            start_date,
            end_date,
            class
        FROM 
            bmds_entries
        WHERE 
            policy_date BETWEEN ? AND ?
            AND (
                is_deleted = 0
            )
            "  . ($car_type ? " AND car_type = ?" : "") .
                ($llr_class ? " AND llr_class = ?" : "") .
                ($bmds_type ? " AND bmds_type = ?" : "") . 
                ($class ? " AND class = ?" : "") . "
        ORDER BY 
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
    header('Content-Disposition: attachment; filename="bmds-finance.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['car_type', 'Total Premium Amount', 'Total Recovery Amount']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['car_type'],
            $row['total_amount'],
            $row['total_recov_amount'],
            $row['total_bal_amount']
           
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
    $pdf->Cell(40, 10, 'car_type', 1);
    $pdf->Cell(20, 10, 'Total Premium Amount', 1);
    $pdf->Cell(20, 10, 'Total Recovery Amount', 1);
    
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($results as $row) {
        $pdf->Cell(40, 10, $row['car_type'], 1);
        $pdf->Cell(20, 10, $row['total_amount'], 1);
        $pdf->Cell(20, 10, $row['total_recov_amount'], 1);
        $pdf->Cell(20, 10, $row['total_bal_amount'], 1);
        
        $pdf->Ln();
    }
    $pdf->Output('D', 'bmds-finance.pdf');
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
                <li class="breadcrumb-item" aria-current="page"><a href="bmds">BMDS</a></li>
                <li class="breadcrumb-item active" aria-current="page">BMDS Finance Report</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            

        <?php
            // Preserve selected values after form submit
            $car_type = $_POST['car_type'] ?? '';
            $llr_class = $_POST['llr_class'] ?? '';

            // Default date values
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            $start_date = $_POST['start_date'] ?? $firstDayOfMonth;
            $end_date = $_POST['end_date'] ?? $lastDayOfMonth;

            // Re-run database queries to fill dropdowns
            $companyResult = $conn->query("SELECT DISTINCT car_type FROM bmds_entries");
            $classResult = $conn->query("SELECT DISTINCT llr_class FROM bmds_entries");
        ?>


        <form method="POST">
            <div class="row">

                <div class="col-md-2 field">
                    <label for="type" class="form-label">Type :</label>
                    <select name="bmds_type" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $typeResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['bmds_type']) ?>" <?= $bmds_type === $row['bmds_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['bmds_type']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                

                <div class="col-md-2 field">
                    <label for="llr_class" class="form-label">Class Of Vehicle :</label>
                    <select name="llr_class" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $classResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['llr_class']) ?>" <?= $llr_class === $row['llr_class'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['llr_class']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="class" class="form-label">Class Of NUmber :</label>
                    <select name="class" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $classNumResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['class']) ?>" <?= $class === $row['class'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['class']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 field">
                    <label for="car_type" class="form-label">Car Type :</label>
                    <select name="car_type" class="form-control">
                        <option value="">All</option>
                        <?php while ($row = $companyResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['car_type']) ?>" <?= $car_type === $row['car_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['car_type']) ?>
                            </option>
                        <?php endwhile; ?>
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
        
                <div class="col-md-1">
                    <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                </div>

            </div>
        </form>
        <?php if (!empty($results)): ?>
    <div id="reportSection" class="mt-5">

        <div class="text-center">
            <?php
                $formatted_start_date = (!empty($start_date) && strtotime($start_date)) ? date("d/m/Y", strtotime($start_date)) : "00/00/0000";
                $formatted_end_date = (!empty($end_date) && strtotime($end_date)) ? date("d/m/Y", strtotime($end_date)) : "00";
            ?>
            <h1 class="mt-4">
                <?= htmlspecialchars($bmds_type) ?> Report from <?= htmlspecialchars($formatted_start_date) ?> To <?= htmlspecialchars($formatted_end_date) ?>
            </h1>
        </div>

        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Car Type</th>
                    <th>Class Of Vehicle</th>
                    <th>Class Of Number</th>
                    <th>Total Entries</th>
                    <th>Premium</th>
                    <th>Recovery Amount</th>
                    <th>Excess Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): 
                                
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['bmds_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['llr_class']); ?> <?php echo htmlspecialchars($row['car_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['class']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_entries']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_recov_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_bal_amount']); ?></td>
                        <!--<td><?php //echo htmlspecialchars($row_remark); ?></td>-->
                        
                    </tr>
                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td><strong><?php echo $total_entries; ?></strong></td>
                    <td><strong><?php echo $total_amount; ?></strong></td>
                    <td><strong><?php echo $total_recov_amount; ?></strong></td>
                    <td><strong><?php echo $total_bal_amount; ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        
        <!--Details Clients Table-->
        
    <div class="pt-5">
        
        <div class="text-center">
            <?php
                $formatted_start_date = (!empty($start_date) && strtotime($start_date)) ? date("d/m/Y", strtotime($start_date)) : "00/00/0000";
                $formatted_end_date = (!empty($end_date) && strtotime($end_date)) ? date("d/m/Y", strtotime($end_date)) : "00";
            ?>
            <h1 class="mt-4">
                <?= htmlspecialchars($bmds_type) ?> Report from <?= htmlspecialchars($formatted_start_date) ?> To <?= htmlspecialchars($formatted_end_date) ?>
            </h1>
        </div>
    
        <!-- Display Client Details Table -->
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Reg No</th>
                    <th>Date</th>
                    <th>Client Name</th>
                    <th>Type</th>
                    <th>Class Of Number</th>
                    <th>Class Of Vehicle</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Start/End Time</th>
                    <th>Premium Amt</th>
                    <th>Recovery Amt</th>
                    <th>Excess Amt</th>
                    
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
                    <td><?= $client['bmds_type'] ?></td>
                    <td><?= $client['class'] ?></td>
                    <td><?= $client['llr_class'] ?><?= $client['car_type'] ?></td>
                    
                    <td>
                        <?php 
                        // Check for empty or invalid start_date
                        if (!empty($client['start_date']) && $client['start_date'] !== '0000-00-00') {
                            $start_date = date("d-m-Y", strtotime($client['start_date']));
                            echo htmlspecialchars($start_date); 
                        } else {
                            echo '';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        // Check for empty or invalid end_date
                        if (!empty($client['end_date']) && $client['end_date'] !== '0000-00-00') {
                            $end_date = date("d-m-Y", strtotime($client['end_date']));
                            echo htmlspecialchars($end_date); 
                        } else {
                            echo '';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        // Format start_time if not 00:00:00 or empty
                        $start_time_str = (!empty($client['start_time']) && $client['start_time'] !== '00:00:00') ? date("g:i A", strtotime($client['start_time'])) : '';
                        
                        // Format end_time if not 00:00:00 or empty
                        $end_time_str = (!empty($client['end_time']) && $client['end_time'] !== '00:00:00') ? date("g:i A", strtotime($client['end_time'])) : '';

                        if ($start_time_str || $end_time_str) {
                            echo htmlspecialchars($start_time_str) . ' To ' . htmlspecialchars($end_time_str);
                        } else {
                            echo '';
                        }
                        ?>
                    </td>

                    <td><?= $client['amount'] == 0 ? '' : $client['amount'] ?></td>
                    <td><?= $client['recov_amount'] == 0 ? '' : $client['recov_amount'] ?></td>
                    <td><?= $client['bal_amount'] == 0 ? '' : $client['bal_amount'] ?></td>
                    
                    
                    
                
                </tr>
                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr>
                    <td colspan="10" class="text-center"><strong>Total</strong></td>
                    <td><strong><?php echo $total_amount; ?></strong></td>
                    <td><strong><?php echo $total_recov_amount; ?></strong></td>
                    <td><strong><?php echo $total_bal_amount; ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
        

        <?php
            // Display the total data
            echo "<h3>Summary :-</h3>";
            echo "Number Of Policy: <strong>" . $total_entries . "</strong><br>";
            echo "Total Premium Amount: <strong>" . $total_amount . "</strong><br>";
            echo "Total Recovery Amount: <strong>" . $total_recov_amount . "</strong><br>";
            echo "Total Recovery Amount: <strong>" . $total_bal_amount . "</strong><br>";
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

