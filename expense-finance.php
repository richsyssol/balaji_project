<?php 
ob_start();
include 'session_check.php';
 
?>



<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';




// Fetch unique company names from the database
$companyQuery = "SELECT DISTINCT expense_type FROM expenses ORDER BY expense_type";
$companyResult = $conn->query($companyQuery);

// Fetch unique Vehicle names from the database
$vehicleQuery = "SELECT DISTINCT vehicle_type FROM expenses ORDER BY vehicle_type";
$vehicleResult = $conn->query($vehicleQuery);

// Initialize variables for search criteria
$expense_type = $_POST['expense_type'] ?? '';
$vehicle_type = $_POST['vehicle_type'] ?? '';
$mv_num = $_POST['mv_num'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$expense_status = $_POST['expense_status'] ?? '';

// Initialize variables for totals
$total_entries = 0;
$total_cash = 0;
$total_online = 0;
$total_cheque = 0;
$total_cheque_count = 0;
$total_amount = 0;
$total_km = 0;
$total_fuel = 0;

// Execute the query if a date range is set
$results = [];
$clientDetails = [];
if ($start_date && $end_date) {
    // Parameters for summary query
    $summaryParams = [$start_date, $end_date];
    $summaryTypes = 'ss';

    // Summary Query
    $summaryQuery = "
        SELECT 
            expense_type,
            vehicle_type,
            mv_num,
            expense_status,
            COUNT(*) AS total_entries,
            SUM(CASE WHEN pay_mode = 'Cash' THEN amount ELSE 0 END) AS Cash,
            SUM(CASE WHEN pay_mode = 'Online' THEN amount ELSE 0 END) AS Online,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN amount ELSE 0 END) AS Cheque,
            SUM(CASE WHEN pay_mode = 'Cheque' THEN 1 ELSE 0 END) AS Cheque_count,
            SUM(amount) AS total_amount,
            SUM(liter) AS total_fuel
        FROM 
            expenses
        WHERE 
            policy_date BETWEEN ? AND ? 
            AND (
                is_deleted = 0
            )
            " . ($expense_type ? " AND expense_type = ?" : "") .
                ($vehicle_type ? " AND vehicle_type = ?" : "") .
                ($expense_status ? " AND expense_status = ?" : "") .
                ($mv_num ? " AND mv_num = ?" : "") . "
        GROUP BY 
            expense_type
    ";

    if ($expense_type) {
        $summaryParams[] = $expense_type;
        $summaryTypes .= 's';
    }

    if ($vehicle_type) {
        $summaryParams[] = $vehicle_type;
        $summaryTypes .= 's';
    }

    if ($mv_num) {
        $summaryParams[] = $mv_num;
        $summaryTypes .= 's';
    }
    
    if ($expense_status) {
        $summaryParams[] = $expense_status;
        $summaryTypes .= 's';
    }
    

    // Prepare and execute summary query
    if ($stmt = $conn->prepare($summaryQuery)) {
        $stmt->bind_param($summaryTypes, ...$summaryParams);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);

        // Initialize variables to store first and last 'ride_km'
        $first_ride_km = null;
        $last_ride_km = null;

        // Calculate overall totals and track first and last 'ride_km'
        foreach ($results as $row) {
            $total_entries += $row['total_entries'];
            $total_cash += $row['Cash'];
            $total_online += $row['Online'];
            $total_cheque += $row['Cheque'];
            $total_cheque_count += $row['Cheque_count'];
            $total_amount += $row['total_amount'];
            $total_fuel += $row['total_fuel'];
        }

        // Fetch the individual ride_km values for the given date range
        $detailParams = [$start_date, $end_date];
        $detailTypes = 'ss';

        if ($expense_type) {
            $detailParams[] = $expense_type;
            $detailTypes .= 's';
        }

        if ($vehicle_type) {
            $detailParams[] = $vehicle_type;
            $detailTypes .= 's';
        }

        if ($mv_num) {
            $detailParams[] = $mv_num;
            $detailTypes .= 's';
        }
        
        if ($expense_status) {
            $detailParams[] = $expense_status;
            $detailTypes .= 's';
        }

        // Detailed Query to get the ride_km values in order
        $detailQuery = "
            SELECT 
                reg_num,
                policy_date,
                expense_type,
                pay_mode,
                mv_num,
                vehicle_type,
                fuel,
                ride_km,
                period,
                liter,
                expense_status,
                amount
            FROM 
                expenses
            WHERE 
                policy_date BETWEEN ? AND ? 
                AND (
                    is_deleted = 0
                )
                " . ($expense_type ? " AND expense_type = ?" : "") . 
                ($vehicle_type ? " AND vehicle_type = ?" : "") . 
                ($mv_num ? " AND mv_num = ?" : "") .
                ($expense_status ? " AND expense_status = ?" : "") ."
            ORDER BY 
                policy_date ASC
        ";

        // Prepare and execute detailed query
        if ($stmt = $conn->prepare($detailQuery)) {
            $stmt->bind_param($detailTypes, ...$detailParams);
            $stmt->execute();
            $clientResult = $stmt->get_result();
            $clientDetails = $clientResult->fetch_all(MYSQLI_ASSOC);

            // Get the first and last ride_km values
            if (count($clientDetails) > 0) {
                // Make sure the values are numeric before subtracting
                $first_ride_km = is_numeric($clientDetails[0]['ride_km']) ? (float)$clientDetails[0]['ride_km'] : 0;
                $last_ride_km = is_numeric($clientDetails[count($clientDetails) - 1]['ride_km']) ? (float)$clientDetails[count($clientDetails) - 1]['ride_km'] : 0;
            }

            // Calculate the difference between the last and first ride_km
            if ($first_ride_km !== null && $last_ride_km !== null) {
                $total_km = $last_ride_km - $first_ride_km;
            }

            // Close statement after detailed query
            $stmt->close();
        } else {
            echo "Error preparing the detailed query: " . $conn->error;
        }
    } else {
        echo "Error preparing the summary query: " . $conn->error;
    }

    // Close statement after summary query
    // if (isset($stmt) && $stmt) {
    //     $stmt->close();
    // }
}

