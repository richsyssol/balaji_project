<?php 
// session_start(); // Start the session



ob_start();

// Check if user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true ) {
//     // Redirect to login page if not logged in
//     header("Location: login.php"); // Adjust path if needed
//     exit(); // Ensure no further code is executed
// }

include 'session_check.php';
?>



<?php
require_once('fpdf/fpdf.php');
include 'includes/db_conn.php';

// Initialize variables for search criteria
$combined_option = $_POST['combined_option'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Execute the query if a date range is set
$results = [];
if ($start_date && $end_date) {
    if ($combined_option) {
        // Query with a specific combined option
        $query = "
            SELECT 
                mf_option,
                insurance_option,
                SUM(amount) AS total_amount
            FROM 
                mf_entries
            WHERE 
                (mf_option = ? OR insurance_option = ?)
                AND policy_date BETWEEN ? AND ?
                AND (
                    is_deleted = 0
                )
            GROUP BY 
                mf_option, 
                insurance_option
        ";

        // Prepare and execute the statement
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $combined_option, $combined_option, $start_date, $end_date);
    } else {
        // Query for all options if none is selected
        $query = "
            SELECT 
                mf_option,
                insurance_option,
                SUM(amount) AS total_amount
            FROM 
                mf_entries
            WHERE 
                policy_date BETWEEN ? AND ?
                AND (
                    is_deleted = 0
                )
            GROUP BY 
                mf_option, 
                insurance_option
        ";

        // Prepare and execute the statement
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

$conn->close();


// CSV Download
if (isset($_POST['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="lic-finance.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['MF Option', 'Insurance Option', 'Total Amount']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['mf_option'],
            $row['insurance_option'],
            $row['total_amount']
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
    $pdf->Cell(40, 10, 'MF Option', 1);
    $pdf->Cell(40, 10, 'Insurance Option', 1);
    $pdf->Cell(30, 10, 'Total Amount', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($results as $row) {
        $pdf->Cell(40, 10, $row['mf_option'], 1);
        $pdf->Cell(40, 10, $row['insurance_option'], 1);
        $pdf->Cell(30, 10, $row['total_amount'], 1);
        $pdf->Ln();
    }
    $pdf->Output('D', 'lic-finance.pdf');
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
                <h1>MF/INSURANCE FINANCE REPORT</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="mf">MF/INSURANCE</a></li>
                <li class="breadcrumb-item active" aria-current="page">MF/INSURANCE FINANCE REPORT</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
            

        <?php
    // Handle default and submitted values
    $combined_option = $_POST['combined_option'] ?? '';

    $currentDate = date('Y-m-d');
    $firstDayOfMonth = date('Y-m-01');
    $lastDayOfMonth = date('Y-m-t');

    $start_date = $_POST['start_date'] ?? $firstDayOfMonth;
    $end_date = $_POST['end_date'] ?? $lastDayOfMonth;
?>

        <form method="POST">
            <div class="row">
                
               <!-- TYPE Dropdown -->
        <div class="col-md-2 field">
            <label for="combined_option" class="form-label">Type :</label>
            <select name="combined_option" class="form-control">
                <option value="">All</option>
                <option value="SIP" <?= $combined_option === 'SIP' ? 'selected' : '' ?>>SIP</option>
                <option value="SWP" <?= $combined_option === 'SWP' ? 'selected' : '' ?>>SWP</option>
                <option value="Lumsum" <?= $combined_option === 'Lumsum' ? 'selected' : '' ?>>Lumsum</option>
                <option value="LIC" <?= $combined_option === 'LIC' ? 'selected' : '' ?>>LIC</option>
                <option value="GIC" <?= $combined_option === 'GIC' ? 'selected' : '' ?>>GIC</option>
            </select>
        </div>

        <!-- Start Date -->
        <div class="col-md-2 field">
            <label for="start_date" class="form-label">Start Date :</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date); ?>" />
        </div>

        <!-- End Date -->
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
        
        <?php 
        // Debugging: Print fetched results to verify the values
// echo "<pre>";
// print_r($results); // Check if values are as expected
// echo "</pre>";

?>

<?php if (!empty($results)): ?>
<div id="reportSection" class="mt-5">
    <h1 class="text-center">MF Finance Report</h1>
    <table class="table my-5">
        <thead>
            <tr>
                <th>Type</th>
                
                <th>Total Amount</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td>
                <?php 
                // Display mf_option or insurance_option, excluding 'Select option'
                $mf_type = $row['mf_option'] !== 'Select option' ? $row['mf_option'] : 
                        ($row['insurance_option'] !== 'Select option' ? $row['insurance_option'] : 'N/A');
                echo htmlspecialchars($mf_type); 
                ?>
            </td>
                    
                    <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                   
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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

