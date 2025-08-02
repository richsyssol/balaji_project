<?php 


    include 'session_check.php';
   
?> 


<?php
// Database connection 
include 'includes/db_conn.php';

// Initialize report variable
$report = [];
$total_amount = 0; // To store the total amount

// Check if the form is submitted
if (isset($_POST['generate_report']) || isset($_POST['generate_csv']) || isset($_POST['generate_pdf'])) {
    
    // Initialize query variable
    $query = "";

    // Handle search by date range
    if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        // Prepare the query to fetch expenses in the specified date range
        $query = "SELECT date, amount, pay_mode, expense_type, details, username FROM expenses WHERE date BETWEEN '$from_date' AND '$to_date' AND is_deleted = 0";
    }

    // Execute the query
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $report[] = $row; // Store results in the report array
            $total_amount += $row['amount']; // Sum up amounts
        }
    } else {
        echo "No records found.";
    }

    // If user clicks on 'Generate CSV'
    if (isset($_POST['generate_csv'])) {
        // CSV generation logic
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=expense_report.csv');

        // Open file in write mode for generating CSV
        $output = fopen('php://output', 'w');

        // Output the column headers
        fputcsv($output, ['Date', 'Amount', 'Pay Mode', 'Expense Type', 'Details', 'Username']);

        // Output each row of the report
        foreach ($report as $item) {
            fputcsv($output, [
                (new DateTime($item['date']))->format('d/m/Y'),
                $item['amount'],
                $item['pay_mode'],
                $item['expense_type'],
                $item['details'],
                $item['username']
            ]);
        }

        // Close the output file
        fclose($output);

        // Stop further script execution to prevent HTML output
        exit();
    }

    // If user clicks on 'Generate PDF'
    if (isset($_POST['generate_pdf'])) {
        ob_end_clean();
        require('fpdf/fpdf.php');

        // Instantiate and use the FPDF class 
        $pdf = new FPDF('L', 'mm', 'A3'); // Landscape mode with A3 size
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Expense Report', 0, 1, 'C');

        // Add column headers
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 10, 'Date', 1);
        $pdf->Cell(30, 10, 'Amount', 1);
        $pdf->Cell(30, 10, 'Pay Mode', 1);
        $pdf->Cell(70, 10, 'Expense Type', 1);
        $pdf->Cell(70, 10, 'Details', 1);
        $pdf->Cell(70, 10, 'Username', 1);
        $pdf->Ln();

        // Add data rows
        $pdf->SetFont('Arial', '', 10);
        foreach ($report as $item) {
            $pdf->Cell(25, 10, (new DateTime($item['date']))->format('d/m/Y'), 1);
            $pdf->Cell(30, 10, $item['amount'], 1);
            $pdf->Cell(30, 10, $item['pay_mode'], 1);
            $pdf->Cell(70, 10, $item['expense_type'], 1);
            $pdf->Cell(70, 10, $item['details'], 1);
            $pdf->Cell(70, 10, $item['username'], 1);
            
            $pdf->Ln();
        }

        // Output the PDF
        $pdf->Output('D', 'expense_report.pdf'); // Force download
        exit();
    }

    // Close the connection
    $conn->close();
}
?>

<?php
    include 'include/head.php'; 
    include 'include/navbar.php'; 
?>

<section class="d-flex pb-5">
   
    
    <div class="container p-5 my-5 bg-white">
        <h1 class="text-center">Generate Expense Report</h1>

        <!-- Form for generating report -->
        <form method="POST" class="p-3">
            <!-- Date Range Fields -->
            <div class="mb-3 date-fields field" id="fromDateField">
                <label for="from_date" class="form-label">From</label>
                <input type="date" name="from_date" class="form-control" id="from_date" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-3 date-fields field" id="toDateField">
                <label for="to_date" class="form-label">To</label>
                <input type="date" name="to_date" class="form-control" id="to_date" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <button type="submit" name="generate_report" class="btn sub-btn1 w-25">Generate Report</button>
            <button type="submit" name="generate_csv" class="btn sub-btn1 w-25">Generate CSV</button>
            <button type="submit" name="generate_pdf" class="btn sub-btn1 w-25">Generate PDF</button>
        </form>   

        <?php if (!empty($report)): ?>
            <h2 class="mt-5">Report Results</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Expense Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars((new DateTime($item['date']))->format('d/m/Y')); ?></td>
                            <td><?= htmlspecialchars($item['amount']); ?></td>
                            <td><?= htmlspecialchars($item['pay_mode']); ?></td>
                            <td><?= htmlspecialchars($item['expense_type']); ?></td>
                            <td><?= htmlspecialchars($item['details']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div>
                <strong>Total Amount: </strong> <?= htmlspecialchars($total_amount); ?>
            </div>
        <?php endif; ?>
    </div>
</section>




<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>


<?php //} ?>