$conn->close();



// CSV Download
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="expense-finance.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Expense Type', 'Cash', 'Online', 'Cheque', 'Total Amount']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['expense_type'],
            $row['Cash'],
            $row['Online'],
            $row['Cheque'],
            $row['total_amount'],
           
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
    $pdf->Cell(40, 10, 'Expense Type', 1);
    $pdf->Cell(20, 10, 'Cash', 1);
    $pdf->Cell(20, 10, 'Online', 1);
    $pdf->Cell(20, 10, 'Cheque', 1);
    $pdf->Cell(30, 10, 'Total Amount', 1);
    
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($results as $row) {
        $pdf->Cell(40, 10, $row['expense_type'], 1);
        $pdf->Cell(20, 10, $row['Cash'], 1);
        $pdf->Cell(20, 10, $row['Online'], 1);
        $pdf->Cell(20, 10, $row['Cheque'], 1);
        $pdf->Cell(30, 10, $row['total_amount'], 1);
        
        $pdf->Ln();
    }
    $pdf->Output('D', 'expense-finance.pdf');
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
                <h1>Expense Finance Report</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="expense">Expense</a></li>
                <li class="breadcrumb-item active" aria-current="page">Expense Finance Report</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            
        
        
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
                        $expense_type     = $_POST['expense_type']     ?? '';
                        $selected_mv      = $_POST['mv_num']        ?? '';
                        $vehicle_type     = $_POST['vehicle_type']          ?? '';
                        $expense_status   = $_POST['expense_status']   ?? '';
                        $start_date       = $_POST['start_date']       ?? '';
                        $end_date         = $_POST['end_date']         ?? date('Y-m-d');
                        ?>
                
                        <!-- Expense Type -->
                        <div class="col-md-3 field">
                            <label for="expense_type" class="form-label">Search by Expense Type:</label>
                            <select name="expense_type" id="expense_type" class="form-control">
                                <option value="">All</option>
                                <?php foreach ($expense_types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" <?= $expense_type === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                
                        <!-- MV Number -->
                        <div class="col-md-2 field">
                            <label for="mvnumber" class="form-label">Search by MV Number:</label>
                            <select name="mv_num" id="mvnumber" class="form-control" onchange="handleMvNumChange(this.value)">
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
                            <input type="text" name="vehicle_type" id="vehicle_type" class="form-control" value="<?= htmlspecialchars($vehicle_type) ?>" readonly>
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
                
                     
                            <div class="col-md-1">
                                <button type="button" class="btn sub-btn1 mt-4" onclick="showPasswordModal()">Print</button>
                            </div>
                            
                    </div>
                </form>


<?php if (!empty($results)): ?>
<div id="reportSection" class="mt-5">
    <h1 class="text-center">Expense Finance Report</h1>
    <table class="table table-bordered my-5">
        <thead>
            <tr>
                <th>Expense Type</th>
                <th>Total Expense Count</th>
                <th>Cash</th>
                <th>Online</th>
                <th>Cheque</th>
                <th>Total Amount</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['expense_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_entries']); ?></td>
                    <td><?php echo htmlspecialchars($row['Cash']); ?></td>
                    <td><?php echo htmlspecialchars($row['Online']); ?></td>
                    <td><?php echo htmlspecialchars($row['Cheque']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                   
                </tr>
            <?php endforeach; ?>
            <!-- Totals Row -->
                <tr>
                    <td ><strong>Total</strong></td>
                    <td><strong><?php echo $total_entries; ?></strong></td>
                    <td><strong><?php echo $total_cash; ?></strong></td>
                    <td><strong><?php echo $total_online; ?></strong></td>
                    <td><strong><?php echo $total_cheque_count . " / " . $total_cheque; ?></strong></td>
                    <td><strong><?php echo $total_amount; ?></strong></td>
                   
                </tr>
        </tbody>
    </table>
    
    
      <!--Details Clients Table-->
        
    <div class="pt-5">
        
        <h2 class="text-center">
             <?php 
                $formatted_start_date = date("d/m/Y", strtotime($start_date));
                $formatted_end_date = date("d/m/Y", strtotime($end_date));
                echo "Expense Report From $formatted_start_date To $formatted_end_date";
            ?>
        </h2>
    
        <!-- Display Client Details Table -->
        <table class="table table-bordered my-5">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Date</th>
                    <th>Type Of Expense</th>
                    <th>MV No/Vehicle Type</th>
                    <th>Fuel</th>
                    <th>KM</th>
                    <th>Quantity Lit</th>
                    <th>Amount</th>
                    <th>Mon/Yr</th>
                    
                    <th>Expense Status</th>
                    <!--<th>Expiry</th>-->
                    <!--<th>Remark</th>-->
                    <th>Payment Mode</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php 
                $srNo = 1; // Initialize serial number
                foreach ($clientDetails as $client): 
                ?>
                <tr>
                    <td><?= $srNo++ ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($client['policy_date'])) ?>
                    </td>
                    <td><?= $client['expense_type'] ?></td>
                    <td><?= $client['mv_num'] ?> <br> <?= $client['vehicle_type'] ?></td>
                    <td><?= $client['fuel'] ?></td>
                    <td><?= $client['ride_km'] ?></td>
                    <td><?= $client['liter'] ?> </td>
                    <td><?= $client['amount'] ?></td>
                    <td><?= $client['period'] ?></td>
                    <td><?= $client['expense_status'] ?></td>
                    <!--<td><?= $client['end_date'] ?></td>-->
                    <td><?= $client['pay_mode'] ?></td>
                    
                
                </tr>
                <?php endforeach; ?>

                <!-- Totals Row -->
                <tr >
                    <td colspan="5"><strong>Total</strong></td>
                    <td><strong><?php echo $total_km; ?>KM</strong></td>
                    <td><strong><?php echo $total_fuel; ?></strong></td>
                    <td><strong><?php echo $total_amount; ?></strong></td>
                    <td colspan="2"></td>
                    
                
                </tr>

            </tbody> 
        </table>
    </div>
    
    
    
    
        <?php
            // Display the total data
            echo "<h3>Summary :-</h3>";
            echo "Number Of Expense: <strong>" . $total_entries . "</strong><br>";
            echo "Total Amount: <strong>" . $total_amount . "</strong><br>";
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


</script>



<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

