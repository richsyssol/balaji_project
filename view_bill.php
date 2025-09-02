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
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container data-table p-5 ">
        <div class="ps-5">
            <div>
                <h1>BILLS</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">BILLS</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .bill-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }
        .bill-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a86e8;
            padding-bottom: 20px;
        }
        .bill-title {
            font-size: 24px;
            font-weight: bold;
            color: #4a86e8;
            margin: 0;
        }
        .bill-subtitle {
            font-size: 16px;
            color: #666;
        }
        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bill-from, .bill-to {
            width: 48%;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .service-type {
            background-color: #4a86e8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .bill-summary {
            margin-left: auto;
            width: 50%;
        }
        .bill-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .bill-summary-row.total {
            font-weight: bold;
            border-top: 2px solid #4a86e8;
            font-size: 1.1em;
        }
        .bill-notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #4a86e8;
        }
        .bill-footer {
            margin-top: 40px;
            text-align: center;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .action-buttons {
            text-align: center;
            margin-top: 20px;
        }
        .action-buttons button {
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #4a86e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .action-buttons button:hover {
            background-color: #3a76d8;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .bill-container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .action-buttons {
                display: none;
            }
        }
    </style>

    <div class="bill-container">
        <div class="bill-header">
            <h1 class="bill-title">INVOICE</h1>
            <p class="bill-subtitle">Number: <?php echo $bill_id; ?></p>
            <p class="bill-subtitle">Date: <?php echo date('F j, Y', strtotime($bill_date)); ?></p>
        </div>
        
        <div class="bill-info">
            <div class="bill-from">
                <div class="info-label">FROM:</div>
                <div>Your Company Name</div>
                <div>123 Business Street</div>
                <div>Business City, BC 12345</div>
                <div>Phone: (123) 456-7890</div>
                <div>Email: company@example.com</div>
            </div>
            
            <div class="bill-to">
                <div class="info-label">BILL TO:</div>
                <div><?php echo htmlspecialchars($bill['client_name']); ?></div>
                <div><?php echo htmlspecialchars($bill['address']); ?></div>
                <div>Contact: <?php echo htmlspecialchars($bill['contact']); ?></div>
                <?php if (!empty($bill['email'])): ?>
                <div>Email: <?php echo htmlspecialchars($bill['email']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="service-type">
            Service Type: <?php echo htmlspecialchars($service_type); ?>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo formatCurrency($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($item['amount']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="bill-summary">
            <div class="bill-summary-row">
                <div>Subtotal:</div>
                <div><?php echo formatCurrency($subtotal); ?></div>
            </div>
            <?php if ($tax > 0): ?>
            <div class="bill-summary-row">
                <div>Tax (<?php echo htmlspecialchars($tax_rate); ?>%):</div>
                <div><?php echo formatCurrency($tax); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($discount > 0): ?>
            <div class="bill-summary-row">
                <div>Discount:</div>
                <div><?php echo formatCurrency($discount); ?></div>
            </div>
            <?php endif; ?>
            <div class="bill-summary-row total">
                <div>TOTAL:</div>
                <div><?php echo formatCurrency($total); ?></div>
            </div>
        </div>
        
        <?php if (!empty($notes)): ?>
        <div class="bill-notes">
            <strong>Notes:</strong><br>
            <?php echo nl2br(htmlspecialchars($notes)); ?>
        </div>
        <?php endif; ?>
        
        <div class="bill-footer">
            <p>Thank you for your business!</p>
            <p>Please make payment within 30 days of receiving this invoice.</p>
        </div>
    </div>
    
    <div class="action-buttons">
        <button onclick="window.print()">Print Bill</button>
    </div>
    </div>
</section>