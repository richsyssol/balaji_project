<?php
include 'include/head.php'; 
    include 'session_check.php';
// Database connection
include 'includes/db_conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("Bill ID not provided");
}

$bill_id = $conn->real_escape_string($_GET['id']);
$query = $conn->query("
    SELECT b.*, c.client_name, c.contact, c.address, c.email
    FROM bills b
    JOIN client c ON b.client_id = c.id
    WHERE b.id = '$bill_id'
");

// Check if query failed
if ($query === false) {
    die("Query failed: " . $conn->error);
}

if ($query->num_rows === 0) {
    die("Bill not found");
}

$bill = $query->fetch_assoc();

// Parse the bill data
$bill_data = json_decode($bill['bill_data'], true);
$service_type = isset($bill_data['service_type']) ? $bill_data['service_type'] : 'General';
$items = isset($bill_data['items']) ? $bill_data['items'] : [];
$subtotal = isset($bill_data['subtotal']) ? $bill_data['subtotal'] : 0;
$tax = isset($bill_data['tax']) ? $bill_data['tax'] : 0;
$tax_rate = isset($bill_data['tax_rate']) ? $bill_data['tax_rate'] : 0;
$discount = isset($bill_data['discount']) ? $bill_data['discount'] : 0;
$total = isset($bill_data['total']) ? $bill_data['total'] : 0;
$notes = isset($bill_data['notes']) ? $bill_data['notes'] : '';
$bill_date = isset($bill_data['bill_date']) ? $bill_data['bill_date'] : $bill['bill_date'];

// Function to format currency
function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}


function convertNumberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Forty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        100000              => 'Lakh',
        10000000            => 'Crore'
    ];

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convertNumberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convertNumberToWords(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int)($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convertNumberToWords($remainder);
            }
            break;
        default:
            $baseUnit     = pow(1000, floor(log($number, 1000)));
            if ($baseUnit >= 10000000) $baseUnit = 10000000;
            elseif ($baseUnit >= 100000) $baseUnit = 100000;
            elseif ($baseUnit >= 1000) $baseUnit = 1000;

            $numBaseUnits = (int)($number / $baseUnit);
            $remainder    = $number % $baseUnit;
            $string = convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convertNumberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = [];
        foreach (str_split((string)$fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}


?>

<section class="container d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container-fluid p-4">
        
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="fw-bold">BILLS</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">BILLS</li>
                </ol>
            </nav>
        </div>

        <!-- Invoice Section -->
        <div class="bg-white shadow rounded p-5" id="reportSection">

            <!-- Header / Letterhead -->
            <div class="text-center border-bottom pb-3 mb-4">
                <h3 class="mb-0">BHAURAO YASHVANTRAO PINGLE</h3>
                <h5 class="mb-1">BALAJI MOTOR DRIVING SCHOOL</h5>
                <p class="mb-1">
                    3/4 Gurukrupa Sankul, Pimpalgaon(B)<br>
                    Tal. Niphad, Dist. Nashik-422209<br>
                    Mo. No. 9881063639 / 9960581819<br>
                    GST No. 27AIGPP4458B1Z9
                </p>
                <h1 class="text-primary fw-bold mt-3">INVOICE</h1>
            </div>

            <!-- Bill Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($bill_date)); ?></p>
                    <p><strong>Invoice No:</strong> <?= $bill_id; ?></p>
                    <h6 class="fw-bold mt-3">BILL TO:</h6>
                    <p class="mb-0"><?= htmlspecialchars($bill['client_name']); ?></p>
                    <p class="mb-0"><?= htmlspecialchars($bill['address']); ?></p>
                    <p class="mb-0">Contact: <?= htmlspecialchars($bill['contact']); ?></p>
                    <?php if (!empty($bill['email'])): ?>
                        <p>Email: <?= htmlspecialchars($bill['email']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bill Items Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['description']); ?></td>
                                <td><?= formatCurrency($item['unit_price']); ?></td>
                                <td class="text-end"><?= formatCurrency($item['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals Section -->
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Subtotal:</span>
                        <span><?= formatCurrency($subtotal); ?></span>
                    </div>
                    <?php if ($tax > 0): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Tax (<?= htmlspecialchars($tax_rate); ?>%):</span>
                            <span><?= formatCurrency($tax); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between fw-bold fs-5 pt-2 mt-2">
                        <span>Total:</span>
                        <span><?= formatCurrency($total); ?></span>
                    </div>
                    <p class="mt-2 fw-bold">
                        Amount in Words: <?= ucwords(convertNumberToWords(round($total))) . ' Rupees Only'; ?>
                    </p>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="row mt-5">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-end">
                    <h5 class="fw-bold mb-4">Authorised Signatory</h5>
                    <div class="p-5"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-5 border-top pt-3 text-muted">
                <p class="mb-0">Subject to Pimpalgaon(B) jurisdiction</p>
                <p class="mb-0">Thanks for doing business with us!</p>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4">
            <button type="button" class="btn btn-primary" onclick="showPasswordModal()">Print</button>
        </div>
    </div>
</section>


<!-- PRINT CSS -->
<style>
@media print {
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        margin: 0;
        padding: 0;
    }
    @page {
        size: A4;
        margin: 30mm 10mm 10mm 10mm;  /* adjust to fit letterhead layout */
    }
    #reportSection {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100%;
    }
    .btn, .breadcrumb, .navbar, .action-buttons {
        display: none !important; /* hide buttons, navbar, etc. */
    }
}
</style>



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